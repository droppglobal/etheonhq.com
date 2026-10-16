<div class="dt-responsive table-responsive">
    <table class="table table-hover advance-datatable">
        <thead>
            <tr>
                <th><?php echo e(__('Property')); ?></th>
                <th><?php echo e(__('Unit')); ?></th>
                <th><?php echo e(__('Tenant')); ?></th>
                <th><?php echo e(__('Issue')); ?></th>
                <th><?php echo e(__('Maintainer')); ?></th>
                <th><?php echo e(__('Request Date')); ?></th>
                <th><?php echo e(__('Status')); ?></th>
                <th><?php echo e(__('Attachment')); ?></th>
                <?php if(Gate::check('edit maintenance request') ||
                        Gate::check('delete maintenance request') ||
                        Gate::check('show maintenance request')): ?>
                    <th class="text-right"><?php echo e(__('Action')); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $maintenanceRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr role="row">
                    <td> <?php echo e(!empty($request->properties) ? $request->properties->name : '-'); ?> </td>
                    <td> <?php echo e(!empty($request->units) ? $request->units->name : '-'); ?> </td>
                    <td><?php echo e(!empty($request->tenetData->user) ? $request->tenetData->user->full_name : '-'); ?></td>
                    <td> <?php echo e(!empty($request->types) ? $request->types->title : '-'); ?>

                        <?php if(!empty($request->ai_priority) && $request->status != 'completed'): ?>
                            <br><span class="badge bg-light-<?php echo e(\App\Support\MaintenanceTriage::color($request->ai_priority)); ?>" title="<?php echo e($request->ai_summary); ?>"><?php echo e(\App\Support\MaintenanceTriage::priorities()[$request->ai_priority] ?? ''); ?></span>
                        <?php endif; ?>
                    </td>
                    <?php if(Gate::check('assign maintainer')): ?>
                        <td>
                            <?php if(!empty($request->maintainers)): ?>
                                <?php echo e($request->maintainers->name); ?>

                            <?php else: ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create maintenance request')): ?>
                                    <a href="#" class="customModal" data-size="md"
                                        data-url="<?php echo e(route('maintenance.assign', ['request_id' => $request->id])); ?>"
                                        data-title="<?php echo e(__('Assign Maintainer')); ?>">
                                        <i class="ti ti-circle-plus text-secondary fs-2 text-center"
                                            data-bs-original-title="<?php echo e(__('Assign Maintainer')); ?>"
                                            data-bs-toggle="tooltip"></i>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    <?php else: ?>
                        <td>
                            <?php echo e(!empty($request->maintainers) ? $request->maintainers->name : '-'); ?>

                        </td>
                    <?php endif; ?>
                    <td> <?php echo e(dateFormat($request->request_date)); ?> </td>
                    <td>
                        <?php if($request->status == 'pending'): ?>
                            <span class="badge bg-light-warning">
                                <?php echo e(\App\Models\MaintenanceRequest::status()[$request->status]); ?></span>
                        <?php elseif($request->status == 'in_progress'): ?>
                            <span class="badge bg-light-info">
                                <?php echo e(\App\Models\MaintenanceRequest::status()[$request->status]); ?></span>
                        <?php else: ?>
                            <span class="badge bg-light-success">
                                <?php echo e(\App\Models\MaintenanceRequest::status()[$request->status]); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($request->issue_attachment)): ?>
                            <a href="<?php echo e(!empty($request->issue_attachment) ? fetch_file($request->issue_attachment, 'upload/issue_attachment/') : '#'); ?>"
                                download="download"><i class="ti ti-download"></i></a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <?php if(Gate::check('edit maintenance request') ||
                            Gate::check('delete maintenance request') ||
                            Gate::check('show maintenance request')): ?>
                        <td class="text-right">
                            <div class="cart-action">
                                <?php echo Form::open(['method' => 'DELETE', 'route' => ['maintenance-request.destroy', $request->id]]); ?>

                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show maintenance request')): ?>
                                    <a class="avtar avtar-xs btn-link-warning text-warning" data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Show')); ?>"
                                        href="<?php echo e(route('maintenance-request.show', \Crypt::encrypt($request->id))); ?>"
                                        data-title="<?php echo e(__('Maintenance Request Details')); ?>"> <i
                                            data-feather="eye"></i></a>
                                <?php endif; ?>
                                <?php if($request->status != 'completed'): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit maintenance request')): ?>
                                        <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                            data-size="lg" data-bs-toggle="tooltip"
                                            data-bs-original-title="<?php echo e(__('Edit')); ?>" href="#"
                                            data-url="<?php echo e(route('maintenance-request.edit', $request->id)); ?>"
                                            data-title="<?php echo e(__('Maintenance Request')); ?>"> <i data-feather="edit"></i></a>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete maintenance request')): ?>
                                    <a class="avtar avtar-xs btn-link-danger text-danger confirm_dialog"
                                        data-bs-toggle="tooltip" data-bs-original-title="<?php echo e(__('Detete')); ?>"
                                        href="#"> <i data-feather="trash-2"></i></a>
                                <?php endif; ?>
                                <?php if(\Auth::user()->type == 'maintainer' && $request->status != 'completed'): ?>
                                    <a class="avtar avtar-xs btn-link-secondary text-secondary customModal"
                                        data-size="lg" data-bs-toggle="tooltip"
                                        data-bs-original-title="<?php echo e(__('Status Update')); ?>" href="#"
                                        data-url="<?php echo e(route('maintenance-request.action', $request->id)); ?>"
                                        data-title="<?php echo e(__('Maintenance Request Status')); ?>"> <i
                                            data-feather="check-square"></i></a>
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
<?php /**PATH /home/etheonbe/public_html/resources/views/maintenance_request/maintenance_list.blade.php ENDPATH**/ ?>