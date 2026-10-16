<?php $__env->startSection('page-title'); ?>
    <?php echo e(__('Tenant Chats')); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"><?php echo e(__('Tenant Chats')); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0"><?php echo e(__('Tenant Chats')); ?></h5>
                <small class="text-muted"><?php echo e(__('Conversations tenants had with the assistant in the app and on WhatsApp. Ones the assistant could not answer are marked "Needs reply".')); ?></small>
            </div>
            <div class="btn-group btn-group-sm">
                <a href="<?php echo e(route('assistant-chats.index')); ?>" class="btn <?php echo e(request('filter') != 'waiting' ? 'btn-secondary' : 'btn-light-secondary'); ?>"><?php echo e(__('All')); ?></a>
                <a href="<?php echo e(route('assistant-chats.index', ['filter' => 'waiting'])); ?>" class="btn <?php echo e(request('filter') == 'waiting' ? 'btn-secondary' : 'btn-light-secondary'); ?>"><?php echo e(__('Needs reply')); ?></a>
            </div>
        </div>
        <div class="card-body pt-1">
            <?php $__empty_1 = true; $__currentLoopData = $threads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thread): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $tenant = $tenants[$thread->tenant_id] ?? null;
                    $last = $lastMessages[$thread->tenant_id] ?? null;
                ?>
                <?php if(!$tenant) continue; ?>
                <a href="<?php echo e(route('assistant-chats.show', $tenant->id)); ?>" class="d-flex align-items-center gap-3 py-3 border-bottom text-reset">
                    <div class="avtar avtar-s <?php echo e($thread->waiting ? 'bg-light-danger' : 'bg-light-secondary'); ?> flex-shrink-0">
                        <i class="ti ti-<?php echo e(optional($last)->channel == 'whatsapp' ? 'brand-whatsapp' : 'messages'); ?> f-18"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width:0">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="fw-semibold text-truncate"><?php echo e(optional($tenant->user)->name ?: __('Tenant')); ?>

                                <small class="text-muted fw-normal">· <?php echo e(optional($tenant->properties)->name); ?> <?php echo e(optional($tenant->units)->name); ?></small></span>
                            <small class="text-muted flex-shrink-0"><?php echo e(optional(optional($last)->created_at)->diffForHumans()); ?></small>
                        </div>
                        <div class="d-flex justify-content-between gap-2">
                            <small class="text-muted text-truncate">
                                <?php if(optional($last)->role == 'staff'): ?><?php echo e(__('You')); ?>: <?php elseif(optional($last)->role == 'assistant'): ?><?php echo e(__('Assistant')); ?>: <?php endif; ?><?php echo e(optional($last)->content); ?>

                            </small>
                            <?php if($thread->waiting): ?>
                                <span class="badge bg-danger flex-shrink-0"><?php echo e(__('Needs reply')); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-center text-muted py-5 mb-0">
                    <?php echo e(request('filter') == 'waiting' ? __('Nothing is waiting for a reply.') : __('No conversations yet. Tenants can chat with the assistant from their Assistant page or on WhatsApp.')); ?>

                </p>
            <?php endif; ?>
            <div class="mt-3"><?php echo e($threads->links()); ?></div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/assistant_chat/index.blade.php ENDPATH**/ ?>