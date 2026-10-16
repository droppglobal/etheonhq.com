<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item" aria-current="page">
        <?php echo e(__('Dashboard')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
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
                    name: "<?php echo e(__('Total Income')); ?>",
                    data: <?php echo json_encode($result['incomeExpenseByMonth']['income']); ?>

                },
                {
                    name: "<?php echo e(__('Total Expense')); ?>",
                    data: <?php echo json_encode($result['incomeExpenseByMonth']['expense']); ?>

                }
            ],
            xaxis: {
                categories: <?php echo json_encode($result['incomeExpenseByMonth']['label']); ?>,
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
                data: <?php echo json_encode($result['revenueAnalytics']['revenue']); ?>

            }],
            xaxis: {
                categories: <?php echo json_encode($result['revenueAnalytics']['label']); ?>,
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
            let url = `<?php echo e(route('revenue.analytics')); ?>?type=${type}`;

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
            fetch(`<?php echo e(route('property.unit.analytics')); ?>?property_id=${propertyId}`)
                .then(res => res.json())
                .then(data => {
                    donutChart.updateSeries([data.occupied, data.vacant]); // Occupied 🔴, Vacant 🟢
                    document.getElementById('occupiedCount').innerText = data.occupied;
                    document.getElementById('vacantCount').innerText = data.vacant;
                });
        }
    </script>
<?php $__env->stopPush(); ?>

<?php
    $settings = settings();
