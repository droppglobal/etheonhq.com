{{-- quick actions --}}
<div class="card">
    <div class="card-body d-flex flex-wrap gap-2 py-3">
        @can('manage invoice')<a href="{{ route('invoice.index') }}" class="btn btn-sm btn-secondary"><i class="ti ti-receipt"></i> {{ __('Pay Rent') }}</a>@endcan
        @can('create maintenance request')<a href="{{ route('maintenance-request.index') }}" class="btn btn-sm btn-light-secondary"><i class="ti ti-tool"></i> {{ __('Report a Problem') }}</a>@endcan
        @can('book facility')<a href="{{ route('facility.index') }}" class="btn btn-sm btn-light-secondary"><i class="ti ti-calendar-event"></i> {{ __('Book a Facility') }}</a>@endcan
        @can('create visitor pass')<a href="{{ route('visitor.index') }}" class="btn btn-sm btn-light-secondary"><i class="ti ti-user-check"></i> {{ __('Visitor Pass') }}</a>@endcan
        <a href="{{ route('assistant.index') }}" class="btn btn-sm btn-light-secondary"><i class="ti ti-messages"></i> {{ __('Ask the Assistant') }}</a>
    </div>
</div>

@if ($extra['inspectionToSign'])
    <div class="alert alert-light-warning d-flex justify-content-between align-items-center">
        <span><i class="ti ti-clipboard-check"></i> {{ __('Your :type inspection is ready for you to review and sign.', ['type' => mb_strtolower(\App\Models\Inspection::types()[$extra['inspectionToSign']->type] ?? '')]) }}</span>
        <a href="{{ route('inspection.show', $extra['inspectionToSign']->id) }}" class="btn btn-sm btn-warning">{{ __('Review') }}</a>
    </div>
@endif

<div class="row">
    @php $nd = $extra['nextDue']; @endphp
    @include('dashboard._stat', ['icon' => 'calendar-event', 'color' => $nd && $nd['overdue'] ? 'danger' : 'primary', 'label' => __('Next Payment'),
        'value' => $nd ? priceFormat($nd['amount']) : __('All paid'),
        'sub' => $nd ? (($nd['overdue'] ? __('Overdue since') : __('Due')) . ' ' . dateFormat($nd['date'])) : __('Nothing to pay right now'), 'url' => route('invoice.index')])
    @include('dashboard._stat', ['icon' => 'calendar-time', 'color' => 'info', 'label' => __('Lease'),
        'value' => $extra['leaseDaysLeft'] !== null ? ($extra['leaseDaysLeft'] == 1 ? __('1 day left') : __(':count days left', ['count' => $extra['leaseDaysLeft']])) : '-',
        'sub' => $extra['leaseProgress'] !== null ? '<div class="progress mt-1" style="height:5px"><div class="progress-bar bg-info" style="width:' . $extra['leaseProgress'] . '%"></div></div>' : null])
    @include('dashboard._stat', ['icon' => 'credit-card', 'color' => $extra['autopay'] ? 'success' : 'secondary', 'label' => __('Autopay'),
        'value' => $extra['autopay'] ? __('On') : __('Off'), 'sub' => $extra['autopay'] ?: __('Pay automatically on each due date'), 'url' => route('autopay.index')])
    @include('dashboard._stat', ['icon' => 'building-bank', 'color' => 'warning', 'label' => __('Deposit Held'), 'value' => priceFormat($extra['depositHeld'])])
</div>

