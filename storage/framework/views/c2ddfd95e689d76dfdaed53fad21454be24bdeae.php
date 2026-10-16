<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Notice Board')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"> <?php echo e(__('Notice Board')); ?></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5><?php echo e(__('Note List')); ?></h5>
                        </div>
                        <?php if(Gate::check('create note')): ?>
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="md"
                                    data-url="<?php echo e(route('note.create')); ?>" data-title="<?php echo e(__('Create Note')); ?>"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> <?php echo e(__('Create Note')); ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-xxl-3 col-xl-4 col-md-6">
                                <div class="card follower-card">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-grow-1 "><?php echo e(dateFormat($note->created_at)); ?></div>
                                            <?php if($note->parent_id == parentId() && in_array(auth()->user()->type, ['super admin', 'owner'])): ?>
                                                <?php if(Gate::check('edit note') || Gate::check('delete note')): ?>
                                                    <div class="flex-shrink-0">
                                                        <div class="dropdown">
                                                            <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                                href="#" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="ti ti-dots f-16"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <?php if(Gate::check('edit note')): ?>
                                                                    <a class="dropdown-item customModal" href="#"
                                                                        data-url="<?php echo e(route('note.edit', $note->id)); ?>"
                                                                        data-title="<?php echo e(__('Edit Note')); ?>"> <i
                                                                            class="ti ti-edit"></i><?php echo e(__('Edit')); ?></a>
                                                                <?php endif; ?>
                                                                <?php if(Gate::check('delete note')): ?>
                                                                    <?php echo Form::open(['method' => 'DELETE', 'route' => ['note.destroy', $note->id], 'id' => 'user-' . $note->id]); ?>

                                                                    <a href="#"
                                                                        class="dropdown-item confirm_dialog"><i
                                                                            class="ti ti-trash"></i>
                                                                        <?php echo e(__('Delete')); ?></a>
                                                                    <?php echo Form::close(); ?>

                                                                <?php endif; ?>
                                                                <?php if(!empty($note->attachment)): ?>
                                                                    <a class="dropdown-item"
                                                                        href="<?php echo e(!empty($note->attachment) ? fetch_file($note->attachment, 'upload/note/') : '#'); ?>"
                                                                        target="_blank"><i
                                                                            class="ti ti-download"></i><?php echo e(__('Download')); ?></a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 mb-4">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Title')); ?></p>
                                                <h6 class="mb-0"><?php echo e($note->title); ?> </h6>
                                            </div>
                                            <div class="col-12 mb-4">
                                                <p class="mb-0 text-muted text-sm"><?php echo e(__('Description')); ?></p>
                                                <h6 class="mb-0">
                                                    <?php echo e(!empty($note->description) ? $note->description : '-'); ?>

                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/note/index.blade.php ENDPATH**/ ?>