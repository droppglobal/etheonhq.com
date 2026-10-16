@extends('layouts.app')

@section('page-title')
    {{ __('Visitor Passes') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Visitor Passes') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Expecting a visitor?') }}</h5></div>
                <div class="card-body">
                    {{ Form::open(['route' => 'visitor.store', 'method' => 'post']) }}
                    <div class="form-group">{{ Form::label('visitor_name', __('Visitor name'), ['class' => 'form-label']) }}{{ Form::text('visitor_name', null, ['class' => 'form-control', 'required' => 'required']) }}</div>
                    <div class="form-group">{{ Form::label('visitor_phone', __('Phone'), ['class' => 'form-label']) }}{{ Form::text('visitor_phone', null, ['class' => 'form-control']) }}</div>
                    <div class="form-group">{{ Form::label('visit_date', __('Date'), ['class' => 'form-label']) }}{{ Form::date('visit_date', date('Y-m-d'), ['class' => 'form-control', 'min' => date('Y-m-d'), 'required' => 'required']) }}</div>
                    <div class="form-group">{{ Form::label('purpose', __('Purpose'), ['class' => 'form-label']) }}{{ Form::text('purpose', null, ['class' => 'form-control', 'placeholder' => __('Guest, delivery, repair...')]) }}</div>
                    <div class="form-group">{{ Form::label('vehicle_number', __('Vehicle number (optional)'), ['class' => 'form-label']) }}{{ Form::text('vehicle_number', null, ['class' => 'form-control']) }}</div>
                    <button type="submit" class="btn btn-secondary w-100">{{ __('Create Pass') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('My visitors') }}</h5></div>
                <div class="card-body pt-0">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Visitor') }}</th><th>{{ __('Date') }}</th><th>{{ __('Code') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($passes as $pass)
                                <tr>
                                    <td class="fw-semibold">{{ $pass->visitor_name }}<br><small class="text-muted">{{ $pass->purpose }}</small></td>
                                    <td>{{ dateFormat($pass->visit_date) }}</td>
                                    <td><span class="fs-5 fw-bold font-monospace">{{ $pass->code }}</span></td>
                                    <td><span class="badge bg-light-{{ \App\Models\VisitorPass::statusColor($pass->status) }}">{{ \App\Models\VisitorPass::statuses()[$pass->status] ?? $pass->status }}</span>
                                        @if ($pass->checked_in_at)<br><small class="text-muted">{{ __('in') }} {{ timeFormat($pass->checked_in_at) }}{{ $pass->checked_out_at ? ' · ' . __('out') . ' ' . timeFormat($pass->checked_out_at) : '' }}</small>@endif</td>
                                    <td class="text-end">
                                        @if ($pass->status == 'active')
                                            {{ Form::open(['route' => ['visitor.cancel', $pass->id], 'method' => 'post']) }}
                                            <button class="btn btn-sm btn-light-danger">{{ __('Cancel') }}</button>
                                            {{ Form::close() }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">{{ __('No visitor passes yet. Create one and share the code with your visitor to show at the gate.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
