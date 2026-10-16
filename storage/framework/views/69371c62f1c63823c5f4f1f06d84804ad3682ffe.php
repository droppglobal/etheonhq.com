<div class="row g-3">
    <?php $__empty_1 = true; $__currentLoopData = $maintainers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maintainer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-xxl-3 col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm border">
                <div class="card-body p-3">

                    
                    <div class="d-flex justify-content-between align-items-start mb-3">

                        
                        <div class="flex-grow-1 text-center">
                            <img class="rounded-circle"
                                style="width:70px;height:70px;object-fit:cover;border:2px solid #e2e8f0;"
                                src="<?php echo e(!empty($maintainer->user->profile) ? fetch_file($maintainer->user->profile, 'upload/profile/') : $profile); ?>"
                                alt="Profile">
                        </div>

                        
                        <?php if(Gate::check('edit maintainer') || Gate::check('delete maintainer')): ?>
                            <div class="flex-shrink-0">
                                <div class="dropdown">
                                    <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                        data-bs-toggle="dropdown">
                                        <i class="ti ti-dots f-16"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item"
                                            href="<?php echo e(route('maintainer.show', encrypt($maintainer->id))); ?>"
                                            data-title="<?php echo e(__('Show Maintainer')); ?>" data-size="lg">
                                            <i class="material-icons-two-tone me-1">visibility</i>
                                            <?php echo e(__('Show Maintainer')); ?>

                                        </a>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit maintainer')): ?>
                                            <a class="dropdown-item customModal" href="#"
                                                data-url="<?php echo e(route('maintainer.edit', $maintainer->id)); ?>"
                                                data-title="<?php echo e(__('Edit Maintainer')); ?>" data-size="lg">
                                                <i class="material-icons-two-tone me-1">edit</i>
                                                <?php echo e(__('Edit Maintainer')); ?>

                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete maintainer')): ?>
                                            <?php echo Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['maintainer.destroy', $maintainer->id],
                                                'id' => 'maintainer-' . $maintainer->id,
                                            ]); ?>

                                            <a class="dropdown-item confirm_dialog" href="#">
                                                <i class="material-icons-two-tone me-1">delete</i>
                                                <?php echo e(__('Delete Maintainer')); ?>

                                            </a>
                                            <?php echo Form::close(); ?>

                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="text-center mb-3">
                        
                        <a class="dropdown-item" href="<?php echo e(route('maintainer.show', encrypt($maintainer->id))); ?>"
                            data-title="<?php echo e(__('Show Maintainer')); ?>" data-size="lg">
                            <h5 class="mb-1">
                                <?php echo e(ucfirst($maintainer->user->first_name ?? '') . ' ' . ucfirst($maintainer->user->last_name ?? '')); ?>

                            </h5>
                        </a>
                        
                        <p class="text-muted text-sm mb-0 text-truncate">
                            <?php echo e($maintainer->user->email ?? '-'); ?>

                        </p>
                    </div>

                    <hr class="my-2">

                    
                    <div class="row g-2">

                        <?php if(!empty($maintainer->user->phone_number)): ?>
                            <div class="col-6">
                                <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Phone')); ?></p>
                                <p class="mb-0 fw-semibold" style="font-size:12px;">
                                    <?php echo e($maintainer->user->phone_number); ?>

                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Type')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e($maintainer->types->title ?? '-'); ?>

                            </p>
                        </div>

                        <div class="col-6">
                            <p class="mb-0 text-muted" style="font-size:11px;"><?php echo e(__('Created Date')); ?></p>
                            <p class="mb-0 fw-semibold" style="font-size:12px;">
                                <?php echo e(dateFormat($maintainer->created_at)); ?>

                            </p>
                        </div>

                    </div>

                    
                    <?php $properties = $maintainer->properties(); ?>
                    <?php if(!empty($properties) && count($properties) > 0): ?>
                        <hr class="my-2">
                        <p class="mb-1 text-muted" style="font-size:11px;"><?php echo e(__('Properties')); ?></p>
                        <div class="d-flex flex-wrap gap-1">
                            <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="badge bg-light-secondary text-secondary" style="font-size:11px;">
                                    <i class="ti ti-building me-1"></i><?php echo e($property->name); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12 text-center py-5">
            <i class="ti ti-tool-off" style="font-size:48px;color:#ccc;"></i>
            <p class="text-muted mt-2"><?php echo e(__('No Maintainers Found')); ?></p>
        </div>
    <?php endif; ?>
    <?php if($maintainers->hasPages()): ?>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                <?php echo e(__('Showing')); ?> <?php echo e($maintainers->firstItem()); ?> <?php echo e(__('to')); ?>

                <?php echo e($maintainers->lastItem()); ?>

                <?php echo e(__('of')); ?> <?php echo e($maintainers->total()); ?> <?php echo e(__('results')); ?>

            </small>
            <div class="pagination-wrapper detail-pagination">
                <?php echo e($maintainers->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH /home/etheonbe/public_html/resources/views/maintainer/maintainer_list.blade.php ENDPATH**/ ?>