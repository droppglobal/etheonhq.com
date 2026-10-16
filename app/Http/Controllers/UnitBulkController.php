<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Http\Request;

/**
 * Create many units at once from a floor / numbering pattern, sharing the same rent and terms.
 */
class UnitBulkController extends Controller
{
    const MAX_UNITS = 500;

    // unit names for the chosen pattern, in order
    public static function names($style, $prefix, $floorFrom, $floorTo, $perFloor, $start)
    {
        $names = [];
        $prefix = trim((string) $prefix);
        $join = $prefix === '' ? '' : (preg_match('/[-\/ ]$/', $prefix) ? '' : ' ');
        if ($style == 'sequence') {
            for ($i = 0; $i < $perFloor; $i++) {
                $names[] = $prefix . $join . ($start + $i);
            }
            return $names;
        }
        for ($floor = $floorFrom; $floor <= $floorTo; $floor++) {
            for ($n = 1; $n <= $perFloor; $n++) {
                // floor style: 101, 102 ... 201, 202 (floor 0 gives G01, G02)
                $number = $floor == 0 ? 'G' . str_pad($n, 2, '0', STR_PAD_LEFT) : $floor . str_pad($n, 2, '0', STR_PAD_LEFT);
                $names[] = $prefix . $join . $number;
            }
        }
        return $names;
    }

    public function create(Request $request)
    {
        if (!\Auth::user()->can('create unit')) {
            return response(__('Permission Denied!'), 403);
        }
        $properties = Property::where('parent_id', parentId())->orderBy('name')->pluck('name', 'id');
        $selected = $request->query('property');
        return view('unit.bulk_create', [
            'properties' => $properties,
            'selected' => $selected,
            'types' => PropertyUnit::type(),
        ]);
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create unit')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $property = Property::where('parent_id', parentId())->where('id', (int) $request->property_id)->first();
        if (!$property) {
            return redirect()->back()->with('error', __('Choose a property.'));
        }
        $validator = \Validator::make($request->all(), [
            'style' => 'required|in:floors,sequence',
            'units_per_floor' => 'required|integer|min:1|max:200',
            'floor_from' => 'required_if:style,floors|nullable|integer|min:0|max:200',
            'floor_to' => 'required_if:style,floors|nullable|integer|min:0|max:200',
            'start_number' => 'required_if:style,sequence|nullable|integer|min:0',
            'rent' => 'required|numeric|min:0',
            'rent_type' => 'required|in:monthly,yearly',
            'rent_duration' => 'nullable|integer|min:1|max:31',
            'bedroom' => 'required|integer|min:0', 'kitchen' => 'required|integer|min:0', 'baths' => 'required|integer|min:0',
            'deposit_type' => 'required|in:fixed,percentage', 'deposit_amount' => 'required|numeric|min:0',
            'late_fee_type' => 'required|in:fixed,percentage', 'late_fee_amount' => 'required|numeric|min:0',
            'incident_receipt_amount' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if ($request->style == 'floors' && $request->floor_to < $request->floor_from) {
            return redirect()->back()->with('error', __('The last floor must be the same as or above the first floor.'));
        }

        $names = self::names($request->style, $request->prefix, (int) $request->floor_from, (int) $request->floor_to, (int) $request->units_per_floor, (int) $request->start_number);
        if (count($names) > self::MAX_UNITS) {
            return redirect()->back()->with('error', __('That would create :count units; the most at once is :max.', ['count' => count($names), 'max' => self::MAX_UNITS]));
        }
        $existing = PropertyUnit::where('property_id', $property->id)->pluck('name')->map(fn($n) => mb_strtolower(trim($n)))->all();

        $created = 0;
        $skipped = [];
        \DB::transaction(function () use ($names, $existing, $property, $request, &$created, &$skipped) {
            foreach ($names as $name) {
                if (in_array(mb_strtolower($name), $existing)) {
                    $skipped[] = $name;
                    continue;
                }
                $unit = new PropertyUnit();
                $unit->name = $name;
                $unit->bedroom = (int) $request->bedroom;
                $unit->kitchen = (int) $request->kitchen;
                $unit->baths = (int) $request->baths;
                $unit->rent = $request->rent;
                $unit->rent_type = $request->rent_type;
                $unit->rent_duration = (int) ($request->rent_duration ?: 1);
                $unit->deposit_type = $request->deposit_type;
                $unit->deposit_amount = $request->deposit_amount;
                $unit->late_fee_type = $request->late_fee_type;
                $unit->late_fee_amount = $request->late_fee_amount;
                $unit->incident_receipt_amount = $request->incident_receipt_amount;
                $unit->notes = $request->notes;
                $unit->property_id = $property->id;
                $unit->is_occupied = 0;
                $unit->parent_id = parentId();
                $unit->save();
                $created++;
            }
        });

        $message = $created == 1 ? __('1 unit created.') : __(':count units created.', ['count' => $created]);
        if ($skipped) {
            $message .= ' ' . __('Skipped (name already used): :names', ['names' => implode(', ', array_slice($skipped, 0, 10)) . (count($skipped) > 10 ? '…' : '')]);
        }
        return redirect()->back()->with('success', $message);
    }
}
