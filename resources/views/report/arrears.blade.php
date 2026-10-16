@extends('layouts.app')

@section('page-title')
    {{ __('Arrears Ageing') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Arrears Ageing') }}</li>
@endsection

@section('content')
    @php $labels = \App\Http\Controllers\FinancialReportController::bucketLabels(); @endphp
    <div class="card">
        <div class="card-header">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-3">{{ Form::label('as_of', __('As of'), ['class' => 'form-label']) }}<input type="date" name="as_of" value="{{ $asOf->toDateString() }}" class="form-control"></div>
                <div class="col-md-3">{{ Form::label('property', __('Property'), ['class' => 'form-label']) }}{!! Form::select('property', $properties, request('property'), ['class' => 'form-control']) !!}</div>
                <div class="col-md-6 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="ti ti-search"></i> {{ __('Show') }}</button>
                    <button type="submit" name="export" value="csv" class="btn btn-light-secondary"><i class="ti ti-download"></i> CSV</button>
                    <button type="submit" name="export" value="pdf" class="btn btn-light-secondary"><i class="ti ti-file-download"></i> PDF</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                @foreach ($labels as $key => $label)
                    <div class="col-md col-6">
                        <div class="border rounded p-3 h-100 {{ $key == '90_plus' && $totals[$key] > 0 ? 'bg-light-danger' : '' }}">
                            <div class="text-muted small">{{ $label }}</div>
                            <h5 class="mb-0">{{ priceFormat(round($totals[$key], 2)) }}</h5>
                        </div>
                    </div>
                @endforeach
                <div class="col-md col-12">
                    <div class="border rounded p-3 h-100 bg-light-secondary"><div class="text-muted small">{{ __('Total owed') }}</div><h5 class="mb-0">{{ priceFormat(round($totals['total'], 2)) }}</h5></div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('Tenant') }}</th><th>{{ __('Property & Unit') }}</th>
                            @foreach ($labels as $label)<th class="text-end">{{ $label }}</th>@endforeach
                            <th class="text-end">{{ __('Total owed') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row['tenant'] }}</div>
                                    <small class="text-muted">{{ $row['invoices'] }} {{ $row['invoices'] == 1 ? __('invoice') : __('invoices') }}@if ($row['oldest'] > 0) · {{ __('oldest :d days late', ['d' => $row['oldest']]) }}@endif</small>
                                </td>
                                <td>{{ $row['property'] }}<div class="small text-muted">{{ $row['unit'] }}</div></td>
                                @foreach (array_keys($labels) as $bucket)
                                    <td class="text-end {{ $row[$bucket] > 0 && $bucket != 'current' ? 'text-danger' : 'text-muted' }}">{{ $row[$bucket] > 0 ? priceFormat(round($row[$bucket], 2)) : '–' }}</td>
                                @endforeach
                                <td class="text-end fw-semibold">{{ priceFormat(round($row['total'], 2)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('Nobody owes anything. 🎉') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
