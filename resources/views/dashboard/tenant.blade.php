@extends('layouts.app')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
    </ul>
@endsection

@section('content')
    @if (!empty($extra))
        @include('dashboard._tenant_extra')
    @else
        <div class="card">
            <div class="card-body text-center text-muted py-5">{{ __('Your tenancy details have not been set up yet. Please contact your property manager.') }}</div>
        </div>
    @endif
@endsection
