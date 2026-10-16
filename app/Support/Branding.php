<?php

namespace App\Support;

/**
 * Name, logos, favicon and colours for the mobile app, read from the website settings so they change
 * without a new app build. Platform branding comes from the super admin (Settings > General and theme);
 * a company's own settings (the owner's General settings and theme) override it after sign-in.
 */
class Branding
{
    // theme presets of the website: [button / link colour, dark accent]
    const PRESETS = [
        'preset-1' => ['#673ab7', '#2196f3'],
        'preset-2' => ['#009688', '#607d8b'],
        'preset-3' => ['#ec407a', '#203461'],
        'preset-4' => ['#c77e23', '#16595a'],
        'preset-5' => ['#3fb0ac', '#173e43'],
        'preset-6' => ['#2ca58d', '#0a2342'],
        'preset-7' => ['#3f51b5', '#3f51b5'],
    ];

    private static function colours(array $settings)
    {
        if (($settings['color_type'] ?? '') == 'custom' && preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($settings['custom_color_code'] ?? ''))) {
            return [strtolower($settings['custom_color_code']), self::PRESETS['preset-6'][1]];
        }
        return self::PRESETS[$settings['accent_color'] ?? 'preset-6'] ?? self::PRESETS['preset-6'];
    }

    private static function file($name)
    {
        return !empty($name) ? fetch_file($name, 'upload/logo/') : null;
    }

    public static function platform()
    {
        $s = settingsById(1);
        [$brand, $dark] = self::colours($s);
        return [
            // only an Application Name set in Settings > General; otherwise the app keeps its own name ("Smart Tenant")
            'name' => !empty($s['app_name']) ? $s['app_name'] : null,
            'logo' => self::file($s['logo'] ?? $s['company_logo'] ?? null),
            'light_logo' => self::file($s['light_logo'] ?? null),
            'favicon' => self::file($s['favicon'] ?? $s['company_favicon'] ?? null),
            'brand_color' => $brand,
            'dark_color' => $dark,
        ];
    }

    /**
     * Branding for the company a tenant or maintainer belongs to: the owner's own logo, favicon, name and
     * theme where set, otherwise the platform's.
     */
    public static function forCompany($ownerId)
    {
        $platform = self::platform();
        if (empty($ownerId) || (int) $ownerId === 1) {
            return $platform;
        }
        $raw = \DB::table('settings')->where('parent_id', $ownerId)->pluck('value', 'name');
        $s = settingsById($ownerId);
        [$brand, $dark] = $raw->has('accent_color') || $raw->has('color_type') ? self::colours($s) : [$platform['brand_color'], $platform['dark_color']];
        return [
            'name' => !empty($raw['app_name']) ? $raw['app_name'] : $platform['name'],
            'logo' => !empty($raw['company_logo']) ? self::file($raw['company_logo']) : $platform['logo'],
            'light_logo' => !empty($raw['company_light_logo']) ? self::file($raw['company_light_logo']) : $platform['light_logo'],
            'favicon' => !empty($raw['company_favicon']) ? self::file($raw['company_favicon']) : $platform['favicon'],
            'brand_color' => $brand,
            'dark_color' => $dark,
        ];
    }

    // adds a short version so the app can tell when anything changed
    public static function withVersion(array $branding)
    {
        return $branding + ['version' => substr(md5(json_encode($branding)), 0, 12)];
    }
}
