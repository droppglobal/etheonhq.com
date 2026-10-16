@extends('layouts.app')

@section('page-title')
    {{ __('Landlord Payouts') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Landlord Payouts') }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-2">{{ Form::label('from', __('From'), ['class' => 'form-label']) }}<input type="date" name="from" value="{{ $from }}" class="form-control"></div>
                <div class="col-md-2">{{ Form::label('to', __('To'), ['class' => 'form-label']) }}<input type="date" name="to" value="{{ $to }}" class="form-control"></div>
                <div class="col-md-3">{{ Form::label('landlord', __('Landlord'), ['class' => 'form-label']) }}{!! Form::select('landlord', $landlords->prepend(__('All landlords'), ''), request('landlord'), ['class' => 'form-control']) !!}</div>
                <div class="col-md-5 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="ti ti-search"></i> {{ __('Show') }}</button>
                    <button type="submit" name="export" value="csv" class="btn btn-light-secondary"><i class="ti ti-download"></i> CSV</button>
                    <button type="submit" name="export" value="pdf" class="btn btn-light-secondary"><i class="ti ti-file-download"></i> PDF</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-xl-4">
                    <div class="border rounded p-3 mb-3 bg-light-secondary"><div class="text-muted small">{{ __('Paid out in this period') }}</div><h4 class="mb-0">{{ priceFormat($payouts->sum('amount')) }}</h4><small class="text-muted">{{ $payouts->count() }} {{ __('payouts') }}</small></div>
                    @foreach ($byLandlord as $row)
                        <div class="d-flex justify-content-between py-2 border-bottom"><span>{{ $row['name'] }} <small class="text-muted">· {{ $row['count'] }}</small></span><span class="fw-semibold">{{ priceFormat($row['total']) }}</span></div>
                    @endforeach
                </div>
                <div class="col-xl-8">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Landlord') }}</th><th class="text-end">{{ __('Amount') }}</th><th>{{ __('Method') }}</th><th>{{ __('Reference') }}</th></tr></thead>
                            <tbody>
                                @forelse ($payouts as $payout)
                                    <tr>
                                        <td>{{ dateFormat($payout->payout_date) }}</td>
                                        <td><a href="{{ route('landlord.show', $payout->landlord_id) }}">{{ $landlords[$payout->landlord_id] ?? '-' }}</a></td>
                                        <td class="text-end fw-semibold">{{ priceFormat($payout->amount) }}</td>
                                        <td>{{ $payout->method }}</td>
                                        <td>{{ $payout->reference }}@if ($payout->notes)<div class="small text-muted">{{ $payout->notes }}</div>@endif</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No payouts in this period. Payouts are recorded on each landlord\'s page.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
