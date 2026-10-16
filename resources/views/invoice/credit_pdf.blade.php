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
        .lines td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .lines .k { color: #6b7280; width: 45%; }
        .amount { font-size: 22px; font-weight: bold; }
        .foot { margin-top: 18px; font-size: 9px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td>
                <div class="company">{{ $settings['company_name'] ?? '' }}</div>
                <div class="muted">{{ $settings['company_address'] ?? '' }}</div>
            </td>
            <td style="text-align:right">
                <h2>{{ $note->type == 'refund' ? __('REFUND RECEIPT') : __('CREDIT NOTE') }}</h2>
                <div class="muted">{{ $note->displayNumber() }} · {{ dateFormat($note->date) }}</div>
            </td>
        </tr>
    </table>
    <p>{{ __('To') }}: <strong>{{ optional(optional($invoice->tenants)->user)->name ?: '-' }}</strong><br>
        {{ optional($invoice->properties)->name }} · {{ optional($invoice->units)->name }}</p>
    <div class="muted">{{ $note->type == 'refund' ? __('Amount refunded') : __('Amount credited') }}</div>
    <div class="amount">{{ priceFormat($note->amount) }}</div>
    <table class="lines" style="margin-top:12px">
        <tr><td class="k">{{ __('Invoice') }}</td><td>{{ invoicePrefix() . $invoice->invoice_id }} · {{ date('F Y', strtotime($invoice->invoice_month)) }}</td></tr>
        <tr><td class="k">{{ __('Reason') }}</td><td>{{ $note->reason }}</td></tr>
        @if ($note->type == 'refund' && $note->method)
            <tr><td class="k">{{ __('Refunded by') }}</td><td>{{ $note->method }}</td></tr>
        @endif
        <tr><td class="k">{{ __('Invoice balance now') }}</td><td><strong>{{ priceFormat(max(0, $invoice->getInvoiceDueAmount())) }}</strong></td></tr>
    </table>
    <div class="foot">{{ __('Generated on :date.', ['date' => dateFormat(now())]) }}</div>
</body>
</html>
