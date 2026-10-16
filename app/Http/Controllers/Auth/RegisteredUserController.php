<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PackageTransaction;
use App\Models\Page;
use App\Models\Subscription;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = \App\Models\User::find(1);
        \App::setLocale($user->lang);
        $registerPage = getSettingsValByName('register_page');

        if ($registerPage == 'on') {
            $menu = Page::where('slug', 'terms_conditions')->first();
            return view('auth.register', compact('menu'));
        } else {
            return redirect()->route('login');
        }
    }

    /**
     * Handle an incoming registration request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
         $google_recaptcha = getSettingsValByName('google_recaptcha');
        if ($google_recaptcha == 'on') {
            $validation['g-recaptcha-response'] = 'required|captcha';
        } else {
            $validation = [];
        }
        $this->validate($request, $validation);


        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $userData = [
            'first_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'owner',
            'lang' => 'english',

            'code' => uniqid(),
            'parent_id' => 1,
        ];
        $owner_email_verification = getSettingsValByName('owner_email_verification');
        $owner = User::create($userData);
        $userRole = Role::findByName('owner');
        $owner->assignRole($userRole);
        Auth::login($owner);
        defaultTenantCreate($owner->id);
        defaultMaintainerCreate($owner->id);
        defaultLandlordCreate($owner->id);
        defaultStaffRolesCreate($owner->id);
        defaultTemplate($owner->id);

        // Default frontend Template
        FrontHomePageSection($owner->id);
        AdditionalPageSection($owner->id);

        if ($request->free_trial == 1) {
            $settings = subscriptionPaymentSettings();

            $id = decrypt($request->subscription_id);
            $subscription = Subscription::find($id);
            $packageTransId = uniqid('', true);

            $userId = auth()->id();
            $userEmail = auth()->user()->email;
            $userIp = $_SERVER['REMOTE_ADDR'];

            $hasUsedTrial = PackageTransaction::where('free_trial', 1)
                ->where(function ($query) use ($userId, $userEmail, $userIp) {
                    $query->where('user_id', $userId)->orWhere('email', $userEmail)->orWhere('ip', $userIp);
                })
                ->exists();

            if ($hasUsedTrial) {
                return redirect()->route('subscriptions.index')->with('error', __('Free trial has already been used.'));
            }

            $data['holder_name'] = $request->name;
            $data['subscription_id'] = $subscription->id;
            $data['amount'] = 0;
            $data['subscription_transactions_id'] = $packageTransId;
            $data['payment_type'] = 'Trial';
            $data['status'] = 'trial';
            $data['free_trial'] = 1;
            $data['ip'] = $_SERVER['REMOTE_ADDR'];
            $data['email'] = auth()->user()->email;


            PackageTransaction::transactionData($data);

            $trialDays = $subscription->free_trial_days ?? 0;
    
            $user = User::find(auth()->id());
            $user->subscription = $subscription->id;
            $user->subscription_expire_date = now()->addDays($trialDays);
            $user->save();

            return redirect()->route('subscriptions.index')->with('success', __('Free trial activated successfully.'));
        }


        if ($owner_email_verification == 'on') {
            $token = sha1($owner->email);
            $url = route('email-verification', $token);

            $owner->email_verification_token = $token;
            $owner->save();

            $data = [
                'module' => 'email_verification',
                'subject' => 'Email Verification',
                'email' => $owner->email,
                'name' => $owner->name,
                'url' => $url,
            ];
            $to = $owner->email;
            $response = sendEmailVerification($to, $data);
            if ($response['status'] == 'success') {
                auth()->logout();
                return redirect()->route('login')->with('error', __('We have sent an account verification email to your registered email inbox. Please check your email and follow the instructions to verify your account.'));
            } else {
                $owner->delete();
                return redirect()->back()->with('error',  $response['message']);
            }
        } else {
            $module = 'owner_create';
            $setting = settings();
            if (!empty($owner)) {
                $data['subject'] = 'New User Created';
                $data['module'] = $module;
                $data['password'] = $request->password;
                $data['name'] = $request->name;
                $data['email'] = $request->email;
                $data['url'] = env('APP_URL');
                $data['logo'] = $setting['company_logo'];
                $to = $owner->email;
                commonEmailSend($to, $data);
            }
            $owner->email_verified_at = now();
            $owner->email_verification_token = null;
            $owner->save();
            return redirect(RouteServiceProvider::HOME);
        }
    }
}
