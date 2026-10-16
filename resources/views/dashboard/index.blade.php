@extends('layouts.app')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">
        {{ __('Dashboard') }}</li>
@endsection

@push('script-page')
    <script>
        var options = {
            chart: {
                type: 'area',
                height: 450,
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
                    name: "{{ __('Total Income') }}",
                    data: {!! json_encode($result['incomeExpenseByMonth']['income']) !!}
                },
                {
                    name: "{{ __('Total Expense') }}",
                    data: {!! json_encode($result['incomeExpenseByMonth']['expense']) !!}
                }
            ],
            xaxis: {
                categories: {!! json_encode($result['incomeExpenseByMonth']['label']) !!},
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
        var incomeExpenseChart = new ApexCharts(document.querySelector('#incomeExpense'), options);
        incomeExpenseChart.render();

        var options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            colors: ['#FFC107'],
            dataLabels: {
                enabled: false
            },
            legend: {
                show: true,
                position: 'top'
            },
            markers: {
                size: 1,
                colors: ['#fff'],
                strokeColors: ['#FFC107'],
                strokeWidth: 1,
                shape: 'circle',
                hover: {
                    size: 4
                }
            },
            stroke: {
                width: 1,
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
                name: "Revenue",
                data: {!! json_encode($result['revenueAnalytics']['revenue']) !!}
            }],
            xaxis: {
                categories: {!! json_encode($result['revenueAnalytics']['label']) !!},
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

        var revenueChart = new ApexCharts(document.querySelector('#revenue-analytics-chart'), options);
        revenueChart.render();

        // Filter change
        const filter = document.getElementById('revenueFilter');
        const customRange = document.getElementById('customRange');

        filter.addEventListener('change', function() {
            let type = this.value;
            if (type === 'custom') {
                customRange.classList.remove('d-none');
                return;
            } else {
                customRange.classList.add('d-none');
            }
            fetchData(type);
        });

        // Daterangepicker init
        $('#demo').daterangepicker({
            autoApply: true,
            autoUpdateInput: false,
            locale: {
                format: 'MM/DD/YYYY'
            }
        }, function(start, end) {
            $('#demo').val(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
        });

        // Apply button
        document.getElementById('applyCustom').addEventListener('click', function() {
            let val = document.getElementById('demo').value;

            if (!val || !val.includes(' - ')) {
                alert('Please select a date range');
                return;
            }

            let parts = val.split(' - ');
            let start = parts[0].trim();
            let end = parts[1].trim();

            fetchData('custom', start, end);
        });

        // Fetch function
        function fetchData(type, start = '', end = '') {
            let url = `{{ route('revenue.analytics') }}?type=${type}`;

            if (type === 'custom') {
                url += `&start=${start}&end=${end}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    revenueChart.updateOptions({
                        xaxis: {
                            categories: data.label
                        },
                        series: [{
                            name: 'Revenue',
                            data: data.revenue
                        }]
                    });
                });
        }

        



        var total_income_graph_options = {
            chart: {
                height: 225,
                type: 'donut'
            },
            series: [0, 0],
            colors: ['#F44336', '#00C853'], // 🔴 Occupied first, 🟢 Vacant second
            labels: ['Occupied', 'Vacant'], // Occupied first, Vacant second
            legend: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        };

        var donutChart = new ApexCharts(document.querySelector('#total-income-graph'), total_income_graph_options);
        donutChart.render();

        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.property-filter');
            if (items.length > 0) {
                let first = items[0];
                document.getElementById('currentPropertyName').innerText = first.innerText;
                loadPropertyData(first.dataset.id);
            }

            items.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    items.forEach(el => el.classList.remove('active', 'fw-bold'));
                    this.classList.add('active', 'fw-bold');
                    document.getElementById('currentPropertyName').innerText = this.innerText;
                    loadPropertyData(this.dataset.id);
                });
            });
        });

        function loadPropertyData(propertyId) {
            fetch(`{{ route('property.unit.analytics') }}?property_id=${propertyId}`)
                .then(res => res.json())
                .then(data => {
                    donutChart.updateSeries([data.occupied, data.vacant]); // Occupied 🔴, Vacant 🟢
                    document.getElementById('occupiedCount').innerText = data.occupied;
                    document.getElementById('vacantCount').innerText = data.vacant;
                });
        }
    </script>
@endpush

@php
    $settings = settings();
@endphp
@section('content')
    @php
        $cur = $settings['CURRENCY_SYMBOL'];
        $occupancyRate = $result['totalUnit'] > 0 ? round(100 - $result['vacancyRate'], 1) : 0;
        $net = $result['totalIncome'] - $result['totalExpense'];
    @endphp

    {{-- Portfolio at a glance --}}
    @include('dashboard._section', ['title' => __('Portfolio at a Glance')])
    <div class="row">
        @include('dashboard._stat', ['icon' => 'building', 'color' => 'secondary', 'label' => __('Properties'), 'value' => $result['totalProperty'], 'sub' => $result['totalUnit'] . ' ' . __('units'), 'url' => Gate::check('manage property') ? route('property.index') : null])
        @include('dashboard._stat', ['icon' => 'users', 'color' => 'info', 'label' => __('Tenants'), 'value' => $result['totalTenant'], 'url' => Gate::check('manage tenant') ? route('tenant.index') : null])
        @include('dashboard._stat', ['icon' => 'chart-pie', 'color' => $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger'), 'label' => __('Occupancy'), 'value' => $occupancyRate . '%', 'sub' => __('Vacancy') . ' ' . $result['vacancyRate'] . '%'])
        @include('dashboard._stat', ['icon' => 'report-money', 'color' => 'primary', 'label' => __('Rent Collected This Month'), 'value' => $result['collectionRate'] . '%', 'sub' => priceFormat($result['monthCollected']) . ' / ' . priceFormat($result['monthInvoiced'])])
    </div>

    {{-- Needs attention --}}
    @php
        $attention = [
            ['icon' => 'tool', 'color' => 'warning', 'label' => __('Pending Repairs'), 'value' => $result['totalPendingRequest'], 'url' => Gate::check('manage maintenance request') ? route('maintenance-request.index') : null],
            ['icon' => 'calendar-event', 'color' => 'info', 'label' => __('Leases Ending (30 Days)'), 'value' => $result['leasesEnding'], 'url' => null],
            ['icon' => 'file-alert', 'color' => 'danger', 'label' => __('Documents Expiring'), 'value' => $result['documentsExpiring'], 'url' => null],
        ];
        if (Gate::check('manage lead')) {
            $attention[] = ['icon' => 'user-plus', 'color' => 'primary', 'label' => __('New Leads (30 Days)'), 'value' => $result['newLeads'], 'url' => route('lead.index')];
            $attention[] = ['icon' => 'phone-call', 'color' => 'warning', 'label' => __('Follow-ups Due'), 'value' => $result['followUpsDue'], 'url' => route('lead.index')];
        }
        if (Gate::check('manage rental application')) {
            $attention[] = ['icon' => 'file-certificate', 'color' => 'secondary', 'label' => __('Pending Applications'), 'value' => $result['pendingApplications'], 'url' => route('rental-application.index', ['status' => 'pending'])];
        }
    @endphp
    @include('dashboard._section', ['title' => __('Needs Attention')])
    <div class="card">
        <div class="card-body py-3">
            <div class="row g-2">
                @foreach ($attention as $item)
                    <div class="col-6 col-md-4 col-xl">
                        <a @if ($item['url']) href="{{ $item['url'] }}" @endif class="d-flex align-items-center gap-2 rounded-3 p-2 h-100 text-reset {{ $item['value'] > 0 ? 'bg-light-' . $item['color'] : 'bg-light' }}">
                            <i class="ti ti-{{ $item['icon'] }} f-20 text-{{ $item['color'] }} flex-shrink-0"></i>
                            <div style="min-width:0">
                                <div class="fw-bold fs-5 lh-1">{{ $item['value'] }}</div>
                                <small class="text-muted d-block">{{ $item['label'] }}</small>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Agenda: next 7 days, recent payments, repairs & occupancy --}}
    @if (!empty($extra))
        @include('dashboard._section', ['title' => __('Activity & Schedule')])
        @include('dashboard._owner_extra')
    @endif

    {{-- Action lists --}}
    <div class="row">
        <div class="col-xl-7 d-flex">
            <div class="card w-100">
                <div class="card-header">
                    <h5>
                        {{ __('Due / Overdue List') }}
                    </h5>
                </div>
                <div class="card-body pt-0">

                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Property & Unit') }}</th>
                                    <th>{{ __('Due Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['invoiceDue'] as $invoice)
                                    <tr>
                                        <td>
                                            @can('show invoice')
                                                <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('View') }}">
                                                    {{ invoicePrefix() . $invoice->invoice_id }}
                                                </a>
                                            @else
                                                {{ invoicePrefix() . $invoice->invoice_id }}
                                            @endcan
                                        </td>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ optional($invoice->properties)->name }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ optional($invoice->units)->name }}
                                            </div>
                                        </td>
                                        <td>{{ dateFormat($invoice->end_date) }} </td>
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
                </div>
            </div>
        </div>
        <div class="col-xl-5 d-flex">

            <div class="card w-100">
                <div class="card-header">
                    <h5>{{ __('Maintenance Request') }}</h5>
                </div>

                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">

                        @forelse ($maintenanceRequests as $request)
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">

                                    <div style="width: 35%;">
                                        <div class="fw-semibold">
                                            {{ optional($request->properties)->name }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ optional($request->units)->name }}
                                        </div>
                                    </div>

                                    <div style="width: 45%;" class="text-muted">
                                        {{ optional($request->types)->title }}
                                    </div>

                                    <div style="width: 20%;" class="text-end text-muted small">
                                        {{ dateFormat($request->request_date) }}
                                    </div>
                                    @can('create maintenance request')
                                        <div style="width: 20%;" class="text-end text-muted small">
                                            <a href="#" class="customModal" data-size="md"
                                                data-url="{{ route('maintenance.assign', ['request_id' => $request->id]) }}"
                                                data-title="{{ __('Assign Maintainer') }}">
                                                <i class="ti ti-circle-plus text-secondary fs-2 text-center"
                                                    data-bs-original-title="{{ __('Assign Maintainer') }}"
                                                    data-bs-toggle="tooltip"></i>
                                            </a>
                                        </div>
                                    @endcan

                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">
                                {{ __('No Maintenance Requests Found') }}
                            </li>
                        @endforelse

                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Finance --}}
    @include('dashboard._section', ['title' => __('Finance'), 'sub' => __('This month')])
    <div class="row">
        @include('dashboard._stat', ['icon' => 'trending-up', 'color' => 'success', 'label' => __('Revenue'), 'value' => $cur . '<span class="count">' . $result['totalIncome'] . '</span>'])
        @include('dashboard._stat', ['icon' => 'trending-down', 'color' => 'danger', 'label' => __('Expenses'), 'value' => $cur . '<span class="count">' . $result['totalExpense'] . '</span>', 'sub' => __('Net') . ' ' . priceFormat($net), 'url' => Gate::check('manage expense') ? route('expense.index') : null])
        @include('dashboard._stat', ['icon' => 'credit-card-off', 'color' => 'warning', 'label' => __('Pending Payments'), 'value' => $cur . '<span class="count">' . $result['totalPendingPayment'] . '</span>', 'sub' => __('all open invoices'), 'url' => Gate::check('manage invoice') ? route('invoice.index') : null])
        @can('manage deposit')
            @include('dashboard._stat', ['icon' => 'building-bank', 'color' => 'info', 'label' => __('Deposits Held'), 'value' => priceFormat($result['depositsHeld'])])
        @endcan
    </div>
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <h5 class="mb-0">{{ __('Revenue Analytics') }}</h5>
                        <select id="revenueFilter" class="form-select rounded-3 form-select-sm w-auto">
                            <option value="monthly" selected>Month</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div id="customRange" class="d-none mt-2">
                        <div class="d-flex align-items-end gap-2 flex-wrap">
                            <div>
                                <b>{{ Form::label('date range', __('Date'), ['class' => 'form-label']) }}</b>
                                {{ Form::text('date', request('date'), [
                                    'class' => 'form-control date',
                                    'placeholder' => __('Select date range'),
                                    'id' => 'demo',
                                    'autocomplete' => 'off',
                                    'readonly' => 'readonly',
                                ]) }}
                            </div>
                            <div>
                                <button id="applyCustom" class="btn btn-primary">{{ __('Apply') }}</button>
                            </div>
                        </div>
                    </div>
                    <div id="revenue-analytics-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">{{ __('Occupied vs Vacant') }}</h5>
                        <div class="dropdown">
                            <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical f-18"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" id="propertyDropdown">
                                @foreach ($properties as $id => $name)
                                    <a class="dropdown-item property-filter {{ $loop->first ? 'active fw-bold' : '' }}"
                                        href="#" data-id="{{ $id }}">
                                        {{ $name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <small class="text-muted">{{ __('Property') }}</small>
                    <h6 id="currentPropertyName">--</h6>

                    <div id="total-income-graph"></div>

                    <div class="mb-2 mt-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-circle text-danger f-12"></i>
                            {{ __('Occupied') }}
                        </h6>
                        <p class="mb-0 text-muted" id="occupiedCount">0</p>
                    </div>

                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-circle text-success f-12"></i>
                            {{ __('Vacant') }}
                        </h6>
                        <p class="mb-0 text-muted" id="vacantCount">0</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h5 class="mb-1">{{ __('Analysis Report') }}</h5>
                            <p class="text-muted mb-2">{{ __('Income and Expense Overview') }}</p>
                        </div>

                    </div>
                    <div id="incomeExpense"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
