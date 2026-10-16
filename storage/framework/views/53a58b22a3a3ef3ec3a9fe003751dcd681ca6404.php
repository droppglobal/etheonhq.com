<?php $__env->startSection('page-title'); ?>
    <?php echo e(optional($tenant->user)->name); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>"><?php echo e(__('Dashboard')); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('assistant-chats.index')); ?>"><?php echo e(__('Tenant Chats')); ?></a></li>
    <li class="breadcrumb-item" aria-current="page"><?php echo e(optional($tenant->user)->name); ?></li>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('css-page'); ?>
    <style>
        .chat-box { max-height: 60vh; overflow-y: auto; background: #f8f9fb; border-radius: 10px; padding: 16px; }
        .chat-msg { width: fit-content; max-width: 78%; padding: 10px 14px; border-radius: 14px; margin-bottom: 12px; }
        .chat-text { white-space: pre-line; }
        .chat-msg.user { background: #fff; border: 1px solid #e6e8ee; border-bottom-left-radius: 4px; }
        .chat-msg.assistant { background: #eef6f4; margin-left: auto; border-bottom-right-radius: 4px; }
        .chat-msg.staff { background: var(--bs-secondary, #2ca58d); color: #fff; margin-left: auto; border-bottom-right-radius: 4px; }
        .chat-meta { display: block; font-size: 11px; opacity: .75; margin-bottom: 2px; }
        .chat-msg.waiting { border: 2px solid #dc3545; }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo e(__('Conversation')); ?></h5>
                    <?php if($messages->where('needs_reply', 1)->count()): ?>
                        <?php echo e(Form::open(['route' => ['assistant-chats.resolve', $tenant->id], 'method' => 'post'])); ?>

                        <button class="btn btn-sm btn-light-secondary"><i class="ti ti-check"></i> <?php echo e(__('Mark as handled')); ?></button>
                        <?php echo e(Form::close()); ?>

                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="chat-box" id="chat-box">
                        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="chat-msg <?php echo e($message->role); ?> <?php echo e($message->needs_reply ? 'waiting' : ''); ?>">
                                <span class="chat-meta">
                                    <?php if($message->role == 'user'): ?><?php echo e(optional($tenant->user)->first_name ?: __('Tenant')); ?><?php elseif($message->role == 'staff'): ?><?php echo e(optional($message->staff)->name ?: __('Office')); ?><?php else: ?><?php echo e(__('Assistant')); ?><?php endif; ?>
                                    · <?php echo e($message->channel == 'whatsapp' ? 'WhatsApp' : __('App')); ?> · <?php echo e(dateFormat($message->created_at)); ?> <?php echo e($message->created_at->format('H:i')); ?>

                                </span>
                                <div class="chat-text"><?php echo e($message->content); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-muted text-center mb-0"><?php echo e(__('No messages.')); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php echo e(Form::open(['route' => ['assistant-chats.reply', $tenant->id], 'method' => 'post', 'class' => 'mt-3'])); ?>

                    <textarea name="message" class="form-control" rows="2" maxlength="2000" required placeholder="<?php echo e(__('Write a reply to the tenant...')); ?>"></textarea>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" value="web" id="ch-web" <?php echo e($lastChannel != 'whatsapp' || !$whatsapp ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="ch-web"><?php echo e(__('In-app chat')); ?></label>
                            </div>
                            <?php if($whatsapp): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="channel" value="whatsapp" id="ch-wa" <?php echo e($lastChannel == 'whatsapp' ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="ch-wa">WhatsApp</label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-secondary"><i class="ti ti-send"></i> <?php echo e(__('Send reply')); ?></button>
                    </div>
                    <?php echo e(Form::close()); ?>

                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0"><?php echo e(__('Tenant')); ?></h5></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Name')); ?></span><span class="fw-semibold"><?php echo e(optional($tenant->user)->name); ?></span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Property')); ?></span><span class="fw-semibold text-end"><?php echo e(optional($tenant->properties)->name); ?> / <?php echo e(optional($tenant->units)->name); ?></span></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted"><?php echo e(__('Phone')); ?></span><span><?php echo e(optional($tenant->user)->phone_number ?: '-'); ?></span></div>
                    <div class="d-flex justify-content-between py-2"><span class="text-muted"><?php echo e(__('Email')); ?></span><span class="text-truncate ms-2"><?php echo e(optional($tenant->user)->email); ?></span></div>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show tenant')): ?>
                        <a href="<?php echo e(route('tenant.show', \Crypt::encrypt($tenant->id))); ?>" class="btn btn-light-secondary btn-sm w-100 mt-3"><?php echo e(__('Open tenant profile')); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('script-page'); ?>
    <script>
        var box = document.getElementById('chat-box');
        if (box) box.scrollTop = box.scrollHeight;
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/etheonbe/public_html/resources/views/assistant_chat/show.blade.php ENDPATH**/ ?>