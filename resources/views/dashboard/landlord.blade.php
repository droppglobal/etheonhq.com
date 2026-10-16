@extends('layouts.app')

@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection

@section('content')
    @include('dashboard._section', ['title' => __('My Portfolio')])
    <div class="row">
        @include('dashboard._stat', ['icon' => 'chart-pie', 'color' => 'success', 'label' => __('Occupancy'), 'value' => $extra['occupancy'] . '%', 'sub' => $extra['occupied'] . ' / ' . $extra['units'] . ' ' . __('units occupied')])
        @include('dashboard._stat', ['icon' => 'report-money', 'color' => 'primary', 'label' => __('Rent Collected This Year'), 'value' => priceFormat($extra['collectedThisYear'])])
        @include('dashboard._stat', ['icon' => 'tool', 'color' => 'warning', 'label' => __('Open Repairs'), 'value' => $extra['openRepairCount']])
        @include('dashboard._stat', ['icon' => 'home', 'color' => 'danger', 'label' => __('Vacant Units'), 'value' => $extra['vacantUnits']->count(),
            'sub' => $extra['vacantUnits']->take(3)->pluck('name')->implode(', ')])
    </div>

    <div class="row">
        <div class="{{ $extra['openRepairs']->count() ? 'col-xl-7' : 'col-12' }} d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0">{{ __('Your Properties') }}</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        @forelse ($properties as $property)
                            @php
                                $units = \App\Models\PropertyUnit::where('property_id', $property->id)->get();
                                $occupied = $units->where('is_occupied', 1)->count();
                                $pct = $units->count() ? round($occupied / $units->count() * 100) : 0;
                            @endphp
                            <div class="{{ $extra['openRepairs']->count() ? 'col-md-6' : 'col-md-4' }}">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-semibold text-truncate">{{ $property->name }}</div>
                                    <div class="text-muted small text-truncate">{{ $property->address }}, {{ $property->city }}</div>
                                    <div class="d-flex justify-content-between small mt-2"><span>{{ $occupied }} / {{ $units->count() }} {{ __('units occupied') }}</span><span>{{ $pct }}%</span></div>
                                    <div class="progress mt-1" style="height:6px"><div class="progress-bar {{ $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $pct }}%"></div></div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted">{{ __('No properties are assigned to you yet.') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        @if ($extra['openRepairs']->count())
            <div class="col-xl-5 d-flex">
                <div class="card w-100">
                    <div class="card-header"><h5 class="mb-0">{{ __('Open Repairs') }}</h5></div>
                    <div class="card-body pt-1">
                        @foreach ($extra['openRepairs'] as $job)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div style="min-width:0"><div class="fw-semibold text-truncate">{{ optional($job->types)->title ?? __('Request') }}</div>
                                    <small class="text-muted">{{ optional($job->properties)->name }} / {{ optional($job->units)->name }} · {{ dateFormat($job->request_date) }}</small></div>
                                <span class="badge bg-light-{{ $job->status == 'pending' ? 'warning' : 'info' }} flex-shrink-0 ms-2">{{ \App\Models\MaintenanceRequest::status()[$job->status] ?? $job->status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('landlord._statement', ['monthRoute' => route('dashboard'), 'canEditPayouts' => false])
@endsection
