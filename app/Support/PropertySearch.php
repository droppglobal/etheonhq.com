<?php

namespace App\Support;

use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Http\Request;

/**
 * Property search on an owner's public website: filters, sorting and the facts shown on each card.
 */
class PropertySearch
{
    public const PER_PAGE = 12;

    public static function sorts()
    {
        return [
            'newest' => __('Newest first'),
            'price_asc' => __('Price: low to high'),
            'price_desc' => __('Price: high to low'),
        ];
    }

    // the visitor's filters, cleaned
    public static function filters(Request $request)
    {
        $f = [
            'q' => trim(mb_substr((string) $request->input('q', ''), 0, 80)),
            'listing' => in_array($request->input('listing'), ['rent', 'sell'], true) ? $request->input('listing') : '',
            'type' => array_key_exists((string) $request->input('type'), Property::types()) ? (string) $request->input('type') : '',
            'city' => trim(mb_substr((string) $request->input('city', ''), 0, 120)),
            'min_price' => is_numeric($request->input('min_price')) ? max(0, (int) $request->input('min_price')) : '',
            'max_price' => is_numeric($request->input('max_price')) ? max(0, (int) $request->input('max_price')) : '',
            'bedrooms' => min(10, max(0, (int) $request->input('bedrooms', 0))) ?: '',
            'available' => $request->boolean('available') ? 1 : '',
            'sort' => array_key_exists((string) $request->input('sort'), self::sorts()) ? (string) $request->input('sort') : 'newest',
        ];
        // the old search form sent country / state / city
        foreach (['country', 'state'] as $key) {
            $f[$key] = trim(mb_substr((string) $request->input($key, ''), 0, 120));
        }
        return $f;
    }

    public static function active(array $f)
    {
        return collect($f)->except('sort')->filter(fn($v) => $v !== '' && $v !== null)->count();
    }

    public static function query($ownerId, array $f)
    {
        $query = Property::with(['thumbnail', 'propertyImages', 'totalUnits'])
            ->where('parent_id', $ownerId)->where('is_active', 1);

        if ($f['q'] !== '') {
            $like = '%' . addcslashes($f['q'], '%_\\') . '%';
            $query->where(fn($q) => $q->where('name', 'like', $like)->orWhere('city', 'like', $like)
                ->orWhere('state', 'like', $like)->orWhere('address', 'like', $like)->orWhere('zip_code', 'like', $like));
        }
        foreach (['listing' => 'listing_type', 'type' => 'type', 'city' => 'city', 'country' => 'country', 'state' => 'state'] as $key => $column) {
            if ($f[$key] !== '') {
                $query->where($column, $f[$key]);
            }
        }
        if ($f['min_price'] !== '') {
            $query->where('price', '>=', $f['min_price']);
        }
        if ($f['max_price'] !== '') {
            $query->where('price', '<=', $f['max_price']);
        }
        if ($f['bedrooms'] !== '') {
            $query->whereHas('totalUnits', fn($u) => $u->where('bedroom', '>=', $f['bedrooms']));
        }
        if ($f['available']) {
            $query->whereHas('totalUnits', fn($u) => $u->where('is_occupied', 0));
        }

        switch ($f['sort']) {
            case 'price_asc':
                $query->orderBy('price')->orderByDesc('id');
                break;
            case 'price_desc':
                $query->orderByDesc('price')->orderByDesc('id');
                break;
            default:
                $query->latest()->orderByDesc('id');
        }
        return $query;
    }

    // choices for the search form, built from the owner's own listings
    public static function options($ownerId)
    {
        $base = Property::where('parent_id', $ownerId)->where('is_active', 1);
        $types = Property::types();
        unset($types['']);
        return [
            'cities' => (clone $base)->whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city'),
            'listings' => (clone $base)->whereIn('listing_type', ['rent', 'sell'])->distinct()->pluck('listing_type')->all(),
            'types' => array_intersect_key($types, array_flip((clone $base)->distinct()->pluck('type')->all())),
            'max_bedrooms' => (int) PropertyUnit::whereIn('property_id', (clone $base)->select('id'))->max('bedroom'),
        ];
    }

    // what a property card shows: rooms, units and status tags
    public static function facts(Property $property, array $featuredIds = [])
    {
        $units = $property->relationLoaded('totalUnits') ? $property->totalUnits : $property->totalUnits()->get();
        $vacant = $units->filter(fn($u) => (string) $u->is_occupied === '0')->count();
        $beds = $units->pluck('bedroom')->filter()->all();
        $baths = $units->pluck('baths')->filter()->all();

        $tags = [];
        if (in_array($property->id, $featuredIds)) {
            $tags[] = ['featured', __('Featured')];
        }
        if ($property->created_at && $property->created_at->gt(now()->subDays(14))) {
            $tags[] = ['new', __('New')];
        }
        if ($units->count() && $property->listing_type !== 'sell') {
            $tags[] = $vacant ? ['available', __('Available now')] : ['let', __('Fully let')];
        }

        return [
            'units' => $units->count(),
            'vacant' => $vacant,
            'beds' => self::range($beds),
            'baths' => self::range($baths),
            'tags' => $tags,
            'images' => collect([optional($property->thumbnail)->image ? 'upload/property/thumbnail/' . $property->thumbnail->image : null])
                ->merge(($property->relationLoaded('propertyImages') ? $property->propertyImages : collect())->take(4)
                    ->map(fn($i) => $i->image ? 'upload/property/image/' . $i->image : null))
                ->filter()->values()->all(),
        ];
    }

    private static function range(array $values)
    {
        if (!$values) {
            return '';
        }
        $min = min($values);
        $max = max($values);
        return $min == $max ? (string) $min : $min . '–' . $max;
    }
}
