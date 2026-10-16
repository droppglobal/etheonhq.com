<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Support\WebPush;
use Illuminate\Http\Request;

/**
 * Lets any signed-in user switch push notifications on or off for the current browser / phone.
 */
class PushSubscriptionController extends Controller
{
    public function key()
    {
        if (!WebPush::supported()) {
            return response()->json(['error' => __('Push notifications are not available on this server.')], 422);
        }
        return response()->json(['key' => WebPush::publicKey()]);
    }

    public function store(Request $request)
    {
        $endpoint = (string) $request->input('endpoint');
        $p256dh = (string) $request->input('keys.p256dh');
        $auth = (string) $request->input('keys.auth');

        // only real push services over https, with keys of the right size
        if (!preg_match('#^https://[^\s]+$#', $endpoint) || strlen($endpoint) > 1000
            || strlen(WebPush::unb64($p256dh)) !== 65 || strlen(WebPush::unb64($auth)) !== 16) {
            return response()->json(['error' => __('This browser sent an invalid subscription.')], 422);
        }

        PushSubscription::updateOrCreate(['endpoint_hash' => hash('sha256', $endpoint)], [
            'user_id' => \Auth::id(),
            'parent_id' => parentId(),
            'endpoint' => $endpoint,
            'public_key' => $p256dh,
            'auth_token' => $auth,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 250),
        ]);
        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request)
    {
        PushSubscription::where('user_id', \Auth::id())
            ->where('endpoint_hash', hash('sha256', (string) $request->input('endpoint')))
            ->delete();
        return response()->json(['ok' => true]);
    }

    public function test()
    {
        $sent = WebPush::toUser(\Auth::id(), __('Notifications are on'), __('You will get updates about rent, repairs and visitors here.'), route('dashboard'));
        return response()->json(['sent' => $sent]);
    }
}
