<?php

namespace App\Support;

use App\Models\Advantage;
use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyUnit;

/**
 * Writes a property listing description from the property's own details.
 * Uses AI when the owner's plan allows it, otherwise a template-based draft, so the button always works.
 * The AI returns plain text pieces and the HTML is built here, so nothing unsafe reaches the public page.
 */
class ListingWriter
{
    public static function tones()
    {
        return ['professional' => __('Professional'), 'friendly' => __('Friendly'), 'luxury' => __('Luxury'), 'concise' => __('Short & factual')];
    }

    public static function lengths()
    {
        return ['short' => __('Short (~60 words)'), 'medium' => __('Medium (~120 words)'), 'long' => __('Long (~200 words)')];
    }

    // Facts from the form (create) or the saved property (edit); ids are checked against the owner
    public static function facts(array $input, $parentId)
    {
        $amenityIds = array_filter(array_map('intval', (array) ($input['amenities'] ?? [])));
        $advantageIds = array_filter(array_map('intval', (array) ($input['advantages'] ?? [])));

        $facts = [
            'name' => trim((string) ($input['name'] ?? '')),
            'listing' => in_array($input['listing_type'] ?? '', ['rent', 'sell']) ? $input['listing_type'] : null,
            'price' => is_numeric($input['price'] ?? null) && $input['price'] > 0 ? (float) $input['price'] : null,
            'address' => trim((string) ($input['address'] ?? '')),
            'city' => trim((string) ($input['city'] ?? '')),
            'state' => trim((string) ($input['state'] ?? '')),
            'country' => trim((string) ($input['country'] ?? '')),
            'amenities' => $amenityIds ? Amenity::where('parent_id', $parentId)->whereIn('id', $amenityIds)->pluck('name')->all() : [],
            'advantages' => $advantageIds ? Advantage::where('parent_id', $parentId)->whereIn('id', $advantageIds)->pluck('name')->all() : [],
            'units' => [],
            'highlight' => mb_substr(trim((string) ($input['highlight'] ?? '')), 0, 500),
        ];

        // units: saved ones when editing, plus the unit typed into the create form
        if (!empty($input['property_id'])) {
            $property = Property::where('parent_id', $parentId)->find($input['property_id']);
            if ($property) {
                foreach (PropertyUnit::where('property_id', $property->id)->get() as $unit) {
                    $facts['units'][] = self::unitFacts($unit->bedroom, $unit->baths, $unit->kitchen, $unit->rent, $unit->rent_type, $unit->is_occupied);
                }
            }
        }
        if (!empty($input['bedroom'])) {
            $facts['units'][] = self::unitFacts($input['bedroom'], $input['baths'] ?? null, $input['kitchen'] ?? null, $input['rent'] ?? null, $input['rent_type'] ?? null, 0);
        }
        return $facts;
    }

    private static function unitFacts($bed, $bath, $kitchen, $rent, $rentType, $occupied)
    {
        return array_filter([
            'bedrooms' => (int) $bed ?: null,
            'bathrooms' => (int) $bath ?: null,
            'kitchens' => (int) $kitchen ?: null,
            'rent' => is_numeric($rent) && $rent > 0 ? (float) $rent : null,
            'rent_period' => $rentType ?: null,
            'available' => $occupied ? false : true,
        ], function ($v) {
            return $v !== null;
        });
    }

    /**
     * Returns ['html' => string, 'source' => 'ai'|'template'].
     */
    public static function write(array $facts, $tone, $length, $language, $parentId, $currency)
    {
        $tone = array_key_exists($tone, self::tones()) ? $tone : 'professional';
        $length = array_key_exists($length, self::lengths()) ? $length : 'medium';
        $language = mb_substr(trim((string) $language), 0, 30) ?: 'English';

        if (AiService::enabledFor($parentId)) {
            $words = ['short' => 60, 'medium' => 120, 'long' => 200][$length];
            $data = AiService::json([
                ['role' => 'system', 'content' => "You write property listing descriptions for a rental/real-estate website.\n"
                    . "Use ONLY the facts provided. Never invent features, distances, nearby places, sizes or prices that are not in the facts.\n"
                    . "Tone: {$tone}. Language: {$language}. About {$words} words in total.\n"
                    . "Prices use the currency symbol \"{$currency}\". The \"highlight\" field is the owner's own note: include it if it is relevant.\n"
                    . 'Respond as JSON: {"headline": string (max 10 words), "paragraphs": [string, ...] (1-3 short paragraphs), "highlights": [string, ...] (3-6 short bullet points, may be empty)}.'],
                ['role' => 'user', 'content' => json_encode($facts, JSON_UNESCAPED_UNICODE)],
            ], 700, [
                'type' => 'object',
                'properties' => [
                    'headline' => ['type' => 'string'],
                    'paragraphs' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'highlights' => ['type' => 'array', 'items' => ['type' => 'string']],
                ],
                'required' => ['headline', 'paragraphs', 'highlights'],
                'additionalProperties' => false,
            ]);
            if (!empty($data['paragraphs']) && is_array($data['paragraphs'])) {
                return ['html' => self::toHtml($data['headline'] ?? '', $data['paragraphs'], $data['highlights'] ?? []), 'source' => 'ai'];
            }
        }
        return ['html' => self::template($facts, $tone, $length, $currency), 'source' => 'template'];
    }

