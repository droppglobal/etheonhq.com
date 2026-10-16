
<div class="d-flex align-items-end justify-content-between mb-2 mt-1">
    <div>
        <h6 class="mb-0 text-uppercase text-muted fw-bold small" style="letter-spacing:.05em"><?php echo e($title); ?></h6>
        <?php if(!empty($sub)): ?><small class="text-muted"><?php echo e($sub); ?></small><?php endif; ?>
    </div>
    <?php if(!empty($link)): ?><a href="<?php echo e($link); ?>" class="small"><?php echo e($linkText ?? __('View all')); ?> <i class="ti ti-chevron-right"></i></a><?php endif; ?>
</div>
<?php /**PATH /home/etheonbe/public_html/resources/views/dashboard/_section.blade.php ENDPATH**/ ?>