<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Mobile API requests: the token's user must still be active and of an allowed type
 * (e.g. "mobile:tenant,maintainer"); language and timezone follow the user's account like the website.
 */
class MobileApi
{
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => __('Please log in again.')], 401);
        }
        if ((int) $user->is_active !== 1) {
            optional($user->currentAccessToken())->delete();
            return response()->json(['message' => __('Your account has been deactivated. Please contact the office.')], 403);
        }
        if ($types && !in_array($user->type, $types)) {
            return response()->json(['message' => __('This part of the app is not available for your account.')], 403);
        }

        if (!empty($user->lang)) {
            \App::setLocale($user->lang);
        }
        $timezone = getSettingsValByName('timezone');
        if (!empty($timezone)) {
            \Config::set('app.timezone', $timezone);
            date_default_timezone_set($timezone);
        }
        return $next($request);
    }
}
