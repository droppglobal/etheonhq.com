<?php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
?>
<?php $__env->startSection('page-title'); ?>
    <?php if(\Auth::user()->type == 'super admin'): ?>
        <?php echo e(__('Customer')); ?>

    <?php else: ?>
        <?php echo e(__('User')); ?>

    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item">
        <a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a>
    </li>
    <li class="breadcrumb-item" aria-current="page">
        <?php if(\Auth::user()->type == 'super admin'): ?>
            <?php echo e(__('Customers')); ?>

        <?php else: ?>
            <?php echo e(__('Users')); ?>

        <?php endif; ?>
    </li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>
                                <?php if(\Auth::user()->type == 'super admin'): ?>
                                    <?php echo e(__('Customer List')); ?>

                                <?php else: ?>
                                    <?php echo e(__('User List')); ?>

                                <?php endif; ?>
                            </h5>
                        </div>
                        <?php if(Gate::check('create user')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="<?php echo e(route('users.create')); ?>" data-title="<?php echo e(__('Create User')); ?>"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Create User')); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('User')); ?></th>
                                    <th><?php echo e(__('Email')); ?></th>
                                    <th><?php echo e(__('Phone Number')); ?></th>
                                    <?php if(\Auth::user()->type == 'super admin'): ?>
                                        <th><?php echo e(__('Active Package')); ?></th>
                                        <th><?php echo e(__('Package Due Date')); ?></th>
                                    <?php else: ?>
                                        <th><?php echo e(__('Assign Role')); ?></th>
                                    <?php endif; ?>
                                    <th><?php echo e(__('Action')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="table-user">
                                            <img src="<?php echo e(!empty($user->profile) ? fetch_file($user->profile, 'upload/profile/') : $profile); ?>"
                                                alt="" class="mr-2 avatar-sm rounded-circle user-avatar">
                                            <a href="#"
                                                class="text-body font-weight-semibold"><?php echo e($user->name); ?></a>
                                        </td>
                                        <td><?php echo e($user->email); ?> </td>
                                        <td><?php echo e(!empty($user->phone_number) ? $user->phone_number : '-'); ?> </td>
                                        <?php if(\Auth::user()->type == 'super admin'): ?>
                                            <td><?php echo e(!empty($user->subscriptions) ? $user->subscriptions->title : '-'); ?>

                                            </td>
                                            <td><?php echo e(!empty($user->subscription_expire_date) ? dateFormat($user->subscription_expire_date) : __('Unlimited')); ?>

                                            </td>
                                        <?php else: ?>
                                            <td><?php echo e(ucfirst($user->type)); ?> </td>
                                        <?php endif; ?>
                                        <td>
                                            <div class="cart-action">
                                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id]]); ?>


                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show user')): ?>
                                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Show')); ?>"
                                                        href="<?php echo e(route('users.show', $user->id)); ?>"
                                                        data-title="<?php echo e(__('Edit User')); ?>"> <i data-feather="eye"></i></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit user')): ?>
                                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                                        data-bs-toggle="tooltip" data-size="lg"
                                                        data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                                        data-url="<?php echo e(route('users.edit', $user->id)); ?>"
                                                        data-title="<?php echo e(__('Edit User')); ?>"> <i data-feather="edit"></i></a>
                                                <?php endif; ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete user')): ?>
                                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Detete')); ?>"
                                                        href="#"> <i data-feather="trash-2"></i></a>
                                                <?php endif; ?>

                                                <?php if(Auth::user()->canImpersonate()): ?>
                                                    <a class="avtar avtar-xs btn-link-info text-info"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="<?php echo e(__('Continue as Customer')); ?>"
                                                        href="<?php echo e(route('impersonate', $user->id)); ?>"> <i
                                                            data-feather="log-in"></i></a>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/user/index.blade.php ENDPATH**/ ?>