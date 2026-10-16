<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Email Notification Template')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Email Notification Template')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5><?php echo e(__('Email Notification Template')); ?></h5>
                        </div>

                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Module')); ?></th>
                                    <th><?php echo e(__('Subject')); ?></th>
                                    <th><?php echo e(__('Email Enable')); ?></th>
                                     <th><?php echo e(__('SMS Enable')); ?></th>
                                     <th><?php echo e(__('Whatsapp Enable')); ?></th>
                                    <?php if(Gate::check('edit notification') || Gate::check('delete notification')): ?>
                                        <th><?php echo e(__('Action')); ?></th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($item->name); ?> </td>
                                        <td><?php echo e($item->subject); ?></td>
                                        <td>
                                            <?php if($channels['email'] && $item->enabled_email == 1): ?>
                                                <span class="d-inline badge text-bg-success"><?php echo e(__('Enable')); ?></span>
                                            <?php else: ?>
                                                <span class="d-inline badge text-bg-danger"><?php echo e(__('Disable')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($channels['whatsapp'] && $item->enabled_whatsapp == 1): ?>
                                                <span class="d-inline badge text-bg-success"><?php echo e(__('Enable')); ?></span>
                                            <?php else: ?>
                                                <span class="d-inline badge text-bg-danger"><?php echo e(__('Disable')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($channels['sms'] && $item->enabled_sms == 1): ?>
                                                <span class="d-inline badge text-bg-success"><?php echo e(__('Enable')); ?></span>
                                            <?php else: ?>
                                                <span class="d-inline badge text-bg-danger"><?php echo e(__('Disable')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if(Gate::check('edit notification')): ?>
                                            <td>
                                                <div class="cart-action">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit notification')): ?>
                                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                            data-bs-toggle="tooltip" data-size="lg"
                                                            data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                                            data-url="<?php echo e(route('notification.edit', encrypt($item->id))); ?>"
                                                            data-title="<?php echo e(__('Edit Notification')); ?>"> <i
                                                                data-feather="edit"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/notification/index.blade.php ENDPATH**/ ?>