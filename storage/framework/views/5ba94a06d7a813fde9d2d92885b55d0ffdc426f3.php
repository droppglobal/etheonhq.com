<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Landlords')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"><?php echo e(__('Landlords')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><?php echo e(__('Landlords')); ?></h5>
                        <small class="text-muted"><?php echo e(__('Property owners whose properties you manage. They log in to see their statements and payouts.')); ?></small>
                    </div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create landlord')): ?>
                        <a href="#" class="btn btn-secondary customModal" data-size="lg" data-url="<?php echo e(route('landlord.create')); ?>"
                            data-title="<?php echo e(__('Add Landlord')); ?>"><i class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Add Landlord')); ?></a>
                    <?php endif; ?>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Name')); ?></th>
                                    <th><?php echo e(__('Contact')); ?></th>
                                    <th><?php echo e(__('Properties')); ?></th>
                                    <th><?php echo e(__('Management Fee')); ?></th>
                                    <th><?php echo e(__('Balance Owed')); ?></th>
                                    <th class="text-right"><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $landlords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $landlord): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('landlord.show', $landlord->id)); ?>" class="fw-semibold"><?php echo e($landlord->name); ?></a></td>
                                        <td><?php echo e($landlord->email); ?><br><small class="text-muted"><?php echo e($landlord->phone_number); ?></small></td>
                                        <td><?php echo e($propertyCounts[$landlord->id] ?? 0); ?></td>
                                        <td><?php echo e((float) optional($profiles->get($landlord->id))->management_fee_percent); ?>%</td>
                                        <td class="<?php echo e(($balances[$landlord->id] ?? 0) < 0 ? 'text-danger' : ''); ?>"><?php echo e(priceFormat($balances[$landlord->id] ?? 0)); ?></td>
                                        <td class="text-right">
                                            <div class="cart-action">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['landlord.destroy', $landlord->id]]); ?>

                                                <a class="avtar avtar-xs btn-link-warning text-warning" href="<?php echo e(route('landlord.show', $landlord->id)); ?>"><i data-feather="eye"></i></a>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit landlord')): ?>
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" href="#" data-size="lg"
                                                        data-url="<?php echo e(route('landlord.edit', $landlord->id)); ?>" data-title="<?php echo e(__('Edit Landlord')); ?>"><i data-feather="edit"></i></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete landlord')): ?>
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog" href="#"><i data-feather="trash-2"></i></a>
                                                <?php endif; ?>
                                                <?php echo Form::close(); ?>

                                            </div>
                                        </td>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/landlord/index.blade.php ENDPATH**/ ?>