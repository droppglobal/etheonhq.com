<?php

namespace App\Support;

use App\Models\FrontHomePage;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;

/**
 * The owner's public website (/web/{code}): which sections exist, which fields each one has, how they are stored.
 *
 * Every section is a row in front_home_pages (one set per owner). content_value holds the fields; repeatable
 * boxes (highlights, testimonials, FAQ...) are stored as content_value['items'] = [[field => value], ...].
 * Image fields store the file under "<key>_path". translations holds per-language text: [lang => [field => text,
 * 'items' => [i => [field => text]]]]. The editor (Frontend Manager) and the public page are both driven by
 * definitions(), so adding a section type means adding it here plus a view in theme/sections.
 */
class WebsiteSections
{
    // "Section 8" holds header & footer; it is edited on its own tab and always rendered at the bottom
    const CHROME = 'Section 8';

    public static function definitions()
    {
        $title = ['key' => 'title', 'type' => 'text', 'label' => 'Heading'];
        $info = ['key' => 'info', 'type' => 'textarea', 'label' => 'Intro text'];

        return [
            'Section 0' => [
                'title' => 'Banner', 'icon' => 'ti-photo', 'view' => 'banner', 'order' => 10,
                'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Heading'],
                    ['key' => 'sub_title', 'type' => 'textarea', 'label' => 'Sub heading'],
                    ['key' => 'button_text', 'type' => 'text', 'label' => 'Button text'],
                    ['key' => 'button_link', 'type' => 'link', 'label' => 'Button link'],
                    ['key' => 'banner_image1', 'type' => 'image', 'label' => 'Image'],
                    ['key' => 'show_search', 'type' => 'toggle', 'label' => 'Show a property search box'],
                ],
            ],
            'Section 1' => [
                'title' => 'Highlights', 'icon' => 'ti-star', 'view' => 'highlights', 'order' => 20,
                'fields' => [['key' => 'Sec1_title', 'type' => 'text', 'label' => 'Heading'], ['key' => 'Sec1_info', 'type' => 'textarea', 'label' => 'Intro text']],
                'items' => ['label' => 'Box', 'plural' => 'Boxes', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'info', 'type' => 'textarea', 'label' => 'Text'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Image'],
                ]],
            ],
            'Section 2' => [
                'title' => 'Numbers', 'icon' => 'ti-chart-bar', 'view' => 'stats', 'order' => 30,
                'fields' => [],
                'items' => ['label' => 'Number', 'plural' => 'Numbers', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Label'],
                    ['key' => 'source', 'type' => 'select', 'label' => 'Value', 'options' => self::statSources()],
                    ['key' => 'number', 'type' => 'text', 'label' => 'Number (when typed in)'],
                    ['key' => 'suffix', 'type' => 'text', 'label' => 'After the number (e.g. +, %)'],
                ]],
            ],
            'Section 3' => [
                'title' => 'Amenities', 'icon' => 'ti-swimming', 'view' => 'amenities', 'order' => 40,
                'help' => 'Shows the amenities you added under Property Amenity.',
                'fields' => [['key' => 'Sec3_title', 'type' => 'text', 'label' => 'Heading'], ['key' => 'Sec3_info', 'type' => 'textarea', 'label' => 'Intro text']],
            ],
            'Section 4' => [
                'title' => 'About Us', 'icon' => 'ti-info-circle', 'view' => 'about', 'order' => 50,
                'fields' => [['key' => 'Sec4_title', 'type' => 'text', 'label' => 'Heading'], ['key' => 'about_image', 'type' => 'image', 'label' => 'Image']],
                'items' => ['label' => 'Point', 'plural' => 'Points', 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'subtitle', 'type' => 'textarea', 'label' => 'Text'],
                ]],
            ],
            'Section 5' => [
                'title' => 'Property Listings', 'icon' => 'ti-building', 'view' => 'listings', 'order' => 60,
                'help' => 'Shows your properties for rent and for sale in tabs.',
                'fields' => [['key' => 'Sec5_title', 'type' => 'text', 'label' => 'Heading'], ['key' => 'Sec5_info', 'type' => 'textarea', 'label' => 'Intro text']],
            ],
            'Section 6' => [
                'title' => 'Promotion Banner', 'icon' => 'ti-speakerphone', 'view' => 'promo', 'order' => 70,
                'fields' => [
                    ['key' => 'Sec6_title', 'type' => 'text', 'label' => 'Heading'],
                    ['key' => 'Sec6_info', 'type' => 'textarea', 'label' => 'Text'],
                    ['key' => 'sec6_btn_name', 'type' => 'text', 'label' => 'Button text'],
                    ['key' => 'sec6_btn_link', 'type' => 'link', 'label' => 'Button link'],
                    ['key' => 'banner_image2', 'type' => 'image', 'label' => 'Image'],
                ],
            ],
            'Section 7' => [
                'title' => 'Testimonials', 'icon' => 'ti-message-circle', 'view' => 'testimonials', 'order' => 80,
                'fields' => [['key' => 'Sec7_title', 'type' => 'text', 'label' => 'Heading'], ['key' => 'Sec7_info', 'type' => 'textarea', 'label' => 'Intro text'],
                    ['key' => 'include_reviews', 'type' => 'toggle', 'label' => 'Also show reviews from your tenants (the ones you approve)']],
                'items' => ['label' => 'Review', 'plural' => 'Reviews', 'fields' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                    ['key' => 'tag', 'type' => 'text', 'label' => 'Role (e.g. Tenant)'],
                    ['key' => 'review', 'type' => 'textarea', 'label' => 'Review'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                ]],
            ],
            'Section 9' => [
                'title' => 'Featured Properties', 'icon' => 'ti-home', 'view' => 'featured', 'order' => 25, 'new' => true,
                'fields' => [
                    $title, $info,
                    ['key' => 'mode', 'type' => 'select', 'label' => 'Which properties', 'options' => ['latest' => 'Newest first', 'selected' => 'The ones I pick below']],
                    ['key' => 'count', 'type' => 'number', 'label' => 'How many (newest first)'],
                    ['key' => 'property_ids', 'type' => 'properties', 'label' => 'Pick properties'],
                ],
            ],
            'Section 10' => [
                'title' => 'FAQ', 'icon' => 'ti-help', 'view' => 'faq', 'order' => 85, 'new' => true,
                'fields' => [$title, $info],
                'items' => ['label' => 'Question', 'plural' => 'Questions', 'fields' => [
                    ['key' => 'question', 'type' => 'text', 'label' => 'Question'],
                    ['key' => 'answer', 'type' => 'textarea', 'label' => 'Answer'],
                ]],
            ],
            'Section 11' => [
                'title' => 'Our Team', 'icon' => 'ti-users', 'view' => 'team', 'order' => 55, 'new' => true,
                'fields' => [$title, $info],
                'items' => ['label' => 'Person', 'plural' => 'People', 'fields' => [
                    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                    ['key' => 'role', 'type' => 'text', 'label' => 'Role'],
                    ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                    ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                ]],
            ],
            'Section 12' => [
                'title' => 'Gallery', 'icon' => 'ti-photo', 'view' => 'gallery', 'order' => 65, 'new' => true,
                'fields' => [$title, $info],
                'items' => ['label' => 'Photo', 'plural' => 'Photos', 'fields' => [
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                    ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
                ]],
            ],
            'Section 13' => [
                'title' => 'Video', 'icon' => 'ti-video', 'view' => 'video', 'order' => 66, 'new' => true,
                'fields' => [$title, $info, ['key' => 'video_url', 'type' => 'link', 'label' => 'YouTube or Vimeo link']],
            ],
            'Section 14' => [
                'title' => 'Call to Action', 'icon' => 'ti-click', 'view' => 'cta', 'order' => 75, 'new' => true,
                'fields' => [
                    $title, $info,
                    ['key' => 'button_text', 'type' => 'text', 'label' => 'Button text'],
                    ['key' => 'button_link', 'type' => 'link', 'label' => 'Button link'],
                    ['key' => 'style', 'type' => 'select', 'label' => 'Background', 'options' => ['brand' => 'Brand colour', 'dark' => 'Dark', 'light' => 'Light']],
                ],
            ],
            'Section 15' => [
                'title' => 'Partner Logos', 'icon' => 'ti-building-bank', 'view' => 'partners', 'order' => 82, 'new' => true,
                'fields' => [$title],
                'items' => ['label' => 'Logo', 'plural' => 'Logos', 'fields' => [
                    ['key' => 'image', 'type' => 'image', 'label' => 'Logo'],
                    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                    ['key' => 'link', 'type' => 'link', 'label' => 'Link'],
                ]],
            ],
            'Section 16' => [
                'title' => 'Map & Contact', 'icon' => 'ti-map-pin', 'view' => 'map', 'order' => 90, 'new' => true,
                'fields' => [
                    $title, $info,
                    ['key' => 'address', 'type' => 'textarea', 'label' => 'Address (shown on the map)'],
                    ['key' => 'phone', 'type' => 'text', 'label' => 'Phone'],
                    ['key' => 'email', 'type' => 'text', 'label' => 'Email'],
                    ['key' => 'hours', 'type' => 'text', 'label' => 'Opening hours'],
                ],
            ],
            self::CHROME => [
                'title' => 'Header & Footer', 'icon' => 'ti-layout-navbar', 'view' => null, 'order' => 1000,
                'fields' => [
                    ['key' => 'show_home', 'type' => 'toggle', 'label' => 'Menu: Home', 'group' => 'Menu'],
                    ['key' => 'show_properties', 'type' => 'toggle', 'label' => 'Menu: Properties', 'group' => 'Menu'],
                    ['key' => 'show_blog', 'type' => 'toggle', 'label' => 'Menu: Blog', 'group' => 'Menu'],
                    ['key' => 'show_contact', 'type' => 'toggle', 'label' => 'Menu: Contact', 'group' => 'Menu'],
                    ['key' => 'header_phone', 'type' => 'text', 'label' => 'Phone (header)', 'group' => 'Header'],
                    ['key' => 'header_whatsapp', 'type' => 'text', 'label' => 'WhatsApp number, with country code', 'group' => 'Header'],
                    ['key' => 'header_button_text', 'type' => 'text', 'label' => 'Header button text (e.g. Tenant login)', 'group' => 'Header'],
                    ['key' => 'header_button_link', 'type' => 'link', 'label' => 'Header button link', 'group' => 'Header'],
                    ['key' => 'Sec8_info', 'type' => 'textarea', 'label' => 'About text', 'group' => 'Footer'],
                    ['key' => 'footer_address', 'type' => 'textarea', 'label' => 'Address', 'group' => 'Footer'],
                    ['key' => 'footer_phone', 'type' => 'text', 'label' => 'Phone', 'group' => 'Footer'],
                    ['key' => 'footer_email', 'type' => 'text', 'label' => 'Email', 'group' => 'Footer'],
                    ['key' => 'footer_hours', 'type' => 'text', 'label' => 'Office hours', 'group' => 'Footer'],
                    ['key' => 'copyright', 'type' => 'text', 'label' => 'Copyright line', 'group' => 'Footer'],
                    ['key' => 'fb_link', 'type' => 'link', 'label' => 'Facebook', 'group' => 'Social links'],
                    ['key' => 'twitter_link', 'type' => 'link', 'label' => 'X / Twitter', 'group' => 'Social links'],
                    ['key' => 'insta_link', 'type' => 'link', 'label' => 'Instagram', 'group' => 'Social links'],
                    ['key' => 'linkedin_link', 'type' => 'link', 'label' => 'LinkedIn', 'group' => 'Social links'],
                    ['key' => 'youtube_link', 'type' => 'link', 'label' => 'YouTube', 'group' => 'Social links'],
                    ['key' => 'show_tenant_links', 'type' => 'toggle', 'label' => 'Show tenant links (login, pay rent, report a repair)', 'group' => 'For tenants'],
                    ['key' => 'app_android', 'type' => 'link', 'label' => 'Android app link (Google Play)', 'group' => 'For tenants'],
                    ['key' => 'app_ios', 'type' => 'link', 'label' => 'iPhone app link (App Store)', 'group' => 'For tenants'],
                    ['key' => 'whatsapp_float', 'type' => 'toggle', 'label' => 'Show a floating WhatsApp button (uses the WhatsApp number above)', 'group' => 'Contact buttons'],
                    ['key' => 'cookie_banner', 'type' => 'toggle', 'label' => 'Show a cookie notice', 'group' => 'Privacy'],
                    ['key' => 'cookie_text', 'type' => 'textarea', 'label' => 'Cookie notice text', 'group' => 'Privacy'],
                    ['key' => 'privacy_page', 'type' => 'page', 'label' => 'Privacy policy page', 'group' => 'Privacy'],
                    ['key' => 'terms_page', 'type' => 'page', 'label' => 'Terms page', 'group' => 'Privacy'],
                ],
                'items' => ['label' => 'Extra menu link', 'plural' => 'Extra menu links', 'fields' => [
                    ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                    ['key' => 'link', 'type' => 'link', 'label' => 'Link'],
                ]],
            ],
        ];
    }

    public static function statSources()
    {
        return [
            'manual' => 'Typed in below',
            'properties' => 'Number of properties',
            'units' => 'Number of units',
            'occupied' => 'Occupied units',
            'tenants' => 'Number of tenants',
            'occupancy' => 'Occupancy rate (%)',
        ];
    }

    // live figures for the Numbers section
    public static function statValue($ownerId, $source)
    {
        switch ($source) {
            case 'properties':
                return Property::where('parent_id', $ownerId)->count();
            case 'units':
                return PropertyUnit::where('parent_id', $ownerId)->count();
            case 'occupied':
                return PropertyUnit::where('parent_id', $ownerId)->where('is_occupied', 1)->count();
            case 'tenants':
                return Tenant::where('parent_id', $ownerId)->whereNull('exit_date')->count(); // current tenants
            case 'occupancy':
                $all = PropertyUnit::where('parent_id', $ownerId)->count();
                return $all ? (int) round(PropertyUnit::where('parent_id', $ownerId)->where('is_occupied', 1)->count() / $all * 100) : 0;
        }
        return null;
    }

    /**
     * Starting content for a section that does not exist yet (new owners, and new section types for existing owners).
     */
    public static function defaults($section)
    {
        $d = [
            'Section 9' => ['title' => 'Featured Properties', 'info' => 'Hand-picked homes available right now.', 'mode' => 'latest', 'count' => 6],
            'Section 10' => ['title' => 'Frequently Asked Questions', 'info' => 'Everything you need to know before you move in.', 'items' => [
                ['question' => 'How do I apply for a property?', 'answer' => 'Open the property, choose a unit and press "Apply Now". It takes a few minutes and you can upload your documents online.'],
                ['question' => 'How do I pay my rent?', 'answer' => 'You can pay online by card or bank transfer from your tenant account, or turn on automatic payments.'],
                ['question' => 'How do I report a repair?', 'answer' => 'Log in to your tenant account or the mobile app and create a maintenance request with a photo. We will keep you updated.'],
            ]],
            'Section 11' => ['title' => 'Meet Our Team', 'info' => 'The people who look after your home.', 'items' => []],
            'Section 12' => ['title' => 'Gallery', 'info' => 'A look around our properties.', 'items' => []],
            'Section 13' => ['title' => 'Take a Tour', 'info' => 'See our properties before you visit.', 'video_url' => ''],
            'Section 14' => ['title' => 'Looking for your next home?', 'info' => 'Browse available properties and book a viewing today.', 'button_text' => 'View Properties', 'button_link' => '', 'style' => 'brand'],
            'Section 15' => ['title' => 'Trusted by', 'items' => []],
            'Section 16' => ['title' => 'Visit Our Office', 'info' => 'We are happy to help you in person.', 'address' => '', 'phone' => '', 'email' => '', 'hours' => 'Mon–Fri 9:00–18:00'],
        ];
        $def = self::definitions()[$section] ?? [];
        return ['name' => $def['title'] ?? $section, 'section_enabled' => 'deactive'] + ($d[$section] ?? []);
    }

    /**
     * Brings old content into the current format: the fixed "box 1..n" fields become an items list.
     * Leaves content that already has items untouched.
     */
    public static function normalize($section, array $c)
    {
        if ($section === self::CHROME) {
            // switches added later must have a value, or the editor shows them as on
            $c += ['show_home' => 'active', 'show_properties' => 'active', 'show_blog' => 'active', 'show_contact' => 'active',
                'show_tenant_links' => 'active', 'whatsapp_float' => 'deactive', 'cookie_banner' => 'deactive'];
            // an empty cookie text shows the standard one, in the visitor's language
        }
        if (array_key_exists('items', $c) && is_array($c['items'])) {
            return $c;
        }
        $items = null;
        if ($section === 'Section 1') {
            $items = [];
            for ($i = 1; $i <= 8; $i++) {
                if (!isset($c["Sec1_box{$i}_title"]) && !isset($c["Sec1_box{$i}_info"])) {
                    continue;
                }
                if (($c["Sec1_box{$i}_enabled"] ?? 'active') !== 'active') {
                    continue;
                }
                $items[] = ['title' => $c["Sec1_box{$i}_title"] ?? '', 'info' => $c["Sec1_box{$i}_info"] ?? '', 'image_path' => $c["Sec1_box{$i}_image_path"] ?? ''];
            }
        } elseif ($section === 'Section 2') {
            $items = [];
            for ($i = 1; $i <= 8; $i++) {
                if (isset($c["Box{$i}_title"]) || isset($c["Box{$i}_number"])) {
                    $items[] = ['title' => $c["Box{$i}_title"] ?? '', 'source' => 'manual', 'number' => $c["Box{$i}_number"] ?? '', 'suffix' => ''];
                }
            }
        } elseif ($section === 'Section 4') {
            $items = [];
            foreach ((array) ($c['Sec4_Box_title'] ?? []) as $k => $t) {
                $items[] = ['title' => $t, 'subtitle' => $c['Sec4_Box_subtitle'][$k] ?? ''];
            }
        } elseif ($section === 'Section 7') {
            $items = [];
            for ($i = 1; $i <= 8; $i++) {
                if (empty($c["Sec7_box{$i}_name"]) && empty($c["Sec7_box{$i}_review"])) {
                    continue;
                }
                if (($c["Sec7_box{$i}_Enabled"] ?? 'active') !== 'active') {
                    continue;
                }
                $items[] = ['name' => $c["Sec7_box{$i}_name"] ?? '', 'tag' => $c["Sec7_box{$i}_tag"] ?? '', 'review' => $c["Sec7_box{$i}_review"] ?? '', 'image_path' => $c["Sec7_box{$i}_image_path"] ?? ''];
            }
        } elseif ($section === self::CHROME) {
            $items = [];
        }
        if ($items !== null) {
            $c['items'] = $items;
        }
        return $c;
    }

    /**
     * Makes sure an owner has every section, in the current format, with an order. Safe to run again.
     */
    public static function ensure($ownerId)
    {
        $rows = FrontHomePage::where('parent_id', $ownerId)->get()->keyBy('section');
        if ($rows->isEmpty()) {
            return; // owners get their starting content from FrontHomePageSection() first
        }
        foreach (self::definitions() as $section => $def) {
            $row = $rows->get($section);
            if (!$row) {
                $row = new FrontHomePage();
                $row->parent_id = $ownerId;
                $row->section = $section;
                $row->title = $def['title'];
                $row->content = '';
                $row->enabled = 1;
                $row->content_value = json_encode(self::defaults($section), JSON_UNESCAPED_UNICODE);
                $row->sort_order = $def['order'];
                $row->save();
                continue;
            }
            $content = json_decode((string) $row->content_value, true) ?: [];
            $normalized = self::normalize($section, $content);
            $legacyNames = ['OverView', 'Banner2', 'PopularService', 'AboutUs', 'Category', 'Amenity', 'Offer', 'AboutUS - Footer'];
            if (in_array($normalized['name'] ?? '', $legacyNames)) {
                $normalized['name'] = $def['title'];
            }
            $changed = $normalized !== $content;
            if (empty($row->sort_order)) {
                $row->sort_order = $def['order'];
                $changed = true;
            }
            if ($row->title !== $def['title'] && $section === self::CHROME) {
                $row->title = $def['title'];
                $changed = true;
            }
            if ($changed) {
                $row->content_value = json_encode($normalized, JSON_UNESCAPED_UNICODE);
                $row->save();
            }
        }
    }

    // ------------------------------------------------------------------ reading content for the public site

    public static function decode($row)
    {
        $c = is_array($row->content_value) ? $row->content_value : (json_decode((string) $row->content_value, true) ?: []);
        return self::normalize($row->section, $c);
    }

    /**
     * Content in the visitor's language: translated texts replace the default ones; images, links and settings stay.
     */
    public static function localized($row, $lang)
    {
        $c = self::decode($row);
        $t = json_decode((string) ($row->translations ?? ''), true) ?: [];
        if (!$lang || empty($t[$lang]) || !is_array($t[$lang])) {
            return $c;
        }
        foreach ($t[$lang] as $key => $value) {
            if ($key === 'items' && is_array($value)) {
                foreach ($value as $i => $fields) {
                    foreach ((array) $fields as $f => $text) {
                        if (isset($c['items'][$i]) && trim((string) $text) !== '') {
                            $c['items'][$i][$f] = $text;
                        }
                    }
                }
            } elseif (is_string($value) && trim($value) !== '') {
                $c[$key] = $value;
            }
        }
        return $c;
    }

    public static function enabled(array $c)
    {
        return empty($c['section_enabled']) || $c['section_enabled'] === 'active';
    }

    // text fields that can be translated (images, links and settings are shared by all languages)
    public static function translatable($section)
    {
        $def = self::definitions()[$section] ?? [];
        $pick = fn($fields) => collect($fields)->whereIn('type', ['text', 'textarea'])->pluck('key')->all();
        return ['fields' => $pick($def['fields'] ?? []), 'items' => $pick($def['items']['fields'] ?? [])];
    }

    /**
     * "Write with AI" types for the website sections (merged into AiWriter::types()). Outputs are the section's
     * own text fields, so the generated text drops straight into the form.
     */
    public static function aiTypes()
    {
        $types = [];
        foreach (self::definitions() as $section => $def) {
            $outputs = [];
            foreach ($def['fields'] as $f) {
                if (in_array($f['type'], ['text', 'textarea']) && empty($f['group'])) {
                    $outputs['content_value[' . $f['key'] . ']'] = [$f['label'], 'plain', $f['type'] === 'text' ? 90 : 400,
                        $f['type'] === 'text' ? 'short and catchy, max 10 words' : 'one to three sentences'];
                }
            }
            if ($outputs) {
                $types['website_' . str_replace(' ', '_', strtolower($section))] = [
                    'purpose' => 'the "' . $def['title'] . '" section of the public website of a property rental company',
                    'outputs' => $outputs,
                    'context' => ['content_value[name]' => 'Section'],
                ];
            }
        }
        return $types;
    }

    // languages the site content can be written in (folders in resources/lang, without Laravel's own "en")
    public static function languages()
    {
        return collect(\App\Models\Custom::languages())->reject(fn($l) => $l === 'en')->values()
            ->mapWithKeys(fn($l) => [$l => ucfirst($l)])->all();
    }
}
