@php
    $logo = !empty($company['company_logo']) ? fetch_file($company['company_logo'], 'upload/logo/') : asset('assets/images/pwa/icon-192.png');
    $number = invoicePrefix() . $invoice->invoice_id;
    $overdue = $due > 0 && \Carbon\Carbon::parse($invoice->end_date)->lt(\Carbon\Carbon::today());
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Pay invoice') }} {{ $number }} · {{ $company['company_name'] ?? '' }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @if (($company['color_type'] ?? '') == 'custom' && !empty($company['custom_color']))
        <link rel="stylesheet" href="{{ asset('assets/css/custom-color.css') }}">
        <style>:root { {{ $company['custom_color'] }}; }</style>
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}">
    @endif
    <style>
        body { background: #f4f6f9; font-family: Roboto, system-ui, sans-serif; }
        .pay-wrap { max-width: 480px; margin: 0 auto; padding: 24px 16px 48px; }
        .pay-logo { max-height: 48px; max-width: 180px; }
        .pay-amount { font-size: 2.2rem; font-weight: 700; line-height: 1.1; }
        .pay-btn { width: 100%; padding: 14px; font-size: 1.05rem; font-weight: 600; border-radius: 10px; }
        .pay-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eef0f4; }
        .pay-row:last-child { border-bottom: 0; }
    </style>
</head>
<body data-pc-direction="{{ $company['theme_layout'] ?? 'ltr' }}" data-pc-theme="light"
    data-pc-preset="{{ ($company['color_type'] ?? '') == 'custom' ? 'custom' : ($company['accent_color'] ?? 'preset-1') }}">
    <div class="pay-wrap">
        <div class="text-center mb-4">
            <img src="{{ $logo }}" alt="" class="pay-logo mb-2">
            <div class="text-muted small">{{ $company['company_name'] ?? '' }}</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success"><i class="ti ti-circle-check"></i> {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="text-muted small">{{ __('Invoice') }} {{ $number }}</div>
                        @if ($tenantName)<div class="fw-semibold">{{ $tenantName }}</div>@endif
                    </div>
                    @if ($due <= 0)
                        <span class="badge bg-success">{{ __('Paid') }}</span>
                    @elseif ($overdue)
                        <span class="badge bg-danger">{{ __('Overdue') }}</span>
                    @else
                        <span class="badge bg-warning text-dark">{{ __('Due') }} {{ dateFormat($invoice->end_date) }}</span>
                    @endif
                </div>

                <div class="text-muted small">{{ $due > 0 ? __('Amount due') : __('Nothing left to pay') }}</div>
                <div class="pay-amount mb-3">{{ priceFormatFor($due, $company) }}</div>

                <div class="mb-4">
                    <div class="pay-row"><span class="text-muted">{{ __('Property') }}</span><span class="text-end">{{ optional($invoice->properties)->name }} · {{ optional($invoice->units)->name }}</span></div>
                    <div class="pay-row"><span class="text-muted">{{ __('Period') }}</span><span>{{ date('F Y', strtotime($invoice->invoice_month)) }}</span></div>
                    @foreach ($invoice->types as $item)
                        <div class="pay-row"><span class="text-muted">{{ optional($item->types)->title ?: __('Item') }}</span><span>{{ priceFormatFor($item->amount, $company) }}</span></div>
                    @endforeach
                    @if ($invoice->payments->count())
                        <div class="pay-row"><span class="text-muted">{{ __('Already paid') }}</span><span>-{{ priceFormatFor($invoice->payments->sum('amount'), $company) }}</span></div>
                    @endif
                </div>

                @if ($due > 0)
                    @if ($razorpay)
                        <button id="pay-razorpay" class="btn btn-secondary pay-btn mb-2"><i class="ti ti-qrcode"></i> {{ __('Pay with UPI, card or netbanking') }}</button>
                        <form id="rzp-verify" method="post" action="{{ route('pay.razorpay.verify', $token) }}" class="d-none">
                            @csrf
                            <input type="hidden" name="razorpay_order_id"><input type="hidden" name="razorpay_payment_id"><input type="hidden" name="razorpay_signature">
                        </form>
                    @endif
                    @if ($stripe)
                        <form method="post" action="{{ route('pay.stripe', $token) }}">
                            @csrf
                            <button class="btn {{ $razorpay ? 'btn-light-secondary' : 'btn-secondary' }} pay-btn mb-2"><i class="ti ti-credit-card"></i> {{ __('Pay by card') }}</button>
                        </form>
                    @endif
                    @if (!$stripe && !$razorpay)
                        <div class="alert alert-light-warning mb-2">{{ __('Online payment is not set up. Please log in or contact the office to pay.') }}</div>
                    @endif
                    <div id="pay-error" class="text-danger small text-center"></div>
                    @if (($payment['bank_transfer_payment'] ?? '') == 'on' && !empty($payment['bank_account_number']))
                        <details class="mt-3 small">
                            <summary class="text-muted">{{ __('Prefer a bank transfer?') }}</summary>
                            <div class="mt-2">
                                <div>{{ $payment['bank_name'] }} · {{ $payment['bank_holder_name'] }}</div>
                                <div>{{ __('Account') }}: {{ $payment['bank_account_number'] }} @if (!empty($payment['bank_ifsc_code'])) · {{ $payment['bank_ifsc_code'] }}@endif</div>
                                <div class="text-muted">{{ __('Use :number as the reference.', ['number' => $number]) }}</div>
                            </div>
                        </details>
                    @endif
                @endif
            </div>
        </div>

        <p class="text-center text-muted small mt-3">
            <i class="ti ti-lock"></i> {{ __('Payments are processed securely by the payment provider.') }}
            <br><a href="{{ route('login') }}">{{ __('Log in to see all your invoices') }}</a>
        </p>
    </div>

    @if ($due > 0 && $razorpay)
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            document.getElementById('pay-razorpay').addEventListener('click', function() {
                var btn = this, err = document.getElementById('pay-error');
                btn.disabled = true; err.textContent = '';
                fetch('{{ route('pay.razorpay', $token) }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); })
                    .then(function(o) {
                        if (o.error) throw new Error(o.error);
                        var rzp = new Razorpay({
                            key: o.key, order_id: o.order_id, amount: o.amount, currency: o.currency, name: o.name, description: o.description, prefill: o.prefill,
                            handler: function(r) {
                                var f = document.getElementById('rzp-verify');
                                f.razorpay_order_id.value = r.razorpay_order_id;
                                f.razorpay_payment_id.value = r.razorpay_payment_id;
                                f.razorpay_signature.value = r.razorpay_signature;
                                f.submit();
                            },
                            modal: { ondismiss: function() { btn.disabled = false; } }
                        });
                        rzp.on('payment.failed', function(r) { err.textContent = r.error.description; btn.disabled = false; });
                        rzp.open();
                    })
                    .catch(function(e) { err.textContent = e.message || @json(__('Something went wrong.')); btn.disabled = false; });
            });
        </script>
    @endif
</body>
</html>
