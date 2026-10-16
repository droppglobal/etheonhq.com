<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\Tenant;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    private function isTenant()
    {
        return \Auth::user()->type == 'tenant';
    }

    // Owner staff see every inspection of the account; a tenant only their own completed ones.
    private function findVisible($id)
    {
        $query = Inspection::where('parent_id', parentId())->where('id', $id);
        if ($this->isTenant()) {
            $query->where('tenant_id', optional(\Auth::user()->tenants)->id)->where('status', 'completed');
        } elseif (\Auth::user()->type == 'maintainer') {
            return null;
        }
        return $query->first();
    }

    private function findEditable($id)
    {
        if ($this->isTenant() || !\Auth::user()->can('edit inspection')) {
            return null;
        }
        return Inspection::where('parent_id', parentId())->where('id', $id)->first();
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage inspection')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $query = Inspection::where('parent_id', parentId())->orderBy('inspection_date', 'desc')->orderBy('id', 'desc');
        if ($this->isTenant()) {
            $query->where('tenant_id', optional(\Auth::user()->tenants)->id)->where('status', 'completed');
        }
        if (!empty($request->tenant_id)) {
            $query->where('tenant_id', $request->tenant_id);
        }
        $inspections = $query->get();
        return view('inspection.index', compact('inspections'));
    }

    public function create(Request $request)
    {
        if (!\Auth::user()->can('create inspection')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $tenants = Tenant::where('parent_id', parentId())->with('user', 'units', 'properties')->get()->mapWithKeys(function ($tenant) {
            $label = optional($tenant->user)->name . ' - ' . optional($tenant->properties)->name . ' / ' . optional($tenant->units)->name;
            return [$tenant->id => $label . (!empty($tenant->exit_date) ? ' (' . __('exited') . ')' : '')];
        });
        $types = Inspection::types();
        $selectedTenant = $request->tenant_id;
        return view('inspection.create', compact('tenants', 'types', 'selectedTenant'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create inspection')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'tenant_id' => 'required',
            'type' => 'required|in:' . implode(',', array_keys(Inspection::types())),
            'inspection_date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $tenant = Tenant::where('parent_id', parentId())->find($request->tenant_id);
        if (!$tenant) {
            return redirect()->back()->with('error', __('Tenant not found.'));
        }

        $inspection = Inspection::create([
            'tenant_id' => $tenant->id,
            'property_id' => $tenant->property,
            'unit_id' => $tenant->unit,
            'type' => $request->type,
            'inspection_date' => $request->inspection_date,
            'status' => 'draft',
            'parent_id' => parentId(),
        ]);

        // a move-out starts from the move-in checklist so rooms line up for comparison
        $baseline = $inspection->type == 'move_out' ? $inspection->baseline() : null;
        $order = 0;
        if ($baseline && $baseline->items->count()) {
            foreach ($baseline->items as $item) {
                InspectionItem::create(['inspection_id' => $inspection->id, 'area' => $item->area, 'item' => $item->item, 'condition' => 'good', 'sort_order' => $order++]);
            }
        } else {
            foreach (Inspection::defaultChecklist() as $area => $items) {
                foreach ($items as $item) {
                    InspectionItem::create(['inspection_id' => $inspection->id, 'area' => $area, 'item' => $item, 'condition' => 'good', 'sort_order' => $order++]);
                }
            }
        }

        return redirect()->route('inspection.edit', $inspection->id)->with('success', __('Inspection created. Fill in the checklist below.'));
    }

    public function show($id)
    {
        $inspection = $this->findVisible($id);
        if (!\Auth::user()->can('manage inspection') || !$inspection) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $baseline = $inspection->type == 'move_out' ? $inspection->baseline() : null;
        $baselineItems = $baseline ? $baseline->items->keyBy(function ($item) {
            return mb_strtolower($item->area . '|' . $item->item);
        }) : collect();
        return view('inspection.show', compact('inspection', 'baseline', 'baselineItems'));
    }

    public function edit($id)
    {
        $inspection = $this->findEditable($id);
        if (!$inspection) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!empty($inspection->tenant_signed_at)) {
            return redirect()->route('inspection.show', $inspection->id)->with('error', __('The tenant has signed this inspection, so it can no longer be changed.'));
        }
        return view('inspection.edit', compact('inspection'));
    }

    public function update(Request $request, $id)
    {
        $inspection = $this->findEditable($id);
        if (!$inspection || !empty($inspection->tenant_signed_at)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'inspection_date' => 'required|date',
            'items' => 'nullable|array',
            'items.*.condition' => 'nullable|in:' . implode(',', array_keys(Inspection::conditions())),
            'photos.*' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        foreach ((array) $request->items as $itemId => $data) {
            $item = InspectionItem::where('inspection_id', $inspection->id)->find($itemId);
            if (!$item) {
                continue;
            }
            if (!empty($data['remove'])) {
                if (!empty($item->photo)) {
                    deleteOldFile($item->photo, 'upload/inspection/');
                }
                $item->delete();
                continue;
            }
            $item->condition = $data['condition'] ?? $item->condition;
            $item->notes = $data['notes'] ?? null;
            if ($request->hasFile('photos.' . $itemId)) {
                $upload = handleFileUpload($request->file('photos.' . $itemId), 'upload/inspection/', ['mimes:jpg,jpeg,png', 'max:10240']);
                if ($upload['flag'] == 0) {
                    return redirect()->back()->with('error', $upload['msg']);
                }
                if (!empty($item->photo)) {
                    deleteOldFile($item->photo, 'upload/inspection/');
                }
                $item->photo = $upload['filename'];
            }
            $item->save();
        }

        // extra rows typed in by the inspector
        $order = (int) InspectionItem::where('inspection_id', $inspection->id)->max('sort_order');
        foreach ((array) $request->new_items as $new) {
            if (empty($new['area']) || empty($new['item'])) {
                continue;
            }
            InspectionItem::create([
                'inspection_id' => $inspection->id,
                'area' => $new['area'],
                'item' => $new['item'],
                'condition' => array_key_exists($new['condition'] ?? '', Inspection::conditions()) ? $new['condition'] : 'good',
                'notes' => $new['notes'] ?? null,
                'sort_order' => ++$order,
            ]);
        }

        $inspection->inspection_date = $request->inspection_date;
        $inspection->notes = $request->notes;
        if ($request->input('action') == 'complete') {
            $inspection->status = 'completed';
        }
        $inspection->save();

        if ($inspection->status == 'completed') {
            return redirect()->route('inspection.show', $inspection->id)->with('success', __('Inspection completed. The tenant can now review and sign it.'));
        }
        return redirect()->back()->with('success', __('Inspection saved.'));
    }

    public function destroy($id)
    {
        $inspection = Inspection::where('parent_id', parentId())->find($id);
        if ($this->isTenant() || !\Auth::user()->can('delete inspection') || !$inspection) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        foreach ($inspection->items as $item) {
            if (!empty($item->photo)) {
                deleteOldFile($item->photo, 'upload/inspection/');
            }
            $item->delete();
        }
        $inspection->delete();
        return redirect()->route('inspection.index')->with('success', __('Inspection successfully deleted.'));
    }

    // Tenant confirms the inspection by typing their name
    public function sign(Request $request, $id)
    {
        $inspection = $this->findVisible($id);
        if (!$this->isTenant() || !$inspection || !empty($inspection->tenant_signed_at)) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'signed_name' => 'required|max:191',
            'agree' => 'accepted',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $inspection->tenant_signed_name = $request->signed_name;
        $inspection->tenant_signed_at = now();
        $inspection->tenant_signed_ip = $request->ip();
        $inspection->save();

        return redirect()->back()->with('success', __('Thank you, the inspection has been signed.'));
    }
}
