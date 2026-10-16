@extends('layouts.app')

@section('page-title')
    {{ __('Autopay') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Autopay') }}</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Automatic Rent Payment') }}</h5></div>
                <div class="card-body">
                    @if (!$available)
                        <div class="alert alert-light-warning mb-0">{{ __('Automatic payment is not available. Please contact your landlord.') }}</div>
                    @elseif ($tenant->autopay_enabled)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avtar avtar-l bg-light-success"><i class="ti ti-{{ $tenant->card_brand == 'UPI' ? 'qrcode' : 'credit-card' }} f-24"></i></div>
                            <div>
                                @if ($tenant->card_brand == 'UPI')
                                    <div class="fw-semibold">{{ __('UPI AutoPay') }} · {{ $tenant->card_last4 }}</div>
                                @else
                                    <div class="fw-semibold">{{ ucfirst($tenant->card_brand) }} **** {{ $tenant->card_last4 }}</div>
                                @endif
                                <div class="text-success small">{{ __('Autopay is on') }} · {{ $tenant->autopay_gateway == 'razorpay' ? 'Razorpay' : 'Stripe' }}</div>
                            </div>
                        </div>
                        <p class="text-muted">{{ __('On each invoice due date the amount still owed is charged automatically. You get the usual payment confirmation, and an email if a charge fails.') }}</p>
                        {{ Form::open(['route' => 'autopay.disable', 'method' => 'post', 'onsubmit' => "return confirm('" . __('Switch off autopay and remove this card?') . "')"]) }}
                        <button type="submit" class="btn btn-light-danger">{{ __('Switch off autopay') }}</button>
                        {{ Form::close() }}
                    @else
                        <p class="text-muted">{{ __('Set up automatic payment once and your rent is paid on each due date. Your card or UPI details go directly to the payment provider and are never stored by us.') }}</p>
                        @if ($razorpayAvailable)
                            <div class="border rounded p-3 mb-3">
                                <div class="fw-semibold mb-1">{{ __('UPI AutoPay or card (Razorpay)') }}</div>
                                <p class="small text-muted mb-2">{{ __('You approve a :amount authorisation once; it is refunded straight away. Each debit is limited to :max.', ['amount' => priceFormat(\App\Support\RazorpayAutopay::AUTH_AMOUNT), 'max' => priceFormat($maxAmount)]) }}</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-secondary rzp-autopay" data-method="upi"><i class="ti ti-qrcode"></i> {{ __('Set up UPI AutoPay') }}</button>
                                    <button class="btn btn-light-secondary rzp-autopay" data-method="card"><i class="ti ti-credit-card"></i> {{ __('Use a card') }}</button>
                                </div>
                                <div id="rzp-error" class="text-danger small mt-2"></div>
                                {{ Form::open(['route' => 'autopay.razorpay.save', 'method' => 'post', 'id' => 'rzp-form']) }}
                                <input type="hidden" name="razorpay_order_id"><input type="hidden" name="razorpay_payment_id"><input type="hidden" name="razorpay_signature">
                                {{ Form::close() }}
                            </div>
                        @endif
                        @if ($stripeAvailable)
                            <div class="border rounded p-3">
                                <div class="fw-semibold mb-2">{{ __('Card (Stripe)') }}</div>
                                <div id="autopay-card" class="form-control py-3 mb-2"></div>
                                <div id="autopay-error" class="text-danger small mb-2"></div>
                                <button id="autopay-save" class="btn btn-secondary">{{ __('Save card & switch on autopay') }}</button>
                                {{ Form::open(['route' => 'autopay.save', 'method' => 'post', 'id' => 'autopay-form']) }}
                                <input type="hidden" name="setup_intent" id="autopay-setup-intent">
                                {{ Form::close() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($available && !$tenant->autopay_enabled && $razorpayAvailable)
    @push('script-page')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            $('.rzp-autopay').on('click', function() {
                var $btns = $('.rzp-autopay').prop('disabled', true), $err = $('#rzp-error').text('');
                $.post('{{ route('autopay.razorpay.order') }}', { _token: '{{ csrf_token() }}', method: $(this).data('method') }).done(function(o) {
                    var rzp = new Razorpay({
                        key: o.key, order_id: o.order_id, customer_id: o.customer_id, recurring: '1', amount: o.amount, currency: 'INR',
                        name: o.company, description: @json(__('Autopay authorisation (refunded)')),
                        prefill: { name: o.name, email: o.email, contact: o.contact, method: o.method },
                        handler: function(r) {
                            var $f = $('#rzp-form');
                            $f.find('[name=razorpay_order_id]').val(r.razorpay_order_id);
                            $f.find('[name=razorpay_payment_id]').val(r.razorpay_payment_id);
                            $f.find('[name=razorpay_signature]').val(r.razorpay_signature);
                            $f.submit();
                        },
                        modal: { ondismiss: function() { $btns.prop('disabled', false); } }
                    });
                    rzp.on('payment.failed', function(r) { $err.text(r.error.description); $btns.prop('disabled', false); });
                    rzp.open();
                }).fail(function(xhr) {
                    $err.text((xhr.responseJSON && xhr.responseJSON.error) || '{{ __('Something went wrong.') }}');
                    $btns.prop('disabled', false);
                });
            });
        </script>
    @endpush
@endif

@if ($available && !$tenant->autopay_enabled && $stripeAvailable)
    @push('script-page')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            (function() {
                var stripe = Stripe(@json($stripeKey));
                var card = stripe.elements().create('card', { hidePostalCode: true });
                card.mount('#autopay-card');
                var $btn = $('#autopay-save'), $err = $('#autopay-error');
                $btn.on('click', function() {
                    $btn.prop('disabled', true); $err.text('');
                    $.post('{{ route('autopay.setup-intent') }}', { _token: '{{ csrf_token() }}' }).done(function(res) {
                        stripe.confirmCardSetup(res.client_secret, { payment_method: { card: card } }).then(function(result) {
                            if (result.error) { $err.text(result.error.message); $btn.prop('disabled', false); return; }
                            $('#autopay-setup-intent').val(result.setupIntent.id);
                            $('#autopay-form').submit();
                        });
                    }).fail(function(xhr) {
                        $err.text((xhr.responseJSON && xhr.responseJSON.error) || '{{ __('Something went wrong.') }}');
                        $btn.prop('disabled', false);
                    });
                });
            })();
        </script>
    @endpush
@endif
