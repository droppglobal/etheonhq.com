@extends('layouts.app')

@section('page-title')
    {{ __('My Parking') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('My Parking') }}</li>
@endsection

@section('content')
    <div class="row">
        @forelse ($assignments as $a)
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="text-muted small">{{ optional(optional($a->slot)->properties)->name }}</div>
                        <div class="display-6 fw-bold my-2">{{ optional($a->slot)->code }}</div>
                        <div>{{ \App\Models\ParkingSlot::vehicleTypes()[optional($a->slot)->vehicle_type] ?? '' }}</div>
                        @if ($a->vehicle_number)<div class="badge bg-light-secondary mt-2 fs-6">{{ $a->vehicle_number }}</div>@endif
                        <div class="text-muted small mt-2">{{ __('Since') }} {{ dateFormat($a->start_date) }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card"><div class="card-body text-muted">{{ __('You do not have a parking slot. Contact the office to request one.') }}</div></div></div>
        @endforelse
    </div>
@endsection
