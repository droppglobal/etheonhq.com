<?php

namespace App\Support;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Owners' own domains (www.acmehomes.com) for their public website. Allowed per plan ("Custom domain"),
 * or for everyone when the pricing feature is off.
 */
class WebsiteDomains
{
    private static $allowed = [];

    public static function allowed($owner)
    {
        if (!$owner || $owner->type !== 'owner') {
            return false;
        }
        if (!array_key_exists($owner->id, self::$allowed)) {
            $pricing = getSettingsValByIdName(1, 'pricing_feature');
            self::$allowed[$owner->id] = $pricing === 'off' || (int) optional(Subscription::find($owner->subscription))->enabled_custom_domain === 1;
        }
        return self::$allowed[$owner->id];
    }

    // "https://www.Example.com/" -> "www.example.com"; '' when it is not a usable domain name
    public static function clean($domain)
    {
        $domain = strtolower(trim((string) $domain));
        $domain = preg_replace('#^[a-z]+://#', '', $domain);
        $domain = rtrim(explode('/', $domain)[0], '.');
        if (!preg_match('/^(?=.{4,253}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain)) {
            return '';
        }
        $appHost = strtolower((string) parse_url(config('app.url'), PHP_URL_HOST));
        return $domain === $appHost ? '' : $domain;
    }

    // domain => owner code, for every owner who set one
    public static function map()
    {
        return Cache::rememberForever('website_domains_map', function () {
            $map = [];
            foreach (DB::table('settings')->where('name', 'website_seo')->get(['parent_id', 'value']) as $row) {
                $domain = json_decode((string) $row->value, true)['domain'] ?? '';
                $code = $domain ? User::where('id', $row->parent_id)->where('type', 'owner')->value('code') : null;
                if ($domain && $code) {
                    $map[$domain] = $code;
                }
            }
            return $map;
        });
    }

    public static function forget()
    {
        Cache::forget('website_domains_map');
    }

    // the owner for a request host (with or without "www.")
    public static function ownerFor($host)
    {
        $host = strtolower((string) $host);
        $map = self::map();
        $code = $map[$host] ?? $map[preg_replace('/^www\./', '', $host)] ?? $map['www.' . $host] ?? null;
        $owner = $code ? User::where('code', $code)->where('type', 'owner')->first() : null;
        return $owner && self::allowed($owner) ? $owner : null;
    }

    // is the domain pointing at this server yet?
    public static function check($domain)
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $server = gethostbyname($appHost);
        $target = gethostbyname($domain);
        return ['server_ip' => $server, 'domain_ip' => $target === $domain ? '' : $target, 'ok' => $target !== $domain && $target === $server];
    }
}
