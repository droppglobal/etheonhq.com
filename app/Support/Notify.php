<?php

namespace App\Support;

/**
 * Push notifications for app events. Sending happens after the response is sent,
 * so a slow push service never delays the page the user is waiting for.
 */
class Notify
{
    /** $screen: the app screen to open; worked out from $url when not given. */
    public static function push($userIds, $title, $body, $url = null, $screen = null)
    {
        $userIds = array_values(array_unique(array_filter((array) $userIds)));
        if (!$userIds) {
            return;
        }
        $screen = $screen ?: self::screenFor($url);
        // every notification is also kept in the user's in-app inbox
        $saved = [];
        foreach ($userIds as $userId) {
            try {
                $saved[$userId] = \App\Models\AppNotification::create([
                    'user_id' => $userId,
                    'title' => mb_substr(strip_tags((string) $title), 0, 190),
                    'body' => mb_substr(strip_tags((string) $body), 0, 500),
                    'screen' => $screen,
                    'url' => mb_substr((string) $url, 0, 500),
                ])->id;
            } catch (\Throwable $e) {
                $saved[$userId] = null; // inbox table missing before the update has run
            }
        }
        $send = function () use ($userIds, $title, $body, $url, $screen, $saved) {
            WebPush::toUsers($userIds, $title, $body, $url);
            // the mobile app opens the matching screen from the "screen" hint and shows the unread count on its icon
            foreach ($userIds as $userId) {
                FcmPush::toUser($userId, $title, $body, ['url' => (string) $url, 'screen' => $screen, 'notification_id' => (string) ($saved[$userId] ?? '')],
                    \App\Models\AppNotification::where('user_id', $userId)->whereNull('read_at')->count());
            }
        };
        app()->runningInConsole() ? $send() : app()->terminating($send);
    }

    // owner of the account plus staff who can manage maintenance (managers)
    public static function staffOf($parentId, $permission = null)
    {
        $users = \App\Models\User::where(function ($query) use ($parentId) {
            $query->where('id', $parentId)->orWhere(function ($q) use ($parentId) {
                $q->where('parent_id', $parentId)->whereNotIn('type', ['tenant', 'maintainer', 'landlord']);
            });
        })->where('is_active', 1)->get();
        if ($permission) {
            $users = $users->filter(fn($user) => $user->type == 'owner' || $user->can($permission));
        }
        return $users->pluck('id')->all();
    }

    // which app screen a web link belongs to
    public static function screenFor($url)
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        foreach (['/pay/' => 'invoices', '/invoice' => 'invoices', '/maintenance-request' => 'repairs', '/assistant' => 'chat',
                  '/visitor' => 'visitors', '/facility' => 'bookings', '/poll' => 'polls', '/note' => 'notices', '/agreement' => 'agreements',
                  '/inspection' => 'inspections'] as $part => $screen) {
            if (str_contains($path, $part)) {
                return $screen;
            }
        }
        return 'home';
    }
}
