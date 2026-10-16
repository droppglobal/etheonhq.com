@extends('layouts.app')

@section('page-title')
    {{ __('Rent Roll') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Rent Roll') }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-3">{{ Form::label('property', __('Property'), ['class' => 'form-label']) }}{!! Form::select('property', $properties, request('property'), ['class' => 'form-control']) !!}</div>
                <div class="col-md-9 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="ti ti-search"></i> {{ __('Show') }}</button>
                    <button type="submit" name="export" value="csv" class="btn btn-light-secondary"><i class="ti ti-download"></i> CSV</button>
                    <button type="submit" name="export" value="pdf" class="btn btn-light-secondary"><i class="ti ti-file-download"></i> PDF</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md col-6"><div class="border rounded p-3 h-100"><div class="text-muted small">{{ __('Occupancy') }}</div><h5 class="mb-0">{{ $summary['occupancy'] }}%</h5><small class="text-muted">{{ $summary['occupied'] }} / {{ $summary['units'] }} {{ __('units') }}</small></div></div>
                <div class="col-md col-6"><div class="border rounded p-3 h-100"><div class="text-muted small">{{ __('Monthly rent (occupied)') }}</div><h5 class="mb-0">{{ priceFormat($summary['monthly_rent']) }}</h5></div></div>
                <div class="col-md col-6"><div class="border rounded p-3 h-100"><div class="text-muted small">{{ __('Monthly rent lost to vacancy') }}</div><h5 class="mb-0 {{ $summary['vacant_rent'] > 0 ? 'text-danger' : '' }}">{{ priceFormat($summary['vacant_rent']) }}</h5></div></div>
                <div class="col-md col-6"><div class="border rounded p-3 h-100"><div class="text-muted small">{{ __('Deposits held') }}</div><h5 class="mb-0">{{ priceFormat($summary['deposits']) }}</h5></div></div>
                <div class="col-md col-12"><div class="border rounded p-3 h-100 bg-light-secondary"><div class="text-muted small">{{ __('Balance owed') }}</div><h5 class="mb-0">{{ priceFormat($summary['balance']) }}</h5></div></div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>{{ __('Property & Unit') }}</th><th>{{ __('Tenant') }}</th><th>{{ __('Lease') }}</th><th class="text-end">{{ __('Rent') }}</th><th class="text-end">{{ __('Deposit held') }}</th><th class="text-end">{{ __('Balance owed') }}</th></tr></thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="{{ $row['status'] == 'vacant' ? 'table-light' : '' }}">
                                <td>{{ $row['property'] }}<div class="small text-muted">{{ $row['unit'] }}</div></td>
                                <td>
                                    @if ($row['tenant'])
                                        <span class="fw-semibold">{{ $row['tenant'] }}</span>
                                    @else
                                        <span class="badge bg-light-warning">{{ __('Vacant') }}</span>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($row['lease_start']){{ dateFormat($row['lease_start']) }} – {{ $row['lease_end'] ? dateFormat($row['lease_end']) : __('open') }}
                                        @if ($row['lease_end'] && $row['lease_end'] < date('Y-m-d'))<span class="badge bg-light-danger">{{ __('expired') }}</span>@endif
                                    @else – @endif
                                </td>
                                <td class="text-end">{{ priceFormat($row['rent']) }}<div class="small text-muted">{{ \App\Models\PropertyUnit::rentTypes()[$row['rent_type']] ?? '' }}</div>
                                    @if ($row['change'])<div class="small text-info">{{ $row['change'] }}</div>@endif</td>
                                <td class="text-end">{{ $row['deposit'] ? priceFormat($row['deposit']) : '–' }}</td>
                                <td class="text-end {{ $row['balance'] > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $row['balance'] > 0 ? priceFormat($row['balance']) : '–' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No units.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
