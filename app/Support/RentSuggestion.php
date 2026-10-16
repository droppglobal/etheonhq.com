<?php

namespace App\Support;

use App\Models\PropertyUnit;

/**
 * Suggests a rent range for a unit from comparable units in the same account
 * (same bedrooms and rent type, same city first). No external data, no AI.
 */
class RentSuggestion
{
    public static function forUnit(PropertyUnit $unit)
    {
        $base = PropertyUnit::where('property_units.parent_id', $unit->parent_id)
            ->where('property_units.id', '!=', $unit->id)
            ->where('property_units.bedroom', $unit->bedroom)
            ->where('property_units.rent_type', $unit->rent_type)
            ->where('property_units.rent', '>', 0)
            ->join('properties', 'properties.id', '=', 'property_units.property_id')
            ->select('property_units.rent', 'property_units.is_occupied');

        $city = optional($unit->properties)->city;
        $scope = __('in :city', ['city' => $city]);
        $comparables = (clone $base)->where('properties.city', $city)->get();
        if ($comparables->count() < 3) {
            $comparables = $base->get();
            $scope = __('across your properties');
        }
        // fewer than 3 comparables gives a misleading range
        if ($comparables->count() < 3) {
            return null;
        }

        $rents = $comparables->pluck('rent')->map(function ($rent) {
            return (float) $rent;
        })->sort()->values()->all();

        return [
            'count' => count($rents),
            'scope' => $scope,
            'low' => self::percentile($rents, 25),
            'median' => self::percentile($rents, 50),
            'high' => self::percentile($rents, 75),
            'occupancy' => round($comparables->where('is_occupied', 1)->count() / $comparables->count() * 100),
            'current' => (float) $unit->rent,
        ];
    }

    private static function percentile(array $sorted, $p)
    {
        $index = ($p / 100) * (count($sorted) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        return round($sorted[$lower] + ($sorted[$upper] - $sorted[$lower]) * ($index - $lower), 2);
    }
}
