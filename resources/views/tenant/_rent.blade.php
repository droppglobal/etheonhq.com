@php
    $increases = \App\Models\RentIncrease::where('tenant_id', $tenant->id)->orderByDesc('id')->get();
    $scheduled = $increases->firstWhere('status', 'scheduled');
    $canEdit = Gate::check('edit tenant') && !in_array(\Auth::user()->type, ['tenant', 'maintainer', 'landlord']);
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h5 class="mb-0">{{ __('Rent') }}</h5>
        <small class="text-muted">{{ __('Current rent') }}: <strong>{{ $tenant->units ? priceFormat($tenant->units->rent) : '-' }}</strong>
            @if ($tenant->units) · {{ \App\Models\PropertyUnit::rentTypes()[$tenant->units->rent_type] ?? '' }}@endif</small>
    </div>
    @if ($canEdit && $tenant->units && !$scheduled)
        <a href="#" class="btn btn-secondary customModal" data-size="md" data-url="{{ route('tenant.rent-increase.create', $tenant->id) }}" data-title="{{ __('Schedule rent change') }}">
            <i class="ti ti-trending-up"></i> {{ __('Schedule rent change') }}</a>
    @endif
</div>
@if ($scheduled)
    <div class="alert alert-light-info d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="ti ti-calendar-event"></i>
            {{ __('Rent changes from :old to :new on :date', ['old' => priceFormat($scheduled->old_rent), 'new' => priceFormat($scheduled->new_rent), 'date' => dateFormat($scheduled->effective_date)]) }}
            @if ($scheduled->changePercent() !== null)({{ $scheduled->changePercent() > 0 ? '+' : '' }}{{ $scheduled->changePercent() }}%)@endif
            · {{ $scheduled->notice_sent_at ? __('notice sent :date', ['date' => dateFormat($scheduled->notice_sent_at)]) : __('tenant not notified yet') }}</span>
        @if ($canEdit)
            {{ Form::open(['route' => ['tenant.rent-increase.cancel', $tenant->id, $scheduled->id], 'method' => 'post']) }}
            <button class="btn btn-sm btn-light-danger">{{ __('Cancel') }}</button>
            {{ Form::close() }}
        @endif
    </div>
@endif
<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead><tr><th>{{ __('Effective') }}</th><th>{{ __('Old rent') }}</th><th>{{ __('New rent') }}</th><th>{{ __('Change') }}</th><th>{{ __('Status') }}</th><th class="text-end">{{ __('Notice') }}</th></tr></thead>
        <tbody>
            @forelse ($increases as $increase)
                <tr>
                    <td>{{ dateFormat($increase->effective_date) }}</td>
                    <td>{{ priceFormat($increase->old_rent) }}</td>
                    <td class="fw-semibold">{{ priceFormat($increase->new_rent) }}</td>
                    <td>{{ $increase->changePercent() !== null ? ($increase->changePercent() > 0 ? '+' : '') . $increase->changePercent() . '%' : '-' }}</td>
                    <td><span class="badge bg-light-{{ ['scheduled' => 'info', 'applied' => 'success', 'cancelled' => 'secondary'][$increase->status] }}">{{ \App\Models\RentIncrease::statuses()[$increase->status] }}</span></td>
                    <td class="text-end">@if ($canEdit)<a href="{{ route('tenant.rent-increase.letter', [$tenant->id, $increase->id]) }}"><i class="ti ti-file-download f-18"></i></a>@endif</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">{{ __('No rent changes yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
