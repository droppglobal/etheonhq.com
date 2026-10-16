<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Type;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage expense')) {

            if ($request->ajax()) {
                $query = scopeLandlord(Expense::where('parent_id', parentId()));
                if (!empty($request->property))
                    $query->where('property_id', $request->property);

                if (!empty($request->unit))
                    $query->where('unit_id', $request->unit);

                if (!empty($request->type))
                    $query->where('type', $request->type);

                if (!empty($request->date)) {
                    [$start, $end] = explode(' - ', $request->date);
                    $query->whereBetween('date', [
                        \Carbon\Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d'),
                        \Carbon\Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d'),
                    ]);
                }

                $expenses = $query->orderBy('id', 'desc')->get();

                return response()->json([
                    'html' => view('expense.expense_list', compact('expenses'))->render()
                ]);
            }

            $properties = scopeLandlord(Property::where('parent_id', parentId()), 'id')->pluck('name', 'id');
            $properties->prepend(__('Select Property'), '');

            $units = scopeLandlord(PropertyUnit::where('parent_id', parentId()))->pluck('name', 'id');
            $units->prepend(__('Select Unit'), '');

            $types = Type::where('parent_id', parentId())->where('type', 'expense')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            return view('expense.index', compact('types', 'properties', 'units'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create expense')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'expense')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $billNumber = $this->expenseNumber();
            $vendors = \App\Models\Vendor::where('parent_id', parentId())->where('is_active', 1)->pluck('name', 'id');
            return view('expense.create', compact('types', 'property', 'billNumber', 'vendors'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    // one bill shared by several properties: an expense per property with its share
    private function storeSplit(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'required',
            'expense_type' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'split_properties' => 'required|array|min:2',
        ], ['split_properties.min' => __('Choose at least two properties to split between.')]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }
        $properties = Property::where('parent_id', parentId())->whereIn('id', (array) $request->split_properties)->get();
        if ($properties->count() < 2) {
            return redirect()->back()->with('error', __('Choose at least two properties to split between.'));
        }

        $weights = $properties->mapWithKeys(fn($p) => [$p->id => $request->split_by == 'units' ? max(1, \App\Models\PropertyUnit::where('property_id', $p->id)->count()) : 1]);
        $total = round((float) $request->amount, 2);
        $sum = $weights->sum();
        $shares = [];
        $given = 0;
        foreach ($properties->values() as $i => $p) {
            // rounding difference goes to the last share so the parts add up exactly
            $share = $i == $properties->count() - 1 ? round($total - $given, 2) : round($total * $weights[$p->id] / $sum, 2);
            $shares[$p->id] = $share;
            $given += $share;
        }

        $receipt = null;
        if ($request->hasFile('receipt')) {
            $uploadResult = handleFileUpload($request->file('receipt'), 'upload/receipt/');
            if ($uploadResult['flag'] == 0) {
                return redirect()->back()->with('error', $uploadResult['msg']);
            }
            $receipt = $uploadResult['filename'];
        }
        $group = (string) \Illuminate\Support\Str::uuid();
        $vendorId = !empty($request->vendor_id) && \App\Models\Vendor::where('parent_id', parentId())->where('id', $request->vendor_id)->exists() ? (int) $request->vendor_id : 0;
        $n = 1;
        foreach ($properties as $p) {
            $expense = new Expense();
            $expense->title = $request->title;
            $expense->expense_id = $request->expense_id;
            $expense->property_id = $p->id;
            $expense->unit_id = 0;
            $expense->expense_type = $request->expense_type;
            $expense->vendor_id = $vendorId;
            $expense->amount = $shares[$p->id];
            $expense->date = $request->date;
            $expense->receipt = $receipt;
            $expense->notes = trim(__('Share :n of :count of a :total bill split :how.', [
                'n' => $n++, 'count' => $properties->count(), 'total' => priceFormat($total),
                'how' => $request->split_by == 'units' ? __('by number of units') : __('equally'),
            ]) . "\n" . $request->notes);
            $expense->split_group = $group;
            $expense->parent_id = parentId();
            $expense->save();
        }
        return redirect()->back()->with('success', __('Expense split across :count properties.', ['count' => $properties->count()]));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create expense')) {
            if ($request->boolean('split')) {
                return $this->storeSplit($request);
            }
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'expense_type' => 'required',
                    'amount' => 'required',
                    'date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $expense = new Expense();
            $expense->title = $request->title;
            $expense->expense_id = $request->expense_id;
            $expense->property_id = $request->property_id;
            $expense->unit_id = $request->unit_id;
            $expense->expense_type = $request->expense_type;
            $expense->vendor_id = !empty($request->vendor_id) && \App\Models\Vendor::where('parent_id', parentId())->where('id', $request->vendor_id)->exists() ? (int) $request->vendor_id : 0;
            $expense->amount = $request->amount;
            $expense->date = $request->date;

            if ($request->hasFile('receipt')) {
                $uploadResult = handleFileUpload($request->file('receipt'), 'upload/receipt/');

                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                $expense->receipt = $uploadResult['filename'];
            }
            $expense->notes = $request->notes;
            $expense->parent_id = parentId();
            $expense->save();

            return redirect()->back()->with('success', __('Expense successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function show(Expense $expense)
    {
        // only this company's expenses (and, for a landlord, those of their own properties)
        $own = scopeLandlord(Expense::where('parent_id', parentId())->where('id', $expense->id))->exists();
        if (\Auth::user()->can('show expense') && $own) {
            return view('expense.show', compact('expense'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function edit(Expense $expense)
    {
        if (\Auth::user()->can('edit expense')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'expense')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $billNumber = $expense->expense_id;
            $vendors = \App\Models\Vendor::where('parent_id', parentId())->pluck('name', 'id');
            return view('expense.edit', compact('types', 'property', 'billNumber', 'expense', 'vendors'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function update(Request $request, Expense $expense)
    {
        if (\Auth::user()->can('edit expense')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'title' => 'required',
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'expense_type' => 'required',
                    'amount' => 'required',
                    'date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if ($request->hasFile('receipt')) {

                $uploadResult = handleFileUpload($request->file('receipt'), 'upload/receipt/');
                if ($uploadResult['flag'] == 0) {
                    return redirect()->back()->with('error', $uploadResult['msg']);
                }
                if (!empty($expense->receipt)) {
                    deleteOldFile($expense->receipt, 'upload/receipt/');
                }
                $expense->receipt = $uploadResult['filename'];
            }
            $expense->title = $request->title;
            $expense->expense_id = $request->expense_id;
            $expense->property_id = $request->property_id;
            $expense->unit_id = $request->unit_id;
            $expense->expense_type = $request->expense_type;
            $expense->vendor_id = !empty($request->vendor_id) && \App\Models\Vendor::where('parent_id', parentId())->where('id', $request->vendor_id)->exists() ? (int) $request->vendor_id : 0;
            $expense->amount = $request->amount;
            $expense->date = $request->date;
            $expense->notes = $request->notes;
            $expense->save();

            return redirect()->back()->with('success', __('Expense successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function destroy(Expense $expense)
    {
        if (\Auth::user()->can('delete expense')) {
            if ($expense) {
                if (!empty($expense->receipt)) {
                    deleteOldFile($expense->receipt, 'upload/receipt/');
                }
                $expense->delete();
            }


            return redirect()->back()->with('success', __('Expense successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function expenseNumber()
    {
        $latest = Expense::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->expense_id + 1;
        }
    }
}
