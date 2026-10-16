<?php if($extra['upcoming']->count() || $extra['recentPayments']->count() || isset($extra['openByPriority']) || !empty($extra['occupancy'])): ?>
    <div class="row">
        <div class="col-xl-4 col-md-6 d-flex">
            <div class="card w-100">
                <div class="card-header"><h5 class="mb-0"><?php echo e(__('Next 7 Days')); ?></h5></div>
                <div class="card-body pt-1">
                    <?php echo $__env->make('dashboard._timeline', ['items' => $extra['upcoming'], 'empty' => __('No viewings, bookings, preventive tasks or lease ends in the next 7 days.')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>
        <?php if($extra['recentPayments']->count()): ?>
            <div class="col-xl-4 col-md-6 d-flex">
                <div class="card w-100">
                    <div class="card-header"><h5 class="mb-0"><?php echo e(__('Recent Payments')); ?></h5></div>
                    <div class="card-body pt-1">
                        <?php $__currentLoopData = $extra['recentPayments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $payer = optional(optional(\App\Models\Tenant::find($payment->tenant_id))->user)->name; ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div style="min-width:0">
                                    <div class="fw-semibold text-truncate"><?php echo e($payer ?: invoicePrefix() . $payment->invoice_number); ?></div>
                                    <small class="text-muted"><?php echo e(invoicePrefix() . $payment->invoice_number); ?> · <?php echo e($payment->payment_type); ?> · <?php echo e(dateFormat($payment->payment_date)); ?></small>
                                </div>
                                <span class="fw-semibold <?php echo e($payment->amount < 0 ? 'text-danger' : 'text-success'); ?> flex-shrink-0 ms-2"><?php echo e($payment->amount < 0 ? '-' . priceFormat(abs($payment->amount)) : priceFormat($payment->amount)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-xl-4 col-md-12">
            <?php if(isset($extra['openByPriority'])): ?>
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><?php echo e(__('Open Repairs by Priority')); ?></h5></div>
                    <div class="card-body">
                        <div class="row g-2 text-center">
                            <?php $__currentLoopData = ['emergency' => 'danger', 'high' => 'warning', 'medium' => 'info', 'low' => 'secondary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-6">
                                    <a href="<?php echo e(route('maintenance-request.index')); ?>" class="d-block rounded p-2 bg-light-<?php echo e($color); ?> text-reset">
                                        <div class="fs-4 fw-bold"><?php echo e($extra['openByPriority'][$p] ?? 0); ?></div>
                                        <small><?php echo e(\App\Support\MaintenanceTriage::priorities()[$p]); ?></small>
                                    </a>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="small text-muted mt-3">
                            <?php if(($extra['openByPriority']['unrated'] ?? 0) > 0): ?><?php echo e($extra['openByPriority']['unrated']); ?> <?php echo e(__('older requests without priority')); ?> · <?php endif; ?>
                            <?php echo e($extra['unassigned']); ?> <?php echo e(__('without maintainer')); ?>

                            <?php if($extra['oldestOpenDays'] !== null): ?> · <?php echo e(__('oldest open for :d days', ['d' => $extra['oldestOpenDays']])); ?><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if(!empty($extra['occupancy']) && $extra['occupancy']->count()): ?>
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><?php echo e(__('Occupancy by Property')); ?></h5></div>
                    <div class="card-body pt-2">
                        <?php $__currentLoopData = $extra['occupancy']->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $pct = round($row['occupied'] / $row['units'] * 100); ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small"><span class="text-truncate me-2"><?php echo e($row['name']); ?></span><span class="flex-shrink-0"><?php echo e($row['occupied']); ?>/<?php echo e($row['units']); ?> · <?php echo e($pct); ?>%</span></div>
                                <div class="progress" style="height:6px"><div class="progress-bar <?php echo e($pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger')); ?>" style="width: <?php echo e($pct); ?>%"></div></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/_owner_extra.blade.php ENDPATH**/ ?>