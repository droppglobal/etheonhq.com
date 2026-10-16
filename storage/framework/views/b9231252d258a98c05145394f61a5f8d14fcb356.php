
<div class="card">
    <div class="card-body d-flex flex-wrap gap-2 py-3">
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage invoice')): ?><a href="<?php echo e(route('invoice.index')); ?>" class="btn btn-sm btn-secondary"><i class="ti ti-receipt"></i> <?php echo e(__('Pay Rent')); ?></a><?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create maintenance request')): ?><a href="<?php echo e(route('maintenance-request.index')); ?>" class="btn btn-sm btn-light-secondary"><i class="ti ti-tool"></i> <?php echo e(__('Report a Problem')); ?></a><?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('book facility')): ?><a href="<?php echo e(route('facility.index')); ?>" class="btn btn-sm btn-light-secondary"><i class="ti ti-calendar-event"></i> <?php echo e(__('Book a Facility')); ?></a><?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create visitor pass')): ?><a href="<?php echo e(route('visitor.index')); ?>" class="btn btn-sm btn-light-secondary"><i class="ti ti-user-check"></i> <?php echo e(__('Visitor Pass')); ?></a><?php endif; ?>
        <a href="<?php echo e(route('assistant.index')); ?>" class="btn btn-sm btn-light-secondary"><i class="ti ti-messages"></i> <?php echo e(__('Ask the Assistant')); ?></a>
    </div>
</div>

<?php if($extra['inspectionToSign']): ?>
    <div class="alert alert-light-warning d-flex justify-content-between align-items-center">
        <span><i class="ti ti-clipboard-check"></i> <?php echo e(__('Your :type inspection is ready for you to review and sign.', ['type' => mb_strtolower(\App\Models\Inspection::types()[$extra['inspectionToSign']->type] ?? '')])); ?></span>
        <a href="<?php echo e(route('inspection.show', $extra['inspectionToSign']->id)); ?>" class="btn btn-sm btn-warning"><?php echo e(__('Review')); ?></a>
    </div>
<?php endif; ?>

