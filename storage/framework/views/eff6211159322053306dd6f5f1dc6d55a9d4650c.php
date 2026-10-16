<div class="row g-3">
    <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        
        <?php if(!empty($property->thumbnail) && !empty($property->thumbnail->image)): ?>
            <?php $thumbnail= $property->thumbnail->image; ?>
        <?php else: ?>
            <?php $thumbnail= 'default.jpg'; ?>
        <?php endif; ?>
        <div class="col-sm-6 col-md-4 col-xxl-3">
            <div class="card h-100 shadow-sm border">

                <div class="card product-card">
                    <div class="card-img-top">
                        <img src="<?php echo e(fetch_file($thumbnail, 'upload/property/thumbnail/')); ?>" alt="<?php echo e($property->name); ?>"
                            class="img-prod" />

                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show property')): ?> <?php echo e(route('property.show', \Crypt::encrypt($property->id))); ?>  <?php endif; ?>"
                                class="fw-semibold mb-0 text-truncate">
                                <h4><?php echo e($property->name); ?></h4>
                            </a>
                            <?php if(Gate::check('edit property') || Gate::check('delete property') || Gate::check('show property')): ?>
                                <div class="dropdown">
                                    <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="ti ti-dots f-16"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <?php echo Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['property.destroy', $property->id],
                                            'id' => 'property-' . $property->id,
                                        ]); ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit property')): ?>
                                            <a class="dropdown-item"
                                                href="<?php echo e(route('property.edit', \Crypt::encrypt($property->id))); ?>">
                                                <i class="material-icons-two-tone">edit</i>
                                                <?php echo e(__('Edit Property')); ?>

                                            </a>
                                        <?php endif; ?>

                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show property')): ?>
                                            <a class="dropdown-item"
                                                href="<?php echo e(route('property.show', \Crypt::encrypt($property->id))); ?>">
                                                <i class="material-icons-two-tone">remove_red_eye</i>
                                                <?php echo e(__('View property')); ?>

                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete property')): ?>
                                            <a class="dropdown-item confirm_dialog" href="#">
                                                <i class="material-icons-two-tone">delete</i>
                                                <?php echo e(__('Delete Property')); ?>

                                            </a>
                                        <?php endif; ?>

                                        <?php echo Form::close(); ?>

                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-light-info">
                                    <i class="material-icons-two-tone">ad_units</i>
                                    <?php echo e($property->totalUnit()); ?> <?php echo e(__('Unit')); ?>

                                </button>

                                <button type="button" class="btn btn-sm btn-light-success">
                                    <i class="material-icons-two-tone">ad_units</i>
                                    <?php echo e($property->vacantUnit()); ?> <?php echo e(__('Vacant')); ?>

                                </button>

                                <button type="button" class="btn btn-sm btn-light-danger">
                                    <i class="material-icons-two-tone">ad_units</i>
                                    <?php echo e($property->occupiedUnit()); ?> <?php echo e(__('Occupied ')); ?>

                                </button>
                            </div>
                        </div>


                        <p class="prod-content my-2 text-muted">
                            <?php echo e(Str::limit(strip_tags($property->description), 200, '...')); ?>

                        </p>

                        <div class="d-flex align-items-center justify-content-between mt-3">

                            <span class="badge bg-light-secondary" data-bs-toggle="tooltip"
                                data-bs-original-title="<?php echo e(__('Type')); ?>"><?php echo e(\App\Models\Property::types()[$property->type]); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($properties->hasPages()): ?>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <small class="text-muted">
                <?php echo e(__('Showing')); ?> <?php echo e($properties->firstItem()); ?> <?php echo e(__('to')); ?>

                <?php echo e($properties->lastItem()); ?>

                <?php echo e(__('of')); ?> <?php echo e($properties->total()); ?> <?php echo e(__('results')); ?>

            </small>
            <div class="pagination-wrapper detail-pagination">
                <?php echo e($properties->links('pagination::bootstrap-5')); ?>

            </div>
        </div>
    <?php endif; ?>

</div>
<?php /**PATH /home/etheonbe/public_html/resources/views/property/property_list.blade.php ENDPATH**/ ?>