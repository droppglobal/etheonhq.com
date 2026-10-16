<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    private function findOwn($id)
    {
        return Vendor::where('parent_id', parentId())->where('id', $id)->first();
    }

    private function rules()
    {
        return [
            'name' => 'required|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|max:50',
            'company' => 'nullable|max:191',
            'service' => 'nullable|max:191',
        ];
    }

    public function index()
    {
        if (!\Auth::user()->can('manage vendor')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $vendors = Vendor::where('parent_id', parentId())->orderBy('name')->get();
        $spent = Expense::where('parent_id', parentId())->where('vendor_id', '>', 0)->selectRaw('vendor_id, SUM(amount) as total')->groupBy('vendor_id')->pluck('total', 'vendor_id');
        $jobs = MaintenanceRequest::where('parent_id', parentId())->where('vendor_id', '>', 0)->selectRaw('vendor_id, COUNT(*) as total')->groupBy('vendor_id')->pluck('total', 'vendor_id');
        return view('vendor.index', compact('vendors', 'spent', 'jobs'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create vendor')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $vendor = null;
        return view('vendor.form', compact('vendor'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create vendor')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        Vendor::create($request->only('name', 'company', 'email', 'phone', 'service', 'address', 'notes') + ['is_active' => 1, 'parent_id' => parentId()]);
        return redirect()->back()->with('success', __('Vendor successfully created.'));
    }

    public function show($id)
    {
        $vendor = $this->findOwn($id);
        if (!\Auth::user()->can('manage vendor') || !$vendor) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $expenses = Expense::where('parent_id', parentId())->where('vendor_id', $vendor->id)->orderBy('date', 'desc')->get();
        $jobs = MaintenanceRequest::where('parent_id', parentId())->where('vendor_id', $vendor->id)->orderBy('id', 'desc')->get();
        return view('vendor.show', compact('vendor', 'expenses', 'jobs'));
    }

    public function edit($id)
    {
        $vendor = $this->findOwn($id);
        if (!\Auth::user()->can('edit vendor') || !$vendor) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        return view('vendor.form', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $vendor = $this->findOwn($id);
        if (!\Auth::user()->can('edit vendor') || !$vendor) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $vendor->fill($request->only('name', 'company', 'email', 'phone', 'service', 'address', 'notes') + ['is_active' => $request->has('is_active') ? 1 : 0])->save();
        return redirect()->back()->with('success', __('Vendor successfully updated.'));
    }

    public function destroy($id)
    {
        $vendor = $this->findOwn($id);
        if (!\Auth::user()->can('delete vendor') || !$vendor) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        // keep history: unlink instead of deleting expenses and jobs
        Expense::where('parent_id', parentId())->where('vendor_id', $vendor->id)->update(['vendor_id' => 0]);
        MaintenanceRequest::where('parent_id', parentId())->where('vendor_id', $vendor->id)->update(['vendor_id' => 0]);
        \App\Models\MaintenanceSchedule::where('parent_id', parentId())->where('vendor_id', $vendor->id)->update(['vendor_id' => 0]);
        $vendor->delete();
        return redirect()->route('vendor.index')->with('success', __('Vendor successfully deleted.'));
    }
}
