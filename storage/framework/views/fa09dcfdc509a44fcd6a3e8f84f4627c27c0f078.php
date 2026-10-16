<div class="card-body pt-0">
    <div class="dt-responsive table-responsive">
        <table class="table table-hover advance-datatable">
            <thead>
                <tr>
                    <th><?php echo e(__('Property')); ?></th>
                    <th><?php echo e(__('Unit')); ?></th>
                    <th><?php echo e(__('Tenant Name')); ?></th>
                    <th><?php echo e(__('Maintainer Name')); ?></th>
                    <th><?php echo e(__('Issue')); ?></th>
                    <th><?php echo e(__('Status')); ?></th>

                </tr>
            </thead>
            <tbody>

                <?php $__currentLoopData = $maintenances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintenance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($maintenance->properties->name ?? '-'); ?></td>
                        <td><?php echo e($maintenance->units->name ?? '-'); ?></td>
                        <td><?php echo e($maintenance->tenetData?->user?->first_name . ' ' . $maintenance->tenetData?->user?->last_name ?? '-'); ?>

                        </td>
                        <td><?php echo e($maintenance->maintainers->first_name ?? '-'); ?></td>
                        <td><?php echo e($maintenance->types->title ?? '-'); ?></td>

                        <td>

                            <?php if($maintenance->status == 'in_progress'): ?>
                                <span class="badge bg-light-info">
                                    <?php echo e(__('In Progress')); ?></span>
                            <?php elseif($maintenance->status == 'completed'): ?>
                                <span class="badge bg-light-success">
                                    <?php echo e(ucfirst($maintenance->status)); ?></span>
                            <?php elseif($maintenance->status == 'pending'): ?>
                                <span class="badge bg-light-warning">
                                    <?php echo e(ucfirst($maintenance->status)); ?></span>
                            <?php else: ?>
                                <span><?php echo e(__('No Invoice Found')); ?></span>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php /**PATH /home/etheonbe/public_html/resources/views/report/maintenance_report.blade.php ENDPATH**/ ?>