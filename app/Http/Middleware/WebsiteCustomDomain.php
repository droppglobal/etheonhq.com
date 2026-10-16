<?php

namespace App\Http\Middleware;

use App\Support\WebsiteDomains;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * An owner's own domain (www.acmehomes.com) shows their website: "/properties" is served as "/web/{code}/properties",
 * and links built on that domain leave out the "/web/{code}" part.
 */
class WebsiteCustomDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = strtolower($request->getHost());
        $appHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
        if ($host === $appHost || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return $next($request);
        }
        try {
            $owner = WebsiteDomains::ownerFor($host);
        } catch (\Throwable $e) {
            return $next($request); // e.g. before installation
        }
        if (!$owner) {
            return $next($request);
        }

        $prefix = '/web/' . $owner->code;
        $path = '/' . ltrim($request->getPathInfo(), '/');
        if ($path !== $prefix && !str_starts_with($path, $prefix . '/')) {
            $server = $request->server->all();
            $query = $request->getQueryString();
            $server['REQUEST_URI'] = $request->getBaseUrl() . $prefix . ($path === '/' ? '' : $path) . ($query ? '?' . $query : '');
            $request = $request->duplicate(null, null, null, null, null, $server);
        }

        URL::forceRootUrl($request->getSchemeAndHttpHost() . $request->getBaseUrl());
        URL::formatPathUsing(function ($path) use ($prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return substr($path, strlen($prefix)) ?: '/';
            }
            return $path;
        });
        app()->instance('website.domain', $host);

        return $next($request);
    }
}
