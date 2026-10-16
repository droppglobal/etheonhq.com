<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function income(Request $request)
    {
        if (!\Auth::user()->can('manage income report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $query = Invoice::where('parent_id', parentId());

        // Filters
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('month')) {
            $month = Carbon::parse($request->month);
            $query->whereMonth('invoice_month', $month->month)
                ->whereYear('invoice_month', $month->year);
        }

        if (!empty($request->date)) {
            [$start, $end] = explode(' - ', $request->date);
            $query->whereBetween('end_date', [
                Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d'),
                Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d'),
            ]);
        }

        $countQuery = clone $query;

        if ($request->ajax()) {

            $invoices = $query->orderByDesc('id')->get();
            $count = $countQuery->count();

            $html = view('report.income_report', compact('invoices', 'count'))->render();

            return response()->json([
                'html' => $html,
                'count' => $count
            ]);
        }



        $property = Property::where('parent_id', parentId())
            ->pluck('name', 'id')
            ->prepend(__('Select Property'), '');

        $units = [];
        if ($request->filled('property_id')) {
            $units = PropertyUnit::where('property_id', $request->property_id)
                ->pluck('name', 'id');
        }

        return view('report.income', compact('property', 'units'));
    }

    public function expense(Request $request)
    {
        if (!\Auth::user()->can('manage income report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $property = Property::where('parent_id', parentId())->pluck('name', 'id');
        $property->prepend(__('Select Property'), 0);

        $expenses = Expense::where('parent_id', parentId());

        if ($request->filled('property_id') && $request->property_id != 0) {
            $expenses->where('property_id', $request->property_id);
        }

        if ($request->filled('unit_id') && $request->unit_id != 0) {
            $expenses->where('unit_id', $request->unit_id);
        }

        if (!empty($request->date)) {
            [$start, $end] = explode(' - ', $request->date);
            $expenses->whereBetween('date', [
                Carbon::createFromFormat('m/d/Y', trim($start))->format('Y-m-d'),
                Carbon::createFromFormat('m/d/Y', trim($end))->format('Y-m-d'),
            ]);
        }

        $units = [];
        if ($request->filled('property_id')) {
            $units = PropertyUnit::where('property_id', $request->property_id)
                ->where('parent_id', parentId())
                ->pluck('name', 'id');
        }

        if ($request->ajax()) {
            $expenses = $expenses->orderBy('id', 'desc')->get();
            $count = $expenses->count();

            $html = view('report.expense_report', compact('expenses', 'count'))->render();

            return response()->json([
                'html' => $html,
                'count' => $count
            ]);
        }

        return view('report.expense', compact('expenses', 'property', 'units'));
    }

    public function reportProfitLoss(Request $request)
    {
        if (!\Auth::user()->can('manage profit loss report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }

        $year = $request->get('year', now()->year);

        $incomeQuery = InvoicePayment::selectRaw('MONTH(invoice_payments.payment_date) as month, SUM(invoice_payments.amount) as income')
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->whereYear('invoice_payments.payment_date', $year);

        $expenseQuery = Expense::selectRaw('MONTH(date) as month, SUM(amount) as expense')
            ->whereYear('date', $year);

        // Filters
        if ($request->filled('property_id')) {
            $incomeQuery->where('invoices.property_id', $request->property_id);
            $expenseQuery->where('property_id', $request->property_id);
        }

        if ($request->filled('unit_id')) {
            $incomeQuery->where('invoices.unit_id', $request->unit_id);
            $expenseQuery->where('unit_id', $request->unit_id);
        }

        $incomeData = $incomeQuery->groupBy('month')->pluck('income', 'month');
        $expenseData = $expenseQuery->groupBy('month')->pluck('expense', 'month');

        $report = [];
        for ($m = 1; $m <= 12; $m++) {
            $income = $incomeData[$m] ?? 0;
            $expense = $expenseData[$m] ?? 0;

            if ($income == 0 && $expense == 0)
                continue;

            $report[] = (object) [
                'month' => date('F Y', mktime(0, 0, 0, $m, 1, $year)),
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
            ];
        }

        if ($request->ajax()) {
            $chartData = $this->incomeByMonth($year, $request->property_id, $request->unit_id);
            $html = view('report.profit_loss_report', compact('report'))->render();

            return response()->json([
                'html' => $html,
                'income' => $chartData['income'],
                'expense' => $chartData['expense'],
                'labels' => $chartData['label'],
            ]);
        }

        $properties = Property::where('parent_id', parentId())
            ->pluck('name', 'id')
            ->prepend(__('Select Property'), '');

        $units = [];
        if ($request->filled('property_id')) {
            $units = PropertyUnit::where('property_id', $request->property_id)
                ->pluck('name', 'id');
        }

        return view('report.profit_loss', [
            'year' => $year,
            'years' => range(now()->year, now()->year - 10),
            'property' => $properties,
            'units' => $units,
            'report' => $report,
            'incomeExpenseByMonth' => $this->incomeByMonth($year, $request->property_id, $request->unit_id),
        ]);
    }


    public function reportPropertyUnit(Request $request)
    {
        if (!\Auth::user()->can('manage property unit report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $unitsQuery = PropertyUnit::with('properties')->whereHas('properties', function ($q) {
            $q->where('parent_id', parentId());
        });

        if ($request->filled('property_id')) {
            $unitsQuery->where('property_id', $request->property_id);
        }

        $proUnits = $unitsQuery->get()
            ->groupBy('property_id')
            ->map(function ($group) {
                return (object) [
                    'property_id' => $group->first()->property_id,
                    'property' => $group->first()->properties,
                    'vacant' => $group->where('is_occupied', 0)->count(),
                    'occupied' => $group->where('is_occupied', 1)->count(),
                    'total' => $group->count(),
                ];
            });

        if ($request->ajax()) {
            $html = view('report.property_unit_report', compact('proUnits'))->render();

            return response()->json([
                'html' => $html,
            ]);
        }

        $property = Property::where('parent_id', parentId())
            ->pluck('name', 'id')
            ->prepend(__('Select Property'), '');

        return view('report.property_unit', compact('proUnits', 'property'));
    }


    public function tenant(Request $request)
    {
        if (!\Auth::user()->can('manage tenant history report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = Tenant::with(['user', 'properties', 'units'])
            ->where('parent_id', parentId());

        // Filters
        if ($request->filled('tenant_id')) {
            $query->where('id', $request->tenant_id);
        }

        if ($request->filled('property_id')) {
            $query->where('property', $request->property_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit', $request->unit_id);
        }

        $countQuery = clone $query;

        if ($request->ajax()) {
            $tenants = $query->orderByDesc('id')->get();
            $count = $countQuery->count();

            $html = view('report.tenant_report', compact('tenants', 'count'))->render();

            return response()->json([
                'html' => $html,
                'count' => $count,
            ]);
        }

        $tenantOptions = Tenant::where('parent_id', parentId())
            ->whereHas('user')
            ->with('user')
            ->get()
            ->mapWithKeys(fn($tenant) => [$tenant->id => $tenant->user->full_name])
            ->prepend(__('Select Tenant'), '')
            ->toArray();

        $propertyOptions = Property::where('parent_id', parentId())
            ->pluck('name', 'id')
            ->prepend(__('Select Property'), '');

        $unitOptions = [];
        if ($request->filled('property_id')) {
            $unitOptions = PropertyUnit::where('property_id', $request->property_id)
                ->pluck('name', 'id')
                ->prepend(__('Select Unit'), '');
        }

        return view('report.tenant', [
            'tenants' => $tenantOptions,
            'property' => $propertyOptions,
            'units' => $unitOptions,
        ]);
    }


    public function maintenance(Request $request)
    {
        if (!\Auth::user()->can('manage maintenance report')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = MaintenanceRequest::with(['properties', 'units', 'types', 'maintainers'])
            ->where('parent_id', parentId());

        // Filters
        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tenant_id')) {
            $tenant = Tenant::where('parent_id', parentId())->find($request->tenant_id);
            if ($tenant) {
                $query->where('property_id', $tenant->property)
                    ->where('unit_id', $tenant->unit);
            }
        }

        $countQuery = clone $query;

        if ($request->ajax()) {
            $maintenances = $query->orderByDesc('id')->get();
            $count = $countQuery->count();

            $html = view('report.maintenance_report', compact('maintenances', 'count'))->render();

            return response()->json([
                'html' => $html,
                'count' => $count,
            ]);
        }

        $tenantOptions = Tenant::where('parent_id', parentId())
            ->whereHas('user')
            ->with('user')
            ->get()
            ->mapWithKeys(fn($tenant) => [$tenant->id => $tenant->user->full_name])
            ->prepend(__('Select Tenant'), '')
            ->toArray();

        $propertyOptions = Property::where('parent_id', parentId())
            ->pluck('name', 'id')
            ->prepend(__('Select Property'), '');

        $unitOptions = [];
        if ($request->filled('property_id')) {
            $unitOptions = PropertyUnit::where('property_id', $request->property_id)
                ->pluck('name', 'id');
        }

        $reqstatus = MaintenanceRequest::status();

        return view('report.maintenance', [
            'tenant_options' => $tenantOptions,
            'property' => $propertyOptions,
            'units' => $unitOptions,
            'status' => $reqstatus,
        ]);
    }

    public function incomeByMonth($year = null, $property_id = null, $unit_id = null)
    {
        $year = $year ?? date('Y');
        $start = strtotime("$year-01");
        $end = strtotime("$year-12");

        $currentdate = $start;
        $payment = [];

        while ($currentdate <= $end) {
            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['label'][] = date('M-Y', $currentdate);

            // Income
            $incomeQuery = InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->whereMonth('payment_date', $month)
                ->whereYear('payment_date', $year)
                ->where('invoice_payments.parent_id', parentId());

            if ($property_id) {
                $incomeQuery->where('invoices.property_id', $property_id);
            }

            if ($unit_id) {
                $incomeQuery->where('invoices.unit_id', $unit_id);
            }

            $payment['income'][] = $incomeQuery->sum('invoice_payments.amount');

            // Expense
            $expenseQuery = Expense::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('parent_id', parentId());

            if ($property_id) {
                $expenseQuery->where('property_id', $property_id);
            }

            if ($unit_id) {
                $expenseQuery->where('unit_id', $unit_id);
            }

            $payment['expense'][] = $expenseQuery->sum('amount');

            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }

    // CSV download; cells that start like a formula are prefixed so spreadsheets do not execute them
    private function csvResponse($filename, $header, $rows)
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, array_map(function ($cell) {
                    $cell = (string) $cell;
                    return preg_match('/^[=+\-@\t\r]/', $cell) && !is_numeric($cell) ? "'" . $cell : $cell;
                }, $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Ledger: every payment received and expense paid in a period, in date order, with a running balance.
     */
    public function ledger(Request $request)
    {
        if (!\Auth::user()->can('manage ledger report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $from = $request->from && strtotime($request->from) ? date('Y-m-d', strtotime($request->from)) : Carbon::now()->startOfMonth()->toDateString();
        $to = $request->to && strtotime($request->to) ? date('Y-m-d', strtotime($request->to)) : Carbon::now()->endOfMonth()->toDateString();

        $payments = InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoices.parent_id', parentId())
            ->whereBetween('invoice_payments.payment_date', [$from, $to])
            ->when($request->property, function ($q) use ($request) {
                $q->where('invoices.property_id', $request->property);
            })
            ->select('invoice_payments.*', 'invoices.property_id', 'invoices.unit_id', 'invoices.invoice_id as invoice_number', 'invoices.tenant as tenant_id')
            ->get();

        $expenses = Expense::where('parent_id', parentId())
            ->whereBetween('date', [$from, $to])
            ->when($request->property, function ($q) use ($request) {
                $q->where('property_id', $request->property);
            })
            ->get();

        $propertyNames = Property::where('parent_id', parentId())->pluck('name', 'id');
        $entries = collect();
        foreach ($payments as $payment) {
            $tenant = Tenant::find($payment->tenant_id);
            $entries->push([
                'date' => $payment->payment_date,
                'type' => 'income',
                'description' => __('Invoice') . ' ' . invoicePrefix() . $payment->invoice_number . ' - ' . $payment->payment_type
                    . (!empty(optional($tenant)->user) ? ' (' . $tenant->user->name . ')' : ''),
                'property' => $propertyNames[$payment->property_id] ?? '-',
                'in' => (float) $payment->amount,
                'out' => 0,
            ]);
        }
        foreach ($expenses as $expense) {
            $entries->push([
                'date' => $expense->date,
                'type' => 'expense',
                'description' => $expense->title . (!empty($expense->types) ? ' (' . $expense->types->title . ')' : ''),
                'property' => $propertyNames[$expense->property_id] ?? '-',
                'in' => 0,
                'out' => (float) $expense->amount,
            ]);
        }
        $balance = 0;
        $entries = $entries->sortBy('date')->values()->map(function ($entry) use (&$balance) {
            $balance += $entry['in'] - $entry['out'];
            $entry['balance'] = round($balance, 2);
            return $entry;
        });

        if ($request->export == 'csv') {
            return $this->csvResponse('ledger-' . $from . '-to-' . $to . '.csv',
                [__('Date'), __('Description'), __('Property'), __('Money In'), __('Money Out'), __('Balance')],
                $entries->map(function ($e) {
                    return [$e['date'], $e['description'], $e['property'], $e['in'] ?: '', $e['out'] ?: '', $e['balance']];
                }));
        }

        if ($request->export == 'pdf') {
            return \App\Support\ReportExport::pdf('ledger-' . $from . '-to-' . $to . '.pdf', __('Ledger'),
                [__('Date'), __('Description'), __('Property'), __('Money In'), __('Money Out'), __('Balance')],
                $entries->map(fn($e) => [dateFormat($e['date']), $e['description'], $e['property'], $e['in'] ? priceFormat($e['in']) : '', $e['out'] ? priceFormat($e['out']) : '', priceFormat($e['balance'])])->all(),
                [
                    'subtitle' => dateFormat($from) . ' – ' . dateFormat($to),
                    'totals' => [__('Total'), '', '', priceFormat($entries->sum('in')), priceFormat($entries->sum('out')), priceFormat($entries->sum('in') - $entries->sum('out'))],
                    'align' => [3 => 'right', 4 => 'right', 5 => 'right'],
                ]);
        }

        $properties = $propertyNames->prepend(__('All Properties'), '');
        return view('report.ledger', compact('entries', 'from', 'to', 'properties'));
    }

    /**
     * Yearly income and expense summary for tax filing: cash received and spent per month,
     * income invoiced by type and expenses by type.
     */
    public function taxSummary(Request $request)
    {
        if (!\Auth::user()->can('manage tax report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $year = (int) ($request->year ?: date('Y'));

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $income = InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                ->where('invoices.parent_id', parentId())
                ->whereYear('invoice_payments.payment_date', $year)->whereMonth('invoice_payments.payment_date', $m)
                ->sum('invoice_payments.amount');
            $expense = Expense::where('parent_id', parentId())->whereYear('date', $year)->whereMonth('date', $m)->sum('amount');
            $months[] = ['month' => Carbon::create($year, $m, 1)->format('M Y'), 'income' => (float) $income, 'expense' => (float) $expense, 'net' => (float) $income - (float) $expense];
        }

        $incomeByType = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('types', 'types.id', '=', 'invoice_items.invoice_type')
            ->where('invoices.parent_id', parentId())
            ->whereYear('invoices.invoice_month', $year)
            ->groupBy('types.title')
            ->selectRaw("COALESCE(types.title, '-') as title, SUM(invoice_items.amount) as total")
            ->orderByDesc('total')
            ->get();

        $expenseByType = DB::table('expenses')
            ->leftJoin('types', 'types.id', '=', 'expenses.expense_type')
            ->where('expenses.parent_id', parentId())
            ->whereYear('expenses.date', $year)
            ->groupBy('types.title')
            ->selectRaw("COALESCE(types.title, '-') as title, SUM(expenses.amount) as total")
            ->orderByDesc('total')
            ->get();

        $totals = [
            'income' => array_sum(array_column($months, 'income')),
            'expense' => array_sum(array_column($months, 'expense')),
        ];
        $totals['net'] = $totals['income'] - $totals['expense'];

        if ($request->export == 'csv') {
            $rows = [];
            foreach ($months as $row) {
                $rows[] = [$row['month'], $row['income'], $row['expense'], $row['net']];
            }
            $rows[] = [__('Total'), $totals['income'], $totals['expense'], $totals['net']];
            $rows[] = [];
            $rows[] = [__('Income invoiced by type')];
            foreach ($incomeByType as $row) {
                $rows[] = [$row->title, $row->total];
            }
            $rows[] = [];
            $rows[] = [__('Expenses by type')];
            foreach ($expenseByType as $row) {
                $rows[] = [$row->title, $row->total];
            }
            return $this->csvResponse('tax-summary-' . $year . '.csv', [__('Month'), __('Income Received'), __('Expenses Paid'), __('Net')], $rows);
        }

        if ($request->export == 'pdf') {
            $rows = array_map(fn($row) => [$row['month'], priceFormat($row['income']), priceFormat($row['expense']), priceFormat($row['net'])], $months);
            $rows[] = ['', '', '', ''];
            foreach ($incomeByType as $row) {
                $rows[] = [__('Income invoiced') . ': ' . $row->title, priceFormat($row->total), '', ''];
            }
            foreach ($expenseByType as $row) {
                $rows[] = [__('Expense') . ': ' . $row->title, '', priceFormat($row->total), ''];
            }
            return \App\Support\ReportExport::pdf('tax-summary-' . $year . '.pdf', __('Tax Summary') . ' ' . $year,
                [__('Month'), __('Income Received'), __('Expenses Paid'), __('Net')], $rows, [
                    'totals' => [__('Total'), priceFormat($totals['income']), priceFormat($totals['expense']), priceFormat($totals['net'])],
                    'align' => [1 => 'right', 2 => 'right', 3 => 'right'],
                ]);
        }

        $settings = settings();
        $firstYear = (int) (Invoice::where('parent_id', parentId())->min(DB::raw('YEAR(invoice_month)')) ?: date('Y'));
        $years = array_combine(range(date('Y'), min($firstYear, date('Y'))), range(date('Y'), min($firstYear, date('Y'))));
        return view('report.tax_summary', compact('year', 'years', 'months', 'incomeByType', 'expenseByType', 'totals', 'settings'));
    }
}