?>
<?php $__env->startSection('content'); ?>
    <?php
        $cur = $settings['CURRENCY_SYMBOL'];
        $occupancyRate = $result['totalUnit'] > 0 ? round(100 - $result['vacancyRate'], 1) : 0;
        $net = $result['totalIncome'] - $result['totalExpense'];
    ?>

    
    <?php echo $__env->make('dashboard._section', ['title' => __('Portfolio at a Glance')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <?php echo $__env->make('dashboard._stat', ['icon' => 'building', 'color' => 'secondary', 'label' => __('Properties'), 'value' => $result['totalProperty'], 'sub' => $result['totalUnit'] . ' ' . __('units'), 'url' => Gate::check('manage property') ? route('property.index') : null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'users', 'color' => 'info', 'label' => __('Tenants'), 'value' => $result['totalTenant'], 'url' => Gate::check('manage tenant') ? route('tenant.index') : null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'chart-pie', 'color' => $occupancyRate >= 80 ? 'success' : ($occupancyRate >= 50 ? 'warning' : 'danger'), 'label' => __('Occupancy'), 'value' => $occupancyRate . '%', 'sub' => __('Vacancy') . ' ' . $result['vacancyRate'] . '%'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'report-money', 'color' => 'primary', 'label' => __('Rent Collected This Month'), 'value' => $result['collectionRate'] . '%', 'sub' => priceFormat($result['monthCollected']) . ' / ' . priceFormat($result['monthInvoiced'])], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    
    <?php
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
    ?>
    <?php echo $__env->make('dashboard._section', ['title' => __('Needs Attention')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="card">
        <div class="card-body py-3">
            <div class="row g-2">
                <?php $__currentLoopData = $attention; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-6 col-md-4 col-xl">
                        <a <?php if($item['url']): ?> href="<?php echo e($item['url']); ?>" <?php endif; ?> class="d-flex align-items-center gap-2 rounded-3 p-2 h-100 text-reset <?php echo e($item['value'] > 0 ? 'bg-light-' . $item['color'] : 'bg-light'); ?>">
                            <i class="ti ti-<?php echo e($item['icon']); ?> f-20 text-<?php echo e($item['color']); ?> flex-shrink-0"></i>
                            <div style="min-width:0">
                                <div class="fw-bold fs-5 lh-1"><?php echo e($item['value']); ?></div>
                                <small class="text-muted d-block"><?php echo e($item['label']); ?></small>
                            </div>
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    
    <?php if(!empty($extra)): ?>
        <?php echo $__env->make('dashboard._section', ['title' => __('Activity & Schedule')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._owner_extra', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>

    
    <div class="row">
        <div class="col-xl-7 d-flex">
            <div class="card w-100">
                <div class="card-header">
                    <h5>
                        <?php echo e(__('Due / Overdue List')); ?>

                    </h5>
                </div>
                <div class="card-body pt-0">

                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Invoice')); ?></th>
                                    <th><?php echo e(__('Property & Unit')); ?></th>
                                    <th><?php echo e(__('Due Date')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $result['invoiceDue']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show invoice')): ?>
                                                <a href="<?php echo e(route('invoice.show', \Crypt::encrypt($invoice->id))); ?>"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="<?php echo e(__('View')); ?>">
                                                    <?php echo e(invoicePrefix() . $invoice->invoice_id); ?>

                                                </a>
                                            <?php else: ?>
                                                <?php echo e(invoicePrefix() . $invoice->invoice_id); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">
                                                <?php echo e(optional($invoice->properties)->name); ?>

                                            </div>
                                            <div class="text-muted small">
                                                <?php echo e(optional($invoice->units)->name); ?>

                                            </div>
                                        </td>
                                        <td><?php echo e(dateFormat($invoice->end_date)); ?> </td>
                                        <td><?php echo e(priceFormat($invoice->getInvoiceSubTotalAmount())); ?></td>
                                        <td>
                                            <?php if($invoice->status == 'unpaid'): ?>
                                                <span
                                                    class="badge bg-light-danger"><?php echo e(\App\Models\Invoice::status()[$invoice->status]); ?></span>
                                            <?php elseif($invoice->status == 'paid'): ?>
                                                <span
                                                    class="badge bg-light-success"><?php echo e(\App\Models\Invoice::status()[$invoice->status]); ?></span>
                                            <?php elseif($invoice->status == 'partial_paid'): ?>
                                                <span
                                                    class="badge bg-light-warning"><?php echo e(\App\Models\Invoice::status()[$invoice->status]); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 d-flex">

            <div class="card w-100">
                <div class="card-header">
                    <h5><?php echo e(__('Maintenance Request')); ?></h5>
                </div>

                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">

                        <?php $__empty_1 = true; $__currentLoopData = $maintenanceRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">

                                    <div style="width: 35%;">
                                        <div class="fw-semibold">
                                            <?php echo e(optional($request->properties)->name); ?>

                                        </div>
                                        <div class="text-muted small">
                                            <?php echo e(optional($request->units)->name); ?>

                                        </div>
                                    </div>

                                    <div style="width: 45%;" class="text-muted">
                                        <?php echo e(optional($request->types)->title); ?>

                                    </div>

                                    <div style="width: 20%;" class="text-end text-muted small">
                                        <?php echo e(dateFormat($request->request_date)); ?>

                                    </div>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create maintenance request')): ?>
                                        <div style="width: 20%;" class="text-end text-muted small">
                                            <a href="#" class="customModal" data-size="md"
                                                data-url="<?php echo e(route('maintenance.assign', ['request_id' => $request->id])); ?>"
                                                data-title="<?php echo e(__('Assign Maintainer')); ?>">
                                                <i class="ti ti-circle-plus text-secondary fs-2 text-center"
                                                    data-bs-original-title="<?php echo e(__('Assign Maintainer')); ?>"
                                                    data-bs-toggle="tooltip"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li class="list-group-item text-center text-muted">
                                <?php echo e(__('No Maintenance Requests Found')); ?>

                            </li>
                        <?php endif; ?>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    
    <?php echo $__env->make('dashboard._section', ['title' => __('Finance'), 'sub' => __('This month')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <?php echo $__env->make('dashboard._stat', ['icon' => 'trending-up', 'color' => 'success', 'label' => __('Revenue'), 'value' => $cur . '<span class="count">' . $result['totalIncome'] . '</span>'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'trending-down', 'color' => 'danger', 'label' => __('Expenses'), 'value' => $cur . '<span class="count">' . $result['totalExpense'] . '</span>', 'sub' => __('Net') . ' ' . priceFormat($net), 'url' => Gate::check('manage expense') ? route('expense.index') : null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'credit-card-off', 'color' => 'warning', 'label' => __('Pending Payments'), 'value' => $cur . '<span class="count">' . $result['totalPendingPayment'] . '</span>', 'sub' => __('all open invoices'), 'url' => Gate::check('manage invoice') ? route('invoice.index') : null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage deposit')): ?>
            <?php echo $__env->make('dashboard._stat', ['icon' => 'building-bank', 'color' => 'info', 'label' => __('Deposits Held'), 'value' => priceFormat($result['depositsHeld'])], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php endif; ?>
    </div>
    <div class="row">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <h5 class="mb-0"><?php echo e(__('Revenue Analytics')); ?></h5>
                        <select id="revenueFilter" class="form-select rounded-3 form-select-sm w-auto">
                            <option value="monthly" selected>Month</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div id="customRange" class="d-none mt-2">
                        <div class="d-flex align-items-end gap-2 flex-wrap">
                            <div>
                                <b><?php echo e(Form::label('date range', __('Date'), ['class' => 'form-label'])); ?></b>
                                <?php echo e(Form::text('date', request('date'), [
                                    'class' => 'form-control date',
                                    'placeholder' => __('Select date range'),
                                    'id' => 'demo',
                                    'autocomplete' => 'off',
                                    'readonly' => 'readonly',
                                ])); ?>

                            </div>
                            <div>
                                <button id="applyCustom" class="btn btn-primary"><?php echo e(__('Apply')); ?></button>
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
                        <h5 class="mb-0"><?php echo e(__('Occupied vs Vacant')); ?></h5>
                        <div class="dropdown">
                            <a class="avtar avtar-s btn-link-secondary dropdown-toggle arrow-none" href="#"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="ti ti-dots-vertical f-18"></i>
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" id="propertyDropdown">
                                <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a class="dropdown-item property-filter <?php echo e($loop->first ? 'active fw-bold' : ''); ?>"
                                        href="#" data-id="<?php echo e($id); ?>">
                                        <?php echo e($name); ?>

                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <small class="text-muted"><?php echo e(__('Property')); ?></small>
                    <h6 id="currentPropertyName">--</h6>

                    <div id="total-income-graph"></div>

                    <div class="mb-2 mt-3 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-circle text-danger f-12"></i>
                            <?php echo e(__('Occupied')); ?>

                        </h6>
                        <p class="mb-0 text-muted" id="occupiedCount">0</p>
                    </div>

                    <div class="mb-2 d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-circle text-success f-12"></i>
                            <?php echo e(__('Vacant')); ?>

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
                            <h5 class="mb-1"><?php echo e(__('Analysis Report')); ?></h5>
                            <p class="text-muted mb-2"><?php echo e(__('Income and Expense Overview')); ?></p>
                        </div>

                    </div>
                    <div id="incomeExpense"></div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/index.blade.php ENDPATH**/ ?>