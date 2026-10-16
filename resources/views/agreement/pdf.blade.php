<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h2 { text-align: center; margin: 0 0 6px; }
        .muted { color: #6b7280; }
        .section { margin-top: 14px; }
        .label { font-weight: bold; text-transform: uppercase; font-size: 10px; color: #6b7280; margin-bottom: 4px; }
        table.fields { width: 100%; border-collapse: collapse; background: #f9fafb; }
        table.fields td { padding: 4px 8px; vertical-align: top; }
        table.fields td.k { width: 35%; color: #6b7280; }
        .terms { border: 1px solid #e5e7eb; padding: 8px; }
        table.sig { width: 100%; margin-top: 10px; }
        table.sig td { width: 50%; vertical-align: bottom; padding: 0 10px; }
        .sig-img { height: 70px; }
        .sig-line { border-top: 1px solid #111; margin-top: 4px; padding-top: 3px; }
        .audit { margin-top: 18px; font-size: 10px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 6px; }
    </style>
</head>

<body>
    @php
        // dompdf needs the GD extension to embed PNG signatures; without it, print a text line instead
        $embedSignatures = extension_loaded('gd');
    @endphp
    <h2>{{ __('Rental Agreement') }}</h2>
    <div class="muted" style="text-align:center">{{ agreementPrefix() . $agreement->agreement_id }} &middot;
        {{ __('This agreement is made on') }} {{ dateFormat($agreement->date) }}</div>

    <div class="section">
        <div class="label">{{ __('Landlord details') }}</div>
        <table class="fields">
            <tr><td class="k">{{ __('Name') }}</td><td>{{ $settings['company_name'] ?? '-' }}</td></tr>
            <tr><td class="k">{{ __('Phone') }}</td><td>{{ $settings['company_phone'] ?? '-' }}</td></tr>
            <tr><td class="k">{{ __('Email') }}</td><td>{{ $settings['company_email'] ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="label">{{ __('Tenant details') }}</div>
        <table class="fields">
            <tr><td class="k">{{ __('Name') }}</td><td>{{ optional($tenantUser)->full_name ?? '-' }}</td></tr>
            <tr><td class="k">{{ __('Phone') }}</td><td>{{ optional($tenantUser)->phone_number ?? '-' }}</td></tr>
            <tr><td class="k">{{ __('Email') }}</td><td>{{ optional($tenantUser)->email ?? '-' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="label">{{ __('Property details') }}</div>
        <table class="fields">
            <tr><td class="k">{{ __('Property name') }}</td><td>{{ $agreement->properties->name ?? '-' }}</td></tr>
            <tr><td class="k">{{ __('Unit name') }}</td><td>{{ $agreement->units->name ?? '-' }}</td></tr>
            <tr><td class="k">{{ __('Address') }}</td><td>{{ $agreement->properties->address ?? '-' }}</td></tr>
            <tr><td class="k">{{ __('Lease') }}</td>
                <td>{{ $agreement->agreement_start_date ? dateFormat($agreement->agreement_start_date) : '-' }} -
                    {{ $agreement->agreement_end_date ? dateFormat($agreement->agreement_end_date) : '-' }}</td></tr>
            @if (!empty($agreement->units))
                <tr><td class="k">{{ __('Rent') }}</td><td>{{ priceFormat($agreement->units->rent) }} / {{ $agreement->units->rent_type }}</td></tr>
                <tr><td class="k">{{ __('Security deposit') }}</td><td>{{ priceFormat($agreement->units->deposit_amount) }}</td></tr>
            @endif
        </table>
    </div>

    <div class="section">
        <div class="label">{{ __('Terms & conditions') }}</div>
        <div class="terms">{!! $agreement->terms_condition !!}</div>
    </div>

    <div class="section">
        <div class="label">{{ __('Description') }}</div>
        <div class="terms">{!! $agreement->description !!}</div>
    </div>

    <div class="section">
        <div class="label">{{ __('Signatures') }}</div>
        <table class="sig">
            <tr>
                <td>
                    @php $landlordSignature = $agreement->landlord_signature ?: ($settings['landlord_signature'] ?? ''); @endphp
                    @if (!empty($landlordSignature))
                        @if ($embedSignatures)
                            <img class="sig-img" src="{{ $landlordSignature }}">
                        @else
                            <div class="muted"><em>{{ __('Signed electronically') }}</em></div>
                        @endif
                    @endif
                    <div class="sig-line">{{ $settings['company_name'] ?? '-' }}<br>
                        <span class="muted">{{ __('Landlord / Authorised signatory') }}</span><br>
                        <span class="muted">{{ $agreement->landlord_signature_date ? dateFormat($agreement->landlord_signature_date) . ' ' . timeFormat($agreement->landlord_signature_date) : '' }}</span>
                    </div>
                </td>
                <td>
                    @if (!empty($agreement->tenant_signature))
                        @if ($embedSignatures)
                            <img class="sig-img" src="{{ $agreement->tenant_signature }}">
                        @else
                            <div class="muted"><em>{{ __('Signed electronically') }}</em></div>
                        @endif
                    @endif
                    <div class="sig-line">{{ optional($tenantUser)->full_name ?? '-' }}<br>
                        <span class="muted">{{ __('Tenant') }}</span><br>
                        <span class="muted">{{ $agreement->tenant_signature_date ? dateFormat($agreement->tenant_signature_date) . ' ' . timeFormat($agreement->tenant_signature_date) : '' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if (!empty($agreement->tenant_signature_date))
        <div class="audit">
            {{ __('Electronic signature record') }}:
            {{ __('signed by tenant on') }} {{ $agreement->tenant_signature_date }}
            @if (!empty($agreement->tenant_signature_ip))
                &middot; IP {{ $agreement->tenant_signature_ip }}
            @endif
            @if (!empty($agreement->tenant_signature_agent))
                &middot; {{ \Illuminate\Support\Str::limit($agreement->tenant_signature_agent, 120) }}
            @endif
        </div>
    @endif
</body>

</html>
