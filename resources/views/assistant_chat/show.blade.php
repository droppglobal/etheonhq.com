@extends('layouts.app')

@section('page-title')
    {{ optional($tenant->user)->name }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('assistant-chats.index') }}">{{ __('Tenant Chats') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ optional($tenant->user)->name }}</li>
@endsection

@push('css-page')
    <style>
        .chat-box { max-height: 60vh; overflow-y: auto; background: #f8f9fb; border-radius: 10px; padding: 16px; }
        .chat-msg { width: fit-content; max-width: 78%; padding: 10px 14px; border-radius: 14px; margin-bottom: 12px; }
        .chat-text { white-space: pre-line; }
        .chat-msg.user { background: #fff; border: 1px solid #e6e8ee; border-bottom-left-radius: 4px; }
        .chat-msg.assistant { background: #eef6f4; margin-left: auto; border-bottom-right-radius: 4px; }
        .chat-msg.staff { background: var(--bs-secondary, #2ca58d); color: #fff; margin-left: auto; border-bottom-right-radius: 4px; }
        .chat-meta { display: block; font-size: 11px; opacity: .75; margin-bottom: 2px; }
        .chat-msg.waiting { border: 2px solid #dc3545; }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Conversation') }}</h5>
                    @if ($messages->where('needs_reply', 1)->count())
                        {{ Form::open(['route' => ['assistant-chats.resolve', $tenant->id], 'method' => 'post']) }}
                        <button class="btn btn-sm btn-light-secondary"><i class="ti ti-check"></i> {{ __('Mark as handled') }}</button>
                        {{ Form::close() }}
                    @endif
                </div>
                <div class="card-body">
                    <div class="chat-box" id="chat-box">
                        @forelse ($messages as $message)
                            <div class="chat-msg {{ $message->role }} {{ $message->needs_reply ? 'waiting' : '' }}">
                                <span class="chat-meta">
                                    @if ($message->role == 'user'){{ optional($tenant->user)->first_name ?: __('Tenant') }}@elseif ($message->role == 'staff'){{ optional($message->staff)->name ?: __('Office') }}@else{{ __('Assistant') }}@endif
                                    · {{ $message->channel == 'whatsapp' ? 'WhatsApp' : __('App') }} · {{ dateFormat($message->created_at) }} {{ $message->created_at->format('H:i') }}
                                </span>
                                <div class="chat-text">{{ $message->content }}</div>
                            </div>
                        @empty
                            <p class="text-muted text-center mb-0">{{ __('No messages.') }}</p>
                        @endforelse
                    </div>
                    {{ Form::open(['route' => ['assistant-chats.reply', $tenant->id], 'method' => 'post', 'class' => 'mt-3']) }}
                    <textarea name="message" class="form-control" rows="2" maxlength="2000" required placeholder="{{ __('Write a reply to the tenant...') }}"></textarea>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" value="web" id="ch-web" {{ $lastChannel != 'whatsapp' || !$whatsapp ? 'checked' : '' }}>
                                <label class="form-check-label" for="ch-web">{{ __('In-app chat') }}</label>
                            </div>
                            @if ($whatsapp)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="channel" value="whatsapp" id="ch-wa" {{ $lastChannel == 'whatsapp' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ch-wa">WhatsApp</label>
                                </div>
                            @endif
                        </div>
                        <button class="btn btn-secondary"><i class="ti ti-send"></i> {{ __('Send reply') }}</button>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Tenant') }}</h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Name') }}</span><span class="fw-semibold">{{ optional($tenant->user)->name }}</span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Property') }}</span><span class="fw-semibold text-end">{{ optional($tenant->properties)->name }} / {{ optional($tenant->units)->name }}</span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Phone') }}</span><span>{{ optional($tenant->user)->phone_number ?: '-' }}</span></div>
                    <div class="d-flex justify-content-between py-2"><span class="text-muted">{{ __('Email') }}</span><span class="text-truncate ms-2">{{ optional($tenant->user)->email }}</span></div>
                    @can('show tenant')
                        <a href="{{ route('tenant.show', \Crypt::encrypt($tenant->id)) }}" class="btn btn-light-secondary btn-sm w-100 mt-3">{{ __('Open tenant profile') }}</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        var box = document.getElementById('chat-box');
        if (box) box.scrollTop = box.scrollHeight;
    </script>
@endpush
