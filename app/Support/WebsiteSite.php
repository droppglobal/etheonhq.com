<?php

namespace App\Support;

use App\Models\FrontHomePage;
use App\Models\WebsitePage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Everything the public website layout needs for one owner: design choices, visitor language, header/footer
 * content, menu pages and the owner's own logo. Built once per request (WebsiteSite::for($user)).
 */
class WebsiteSite
{
    public $user;
    public $design;
    public $lang;
    public $languages;
    public $chrome;
    public $menuPages;
    public $logo;
    public $favicon;
    public $companyName;
    public $siteName; // company name, else the application name
    public $seo;
    public $meta = []; // set by a page: title, description, image, type (see setMeta)

    private static $cache = [];

    public static function for($user)
    {
        if (isset(self::$cache[$user->id])) {
            return self::$cache[$user->id];
        }
        $site = new self();
        $site->user = $user;
        $site->design = self::design($user->id);
        $all = WebsiteSections::languages();
        $site->languages = collect($site->design['site_languages'])->filter(fn($l) => isset($all[$l]))->mapWithKeys(fn($l) => [$l => self::languageName($l)])->all();
        $chosen = session('website_lang_' . $user->code);
        $site->lang = isset($site->languages[$chosen]) ? $chosen : $site->design['site_language'];
        app()->setLocale($site->lang); // labels on the public pages follow the visitor's language

        $chromeRow = FrontHomePage::where('parent_id', $user->id)->where('section', WebsiteSections::CHROME)->first();
        $site->chrome = $chromeRow ? WebsiteSections::localized($chromeRow, $site->lang) : WebsiteSections::normalize(WebsiteSections::CHROME, []);
        $site->menuPages = WebsitePage::where('parent_id', $user->id)->where('is_active', 1)->where('show_in_menu', 1)
            ->orderBy('sort_order')->orderBy('id')->get()->map(fn($p) => ['slug' => $p->slug, 'title' => $p->localized($site->lang)['title']]);

        $settings = settingsById($user->id);
        $site->logo = asset(Storage::url('upload/logo/')) . '/' . ($settings['company_logo'] ?: ($settings['logo'] ?? '') ?: 'logo.png');
        $favicon = $settings['company_favicon'] ?? ($settings['favicon'] ?? '');
        $site->favicon = $favicon ? asset(Storage::url('upload/logo/')) . '/' . $favicon : asset('favicon.png');
        $site->companyName = $settings['company_name'] ?? '';
        $site->siteName = $site->companyName ?: (!empty($settings['app_name']) ? $settings['app_name'] : config('app.name'));
        $site->seo = self::seo($user->id);
        return self::$cache[$user->id] = $site;
    }

    // properties the owner hand-picked in the Featured Properties section (they get a "Featured" tag)
    private $featuredIds;

    public function featuredIds()
    {
        if ($this->featuredIds === null) {
            $row = FrontHomePage::where('parent_id', $this->user->id)->where('section', 'Section 9')->first();
            $c = $row ? WebsiteSections::decode($row) : [];
            $this->featuredIds = $row && WebsiteSections::enabled($c) && ($c['mode'] ?? '') === 'selected'
                ? array_map('intval', (array) ($c['property_ids'] ?? [])) : [];
        }
        return $this->featuredIds;
    }

    // the owner's search engine, sharing and analytics settings
    public static function seo($ownerId)
    {
        $saved = json_decode((string) DB::table('settings')->where('parent_id', $ownerId)->where('name', 'website_seo')->value('value'), true) ?: [];
        return $saved + [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'share_image' => '',
            'google_verification' => '',
            'ga_id' => '',
            'pixel_id' => '',
            'domain' => '',
            'domain_redirect' => 0,
        ];
    }

    // a page describes itself for search engines and link previews
    public function setMeta(array $meta)
    {
        $this->meta = array_filter($meta, fn($v) => $v !== null && $v !== '') + $this->meta;
        return '';
    }

