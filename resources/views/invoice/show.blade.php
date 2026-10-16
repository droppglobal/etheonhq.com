@extends('layouts.app')
@section('page-title')
    {{ __('Invoice') }}
@endsection
@php
    $main_logo = getSettingsValByName('company_logo');
    $settings = settings();

@endphp


@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Details') }}</a>
        </li>
    </ul>
@endsection
@section('content')
    @if (!empty($invoice->autopay_error) && in_array($invoice->status, ['unpaid', 'partial_paid']))
        <div class="alert alert-light-danger">{{ __('Automatic card payment failed on') }} {{ dateFormat($invoice->autopay_attempted_at) }}: {{ $invoice->autopay_error }}</div>
    @endif
    <div class="row">

        <div class="{{ $invoice->getInvoiceDueAmount() > 0 ? 'col-lg-8 col-md-12' : 'col-lg-12 col-md-12' }}">
            <div>
                <div class="card">
                    <div class="card-header d-print-none">
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            @if (\Auth::user()->type == 'owner')
                                @if ($invoice->getInvoiceDueAmount() > 0)
                                    <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                        data-url="{{ route('invoice.reminder', $invoice->id) }}"
                                        data-title="{{ __('Payment Reminder') }}">
                                        {{ __('Payment Reminder') }}
                                    </a>
                                @endif
                            @endif
                            @if ($invoice->getInvoiceDueAmount() > 0 && \Auth::user()->type != 'tenant' && (Gate::check('edit invoice') || Gate::check('create invoice')) && \App\Support\PayLink::canPayOnline($invoice->parent_id))
                                <div class="dropdown">
                                    <button class="btn btn-light-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-link"></i> {{ __('Payment Link') }}</button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="#" class="dropdown-item pay-link" data-channel="copy"><i class="ti ti-copy"></i> {{ __('Copy link') }}</a>
                                        <a href="#" class="dropdown-item pay-link" data-channel="wa-me"><i class="ti ti-brand-whatsapp"></i> {{ __('Share on WhatsApp') }}</a>
                                        @if (!empty(settings()['twilio_sid']))
                                            <a href="#" class="dropdown-item pay-link" data-channel="whatsapp"><i class="ti ti-send"></i> {{ __('Send via WhatsApp (Twilio)') }}</a>
                                        @endif
                                        <a href="#" class="dropdown-item pay-link" data-channel="email"><i class="ti ti-mail"></i> {{ __('Send by email') }}</a>
                                    </div>
                                </div>
                            @endif
                            @if (\Auth::user()->type != 'tenant' && Gate::check('edit invoice') && ($invoice->getInvoiceDueAmount() > 0 || $invoice->getPaidTotal() > 0))
                                <a href="#" class="btn btn-light-secondary customModal" data-size="md"
                                    data-url="{{ route('invoice.credit.create', $invoice->id) }}" data-title="{{ __('Credit note or refund') }}">
                                    <i class="ti ti-receipt-refund"></i> {{ __('Credit / Refund') }}
                                </a>
                            @endif
                            <a href="#" class="btn btn-primary print">
                                {{ __('Print') }}
                            </a>
                        </div>
                    </div>

                    <div class="card-body" id="invoice-print">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="row align-items-center g-3">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-2 navbar-brand img-fluid invoice-logo">
                                            <img src="{{ !empty($main_logo) ? fetch_file($main_logo, 'upload/logo/') : '#' }}"
                                                class="img-fluid brand-logo" alt="images" />
                                        </div>
                                        <p class="mb-0 fw-semibold">{{ invoicePrefix() . $invoice->invoice_id }}</p>
                                    </div>
                                    <div class="col-sm-6 text-sm-end">
                                        <h6 class="mb-1">
                                            {{ __('Invoice Month') }}
                                            <span
                                                class="text-muted f-w-400">{{ date('F Y', strtotime($invoice->invoice_month)) }}</span>
                                        </h6>
                                        <h6 class="mb-1">
                                            {{ __('Due Date') }}
                                            <span class="text-muted f-w-400">{{ dateFormat($invoice->end_date) }}</span>
                                        </h6>
                                        <h6 class="mb-0">
                                            {{ __('Status') }}
                                            <span class="text-muted f-w-400">
                                                @if ($invoice->status == 'unpaid')
                                                    <span
                                                        class="badge text-bg-danger">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                @elseif($invoice->status == 'paid')
                                                    <span
                                                        class="badge text-bg-success">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                @elseif($invoice->status == 'partial_paid')
                                                    <span
                                                        class="badge text-bg-warning">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                @endif
                                            </span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted text-uppercase small">{{ __('From') }}:</h6>
                                    <h5 class="mb-1">{{ $settings['company_name'] }}</h5>
                                    <p class="mb-0">{{ $settings['company_phone'] }}</p>
                                    <p class="mb-0">{{ $settings['company_email'] }}</p>
                                    <p class="mb-0">{{ $settings['tax_title'] ?? __('Tax Number') }}:
                                        {{ $settings['tax_number'] }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="mb-2 text-muted text-uppercase small">{{ __('To') }}:</h6>
                                    <h5 class="mb-1">
                                        {{ !empty($tenant) && !empty($tenant->user) ? $tenant->user->first_name . ' ' . $tenant->user->last_name : '' }}
                                    </h5>
                                    <p class="mb-0">
                                        {{ !empty($tenant) && !empty($tenant->user) ? $tenant->user->phone_number : '-' }}
                                    </p>
                                    <p class="mb-0">
                                        {{ !empty($tenant) ? $tenant->address : '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th width="46%">{{ __('Type') }}</th>
                                                <th width="46%">{{ __('Description') }}</th>
                                                <th class="text-end">{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice->types as $k => $type)
                                                <tr>
                                                    <td>{{ !empty($type->types) ? $type->types->title : '-' }}</td>
                                                    <td>{{ $type->description }}</td>
                                                    <td class="text-end">{{ priceFormat($type->amount) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-start">
                                    <hr class="mb-2 mt-1 border-secondary border-opacity-50" />
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="invoice-total ms-auto" style="max-width: 320px; margin-left: auto;">
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="f-w-600 mb-1 text-start">{{ __('Total') }} :</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="f-w-600 mb-1 text-end">
                                                {{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}
                                            </p>
                                        </div>
                                        @if ($invoice->getCreditTotal() > 0)
                                            <div class="col-6"><p class="mb-1 text-start text-muted">{{ __('Credits') }} :</p></div>
                                            <div class="col-6"><p class="mb-1 text-end text-muted">-{{ priceFormat($invoice->getCreditTotal()) }}</p></div>
                                        @endif
                                        @if ($invoice->payments->count())
                                            <div class="col-6"><p class="mb-1 text-start text-muted">{{ __('Paid') }} :</p></div>
                                            <div class="col-6"><p class="mb-1 text-end text-muted">-{{ priceFormat($invoice->getPaidTotal()) }}</p></div>
                                        @endif
                                        <div class="col-6">
                                            <p class="f-w-600 mb-1 text-start">{{ __('Due Amount') }} :</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="f-w-600 mb-1 text-end">
                                                {{ priceFormat($invoice->getInvoiceDueAmount()) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($invoice->getInvoiceDueAmount() > 0)
            @if (\Auth::user()->type == 'tenant')
                <div class="mt-25 col-lg-4 col-md-12 d-print-none" id="paymentModal">
                    <div class="card">
                        <div class="col-xxl-12 cdx-xxl-100">
                            <div class="payment-method">
                                <div class="card-header">
                                    <h5>{{ __('Add Payment') }}</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs profile-tabs border-bottom mb-3" id="myTab" role="tablist">
                                        @if ($settings['bank_transfer_payment'] == 'on')
                                            <li class="nav-item">
                                                <a class="nav-link active text-sm" id="profile-tab-1" data-bs-toggle="tab"
                                                    href="#bank_transfer" role="tab"
                                                    aria-selected="true">{{ __('Bank Transfer') }} </a>

                                            </li>
                                        @endif

                                        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                                            <li class="nav-item">
                                                <a class="nav-link text-sm" id="profile-tab-2" data-bs-toggle="tab"
                                                    href="#stripe_payment" role="tab"
                                                    aria-selected="true">{{ __('Stripe') }}</a>
                                            </li>
                                        @endif

                                        @if (
                                            $settings['paypal_payment'] == 'on' &&
                                                !empty($settings['paypal_client_id']) &&
                                                !empty($settings['paypal_secret_key']))
                                            <li class="nav-item">
                                                <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                                    href="#paypal_payment" role="tab" aria-selected="true">
                                                    {{ __('Paypal') }} </a>
                                            </li>
                                        @endif

                                        @if (
                                            $settings['flutterwave_payment'] == 'on' &&
                                                !empty($settings['flutterwave_public_key']) &&
                                                !empty($settings['flutterwave_secret_key']))
                                            <li class="nav-item">
                                                <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                                    href="#flutterwave_payment" role="tab" aria-selected="true">
                                                    {{ __('Flutterwave') }}
                                                </a>
                                            </li>
                                        @endif

                                        @if (
                                            $settings['paystack_payment'] == 'on' &&
                                                !empty($settings['paystack_public_key']) &&
                                                !empty($settings['paystack_secret_key']))
                                            <li class="nav-item">
                                                <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                                    href="#paystack_payment" role="tab" aria-selected="true">
                                                    {{ __('Paystack') }}
                                                </a>
                                            </li>
                                        @endif

                                        @if (
                                            $settings['razorpay_payment'] == 'on' &&
                                                !empty($settings['razorpay_api_key']) &&
                                                !empty($settings['razorpay_secret_key']))
                                            <li class="nav-item">
                                                <a class="nav-link text-sm" id="profile-tab-3" data-bs-toggle="tab"
                                                    href="#razorpay_payment" role="tab" aria-selected="true">
                                                    {{ __('Razorpay') }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>

                                    <div class="tab-content">
                                        @if ($settings['bank_transfer_payment'] == 'on')
                                            <div class="tab-pane fade active show" id="bank_transfer">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form
                                                                action="{{ route('invoice.banktransfer.payment', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
                                                                method="post" class="require-validation"
                                                                id="bank-payment" enctype="multipart/form-data">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Name') }}</label>
                                                                            <p>{{ $settings['bank_name'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Holder Name') }}</label>
                                                                            <p>{{ $settings['bank_holder_name'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Account Number') }}</label>
                                                                            <p>{{ $settings['bank_account_number'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank IFSC Code') }}</label>
                                                                            <p>{{ $settings['bank_ifsc_code'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    @if (!empty($settings['bank_other_details']))
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label for="card-name-on"
                                                                                    class="f-w-600 mb-1 text-start">{{ __('Bank Other Details') }}</label>
                                                                                <p>{{ $settings['bank_other_details'] }}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="form-label text-dark">{{ __('Attachment') }}</label>
                                                                            <input type="file" name="receipt"
                                                                                id="receipt" class="form-control"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="notes"
                                                                                class="form-label text-dark">{{ __('Notes') }}</label>
                                                                            <input type="text" name="notes"
                                                                                class="form-control " value=""
                                                                                placeholder="{{ __('Enter notes') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 ">
                                                                        <input type="submit" value="{{ __('Pay') }}"
                                                                            class="btn btn-secondary">
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                                            <div class="tab-pane fade" id="stripe_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form
                                                                action="{{ route('invoice.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
                                                                method="post" class="require-validation"
                                                                id="stripe-payment">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="form-label text-dark">{{ __('Card Name') }}</label>
                                                                            <input type="text" name="name"
                                                                                id="card-name-on"
                                                                                class="form-control required"
                                                                                placeholder="{{ __('Card Holder Name') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <label for="card-name-on"
                                                                            class="form-label text-dark">{{ __('Card Details') }}</label>
                                                                        <div id="card-element">
                                                                        </div>
                                                                        <div id="card-errors" role="alert"></div>
                                                                    </div>
                                                                    <div class="col-sm-12 mt-3">

                                                                        <input type="submit" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-secondary">
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (
                                            $settings['paypal_payment'] == 'on' &&
                                                !empty($settings['paypal_client_id']) &&
                                                !empty($settings['paypal_secret_key']))
                                            <div class="tab-pane fade" id="paypal_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form
                                                                action="{{ route('invoice.paypal', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
                                                                method="post" class="require-validation">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 ">
                                                                        <input type="submit" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-secondary">
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (
                                            $settings['flutterwave_payment'] == 'on' &&
                                                !empty($settings['flutterwave_public_key']) &&
                                                !empty($settings['flutterwave_secret_key']))
                                            <div class="tab-pane fade" id="flutterwave_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form action="#" method="post"
                                                                class="require-validation" id="flutterwavePaymentForm">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">

                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control amount required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-12 ">
                                                                        <input type="button" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-secondary"
                                                                            id="flutterwavePaymentBtn">
                                                                    </div>

                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (
                                            $settings['paystack_payment'] == 'on' &&
                                                !empty($settings['paystack_public_key']) &&
                                                !empty($settings['paystack_secret_key']))
                                            <div class="tab-pane fade" id="paystack_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form class="require-validation" method="POST"
                                                                id="paystack-payment-form"
                                                                action="{{ route('invoice.paystack.payment', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}">

                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">

                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control amount required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-12 ">
                                                                        <input type="button" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-secondary"
                                                                            id="paystackPaymentBtn">
                                                                    </div>

                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (
                                            $settings['razorpay_payment'] == 'on' &&
                                                !empty($settings['razorpay_api_key']) &&
                                                !empty($settings['razorpay_secret_key']))
                                            <div class="tab-pane fade" id="razorpay_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form class="require-validation" method="POST"
                                                                id="razorpay-payment-form"
                                                                action="{{ route('invoice.razorpay.payment', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}">

                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">

                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control amount required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-12 ">
                                                                        <input type="button" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-secondary"
                                                                            id="razorpayPaymentBtn">
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
                    </div>
                </div>
            @else
                <div class="mt-25 col-lg-4 col-md-12 d-print-none" id="paymentModal">
                    <div class="card">
                        <div class="col-xxl-12 cdx-xxl-100">
                            <div class="payment-method">
                                <div class="card-header">
                                    <h5>{{ __('Add Payment') }}</h5>
                                </div>
                                <div class="card-body">
                                    {{ Form::open(['route' => ['invoice.payment.store', $invoice->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="form-group  col-md-12">
                                                {{ Form::label('payment_date', __('Payment Date'), ['class' => 'form-label']) }}
                                                {{ Form::date('payment_date', date('Y-m-d'), ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group  col-md-12">
                                                {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                                                {{ Form::number('amount', $invoice->getInvoiceDueAmount(), ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group  col-md-12">
                                                {{ Form::label('receipt', __('Receipt'), ['class' => 'form-label']) }}
                                                {{ Form::file('receipt', ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group ">
                                                {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Payment Notes')]) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        {{ Form::submit(__('Add'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <div class="row d-print-none">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Payment History') }}</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover ">
                            <thead>
                                <tr>
                                    <th>{{ __('Transaction Id') }}</th>
                                    <th>{{ __('Payment Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Receipt') }}</th>
                                    @can('delete invoice payment')
                                        <th class="text-end">{{ __('Action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->payments as $payment)
                                    <tr role="row">
                                        <td>{{ $payment->transaction_id }} </td>
                                        <td>{{ dateFormat($payment->payment_date) }} </td>
                                        <td class="{{ $payment->amount < 0 ? 'text-danger' : '' }}">{{ $payment->amount < 0 ? '-' . priceFormat(abs($payment->amount)) : priceFormat($payment->amount) }}</td>
                                        <td>{{ $payment->notes }} </td>
                                        <td>
                                            @if ($payment->amount > 0)
                                                <a href="{{ route('invoice.payment.receipt', [$invoice->id, $payment->id]) }}" class="me-2" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download receipt (PDF)') }}"><i class="ti ti-file-download f-18"></i></a>
                                            @endif
                                            @if (!empty($payment->receipt))
                                                <a href="{{ fetch_file($payment->receipt, 'upload/receipt/') }}" download="download" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Uploaded proof') }}"><i data-feather="paperclip"></i></a>
                                            @endif
                                        </td>
                                        @can('delete invoice payment')
                                            <td class="text-end">
                                                @if ($payment->amount > 0)
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.payment.destroy', $invoice->id, $payment->id]]) !!}
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Delete') }}" href="#"> <i
                                                            data-feather="trash-2"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                                @endif
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($invoice->creditNotes->count())
        <div class="row d-print-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h5>{{ __('Credit Notes & Refunds') }}</h5></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>{{ __('Number') }}</th><th>{{ __('Type') }}</th><th>{{ __('Date') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Reason') }}</th><th class="text-end">{{ __('Action') }}</th></tr></thead>
                                <tbody>
                                    @foreach ($invoice->creditNotes as $note)
                                        <tr>
                                            <td class="fw-semibold">{{ $note->displayNumber() }}</td>
                                            <td><span class="badge bg-light-{{ $note->type == 'refund' ? 'warning' : 'info' }}">{{ \App\Models\CreditNote::types()[$note->type] }}</span></td>
                                            <td>{{ dateFormat($note->date) }}</td>
                                            <td>{{ priceFormat($note->amount) }}</td>
                                            <td>{{ $note->reason }}{{ $note->method ? ' · ' . $note->method : '' }}</td>
                                            <td class="text-end">
                                                <div class="cart-action">
                                                    <a href="{{ route('invoice.credit.pdf', [$invoice->id, $note->id]) }}" class="avtar avtar-xs btn-link-secondary" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Download PDF') }}"><i class="ti ti-file-download f-18"></i></a>
                                                    @if (\Auth::user()->type != 'tenant' && Gate::check('edit invoice'))
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.credit.destroy', $invoice->id, $note->id], 'class' => 'd-inline']) !!}
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                                        {!! Form::close() !!}
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@push('script-page')
    <script>
        $(document).on('click', '.pay-link', function(e) {
            e.preventDefault();
            var channel = $(this).data('channel');
            var phone = @json(preg_replace('/\D+/', '', (string) optional(optional($invoice->tenants)->user)->phone_number));
            // open the WhatsApp window right away (inside the click) so pop-up blockers allow it
            var waWindow = channel == 'wa-me' ? window.open('about:blank', '_blank') : null;
            $.post('{{ route('invoice.pay-link.send', $invoice->id) }}', { _token: '{{ csrf_token() }}', channel: channel }).done(function(res) {
                if (channel == 'copy') {
                    (navigator.clipboard ? navigator.clipboard.writeText(res.url) : Promise.reject()).then(function() {
                        toastrs('{{ __('Success') }}', '{{ __('Payment link copied') }}', 'success');
                    }, function() { window.prompt('{{ __('Copy this link') }}', res.url); });
                } else if (channel == 'wa-me') {
                    waWindow.location = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(res.text);
                } else {
                    toastrs(res.sent ? '{{ __('Success') }}' : '{{ __('Error') }}', res.sent ? '{{ __('Payment link sent') }}' : '{{ __('Could not send. Check the tenant contact details and your WhatsApp / email settings.') }}', res.sent ? 'success' : 'error');
                }
            }).fail(function(xhr) {
                if (waWindow) waWindow.close();
                toastrs('{{ __('Error') }}', (xhr.responseJSON && xhr.responseJSON.error) || '{{ __('Something went wrong.') }}', 'error');
            });
        });
    </script>
    <script>
        $(document).on('click', '.print', function() {
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        });
    </script>
    @if (\Auth::user()->type == 'tenant')
        @if (
            $invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' &&
                !empty($invoicePaymentSettings['STRIPE_KEY']) &&
                !empty($invoicePaymentSettings['STRIPE_SECRET']))
            <script src="https://js.stripe.com/v3/"></script>

            <script type="text/javascript">
                var stripe_key = Stripe('{{ $invoicePaymentSettings['STRIPE_KEY'] }}');
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

                var stripe_form = document.getElementById('stripe-payment');
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
                            var stripeForm = document.getElementById('stripe-payment');
                            var stripeHiddenData = document.createElement('input');
                            stripeHiddenData.setAttribute('type', 'hidden');
                            stripeHiddenData.setAttribute('name', 'stripeToken');
                            stripeHiddenData.setAttribute('value', token.id);
                            stripeForm.appendChild(stripeHiddenData);
                            stripeForm.submit();
                        }
                    });
                });
            </script>
        @endif
        @if (isset($invoicePaymentSettings['flutterwave_payment']) && $invoicePaymentSettings['flutterwave_payment'] == 'on')
            <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
            <script>
                $(document).on("click", "#flutterwavePaymentBtn", function() {
                    var amount = $('.amount').val().trim();
                    if (!amount || amount <= 0) {
                        alert('Please enter a valid amount');
                        return;
                    }

                    var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
                    var customer_email = '{{ \Auth::user()->email }}';
                    var customer_name = '{{ \Auth::user()->name }}';
                    var flutterwave_public_key = '{{ $invoicePaymentSettings['flutterwave_public_key'] }}';
                    var currency = '{{ $invoicePaymentSettings['CURRENCY'] }}';

                    var flutterwavePayment = getpaidSetup({
                        txref: tx_ref,
                        PBFPubKey: flutterwave_public_key,
                        amount: amount, // Ensure amount is passed
                        currency: currency,
                        customer_email: customer_email,
                        customer_name: customer_name,
                        meta: [{
                            metaname: "payment_id",
                            metavalue: "id"
                        }],
                        onclose: function() {},
                        callback: function(result) {
                            if (result.tx.chargeResponseCode == "00" || result.tx.chargeResponseCode == "0") {
                                var txRef = result.tx.txRef;
                                var redirectUrl =
                                    "{{ url('invoice/flutterwave') }}/{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}/" +
                                    txRef + "?amount=" + amount;
                                window.location.href = redirectUrl;
                            } else {
                                alert('Payment failed');
                            }
                            flutterwavePayment.close();
                        }
                    });
                });
            </script>
        @endif

        @if (isset($invoicePaymentSettings['paystack_payment']) && $invoicePaymentSettings['paystack_payment'] == 'on')
            <script src="{{ asset('assets/js/plugins/jquery.form.min.js') }}"></script>
            <script src="https://js.paystack.co/v1/inline.js"></script>
            <script>
                $(document).on("click", "#paystackPaymentBtn", function() {

                    $('#paystack-payment-form').ajaxForm(function(res) {
                        if (res.flag == 1) {
                            var paystack_callback = "{{ url('/invoice/paystack') }}";
                            var order_id = '{{ time() }}';
                            var coupon_id = res.coupon;
                            var handler = PaystackPop.setup({
                                key: '{{ $invoicePaymentSettings['paystack_public_key'] }}',
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
                                        .reference + '/' + '{{ encrypt($invoice->id) }}' +
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
        @if (isset($invoicePaymentSettings['razorpay_payment']) && $invoicePaymentSettings['razorpay_payment'] == 'on')
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
                $(document).on("click", "#razorpayPaymentBtn", function() {

                    $('#razorpay-payment-form').ajaxForm(function(res) {

                        if (res.flag == 1) {

                            var razorpay_callback = "{{ url('/invoice/razorpay') }}";

                            var options = {
                                key: '{{ $settings['razorpay_api_key'] }}', // ✅ correct key
                                amount: res.total_price * 100,
                                currency: res.currency,
                                name: "Invoice Payment",
                                description: "Invoice भुगतान",
                                handler: function(response) {

                                    window.location.href =
                                        razorpay_callback + '/' +
                                        response.razorpay_payment_id + '/' +
                                        '{{ encrypt($invoice->id) }}';
                                },
                                prefill: {
                                    email: res.email
                                },
                                theme: {
                                    color: "#3399cc"
                                }
                            };

                            var rzp = new Razorpay(options);
                            rzp.open();

                        } else {
                            show_toastr('Error', res.message, 'error');
                        }

                    }).submit();
                });
            </script>
        @endif
    @endif
@endpush
