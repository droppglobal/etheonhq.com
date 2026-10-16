<?php

namespace App\Http\Controllers;

use App\Models\ParkingAssignment;
use App\Models\ParkingSlot;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    private function findSlot($id)
    {
        return ParkingSlot::where('parent_id', parentId())->where('id', $id)->first();
    }

    public function index(Request $request)
    {
        if (\Auth::user()->type == 'tenant') {
            $tenant = \Auth::user()->tenants;
            $assignments = $tenant ? ParkingAssignment::where('parent_id', parentId())->where('tenant_id', $tenant->id)
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->toDateString());
                })->get() : collect();
            return view('parking.tenant', compact('assignments'));
        }
        if (!\Auth::user()->can('manage parking')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $query = ParkingSlot::where('parent_id', parentId())->with('currentAssignment')->orderBy('property_id')->orderBy('code');
        if (!empty($request->property)) {
            $query->where('property_id', $request->property);
        }
        $slots = $query->get();
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id');
        $tenants = Tenant::where('parent_id', parentId())->whereNull('exit_date')->with('user', 'units')->get()->mapWithKeys(function ($t) {
            return [$t->id => optional($t->user)->name . ' (' . optional($t->units)->name . ')'];
        });
        return view('parking.index', compact('slots', 'properties', 'tenants'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('manage parking')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'property_id' => 'required',
            'codes' => 'required|max:2000',
            'vehicle_type' => 'required|in:' . implode(',', array_keys(ParkingSlot::vehicleTypes())),
            'monthly_fee' => 'nullable|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (!Property::where('parent_id', parentId())->where('id', $request->property_id)->exists()) {
            return redirect()->back()->with('error', __('Property not found.'));
        }
        // "P1, P2, P3" or "P1-P20" creates several slots at once
        $codes = [];
        foreach (preg_split('/[\s,]+/', trim($request->codes)) as $part) {
            if (preg_match('/^([A-Za-z\-]*)(\d+)-\1?(\d+)$/', $part, $m) && $m[3] >= $m[2] && $m[3] - $m[2] < 500) {
                for ($i = (int) $m[2]; $i <= (int) $m[3]; $i++) {
                    $codes[] = $m[1] . $i;
                }
            } elseif ($part !== '') {
                $codes[] = mb_substr($part, 0, 50);
            }
        }
        $existing = ParkingSlot::where('parent_id', parentId())->where('property_id', $request->property_id)->pluck('code')->map('strtoupper')->all();
        $created = 0;
        foreach (array_unique($codes) as $code) {
            if (in_array(strtoupper($code), $existing)) {
                continue;
            }
            ParkingSlot::create(['property_id' => $request->property_id, 'code' => $code, 'vehicle_type' => $request->vehicle_type,
                'monthly_fee' => $request->monthly_fee ?: 0, 'is_active' => 1, 'parent_id' => parentId()]);
            $created++;
        }
        return redirect()->back()->with('success', __(':n parking slot(s) created.', ['n' => $created]));
    }

    public function destroy($id)
    {
        $slot = $this->findSlot($id);
        if (!\Auth::user()->can('manage parking') || !$slot) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if ($slot->currentAssignment) {
            return redirect()->back()->with('error', __('Free the slot before deleting it.'));
        }
        ParkingAssignment::where('slot_id', $slot->id)->delete();
        $slot->delete();
        return redirect()->back()->with('success', __('Parking slot deleted.'));
    }

    public function assign(Request $request, $id)
    {
        $slot = $this->findSlot($id);
        if (!\Auth::user()->can('manage parking') || !$slot) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if ($slot->currentAssignment) {
            return redirect()->back()->with('error', __('This slot is already assigned.'));
        }
        $tenant = Tenant::where('parent_id', parentId())->whereNull('exit_date')->find($request->tenant_id);
        if (!$tenant) {
            return redirect()->back()->with('error', __('Tenant not found.'));
        }
        ParkingAssignment::create([
            'slot_id' => $slot->id,
            'tenant_id' => $tenant->id,
            'vehicle_number' => mb_substr((string) $request->vehicle_number, 0, 50),
            'vehicle_model' => mb_substr((string) $request->vehicle_model, 0, 100),
            'start_date' => now()->toDateString(),
            'parent_id' => parentId(),
        ]);
        return redirect()->back()->with('success', __('Slot :code assigned.', ['code' => $slot->code]));
    }

    public function release($id)
    {
        $slot = $this->findSlot($id);
        if (!\Auth::user()->can('manage parking') || !$slot) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!$slot->currentAssignment) {
            return redirect()->back()->with('error', __('This slot is not assigned.'));
        }
        // keep history: end the assignment yesterday so the slot is free today
        $assignment = $slot->currentAssignment;
        $assignment->end_date = now()->subDay()->toDateString();
        $assignment->save();
        return redirect()->back()->with('success', __('Slot :code is free again.', ['code' => $slot->code]));
    }
}
