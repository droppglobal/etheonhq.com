<?php
    $profile = asset(Storage::url('upload/profile/avatar.png'));
?>

<div class="row g-3">
    <?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-xxl-3 col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm border">
                <div class="card-body p-3">

                    
                    <div class="d-flex justify-content-between align-items-start mb-3">

                        
                        <div class="flex-grow-1 text-center">
                            <img class="rounded-circle"
                                style="width:70px;height:70px;object-fit:cover;border:2px solid #e2e8f0;"
                                src="<?php echo e(!empty($tenant->user->profile) ? fetch_file($tenant->user->profile, 'upload/profile/') : $profile); ?>"
                                alt="Profile">
                        </div>

                        
                        <?php if(Gate::check('edit tenant') || Gate::check('delete tenant') || Gate::check('show tenant')): ?>
                            <div class="flex-shrink-0">
                                <div class="dropdown">
                                    <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-dots f-16"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit tenant')): ?>
                                            <a class="dropdown-item"
                                                href="<?php echo e(route('tenant.edit', \Crypt::encrypt($tenant->id))); ?>">
                                                <i class="material-icons-two-tone me-1">edit</i>
                                                <?php echo e(__('Edit Tenant')); ?>

                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show tenant')): ?>
                                            <a class="dropdown-item"
                                                href="<?php echo e(route('tenant.show', \Crypt::encrypt($tenant->id))); ?>">
                                                <i class="material-icons-two-tone me-1">remove_red_eye</i>
                                                <?php echo e(__('View Tenant')); ?>

                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete tenant')): ?>
                                            <?php echo Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['tenant.destroy', $tenant->id],
                                                'id' => 'tenant-' . $tenant->id,
                                            ]); ?>

                                            <a class="dropdown-item confirm_dialog" href="#">
                                                <i class="material-icons-two-tone me-1">delete</i>
                                                <?php echo e(__('Delete Tenant')); ?>

                                            </a>
                                            <?php echo Form::close(); ?>

                                        <?php endif; ?>
                                        <?php
                                            $leaseEndDate = \Carbon\Carbon::parse($tenant->lease_end_date);
                                            $daysUntilExpiry = now()->diffInDays($leaseEndDate, false);
                                        ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage lease renew')): ?>
                                            <?php if($daysUntilExpiry <= 3): ?>
                                                <a class="dropdown-item customModal" href="#"
                                                    data-url="<?php echo e(route('lease.renew', ['tenant_id' => $tenant->id])); ?>"
                                                    data-title="<?php echo e(__('Renew Lease')); ?>" data-size="lg">
                                                    <span class="material-icons-two-tone me-1">autorenew</span>
                                                    <?php echo e(__('Renew Lease')); ?>

                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="text-center mb-3">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show tenant')): ?>
                            <a href="<?php echo e(route('tenant.show', \Crypt::encrypt($tenant->id))); ?>">
                                <h5 class="mb-1">
                                    <?php echo e(ucfirst($tenant->user->first_name ?? '') . ' ' . ucfirst($tenant->user->last_name ?? '')); ?>

                                </h5>
                            </a>
                        <?php else: ?>
                            <h5 class="mb-1">
                                <?php echo e(ucfirst($tenant->user->first_name ?? '') . ' ' . ucfirst($tenant->user->last_name ?? '')); ?>

                            </h5>
                        <?php endif; ?>
                        <p class="text-muted text-sm mb-1 text-truncate">
                            <?php echo e($tenant->user->email ?? '-'); ?>

                        </p>
                        <?php if(!empty($tenant->address)): ?>
                            <p class="text-muted text-sm mb-0 text-truncate">
                                <i class="ti ti-map-pin me-1"></i><?php echo e($tenant->address); ?>

                            </p>
                        <?php endif; ?>
                    </div>

                    <hr class="my-2">

                    
                    <div class="row g-2">
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Phone')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e($tenant->user->phone_number ?? '-'); ?>

                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Family Member')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e($tenant->family_member ?? '-'); ?>

                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Property')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e($tenant->properties->name ?? '-'); ?>

                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Unit')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e($tenant->units->name ?? '-'); ?>

                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Lease Start')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e(!empty($tenant->lease_start_date) ? dateFormat($tenant->lease_start_date) : '-'); ?>

                            </p>
                        </div>
                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Lease End')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e(!empty($tenant->lease_end_date) ? dateFormat($tenant->lease_end_date) : '-'); ?>

                            </p>
                        </div>
                    </div>



                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12 text-center py-5">
            <i class="ti ti-users-off" style="font-size:48px;color:#ccc;"></i>
            <p class="text-muted mt-2"><?php echo e(__('No Tenants Found')); ?></p>
        </div>
    <?php endif; ?>
</div>


<?php if($tenants->hasPages()): ?>
    <div class="d-flex justify-content-between align-items-center mt-4">
        <small class="text-muted">
            <?php echo e(__('Showing')); ?> <?php echo e($tenants->firstItem()); ?> <?php echo e(__('to')); ?>

            <?php echo e($tenants->lastItem()); ?>

            <?php echo e(__('of')); ?> <?php echo e($tenants->total()); ?> <?php echo e(__('results')); ?>

        </small>
        <div class="pagination-wrapper detail-pagination">
            <?php echo e($tenants->links('pagination::bootstrap-5')); ?>

        </div>
    </div>
<?php endif; ?>
<?php /**PATH /home/etheonbe/public_html/resources/views/tenant/tenant_list.blade.php ENDPATH**/ ?>