<?php

namespace App\Http\Controllers\Api;

use App\Models\MobileDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PragmaRX\Google2FAQRCode\Google2FA;

/**
 * Mobile app sign-in with Sanctum tokens. Version 1 of the app is for tenants and maintainers.
 */
class AuthController extends BaseController
{
    const APP_TYPES = ['tenant', 'maintainer'];

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:100',
            'otp' => 'nullable|string|max:10',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->fail(__('These credentials do not match our records.'), 422);
        }
        if ((int) $user->is_active !== 1) {
            return $this->fail(__('Your account has been deactivated. Please contact the office.'), 403);
        }
        if (empty($user->email_verified_at)) {
            return $this->fail(__('Please verify your email address before logging in.'), 403);
        }
        if (!in_array($user->type, self::APP_TYPES)) {
            return $this->fail(__('The app is for tenants and maintainers. Please use the website to manage your account.'), 403);
        }

        // two-factor: the app asks for the 6-digit code and sends the login again with "otp"
        if (!empty($user->twofa_secret)) {
            if (empty($request->otp)) {
                return response()->json(['message' => __('Enter the 6-digit code from your authenticator app.'), 'two_factor_required' => true], 409);
            }
            if (!(new Google2FA())->verify($request->otp, $user->twofa_secret)) {
                return $this->fail(__('Incorrect Code. Please try again...'), 422);
            }
        }

        $token = $user->createToken(mb_substr($request->device_name, 0, 100), ['mobile'])->plainTextToken;
        \Auth::setUser($user); // settings(), priceFormat() etc. use the account of this user

        return $this->ok(['token' => $token, 'user' => $this->profile($user)]);
    }

    // public: platform name, logos and colours for the login screen (changeable from the website settings)
    public function appConfig()
    {
        return $this->ok(\App\Support\Branding::withVersion(\App\Support\Branding::platform() + [
            // public Firebase app settings (from google-services.json / GoogleService-Info.plist), so the app needs no rebuild;
            // inside the version so phones notice when they are pasted or changed
            'firebase' => \App\Support\FcmPush::clientConfig(),
        ]))->header('Cache-Control', 'public, max-age=300');
    }

    public function me(Request $request)
    {
        return $this->ok($this->profile($request->user()));
    }

    public function logout(Request $request)
    {
        if ($request->filled('fcm_token')) {
            MobileDevice::where('user_id', $request->user()->id)->where('token_hash', hash('sha256', $request->fcm_token))->delete();
        }
        optional($request->user()->currentAccessToken())->delete();
        return $this->ok(null, __('Logged out.'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            \Log::info('Mobile password reset mail failed: ' . $e->getMessage());
        }
        // same answer whether or not the address exists, so accounts cannot be discovered
        return $this->ok(null, __('If this email has an account, a password reset link is on its way.'));
    }

    public function registerDevice(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string|max:4096',
            'platform' => 'nullable|in:android,ios',
            'app_version' => 'nullable|string|max:20',
            'device_name' => 'nullable|string|max:100',
        ]);
        // the same phone re-registers on every app start; keep details it does not send again
        MobileDevice::updateOrCreate(['token_hash' => hash('sha256', $request->fcm_token)], array_filter([
            'user_id' => $request->user()->id,
            'parent_id' => $request->user()->parent_id,
            'fcm_token' => $request->fcm_token,
            'platform' => $request->platform,
            'app_version' => $request->app_version,
            'device_name' => $request->device_name,
            'last_seen_at' => now(),
        ], fn($v) => $v !== null && $v !== ''));
        return $this->ok(null, __('Notifications are on for this phone.'));
    }

    // switch notifications off for this phone
    public function removeDevice(Request $request)
    {
        $request->validate(['fcm_token' => 'required|string|max:4096']);
        MobileDevice::where('user_id', $request->user()->id)->where('token_hash', hash('sha256', $request->fcm_token))->delete();
        return $this->ok(null, __('Notifications are off for this phone.'));
    }

    // sends a notification to the user's own phones (checks the whole set-up end to end)
    public function testPush(Request $request)
    {
        if (!\App\Support\FcmPush::configured()) {
            return $this->fail(__('Push notifications are not set up on the website yet (Settings > Mobile App).'));
        }
        if (!MobileDevice::where('user_id', $request->user()->id)->exists()) {
            return $this->fail(__('This phone is not registered for notifications yet. Turn notifications on first.'));
        }
        \App\Support\Notify::push($request->user()->id, __('Test notification'), __('Notifications are working on this phone.'), route('dashboard'));
        return $this->ok(null, __('Test notification sent. It should arrive in a few seconds.'));
    }

    // ------------------------------------------------------------------ notification inbox

    public function notifications(Request $request)
    {
        $page = \App\Models\AppNotification::where('user_id', $request->user()->id)->orderByDesc('id')->paginate(30);
        $unread = \App\Models\AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count();
        return response()->json([
            'data' => collect($page->items())->map(fn($n) => [
                'id' => $n->id, 'title' => $n->title, 'body' => $n->body, 'screen' => $n->screen,
                'read' => !empty($n->read_at), 'at' => $this->dateTime($n->created_at),
                // records written by the cron job can carry another time zone; never show a time "from now"
                'ago' => $n->created_at->isFuture() ? __('just now') : $n->created_at->diffForHumans(),
            ])->values(),
            'meta' => ['page' => $page->currentPage(), 'per_page' => $page->perPage(), 'total' => $page->total(), 'last_page' => $page->lastPage(), 'unread' => $unread],
        ]);
    }

    public function unreadCount(Request $request)
    {
        return $this->ok(['unread' => \App\Models\AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count()]);
    }

    // marks one notification (id) or all of them as read
    public function markRead(Request $request)
    {
        $query = \App\Models\AppNotification::where('user_id', $request->user()->id)->whereNull('read_at');
        if ($request->filled('id')) {
            $query->where('id', (int) $request->id);
        }
        $query->update(['read_at' => now()]);
        return $this->ok(['unread' => \App\Models\AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|nullable|string|max:100',
            'phone_number' => 'sometimes|nullable|string|max:30',
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'nullable|string|min:6|confirmed',
        ]);
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return $this->fail(__('Your current password is not correct.'), 422);
            }
            $user->password = Hash::make($request->new_password);
        }
        foreach (['first_name', 'last_name', 'phone_number'] as $field) {
            if ($request->has($field)) {
                $user->{$field} = $request->{$field};
            }
        }
        $user->save();
        return $this->ok($this->profile($user), __('Profile updated.'));
    }

    // who is signed in, plus the owner company's branding and formats for the app to use
    private function profile(User $user)
    {
        $company = settingsById($user->parent_id);
        $paymentSettings = invoicePaymentSettings($user->parent_id);
        return [
            'id' => $user->id,
            'type' => $user->type,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'avatar' => !empty($user->profile) ? fetch_file($user->profile, 'upload/profile/') : null,
            'language' => $user->lang,
            'company' => [
                'name' => $company['company_name'] ?? '',
                'email' => $company['company_email'] ?? '',
                'phone' => $company['company_phone'] ?? '',
                'address' => $company['company_address'] ?? '',
                'logo' => !empty($company['company_logo']) ? fetch_file($company['company_logo'], 'upload/logo/') : null,
                'currency_symbol' => $paymentSettings['CURRENCY_SYMBOL'] ?? ($company['CURRENCY_SYMBOL'] ?? '$'),
                'currency' => $paymentSettings['CURRENCY'] ?? 'USD',
            ],
            // the company's own logo, favicon, name and colours (falls back to the platform's)
            'branding' => \App\Support\Branding::withVersion(\App\Support\Branding::forCompany($user->parent_id)),
            'features' => [
                // quick answers work without AI; with AI enabled the assistant answers free-text questions too
                'assistant' => $user->type == 'tenant',
                'assistant_ai' => $user->type == 'tenant' && \App\Support\AiService::enabledFor($user->parent_id),
                'online_payment' => \App\Support\PayLink::canPayOnline($user->parent_id),
                'push' => \App\Support\FcmPush::configured(),
            ],
        ];
    }
}
