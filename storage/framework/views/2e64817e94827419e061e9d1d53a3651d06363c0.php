<?php echo $__env->make('dashboard._section', ['title' => __('My Jobs')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="row">
    <?php echo $__env->make('dashboard._stat', ['icon' => 'alert-triangle', 'color' => 'danger', 'label' => __('Urgent Open Jobs'), 'value' => $extra['urgentOpen'] ?? 0, 'sub' => __('Emergency and high priority'), 'url' => route('maintenance-request.index')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('dashboard._stat', ['icon' => 'clock', 'color' => 'warning', 'label' => __('Pending'), 'value' => $result['pendingRequest'] ?? 0, 'sub' => ($result['todayRequest'] ?? 0) . ' ' . __('new today') . ' · ' . ($result['totalRequest'] ?? 0) . ' ' . __('all time'), 'url' => route('maintenance-request.pending')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('dashboard._stat', ['icon' => 'loader', 'color' => 'info', 'label' => __('In Progress'), 'value' => $extra['inProgress'] ?? 0, 'url' => route('maintenance-request.inprogress')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('dashboard._stat', ['icon' => 'circle-check', 'color' => 'success', 'label' => __('Completed This Month'), 'value' => $extra['completedThisMonth'] ?? 0, 'sub' => (float) ($extra['hoursThisMonth'] ?? 0) . ' ' . __('hours logged')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<div class="row">
    <div class="col-xl-7 d-flex">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><h5 class="mb-0"><?php echo e(__('My Work Queue')); ?></h5><small class="text-muted"><?php echo e(__('Most urgent first')); ?></small></div>
                <a href="<?php echo e(route('maintenance-request.index')); ?>" class="small"><?php echo e(__('View all')); ?> <i class="ti ti-chevron-right"></i></a>
            </div>
            <div class="card-body pt-1">
                <?php $__empty_1 = true; $__currentLoopData = ($extra['queue'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('maintenance-request.show', \Crypt::encrypt($job->id))); ?>" class="d-flex justify-content-between align-items-center py-2 border-bottom text-reset">
                        <div style="min-width:0">
                            <div class="fw-semibold text-truncate"><?php echo e(optional($job->types)->title ?? __('Request')); ?> · <?php echo e(optional($job->units)->name); ?></div>
                            <small class="text-muted"><?php echo e(optional($job->properties)->name); ?> · <?php echo e(__('since')); ?> <?php echo e(dateFormat($job->request_date)); ?> · <?php echo e(\App\Models\MaintenanceRequest::status()[$job->status] ?? $job->status); ?><?php if($job->preferredVisitText()): ?> · <i class="ti ti-clock"></i> <?php echo e($job->preferredVisitText()); ?><?php endif; ?></small>
                        </div>
                        <?php if($job->ai_priority): ?>
                            <span class="badge bg-light-<?php echo e(\App\Support\MaintenanceTriage::color($job->ai_priority)); ?> flex-shrink-0 ms-2"><?php echo e(\App\Support\MaintenanceTriage::priorities()[$job->ai_priority] ?? ''); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0 py-2"><?php echo e(__('No open jobs. Nice work!')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5">
        <?php $assigned = !empty($maintainer) ? array_filter($maintainer->properties()) : []; ?>
        <?php if(count($assigned)): ?>
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><?php echo e(__('Assigned Properties')); ?></h5></div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <?php $__currentLoopData = $assigned; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge rounded-pill px-3 py-2 fw-medium bg-light-primary"><i class="ti ti-building me-1"></i><?php echo e($property->name); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><?php echo e(__('Preventive Tasks (Next 14 Days)')); ?></h5></div>
            <div class="card-body pt-1">
                <?php $__empty_1 = true; $__currentLoopData = ($extra['upcomingPreventive'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div style="min-width:0"><div class="fw-semibold text-truncate"><?php echo e($task->title); ?></div><small class="text-muted"><?php echo e(optional($task->properties)->name); ?><?php echo e(!empty($task->units) ? ' / ' . $task->units->name : ''); ?></small></div>
                        <span class="badge bg-light-secondary flex-shrink-0 ms-2"><?php echo e(dateFormat($task->next_date)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0 py-2"><?php echo e(__('No preventive tasks scheduled for you.')); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo e(__('Tenant Feedback')); ?></h5>
                <span class="small text-muted"><?php if(($extra['avgRating'] ?? null) !== null): ?><span class="fw-semibold text-body"><?php echo e($extra['avgRating'] ?? ''); ?></span> <span class="text-warning">★</span> · <?php endif; ?><?php echo e(($extra['ratingCount'] ?? 0) == 1 ? __('1 rating') : __(':count ratings', ['count' => $extra['ratingCount'] ?? 0])); ?></span>
            </div>
            <div class="card-body pt-1">
                <?php $__empty_1 = true; $__currentLoopData = ($extra['recentFeedback'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="py-2 border-bottom">
                        <span class="text-warning"><?php echo e(str_repeat('★', (int) $job->tenant_rating)); ?></span><span class="text-muted"><?php echo e(str_repeat('★', 5 - (int) $job->tenant_rating)); ?></span>
                        <small class="text-muted ms-1"><?php echo e(optional($job->types)->title); ?> · <?php echo e(dateFormat($job->updated_at)); ?></small>
                        <?php if($job->tenant_feedback): ?><div class="small">"<?php echo e($job->tenant_feedback); ?>"</div><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted mb-0 py-2"><?php echo e(__('No ratings yet.')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/_maintainer_extra.blade.php ENDPATH**/ ?>