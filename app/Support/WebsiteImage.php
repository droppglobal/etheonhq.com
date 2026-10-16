<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Smaller WebP copies of uploaded photos for the public website. A copy is made the first time it is needed
 * (upload/_resized/{width}/...) and reused after that; the original is used when that is not possible.
 */
class WebsiteImage
{
    private const MAX_NEW_PER_REQUEST = 16; // keep a first visit fast; the rest are made on later visits
    private static $made = 0;

    public static function url($path, $width = 800)
    {
        $original = WebsiteSite::safeUrl(asset(Storage::url($path)));
        try {
            $src = Storage::path($path);
            if (!is_file($src) || !function_exists('imagewebp')) {
                return $original;
            }
            $target = 'upload/_resized/' . (int) $width . '/' . substr(md5($path . '|' . filemtime($src)), 0, 20) . '.webp';
            if (!Storage::exists($target)) {
                if (self::$made >= self::MAX_NEW_PER_REQUEST || !self::make($src, Storage::path($target), $width)) {
                    return $original;
                }
                self::$made++;
            }
            return asset(Storage::url($target));
        } catch (\Throwable $e) {
            return $original;
        }
    }

    private static function make($src, $dest, $width)
    {
        $info = @getimagesize($src);
        if (!$info || $info[0] < 1 || $info[0] * $info[1] > 40000000) { // skip unreadable or huge images
            return false;
        }
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                $img = @imagecreatefromjpeg($src);
                break;
            case IMAGETYPE_PNG:
                $img = @imagecreatefrompng($src);
                break;
            case IMAGETYPE_WEBP:
                $img = @imagecreatefromwebp($src);
                break;
            case IMAGETYPE_GIF:
                $img = @imagecreatefromgif($src);
                break;
            default:
                return false;
        }
        if (!$img) {
            return false;
        }
        $w = min($width, $info[0]);
        $h = (int) round($info[1] * $w / $info[0]);
        $out = imagecreatetruecolor($w, $h);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $img, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);
        if (!is_dir(dirname($dest))) {
            @mkdir(dirname($dest), 0775, true);
        }
        $ok = imagewebp($out, $dest, 80);
        imagedestroy($img);
        imagedestroy($out);
        return $ok && is_file($dest);
    }
}
