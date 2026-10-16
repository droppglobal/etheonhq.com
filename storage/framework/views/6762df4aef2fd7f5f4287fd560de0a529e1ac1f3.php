
<?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <a href="<?php echo e($item['url']); ?>" class="d-flex align-items-center gap-3 py-2 border-bottom text-reset">
        <div class="text-center flex-shrink-0" style="width:46px">
            <div class="small text-muted text-uppercase"><?php echo e(\Carbon\Carbon::parse($item['date'])->format('M')); ?></div>
            <div class="fw-bold fs-5 lh-1"><?php echo e(\Carbon\Carbon::parse($item['date'])->format('d')); ?></div>
        </div>
        <div class="avtar avtar-s bg-light-<?php echo e($item['color']); ?> flex-shrink-0"><i class="ti ti-<?php echo e($item['icon']); ?>"></i></div>
        <div style="min-width:0">
            <div class="fw-semibold text-truncate"><?php echo e($item['title']); ?></div>
            <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($item['date'])->isToday() ? __('Today') : (\Carbon\Carbon::parse($item['date'])->isTomorrow() ? __('Tomorrow') : \Carbon\Carbon::parse($item['date'])->format('l'))); ?><?php echo e(!empty($item['time']) ? ' · ' . substr($item['time'], 0, 5) : ''); ?><?php echo e(!empty($item['sub']) ? ' · ' . $item['sub'] : ''); ?></small>
        </div>
    </a>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="text-muted mb-0 py-2"><?php echo e($empty ?? __('Nothing coming up.')); ?></p>
<?php endif; ?>
<?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/_timeline.blade.php ENDPATH**/ ?>