    private static function toHtml($headline, array $paragraphs, array $highlights)
    {
        $html = '';
        if (trim((string) $headline) !== '') {
            $html .= '<h3>' . e(mb_substr(trim($headline), 0, 120)) . '</h3>';
        }
        foreach (array_slice($paragraphs, 0, 4) as $paragraph) {
            if (is_string($paragraph) && trim($paragraph) !== '') {
                $html .= '<p>' . e(mb_substr(trim($paragraph), 0, 1500)) . '</p>';
            }
        }
        $items = array_filter(array_slice($highlights, 0, 8), function ($h) {
            return is_string($h) && trim($h) !== '';
        });
        if ($items) {
            $html .= '<ul>' . implode('', array_map(function ($h) {
                return '<li>' . e(mb_substr(trim($h), 0, 200)) . '</li>';
            }, $items)) . '</ul>';
        }
        return $html;
    }

    // Draft without AI, built only from the facts
    public static function template(array $f, $tone, $length, $currency)
    {
        $place = implode(', ', array_filter([$f['city'], $f['state']]));
        // the "type" field is the ownership model (own / lease), which means nothing to a renter
        $kind = $f['listing'] == 'sell' ? __('property') : __('home');
        $name = $f['name'] ?: __('This property');
        $opening = [
            'professional' => __(':name is a well-presented :kind:in.', ['name' => $name, 'kind' => $kind, 'in' => $place ? ' ' . __('in') . ' ' . $place : '']),
            'friendly' => __('Welcome to :name, a lovely place:in that you will be happy to call home.', ['name' => $name, 'kind' => $kind, 'in' => $place ? ' ' . __('in') . ' ' . $place : '']),
            'luxury' => __('Discover :name, an exceptional :kind:in offering comfort and style.', ['name' => $name, 'kind' => $kind, 'in' => $place ? ' ' . __('in') . ' ' . $place : '']),
            'concise' => __(':name - :kind:in.', ['name' => $name, 'kind' => $kind, 'in' => $place ? ', ' . $place : '']),
        ][$tone];

        $paragraphs = [$opening];

        if ($f['units']) {
            $beds = array_filter(array_column($f['units'], 'bedrooms'));
            $available = count(array_filter($f['units'], function ($u) {
                return !empty($u['available']);
            }));
            $sentence = count($f['units']) > 1
                ? __('It has :n units', ['n' => count($f['units'])]) . ($beds ? ' ' . __('with :min to :max bedrooms', ['min' => min($beds), 'max' => max($beds)]) : '') . ($available ? ', ' . __(':a currently available', ['a' => $available]) : '') . '.'
                : __('It offers :details.', ['details' => self::humanList(array_filter([
                    self::count($f['units'][0]['bedrooms'] ?? 0, __('bedroom'), __('bedrooms')),
                    self::count($f['units'][0]['bathrooms'] ?? 0, __('bathroom'), __('bathrooms')),
                    self::count($f['units'][0]['kitchens'] ?? 0, __('kitchen'), __('kitchens')),
                ])) ?: __('comfortable living space')]);
            $paragraphs[] = $sentence;
        }

        if ($f['amenities'] && $length != 'short') {
            $paragraphs[] = __('Residents can enjoy :list.', ['list' => self::humanList($f['amenities'])]);
        }
        if ($f['highlight']) {
            $paragraphs[] = self::sentence($f['highlight']);
        }

        // "from" price: the listing price, otherwise the cheapest available unit
        $rentUnits = array_filter($f['units'], function ($u) {
            return !empty($u['rent']) && !empty($u['available']);
        }) ?: array_filter($f['units'], function ($u) {
            return !empty($u['rent']);
        });
        usort($rentUnits, function ($a, $b) {
            return $a['rent'] <=> $b['rent'];
        });
        $cheapest = $rentUnits[0] ?? null;
        $price = $f['price'] ?: ($cheapest['rent'] ?? null);
        if ($price) {
            $period = ['monthly' => __('per month'), 'yearly' => __('per year')][$cheapest['rent_period'] ?? ''] ?? '';
            $paragraphs[] = $f['listing'] == 'sell'
                ? __('Offered for sale at :price.', ['price' => $currency . number_format($price)])
                : trim(__('Available to rent from :price :period', ['price' => $currency . number_format($price), 'period' => $period])) . '.';
        }
        if ($tone != 'concise') {
            $paragraphs[] = __('Get in touch today to arrange a viewing.');
        }

        // join into 1-3 paragraphs depending on length
        $html = '';
        $chunks = $length == 'short' ? [array_slice($paragraphs, 0, 3)] : array_chunk($paragraphs, 3);
        foreach ($chunks as $chunk) {
            $html .= '<p>' . e(implode(' ', $chunk)) . '</p>';
        }
        $points = array_slice(array_merge($f['advantages'], $length == 'long' ? $f['amenities'] : []), 0, 6);
        if ($points && $length != 'short') {
            $html .= '<ul>' . implode('', array_map(function ($p) {
                return '<li>' . e($p) . '</li>';
            }, $points)) . '</ul>';
        }
        return $html;
    }

    private static function count($n, $one, $many)
    {
        $n = (int) $n;
        return $n > 0 ? $n . ' ' . ($n == 1 ? $one : $many) : null;
    }

    // owner's note as a proper sentence: capital first letter, ending punctuation
    private static function sentence($text)
    {
        $text = trim($text);
        $text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
        return preg_match('/[.!?]$/u', $text) ? $text : $text . '.';
    }

    private static function humanList(array $items)
    {
        $items = array_values(array_slice($items, 0, 6));
        if (count($items) < 2) {
            return implode('', $items);
        }
        $last = array_pop($items);
        return implode(', ', $items) . ' ' . __('and') . ' ' . $last;
    }
}