{{-- my home + what's coming --}}
<div class="row">
    <div class="col-xl-5 d-flex">
        <div class="card w-100">
            <div class="card-header"><h5 class="mb-0">{{ __('My Home') }}</h5></div>
            <div class="card-body pt-2">
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Property') }}</span><span class="fw-semibold text-end">{{ optional($tenant->properties)->name ?: '-' }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Unit') }}</span><span class="fw-semibold text-end">{{ optional($tenant->units)->name ?: '-' }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Monthly rent') }}</span>
                    <span class="fw-semibold text-end">{{ !empty($result['unit']) ? priceFormat($result['unit']->rent) : '-' }}
                        @if (!empty($extra['rentChange']))<br><small class="text-info fw-normal">{{ __(':new from :date', ['new' => priceFormat($extra['rentChange']->new_rent), 'date' => dateFormat($extra['rentChange']->effective_date)]) }}</small>@endif
                    </span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Agreement') }}</span>
                    <span class="fw-semibold text-end">{{ !empty($result['agreement']) ? dateFormat($result['agreement']->agreement_start_date) . ' - ' . dateFormat($result['agreement']->agreement_end_date) : '-' }}</span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">{{ __('Parking') }}</span>
                    <span class="fw-semibold text-end">
                        @forelse ($extra['parking'] as $p){{ optional($p->slot)->code }}{{ $p->vehicle_number ? ' (' . $p->vehicle_number . ')' : '' }}{{ !$loop->last ? ', ' : '' }}@empty{{ __('No slot') }}@endforelse
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2"><span class="text-muted">{{ __('Polls waiting for your vote') }}</span>
                    @can('vote poll')<a href="{{ route('poll.index') }}" class="fw-semibold">{{ $extra['openPolls'] }}</a>@else<span>-</span>@endcan</div>
            </div>
        </div>
    </div>
    <div class="col-xl-7 d-flex">
        <div class="card w-100">
            <div class="card-header"><h5 class="mb-0">{{ __('Coming Up') }}</h5></div>
            <div class="card-body pt-1">
                @include('dashboard._timeline', ['items' => $extra['upcoming'], 'empty' => __('No upcoming bookings or visitors.')])
            </div>
        </div>
    </div>
</div>

{{-- money and repairs --}}
<div class="row">
    <div class="col-xl-7 d-flex">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Due / Overdue Invoices') }}</h5>
                <a href="{{ route('invoice.index') }}" class="small">{{ $extra['openInvoices']->count() }} {{ __('unpaid') }} · {{ priceFormat($extra['amountDue']) }}</a>
            </div>
            <div class="card-body pt-0">
                @if ($result['overdueInvoices']->count())
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['overdueInvoices'] as $invoice)
                                    <tr>
                                        <td>
                                            @can('show invoice')
                                                <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                    data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}">
                                                    {{ invoicePrefix() . $invoice->invoice_id }}
                                                </a>
                                            @else
                                                {{ invoicePrefix() . $invoice->invoice_id }}
                                            @endcan
                                            <small class="text-muted d-block">{{ date('F Y', strtotime($invoice->invoice_month)) }}</small>
                                        </td>
                                        <td>{{ dateFormat($invoice->end_date) }}</td>
                                        <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</td>
                                        <td>
                                            @if ($invoice->status == 'unpaid')
                                                <span
                                                    class="badge bg-light-danger">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                            @elseif($invoice->status == 'paid')
                                                <span
                                                    class="badge bg-light-success">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                            @elseif($invoice->status == 'partial_paid')
                                                <span
                                                    class="badge bg-light-warning">{{ \App\Models\Invoice::status()[$invoice->status] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-4 mb-0">{{ __('Nothing is due right now.') }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-5 d-flex">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('My Maintenance Requests') }}</h5>
                <a href="{{ route('maintenance-request.index') }}" class="small">{{ $extra['openRequests'] }} {{ __('open') }}</a>
            </div>
            <div class="card-body pt-1">
                @forelse ($result['maintenanceList']->take(6) as $maintenance)
                    <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div style="min-width:0">
                            <div class="fw-semibold text-truncate">{{ optional($maintenance->types)->title ?: '-' }}</div>
                            <small class="text-muted">{{ dateFormat($maintenance->request_date) }}</small>
                        </div>
                        @if ($maintenance->status == 'pending')
                            <span class="badge bg-light-warning flex-shrink-0">{{ __('Pending') }}</span>
                        @elseif($maintenance->status == 'in_progress')
                            <span class="badge bg-light-info flex-shrink-0">{{ __('In Progress') }}</span>
                        @else
                            <span class="badge bg-light-success flex-shrink-0">{{ __('Completed') }}</span>
                        @endif
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">{{ __('No repair requests yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
