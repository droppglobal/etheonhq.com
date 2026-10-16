@if ($extra['upcoming']->count() || $extra['recentPayments']->count() || isset($extra['openByPriority']) || !empty($extra['occupancy']))
    <div class="row">
        <div class="col-xl-4 col-md-6 d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0">{{ __('Next 7 Days') }}</h5></div>
                <div class="card-body pt-1">
                    @include('dashboard._timeline', ['items' => $extra['upcoming'], 'empty' => __('No viewings, bookings, preventive tasks or lease ends in the next 7 days.')])
                </div>
            </div>
        </div>
        @if ($extra['recentPayments']->count())
            <div class="col-xl-4 col-md-6 d-flex">
                <div class="card w-100">
                    <div class="card-header"><h5 class="mb-0">{{ __('Recent Payments') }}</h5></div>
                    <div class="card-body pt-1">
                        @foreach ($extra['recentPayments'] as $payment)
                            @php $payer = optional(optional(\App\Models\Tenant::find($payment->tenant_id))->user)->name; @endphp
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div style="min-width:0">
                                    <div class="fw-semibold text-truncate">{{ $payer ?: invoicePrefix() . $payment->invoice_number }}</div>
                                    <small class="text-muted">{{ invoicePrefix() . $payment->invoice_number }} · {{ $payment->payment_type }} · {{ dateFormat($payment->payment_date) }}</small>
                                </div>
                                <span class="fw-semibold {{ $payment->amount < 0 ? 'text-danger' : 'text-success' }} flex-shrink-0 ms-2">{{ $payment->amount < 0 ? '-' . priceFormat(abs($payment->amount)) : priceFormat($payment->amount) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        <div class="col-xl-4 col-md-12">
            @isset($extra['openByPriority'])
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">{{ __('Open Repairs by Priority') }}</h5></div>
                    <div class="card-body">
                        <div class="row g-2 text-center">
                            @foreach (['emergency' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary'] as $p => $color)
                                <div class="col-6">
                                    <a href="{{ route('maintenance-request.index') }}" class="d-block rounded p-2 bg-light-{{ $color }} text-reset">
                                        <div class="fs-4 fw-bold">{{ $extra['openByPriority'][$p] ?? 0 }}</div>
                                        <small>{{ \App\Support\MaintenanceTriage::priorities()[$p] }}</small>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="small text-muted mt-3">
                            @if (($extra['openByPriority']['unrated'] ?? 0) > 0){{ $extra['openByPriority']['unrated'] }} {{ __('older requests without priority') }} · @endif
                            {{ $extra['unassigned'] }} {{ __('without maintainer') }}
                            @if ($extra['oldestOpenDays'] !== null) · {{ __('oldest open for :d days', ['d' => $extra['oldestOpenDays']]) }}@endif
                        </div>
                    </div>
                </div>
            @endisset
            @if (!empty($extra['occupancy']) && $extra['occupancy']->count())
                <div class="card">
                    <div class="card-header"><h5 class="mb-0">{{ __('Occupancy by Property') }}</h5></div>
                    <div class="card-body pt-2">
                        @foreach ($extra['occupancy']->take(6) as $row)
                            @php $pct = round($row['occupied'] / $row['units'] * 100); @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small"><span class="text-truncate me-2">{{ $row['name'] }}</span><span class="flex-shrink-0">{{ $row['occupied'] }}/{{ $row['units'] }} · {{ $pct }}%</span></div>
                                <div class="progress" style="height:6px"><div class="progress-bar {{ $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $pct }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
