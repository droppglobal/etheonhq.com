<?php

namespace App\Support;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Http;

/**
 * Web Push (RFC 8030) with VAPID (RFC 8292) and aes128gcm payload encryption (RFC 8291),
 * built on PHP's OpenSSL extension only, so no GMP/BCMath or extra packages are needed.
 * The VAPID key pair is created once per installation and stored on the super admin's settings.
 */
class WebPush
{
    // DER prefix of a P-256 SubjectPublicKeyInfo; followed by the 65-byte uncompressed point
    const P256_SPKI_PREFIX = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    // tests can replace the HTTP sender: fn($endpoint, $headers, $body) => status code
    public static $sender = null;

    public static function supported()
    {
        return function_exists('openssl_pkey_derive') && defined('OPENSSL_KEYTYPE_EC');
    }

    // ------------------------------------------------------------------ VAPID keys

    public static function vapidKeys()
    {
        $rows = \DB::table('settings')->where('parent_id', 1)->whereIn('name', ['vapid_public_key', 'vapid_private_key'])->pluck('value', 'name');
        if (!empty($rows['vapid_public_key']) && !empty($rows['vapid_private_key'])) {
            return ['public' => $rows['vapid_public_key'], 'private' => $rows['vapid_private_key']];
        }
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        openssl_pkey_export($key, $pem);
        $keys = ['public' => self::b64(self::rawPublicKey($key)), 'private' => $pem];
        foreach (['vapid_public_key' => $keys['public'], 'vapid_private_key' => $keys['private']] as $name => $value) {
            \DB::table('settings')->updateOrInsert(['name' => $name, 'parent_id' => 1], ['value' => $value]);
        }
        return $keys;
    }

    public static function publicKey()
    {
        return self::vapidKeys()['public'];
    }

    // ------------------------------------------------------------------ sending

    /**
     * Sends a notification to every device a user has registered. Dead subscriptions are removed.
     * Never throws: a failed push must not break the action that triggered it.
     */
    public static function toUser($userId, $title, $body, $url = null)
    {
        if (!$userId || !self::supported()) {
            return 0;
        }
        $sent = 0;
        foreach (PushSubscription::where('user_id', $userId)->get() as $subscription) {
            try {
                $status = self::send($subscription, [
                    'title' => mb_substr((string) $title, 0, 120),
                    'body' => mb_substr(strip_tags((string) $body), 0, 300),
                    'url' => $url ?: route('dashboard'),
                ]);
                if (in_array($status, [404, 410])) {
                    $subscription->delete(); // the browser unsubscribed or the device is gone
                } elseif ($status >= 200 && $status < 300) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                \Log::info('Push notification failed: ' . $e->getMessage());
            }
        }
        return $sent;
    }

    public static function toUsers($userIds, $title, $body, $url = null)
    {
        foreach (array_unique(array_filter((array) $userIds)) as $userId) {
            self::toUser($userId, $title, $body, $url);
        }
    }

    public static function send(PushSubscription $subscription, array $payload)
    {
        $body = self::encrypt(json_encode($payload, JSON_UNESCAPED_UNICODE), self::unb64($subscription->public_key), self::unb64($subscription->auth_token));
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Encoding' => 'aes128gcm',
            'TTL' => '86400',
            'Urgency' => 'normal',
            'Authorization' => self::vapidHeader($subscription->endpoint),
        ];
        if (static::$sender) {
            return (int) call_user_func(static::$sender, $subscription->endpoint, $headers, $body);
        }
        return Http::timeout(10)->withHeaders($headers)->withBody($body, 'application/octet-stream')->post($subscription->endpoint)->status();
    }

    // ------------------------------------------------------------------ crypto

    public static function vapidHeader($endpoint)
    {
        $parts = parse_url($endpoint);
        $audience = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $email = getSettingsValByIdName(1, 'company_email') ?: 'admin@example.com';
        $header = self::b64(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = self::b64(json_encode(['aud' => $audience, 'exp' => time() + 12 * 3600, 'sub' => 'mailto:' . $email]));
        $keys = self::vapidKeys();
        openssl_sign($header . '.' . $claims, $der, openssl_pkey_get_private($keys['private']), OPENSSL_ALGO_SHA256);
        $jwt = $header . '.' . $claims . '.' . self::b64(self::derToRaw($der));
        return 'vapid t=' . $jwt . ', k=' . $keys['public'];
    }

    /**
     * RFC 8291 message encryption. $userPublic is the browser's 65-byte p256dh key, $authSecret its 16-byte auth.
     * $localKey / $salt can be fixed for the RFC test vector; normally they are random per message.
     */
    public static function encrypt($plaintext, $userPublic, $authSecret, $localKey = null, $salt = null)
    {
        $localKey = $localKey ?: openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $salt = $salt ?: random_bytes(16);
        $localPublic = self::rawPublicKey($localKey);

        $shared = openssl_pkey_derive(self::publicKeyFromRaw($userPublic), $localKey, 32);
        if ($shared === false) {
            throw new \RuntimeException('Invalid push subscription key');
        }
        $prkKey = hash_hmac('sha256', $shared, $authSecret, true);
        $ikm = hash_hmac('sha256', "WebPush: info\0" . $userPublic . $localPublic . "\x01", $prkKey, true);
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $cek = substr(hash_hmac('sha256', "Content-Encoding: aes128gcm\0\x01", $prk, true), 0, 16);
        $nonce = substr(hash_hmac('sha256', "Content-Encoding: nonce\0\x01", $prk, true), 0, 12);

        // single record: plaintext + 0x02 padding delimiter
        $cipher = openssl_encrypt($plaintext . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
        return $salt . pack('N', 4096) . chr(strlen($localPublic)) . $localPublic . $cipher . $tag;
    }

    public static function rawPublicKey($key)
    {
        $details = openssl_pkey_get_details($key);
        return "\x04" . str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
    }

    public static function publicKeyFromRaw($raw)
    {
        if (strlen($raw) !== 65 || $raw[0] !== "\x04") {
            throw new \RuntimeException('Invalid push subscription key');
        }
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode(hex2bin(self::P256_SPKI_PREFIX) . $raw), 64, "\n") . "-----END PUBLIC KEY-----\n";
        return openssl_pkey_get_public($pem);
    }

    // ECDSA signatures from OpenSSL are DER; JWT ES256 wants the raw 64-byte r||s
    public static function derToRaw($der)
    {
        $offset = 3 + (ord($der[1]) & 0x80 ? 1 : 0);
        $rLen = ord($der[$offset]);
        $r = substr($der, $offset + 1, $rLen);
        $offset += 1 + $rLen + 1;
        $sLen = ord($der[$offset]);
        $s = substr($der, $offset + 1, $sLen);
        $fix = fn($n) => str_pad(ltrim($n, "\0"), 32, "\0", STR_PAD_LEFT);
        return $fix($r) . $fix($s);
    }

    public static function b64($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function unb64($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
