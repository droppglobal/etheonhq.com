<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\WebsiteSite;
use Closure;
use Illuminate\Http\Request;

// Public owner website (/web/{code}/...): pages and labels in the visitor's chosen language, times in the owner's timezone
class WebsiteLocale
{
    public function handle(Request $request, Closure $next)
    {
        $code = $request->route('code');
        if ($code && ($owner = User::where('code', $code)->where('type', 'owner')->first())) {
            useAccountSettings($owner->id); // prices and dates in the owner's currency and format
            $site = WebsiteSite::for($owner); // picks the language and sets the app locale
            // the owner confirmed their own domain works: send visitors of the old address there
            if (!app()->bound('website.domain') && $request->isMethod('GET') && !empty($site->seo['domain_redirect']) && $site->activeDomain()) {
                $query = $request->getQueryString();
                return redirect()->away($site->canonical($request->url()) . ($query ? '?' . $query : ''), 301);
            }
            $timezone = \DB::table('settings')->where('parent_id', $owner->id)->where('name', 'timezone')->value('value');
            if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
                config(['app.timezone' => $timezone]);
                date_default_timezone_set($timezone);
            }
        }
        return $next($request);
    }
}