    public function metaDescription()
    {
        $text = ($this->meta['description'] ?? '') ?: ($this->seo['description'] ?: ($this->chrome['Sec8_info'] ?? ''));
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode((string) $text)))), 160, '…');
    }

    public function shareImage()
    {
        if (!empty($this->meta['image'])) {
            return self::safeUrl($this->meta['image']);
        }
        return self::safeUrl($this->seo['share_image'] ? asset(Storage::url($this->seo['share_image'])) : $this->logo);
    }

    // uploaded file names can contain spaces and brackets, which break link previews
    public static function safeUrl($url)
    {
        return str_replace([' ', '(', ')', '"', "'"], ['%20', '%28', '%29', '%22', '%27'], (string) $url);
    }

    // the address search engines should use for this page (the owner's own domain when it is set up)
    public function canonical($url = null)
    {
        $url = strtok($url ?: url()->current(), '?');
        $domain = $this->activeDomain();
        if ($domain) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $prefix = parse_url(route('web.page', $this->user->code), PHP_URL_PATH);
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix)) ?: '/';
            }
            return 'https://' . $domain . $path;
        }
        return $url;
    }

    // custom domain, only when the owner's plan allows it
    public function activeDomain()
    {
        return $this->seo['domain'] && WebsiteDomains::allowed($this->user) ? $this->seo['domain'] : '';
    }

    // a page of the main app (login, tenant pages); on an owner's own domain route() would point at that domain
    public static function appUrl($name, $params = [])
    {
        return rtrim(config('app.url'), '/') . route($name, $params, false);
    }

    // clean property address: /property/12-sunny-garden-flat
    public static function propertyUrl($user, $property)
    {
        return route('property.detail', ['code' => $user->code, 'id' => $property->id . '-' . (Str::slug($property->name) ?: 'property')]);
    }

    // visitor features the owner can turn on: viewing booking hours, listing alerts, tenant reviews
    public static function visitors($ownerId)
    {
        $saved = json_decode((string) DB::table('settings')->where('parent_id', $ownerId)->where('name', 'website_visitors')->value('value'), true) ?: [];
        return $saved + [
            'viewings' => 0,
            'viewing_days' => [1, 2, 3, 4, 5], // 1 = Monday ... 7 = Sunday
            'viewing_from' => '09:00',
            'viewing_to' => '17:00',
            'viewing_slot' => 30,
            'viewing_ahead' => 14,
            'viewing_per_slot' => 1,
            'alerts' => 1,
            'reviews' => 1,
        ];
    }

    public static function design($ownerId)
    {
        $saved = json_decode((string) DB::table('settings')->where('parent_id', $ownerId)->where('name', 'website_design')->value('value'), true) ?: [];
        $defaultLang = DB::table('users')->where('id', $ownerId)->value('lang') ?: 'english';
        $d = $saved + [
            'primary_color' => '#0a2342',
            'secondary_color' => '#2ca58d',
            'font' => 'default',
            'button_style' => 'rounded',
            'layout_style' => 'classic',
            // before v3.7 the site followed the owner's admin theme mode
            'color_mode' => (DB::table('settings')->where('parent_id', $ownerId)->where('name', 'theme_mode')->value('value') === 'dark') ? 'dark' : 'light',
            'mode_switch' => 0,
            'site_language' => $defaultLang,
            'site_languages' => [],
        ];
        $d['site_languages'] = array_values(array_unique(array_merge([$d['site_language']], (array) $d['site_languages'])));
        return $d;
    }

    // font choice => [name shown, CSS family, Google Fonts family or null for the theme's own]
    public static function fonts()
    {
        return [
            'default' => ['Theme default', null, null],
            'inter' => ['Inter', "'Inter', sans-serif", 'Inter:wght@400;500;600;700'],
            'poppins' => ['Poppins', "'Poppins', sans-serif", 'Poppins:wght@400;500;600;700'],
            'roboto' => ['Roboto', "'Roboto', sans-serif", 'Roboto:wght@400;500;700'],
            'lato' => ['Lato', "'Lato', sans-serif", 'Lato:wght@400;700'],
            'montserrat' => ['Montserrat', "'Montserrat', sans-serif", 'Montserrat:wght@400;500;600;700'],
            'playfair' => ['Playfair Display (elegant)', "'Playfair Display', serif", 'Playfair+Display:wght@400;600;700'],
            'cairo' => ['Cairo (good for Arabic)', "'Cairo', sans-serif", 'Cairo:wght@400;600;700'],
            'noto_jp' => ['Noto Sans JP (good for Japanese)', "'Noto Sans JP', sans-serif", 'Noto+Sans+JP:wght@400;500;700'],
        ];
    }

    public static function languageName($lang)
    {
        $names = ['arabic' => 'العربية', 'danish' => 'Dansk', 'dutch' => 'Nederlands', 'english' => 'English', 'french' => 'Français',
            'german' => 'Deutsch', 'italian' => 'Italiano', 'japanese' => '日本語', 'polish' => 'Polski', 'portuguese' => 'Português',
            'russian' => 'Русский', 'spanish' => 'Español'];
        return $names[$lang] ?? ucfirst($lang);
    }

    public function rtl()
    {
        return in_array($this->lang, ['arabic', 'hebrew', 'farsi', 'urdu']);
    }

    // "#0a2342" => "10, 35, 66" for the theme's rgb(var(--bs-primary-rgb)) colours
    public static function rgb($hex)
    {
        $hex = ltrim((string) $hex, '#');
        return strlen($hex) === 6 ? implode(', ', array_map('hexdec', str_split($hex, 2))) : '10, 35, 66';
    }

    public function menuEnabled($key)
    {
        return ($this->chrome[$key] ?? 'active') === 'active';
    }

    /**
     * Rich text written by owners, made safe to show to visitors: only formatting tags and attributes are kept;
     * scripts, event handlers and javascript: links are removed.
     */
    public static function cleanHtml($html)
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }
        $allowed = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'a', 'img',
            'blockquote', 'figure', 'figcaption', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'span', 'div', 'hr', 'pre', 'code', 'iframe'];
        $attributes = ['href', 'src', 'alt', 'title', 'width', 'height', 'colspan', 'rowspan', 'target', 'rel', 'class', 'style', 'allowfullscreen', 'frameborder'];
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $walk = function (\DOMNode $node) use (&$walk, $allowed, $attributes) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                if ($child instanceof \DOMElement) {
                    $tag = strtolower($child->tagName);
                    if (!in_array($tag, $allowed)) {
                        if (in_array($tag, ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select'])) {
                            $node->removeChild($child);
                        } else { // unknown wrapper: keep its text
                            $walk($child);
                            while ($child->firstChild) {
                                $node->insertBefore($child->firstChild, $child);
                            }
                            $node->removeChild($child);
                        }
                        continue;
                    }
                    foreach (iterator_to_array($child->attributes) as $attr) {
                        $name = strtolower($attr->name);
                        $value = trim($attr->value);
                        $badUrl = in_array($name, ['href', 'src']) && !preg_match('#^(https?://|mailto:|tel:|/|\#|data:image/)#i', $value);
                        $badStyle = $name === 'style' && preg_match('/expression|url\s*\(|javascript/i', $value);
                        $badFrame = $tag === 'iframe' && $name === 'src' && !preg_match('#^https://(www\.)?(youtube\.com|youtube-nocookie\.com|player\.vimeo\.com|maps\.google\.com|www\.google\.com/maps)/#i', $value);
                        if (!in_array($name, $attributes) || $badUrl || $badStyle || $badFrame) {
                            $child->removeAttribute($attr->name);
                        }
                    }
                    if ($tag === 'iframe' && !$child->hasAttribute('src')) {
                        $node->removeChild($child);
                        continue;
                    }
                    if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                        $child->setAttribute('rel', 'noopener noreferrer');
                    }
                    $walk($child);
                } elseif ($child instanceof \DOMComment) {
                    $node->removeChild($child);
                }
            }
        };
        $root = $doc->getElementById('__root');
        $walk($root);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return $out;
    }

    // "https://www.youtube.com/watch?v=abc" / "https://youtu.be/abc" / "https://vimeo.com/123" => embeddable URL
    public static function videoEmbed($url)
    {
        $url = trim((string) $url);
        if (preg_match('#(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,15})#', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1];
        }
        if (preg_match('#vimeo\.com/(?:video/)?(\d{5,12})#', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        return null;
    }

    // prices on the public website: currency symbol and thousands separators ($725,000), cents only when there are any
    public static function price($amount)
    {
        $amount = (float) $amount;
        return (settings()['CURRENCY_SYMBOL'] ?? '') . number_format($amount, floor($amount) == $amount ? 0 : 2);
    }

    // digits only, for wa.me links
    public function whatsappNumber()
    {
        return preg_replace('/\D+/', '', (string) ($this->chrome['header_whatsapp'] ?? ''));
    }
}
