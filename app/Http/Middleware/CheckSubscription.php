<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    private const OWNER_TYPE = 'owner';

    private const NO_SUBSCRIPTION_MESSAGE_KEY =
        'No active subscription found. Please choose a plan to continue.';

    private const SUBSCRIPTION_EXPIRED_MESSAGE_KEY =
        'Your subscription has ended, and access to premium features is now restricted. To continue using our services without interruption, please renew your plan or upgrade to a higher-tier package.';

    public function handle(Request $request, Closure $next)
    {
        
        $pricing_feature = getSettingsValByIdName('1','pricing_feature');
 
        if ($pricing_feature !== 'on') {
            return $next($request);
        }

       
        $user = auth()->user();

        // Not logged in OR user is not an owner
        if (!$user || $user->type !== self::OWNER_TYPE) {
            return $next($request);
        }

       
        $message = $this->getSubscriptionErrorMessage($user);

        if ($message) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ]);
            }

            return redirect()
                ->route('subscriptions.index')
                ->with('error', $message);
        }

        return $next($request);
    }

    private function getSubscriptionErrorMessage($user): ?string
    {
        // No subscription
        if (empty($user->subscription)) {
            return __(self::NO_SUBSCRIPTION_MESSAGE_KEY);
        }

        // Subscription expired
        if (
            $user->subscription_expire_date &&
            now()->gt($user->subscription_expire_date)
        ) {
            return __(self::SUBSCRIPTION_EXPIRED_MESSAGE_KEY);
        }

        return null;
    }
}