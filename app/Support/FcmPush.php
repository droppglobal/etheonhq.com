<?php

namespace App\Support;

use App\Models\MobileDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Push notifications to the mobile app through Firebase Cloud Messaging (HTTP v1 API).
 * The super admin pastes the Firebase service-account JSON in Settings > Mobile App; the OAuth
 * access token is made from it with OpenSSL (RS256 JWT) and cached for 50 minutes.
 */
class FcmPush
{
    // tests replace the HTTP layer: fn($method, $url, $headers, $body) => [status, decodedBody]
    public static $sender = null;

    public static function serviceAccount()
    {
        $json = getSettingsValByIdName(1, 'fcm_service_account');
        $data = $json ? json_decode($json, true) : null;
        return (is_array($data) && !empty($data['project_id']) && !empty($data['client_email']) && !empty($data['private_key'])) ? $data : null;
    }

    /**
     * Firebase settings the phone app needs to start Firebase (public by design: they ship inside every app).
     * Taken from the google-services.json (Android) and GoogleService-Info.plist (iOS) pasted in Settings > Mobile App.
     */
    public static function clientConfig()
    {
        $config = json_decode((string) getSettingsValByIdName(1, 'fcm_client_config'), true);
        return is_array($config) && (!empty($config['android']) || !empty($config['ios'])) ? $config : null;
    }

    // reads google-services.json → the Android app settings
    public static function parseGoogleServicesJson($json)
    {
        $data = json_decode((string) $json, true);
        $client = $data['client'][0] ?? null;
        if (!$client || empty($data['project_info']['project_id'])) {
            return null;
        }
        return array_filter([
            'api_key' => $client['api_key'][0]['current_key'] ?? null,
            'app_id' => $client['client_info']['mobilesdk_app_id'] ?? null,
            'messaging_sender_id' => $data['project_info']['project_number'] ?? null,
            'project_id' => $data['project_info']['project_id'],
            'storage_bucket' => $data['project_info']['storage_bucket'] ?? null,
            'package_name' => $client['client_info']['android_client_info']['package_name'] ?? null,
        ]);
    }

    // reads GoogleService-Info.plist → the iOS app settings
    public static function parseGoogleServicePlist($xml)
    {
        if (!preg_match_all('#<key>([A-Z_]+)</key>\s*<string>([^<]*)</string>#', (string) $xml, $m)) {
            return null;
        }
        $v = array_combine($m[1], $m[2]);
        if (empty($v['GOOGLE_APP_ID']) || empty($v['PROJECT_ID'])) {
            return null;
        }
        return array_filter([
            'api_key' => $v['API_KEY'] ?? null,
            'app_id' => $v['GOOGLE_APP_ID'],
            'messaging_sender_id' => $v['GCM_SENDER_ID'] ?? null,
            'project_id' => $v['PROJECT_ID'],
            'storage_bucket' => $v['STORAGE_BUCKET'] ?? null,
            'bundle_id' => $v['BUNDLE_ID'] ?? null,
        ]);
    }

    public static function configured()
    {
        return self::serviceAccount() !== null && function_exists('openssl_sign');
    }

    private static function http($method, $url, array $headers, $body)
    {
        if (static::$sender) {
            return call_user_func(static::$sender, $method, $url, $headers, $body);
        }
        $request = Http::timeout(10)->withHeaders($headers);
        $response = is_array($body) ? $request->asForm()->post($url, $body) : $request->withBody($body, 'application/json')->post($url);
        return [$response->status(), $response->json()];
    }

    public static function accessToken(array $account)
    {
        $cacheKey = 'fcm_access_token_' . md5($account['client_email']);
        if (!static::$sender && ($cached = Cache::get($cacheKey))) {
            return $cached;
        }
        $now = time();
        $b64 = fn($d) => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
        $header = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = $b64(json_encode([
            'iss' => $account['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $account['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));
        if (!openssl_sign($header . '.' . $claims, $signature, $account['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('The Firebase private key could not be read.');
        }
        [$status, $body] = self::http('post', $account['token_uri'] ?? 'https://oauth2.googleapis.com/token', [], [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $header . '.' . $claims . '.' . $b64($signature),
        ]);
        if ($status != 200 || empty($body['access_token'])) {
            throw new \RuntimeException('Firebase rejected the service account (' . ($body['error_description'] ?? $body['error'] ?? $status) . ').');
        }
        if (!static::$sender) {
            Cache::put($cacheKey, $body['access_token'], now()->addMinutes(50));
        }
        return $body['access_token'];
    }

    /**
     * Sends to every phone a user has the app on. Never throws. Removes tokens Firebase says are dead.
     * $data values must be strings (FCM rule); "url" tells the app which screen to open.
     */
    public static function toUser($userId, $title, $body, array $data = [], $badge = null)
    {
        $account = self::serviceAccount();
        if (!$userId || !$account) {
            return 0;
        }
        $devices = MobileDevice::where('user_id', $userId)->get();
        if ($devices->isEmpty()) {
            return 0;
        }
        try {
            $token = self::accessToken($account);
        } catch (\Throwable $e) {
            \Log::info('FCM: ' . $e->getMessage());
            return 0;
        }
        $sent = 0;
        foreach ($devices as $device) {
            $message = [
                'message' => [
                    'token' => $device->fcm_token,
                    'notification' => ['title' => mb_substr((string) $title, 0, 120), 'body' => mb_substr(strip_tags((string) $body), 0, 300)],
                    'data' => array_map('strval', $data),
                    'android' => ['priority' => 'high', 'notification' => array_filter([
                        'channel_id' => 'smart_tenant_default', 'sound' => 'default', 'notification_count' => $badge,
                    ], fn($v) => $v !== null)],
                    'apns' => ['payload' => ['aps' => array_filter(['sound' => 'default', 'badge' => $badge], fn($v) => $v !== null)]],
                ],
            ];
            try {
                [$status, $response] = self::http('post', 'https://fcm.googleapis.com/v1/projects/' . rawurlencode($account['project_id']) . '/messages:send',
                    ['Authorization' => 'Bearer ' . $token], json_encode($message, JSON_UNESCAPED_UNICODE));
                $errorCode = $response['error']['details'][0]['errorCode'] ?? ($response['error']['status'] ?? null);
                if ($status == 200) {
                    $sent++;
                } elseif ($status == 404 || in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                    $device->delete(); // app uninstalled or token replaced
                } else {
                    \Log::info('FCM send failed: ' . $status . ' ' . json_encode($response['error'] ?? $response));
                }
            } catch (\Throwable $e) {
                \Log::info('FCM send failed: ' . $e->getMessage());
            }
        }
        return $sent;
    }

    public static function toUsers($userIds, $title, $body, array $data = [])
    {
        foreach (array_unique(array_filter((array) $userIds)) as $userId) {
            self::toUser($userId, $title, $body, $data);
        }
    }
}
