@extends('layouts.app')
@section('page-title')
    {{ __('Subscription') }}
@endsection
@push('script-page')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        $(document).on('click', '.have_coupon', function() {
            var element = $(this).parent().parent().parent().parent().parent().find('.coupon_div');
            if ($(this).is(":checked")) {
                $(element).removeClass('d-none');
            } else {
                $(element).addClass('d-none');
            }
        });

        $(document).on('click', '.packageCouponApplyBtn', function() {
            var element = $(this);
            var couponCode = element.closest('.row').find('.packageCouponCode').val();
            $.ajax({
                url: '{{ route('coupons.apply') }}',
                datType: 'json',
                data: {
                    package: '{{ \Illuminate\Support\Facades\Crypt::encrypt($subscription->id) }}',
                    coupon: couponCode
                },
                success: function(result) {
                    $('.discoutedPrice').text(result.discoutedPrice);
                    $('#amount').val(result.discoutedPrice);
                    if (result != '') {
                        if (result.status == true) {
                            toastrs('success', result.msg, 'success');
                        } else {
                            toastrs('Error', result.msg, 'error');
                        }
                    } else {
                        toastrs('Error', "{{ __('Please enter coupon code.') }}", 'error');
                    }
                }
            })
        });
    </script>

    <script>
        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
            var stripe_key = Stripe('{{ $settings['STRIPE_KEY'] }}');
            var stripe_elements = stripe_key.elements();
            var strip_css = {
                base: {
                    fontSize: '14px',
                    color: '#32325d',
                },
            };
            var stripe_card = stripe_elements.create('card', {
                style: strip_css
            });
            stripe_card.mount('#card-element');

            var stripe_form = document.getElementById('stripe-payment-form');
            stripe_form.addEventListener('submit', function(event) {
                event.preventDefault();

                var billingDetails = {
                    line1: document.querySelector('[name="state"]')?.value || '',
                    city: document.querySelector('[name="city"]')?.value || '',
                    postal_code: document.querySelector('[name="zipcode"]')?.value || '',
                    country: document.querySelector('[name="country"]')?.value || ''
                };

                stripe_key.createToken(stripe_card).then(function(result) {
                    if (result.error) {
                        $("#stripe_card_errors").html(result.error.message);
                        $.NotificationApp.send("Error", result.error.message, "top-right",
                            "rgba(0,0,0,0.2)", "error");
                    } else {
                        var token = result.token;
                        var stripeForm = document.getElementById('stripe-payment-form');
                        var stripeHiddenData = document.createElement('input');
                        stripeHiddenData.setAttribute('type', 'hidden');
                        stripeHiddenData.setAttribute('name', 'stripeToken');
                        stripeHiddenData.setAttribute('value', token.id);
                        stripeForm.appendChild(stripeHiddenData);
                        stripeForm.submit();
                    }
                });
            });
        @endif
    </script>

    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>

    <script>
        @if (
            $settings['flutterwave_payment'] == 'on' &&
                !empty($settings['flutterwave_public_key']) &&
                !empty($settings['flutterwave_secret_key']))

            $(document).on("click", "#flutterwavePaymentBtn", function() {
                var discoutedPrice = $('.discoutedPrice').text();
                var currency_symbol = '{{ $settings['CURRENCY_SYMBOL'] }}';
                var amount = discoutedPrice.replace(currency_symbol, "");
                var flutterwaveCallbackURL = "{{ url('subscription/flutterwave/') }}";
                var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
                var customer_email = '{{ \Auth::user()->email }}';
                var customer_name = '{{ \Auth::user()->name }}';
                var flutterwave_public_key = '{{ $settings['flutterwave_public_key'] }}';
                var nowTim = "{{ date('d-m-Y-h-i-a') }}";
                var currency = '{{ $settings['CURRENCY'] }}';

                if (amount) {
                    var flutterwavePayment = getpaidSetup({
                        txref: tx_ref,
                        PBFPubKey: flutterwave_public_key,
                        amount: amount,
                        currency: currency,
                        customer_name: customer_name,
                        customer_email: customer_email,
                        meta: [{
                            consumer_id: "23",
                            consumer_mac: "92a3-912ba-1192a"
                        }],
                        onclose: function() {},
                        callback: function(result) {
                            var txRef = result.tx.txRef;
                            var redirectUrl = flutterwaveCallbackURL + '/' +
                                '{{ \Illuminate\Support\Facades\Crypt::encrypt($subscription->id) }}' +
                                '/' + txRef;
                            if (result.tx.chargeResponseCode == "00" || result.tx.chargeResponseCode ==
                                "0") {
                                window.location.href = redirectUrl;
                            } else {
                                alert('Payment failed');
                            }
                            flutterwavePayment.close();
                        }
                    });
                } else {
                    alert('Please enter a valid amount');
                }
            });
        @endif
    </script>


    <script src="{{ asset('assets/js/plugins/jquery.form.min.js') }}"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    @if (isset($settings['paystack_payment']) && $settings['paystack_payment'] == 'on')
        <script>
            $(document).on("click", "#subscription_pay_with_paystack", function() {

                $('#paystack-payment-form').ajaxForm(function(res) {
                    if (res.flag == 1) {
                        var paystack_callback = "{{ url('/subscription/paystack') }}";
                        var order_id = '{{ time() }}';
                        var coupon_id = res.coupon;
                        var handler = PaystackPop.setup({
                            key: '{{ $settings['paystack_public_key'] }}',
                            email: res.email,
                            amount: res.total_price * 100,
                            currency: res.currency,
                            ref: 'pay_ref_id' + Math.floor((Math.random() * 1000000000) +
                                1
                            ), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you
                            metadata: {
                                custom_fields: [{
                                    display_name: "Email",
                                    variable_name: "email",
                                    value: res.email,
                                }]
                            },

                            callback: function(response) {
                                window.location.href = paystack_callback + '/' + response
                                    .reference + '/' + '{{ encrypt($subscription->id) }}' +
                                    '?coupon_id=' + coupon_id;
                            },
                            onClose: function() {
                                alert('window closed');
                            }
                        });
                        handler.openIframe();
                        console.log(handler);
                    } else if (res.flag == 2) {

                    } else {
                        show_toastr('Error', data.message, 'msg');
                    }

                }).submit();
            });
        </script>
    @endif
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @if (isset($settings['razorpay_payment']) && $settings['razorpay_payment'] == 'on')
        <script>
            $(document).on("click", "#pay_with_razorpay", function() {

                $('#razorpay-payment-form').ajaxForm(function(res) {
                    console.log(res.flag);
                    if (res.flag == 1) {

                        var razorPay_callback = '{{ url('/subscription/razorpay') }}';
                        var totalAmount = res.total_price * 100;
                        var coupon_id = res.coupon;
                        var options = {
                            "key": "{{ $settings['razorpay_api_key'] }}",
                            "amount": totalAmount,
                            "name": 'subscription',
                            "currency": '{{ $settings['CURRENCY'] }}',
                            "description": "",
                            "handler": function(response) {
                                window.location.href = razorPay_callback + '/' + response
                                    .razorpay_payment_id + '/' +
                                    '{{ \Illuminate\Support\Facades\Crypt::encrypt($subscription->id) }}?coupon_id=' +
                                    coupon_id;
                            },
                            "theme": {
                                "color": "#528FF0"
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    } else if (res.flag == 2) {
                        toastrs('success', res.msg, 'success');

                        setInterval(function() {
                            window.location.href = "{{ route('subscriptions.index') }}";
                        }, 2000);
                    } else {
                        toastrs('Error', data.message, 'msg');
                    }

                }).submit();
            });
        </script>
    @endif
@endpush
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('subscriptions.index') }}">{{ __('Subscription') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Details') }}</a>
        </li>
    </ul>
