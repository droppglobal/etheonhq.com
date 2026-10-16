@extends('layouts.app')

@section('page-title')
    {{ __('Polls') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Polls') }}</li>
@endsection

@section('content')
    <div class="row">
        @forelse ($polls as $poll)
            @php $mine = $myVotes[$poll->id] ?? null; @endphp
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">{{ $poll->question }}</h5>
                        <small class="text-muted">{{ $poll->isOpen() ? ($poll->closes_on ? __('Closes') . ' ' . dateFormat($poll->closes_on) : __('Open')) : __('Closed') }}</small></div>
                    <div class="card-body">
                        @if ($poll->description)<p class="small">{!! nl2br(e($poll->description)) !!}</p>@endif
                        @if (!$mine && $poll->isOpen())
                            {{ Form::open(['route' => ['poll.vote', $poll->id], 'method' => 'post']) }}
                            @foreach ($poll->options as $option)
                                <label class="d-block border rounded p-2 mb-2" style="text-transform:none"><input type="radio" name="option_id" value="{{ $option->id }}" required> {{ $option->label }}</label>
                            @endforeach
                            <button type="submit" class="btn btn-secondary">{{ __('Vote') }}</button>
                            {{ Form::close() }}
                        @elseif ($poll->show_results)
                            @include('poll._results', ['results' => $poll->results(), 'mine' => $mine])
                        @else
                            <p class="text-muted mb-0">{{ $mine ? __('Thanks, your vote is recorded.') : __('This poll is closed.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card"><div class="card-body text-muted">{{ __('No polls right now.') }}</div></div></div>
        @endforelse
    </div>
@endsection
