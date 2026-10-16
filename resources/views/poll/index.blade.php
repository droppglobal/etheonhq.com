@extends('layouts.app')

@section('page-title')
    {{ __('Polls') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Polls') }}</li>
@endsection

@section('content')
    <div class="card table-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Polls') }}</h5>
            <a href="#" class="btn btn-secondary customModal" data-size="lg" data-url="{{ route('poll.create') }}" data-title="{{ __('New Poll') }}"><i class="ti ti-circle-plus align-text-bottom"></i> {{ __('New Poll') }}</a>
        </div>
        <div class="card-body pt-0">
            <table class="table table-hover">
                <thead><tr><th>{{ __('Question') }}</th><th>{{ __('Audience') }}</th><th>{{ __('Votes') }}</th><th>{{ __('Closes') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Action') }}</th></tr></thead>
                <tbody>
                    @forelse ($polls as $poll)
                        <tr>
                            <td><a href="{{ route('poll.show', $poll->id) }}" class="fw-semibold">{{ $poll->question }}</a></td>
                            <td>{{ optional($poll->properties)->name ?? __('All properties') }}</td>
                            <td>{{ $poll->votes()->count() }}</td>
                            <td>{{ $poll->closes_on ? dateFormat($poll->closes_on) : '-' }}</td>
                            <td><span class="badge bg-light-{{ $poll->isOpen() ? 'success' : 'secondary' }}">{{ $poll->isOpen() ? __('Open') : __('Closed') }}</span></td>
                            <td class="text-end">
                                <div class="cart-action">
                                    {{ Form::open(['route' => ['poll.close', $poll->id], 'method' => 'post', 'class' => 'd-inline']) }}
                                    <button class="btn btn-sm btn-light-secondary">{{ $poll->is_active ? __('Close') : __('Reopen') }}</button>
                                    {{ Form::close() }}
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['poll.destroy', $poll->id], 'class' => 'd-inline']) !!}
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                    {!! Form::close() !!}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">{{ __('No polls yet. Ask tenants for a quick decision, like "Which day for the society meeting?".') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
