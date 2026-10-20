<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Dashboard')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item" aria-current="page"><?php echo e(__('Dashboard')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('script-page'); ?>
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
                name: "<?php echo e(__('Total Users')); ?>",
                data: <?php echo json_encode($result['organizationByMonth']['data']); ?>

            },
            {
                name: "<?php echo e(__('Total Payment')); ?>",
                data: <?php echo json_encode($result['paymentByMonth']['data']); ?>

            }
        ],
        xaxis: {
            categories: <?php echo json_encode($result['organizationByMonth']['label']); ?>,
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <?php if(!empty($extra['serverIssues'])): ?>
        <div class="alert alert-light-warning">
            <div class="fw-semibold mb-1"><i class="ti ti-alert-triangle"></i> <?php echo e(__('Server check')); ?></div>
            <ul class="mb-0 ps-3">
                <?php $__currentLoopData = $extra['serverIssues']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($issue); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if($extra['pendingTransfers'] > 0): ?>
        <div class="alert alert-light-danger d-flex justify-content-between align-items-center">
            <span><i class="ti ti-building-bank"></i> <?php echo e($extra['pendingTransfers'] == 1 ? __('1 bank transfer is waiting for your approval.') : __(':count bank transfers are waiting for your approval.', ['count' => $extra['pendingTransfers']])); ?></span>
            <a href="<?php echo e(route('subscription.transaction')); ?>" class="btn btn-sm btn-danger"><?php echo e(__('Review')); ?></a>
        </div>
    <?php endif; ?>

    <?php echo $__env->make('dashboard._section', ['title' => __('Subscriptions & Revenue')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <?php echo $__env->make('dashboard._stat', ['icon' => 'users', 'color' => 'secondary', 'label' => __('Owners'), 'value' => $result['totalOrganization'], 'sub' => $extra['newOwnersThisMonth'] . ' ' . __('new this month'), 'url' => route('users.index')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'circle-check', 'color' => 'success', 'label' => __('Active Subscriptions'), 'value' => $extra['activeOwners'], 'sub' => $extra['onTrial'] . ' ' . __('on free trial'), 'url' => route('users.index')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'report-money', 'color' => 'primary', 'label' => __('Revenue This Month'), 'value' => priceFormat($extra['revenueThisMonth']), 'sub' => __('All time') . ' ' . subscriptionPaymentSettings()['CURRENCY_SYMBOL'] . $result['totalIncome']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'calendar-minus', 'color' => 'warning', 'label' => __('Expiring in 7 Days'), 'value' => $extra['expiringSoon']->count(), 'sub' => $extra['expiredOwners'] . ' ' . __('already expired')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    <?php echo $__env->make('dashboard._section', ['title' => __('Platform Usage')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="row">
        <?php echo $__env->make('dashboard._stat', ['icon' => 'building', 'color' => 'info', 'label' => __('Properties'), 'value' => $extra['platformProperties']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'layout-grid', 'color' => 'secondary', 'label' => __('Units'), 'value' => $extra['platformUnits']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'users', 'color' => 'success', 'label' => __('Active Tenants'), 'value' => $extra['platformTenants']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('dashboard._stat', ['icon' => 'package', 'color' => 'warning', 'label' => __('Packages'), 'value' => $result['totalSubscription'], 'sub' => $result['totalTransaction'] . ' ' . __('transactions'), 'url' => route('subscriptions.index')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <h5 class="mb-1"><?php echo e(__('Analysis Report')); ?></h5>
                            <p class="text-muted mb-2"><?php echo e(__('Users and Payments Overview')); ?></p>
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
                <div class="card-header"><h5 class="mb-0"><?php echo e(__('Owners per Plan')); ?></h5></div>
                <div class="card-body">
                    <?php $planTotal = max(1, $extra['ownersPerPlan']->sum()); ?>
                    <?php $__empty_1 = true; $__currentLoopData = $extra['ownersPerPlan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small"><span class="fw-semibold"><?php echo e($plan); ?></span><span><?php echo e($count); ?></span></div>
                            <div class="progress" style="height:8px"><div class="progress-bar bg-secondary" style="width: <?php echo e(round($count / $planTotal * 100)); ?>%"></div></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted mb-0"><?php echo e(__('No owners yet.')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0"><?php echo e(__('Newest Owners')); ?></h5></div>
                <div class="card-body pt-2">
                    <?php $__empty_1 = true; $__currentLoopData = $extra['recentOwners']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $owner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div style="min-width:0"><div class="fw-semibold text-truncate"><?php echo e($owner->name); ?></div><small class="text-muted"><?php echo e($owner->email); ?></small></div>
                            <div class="text-end flex-shrink-0 ms-2"><span class="badge bg-light-secondary"><?php echo e($extra['plans'][$owner->subscription] ?? '-'); ?></span><br><small class="text-muted"><?php echo e(dateFormat($owner->created_at)); ?></small></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted mb-0"><?php echo e(__('No owners yet.')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-12 d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0"><?php echo e(__('Subscriptions Expiring Soon')); ?></h5></div>
                <div class="card-body pt-2">
                    <?php $__empty_1 = true; $__currentLoopData = $extra['expiringSoon']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $owner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div style="min-width:0"><div class="fw-semibold text-truncate"><?php echo e($owner->name); ?></div><small class="text-muted"><?php echo e($extra['plans'][$owner->subscription] ?? '-'); ?></small></div>
                            <span class="badge bg-light-warning flex-shrink-0 ms-2"><?php echo e(dateFormat($owner->subscription_expire_date)); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted mb-0"><?php echo e(__('No subscriptions expire in the next 7 days.')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/super_admin.blade.php ENDPATH**/ ?>