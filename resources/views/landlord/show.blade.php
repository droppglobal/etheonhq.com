@extends('layouts.app')

@section('page-title')
    {{ __('Landlord') }}: {{ $landlord->name }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('landlord.index') }}">{{ __('Landlords') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $landlord->name }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ $landlord->name }}</h5></div>
                <div class="card-body">
                    <p class="mb-1"><i class="ti ti-mail me-1"></i> {{ $landlord->email }}</p>
                    <p class="mb-3"><i class="ti ti-phone me-1"></i> {{ $landlord->phone_number ?: '-' }}</p>
                    <h6>{{ __('Properties') }}</h6>
                    <ul class="mb-3">
                        @forelse ($properties as $property)
                            <li>{{ $property->name }}</li>
                        @empty
                            <li class="text-muted">{{ __('None assigned') }}</li>
                        @endforelse
                    </ul>
                    @if (!empty(optional($profile)->bank_details))
                        <h6>{{ __('Payout Bank Details') }}</h6>
                        <p class="small">{!! nl2br(e($profile->bank_details)) !!}</p>
                    @endif
                </div>
            </div>
            @can('edit landlord')
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">{{ __('Record Payout') }}</h5></div>
                    <div class="card-body">
                        {{ Form::open(['route' => ['landlord.payout.store', $landlord->id], 'method' => 'post']) }}
                        <div class="form-group">
                            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                            {{ Form::number('amount', $statement['balance'] > 0 ? $statement['balance'] : null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'required' => 'required']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('payout_date', __('Date'), ['class' => 'form-label']) }}
                            {{ Form::date('payout_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('method', __('Method'), ['class' => 'form-label']) }}
                            {{ Form::text('method', __('Bank transfer'), ['class' => 'form-control']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
                            {{ Form::text('reference', null, ['class' => 'form-control']) }}
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">{{ __('Record Payout') }}</button>
                        {{ Form::close() }}
                    </div>
                </div>
            @endcan
        </div>
        <div class="col-lg-9">
            @include('landlord._statement', ['monthRoute' => route('landlord.show', $landlord->id), 'canEditPayouts' => \Auth::user()->can('edit landlord')])
        </div>
    </div>
@endsection
