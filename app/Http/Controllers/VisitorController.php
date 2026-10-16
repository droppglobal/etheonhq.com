<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\VisitorPass;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Tenants pre-register visitors and share a code; gate staff check visitors in and out.
 */
class VisitorController extends Controller
{
    private function tenant()
    {
        return \Auth::user()->type == 'tenant' ? \Auth::user()->tenants : null;
    }

    public function index(Request $request)
    {
        if ($tenant = $this->tenant()) {
            if (!\Auth::user()->can('create visitor pass')) {
                return redirect()->back()->with('error', __('Permission Denied!'));
            }
            $passes = VisitorPass::where('parent_id', parentId())->where('tenant_id', $tenant->id)
                ->orderBy('visit_date', 'desc')->orderBy('id', 'desc')->limit(100)->get();
            return view('visitor.tenant', compact('passes'));
        }
        if (!\Auth::user()->can('manage visitor')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $date = $request->date && strtotime($request->date) ? date('Y-m-d', strtotime($request->date)) : Carbon::today()->toDateString();
        $passes = VisitorPass::where('parent_id', parentId())->whereDate('visit_date', $date)->orderBy('status')->orderBy('visitor_name')->get();
        $inside = VisitorPass::where('parent_id', parentId())->where('status', 'checked_in')->orderBy('checked_in_at')->get();
        $tenants = Tenant::where('parent_id', parentId())->whereNull('exit_date')->with('user', 'units', 'properties')->get()->mapWithKeys(function ($t) {
            return [$t->id => optional($t->properties)->name . ' / ' . optional($t->units)->name . ' - ' . optional($t->user)->name];
        });
        return view('visitor.gate', compact('passes', 'inside', 'date', 'tenants'));
    }

    // tenant pre-registers a visitor
    public function store(Request $request)
    {
        $tenant = $this->tenant();
        if (!$tenant || !\Auth::user()->can('create visitor pass') || !empty($tenant->exit_date)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'visitor_name' => 'required|max:191',
            'visitor_phone' => 'nullable|max:50',
            'visit_date' => 'required|date|after_or_equal:today|before_or_equal:' . Carbon::today()->addDays(30)->toDateString(),
            'purpose' => 'nullable|max:191',
            'vehicle_number' => 'nullable|max:50',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $pass = VisitorPass::create($request->only('visitor_name', 'visitor_phone', 'visit_date', 'purpose', 'vehicle_number') + [
            'tenant_id' => $tenant->id,
            'property_id' => $tenant->property,
            'unit_id' => $tenant->unit,
            'code' => VisitorPass::newCode(parentId()),
            'status' => 'active',
            'parent_id' => parentId(),
        ]);
        return redirect()->back()->with('success', __('Visitor pass created. Share code :code with :name.', ['code' => $pass->code, 'name' => $pass->visitor_name]));
    }

    public function cancel($id)
    {
        $tenant = $this->tenant();
        $pass = $tenant ? VisitorPass::where('parent_id', parentId())->where('tenant_id', $tenant->id)->where('id', $id)->first() : null;
        if (!$pass || $pass->status != 'active') {
            return redirect()->back()->with('error', __('This pass cannot be cancelled.'));
        }
        $pass->status = 'cancelled';
        $pass->save();
        return redirect()->back()->with('success', __('Visitor pass cancelled.'));
    }

    // gate: look up a code and check the visitor in
    public function checkIn(Request $request)
    {
        if ($this->tenant() || !\Auth::user()->can('manage visitor')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->code));
        $pass = VisitorPass::where('parent_id', parentId())->where('code', $code)->first();
        if (!$pass) {
            return redirect()->back()->with('error', __('No visitor pass found for code :code.', ['code' => $code]));
        }
        if ($pass->status != 'active') {
            $messages = [
                'checked_in' => __(':name is already checked in.', ['name' => $pass->visitor_name]),
                'checked_out' => __('This pass was already used; :name has left.', ['name' => $pass->visitor_name]),
                'cancelled' => __('This pass was cancelled by the tenant.'),
            ];
            return redirect()->back()->with('error', $messages[$pass->status] ?? __('This pass is not valid.'));
        }
        if (!Carbon::parse($pass->visit_date)->isToday()) {
            return redirect()->back()->with('error', __('This pass is for :date, not today.', ['date' => dateFormat($pass->visit_date)]));
        }
        $pass->status = 'checked_in';
        $pass->checked_in_at = now();
        $pass->checked_in_by = \Auth::user()->id;
        $pass->save();
        return redirect()->back()->with('success', __(':name checked in for :unit.', ['name' => $pass->visitor_name, 'unit' => optional($pass->units)->name]));
    }

    // gate: register a walk-in visitor (no pass) and check them in
    public function walkIn(Request $request)
    {
        if ($this->tenant() || !\Auth::user()->can('manage visitor')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'tenant_id' => 'required',
            'visitor_name' => 'required|max:191',
            'visitor_phone' => 'nullable|max:50',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $tenant = Tenant::where('parent_id', parentId())->find($request->tenant_id);
        if (!$tenant) {
            return redirect()->back()->with('error', __('Tenant not found.'));
        }
        VisitorPass::create($request->only('visitor_name', 'visitor_phone', 'purpose', 'vehicle_number') + [
            'tenant_id' => $tenant->id,
            'property_id' => $tenant->property,
            'unit_id' => $tenant->unit,
            'visit_date' => Carbon::today()->toDateString(),
            'code' => VisitorPass::newCode(parentId()),
            'status' => 'checked_in',
            'checked_in_at' => now(),
            'checked_in_by' => \Auth::user()->id,
            'parent_id' => parentId(),
        ]);
        return redirect()->back()->with('success', __('Walk-in visitor checked in.'));
    }

    public function checkOut($id)
    {
        if ($this->tenant() || !\Auth::user()->can('manage visitor')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $pass = VisitorPass::where('parent_id', parentId())->where('id', $id)->where('status', 'checked_in')->first();
        if (!$pass) {
            return redirect()->back()->with('error', __('This visitor is not checked in.'));
        }
        $pass->status = 'checked_out';
        $pass->checked_out_at = now();
        $pass->save();
        return redirect()->back()->with('success', __(':name checked out.', ['name' => $pass->visitor_name]));
    }
}
