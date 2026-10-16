@extends('layouts.app')

@section('page-title')
    {{ __('Gate / Visitors') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Gate / Visitors') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Check in with code') }}</h5></div>
                <div class="card-body">
                    {{ Form::open(['route' => 'visitor.checkin', 'method' => 'post']) }}
                    <input type="text" name="code" class="form-control form-control-lg text-center font-monospace text-uppercase mb-2" maxlength="12" placeholder="ABC123" autofocus required>
                    <button type="submit" class="btn btn-secondary w-100">{{ __('Check In') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Walk-in visitor') }}</h5></div>
                <div class="card-body">
                    {{ Form::open(['route' => 'visitor.walkin', 'method' => 'post']) }}
                    <div class="form-group">{{ Form::label('tenant_id', __('Visiting'), ['class' => 'form-label']) }}{{ Form::select('tenant_id', $tenants, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select unit / tenant')]) }}</div>
                    <div class="form-group">{{ Form::label('visitor_name', __('Visitor name'), ['class' => 'form-label']) }}{{ Form::text('visitor_name', null, ['class' => 'form-control', 'required' => 'required']) }}</div>
                    <div class="form-group">{{ Form::label('visitor_phone', __('Phone'), ['class' => 'form-label']) }}{{ Form::text('visitor_phone', null, ['class' => 'form-control']) }}</div>
                    <div class="form-group">{{ Form::label('purpose', __('Purpose'), ['class' => 'form-label']) }}{{ Form::text('purpose', null, ['class' => 'form-control']) }}</div>
                    <button type="submit" class="btn btn-light-secondary w-100">{{ __('Check In Walk-in') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Inside now') }} ({{ $inside->count() }})</h5></div>
                <div class="card-body pt-0">
                    <table class="table mb-0">
                        <tbody>
                            @forelse ($inside as $pass)
                                <tr>
                                    <td class="fw-semibold">{{ $pass->visitor_name }} <small class="text-muted">{{ $pass->visitor_phone }}</small></td>
                                    <td>{{ optional($pass->properties)->name }} / {{ optional($pass->units)->name }}</td>
                                    <td>{{ __('since') }} {{ timeFormat($pass->checked_in_at) }}</td>
                                    <td class="text-end">
                                        {{ Form::open(['route' => ['visitor.checkout', $pass->id], 'method' => 'post']) }}
                                        <button class="btn btn-sm btn-light-secondary">{{ __('Check Out') }}</button>
                                        {{ Form::close() }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="text-muted">{{ __('Nobody is checked in.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Visitor log') }}</h5>
                    <form method="get"><input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" onchange="this.form.submit()"></form>
                </div>
                <div class="card-body pt-0">
                    <table class="table mb-0">
                        <thead><tr><th>{{ __('Visitor') }}</th><th>{{ __('Visiting') }}</th><th>{{ __('Code') }}</th><th>{{ __('In') }}</th><th>{{ __('Out') }}</th><th>{{ __('Status') }}</th></tr></thead>
                        <tbody>
                            @forelse ($passes as $pass)
                                <tr>
                                    <td>{{ $pass->visitor_name }}<br><small class="text-muted">{{ $pass->purpose }} {{ $pass->vehicle_number }}</small></td>
                                    <td>{{ optional($pass->units)->name }} - {{ optional(optional($pass->tenants)->user)->name }}</td>
                                    <td class="font-monospace">{{ $pass->code }}</td>
                                    <td>{{ $pass->checked_in_at ? timeFormat($pass->checked_in_at) : '-' }}</td>
                                    <td>{{ $pass->checked_out_at ? timeFormat($pass->checked_out_at) : '-' }}</td>
                                    <td><span class="badge bg-light-{{ \App\Models\VisitorPass::statusColor($pass->status) }}">{{ \App\Models\VisitorPass::statuses()[$pass->status] ?? $pass->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted">{{ __('No visitors on this day.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
