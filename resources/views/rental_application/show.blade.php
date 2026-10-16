@extends('layouts.app')

@section('page-title')
    {{ __('Rental Application') }}: {{ $application->full_name }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('rental-application.index') }}">{{ __('Rental Applications') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $application->full_name }}</li>
@endsection

@section('content')
    @php
        $rent = optional($application->units)->rent;
        $incomeRatio = $rent > 0 && $application->monthly_income > 0 ? round($application->monthly_income / $rent, 1) : null;
    @endphp
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Applicant Details') }}</h5>
                    <span class="badge bg-light-{{ $application->status == 'approved' ? 'success' : ($application->status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ \App\Models\RentalApplication::statuses()[$application->status] ?? $application->status }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ([
                            __('Name') => $application->full_name,
                            __('Email') => $application->email,
                            __('Phone') => $application->phone,
                            __('Current Address') => $application->current_address,
                            __('Employer') => $application->employer,
                            __('Monthly Income') => priceFormat($application->monthly_income),
                            __('Family Members') => $application->family_member,
                            __('Preferred Move-in') => $application->move_in_date ? dateFormat($application->move_in_date) : '-',
                            __('Property') => optional($application->properties)->name,
                            __('Unit') => optional($application->units)->name . ($rent ? ' (' . priceFormat($rent) . ' / ' . optional($application->units)->rent_type . ')' : ''),
                            __('Submitted') => dateFormat($application->created_at),
                        ] as $label => $value)
                            <div class="col-md-6 mb-3">
                                <div class="text-muted small">{{ $label }}</div>
                                <div class="fw-semibold">{{ $value ?: '-' }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if ($incomeRatio)
                        <div class="alert {{ $incomeRatio >= 3 ? 'alert-light-success' : 'alert-light-warning' }} mb-3">
                            {{ __('Income is') }} <strong>{{ $incomeRatio }}x</strong> {{ __('the rent') }}.
                            {{ $incomeRatio >= 3 ? __('Meets the common 3x guideline.') : __('Below the common 3x guideline.') }}
                        </div>
                    @endif
                    @if (!empty($application->message))
                        <h6>{{ __('Message') }}</h6>
                        <p>{!! nl2br(e($application->message)) !!}</p>
                    @endif
                    <h6>{{ __('Documents') }}</h6>
                    <div class="d-flex gap-2">
                        @if (!empty($application->id_document))
                            <a href="{{ fetch_file($application->id_document, 'upload/application/') }}" target="_blank" class="btn btn-light-secondary btn-sm">
                                <i class="ti ti-id"></i> {{ __('ID Proof') }}</a>
                        @endif
                        @if (!empty($application->income_document))
                            <a href="{{ fetch_file($application->income_document, 'upload/application/') }}" target="_blank" class="btn btn-light-secondary btn-sm">
                                <i class="ti ti-receipt"></i> {{ __('Income Proof') }}</a>
                        @endif
                    </div>
                    @if (!empty($application->lead_id))
                        <hr>
                        <a href="{{ route('lead.show', $application->lead_id) }}"><i class="ti ti-arrow-left"></i> {{ __('Open lead') }}</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Decision') }}</h5></div>
                <div class="card-body">
                    @if ($application->status != 'pending')
                        <p class="mb-1">{{ __('Reviewed on') }} {{ dateFormat($application->reviewed_at) }}</p>
                        @if (!empty($application->review_notes))
                            <p class="text-muted">{{ $application->review_notes }}</p>
                        @endif
                        @if (!empty($application->tenant_id))
                            <a href="{{ route('tenant.show', \Crypt::encrypt($application->tenant_id)) }}" class="btn btn-secondary btn-sm">{{ __('View tenant') }}</a>
                        @endif
                    @elseif (\Auth::user()->can('review rental application'))
                        @if (!$unitAvailable)
                            <div class="alert alert-light-danger">{{ __('This unit is now occupied. Reject the application or ask the applicant to apply for another unit.') }}</div>
                        @endif
                        @if ($emailTaken)
                            <div class="alert alert-light-danger">{{ __('A user with this email already exists, so a tenant login cannot be created.') }}</div>
                        @endif
                        {{ Form::open(['route' => ['rental-application.approve', $application->id], 'method' => 'post']) }}
                        <div class="form-group">
                            {{ Form::label('lease_start_date', __('Lease Start Date'), ['class' => 'form-label']) }}
                            {{ Form::date('lease_start_date', $application->move_in_date ?: date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('lease_end_date', __('Lease End Date'), ['class' => 'form-label']) }}
                            {{ Form::date('lease_end_date', \Carbon\Carbon::parse($application->move_in_date ?: date('Y-m-d'))->addYear()->subDay()->format('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('review_notes', __('Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('review_notes', null, ['class' => 'form-control', 'rows' => 2]) }}
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-2" {{ !$unitAvailable || $emailTaken ? 'disabled' : '' }}>
                            <i class="ti ti-check"></i> {{ __('Approve & Create Tenant') }}</button>
                        <small class="text-muted d-block mb-3">{{ __('Creates the tenant login, a draft agreement and marks the unit occupied. The tenant receives the welcome email.') }}</small>
                        {{ Form::close() }}

                        {{ Form::open(['route' => ['rental-application.reject', $application->id], 'method' => 'post']) }}
                        {{ Form::hidden('review_notes', null, ['id' => 'reject_notes']) }}
                        <button type="submit" class="btn btn-light-danger w-100"
                            onclick="document.getElementById('reject_notes').value = document.querySelector('textarea[name=review_notes]').value; return confirm('{{ __('Reject this application? The applicant will be emailed.') }}');">
                            <i class="ti ti-x"></i> {{ __('Reject') }}</button>
                        {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
