@extends('layouts.app')
@section('page-title')
    {{ __('Unit Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('unit.index') }}"> {{ __('Unit') }}</a></li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection

@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp
@section('content')
    @if (!empty($rentSuggestion))
        @php
            $position = $rentSuggestion['current'] < $rentSuggestion['low'] ? 'below' : ($rentSuggestion['current'] > $rentSuggestion['high'] ? 'above' : 'within');
        @endphp
        <div class="alert alert-light-{{ $position == 'within' ? 'success' : 'warning' }} d-flex align-items-start gap-2">
            <i class="ti ti-bulb f-20"></i>
            <div>
                <strong>{{ __('Rent check') }}:</strong>
                {{ __('similar :bed-bedroom units :scope rent for', ['bed' => $unit->bedroom, 'scope' => $rentSuggestion['scope']]) }}
                <strong>{{ priceFormat($rentSuggestion['low']) }} - {{ priceFormat($rentSuggestion['high']) }}</strong>
                ({{ __('median') }} {{ priceFormat($rentSuggestion['median']) }}, {{ $rentSuggestion['count'] }} {{ __('units') }}, {{ $rentSuggestion['occupancy'] }}% {{ __('occupied') }}).
                {{ __('This unit is') }} {{ priceFormat($rentSuggestion['current']) }},
                {{ $position == 'within' ? __('within the usual range.') : ($position == 'below' ? __('below the usual range - you may be able to charge more.') : __('above the usual range - this may slow down letting it.')) }}
            </div>
        </div>
    @endif
    <div class="row">

        <div class="col-12">
            <div id="basicwizard" class="form-wizard row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-3">
                            <ul class="nav nav-pills nav-justified">
                                <li class="nav-item" data-target-form="#unitDetailForm">
                                    <a href="#unitDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link active">
                                        <i class="ph-duotone ph-user-circle"></i>
                                        <span class="d-none d-sm-inline">{{ __('Unit Detail') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item" data-target-form="#invoiceDetailForm">
                                    <a href="#invoiceDetail" data-bs-toggle="tab" data-toggle="tab"
                                        class="nav-link icon-btn">
                                        <i class="ph-duotone ph-circles-three-plus"></i>
                                        <span class="d-none d-sm-inline">{{ __('Invoice List') }}</span>
                                    </a>
                                </li>

                                <li class="nav-item" data-target-form="#expenseDetailForm">
                                    <a href="#expenseDetail" data-bs-toggle="tab" data-toggle="tab"
                                        class="nav-link icon-btn">
                                        <i class="ph-duotone ph-circles-three-plus"></i>
                                        <span class="d-none d-sm-inline">{{ __('Expense List') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content">
                                <div id="bar" class="progress mb-3" style="height: 7px">
                                    <div class="bar progress-bar progress-bar-striped progress-bar-animated bg-secondary">
                                    </div>
                                </div>

                                <div class="tab-pane show active" id="unitDetail">
                                    <div id='unitDetailForm'>
                                        <div class="row">
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Unit Name') }}</b>
                                                    <p class="mb-20 text-muted">{{ $unit->name }} </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Status') }}</b>
                                                    <p class="mb-20 text-muted">
                                                        @if ($unit->is_occupied)
                                                            <span class=" ms-1 text-danger">{{ __('Occupied') }}</span>
                                                        @else
                                                            <span class="text-success ms-1">{{ __('Vacant') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Bedroom') }}</b>
                                                    <p class="mb-20 text-muted">{{ $unit->bedroom }} </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Kitchen') }}</b>
                                                    <p class="mb-20 text-muted">{{ $unit->kitchen }} </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Bath') }}</b>
                                                    <p class="mb-20 text-muted">{{ $unit->baths }} </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Rent Type') }}</b>
                                                    <p class="mb-20 text-muted">
                                                        {{ \App\Models\PropertyUnit::rentTypes()[$unit->rent_type] }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Rent') }}</b>
                                                    <p class="mb-20 text-muted">{{ priceFormat($unit->rent) }}</p>
                                                </div>
                                            </div>
                                            @if ($unit->rent_type == 'custom')
                                                <div class="col-md-3 col-lg-3">
                                                    <div class="detail-group">
                                                        <b>{{ __('Start Date') }}</b>
                                                        <p class="mb-20 text-muted">{{ dateformat($unit->start_date) }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-lg-3">
                                                    <div class="detail-group">
                                                        <b>{{ __('End Date') }}</b>
                                                        <p class="mb-20 text-muted">{{ dateformat($unit->end_date) }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="col-md-3 col-lg-3">
                                                    <div class="detail-group">
                                                        <b>{{ __('Rent Duration') }}</b>
                                                        <p class="mb-20 text-muted">{{ $unit->rent_duration }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Deposit Type') }}</b>
                                                    <p class="mb-20 text-muted">
                                                        {{ \App\Models\PropertyUnit::type()[$unit->deposit_type] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Deposit Amount') }}</b>
                                                    <p class="mb-20 text-muted">
                                                        {{ $unit->deposit_type == 'fixed' ? priceFormat($unit->deposit_amount) : $unit->deposit_amount . '%' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Late Fee Type') }}</b>
                                                    <p class="mb-20 text-muted">
                                                        {{ App\Models\PropertyUnit::type()[$unit->late_fee_type] }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Late Fee Amount') }}</b>
                                                    <p class="mb-20 text-muted">
                                                        {{ $unit->late_fee_type == 'fixed' ? priceFormat($unit->late_fee_amount) : $unit->late_fee_amount . '%' }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3">
                                                <div class="detail-group">
                                                    <b>{{ __('Incident Receipt Amount') }}</b>
                                                    <p class="mb-20 text-muted">
                                                        {{ priceFormat($unit->incident_receipt_amount) }}</p>
                                                </div>
                                            </div>

                                            <div class="col-md-6 col-lg-6">
                                                <div class="detail-group">
                                                    <b>{{ __('Notes') }}</b>
                                                    <p class="mb-20 text-muted">{{ $unit->notes }} </p>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <div class="tab-pane" id="invoiceDetail">
                                    <div id='invoiceDetailForm'>
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Invoice') }}</th>
                                                        <th>{{ __('Property') }}</th>
                                                        <th>{{ __('Unit') }}</th>
                                                        <th>{{ __('Invoice Month') }}</th>
                                                        <th>{{ __('End Date') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                                            <th class="text-right">{{ __('Action') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($invoices as $invoice)
                                                        <tr>
                                                            <td>{{ invoicePrefix() . $invoice->invoice_id }} </td>
                                                            <td>{{ !empty($invoice->properties) ? $invoice->properties->name : '-' }}
                                                            </td>
                                                            <td>{{ !empty($invoice->units) ? $invoice->units->name : '-' }}
                                                            </td>
                                                            <td>{{ date('F Y', strtotime($invoice->invoice_month)) }}
                                                            </td>
                                                            <td>{{ dateFormat($invoice->end_date) }} </td>
                                                            <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}
                                                            </td>
                                                            <td>
                                                                @if ($invoice->status == 'open')
                                                                    <span
                                                                        class="badge bg-light-info">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                                @elseif($invoice->status == 'paid')
                                                                    <span
                                                                        class="badge bg-light-success">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                                @elseif($invoice->status == 'partial_paid')
                                                                    <span
                                                                        class="badge bg-light-warning">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                                                @endif
                                                            </td>
                                                            @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                                                <td>
                                                                    <div class="cart-action">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}

                                                                        @can('show invoice')
                                                                            <a class="avtar avtar-xs btn-link-warning text-warning"
                                                                                href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('View') }}">
                                                                                <i data-feather="eye"></i></a>
                                                                        @endcan

                                                                        {!! Form::close() !!}
                                                                    </div>

                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="expenseDetail">
                                    <div id='expenseDetailForm'>
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Expense') }}</th>
                                                        <th>{{ __('Title') }}</th>
                                                        <th>{{ __('Property') }}</th>
                                                        <th>{{ __('Unit') }}</th>
                                                        <th>{{ __('Type') }}</th>
                                                        <th>{{ __('Date') }}</th>
                                                        <th>{{ __('Amount') }}</th>
                                                        <th>{{ __('Receipt') }}</th>
                                                        @if (Gate::check('edit expense') || Gate::check('delete expense') || Gate::check('show expense'))
                                                            <th class="text-right">{{ __('Action') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($expenses as $expense)
                                                        <tr role="row">
                                                            <td>{{ expensePrefix() . $expense->expense_id }} </td>
                                                            <td> {{ $expense->title }} </td>
                                                            <td> {{ !empty($expense->properties) ? $expense->properties->name : '-' }}
                                                            </td>
                                                            <td> {{ !empty($expense->units) ? $expense->units->name : '-' }}
                                                            </td>
                                                            <td> {{ !empty($expense->types) ? $expense->types->title : '-' }}
                                                            </td>
                                                            <td> {{ dateFormat($expense->date) }} </td>
                                                            <td> {{ priceFormat($expense->amount) }} </td>
                                                            <td>
                                                                @if (!empty($expense->receipt))
                                                                    <a href="{{ !empty($expense->receipt) ? fetch_file($expense->receipt, 'upload/receipt/') : '#' }}"
                                                                        download="download"><i
                                                                            data-feather="download"></i></a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            @if (Gate::check('edit expense') || Gate::check('delete expense') || Gate::check('show expense'))
                                                                <td class="text-right">
                                                                    <div class="cart-action">
                                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['expense.destroy', $expense->id]]) !!}
                                                                        @can('show expense')
                                                                            <a class="avtar avtar-xs btn-link-warning text-warning customModal"
                                                                                data-size="lg" data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('View') }}"
                                                                                href="#"
                                                                                data-url="{{ route('expense.show', $expense->id) }}"
                                                                                data-title="{{ __('Expense Details') }}"> <i
                                                                                    data-feather="eye"></i></a>
                                                                        @endcan
                                                                        @can('edit expense')
                                                                            <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                                                data-size="lg" data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('Edit') }}"
                                                                                href="#"
                                                                                data-url="{{ route('expense.edit', $expense->id) }}"
                                                                                data-title="{{ __('Edit Expense') }}"> <i
                                                                                    data-feather="edit"></i></a>
                                                                        @endcan
                                                                        @can('delete expense')
                                                                            <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('Detete') }}"
                                                                                href="#"> <i
                                                                                    data-feather="trash-2"></i></a>
                                                                        @endcan
                                                                        {!! Form::close() !!}
                                                                    </div>

                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex wizard justify-content-between flex-wrap gap-2 mt-3">
                                    <div class="first">
                                        <a href="#" class="btn btn-secondary">{{ __('First') }}</a>
                                    </div>
                                    <div class="d-flex">
                                        <div class="previous me-2">
                                            <a href="#" class="btn btn-secondary">{{ __('Back To Previous') }}</a>
                                        </div>
                                        <div class="next">
                                            <a href="#" class="btn btn-secondary">{{ __('Next Step') }}</a>
                                        </div>
                                    </div>
                                    <div class="last">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-page')
    <style>
        .form-wizard .nav-pills .nav-link.active,
        .form-wizard .nav-pills .show>.nav-link {
            background: var(--bs-secondary) !important;
        }
    </style>
@endpush

@push('script-page')
    <script src="{{ asset('assets/js/plugins/wizard.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            new Wizard('#basicwizard', {
                validate: true,
                progress: true
            });
        });
    </script>
@endpush
