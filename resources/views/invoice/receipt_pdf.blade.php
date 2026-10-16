<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        .head { border-bottom: 2px solid #1f2937; padding-bottom: 8px; margin-bottom: 12px; }
        .company { font-size: 15px; font-weight: bold; }
        .muted { color: #6b7280; }
        h2 { margin: 0; font-size: 18px; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 3px 0; vertical-align: top; }
        .lines td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .lines .k { color: #6b7280; width: 55%; }
        .amount { font-size: 22px; font-weight: bold; }
        .paid { display: inline-block; border: 2px solid #16a34a; color: #16a34a; padding: 2px 10px; font-weight: bold; letter-spacing: 2px; }
        .foot { margin-top: 18px; font-size: 9px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td>
                <div class="company">{{ $settings['company_name'] ?? '' }}</div>
                <div class="muted">{{ $settings['company_address'] ?? '' }}</div>
                <div class="muted">{{ $settings['company_phone'] ?? '' }} {{ !empty($settings['company_email']) ? '· ' . $settings['company_email'] : '' }}</div>
            </td>
            <td style="text-align:right">
                <h2>{{ __('PAYMENT RECEIPT') }}</h2>
                <div class="muted">{{ $number }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td style="width:55%">
                <div class="muted">{{ __('Received from') }}</div>
                <strong>{{ optional(optional($invoice->tenants)->user)->name ?: '-' }}</strong>
                <div>{{ optional($invoice->properties)->name }} · {{ optional($invoice->units)->name }}</div>
            </td>
            <td style="text-align:right">
                <div class="muted">{{ __('Amount received') }}</div>
                <div class="amount">{{ priceFormat($payment->amount) }}</div>
                <span class="paid">{{ __('PAID') }}</span>
            </td>
        </tr>
    </table>

    <table class="lines" style="margin-top:14px">
        <tr><td class="k">{{ __('Payment date') }}</td><td>{{ dateFormat($payment->payment_date) }}</td></tr>
        <tr><td class="k">{{ __('Payment method') }}</td><td>{{ $payment->payment_type ?: '-' }}</td></tr>
        <tr><td class="k">{{ __('Transaction reference') }}</td><td>{{ $payment->transaction_id ?: '-' }}</td></tr>
        <tr><td class="k">{{ __('For invoice') }}</td><td>{{ invoicePrefix() . $invoice->invoice_id }} · {{ date('F Y', strtotime($invoice->invoice_month)) }}</td></tr>
        <tr><td class="k">{{ __('Invoice total') }}</td><td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</td></tr>
        <tr><td class="k">{{ __('Balance after this payment') }}</td><td><strong>{{ priceFormat(max(0, $balanceAfter)) }}</strong></td></tr>
        @if ($payment->notes)
            <tr><td class="k">{{ __('Notes') }}</td><td>{{ $payment->notes }}</td></tr>
        @endif
    </table>

    <div class="foot">{{ __('This receipt was generated on :date and is valid without a signature.', ['date' => dateFormat(now())]) }}</div>
</body>
</html>
