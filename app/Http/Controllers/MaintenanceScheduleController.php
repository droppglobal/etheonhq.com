<?php

namespace App\Http\Controllers;

use App\Models\Maintainer;
use App\Models\MaintenanceSchedule;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Type;
use App\Models\Vendor;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    private function findOwn($id)
    {
        return MaintenanceSchedule::where('parent_id', parentId())->where('id', $id)->first();
    }

    private function formData()
    {
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id');
        $issueTypes = Type::where('parent_id', parentId())->where('type', 'issue')->pluck('title', 'id');
        $maintainers = Maintainer::where('parent_id', parentId())->with('user')->get()->mapWithKeys(function ($maintainer) {
            return [$maintainer->user_id => optional($maintainer->user)->name];
        });
        $vendors = Vendor::where('parent_id', parentId())->where('is_active', 1)->pluck('name', 'id');
        $frequencies = MaintenanceSchedule::frequencies();
        return compact('properties', 'issueTypes', 'maintainers', 'vendors', 'frequencies');
    }

    private function validated(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'required|max:191',
            'property_id' => 'required',
            'issue_type' => 'required',
            'frequency' => 'required|in:' . implode(',', array_keys(MaintenanceSchedule::frequencies())),
            'next_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return $validator->getMessageBag()->first();
        }
        if (!Property::where('parent_id', parentId())->where('id', $request->property_id)->exists()) {
            return __('Property not found.');
        }
        if (!empty($request->unit_id) && !PropertyUnit::where('parent_id', parentId())->where('property_id', $request->property_id)->where('id', $request->unit_id)->exists()) {
            return __('Invalid property unit selected.');
        }
        if (!Type::where('parent_id', parentId())->where('type', 'issue')->where('id', $request->issue_type)->exists()) {
            return __('Invalid issue type.');
        }
        if (!empty($request->maintainer_id) && !Maintainer::where('parent_id', parentId())->where('user_id', $request->maintainer_id)->exists()) {
            return __('Invalid maintainer selected.');
        }
        if (!empty($request->vendor_id) && !Vendor::where('parent_id', parentId())->where('id', $request->vendor_id)->exists()) {
            return __('Invalid vendor selected.');
        }
        return null;
    }

    private function fields(Request $request)
    {
        return [
            'title' => $request->title,
            'property_id' => $request->property_id,
            'unit_id' => $request->unit_id ?: 0,
            'issue_type' => $request->issue_type,
            'maintainer_id' => $request->maintainer_id ?: 0,
            'vendor_id' => $request->vendor_id ?: 0,
            'frequency' => $request->frequency,
            'next_date' => $request->next_date,
            'notes' => $request->notes,
        ];
    }

    public function index()
    {
        if (!\Auth::user()->can('manage maintenance schedule')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $schedules = MaintenanceSchedule::where('parent_id', parentId())->orderBy('is_active', 'desc')->orderBy('next_date')->get();
        return view('maintenance_schedule.index', compact('schedules'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create maintenance schedule')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        return view('maintenance_schedule.form', ['schedule' => null] + $this->formData());
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create maintenance schedule')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if ($error = $this->validated($request)) {
            return redirect()->back()->with('error', $error);
        }
        MaintenanceSchedule::create($this->fields($request) + ['is_active' => 1, 'parent_id' => parentId()]);
        return redirect()->back()->with('success', __('Schedule successfully created.'));
    }

    public function edit($id)
    {
        $schedule = $this->findOwn($id);
        if (!\Auth::user()->can('edit maintenance schedule') || !$schedule) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        return view('maintenance_schedule.form', ['schedule' => $schedule] + $this->formData());
    }

    public function update(Request $request, $id)
    {
        $schedule = $this->findOwn($id);
        if (!\Auth::user()->can('edit maintenance schedule') || !$schedule) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if ($error = $this->validated($request)) {
            return redirect()->back()->with('error', $error);
        }
        $schedule->fill($this->fields($request) + ['is_active' => $request->has('is_active') ? 1 : 0])->save();
        return redirect()->back()->with('success', __('Schedule successfully updated.'));
    }

    public function destroy($id)
    {
        $schedule = $this->findOwn($id);
        if (!\Auth::user()->can('delete maintenance schedule') || !$schedule) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $schedule->delete();
        return redirect()->back()->with('success', __('Schedule successfully deleted.'));
    }
}
