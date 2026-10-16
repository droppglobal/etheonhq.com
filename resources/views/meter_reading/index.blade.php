@extends('layouts.app')

@section('page-title')
    {{ __('Utility Billing') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Utility Billing') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <form method="get" class="d-flex gap-2">
                                {!! Form::select('property', $properties, request('property'), ['class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
                                <select name="billed" class="form-control" onchange="this.form.submit()">
                                    <option value="">{{ __('All readings') }}</option>
                                    <option value="0" {{ request('billed') === '0' ? 'selected' : '' }}>{{ __('Not billed') }}</option>
                                </select>
                            </form>
                        </div>
                        @can('create meter reading')
                            <div class="col-md-auto ms-md-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="md" data-url="{{ route('meter-reading.create') }}"
                                    data-title="{{ __('Add Meter Reading') }}"><i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Add Reading') }}</a>
                            </div>
                        @endcan
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Property / Unit') }}</th>
                                    <th>{{ __('Utility') }}</th>
                                    <th>{{ __('Previous') }}</th>
                                    <th>{{ __('Current') }}</th>
                                    <th>{{ __('Used') }}</th>
                                    <th>{{ __('Rate') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Invoice') }}</th>
                                    <th class="text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($readings as $reading)
                                    <tr>
                                        <td>{{ dateFormat($reading->reading_date) }}</td>
                                        <td>{{ optional($reading->properties)->name }} / {{ optional($reading->units)->name }}</td>
                                        <td>{{ \App\Models\MeterReading::utilityTypes()[$reading->utility_type] ?? $reading->utility_type }}</td>
                                        <td>{{ (float) $reading->previous_reading }}</td>
                                        <td>{{ (float) $reading->current_reading }}</td>
                                        <td>{{ (float) $reading->consumption }}</td>
                                        <td>{{ (float) $reading->rate }}</td>
                                        <td>{{ priceFormat($reading->amount) }}</td>
                                        <td>
                                            @if (!empty($reading->invoice))
                                                <a href="{{ route('invoice.show', \Crypt::encrypt($reading->invoice_id)) }}">{{ invoicePrefix() . $reading->invoice->invoice_id }}</a>
                                            @else
                                                <span class="badge bg-light-warning">{{ __('Not billed') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if (empty($reading->invoice_id))
                                                <div class="cart-action">
                                                    @can('create invoice')
                                                        {!! Form::open(['method' => 'POST', 'route' => ['meter-reading.bill', $reading->id], 'class' => 'd-inline']) !!}
                                                        <button type="submit" class="btn btn-sm btn-light-secondary" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Adds the charge to the unit\'s open invoice, or creates one') }}">
                                                            <i class="ti ti-receipt"></i> {{ __('Bill') }}</button>
                                                        {!! Form::close() !!}
                                                    @endcan
                                                    @can('delete meter reading')
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['meter-reading.destroy', $reading->id], 'class' => 'd-inline']) !!}
                                                        <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                                        {!! Form::close() !!}
                                                    @endcan
                                                </div>
                                            @endif
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
@endsection
