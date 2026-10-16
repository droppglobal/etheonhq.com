<?php

namespace App\Http\Controllers;

use App\Models\DepositTransaction;
use App\Models\Invoice;
use App\Models\LandlordPayout;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\RentIncrease;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ReportExport;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Arrears ageing, rent roll and landlord payout history, each with CSV and PDF export.
 */
class FinancialReportController extends Controller
{
    const BUCKETS = ['current', '1_30', '31_60', '61_90', '90_plus'];

    public static function bucketLabels()
    {
        return [
            'current' => __('Not yet due'),
            '1_30' => __('1–30 days'),
            '31_60' => __('31–60 days'),
            '61_90' => __('61–90 days'),
            '90_plus' => __('90+ days'),
        ];
    }

    private function properties()
    {
        return Property::where('parent_id', parentId())->orderBy('name')->pluck('name', 'id');
    }

    // ------------------------------------------------------------------ arrears ageing

    public function arrears(Request $request)
    {
        if (!\Auth::user()->can('manage arrears report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $asOf = $request->as_of && strtotime($request->as_of) ? Carbon::parse($request->as_of)->startOfDay() : Carbon::today();
        $invoices = Invoice::with(['tenants.user', 'properties', 'units', 'types', 'payments', 'creditNotes'])
            ->where('parent_id', parentId())->whereIn('status', ['unpaid', 'partial_paid'])
            ->when($request->property, fn($q) => $q->where('property_id', $request->property))
            ->get();

        $rows = [];
        foreach ($invoices as $invoice) {
            $due = $invoice->getInvoiceDueAmount();
            if ($due <= 0) {
                continue;
            }
            $days = Carbon::parse($invoice->end_date)->startOfDay()->diffInDays($asOf, false);
            $bucket = $days <= 0 ? 'current' : ($days <= 30 ? '1_30' : ($days <= 60 ? '31_60' : ($days <= 90 ? '61_90' : '90_plus')));
            $key = $invoice->tenant ?: 'unit-' . $invoice->unit_id;
            if (!isset($rows[$key])) {
                $rows[$key] = [
                    'tenant' => optional(optional($invoice->tenants)->user)->name ?: __('No tenant'),
                    'phone' => optional(optional($invoice->tenants)->user)->phone_number,
                    'property' => optional($invoice->properties)->name,
                    'unit' => optional($invoice->units)->name,
                    'tenant_id' => $invoice->tenant,
                    'invoices' => 0,
                    'oldest' => 0,
                    'total' => 0,
                ] + array_fill_keys(self::BUCKETS, 0);
            }
            $rows[$key][$bucket] += $due;
            $rows[$key]['total'] += $due;
            $rows[$key]['invoices']++;
            $rows[$key]['oldest'] = max($rows[$key]['oldest'], (int) $days);
        }
        $rows = collect($rows)->sortByDesc('oldest')->values();
        $totals = array_merge(['total' => $rows->sum('total')], collect(self::BUCKETS)->mapWithKeys(fn($b) => [$b => $rows->sum($b)])->all());

        if (in_array($request->export, ['csv', 'pdf'])) {
            $labels = self::bucketLabels();
            $header = array_merge([__('Tenant'), __('Property'), __('Unit'), __('Invoices')], array_values($labels), [__('Total owed')]);
            $money = fn($v) => $request->export == 'pdf' ? ($v ? priceFormat(round($v, 2)) : '') : round($v, 2);
            $data = $rows->map(fn($r) => array_merge([$r['tenant'], $r['property'], $r['unit'], $r['invoices']], array_map(fn($b) => $money($r[$b]), self::BUCKETS), [$money($r['total'])]))->all();
            $totalRow = array_merge([__('Total'), '', '', $rows->sum('invoices')], array_map(fn($b) => $money($totals[$b]), self::BUCKETS), [$money($totals['total'])]);
            if ($request->export == 'csv') {
                return ReportExport::csv('arrears-' . $asOf->toDateString() . '.csv', $header, array_merge($data, [$totalRow]));
            }
            return ReportExport::pdf('arrears-' . $asOf->toDateString() . '.pdf', __('Arrears Ageing'), $header, $data, [
                'subtitle' => __('As of :date', ['date' => dateFormat($asOf)]), 'totals' => $totalRow, 'landscape' => true,
                'align' => [3 => 'right', 4 => 'right', 5 => 'right', 6 => 'right', 7 => 'right', 8 => 'right', 9 => 'right'],
            ]);
        }

        $properties = $this->properties()->prepend(__('All Properties'), '');
        return view('report.arrears', compact('rows', 'totals', 'asOf', 'properties'));
    }

    // ------------------------------------------------------------------ rent roll

    public function rentRoll(Request $request)
    {
        if (!\Auth::user()->can('manage rent roll report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $units = PropertyUnit::with('properties')->where('parent_id', parentId())
            ->when($request->property, fn($q) => $q->where('property_id', $request->property))
            ->get()->sortBy(fn($u) => optional($u->properties)->name . ' ' . $u->name);
        $tenants = Tenant::with('user')->where('parent_id', parentId())->whereNull('exit_date')->whereIn('unit', $units->pluck('id'))->get()->keyBy('unit');
        $deposits = DepositTransaction::where('parent_id', parentId())->whereIn('tenant_id', $tenants->pluck('id'))->get()->groupBy('tenant_id');
        $openInvoices = Invoice::with(['types', 'payments', 'creditNotes'])->where('parent_id', parentId())->whereIn('status', ['unpaid', 'partial_paid'])
            ->whereIn('tenant', $tenants->pluck('id'))->get()->groupBy('tenant');
        $changes = RentIncrease::where('parent_id', parentId())->where('status', 'scheduled')->get()->keyBy('tenant_id');

        $rows = $units->map(function ($unit) use ($tenants, $deposits, $openInvoices, $changes) {
            $tenant = $tenants->get($unit->id);
            $dep = $tenant ? $deposits->get($tenant->id, collect()) : collect();
            $held = $dep->where('type', 'received')->sum('amount') - $dep->whereIn('type', ['deduction', 'refund'])->sum('amount');
            $balance = $tenant ? $openInvoices->get($tenant->id, collect())->sum(fn($i) => max(0, $i->getInvoiceDueAmount())) : 0;
            $change = $tenant ? $changes->get($tenant->id) : null;
            return [
                'property' => optional($unit->properties)->name,
                'unit' => $unit->name,
                'status' => $tenant ? 'occupied' : 'vacant',
                'tenant' => $tenant ? optional($tenant->user)->name : null,
                'lease_start' => $tenant ? $tenant->lease_start_date : null,
                'lease_end' => $tenant ? $tenant->lease_end_date : null,
                'rent' => (float) $unit->rent,
                'rent_type' => $unit->rent_type,
                'deposit' => round($held, 2),
                'balance' => round($balance, 2),
                'change' => $change ? __(':new from :date', ['new' => priceFormat($change->new_rent), 'date' => dateFormat($change->effective_date)]) : null,
            ];
        })->values();

        $occupied = $rows->where('status', 'occupied');
        $summary = [
            'units' => $rows->count(),
            'occupied' => $occupied->count(),
            'occupancy' => $rows->count() ? round($occupied->count() / $rows->count() * 100, 1) : 0,
            'monthly_rent' => $occupied->where('rent_type', 'monthly')->sum('rent'),
            'vacant_rent' => $rows->where('status', 'vacant')->where('rent_type', 'monthly')->sum('rent'),
            'deposits' => $rows->sum('deposit'),
            'balance' => $rows->sum('balance'),
        ];

        if (in_array($request->export, ['csv', 'pdf'])) {
            $types = PropertyUnit::rentTypes();
            $pdf = $request->export == 'pdf';
            $money = fn($v) => $pdf ? priceFormat($v) : $v;
            $date = fn($d) => $d ? ($pdf ? dateFormat($d) : $d) : '';
            $header = [__('Property'), __('Unit'), __('Status'), __('Tenant'), __('Lease start'), __('Lease end'), __('Rent'), __('Rent type'), __('Deposit held'), __('Balance owed'), __('Scheduled change')];
            $data = $rows->map(fn($r) => [$r['property'], $r['unit'], $r['status'] == 'occupied' ? __('Occupied') : __('Vacant'), $r['tenant'], $date($r['lease_start']), $date($r['lease_end']), $money($r['rent']), $types[$r['rent_type']] ?? $r['rent_type'], $money($r['deposit']), $money($r['balance']), $r['change']])->all();
            if (!$pdf) {
                return ReportExport::csv('rent-roll-' . date('Y-m-d') . '.csv', $header, $data);
            }
            return ReportExport::pdf('rent-roll-' . date('Y-m-d') . '.pdf', __('Rent Roll'), $header, $data, [
                'subtitle' => dateFormat(now()), 'landscape' => true, 'align' => [6 => 'right', 8 => 'right', 9 => 'right'],
                'summary' => [
                    __('Units') => $summary['units'], __('Occupancy') => $summary['occupancy'] . '%',
                    __('Monthly rent (occupied)') => priceFormat($summary['monthly_rent']), __('Deposits held') => priceFormat($summary['deposits']), __('Balance owed') => priceFormat($summary['balance']),
                ],
            ]);
        }

        $properties = $this->properties()->prepend(__('All Properties'), '');
        return view('report.rent_roll', compact('rows', 'summary', 'properties'));
    }

    // ------------------------------------------------------------------ landlord payouts

    public function payouts(Request $request)
    {
        if (!\Auth::user()->can('manage payout report')) {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
        $from = $request->from && strtotime($request->from) ? date('Y-m-d', strtotime($request->from)) : Carbon::now()->startOfYear()->toDateString();
        $to = $request->to && strtotime($request->to) ? date('Y-m-d', strtotime($request->to)) : Carbon::now()->toDateString();
        $landlords = User::where('parent_id', parentId())->where('type', 'landlord')->orderBy('first_name')->get()->mapWithKeys(fn($u) => [$u->id => $u->name]);

        $payouts = LandlordPayout::where('parent_id', parentId())->whereBetween('payout_date', [$from, $to])
            ->when($request->landlord, fn($q) => $q->where('landlord_id', $request->landlord))
            ->orderByDesc('payout_date')->get();
        $byLandlord = $payouts->groupBy('landlord_id')->map(fn($g, $id) => ['name' => $landlords[$id] ?? __('Removed landlord'), 'count' => $g->count(), 'total' => $g->sum('amount')])->sortByDesc('total');

        if (in_array($request->export, ['csv', 'pdf'])) {
            $pdf = $request->export == 'pdf';
            $header = [__('Date'), __('Landlord'), __('Amount'), __('Method'), __('Reference'), __('Notes')];
            $data = $payouts->map(fn($p) => [$pdf ? dateFormat($p->payout_date) : $p->payout_date, $landlords[$p->landlord_id] ?? '-', $pdf ? priceFormat($p->amount) : $p->amount, $p->method, $p->reference, $p->notes])->all();
            $total = [__('Total'), '', $pdf ? priceFormat($payouts->sum('amount')) : $payouts->sum('amount'), '', '', ''];
            if (!$pdf) {
                return ReportExport::csv('landlord-payouts-' . $from . '-to-' . $to . '.csv', $header, array_merge($data, [$total]));
            }
            return ReportExport::pdf('landlord-payouts-' . $from . '-to-' . $to . '.pdf', __('Landlord Payouts'), $header, $data, [
                'subtitle' => dateFormat($from) . ' – ' . dateFormat($to), 'totals' => $total, 'align' => [2 => 'right'],
            ]);
        }

        return view('report.payouts', compact('payouts', 'byLandlord', 'landlords', 'from', 'to'));
    }
}
