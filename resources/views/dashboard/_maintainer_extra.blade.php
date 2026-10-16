@include('dashboard._section', ['title' => __('My Jobs')])
<div class="row">
    @include('dashboard._stat', ['icon' => 'alert-triangle', 'color' => 'danger', 'label' => __('Urgent Open Jobs'), 'value' => $extra['urgentOpen'] ?? 0, 'sub' => __('Emergency and high priority'), 'url' => route('maintenance-request.index')])
    @include('dashboard._stat', ['icon' => 'clock', 'color' => 'warning', 'label' => __('Pending'), 'value' => $result['pendingRequest'] ?? 0, 'sub' => ($result['todayRequest'] ?? 0) . ' ' . __('new today') . ' · ' . ($result['totalRequest'] ?? 0) . ' ' . __('all time'), 'url' => route('maintenance-request.pending')])
    @include('dashboard._stat', ['icon' => 'loader', 'color' => 'info', 'label' => __('In Progress'), 'value' => $extra['inProgress'] ?? 0, 'url' => route('maintenance-request.inprogress')])
    @include('dashboard._stat', ['icon' => 'circle-check', 'color' => 'success', 'label' => __('Completed This Month'), 'value' => $extra['completedThisMonth'] ?? 0, 'sub' => (float) ($extra['hoursThisMonth'] ?? 0) . ' ' . __('hours logged')])
</div>
<div class="row">
    <div class="col-xl-7 d-flex">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h5 class="mb-0">{{ __('My Work Queue') }}</h5><small class="text-muted">{{ __('Most urgent first') }}</small></div>
                <a href="{{ route('maintenance-request.index') }}" class="small">{{ __('View all') }} <i class="ti ti-chevron-right"></i></a>
            </div>
            <div class="card-body pt-1">
                @forelse (($extra['queue'] ?? []) as $job)
                    <a href="{{ route('maintenance-request.show', \Crypt::encrypt($job->id)) }}" class="d-flex justify-content-between align-items-center py-2 border-bottom text-reset">
                        <div style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ optional($job->types)->title ?? __('Request') }} · {{ optional($job->units)->name }}</div>
                            <small class="text-muted">{{ optional($job->properties)->name }} · {{ __('since') }} {{ dateFormat($job->request_date) }} · {{ \App\Models\MaintenanceRequest::status()[$job->status] ?? $job->status }}@if ($job->preferredVisitText()) · <i class="ti ti-clock"></i> {{ $job->preferredVisitText() }}@endif</small>
                        </div>
                        @if ($job->ai_priority)
                            <span class="badge bg-light-{{ \App\Support\MaintenanceTriage::color($job->ai_priority) }} flex-shrink-0 ms-2">{{ \App\Support\MaintenanceTriage::priorities()[$job->ai_priority] ?? '' }}</span>
                        @endif
                    </a>
                @empty
                    <p class="text-muted mb-0 py-2">{{ __('No open jobs. Nice work!') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        @php $assigned = !empty($maintainer) ? array_filter($maintainer->properties()) : []; @endphp
        @if (count($assigned))
            <div class="card">
                <div class="card-header"><h5 class="mb-0">{{ __('Assigned Properties') }}</h5></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    @foreach ($assigned as $property)
                        <span class="badge rounded-pill px-3 py-2 fw-medium bg-light-primary"><i class="ti ti-building me-1"></i>{{ $property->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="card">
            <div class="card-header"><h5 class="mb-0">{{ __('Preventive Tasks (Next 14 Days)') }}</h5></div>
            <div class="card-body pt-1">
                @forelse (($extra['upcomingPreventive'] ?? []) as $task)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div style="min-width:0"><div class="fw-semibold text-truncate">{{ $task->title }}</div><small class="text-muted">{{ optional($task->properties)->name }}{{ !empty($task->units) ? ' / ' . $task->units->name : '' }}</small></div>
                        <span class="badge bg-light-secondary flex-shrink-0 ms-2">{{ dateFormat($task->next_date) }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0 py-2">{{ __('No preventive tasks scheduled for you.') }}</p>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Tenant Feedback') }}</h5>
                <span class="small text-muted">@if (($extra['avgRating'] ?? null) !== null)<span class="fw-semibold text-body">{{ $extra['avgRating'] ?? '' }}</span> <span class="text-warning">★</span> · @endif{{ ($extra['ratingCount'] ?? 0) == 1 ? __('1 rating') : __(':count ratings', ['count' => $extra['ratingCount'] ?? 0]) }}</span>
            </div>
            <div class="card-body pt-1">
                @forelse (($extra['recentFeedback'] ?? []) as $job)
                    <div class="py-2 border-bottom">
                        <span class="text-warning">{{ str_repeat('★', (int) $job->tenant_rating) }}</span><span class="text-muted">{{ str_repeat('★', 5 - (int) $job->tenant_rating) }}</span>
                        <small class="text-muted ms-1">{{ optional($job->types)->title }} · {{ dateFormat($job->updated_at) }}</small>
                        @if ($job->tenant_feedback)<div class="small">"{{ $job->tenant_feedback }}"</div>@endif
                    </div>
                @empty
                    <p class="text-muted mb-0 py-2">{{ __('No ratings yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
