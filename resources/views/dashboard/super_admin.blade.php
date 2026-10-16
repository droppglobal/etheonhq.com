@extends('layouts.app')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection
@push('script-page')
<script>
    var options = {
        chart: {
            type: 'area',
            height: 400,
            toolbar: {
                show: false
            }
        },
        colors: ['#2ca58d', '#0a2342'],
        dataLabels: {
            enabled: false
        },
        legend: {
            show: true,
            position: 'top'
        },
        markers: {
            size: 1,
            colors: ['#fff', '#fff', '#fff'],
            strokeColors: ['#2ca58d', '#0a2342'],
            strokeWidth: 1,
            shape: 'circle',
            hover: {
                size: 4
            }
        },
        stroke: {
            width: 2,
            curve: 'smooth'
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                type: 'vertical',
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0
            }
        },
        grid: {
            show: false
        },
        series: [{
                name: "{{ __('Total Users') }}",
                data: {!! json_encode($result['organizationByMonth']['data']) !!}
            },
            {
                name: "{{ __('Total Payment') }}",
                data: {!! json_encode($result['paymentByMonth']['data']) !!}
            }
        ],
        xaxis: {
            categories: {!! json_encode($result['organizationByMonth']['label']) !!},
            tooltip: {
                enabled: false
            },
            labels: {
                hideOverlappingLabels: true
            },
            axisBorder: {
                show: false
            },
            axisTicks: {
                show: false
            }
        }
    };
    var chart = new ApexCharts(document.querySelector('#users_and_payments_overview'), options);
    chart.render();


</script>
@endpush

@section('content')
    @if (!empty($extra['serverIssues']))
        <div class="alert alert-light-warning">
            <div class="fw-semibold mb-1"><i class="ti ti-alert-triangle"></i> {{ __('Server check') }}</div>
            <ul class="mb-0 ps-3">
                @foreach ($extra['serverIssues'] as $issue)<li>{{ $issue }}</li>@endforeach
            </ul>
        </div>
    @endif
    @if ($extra['pendingTransfers'] > 0)
        <div class="alert alert-light-danger d-flex justify-content-between align-items-center">
            <span><i class="ti ti-building-bank"></i> {{ $extra['pendingTransfers'] == 1 ? __('1 bank transfer is waiting for your approval.') : __(':count bank transfers are waiting for your approval.', ['count' => $extra['pendingTransfers']]) }}</span>
            <a href="{{ route('subscription.transaction') }}" class="btn btn-sm btn-danger">{{ __('Review') }}</a>
        </div>
    @endif

    @include('dashboard._section', ['title' => __('Subscriptions & Revenue')])
    <div class="row">
        @include('dashboard._stat', ['icon' => 'users', 'color' => 'secondary', 'label' => __('Owners'), 'value' => $result['totalOrganization'], 'sub' => $extra['newOwnersThisMonth'] . ' ' . __('new this month'), 'url' => route('users.index')])
        @include('dashboard._stat', ['icon' => 'circle-check', 'color' => 'success', 'label' => __('Active Subscriptions'), 'value' => $extra['activeOwners'], 'sub' => $extra['onTrial'] . ' ' . __('on free trial'), 'url' => route('users.index')])
        @include('dashboard._stat', ['icon' => 'report-money', 'color' => 'primary', 'label' => __('Revenue This Month'), 'value' => priceFormat($extra['revenueThisMonth']), 'sub' => __('All time') . ' ' . subscriptionPaymentSettings()['CURRENCY_SYMBOL'] . $result['totalIncome']])
        @include('dashboard._stat', ['icon' => 'calendar-minus', 'color' => 'warning', 'label' => __('Expiring in 7 Days'), 'value' => $extra['expiringSoon']->count(), 'sub' => $extra['expiredOwners'] . ' ' . __('already expired')])
    </div>

    @include('dashboard._section', ['title' => __('Platform Usage')])
    <div class="row">
        @include('dashboard._stat', ['icon' => 'building', 'color' => 'info', 'label' => __('Properties'), 'value' => $extra['platformProperties']])
        @include('dashboard._stat', ['icon' => 'layout-grid', 'color' => 'secondary', 'label' => __('Units'), 'value' => $extra['platformUnits']])
        @include('dashboard._stat', ['icon' => 'users', 'color' => 'success', 'label' => __('Active Tenants'), 'value' => $extra['platformTenants']])
        @include('dashboard._stat', ['icon' => 'package', 'color' => 'warning', 'label' => __('Packages'), 'value' => $result['totalSubscription'], 'sub' => $result['totalTransaction'] . ' ' . __('transactions'), 'url' => route('subscriptions.index')])
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h5 class="mb-1">{{ __('Analysis Report') }}</h5>
                            <p class="text-muted mb-2">{{ __('Users and Payments Overview') }}</p>
                        </div>

                    </div>
                    <div id="users_and_payments_overview"></div>
                </div>
            </div>
        </div>

    </div>

<div class="row">
        <div class="col-xl-4 col-md-6 d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0">{{ __('Owners per Plan') }}</h5></div>
                <div class="card-body">
                    @php $planTotal = max(1, $extra['ownersPerPlan']->sum()); @endphp
                    @forelse ($extra['ownersPerPlan'] as $plan => $count)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small"><span class="fw-semibold">{{ $plan }}</span><span>{{ $count }}</span></div>
                            <div class="progress" style="height:8px"><div class="progress-bar bg-secondary" style="width: {{ round($count / $planTotal * 100) }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ __('No owners yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0">{{ __('Newest Owners') }}</h5></div>
                <div class="card-body pt-2">
                    @forelse ($extra['recentOwners'] as $owner)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div style="min-width:0"><div class="fw-semibold text-truncate">{{ $owner->name }}</div><small class="text-muted">{{ $owner->email }}</small></div>
                            <div class="text-end flex-shrink-0 ms-2"><span class="badge bg-light-secondary">{{ $extra['plans'][$owner->subscription] ?? '-' }}</span><br><small class="text-muted">{{ dateFormat($owner->created_at) }}</small></div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ __('No owners yet.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12 d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0">{{ __('Subscriptions Expiring Soon') }}</h5></div>
                <div class="card-body pt-2">
                    @forelse ($extra['expiringSoon'] as $owner)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div style="min-width:0"><div class="fw-semibold text-truncate">{{ $owner->name }}</div><small class="text-muted">{{ $extra['plans'][$owner->subscription] ?? '-' }}</small></div>
                            <span class="badge bg-light-warning flex-shrink-0 ms-2">{{ dateFormat($owner->subscription_expire_date) }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">{{ __('No subscriptions expire in the next 7 days.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
