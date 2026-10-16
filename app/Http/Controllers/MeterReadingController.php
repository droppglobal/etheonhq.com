<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MeterReading;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use App\Models\Type;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    private function findOwn($id)
    {
        return MeterReading::where('parent_id', parentId())->where('id', $id)->first();
    }

    private function lastReadingFor($unitId, $utility)
    {
        return MeterReading::where('parent_id', parentId())->where('unit_id', $unitId)->where('utility_type', $utility)
            ->orderBy('reading_date', 'desc')->orderBy('id', 'desc')->first();
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage meter reading')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $query = MeterReading::where('parent_id', parentId())->orderBy('reading_date', 'desc')->orderBy('id', 'desc');
        if (!empty($request->property)) {
            $query->where('property_id', $request->property);
        }
        if ($request->billed === '0') {
            $query->where('invoice_id', 0);
        }
        $readings = $query->get();
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id')->prepend(__('All Properties'), '');
        return view('meter_reading.index', compact('readings', 'properties'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create meter reading')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $properties = Property::where('parent_id', parentId())->pluck('name', 'id')->prepend(__('Select Property'), '');
        $utilityTypes = MeterReading::utilityTypes();
        return view('meter_reading.create', compact('properties', 'utilityTypes'));
    }

    // previous reading and last rate, used to prefill the form
    public function last(Request $request)
    {
        if (!\Auth::user()->can('create meter reading')) {
            return response()->json([], 403);
        }
        $last = $this->lastReadingFor($request->unit_id, $request->utility_type);
        return response()->json([
            'previous_reading' => $last ? (float) $last->current_reading : 0,
            'rate' => $last ? (float) $last->rate : '',
            'last_date' => $last ? dateFormat($last->reading_date) : null,
        ]);
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create meter reading')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $validator = \Validator::make($request->all(), [
            'property_id' => 'required',
            'unit_id' => 'required',
            'utility_type' => 'required|in:' . implode(',', array_keys(MeterReading::utilityTypes())),
            'reading_date' => 'required|date',
            'previous_reading' => 'required|numeric|min:0',
            'current_reading' => 'required|numeric|gte:previous_reading',
            'rate' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        if (!PropertyUnit::where('parent_id', parentId())->where('property_id', $request->property_id)->where('id', $request->unit_id)->exists()) {
            return redirect()->back()->with('error', __('Invalid property unit selected.'));
        }

        MeterReading::create([
            'property_id' => $request->property_id,
            'unit_id' => $request->unit_id,
            'utility_type' => $request->utility_type,
            'reading_date' => $request->reading_date,
            'previous_reading' => $request->previous_reading,
            'current_reading' => $request->current_reading,
            'rate' => $request->rate,
            'amount' => round(($request->current_reading - $request->previous_reading) * $request->rate, 2),
            'notes' => $request->notes,
            'parent_id' => parentId(),
        ]);

        return redirect()->back()->with('success', __('Meter reading successfully saved.'));
    }

    public function destroy($id)
    {
        $reading = $this->findOwn($id);
        if (!\Auth::user()->can('delete meter reading') || !$reading) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!empty($reading->invoice_id)) {
            return redirect()->back()->with('error', __('This reading is already on an invoice.'));
        }
        $reading->delete();
        return redirect()->back()->with('success', __('Meter reading successfully deleted.'));
    }

    /**
     * Adds the charge to the unit's latest open invoice, or creates a new invoice for the current tenant.
     */
    public function bill($id)
    {
        $reading = $this->findOwn($id);
        if (!\Auth::user()->can('create meter reading') || !\Auth::user()->can('create invoice') || !$reading) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        if (!empty($reading->invoice_id)) {
            return redirect()->back()->with('error', __('This reading is already on an invoice.'));
        }
        if ($reading->amount <= 0) {
            return redirect()->back()->with('error', __('There is nothing to bill for this reading.'));
        }

        $invoice = Invoice::where('parent_id', parentId())->where('unit_id', $reading->unit_id)
            ->whereIn('status', ['unpaid', 'partial_paid'])->orderBy('id', 'desc')->first();

        if (!$invoice) {
            $tenant = Tenant::where('parent_id', parentId())->where('unit', $reading->unit_id)->whereNull('exit_date')->orderBy('id', 'desc')->first();
            if (!$tenant) {
                return redirect()->back()->with('error', __('No open invoice and no current tenant for this unit.'));
            }
            $invoice = new Invoice();
            $invoice->invoice_id = invoiceNumber();
            $invoice->property_id = $reading->property_id;
            $invoice->unit_id = $reading->unit_id;
            $invoice->tenant = $tenant->id;
            $invoice->invoice_month = date('Y-m-01');
            $invoice->end_date = now()->addDays(7)->format('Y-m-d');
            $invoice->notes = __('Utility charges');
            $invoice->status = 'unpaid';
            $invoice->parent_id = parentId();
            $invoice->save();
        }

        $label = MeterReading::utilityTypes()[$reading->utility_type] ?? ucfirst($reading->utility_type);
        $type = Type::firstOrCreate(['parent_id' => parentId(), 'type' => 'invoice', 'title' => $label]);
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'invoice_type' => $type->id,
            'amount' => $reading->amount,
            'description' => $label . ': ' . rtrim(rtrim(number_format($reading->consumption, 2, '.', ''), '0'), '.') . ' x ' . (float) $reading->rate
                . ' (' . dateFormat($reading->reading_date) . ')',
        ]);

        // the total grew, so a fully paid status no longer applies
        Invoice::refreshStatus($invoice->id);

        $reading->invoice_id = $invoice->id;
        $reading->save();

        return redirect()->back()->with('success', __('Added to invoice') . ' ' . invoicePrefix() . $invoice->invoice_id);
    }
}
