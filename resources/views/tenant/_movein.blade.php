@php
    $checklist = \App\Support\MoveInChecklist::items($tenant);
    $progress = \App\Support\MoveInChecklist::progress($checklist);
    $canEdit = Gate::check('edit tenant') && !in_array(\Auth::user()->type, ['tenant', 'maintainer', 'landlord']);
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-0">{{ __('Move-in checklist') }}</h5>
        <small class="text-muted">{{ __('Items marked "auto" tick themselves from the agreement, deposit, inspection, meter readings and invoices.') }}</small>
    </div>
    <div class="text-end" style="min-width:180px">
        <div class="small fw-semibold">{{ $progress['done'] }} / {{ $progress['total'] }} {{ __('done') }}</div>
        <div class="progress mt-1" style="height:8px"><div class="progress-bar {{ $progress['percent'] == 100 ? 'bg-success' : 'bg-secondary' }}" style="width: {{ $progress['percent'] }}%"></div></div>
    </div>
</div>
<div class="list-group">
    @foreach ($checklist as $item)
        <div class="list-group-item d-flex flex-wrap align-items-center gap-3">
            <i class="ti ti-{{ $item->done ? 'circle-check text-success' : 'circle text-muted' }} f-24"></i>
            <div class="flex-grow-1" style="min-width:200px">
                <div class="fw-semibold">{{ $item->label }} @if ($item->auto)<span class="badge bg-light-secondary ms-1">{{ __('auto') }}</span>@endif</div>
                @if ($item->detail)<small class="text-muted">{{ $item->detail }}</small>@endif
            </div>
            @if ($canEdit && (!$item->done || $item->manual))
                {{ Form::open(['route' => ['tenant.checklist.toggle', $tenant->id, $item->key], 'method' => 'post', 'class' => 'd-flex gap-2']) }}
                @if (!$item->done)
                    <input type="text" name="note" class="form-control form-control-sm" style="width:180px" maxlength="190" placeholder="{{ __('Note (optional)') }}">
                    <button class="btn btn-sm btn-light-secondary">{{ __('Mark done') }}</button>
                @else
                    <button class="btn btn-sm btn-link text-muted">{{ __('Undo') }}</button>
                @endif
                {{ Form::close() }}
            @endif
        </div>
    @endforeach
</div>