<div class="row">
    <?php $nd = $extra['nextDue']; ?>
    <?php echo $__env->make('dashboard._stat', ['icon' => 'calendar-event', 'color' => $nd && $nd['overdue'] ? 'danger' : 'primary', 'label' => __('Next Payment'),
        'value' => $nd ? priceFormat($nd['amount']) : __('All paid'),
        'sub' => $nd ? (($nd['overdue'] ? __('Overdue since') : __('Due')) . ' ' . dateFormat($nd['date'])) : __('Nothing to pay right now'), 'url' => route('invoice.index')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('dashboard._stat', ['icon' => 'calendar-time', 'color' => 'info', 'label' => __('Lease'),
        'value' => $extra['leaseDaysLeft'] !== null ? ($extra['leaseDaysLeft'] == 1 ? __('1 day left') : __(':count days left', ['count' => $extra['leaseDaysLeft']])) : '-',
        'sub' => $extra['leaseProgress'] !== null ? '<div class="progress mt-1" style="height:5px"><div class="progress-bar bg-info" style="width:' . $extra['leaseProgress'] . '%"></div></div>' : null], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('dashboard._stat', ['icon' => 'credit-card', 'color' => $extra['autopay'] ? 'success' : 'secondary', 'label' => __('Autopay'),
        'value' => $extra['autopay'] ? __('On') : __('Off'), 'sub' => $extra['autopay'] ?: __('Pay automatically on each due date'), 'url' => route('autopay.index')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('dashboard._stat', ['icon' => 'building-bank', 'color' => 'warning', 'label' => __('Deposit Held'), 'value' => priceFormat($extra['depositHeld'])], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>


<div class="row">
    <div class="col-xl-5 d-flex">
        <div class="card w-100">
            <div class="card-header"><h5 class="mb-0"><?php echo e(__('My Home')); ?></h5></div>
            <div class="card-body pt-2">
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Property')); ?></span><span class="fw-semibold text-end"><?php echo e(optional($tenant->properties)->name ?: '-'); ?></span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Unit')); ?></span><span class="fw-semibold text-end"><?php echo e(optional($tenant->units)->name ?: '-'); ?></span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Monthly rent')); ?></span>
                    <span class="fw-semibold text-end"><?php echo e(!empty($result['unit']) ? priceFormat($result['unit']->rent) : '-'); ?>

                        <?php if(!empty($extra['rentChange'])): ?><br><small class="text-info fw-normal"><?php echo e(__(':new from :date', ['new' => priceFormat($extra['rentChange']->new_rent), 'date' => dateFormat($extra['rentChange']->effective_date)])); ?></small><?php endif; ?>
                    </span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Agreement')); ?></span>
                    <span class="fw-semibold text-end"><?php echo e(!empty($result['agreement']) ? dateFormat($result['agreement']->agreement_start_date) . ' - ' . dateFormat($result['agreement']->agreement_end_date) : '-'); ?></span></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Parking')); ?></span>
                    <span class="fw-semibold text-end">
                        <?php $__empty_1 = true; $__currentLoopData = $extra['parking']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php echo e(optional($p->slot)->code); ?><?php echo e($p->vehicle_number ? ' (' . $p->vehicle_number . ')' : ''); ?><?php echo e(!$loop->last ? ', ' : ''); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php echo e(__('No slot')); ?><?php endif; ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between py-2"><span class="text-muted"><?php echo e(__('Polls waiting for your vote')); ?></span>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('vote poll')): ?><a href="<?php echo e(route('poll.index')); ?>" class="fw-semibold"><?php echo e($extra['openPolls']); ?></a><?php else: ?><span>-</span><?php endif; ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-7 d-flex">
        <div class="card w-100">
            <div class="card-header"><h5 class="mb-0"><?php echo e(__('Coming Up')); ?></h5></div>
            <div class="card-body pt-1">
                <?php echo $__env->make('dashboard._timeline', ['items' => $extra['upcoming'], 'empty' => __('No upcoming bookings or visitors.')], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-xl-7 d-flex">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo e(__('Due / Overdue Invoices')); ?></h5>
                <a href="<?php echo e(route('invoice.index')); ?>" class="small"><?php echo e($extra['openInvoices']->count()); ?> <?php echo e(__('unpaid')); ?> · <?php echo e(priceFormat($extra['amountDue'])); ?></a>
            </div>
            <div class="card-body pt-0">
                <?php if($result['overdueInvoices']->count()): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Invoice')); ?></th>
                                    <th><?php echo e(__('Due Date')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $result['overdueInvoices']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show invoice')): ?>
                                                <a href="<?php echo e(route('invoice.show', \Crypt::encrypt($invoice->id))); ?>"
                                                    data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('View')); ?>">
                                                    <?php echo e(invoicePrefix() . $invoice->invoice_id); ?>

                                                </a>
                                            <?php else: ?>
                                                <?php echo e(invoicePrefix() . $invoice->invoice_id); ?>

                                            <?php endif; ?>
                                            <small class="text-muted d-block"><?php echo e(date('F Y', strtotime($invoice->invoice_month))); ?></small>
                                        </td>
                                        <td><?php echo e(dateFormat($invoice->end_date)); ?></td>
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
                <?php else: ?>
                    <p class="text-muted text-center py-4 mb-0"><?php echo e(__('Nothing is due right now.')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xl-5 d-flex">
        <div class="card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo e(__('My Maintenance Requests')); ?></h5>
                <a href="<?php echo e(route('maintenance-request.index')); ?>" class="small"><?php echo e($extra['openRequests']); ?> <?php echo e(__('open')); ?></a>
            </div>
            <div class="card-body pt-1">
                <?php $__empty_1 = true; $__currentLoopData = $result['maintenanceList']->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="d-flex justify-content-between align-items-center py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                        <div style="min-width:0">
                            <div class="fw-semibold text-truncate"><?php echo e(optional($maintenance->types)->title ?: '-'); ?></div>
                            <small class="text-muted"><?php echo e(dateFormat($maintenance->request_date)); ?></small>
                        </div>
                        <?php if($maintenance->status == 'pending'): ?>
                            <span class="badge bg-light-warning flex-shrink-0"><?php echo e(__('Pending')); ?></span>
                        <?php elseif($maintenance->status == 'in_progress'): ?>
                            <span class="badge bg-light-info flex-shrink-0"><?php echo e(__('In Progress')); ?></span>
                        <?php else: ?>
                            <span class="badge bg-light-success flex-shrink-0"><?php echo e(__('Completed')); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-muted text-center py-4 mb-0"><?php echo e(__('No repair requests yet.')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/_tenant_extra.blade.php ENDPATH**/ ?>