<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadViewing;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    private function findOwnLead($id)
    {
        return Lead::where('parent_id', parentId())->where('id', $id)->first();
    }

    // property and unit must belong to this account; unit is optional
    private function validPlace($propertyId, $unitId)
    {
        if (empty($propertyId)) {
            return empty($unitId);
        }
        if (!Property::where('parent_id', parentId())->where('id', $propertyId)->exists()) {
            return false;
        }
        return empty($unitId) || PropertyUnit::where('parent_id', parentId())->where('property_id', $propertyId)->where('id', $unitId)->exists();
    }

    private function propertyOptions()
    {
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id');
        $properties->prepend(__('Select Property'), '');
        return $properties;
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage lead')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $query = Lead::where('parent_id', parentId())->orderBy('id', 'desc');
        if (!empty($request->status) && array_key_exists($request->status, Lead::statuses())) {
            $query->where('status', $request->status);
        }
        if (!empty($request->property)) {
            $query->where('property_id', $request->property);
        }
        $leads = $query->get();

        $counts = Lead::where('parent_id', parentId())->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $statuses = Lead::statuses();
        $properties = $this->propertyOptions();

        return view('lead.index', compact('leads', 'counts', 'statuses', 'properties'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create lead')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $lead = null;
        $properties = $this->propertyOptions();
        $statuses = Lead::statuses();
        $sources = Lead::sources();
        return view('lead.form', compact('lead', 'properties', 'statuses', 'sources'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create lead')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'name' => 'required|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|max:50',
            'follow_up_date' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(Lead::statuses())),
            'source' => 'required|in:' . implode(',', array_keys(Lead::sources())),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (!$this->validPlace($request->property_id, $request->unit_id)) {
            return redirect()->back()->with('error', __('Invalid property unit selected.'));
        }

        Lead::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'property_id' => $request->property_id ?: 0,
            'unit_id' => $request->unit_id ?: 0,
            'source' => $request->source,
            'status' => $request->status,
            'message' => $request->message,
            'notes' => $request->notes,
            'follow_up_date' => $request->follow_up_date ?: null,
            'last_activity_at' => now(),
            'parent_id' => parentId(),
        ]);

        return redirect()->back()->with('success', __('Lead successfully created.'));
    }

    public function show($id)
    {
        $lead = $this->findOwnLead($id);
        if (!\Auth::user()->can('manage lead') || !$lead) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $viewingStatuses = LeadViewing::statuses();
        return view('lead.show', compact('lead', 'viewingStatuses'));
    }

    public function edit($id)
    {
        $lead = $this->findOwnLead($id);
        if (!\Auth::user()->can('edit lead') || !$lead) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $properties = $this->propertyOptions();
        $statuses = Lead::statuses();
        $sources = Lead::sources();
        return view('lead.form', compact('lead', 'properties', 'statuses', 'sources'));
    }

    public function update(Request $request, $id)
    {
        $lead = $this->findOwnLead($id);
        if (!\Auth::user()->can('edit lead') || !$lead) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'name' => 'required|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|max:50',
            'follow_up_date' => 'nullable|date',
            'status' => 'required|in:' . implode(',', array_keys(Lead::statuses())),
            'source' => 'required|in:' . implode(',', array_keys(Lead::sources())),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (!$this->validPlace($request->property_id, $request->unit_id)) {
            return redirect()->back()->with('error', __('Invalid property unit selected.'));
        }

        $lead->fill([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'property_id' => $request->property_id ?: 0,
            'unit_id' => $request->unit_id ?: 0,
            'source' => $request->source,
            'status' => $request->status,
            'notes' => $request->notes,
            'follow_up_date' => $request->follow_up_date ?: null,
            'last_activity_at' => now(),
        ])->save();

        return redirect()->back()->with('success', __('Lead successfully updated.'));
    }

    public function destroy($id)
    {
        $lead = $this->findOwnLead($id);
        if (!\Auth::user()->can('delete lead') || !$lead) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        LeadViewing::where('lead_id', $lead->id)->delete();
        $lead->delete();
        return redirect()->route('lead.index')->with('success', __('Lead successfully deleted.'));
    }

    // ---------------- Viewings ----------------

    public function viewingCreate($leadId)
    {
        $lead = $this->findOwnLead($leadId);
        if (!\Auth::user()->can('edit lead') || !$lead) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $viewing = null;
        $statuses = LeadViewing::statuses();
        return view('lead.viewing', compact('lead', 'viewing', 'statuses'));
    }

    public function viewingStore(Request $request, $leadId)
    {
        $lead = $this->findOwnLead($leadId);
        if (!\Auth::user()->can('edit lead') || !$lead) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'viewing_date' => 'required|date',
            'status' => 'required|in:' . implode(',', array_keys(LeadViewing::statuses())),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        LeadViewing::create([
            'lead_id' => $lead->id,
            'property_id' => $lead->property_id,
            'unit_id' => $lead->unit_id,
            'viewing_date' => $request->viewing_date,
            'viewing_time' => $request->viewing_time,
            'status' => $request->status,
            'notes' => $request->notes,
            'parent_id' => parentId(),
        ]);

        if (in_array($lead->status, ['new', 'contacted'])) {
            $lead->status = 'viewing';
        }
        $lead->touchActivity();

        return redirect()->back()->with('success', __('Viewing successfully saved.'));
    }

    public function viewingEdit($id)
    {
        $viewing = LeadViewing::where('parent_id', parentId())->find($id);
        if (!\Auth::user()->can('edit lead') || !$viewing) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $lead = $viewing->lead;
        $statuses = LeadViewing::statuses();
        return view('lead.viewing', compact('lead', 'viewing', 'statuses'));
    }

    public function viewingUpdate(Request $request, $id)
    {
        $viewing = LeadViewing::where('parent_id', parentId())->find($id);
        if (!\Auth::user()->can('edit lead') || !$viewing) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'viewing_date' => 'required|date',
            'status' => 'required|in:' . implode(',', array_keys(LeadViewing::statuses())),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $viewing->fill($request->only('viewing_date', 'viewing_time', 'status', 'notes'))->save();
        optional($viewing->lead)->touchActivity();

        return redirect()->back()->with('success', __('Viewing successfully updated.'));
    }

    public function viewingDestroy($id)
    {
        $viewing = LeadViewing::where('parent_id', parentId())->find($id);
        if (!\Auth::user()->can('edit lead') || !$viewing) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $viewing->delete();
        return redirect()->back()->with('success', __('Viewing successfully deleted.'));
    }
}
