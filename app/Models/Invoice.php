<?php

namespace App\Models;

use http\Env\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'property_id',
        'unit_id',
        'invoice_month',
        'end_date',
        'status',
        'notes',
        'parent_id',
    ];

    public static function status()
    {

        return [
            'unpaid' => __('Unpaid'),
            'paid' => __('Paid'),
            'partial_paid' => __('Partial Paid'),
        ];
    }

    public function properties()
    {
        return $this->hasOne('App\Models\Property', 'id', 'property_id');
    }

    public function units()
    {
        return $this->hasOne(PropertyUnit::class, 'id', 'unit_id');
    }

    public function tenants()
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant');
    }

    public function types()
    {
        return $this->hasMany('App\Models\InvoiceItem', 'invoice_id', 'id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\InvoicePayment', 'invoice_id', 'id');
    }

    public function getInvoiceSubTotalAmount()
    {
        $invoiceSubTotal = 0;
        foreach ($this->types as $type) {
            $invoiceSubTotal += $type->amount;
        }
        return $invoiceSubTotal;
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class, 'invoice_id', 'id');
    }

    // credit notes lower what is owed; refunds give back money that was paid
    public function getCreditTotal()
    {
        return (float) $this->creditNotes->where('type', 'credit')->sum('amount');
    }

    public function getRefundTotal()
    {
        return (float) $this->creditNotes->where('type', 'refund')->sum('amount');
    }

    // refunds are also stored as negative payment rows, so every income report nets them out
    public function getPaidTotal()
    {
        return (float) $this->payments->sum('amount');
    }

    public function getInvoiceDueAmount()
    {
        return round($this->getInvoiceSubTotalAmount() - $this->getCreditTotal() - $this->getPaidTotal(), 2);
    }

    // status from the current numbers (after a payment, refund or credit changes)
    public static function refreshStatus($invoiceId)
    {
        $invoice = Invoice::with(['payments', 'creditNotes', 'types'])->find($invoiceId);
        if (!$invoice) {
            return null;
        }
        if ($invoice->getInvoiceDueAmount() <= 0) {
            $status = 'paid';
        } elseif ($invoice->getPaidTotal() > 0 || $invoice->getCreditTotal() > 0) {
            $status = 'partial_paid';
        } else {
            $status = 'unpaid';
        }
        return Invoice::statusChange($invoice->id, $status);
    }

    public static function statusChange($invoice_id, $status)
    {
        $invoice = Invoice::find($invoice_id);
        $invoice->status = $status;
        $invoice->save();
        return $invoice;
    }

    public static function addPayment($data)
    {
        $payment = new InvoicePayment();
        $payment->invoice_id = $data['invoice_id'];
        $payment->transaction_id = $data['transaction_id'];
        $payment->payment_type = $data['payment_type'];
        $payment->amount = $data['amount'];
        $payment->payment_date = date('Y-m-d');
        $payment->receipt = !empty($data['receipt']) ? $data['receipt'] : '';
        $payment->notes = $data['notes'];
        ;
        // from the invoice itself: payments are also recorded by the cron (autopay), where nobody is logged in
        $payment->parent_id = Invoice::where('id', $data['invoice_id'])->value('parent_id') ?: parentId();
        $payment->save();
        $invoice = Invoice::refreshStatus($data['invoice_id']);

        // thank the tenant (every payment method ends up here)
        if ($tenantUserId = optional($invoice->tenants)->user_id) {
            \App\Support\Notify::push($tenantUserId, __('Payment received') . ': ' . priceFormat($payment->amount),
                __('Thank you! :number · :method', ['number' => invoicePrefix() . $invoice->invoice_id, 'method' => $payment->payment_type]),
                route('invoice.show', \Crypt::encrypt($invoice->id)));
        }

        // let the office know money came in (every payment method ends up here)
        $payer = optional(optional($invoice->tenants)->user)->name;
        \App\Support\Notify::push(
            \App\Support\Notify::staffOf($invoice->parent_id, 'manage invoice'),
            __('Payment received') . ': ' . priceFormat($payment->amount),
            trim(($payer ? $payer . ' · ' : '') . invoicePrefix() . $invoice->invoice_id . ' · ' . $payment->payment_type),
            route('invoice.show', \Crypt::encrypt($invoice->id))
        );
    }
}
