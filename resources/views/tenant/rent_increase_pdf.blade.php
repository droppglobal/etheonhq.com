<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; line-height: 1.5; }
        .company { font-size: 16px; font-weight: bold; }
        .muted { color: #6b7280; }
        h2 { margin: 24px 0 12px; font-size: 18px; }
        table.box { width: 100%; border-collapse: collapse; margin: 14px 0; }
        table.box td { border: 1px solid #e5e7eb; padding: 8px 10px; }
        table.box td.k { background: #f9fafb; width: 40%; color: #6b7280; }
    </style>
</head>
<body>
    <div class="company">{{ $settings['company_name'] ?? '' }}</div>
    <div class="muted">{{ $settings['company_address'] ?? '' }}<br>{{ $settings['company_phone'] ?? '' }} {{ $settings['company_email'] ?? '' }}</div>
    <p style="margin-top:24px">{{ dateFormat($increase->created_at) }}</p>
    <p><strong>{{ optional($tenant->user)->name }}</strong><br>{{ optional($tenant->properties)->name }}, {{ optional($increase->unit)->name }}<br>{{ $tenant->address }} {{ $tenant->city }}</p>
    <h2>{{ __('Notice of rent change') }}</h2>
    <p>{{ __('Dear :name,', ['name' => optional($tenant->user)->name]) }}</p>
    <p>{{ __('This letter is to let you know that the rent for your home will change as set out below.') }}</p>
    <table class="box">
        <tr><td class="k">{{ __('Property / unit') }}</td><td>{{ optional($tenant->properties)->name }} · {{ optional($increase->unit)->name }}</td></tr>
        <tr><td class="k">{{ __('Current rent') }}</td><td>{{ priceFormat($increase->old_rent) }}</td></tr>
        <tr><td class="k">{{ __('New rent') }}</td><td><strong>{{ priceFormat($increase->new_rent) }}</strong>@if ($increase->changePercent() !== null) ({{ $increase->changePercent() > 0 ? '+' : '' }}{{ $increase->changePercent() }}%)@endif</td></tr>
        <tr><td class="k">{{ __('Effective from') }}</td><td><strong>{{ dateFormat($increase->effective_date) }}</strong></td></tr>
    </table>
    @if ($increase->notes)<p>{!! nl2br(e($increase->notes)) !!}</p>@endif
    <p>{{ __('All other terms of your tenancy stay the same. If you have any questions, please contact us.') }}</p>
    <p style="margin-top:30px">{{ __('Yours sincerely,') }}<br><br>{{ $settings['company_name'] ?? '' }}</p>
</body>
</html>
