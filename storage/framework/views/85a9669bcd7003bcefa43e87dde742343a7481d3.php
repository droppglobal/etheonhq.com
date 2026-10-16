<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th><?php echo e(__('Agreement')); ?></th>
                <th><?php echo e(__('Property')); ?></th>
                <th><?php echo e(__('Unit')); ?></th>
                <th><?php echo e(__('Tenant')); ?></th>
                <th><?php echo e(__('Date')); ?></th>
                <th><?php echo e(__('Status')); ?></th>
                <?php if(Gate::check('edit agreement') || Gate::check('delete agreement') || Gate::check('show agreement')): ?>
                    <th class="text-right"><?php echo e(__('Action')); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $agreements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agreement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e(agreementPrefix() . $agreement->agreement_id); ?> </td>
                    <td><?php echo e(!empty($agreement->properties) ? $agreement->properties->name : '-'); ?> </td>
                    <td><?php echo e(!empty($agreement->units) ? $agreement->units->name : '-'); ?> </td>
                    <td><?php echo e(!empty($agreement->tenants->user) ? $agreement->tenants->user->name : '-'); ?> </td>
                    <td><?php echo e(dateFormat($agreement->date)); ?> </td>
                    <td>
                        <?php if($agreement->status == 'Draft'): ?>
                            <span
                                class="badge bg-light-dark"><?php echo e(\App\Models\agreement::status()[$agreement->status]); ?></span>
                        <?php elseif($agreement->status == 'Pending'): ?>
                            <span
                                class="badge bg-light-warning"><?php echo e(\App\Models\agreement::status()[$agreement->status]); ?></span>
                        <?php elseif($agreement->status == 'Completed'): ?>
                            <span
                                class="badge bg-light-success"><?php echo e(\App\Models\agreement::status()[$agreement->status]); ?></span>
                        <?php elseif($agreement->status == 'Active'): ?>
                            <span
                                class="badge bg-light-info"><?php echo e(\App\Models\agreement::status()[$agreement->status]); ?></span>
                        <?php elseif($agreement->status == 'Cancelled'): ?>
                            <span
                                class="badge bg-light-danger"><?php echo e(\App\Models\agreement::status()[$agreement->status]); ?></span>
                        <?php elseif($agreement->status == 'Confirmed'): ?>
                            <span
                                class="badge bg-light-secondary"><?php echo e(\App\Models\agreement::status()[$agreement->status]); ?></span>
                        <?php endif; ?>
                    </td>

                    <?php if(Gate::check('edit agreement') || Gate::check('delete agreement') || Gate::check('show agreement')): ?>
                        <td>
                            <div class="cart-action">
                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['agreement.destroy', $agreement->id]]); ?>


                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show agreement')): ?>
                                    <a class="avtar avtar-xs btn-link-warning text-warning"
                                        href="<?php echo e(route('agreement.show', \Crypt::encrypt($agreement->id))); ?>"
                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('View')); ?>"> <i
                                            data-feather="eye"></i></a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit agreement')): ?>
                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal" data-size="lg"
                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Edit')); ?>"
                                        href="#" data-url="<?php echo e(route('agreement.edit', $agreement->id)); ?>"
                                        data-title="<?php echo e(__('Edit Agreement')); ?>"> <i data-feather="edit"></i></a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete agreement')): ?>
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Detete')); ?>"
                                        href="#"> <i data-feather="trash-2"></i></a>
                                <?php endif; ?>

                                <?php echo Form::close(); ?>

                            </div>

                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php /**PATH /home/etheonbe/public_html/resources/views/agreement/agreement_list.blade.php ENDPATH**/ ?>