<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Lead;
use App\Models\ListingAlert;
use App\Models\Property;
use App\Models\User;
use App\Support\ListingAlerts;
use App\Support\PropertySearch;
use App\Support\WebsiteSite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Visitor tools on an owner's public website: saved properties (kept in the visitor's browser), comparing them,
 * and new-listing email alerts.
 */
class WebsiteVisitorController extends Controller
{
    private function owner($code)
    {
        return User::where('code', $code)->where('type', 'owner')->firstOrFail();
    }

    // "1,2,3" -> [1,2,3], at most $max, only this owner's active properties, in the given order
    private function properties($owner, $ids, $max)
    {
        $ids = collect(explode(',', (string) $ids))->map(fn($i) => (int) $i)->filter()->unique()->take($max)->values();
        if ($ids->isEmpty()) {
            return collect();
        }
        return Property::with(['thumbnail', 'propertyImages', 'totalUnits'])->where('parent_id', $owner->id)->where('is_active', 1)
            ->whereIn('id', $ids)->get()->sortBy(fn($p) => $ids->search($p->id))->values();
    }

    public function saved($code)
    {
        $user = $this->owner($code);
        $settings = settingsById($user->id);
        return view('theme.saved', compact('user', 'settings'));
    }

    // cards for the saved page (ids come from the visitor's browser)
    public function cards(Request $request, $code)
    {
        $user = $this->owner($code);
        $properties = $this->properties($user, $request->input('ids'), 60);
        return response()->json([
            'ids' => $properties->pluck('id'),
            'html' => view('theme._cards', compact('properties', 'user'))->render(),
        ]);
    }

    public function compare(Request $request, $code)
    {
        $user = $this->owner($code);
        $settings = settingsById($user->id);
        $properties = $this->properties($user, $request->input('ids'), 3);
        $featured = WebsiteSite::for($user)->featuredIds();
        $rows = $properties->map(function ($p) use ($featured, $user) {
            $amenityIds = array_filter(explode(',', (string) $p->amenities_id));
            return [
                'property' => $p,
                'facts' => PropertySearch::facts($p, $featured),
                'amenities' => $amenityIds ? Amenity::where('parent_id', $user->id)->whereIn('id', $amenityIds)->pluck('name')->all() : [],
            ];
        });
        return view('theme.compare', compact('user', 'settings', 'rows'));
    }

    // "email me new listings" from the properties page
    public function alertStore(Request $request, $code)
    {
        $owner = $this->owner($code);
        $thanks = __('Done! We will email you when a new property matches your search.');
        if (empty(WebsiteSite::visitors($owner->id)['alerts'])) {
            abort(404);
        }
        if (!empty($request->input('website'))) { // honeypot
            return redirect()->back()->with('success', $thanks);
        }
        $request->validate([
            'alert_email' => 'required|email|max:191',
            'alert_name' => 'nullable|string|max:120',
            'alert_consent' => 'accepted',
        ]);
        $email = mb_strtolower(trim($request->alert_email));
        $filters = ListingAlerts::filtersFrom($request);

        $active = ListingAlert::where('parent_id', $owner->id)->where('email', $email)->whereNull('unsubscribed_at')->get();
        $same = $active->first(fn($a) => (array) $a->filters == $filters);
        if ($same) {
            return redirect()->back()->with('success', $thanks);
        }
        if ($active->count() >= 5) {
            return redirect()->back()->with('error', __('You already have 5 alerts. Stop one from the link in our emails first.'));
        }

        $lead = Lead::create([
            'name' => strip_tags((string) $request->alert_name) ?: $email,
            'email' => $email,
            'property_id' => 0,
            'unit_id' => 0,
            'source' => 'website',
            'status' => 'new',
            'message' => __('Asked for new listing alerts') . ': ' . ListingAlerts::summary($filters),
            'last_activity_at' => now(),
            'parent_id' => $owner->id,
        ]);
        $alert = ListingAlert::create([
            'parent_id' => $owner->id,
            'lead_id' => $lead->id,
            'name' => strip_tags((string) $request->alert_name),
            'email' => $email,
            'filters' => $filters,
            'token' => Str::random(48),
            // what the visitor can already see is not news to them
            'sent_ids' => Property::where('parent_id', $owner->id)->pluck('id')->all(),
        ]);
        ListingAlerts::sendConfirmation($alert, $owner);
        return redirect()->back()->with('success', $thanks);
    }

    public function alertUnsubscribe($code, $token)
    {
        $user = $this->owner($code);
        $settings = settingsById($user->id);
        $alert = ListingAlert::where('parent_id', $user->id)->where('token', $token)->first();
        if ($alert && !$alert->unsubscribed_at) {
            $alert->update(['unsubscribed_at' => now()]);
        }
        $summary = $alert ? ListingAlerts::summary((array) $alert->filters) : '';
        return view('theme.alert_stopped', compact('user', 'settings', 'alert', 'summary'));
    }

    // sitemap.xml for search engines: home, list pages, every active property, page and blog post
    public function sitemap($code)
    {
        $user = $this->owner($code);
        $site = WebsiteSite::for($user);
        $urls = [
            ['loc' => route('web.page', $code), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('property.home', ['code' => $code]), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('blog.home', ['code' => $code]), 'priority' => '0.5', 'changefreq' => 'weekly'],
            ['loc' => route('contact.home', ['code' => $code]), 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];
        foreach (Property::where('parent_id', $user->id)->where('is_active', 1)->latest('updated_at')->get(['id', 'name', 'updated_at']) as $p) {
            $urls[] = ['loc' => WebsiteSite::propertyUrl($user, $p), 'lastmod' => optional($p->updated_at)->toDateString(), 'priority' => '0.8', 'changefreq' => 'weekly'];
        }
        foreach (\App\Models\WebsitePage::where('parent_id', $user->id)->where('is_active', 1)->get(['slug', 'updated_at']) as $page) {
            $urls[] = ['loc' => route('web.custom.page', [$code, $page->slug]), 'lastmod' => optional($page->updated_at)->toDateString(), 'priority' => '0.6', 'changefreq' => 'monthly'];
        }
        foreach (\App\Models\Blog::where('parent_id', $user->id)->where('enabled', 1)->get(['slug', 'updated_at']) as $post) {
            $urls[] = ['loc' => route('blog.detail', ['code' => $code, 'slug' => $post->slug]), 'lastmod' => optional($post->updated_at)->toDateString(), 'priority' => '0.5', 'changefreq' => 'monthly'];
        }
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= '  <url><loc>' . e($site->canonical($u['loc'])) . '</loc>'
                . (!empty($u['lastmod']) ? '<lastmod>' . $u['lastmod'] . '</lastmod>' : '')
                . '<changefreq>' . $u['changefreq'] . '</changefreq><priority>' . $u['priority'] . "</priority></url>\n";
        }
        $xml .= '</urlset>';
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
