
<div class="<?php echo e($col ?? 'col-xl-3 col-md-6'); ?> d-flex">
    <?php if(!empty($url)): ?><a href="<?php echo e($url); ?>" class="text-reset d-flex w-100"><?php endif; ?>
    <div class="card border-0 w-100">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-3 p-3 bg-<?php echo e($color ?? 'primary'); ?> bg-opacity-10 flex-shrink-0">
                <i class="ti ti-<?php echo e($icon); ?> f-20 text-<?php echo e($color ?? 'primary'); ?>"></i>
            </div>
            <div style="min-width:0">
                <p class="text-muted mb-1 small fw-semibold text-uppercase"><?php echo e($label); ?></p>
                <h5 class="mb-0"><?php echo $value; ?></h5>
                <?php if(!empty($sub)): ?><small class="text-muted d-block text-truncate"><?php echo $sub; ?></small><?php endif; ?>
            </div>
        </div>
    </div>
    <?php if(!empty($url)): ?></a><?php endif; ?>
</div>
<?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/_stat.blade.php ENDPATH**/ ?>