<?php

namespace App\Http\Controllers;

use App\Models\Advantage;
use App\Models\Amenity;
use App\Models\Blog;
use App\Models\FrontHomePage;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\User;
use App\Support\PropertySearch;
use App\Support\WebsiteSections;
use App\Support\WebsiteSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class FrontendController extends Controller
{
    // ------------------------------------------------------------------ public website

    public function themePage($code = null)
    {
        $user = User::where('code', $code)->where('type', 'owner')->firstOrFail();
        $settings = settingsById($user->id);
        $parent_id = $user->id;
        $site = WebsiteSite::for($user);

        $allAmenities = Amenity::where('parent_id', $user->id)->get();
        $listingTypes = Property::where('parent_id', $user->id)->where('is_active', 1)->whereIn('listing_type', ['sell', 'rent'])
            ->distinct()->pluck('listing_type')->toArray();
        $propertiesByType = Property::where('parent_id', $user->id)->where('is_active', 1)->whereIn('listing_type', $listingTypes)
            ->latest()->get()->groupBy('listing_type');

        // sections in the owner's order, in the visitor's language
        $sections = FrontHomePage::where('parent_id', $user->id)->where('section', '!=', WebsiteSections::CHROME)
            ->orderBy('sort_order')->orderBy('id')->get()
            ->map(function ($row) use ($site) {
                $def = WebsiteSections::definitions()[$row->section] ?? null;
                return $def && $def['view'] ? ['row' => $row, 'def' => $def, 'c' => WebsiteSections::localized($row, $site->lang)] : null;
            })
            ->filter(fn($s) => $s && WebsiteSections::enabled($s['c']))->values();

        return view('theme.index', compact('settings', 'parent_id', 'user', 'allAmenities', 'listingTypes', 'propertiesByType', 'sections', 'site'));
    }

    // visitor picks a language on the owner's website
    public function setLanguage($code, $lang)
    {
        $user = User::where('code', $code)->where('type', 'owner')->firstOrFail();
        if (array_key_exists($lang, WebsiteSite::for($user)->languages)) {
            session(['website_lang_' . $code => $lang]);
        }
        return redirect()->back();
    }

    public function searchLocation(Request $request, $code)
    {
        $locationSlug = $request->input('location');

        if (!$locationSlug) {
            return redirect()->back()->with('error', 'Location not selected.');
        }

        return redirect()->route('location.home', ['code' => $code]) . '?location=' . $locationSlug;
    }

    // ------------------------------------------------------------------ Frontend Manager (owner)

    private function ownerSections()
    {
        WebsiteSections::ensure(parentId());
        return FrontHomePage::where('parent_id', parentId())->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn($r) => isset(WebsiteSections::definitions()[$r->section]))->values();
    }

    public function index(Request $request)
    {
        if (!Auth::user()->can('manage front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $loginUser = Auth::user();
        $sections = $this->ownerSections();
        $languages = WebsiteSections::languages();
        $contentLang = $this->contentLang($request);
        $design = WebsiteSite::design(parentId());
        $properties = Property::where('parent_id', parentId())->orderBy('name')->pluck('name', 'id');
        $websiteUrl = route('web.page', $loginUser->code);
        $visitors = WebsiteSite::visitors(parentId());
        $seo = WebsiteSite::seo(parentId());
        $domainAllowed = \App\Support\WebsiteDomains::allowed(User::find(parentId()));
        $pendingReviews = \App\Models\WebsiteReview::where('parent_id', parentId())->where('status', 'pending')->count();
        $alertCount = \App\Models\ListingAlert::where('parent_id', parentId())->whereNull('unsubscribed_at')->count();
        return view('front-home.index', compact('loginUser', 'sections', 'languages', 'contentLang', 'design', 'properties', 'websiteUrl', 'visitors', 'pendingReviews', 'alertCount', 'seo', 'domainAllowed'));
    }

    // '' = the default text; otherwise the language whose translation is being edited
    private function contentLang(Request $request)
    {
        $lang = (string) $request->input('content_lang', '');
        return array_key_exists($lang, WebsiteSections::languages()) && $lang !== WebsiteSite::design(parentId())['site_language'] ? $lang : '';
    }

    public function update(Request $request, FrontHomePage $homePage, $id)
    {
        if (!Auth::user()->can('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        // only this owner's sections (the old form saved any id)
        $row = FrontHomePage::where('parent_id', parentId())->where('id', $id)->firstOrFail();
        $def = WebsiteSections::definitions()[$row->section] ?? null;
        if (!$def) {
            return redirect()->back()->with('error', __('Not found.'));
        }
        $in = (array) $request->input('content_value', []);
        $lang = $this->contentLang($request);

        if ($lang) {
            $this->saveTranslation($row, $lang, $in);
        } else {
            $request->validate([
                'content_value.*' => 'nullable',
                'content_value.items.*.image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
            ]);
            $old = WebsiteSections::decode($row);
            $new = $old;
            $new['name'] = mb_substr(trim((string) ($in['name'] ?? $def['title'])), 0, 60) ?: $def['title'];
            if ($row->section !== WebsiteSections::CHROME) {
                $new['section_enabled'] = ($in['section_enabled'] ?? 'deactive') === 'active' ? 'active' : 'deactive';
            }
            foreach ($def['fields'] as $field) {
                $new = $this->applyField($request, $field, $in, $new, 'content_value.' . $field['key']);
            }
            if (!empty($def['items'])) {
                $new['items'] = $this->collectItems($request, $def['items']['fields'], (array) ($in['items'] ?? []));
            }
            $row->content_value = json_encode($new, JSON_UNESCAPED_UNICODE);
            $row->save();
        }
        return redirect()->route('front-home.index', array_filter(['content_lang' => $lang, 'section' => $row->id]))
            ->with('success', __('Website section saved.'));
    }

    private function applyField(Request $request, array $field, array $in, array $out, $fileKey)
    {
        $key = $field['key'];
        $value = $in[$key] ?? null;
        switch ($field['type']) {
            case 'image':
                if ($request->hasFile($fileKey)) {
                    $request->validate([$fileKey => 'file|mimes:jpg,jpeg,png,webp,gif,svg|max:5120']);
                    $out[$key . '_path'] = $this->storeImage($request->file($fileKey));
                } elseif (!empty($in[$key . '_remove'])) {
                    $out[$key . '_path'] = '';
                }
                unset($out[$key]);
                break;
            case 'toggle':
                $out[$key] = $value === 'active' ? 'active' : 'deactive';
                break;
            case 'number':
                $out[$key] = max(0, min(1000, (int) $value));
                break;
            case 'select':
                $out[$key] = array_key_exists((string) $value, $field['options']) ? (string) $value : array_key_first($field['options']);
                break;
            case 'properties':
                $ids = array_map('intval', (array) $value);
                $out[$key] = Property::where('parent_id', parentId())->whereIn('id', $ids)->pluck('id')->map(fn($i) => (int) $i)->values()->all();
                break;
            case 'link':
                $out[$key] = $this->cleanLink($value);
                break;
            case 'page':
                $out[$key] = (int) \App\Models\WebsitePage::where('parent_id', parentId())->where('id', (int) $value)->value('id');
                break;
            default: // text, textarea
                $out[$key] = mb_substr(trim((string) $value), 0, $field['type'] === 'textarea' ? 3000 : 300);
        }
        return $out;
    }

    // repeatable boxes, in the order they were posted (the editor lets owners drag them)
    private function collectItems(Request $request, array $fields, array $posted)
    {
        $items = [];
        foreach ($posted as $index => $item) {
            $item = (array) $item;
            $new = ['id' => preg_match('/^[a-z0-9]{6,20}$/', (string) ($item['id'] ?? '')) ? $item['id'] : substr(md5(uniqid('', true)), 0, 10)];
            $hasContent = false;
            foreach ($fields as $field) {
                $key = $field['key'];
                if ($field['type'] === 'image') {
                    $fileKey = "content_value.items.$index.$key";
                    if ($request->hasFile($fileKey)) {
                        $new[$key . '_path'] = $this->storeImage($request->file($fileKey));
                    } else {
                        // keep the earlier upload (only paths inside our upload folder)
                        $path = (string) ($item[$key . '_path'] ?? '');
                        $new[$key . '_path'] = preg_match('#^upload/[A-Za-z0-9_\-./]+$#', $path) && strpos($path, '..') === false ? $path : '';
                    }
                    $hasContent = $hasContent || $new[$key . '_path'] !== '';
                    continue;
                }
                $new = $this->applyField($request, $field, $item, $new, "content_value.items.$index.$key");
                if (in_array($field['type'], ['text', 'textarea', 'link']) && $new[$key] !== '') {
                    $hasContent = true;
                }
            }
            if ($hasContent) {
                $items[] = $new;
            }
        }
        return array_slice($items, 0, 60);
    }

    private function saveTranslation(FrontHomePage $row, $lang, array $in)
    {
        $keys = WebsiteSections::translatable($row->section);
        $all = json_decode((string) $row->translations, true) ?: [];
        $t = [];
        foreach ($keys['fields'] as $key) {
            $t[$key] = mb_substr(trim((string) ($in[$key] ?? '')), 0, 3000);
        }
        if ($keys['items']) {
            $t['items'] = [];
            foreach ((array) ($in['items'] ?? []) as $itemId => $fields) {
                foreach ($keys['items'] as $key) {
                    $t['items'][(string) $itemId][$key] = mb_substr(trim((string) ($fields[$key] ?? '')), 0, 3000);
                }
            }
        }
        $all[$lang] = $t;
        $row->translations = json_encode($all, JSON_UNESCAPED_UNICODE);
        $row->save();
    }

    private function storeImage($file)
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = mb_substr($name, 0, 40) . '_' . date('YmdHis') . '_' . substr(md5(uniqid('', true)), 0, 6) . '.' . strtolower($file->getClientOriginalExtension());
        $file->storeAs('upload/fronthomepage/', $fileName);
        return 'upload/fronthomepage/' . $fileName;
    }

    // web links, mail/phone links and on-site paths; anything else (javascript: ...) is dropped
    private function cleanLink($value)
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '#') {
            return $value;
        }
        if (preg_match('#^(https?://|mailto:|tel:|/)#i', $value)) {
            return mb_substr($value, 0, 500);
        }
        if (preg_match('#^[a-z0-9.-]+\.[a-z]{2,}(/.*)?$#i', $value)) {
            return 'https://' . mb_substr($value, 0, 490); // "www.example.com" typed without https
        }
        return '';
    }

    // drag & drop order of the sections
    public function order(Request $request)
    {
        if (!Auth::user()->can('edit front home page')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
        foreach (array_values((array) $request->input('ids', [])) as $position => $id) {
            FrontHomePage::where('parent_id', parentId())->where('id', (int) $id)->where('section', '!=', WebsiteSections::CHROME)
                ->update(['sort_order' => ($position + 1) * 10]);
        }
        return response()->json(['ok' => true]);
    }

    // on/off switch in the section list
    public function toggle(Request $request, $id)
    {
        if (!Auth::user()->can('edit front home page')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
        $row = FrontHomePage::where('parent_id', parentId())->where('id', $id)->firstOrFail();
        $c = WebsiteSections::decode($row);
        $c['section_enabled'] = $request->boolean('enabled') ? 'active' : 'deactive';
        $row->content_value = json_encode($c, JSON_UNESCAPED_UNICODE);
        $row->save();
        return response()->json(['ok' => true, 'enabled' => $c['section_enabled'] === 'active']);
    }

    // colours, font, button and layout style, languages
    public function design(Request $request)
    {
        if (!Auth::user()->can('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $languages = WebsiteSections::languages();
        $request->validate([
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'secondary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'font' => 'required|in:' . implode(',', array_keys(WebsiteSite::fonts())),
            'button_style' => 'required|in:rounded,pill,square',
            'layout_style' => 'required|in:classic,modern,minimal',
            'color_mode' => 'required|in:light,dark,auto',
            'mode_switch' => 'nullable|in:0,1',
            'site_language' => 'required|in:' . implode(',', array_keys($languages)),
            'site_languages' => 'nullable|array',
        ]);
        $value = $request->only(['primary_color', 'secondary_color', 'font', 'button_style', 'layout_style', 'color_mode', 'site_language']);
        $value['mode_switch'] = $request->boolean('mode_switch') ? 1 : 0;
        $value['site_languages'] = array_values(array_unique(array_merge([$request->site_language],
            array_intersect((array) $request->site_languages, array_keys($languages)))));
        DB::table('settings')->updateOrInsert(['name' => 'website_design', 'parent_id' => parentId()], ['value' => json_encode($value), 'type' => 'website']);
        return redirect()->route('front-home.index', ['tab' => 'design'])->with('success', __('Website design saved.'));
    }

    // viewing booking hours, listing alerts and tenant reviews
    public function visitors(Request $request)
    {
        if (!Auth::user()->can('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $request->validate([
            'viewing_days' => 'nullable|array',
            'viewing_days.*' => 'integer|between:1,7',
            'viewing_from' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'viewing_to' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/', 'after:viewing_from'],
            'viewing_slot' => 'required|in:15,20,30,45,60,90,120',
            'viewing_ahead' => 'required|integer|between:1,60',
            'viewing_per_slot' => 'required|integer|between:1,20',
        ]);
        $value = [
            'viewings' => $request->boolean('viewings') ? 1 : 0,
            'viewing_days' => array_values(array_unique(array_map('intval', (array) $request->viewing_days))),
            'viewing_from' => $request->viewing_from,
            'viewing_to' => $request->viewing_to,
            'viewing_slot' => (int) $request->viewing_slot,
            'viewing_ahead' => (int) $request->viewing_ahead,
            'viewing_per_slot' => (int) $request->viewing_per_slot,
            'alerts' => $request->boolean('alerts') ? 1 : 0,
            'reviews' => $request->boolean('reviews') ? 1 : 0,
        ];
        DB::table('settings')->updateOrInsert(['name' => 'website_visitors', 'parent_id' => parentId()], ['value' => json_encode($value), 'type' => 'website']);
        return redirect()->route('front-home.index', ['tab' => 'visitors'])->with('success', __('Visitor settings saved.'));
    }

    // search engines, link previews, analytics and the owner's own domain
    public function seo(Request $request)
    {
        if (!Auth::user()->can('edit front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $request->validate([
            'seo_title' => 'nullable|string|max:70',
            'seo_description' => 'nullable|string|max:320',
            'seo_keywords' => 'nullable|string|max:255',
            'share_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
            'google_verification' => 'nullable|string|max:255',
            'ga_id' => ['nullable', 'regex:/^(G|GT|AW|UA)-[A-Z0-9-]{4,20}$/i'],
            'pixel_id' => ['nullable', 'regex:/^\d{6,20}$/'],
            'domain' => 'nullable|string|max:253',
        ], [
            'ga_id.regex' => __('The Google Analytics ID looks like G-ABC123XYZ.'),
            'pixel_id.regex' => __('The Meta Pixel ID is a number.'),
        ]);
        $old = WebsiteSite::seo(parentId());
        // a pasted <meta name="google-site-verification" content="..."> tag is reduced to its code
        $verification = trim((string) $request->google_verification);
        if (preg_match('/content=["\']([^"\']+)["\']/', $verification, $m)) {
            $verification = $m[1];
        }
        $value = [
            'title' => strip_tags((string) $request->seo_title),
            'description' => strip_tags((string) $request->seo_description),
            'keywords' => strip_tags((string) $request->seo_keywords),
            'share_image' => $old['share_image'],
            'google_verification' => preg_replace('/[^A-Za-z0-9_\-]/', '', $verification),
            'ga_id' => strtoupper(trim((string) $request->ga_id)),
            'pixel_id' => trim((string) $request->pixel_id),
            'domain' => $old['domain'],
            'domain_redirect' => $old['domain_redirect'],
        ];
        if ($request->hasFile('share_image')) {
            $value['share_image'] = $this->storeImage($request->file('share_image'));
        } elseif ($request->boolean('share_image_remove')) {
            $value['share_image'] = '';
        }
        if (\App\Support\WebsiteDomains::allowed(Auth::user()->type === 'owner' ? Auth::user() : User::find(parentId()))) {
            $domain = \App\Support\WebsiteDomains::clean($request->domain);
            if ($request->filled('domain') && !$domain) {
                return redirect()->back()->withInput()->with('error', __('Please enter a domain name like www.example.com.'));
            }
            $taken = $domain && DB::table('settings')->where('name', 'website_seo')->where('parent_id', '!=', parentId())->where('value', 'like', '%"domain":"' . $domain . '"%')->exists();
            if ($taken) {
                return redirect()->back()->withInput()->with('error', __('This domain is already used by another website.'));
            }
            $value['domain'] = $domain;
            $value['domain_redirect'] = $domain && $request->boolean('domain_redirect') ? 1 : 0;
        }
        DB::table('settings')->updateOrInsert(['name' => 'website_seo', 'parent_id' => parentId()], ['value' => json_encode($value), 'type' => 'website']);
        \App\Support\WebsiteDomains::forget();
        return redirect()->route('front-home.index', ['tab' => 'seo'])->with('success', __('SEO settings saved.'));
    }

    // views and enquiries per property
    public function stats(Request $request)
    {
        if (!Auth::user()->can('manage front home page')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        $days = array_key_exists((int) $request->days, \App\Support\WebsiteStats::periods()) ? (int) $request->days : 30;
        $report = \App\Support\WebsiteStats::report(parentId(), $days);
        $owner = User::find(parentId());
        return view('website_stats.index', compact('report', 'days', 'owner'));
    }

    // is the owner's domain pointing at this server yet?
    public function domainCheck()
    {
        if (!Auth::user()->can('manage front home page')) {
            return response()->json(['error' => __('Permission Denied.')], 403);
        }
        $domain = WebsiteSite::seo(parentId())['domain'];
        return response()->json($domain ? \App\Support\WebsiteDomains::check($domain) + ['domain' => $domain] : ['ok' => false, 'domain' => '']);
    }

    // ------------------------------------------------------------------ other public pages

    public function blogPage(Request $request, $code)
    {
        $user = User::where('code', $code)->firstOrFail();
        $settings = settingsById($user->id);
        $blogs = Blog::where('parent_id', $user->id)->where('enabled', 1)->latest()->paginate(8);
        if ($request->ajax()) {
            return view('theme.blogbox', compact('blogs', 'settings', 'user'))->render();
        }

        return view('theme.blog', compact('blogs', 'settings', 'user'));
    }

    public function blogDetailPage($code, $slug)
    {
        $user = User::where('code', $code)->firstOrFail();
        $settings = settingsById($user->id);
        $blog = Blog::where('parent_id', $user->id)->where('enabled', 1)->where('slug', $slug)->firstOrFail();
        $recent = Blog::where('parent_id', $user->id)->where('enabled', 1)->where('id', '!=', $blog->id)->latest()->take(3)->get();

        return view('theme.blog-detail', compact('blog', 'settings', 'user', 'recent'));
    }

    public function propertyPage(Request $request, $code)
    {
        $user = User::where('code', $code)->where('type', 'owner')->firstOrFail();
        $settings = settingsById($user->id);
        $f = PropertySearch::filters($request);
        $properties = PropertySearch::query($user->id, $f)->paginate(PropertySearch::PER_PAGE);

        if ($request->ajax()) {
            return view('theme.propertybox', compact('properties', 'user', 'settings'))->render();
        }
        $options = PropertySearch::options($user->id);
        return view('theme.property', compact('properties', 'settings', 'user', 'f', 'options'));
    }

    public function detailPage($code, $id)
    {
        // clean address "12-sunny-garden-flat"; older encrypted links still work
        if (preg_match('/^(\d+)(?:-[a-z0-9-]*)?$/', $id, $m)) {
            $ids = (int) $m[1];
        } else {
            try {
                $ids = (int) Crypt::decrypt($id);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                abort(404);
            }
        }
        $user = User::where('code', $code)->where('type', 'owner')->firstOrFail();
        // only this owner's active properties are shown on this owner's site
        $property = Property::with(['thumbnail', 'propertyImages'])->where('parent_id', $user->id)->where('is_active', 1)->where('id', $ids)->firstOrFail();
        if ($id !== $property->id . '-' . (\Illuminate\Support\Str::slug($property->name) ?: 'property')) {
            $query = request()->getQueryString();
            return redirect()->to(WebsiteSite::propertyUrl($user, $property) . ($query ? '?' . $query : ''), 301);
        }
        \App\Support\WebsiteStats::countView($property);
        $units = PropertyUnit::where('property_id', $property->id)->orderBy('id', 'desc')->get();
        $property->setRelation('totalUnits', $units);

        $settings = settingsById($user->id);

        $selectedAmenities = collect();
        if (!empty($property->amenities_id)) {
            $ids = array_filter(explode(',', $property->amenities_id));
            $selectedAmenities = Amenity::where('parent_id', $user->id)->whereIn('id', $ids)->get();
        }

        $selectedAdvantages = collect();
        if (!empty($property->advantage_id)) {
            $ids = array_filter(explode(',', $property->advantage_id));
            $selectedAdvantages = Advantage::where('parent_id', $user->id)->whereIn('id', $ids)->get();
        }

        // similar: same listing type first, then the newest others
        $similar = Property::with(['thumbnail', 'propertyImages', 'totalUnits'])->where('parent_id', $user->id)->where('is_active', 1)
            ->where('id', '!=', $property->id)
            ->orderByRaw('listing_type = ? desc', [$property->listing_type])->orderByRaw('city = ? desc', [(string) $property->city])
            ->latest()->take(3)->get();

        return view('theme.detail', compact('code', 'property', 'user', 'settings', 'selectedAmenities', 'selectedAdvantages', 'units', 'similar'));
    }

    public function contactPage(Request $request, $code)
    {
        $user = User::where('code', $code)->firstOrFail();
        $settings = settingsById($user->id);
        return view('theme.contact', compact('settings', 'user'));
    }

    // location lists for one owner's search form (?code=...)
    public function getStates(Request $request)
    {
        $owner = User::where('code', (string) $request->input('code'))->where('type', 'owner')->value('id');
        $states = $owner ? Property::where('parent_id', $owner)->where('is_active', 1)->where('country', $request->country)->distinct()->pluck('state') : [];
        return response()->json($states);
    }

    public function getCities(Request $request)
    {
        $owner = User::where('code', (string) $request->input('code'))->where('type', 'owner')->value('id');
        $cities = $owner ? Property::where('parent_id', $owner)->where('is_active', 1)->where('state', $request->state)->distinct()->pluck('city') : [];
        return response()->json($cities);
    }

    // old search URL; same page as the property list
    public function search(Request $request, $code)
    {
        return $this->propertyPage($request, $code);
    }
}
