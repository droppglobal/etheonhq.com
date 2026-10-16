<?php

namespace App\Support;

use App\Models\ListingAlert;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * "Email me new listings": visitors save a search on an owner's website and get an email when a new property matches.
 */
class ListingAlerts
{
    // the search filters worth keeping (sorting does not matter for alerts)
    public static function filtersFrom(Request $request)
    {
        $f = PropertySearch::filters($request);
        unset($f['sort']);
        return array_filter($f, fn($v) => $v !== '' && $v !== null);
    }

    // a short description of a saved search, e.g. "For rent · Miami · 2+ bedrooms · up to $1,500"
    public static function summary(array $f)
    {
        $parts = [];
        if (!empty($f['listing'])) {
            $parts[] = $f['listing'] === 'sell' ? __('For sale') : __('For rent');
        }
        if (!empty($f['type'])) {
            $parts[] = Property::types()[$f['type']] ?? $f['type'];
        }
        foreach (['q', 'city', 'state', 'country'] as $key) {
            if (!empty($f[$key])) {
                $parts[] = $f[$key];
            }
        }
        if (!empty($f['bedrooms'])) {
            $parts[] = __(':count+ bedrooms', ['count' => $f['bedrooms']]);
        }
        if (!empty($f['min_price'])) {
            $parts[] = __('from :price', ['price' => priceformat($f['min_price'])]);
        }
        if (!empty($f['max_price'])) {
            $parts[] = __('up to :price', ['price' => priceformat($f['max_price'])]);
        }
        if (!empty($f['available'])) {
            $parts[] = __('Only with free units');
        }
        return $parts ? implode(' · ', $parts) : __('All new properties');
    }

    // matching properties added after the alert was made, not emailed yet (ids, not times: timestamps here mix timezones)
    public static function matches(ListingAlert $alert)
    {
        $f = array_merge(PropertySearch::filters(new Request()), (array) $alert->filters, ['sort' => 'newest']);
        return PropertySearch::query($alert->parent_id, $f)
            ->where('properties.created_at', '>', $alert->created_at->copy()->subDay())
            ->whereNotIn('properties.id', (array) $alert->sent_ids ?: [0])
            ->take(10)->get();
    }

    public static function unsubscribeUrl(ListingAlert $alert, $owner)
    {
        return route('web.alerts.unsubscribe', [$owner->code, $alert->token]);
    }

    public static function sendConfirmation(ListingAlert $alert, $owner)
    {
        $settings = settingsById($owner->id);
        commonEmailSend($alert->email, [
            'module' => 'lead_notification',
            'subject' => __('Your property alert is on'),
            'message' => '<p>' . e(__('Hello :name,', ['name' => $alert->name ?: __('there')])) . '</p>'
                . '<p>' . e(__('We will email you when a new property matches your search:')) . '<br><strong>' . e(self::summary((array) $alert->filters)) . '</strong></p>'
                . '<p><a href="' . e(self::unsubscribeUrl($alert, $owner)) . '">' . e(__('Stop these emails')) . '</a></p>',
            'logo' => $settings['company_logo'] ?? '',
            'parent_id' => $owner->id,
        ]);
    }

    // daily: one email per alert that has new matches
    public static function sendDue()
    {
        $sent = 0;
        $ownerIds = ListingAlert::whereNull('unsubscribed_at')->distinct()->pluck('parent_id');
        foreach ($ownerIds as $ownerId) {
            $owner = User::where('id', $ownerId)->where('type', 'owner')->first();
            if (!$owner || empty(WebsiteSite::visitors($ownerId)['alerts'])) {
                continue;
            }
            $settings = settingsById($ownerId);
            useAccountSettings($ownerId); // prices in the owner's currency
            app()->setLocale(WebsiteSite::design($ownerId)['site_language']); // emails in the website's language
            foreach (ListingAlert::where('parent_id', $ownerId)->whereNull('unsubscribed_at')->get() as $alert) {
                $found = self::matches($alert);
                if ($found->isEmpty()) {
                    continue;
                }
                $list = $found->map(function ($p) use ($owner) {
                    $url = WebsiteSite::propertyUrl($owner, $p);
                    $place = trim(implode(', ', array_filter([$p->city, $p->state])));
                    return '<li style="margin-bottom:8px"><a href="' . e($url) . '"><strong>' . e($p->name) . '</strong></a>'
                        . ($place ? '<br>' . e($place) : '') . ($p->price ? ' · ' . e(priceformat($p->price)) : '') . '</li>';
                })->implode('');
                commonEmailSend($alert->email, [
                    'module' => 'lead_notification',
                    'subject' => __('New properties for you') . ' (' . $found->count() . ')',
                    'message' => '<p>' . e(__('New properties match your search:')) . ' <strong>' . e(self::summary((array) $alert->filters)) . '</strong></p>'
                        . '<ul>' . $list . '</ul>'
                        . '<p style="font-size:12px;color:#777"><a href="' . e(self::unsubscribeUrl($alert, $owner)) . '">' . e(__('Stop these emails')) . '</a></p>',
                    'logo' => $settings['company_logo'] ?? '',
                    'parent_id' => $ownerId,
                ]);
                $alert->update(['last_sent_at' => now(), 'sent_ids' => array_values(array_unique(array_merge((array) $alert->sent_ids, $found->pluck('id')->all())))]);
                $sent++;
            }
        }
        return $sent;
    }
}
