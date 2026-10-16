@extends('layouts.app')

@section('page-title')
    {{ __('Parking') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Parking') }}</li>
@endsection

@section('content')
    @php $taken = $slots->filter(fn($s) => $s->currentAssignment)->count(); @endphp
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Add parking slots') }}</h5></div>
                <div class="card-body">
                    {{ Form::open(['route' => 'parking.store', 'method' => 'post']) }}
                    <div class="form-group">{{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}{{ Form::select('property_id', $properties, request('property'), ['class' => 'form-control', 'required' => 'required']) }}</div>
                    <div class="form-group">{{ Form::label('codes', __('Slot numbers'), ['class' => 'form-label']) }}{{ Form::text('codes', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => 'P1-P20, B1, B2']) }}
                        <small class="text-muted">{{ __('Separate with commas; use a range like P1-P20 to add many.') }}</small></div>
                    <div class="row">
                        <div class="form-group col-6">{{ Form::label('vehicle_type', __('Type'), ['class' => 'form-label']) }}{{ Form::select('vehicle_type', \App\Models\ParkingSlot::vehicleTypes(), 'car', ['class' => 'form-control']) }}</div>
                        <div class="form-group col-6">{{ Form::label('monthly_fee', __('Monthly fee'), ['class' => 'form-label']) }}{{ Form::number('monthly_fee', 0, ['class' => 'form-control', 'min' => 0, 'step' => '0.01']) }}</div>
                    </div>
                    <button type="submit" class="btn btn-secondary w-100">{{ __('Add Slots') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">{{ __('Slots') }}: {{ $taken }} / {{ $slots->count() }} {{ __('assigned') }}</h5>
                    <form method="get">{!! Form::select('property', ['' => __('All properties')] + $properties->toArray(), request('property'), ['class' => 'form-control form-control-sm', 'onchange' => 'this.form.submit()']) !!}</form>
                </div>
                <div class="card-body pt-0">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Slot') }}</th><th>{{ __('Property') }}</th><th>{{ __('Type') }}</th><th>{{ __('Fee') }}</th><th>{{ __('Assigned to') }}</th><th class="text-end">{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @forelse ($slots as $slot)
                                @php $a = $slot->currentAssignment; @endphp
                                <tr>
                                    <td class="fw-bold">{{ $slot->code }}</td>
                                    <td>{{ optional($slot->properties)->name }}</td>
                                    <td>{{ \App\Models\ParkingSlot::vehicleTypes()[$slot->vehicle_type] ?? $slot->vehicle_type }}</td>
                                    <td>{{ $slot->monthly_fee > 0 ? priceFormat($slot->monthly_fee) : '-' }}</td>
                                    <td>
                                        @if ($a)
                                            <span class="badge bg-light-success">{{ optional(optional($a->tenants)->user)->name }}</span>
                                            <small class="text-muted">{{ $a->vehicle_number }} {{ $a->vehicle_model }}</small>
                                        @else
                                            {{ Form::open(['route' => ['parking.assign', $slot->id], 'method' => 'post', 'class' => 'd-flex gap-1']) }}
                                            {{ Form::select('tenant_id', $tenants, null, ['class' => 'form-control form-control-sm', 'placeholder' => __('Tenant'), 'required' => 'required']) }}
                                            <input type="text" name="vehicle_number" class="form-control form-control-sm" placeholder="{{ __('Vehicle no.') }}" style="max-width:110px">
                                            <button class="btn btn-sm btn-light-secondary">{{ __('Assign') }}</button>
                                            {{ Form::close() }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($a)
                                            {{ Form::open(['route' => ['parking.release', $slot->id], 'method' => 'post', 'class' => 'd-inline']) }}
                                            <button class="btn btn-sm btn-light-warning">{{ __('Free') }}</button>
                                            {{ Form::close() }}
                                        @else
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['parking.destroy', $slot->id], 'class' => 'd-inline']) !!}
                                            <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                            {!! Form::close() !!}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">{{ __('No parking slots yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