@endsection

@section('content')
    <div class="row pricing-grid">
        <div class="col-12">
            <div class="card">
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Interval') }}</th>
                                    <th>{{ __('User Limit') }}</th>
                                    <th>{{ __('Property Limit') }}</th>
                                    <th>{{ __('Tenant Limit') }}</th>
                                    <th>{{ __('Coupon Applicable') }}</th>
                                    <th>{{ __('User Logged History') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {{ $subscription->title }}
                                    </td>
                                    <td>
                                        <b class="discoutedPrice">
                                            {{ subscriptionPaymentSettings()['CURRENCY_SYMBOL'] }}{{ $subscription->package_amount }}</b>
                                    </td>
                                    <td>{{ $subscription->interval }} </td>
                                    <td>{{ $subscription->user_limit }} </td>
                                    <td>{{ $subscription->property_limit }}</td>
                                    <td>{{ $subscription->tenant_limit }}</td>
                                    <td>
                                        @if ($subscription->couponCheck() > 0)
                                            <i class="text-success mr-4" data-feather="check-circle"></i>
                                        @else
                                            <i class="text-danger mr-4" data-feather="x-circle"></i>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($subscription->enabled_logged_history == 1)
                                            <i class="text-success mr-4" data-feather="check-circle"></i>
                                        @else
                                            <i class="text-danger mr-4" data-feather="x-circle"></i>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="row pricing-grid">
            <div class="col-lg-12">
                <div class="row">
                    @if ($settings['bank_transfer_payment'] == 'on')
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="d-inline">{{ __('Bank Transfer Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu float-end">
                                            <div class="form-group mb-0">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_bank_tran_coupon">
                                                    <label class="form-check-label"
                                                        for="have_bank_tran_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form
                                        action="{{ route('subscription.bank.transfer', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}"
                                        method="post" class="require-validation" id="bank-payment"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank Name') }}</label>
                                                    <p>{{ $settings['bank_name'] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank Holder Name') }}</label>
                                                    <p>{{ $settings['bank_holder_name'] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank Account Number') }}</label>
                                                    <p>{{ $settings['bank_account_number'] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Bank IFSC Code') }}</label>
                                                    <p>{{ $settings['bank_ifsc_code'] }}</p>
                                                </div>
                                            </div>
                                            @if (!empty($settings['bank_other_details']))
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Bank Other Details') }}</label>
                                                        <p>{{ $settings['bank_other_details'] }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="col-md-12 d-none coupon_div">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                    <input type="text" name="coupon"
                                                        class="form-control required packageCouponCode"
                                                        placeholder="{{ __('Enter Coupon Code') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Attachment') }}</label>
                                                    <input type="file" name="payment_receipt" id="payment_receipt"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 ">
                                                <input type="button" value="{{ __('Coupon Apply') }}"
                                                    class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                <input type="submit" value="{{ __('Pay') }}"
                                                    class="btn btn-secondary">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="d-inline">{{ __('Stripe Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu float-end">
                                            <div class="form-group mb-0">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_stripe_coupon">
                                                    <label class="form-check-label"
                                                        for="have_stripe_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form
                                        action="{{ route('subscription.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}"
                                        method="post" class="require-validation" id="stripe-payment-form">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="card-name-on"
                                                        class="form-label text-dark">{{ __('Card Name') }}</label>
                                                    <input type="text" name="name" id="card-name-on"
                                                        class="form-control required"
                                                        placeholder="{{ __('Card Holder Name') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="card-name-on"
                                                    class="form-label text-dark">{{ __('Card Details') }}</label>
                                                <div id="card-element">
                                                </div>
                                                <div id="stripe_card_errors" role="alert"></div>
                                            </div>

                                            @if ($subscription->couponCheck() > 0)
                                                <div class="col-md-12 d-none coupon_div">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                        <input type="text" name="coupon"
                                                            class="form-control required packageCouponCode"
                                                            placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12 mt-15">
                                                @if ($subscription->couponCheck() > 0)
                                                    <input type="button" value="{{ __('Coupon Apply') }}"
                                                        class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                @endif
                                                <input type="submit" value="{{ __('Pay') }}"
                                                    class="btn btn-secondary">

                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (
                        $settings['flutterwave_payment'] == 'on' &&
                            !empty($settings['flutterwave_public_key']) &&
                            !empty($settings['flutterwave_secret_key']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="d-inline">{{ __('Flutterwave Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu float-end">
                                            <div class="form-group mb-0">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_flutterwave_coupon">
                                                    <label class="form-check-label"
                                                        for="have_flutterwave_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form action="#" class="require-validation" method="get">
                                        @csrf
                                        <div class="row">
                                            @if ($subscription->couponCheck() > 0)
                                                <div class="col-md-12 d-none coupon_div">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                        <input type="text" name="coupon"
                                                            class="form-control required packageCouponCode"
                                                            placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12">
                                                @if ($subscription->couponCheck() > 0)
                                                    <input type="button" value="{{ __('Coupon Apply') }}"
                                                        class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                @endif
                                                <input type="button" value="{{ __('Pay') }}"
                                                    id="flutterwavePaymentBtn" class="btn btn-secondary">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (
                        $settings['paypal_payment'] == 'on' &&
                            !empty($settings['paypal_client_id']) &&
                            !empty($settings['paypal_secret_key']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="d-inline">{{ __('Paypal Payment') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu float-end">
                                            <div class="form-group mb-0">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_paypal_coupon">
                                                    <label class="form-check-label"
                                                        for="have_paypal_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form
                                        action="{{ route('subscription.paypal', \Illuminate\Support\Facades\Crypt::encrypt($subscription->id)) }}"
                                        method="post" class="require-validation">
                                        @csrf
                                        <div class="row">
                                            @if ($subscription->couponCheck() > 0)
                                                <div class="col-md-12 mt-15 d-none coupon_div">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                        <input type="text" name="coupon"
                                                            class="form-control required packageCouponCode"
                                                            placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12 mt-15">
                                                @if ($subscription->couponCheck() > 0)
                                                    <input type="button" value="{{ __('Coupon Apply') }}"
                                                        class="btn btn-primary packageCouponApplyBtn d-none coupon_div">
                                                @endif
                                                <input type="submit" value="{{ __('Pay') }}"
                                                    class="btn btn-secondary">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (
                        $settings['paystack_payment'] == 'on' &&
                            !empty($settings['paystack_public_key']) &&
                            !empty($settings['paystack_secret_key']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="d-inline">{{ __('Paystack') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu float-end">
                                            <div class="form-group mb-0">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_paystack_coupon">
                                                    <label class="form-check-label"
                                                        for="have_paystack_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <form class="require-validation" method="POST" id="paystack-payment-form"
                                        action="{{ route('subscription.pay.with.paystack') }}">
                                        @csrf
                                        <input type="hidden" name="plan_id"
                                            value="{{ \Illuminate\Support\Facades\Crypt::encrypt($subscription->id) }}">
                                        <div class="row">
                                            @if ($subscription->couponCheck() > 0)
                                                <div class="col-md-12 d-none coupon_div">
                                                    <div class="form-group">
                                                        <label for="card-name-on"
                                                            class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                        <input type="text" name="coupon"
                                                            class="form-control required packageCouponCode"
                                                            placeholder="{{ __('Enter Coupon Code') }}">
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-sm-12">
                                                @if ($subscription->couponCheck() > 0)
                                                    <input type="button" value="{{ __('Coupon Apply') }}"
                                                        class="btn btn-warning packageCouponApplyBtn d-none coupon_div">
                                                @endif
                                                <input type="button" value="{{ __('Pay') }}"
                                                    id="subscription_pay_with_paystack" class="btn btn-secondary">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (
                        $settings['razorpay_payment'] == 'on' &&
                            !empty($settings['razorpay_api_key']) &&
                            !empty($settings['razorpay_secret_key']))
                        <div class="col-sm-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="d-inline">{{ __('Razorpay') }}</h5>
                                    @if ($subscription->couponCheck() > 0)
                                        <div class="setting-card action-menu float-end">
                                            <div class="form-group mb-0">
                                                <div class="form-check custom-chek">
                                                    <input class="form-check-input have_coupon" type="checkbox"
                                                        value="" id="have_razorpay_coupon">
                                                    <label class="form-check-label"
                                                        for="have_razorpay_coupon">{{ __('Have a Discount Coupon Code?') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body profile-user-box">
                                    <div class="" id="razorpay-payment">
                                        <form action="{{ route('subscription.pay.with.razorpay') }}" method="post"
                                            class="require-validation" id="razorpay-payment-form">
                                            @csrf
                                            <input type="hidden" name="plan_id"
                                                value="{{ \Illuminate\Support\Facades\Crypt::encrypt($subscription->id) }}">
                                            <div class="row">
                                                @if ($subscription->couponCheck() > 0)
                                                    <div class="col-md-12 d-none coupon_div">
                                                        <div class="form-group">
                                                            <label for="card-name-on"
                                                                class="form-label text-dark">{{ __('Coupon Code') }}</label>
                                                            <input type="text" name="coupon"
                                                                class="form-control required packageCouponCode"
                                                                placeholder="{{ __('Enter Coupon Code') }}">
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-sm-12 mt-15">
                                                    @if ($subscription->couponCheck() > 0)
                                                        <input type="button" value="{{ __('Coupon Apply') }}"
                                                            class="btn btn-warning packageCouponApplyBtn d-none coupon_div">
                                                    @endif
                                                    <input type="button" id="pay_with_razorpay"
                                                        value="{{ __('Pay') }}" class="btn btn-secondary">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
