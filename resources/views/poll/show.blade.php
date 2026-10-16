@extends('layouts.app')

@section('page-title')
    {{ __('Poll') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('poll.index') }}">{{ __('Polls') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Results') }}</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ $poll->question }}</h5>
                    <small class="text-muted">{{ array_sum($results->toArray()) }} {{ __('votes') }} &middot; {{ $poll->isOpen() ? __('Open') : __('Closed') }}</small></div>
                <div class="card-body">
                    @if ($poll->description)<p>{!! nl2br(e($poll->description)) !!}</p>@endif
                    @include('poll._results')
                </div>
            </div>
        </div>
    </div>
@endsection
