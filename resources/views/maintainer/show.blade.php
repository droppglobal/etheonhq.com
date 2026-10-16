@extends('layouts.app')
@section('page-title')
    {{ __('Maintainer Details') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item" aria-current="page">
        <a href="{{ route('maintainer.index') }}"> {{ __('Maintainer') }}</a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection

@php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div id="basicwizard" class="form-wizard row justify-content-center">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-3">
                             <ul class="nav nav-pills nav-justified">
                                <li class="nav-item">
                                    <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                        role="tab">
                                        <i class="ph-duotone ph-user"></i>
                                        {{ __('Personal Information') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2"
                                        role="tab">
                                        <i class="ph-duotone ph-cpu"></i>
                                        {{ __('Maintenance Requests') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card">

                        <div class="card-body">
                            <div class="tab-content">

                                {{-- TAB 1: Personal Information --}}
                                <div class="tab-pane show active" id="profile-1" role="tabpanel"
                                    aria-labelledby="profile-tab-1">
                                    <div class="row">

                                        {{-- Profile Card --}}
                                        <div class="col-lg-4 col-xxl-3">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img class="img-radius img-fluid wid-80"
                                                                src="{{ !empty($maintainer->user->profile) ? fetch_file($maintainer->user->profile, 'upload/profile/') : $profile }}"
                                                                alt="User image" />
                                                        </div>
                                                        <div class="flex-grow-1 mx-3">
                                                            <h5 class="mb-1">
                                                                {{ ucfirst(!empty($maintainer->user) ? $maintainer->user->first_name : '') . ' ' . ucfirst(!empty($maintainer->user) ? $maintainer->user->last_name : '') }}
                                                            </h5>
                                                            @php
                                                                $rated = $requests->where('tenant_rating', '>', 0);
                                                                $completedJobs = $requests->where('status', 'completed')->count();
                                                            @endphp
                                                            <div class="small text-muted">
                                                                @if ($rated->count())
                                                                    <span class="text-warning">★</span> {{ number_format($rated->avg('tenant_rating'), 1) }}
                                                                    ({{ $rated->count() }} {{ __('ratings') }}) &middot;
                                                                @endif
                                                                {{ $completedJobs }} {{ __('jobs completed') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body px-2 pb-0">
                                                    <div class="list-group list-group-flush">
                                                        <a href="#" class="list-group-item list-group-item-action">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i class="material-icons-two-tone f-20">email</i>
                                                                </div>
                                                                <div class="flex-grow-1 mx-3">
                                                                    <h5 class="m-0">{{ __('Email') }}</h5>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <small>{{ !empty($maintainer->user) ? $maintainer->user->email : '-' }}</small>
                                                                </div>
                                                            </div>
                                                        </a>
                                                        <a href="#" class="list-group-item list-group-item-action">
                                                            <div class="d-flex align-items-center">
                                                                <div class="flex-shrink-0">
                                                                    <i
                                                                        class="material-icons-two-tone f-20">phonelink_ring</i>
                                                                </div>
                                                                <div class="flex-grow-1 mx-3">
                                                                    <h5 class="m-0">{{ __('Phone') }}</h5>
                                                                </div>
                                                                <div class="flex-shrink-0">
                                                                    <small>{{ !empty($maintainer->user) ? $maintainer->user->phone_number : '-' }}</small>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Assigned Properties Table --}}
                                        <div class="col-lg-8 col-xxl-9">
                                            <div class="card border">
                                                <div class="card-header">
                                                    <h5>{{ __('Assigned Properties') }}</h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-hover mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>{{ __('Property Name') }}</th>
                                                                    <th>{{ __('Address') }}</th>
                                                                    <th>{{ __('Type') }}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse ($maintainer->properties() as $property)
                                                                    <tr>
                                                                        <td>{{ $property->name ?? '-' }}</td>
                                                                        <td>{{ $property->address ?? '-' }}</td>
                                                                        <td>
                                                                            <span class="badge bg-light-secondary"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('Type') }}">
                                                                                {{ \App\Models\Property::types()[$property->type] ?? $property->type }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="3"
                                                                            class="text-center text-muted py-3">
                                                                            {{ __('No properties assigned.') }}
                                                                        </td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                {{-- TAB 2: Maintenance Requests --}}
                                <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">

                                    {{-- Toggle Buttons --}}
                                    <div class="d-flex justify-content-end mb-3 gap-2">
                                        <button class="btn btn-outline-primary btn-sm active" id="btn-list-view"
                                            onclick="switchView('list')">
                                            <i class="ti ti-list me-1"></i> {{ __('List') }}
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" id="btn-kanban-view"
                                            onclick="switchView('kanban')">
                                            <i class="ti ti-layout-kanban me-1"></i> {{ __('Kanban') }}
                                        </button>
                                    </div>

                                    {{-- LIST VIEW --}}
                                    <div id="list-view">
                                        <div class="dt-responsive table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('Property') }}</th>
                                                        <th>{{ __('Unit') }}</th>
                                                        <th>{{ __('Issue') }}</th>
                                                        <th>{{ __('Maintainer') }}</th>
                                                        <th>{{ __('Request Date') }}</th>
                                                        <th>{{ __('Status') }}</th>
                                                        <th>{{ __('Attachment') }}</th>
                                                        @if (Gate::check('show maintenance request'))
                                                            <th class="text-right">{{ __('Action') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($requests as $request)
                                                        <tr role="row">
                                                            <td>{{ !empty($request->properties) ? $request->properties->name : '-' }}
                                                            </td>
                                                            <td>{{ !empty($request->units) ? $request->units->name : '-' }}
                                                            </td>
                                                            <td>{{ !empty($request->types) ? $request->types->title : '-' }}
                                                            </td>
                                                            <td>{{ !empty($request->maintainers) ? $request->maintainers->name : '-' }}
                                                            </td>
                                                            <td>{{ dateFormat($request->request_date) }}</td>
                                                            <td>
                                                                @if ($request->status == 'pending')
                                                                    <span class="badge bg-light-warning">
                                                                        {{ \App\Models\MaintenanceRequest::status()[$request->status] }}
                                                                    </span>
                                                                @elseif ($request->status == 'in_progress')
                                                                    <span class="badge bg-light-info">
                                                                        {{ \App\Models\MaintenanceRequest::status()[$request->status] }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-light-success">
                                                                        {{ \App\Models\MaintenanceRequest::status()[$request->status] }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if (!empty($request->issue_attachment))
                                                                    <a href="{{ fetch_file($request->issue_attachment, 'upload/issue_attachment/') }}"
                                                                        download>
                                                                        <i class="ti ti-download"></i>
                                                                    </a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            @if (Gate::check('show maintenance request'))
                                                                <td class="text-right">
                                                                    <div class="cart-action">
                                                                        @can('show maintenance request')
                                                                            <a class="avtar avtar-xs btn-link-warning text-warning"
                                                                                href="{{ route('maintenance-request.show', \Crypt::encrypt($request->id)) }}">
                                                                                <i data-feather="eye"></i>
                                                                            </a>
                                                                        @endcan
                                                                        @if (\Auth::user()->type == 'maintainer')
                                                                            <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                                                data-size="lg" data-bs-toggle="tooltip"
                                                                                data-bs-original-title="{{ __('Status Update') }}"
                                                                                href="#"
                                                                                data-url="{{ route('maintenance-request.action', $request->id) }}"
                                                                                data-title="{{ __('Maintenance Request Status') }}">
                                                                                <i data-feather="check-square"></i>
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted py-4">
                                                                {{ __('No maintenance requests found.') }}
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div id="kanban-view" style="display:none;">
                                        @php
                                            $statuses = \App\Models\MaintenanceRequest::status();
                                            $statusColors = [
                                                'pending' => '#f0ad4e',
                                                'in_progress' => '#5bc0de',
                                                'completed' => '#5cb85c',
                                            ];
                                        @endphp

                                        <div class="pc-kanban-wrapper">
                                            @foreach ($statuses as $key => $label)
                                                @php
                                                    $columnRequests = $requests->where('status', $key);
                                                @endphp

                                                <div class="pc-kanban-column kanban-column">
                                                    <div class="pc-kanban-header">
                                                        <h4>
                                                            {{ $label }}
                                                            &nbsp;<span class="countTodo counts"
                                                                id="count-{{ $key }}">
                                                                ({{ $columnRequests->count() }})
                                                            </span>
                                                        </h4>
                                                    </div>
                                                    <div class="pc-kanban-body">
                                                        <div class="pc-kanban-cards" id="status-{{ $key }}"
                                                            data-status="{{ $key }}">
                                                            @foreach ($columnRequests as $request)
                                                                <div class="card border"
                                                                    data-request-id="{{ $request->id }}">
                                                                    <div class="card-body px-3 py-3">

                                                                        <h5 class="mb-1 text-truncate">
                                                                            {{ !empty($request->types) ? $request->types->title : '-' }}
                                                                        </h5>

                                                                        <div class="link-secondary text-sm mt-1">
                                                                            <i
                                                                                class="material-icons-two-tone text-secondary f-16 me-1">apartment</i>
                                                                            <span>{{ __('Property') }}: </span>
                                                                            {{ !empty($request->properties) ? $request->properties->name : '-' }}
                                                                        </div>

                                                                        <div class="link-secondary text-sm mt-1">
                                                                            <i
                                                                                class="material-icons-two-tone text-secondary f-16 me-1">door_front</i>
                                                                            <span>{{ __('Unit') }}: </span>
                                                                            {{ !empty($request->units) ? $request->units->name : '-' }}
                                                                        </div>

                                                                        <div class="link-secondary text-sm mt-1">
                                                                            <i
                                                                                class="material-icons-two-tone text-secondary f-16 me-1">calendar_today</i>
                                                                            <span>{{ __('Date') }}: </span>
                                                                            {{ dateFormat($request->request_date) }}
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>

                                 <div class="d-flex wizard justify-content-between flex-wrap gap-2 mt-3">
                                <div class="first">
                                    <a href="#" class="btn btn-secondary">{{ __('First') }}</a>
                                </div>
                                <div class="d-flex">
                                    <div class="previous me-2">
                                        <a href="#" class="btn btn-secondary">{{ __('Back To Previous') }}</a>
                                    </div>
                                    <div class="next">
                                        <a href="#" class="btn btn-secondary">{{ __('Next Step') }}</a>
                                    </div>
                                </div>
                                <div class="last">

                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-page')
    <style>
        .gu-mirror {
            position: fixed !important;
            margin: 0 !important;
            z-index: 9999 !important;
            opacity: 0.85;
            pointer-events: none;
            min-width: 220px;
            background: var(--bs-body-bg, #fff);
            border: 1px solid rgba(0, 0, 0, .12);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
            padding: 12px;
        }

        .gu-transit {
            opacity: 0.3;
        }

        .pc-kanban-column {
            overflow: visible !important;
        }

        .pc-kanban-body {
            overflow: visible !important;
            max-height: 600px;
            overflow-y: auto !important;
        }

        #kanban-view .pc-kanban-cards .card {
            touch-action: none;
        }

        body.gu-unselectable {
            overflow: hidden !important;
            touch-action: none;
        }

        .form-wizard .nav-pills .nav-link.active,
        .form-wizard .nav-pills .show>.nav-link {
            background: var(--bs-secondary) !important;
        }
    </style>
@endpush

@push('script-page')
    <script src="{{ asset('assets/js/plugins/wizard.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            new Wizard('#basicwizard', {
                validate: true,
                progress: true
            });
        });
    </script>
    <script>
        (function() {
            var _orig = EventTarget.prototype.addEventListener;
            EventTarget.prototype.addEventListener = function(type, fn, options) {
                if (type === 'touchstart' || type === 'touchmove' || type === 'touchend') {
                    if (typeof options === 'boolean') {
                        options = {
                            capture: options,
                            passive: false
                        };
                    } else if (options && typeof options === 'object') {
                        options = Object.assign({}, options, {
                            passive: false
                        });
                    } else {
                        options = {
                            passive: false
                        };
                    }
                }
                return _orig.call(this, type, fn, options);
            };
        })();
    </script>

    <script src="{{ asset('assets/js/plugins/dragula.min.js') }}"></script>

    <script>
        var drakeRequests = null;

        function initKanban() {
            if (drakeRequests !== null) return;

            var requestContainers = Array.from(
                document.querySelectorAll('#kanban-view .pc-kanban-cards')
            );

            if (typeof dragula === 'undefined' || !requestContainers.length) return;

            drakeRequests = dragula(requestContainers, {
                mirrorContainer: document.body,
                revertOnSpill: true
            });

            drakeRequests.on('drop', function(el, target, source) {
                if (!target || !target.classList.contains('pc-kanban-cards')) return;

                var requestId = el.getAttribute('data-request-id');
                var newStatus = target.getAttribute('data-status');

                if (!requestId || !newStatus) return;

                var placeholder = target.querySelector('.empty-placeholder');
                if (placeholder) placeholder.remove();

                // Fix count: only direct children, not all nested .card.border
                [source, target].forEach(function(col) {
                    if (!col) return;
                    var count = Array.from(col.children).filter(function(c) {
                        return c.classList.contains('card') && c.classList.contains('border');
                    }).length;
                    var key = col.getAttribute('data-status');
                    var badge = document.getElementById('count-' + key);
                    if (badge) badge.textContent = '(' + count + ')';
                });

                $.ajax({
                    url: '{{ route('maintainer.request.status.update') }}',
                    type: 'POST',
                    data: {
                        requestId: requestId,
                        status: newStatus,
                        "_token": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        toastrs("Success!", "Status updated successfully.", "success");
                    },
                    error: function() {
                        toastrs("Error!", "Failed to update status.", "error");
                    }
                });
            });
        }

        function switchView(view) {
            if (view === 'list') {
                document.getElementById('list-view').style.display = 'block';
                document.getElementById('kanban-view').style.display = 'none';
                document.getElementById('btn-list-view').classList.add('active');
                document.getElementById('btn-kanban-view').classList.remove('active');
            } else {
                document.getElementById('list-view').style.display = 'none';
                document.getElementById('kanban-view').style.display = 'block';
                document.getElementById('btn-kanban-view').classList.add('active');
                document.getElementById('btn-list-view').classList.remove('active');
                initKanban();
            }
        }
    </script>
@endpush
