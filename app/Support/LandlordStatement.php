<?php

namespace App\Support;

use App\Models\Expense;
use App\Models\InvoicePayment;
use App\Models\LandlordPayout;
use App\Models\LandlordProfile;
use App\Models\Property;

/**
 * Money collected and spent on a landlord's properties, the agency's management fee and what is owed to the landlord.
 */
class LandlordStatement
{
    public static function build($landlordId, $parentId, $from, $to)
    {
        $propertyIds = Property::where('parent_id', $parentId)->where('landlord_id', $landlordId)->pluck('id');
        $feePercent = (float) LandlordProfile::where('parent_id', $parentId)->where('user_id', $landlordId)->value('management_fee_percent');

        $payments = InvoicePayment::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoices.parent_id', $parentId)
            ->whereIn('invoices.property_id', $propertyIds)
            ->whereBetween('invoice_payments.payment_date', [$from, $to])
            ->select('invoice_payments.*', 'invoices.property_id', 'invoices.unit_id', 'invoices.invoice_id as invoice_number')
            ->orderBy('invoice_payments.payment_date')
            ->get();

        $expenses = Expense::where('parent_id', $parentId)
            ->whereIn('property_id', $propertyIds)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();

        $income = round($payments->sum('amount'), 2);
        $expenseTotal = round($expenses->sum('amount'), 2);
        $fee = round($income * $feePercent / 100, 2);
        $payouts = LandlordPayout::where('parent_id', $parentId)->where('landlord_id', $landlordId)
            ->whereBetween('payout_date', [$from, $to])->orderBy('payout_date')->get();

        return [
            'from' => $from,
            'to' => $to,
            'feePercent' => $feePercent,
            'payments' => $payments,
            'expenses' => $expenses,
            'payouts' => $payouts,
            'income' => $income,
            'expenseTotal' => $expenseTotal,
            'fee' => $fee,
            'net' => round($income - $expenseTotal - $fee, 2),
            'paidOut' => round($payouts->sum('amount'), 2),
            'balance' => self::balance($landlordId, $parentId),
        ];
    }

    // All-time amount owed to the landlord: net earned minus payouts made
    public static function balance($landlordId, $parentId)
    {
        $all = self::totals($landlordId, $parentId);
        $paid = LandlordPayout::where('parent_id', $parentId)->where('landlord_id', $landlordId)->sum('amount');
        return round($all - $paid, 2);
    }

    private static function totals($landlordId, $parentId)
    {
        $propertyIds = Property::where('parent_id', $parentId)->where('landlord_id', $landlordId)->pluck('id');
        $feePercent = (float) LandlordProfile::where('parent_id', $parentId)->where('user_id', $landlordId)->value('management_fee_percent');
        $income = InvoicePayment::join('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where('invoices.parent_id', $parentId)->whereIn('invoices.property_id', $propertyIds)->sum('invoice_payments.amount');
        $expenses = Expense::where('parent_id', $parentId)->whereIn('property_id', $propertyIds)->sum('amount');
        return $income - $expenses - ($income * $feePercent / 100);
    }

    // [from, to] for a "YYYY-MM" month, defaulting to the current month
    public static function monthRange($month)
    {
        $start = preg_match('/^\d{4}-\d{2}$/', (string) $month) ? \Carbon\Carbon::createFromFormat('Y-m-d', $month . '-01') : \Carbon\Carbon::now()->startOfMonth();
        return [$start->copy()->startOfMonth()->toDateString(), $start->copy()->endOfMonth()->toDateString(), $start->format('Y-m')];
    }
}
