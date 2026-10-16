@extends('layouts.app')

@section('page-title')
    {{ __('Ledger') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Ledger') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-3">{{ Form::label('from', __('From'), ['class' => 'form-label']) }}<input type="date" name="from" value="{{ $from }}" class="form-control"></div>
                        <div class="col-md-3">{{ Form::label('to', __('To'), ['class' => 'form-label']) }}<input type="date" name="to" value="{{ $to }}" class="form-control"></div>
                        <div class="col-md-3">{{ Form::label('property', __('Property'), ['class' => 'form-label']) }}{!! Form::select('property', $properties, request('property'), ['class' => 'form-control']) !!}</div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-secondary"><i class="ti ti-search"></i> {{ __('Show') }}</button>
                            <button type="submit" name="export" value="csv" class="btn btn-light-secondary"><i class="ti ti-download"></i> CSV</button>
                            <button type="submit" name="export" value="pdf" class="btn btn-light-secondary"><i class="ti ti-file-download"></i> PDF</button>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">{{ __('Money In') }}</div><h5 class="mb-0 text-success">{{ priceFormat($entries->sum('in')) }}</h5></div></div>
                        <div class="col-md-4"><div class="border rounded p-3"><div class="text-muted small">{{ __('Money Out') }}</div><h5 class="mb-0 text-danger">{{ priceFormat($entries->sum('out')) }}</h5></div></div>
                        <div class="col-md-4"><div class="border rounded p-3 bg-light-secondary"><div class="text-muted small">{{ __('Net') }}</div><h5 class="mb-0">{{ priceFormat($entries->sum('in') - $entries->sum('out')) }}</h5></div></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>{{ __('Date') }}</th><th>{{ __('Description') }}</th><th>{{ __('Property') }}</th><th class="text-end">{{ __('Money In') }}</th><th class="text-end">{{ __('Money Out') }}</th><th class="text-end">{{ __('Balance') }}</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($entries as $entry)
                                    <tr>
                                        <td>{{ dateFormat($entry['date']) }}</td>
                                        <td>{{ $entry['description'] }}</td>
                                        <td>{{ $entry['property'] }}</td>
                                        <td class="text-end text-success">{{ $entry['in'] ? priceFormat($entry['in']) : '' }}</td>
                                        <td class="text-end text-danger">{{ $entry['out'] ? priceFormat($entry['out']) : '' }}</td>
                                        <td class="text-end fw-semibold">{{ priceFormat($entry['balance']) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">{{ __('No transactions in this period.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
