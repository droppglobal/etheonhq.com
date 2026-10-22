<?php
    $siteName = !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME');
    $contactName = !empty($settings['company_name']) ? $settings['company_name'] : $siteName;
    $contactEmail = !empty($settings['support_email']) ? $settings['support_email'] : (!empty($settings['company_email']) ? $settings['company_email'] : config('mail.from.address'));
    $contactPhone = $settings['company_phone'] ?? '';
    $contactAddress = $settings['company_address'] ?? '';
?>

<h3 class="mb-3"><?php echo e(__('Get in touch with :name', ['name' => $contactName])); ?></h3>
<p class="mb-4"><?php echo e(__('We would be glad to hear from you. Reach us using any of the details below.')); ?></p>

<div class="row g-3 mb-4">
    <?php if(!empty($contactEmail)): ?>
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-1"><i class="ti ti-mail me-2"></i><?php echo e(__('Email')); ?></h6>
                <a href="mailto:<?php echo e($contactEmail); ?>"><?php echo e($contactEmail); ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if(!empty($contactPhone)): ?>
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-1"><i class="ti ti-phone me-2"></i><?php echo e(__('Phone')); ?></h6>
                <a href="tel:<?php echo e(preg_replace('/[^0-9+]/', '', $contactPhone)); ?>"><?php echo e($contactPhone); ?></a>
            </div>
        </div>
    <?php endif; ?>
    <?php if(!empty($contactAddress)): ?>
        <div class="col-md-6">
            <div class="border rounded p-3 h-100">
                <h6 class="mb-1"><i class="ti ti-map-pin me-2"></i><?php echo e(__('Address')); ?></h6>
                <span><?php echo e($contactAddress); ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if(empty($contactEmail) && empty($contactPhone) && empty($contactAddress)): ?>
    <p class="text-muted mb-4">
        <?php echo e(__('Contact details have not been added yet. Please check back soon.')); ?>

    </p>
<?php endif; ?>

<p class="mb-0">
    <?php echo e(__('Looking for help using the platform?')); ?>

    <a href="<?php echo e(route('info.support')); ?>"><?php echo e(__('Support and Documentation')); ?></a>
</p>
<?php /**PATH /home/etheonbe/public_html/resources/views/Pages/info/contact.blade.php ENDPATH**/ ?>