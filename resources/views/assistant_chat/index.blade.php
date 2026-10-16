@extends('layouts.app')

@section('page-title')
    {{ __('Tenant Chats') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Tenant Chats') }}</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0">{{ __('Tenant Chats') }}</h5>
                <small class="text-muted">{{ __('Conversations tenants had with the assistant in the app and on WhatsApp. Ones the assistant could not answer are marked "Needs reply".') }}</small>
            </div>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('assistant-chats.index') }}" class="btn {{ request('filter') != 'waiting' ? 'btn-secondary' : 'btn-light-secondary' }}">{{ __('All') }}</a>
                <a href="{{ route('assistant-chats.index', ['filter' => 'waiting']) }}" class="btn {{ request('filter') == 'waiting' ? 'btn-secondary' : 'btn-light-secondary' }}">{{ __('Needs reply') }}</a>
            </div>
        </div>
        <div class="card-body pt-1">
            @forelse ($threads as $thread)
                @php
                    $tenant = $tenants[$thread->tenant_id] ?? null;
                    $last = $lastMessages[$thread->tenant_id] ?? null;
                @endphp
                @continue(!$tenant)
                <a href="{{ route('assistant-chats.show', $tenant->id) }}" class="d-flex align-items-center gap-3 py-3 border-bottom text-reset">
                    <div class="avtar avtar-s {{ $thread->waiting ? 'bg-light-danger' : 'bg-light-secondary' }} flex-shrink-0">
                        <i class="ti ti-{{ optional($last)->channel == 'whatsapp' ? 'brand-whatsapp' : 'messages' }} f-18"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="fw-semibold text-truncate">{{ optional($tenant->user)->name ?: __('Tenant') }}
                                <small class="text-muted fw-normal">· {{ optional($tenant->properties)->name }} {{ optional($tenant->units)->name }}</small></span>
                            <small class="text-muted flex-shrink-0">{{ optional(optional($last)->created_at)->diffForHumans() }}</small>
                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <small class="text-muted text-truncate">
                                @if (optional($last)->role == 'staff'){{ __('You') }}: @elseif (optional($last)->role == 'assistant'){{ __('Assistant') }}: @endif{{ optional($last)->content }}
                            </small>
                            @if ($thread->waiting)
                                <span class="badge bg-danger flex-shrink-0">{{ __('Needs reply') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-center text-muted py-5 mb-0">
                    {{ request('filter') == 'waiting' ? __('Nothing is waiting for a reply.') : __('No conversations yet. Tenants can chat with the assistant from their Assistant page or on WhatsApp.') }}
                </p>
            @endforelse
            <div class="mt-3">{{ $threads->links() }}</div>
        </div>
    </div>
@endsection
