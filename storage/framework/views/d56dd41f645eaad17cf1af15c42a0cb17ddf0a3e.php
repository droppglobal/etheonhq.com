<?php
    $siteName = !empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME');
    $supportEmail = !empty($settings['support_email']) ? $settings['support_email'] : (!empty($settings['company_email']) ? $settings['company_email'] : config('mail.from.address'));
?>

<h3 class="mb-3"><?php echo e(__('Support')); ?></h3>
<p class="mb-4">
    <?php echo e(__('Need a hand with :name? Start with the guide and answers below. If you still need help, our team is happy to assist.', ['name' => $siteName])); ?>

</p>
<div class="d-flex flex-wrap gap-2 mb-5">
    <a href="<?php echo e(route('info.contact')); ?>" class="btn btn-secondary"><?php echo e(__('Contact us')); ?></a>
    <?php if(!empty($supportEmail)): ?>
        <a href="mailto:<?php echo e($supportEmail); ?>" class="btn btn-outline-secondary"><?php echo e($supportEmail); ?></a>
    <?php endif; ?>
</div>

<h3 class="mb-3"><?php echo e(__('Documentation')); ?></h3>
<p class="mb-3"><?php echo e(__('A quick guide to getting started:')); ?></p>
<ol class="mb-5">
    <li class="mb-2">
        <strong><?php echo e(__('Create your account.')); ?></strong>
        <?php echo e(__('Register, or log in if you already have an account.')); ?>

        <a href="<?php echo e(route('register')); ?>"><?php echo e(__('Get Started')); ?></a> ·
        <a href="<?php echo e(route('login')); ?>"><?php echo e(__('Login')); ?></a>
    </li>
    <li class="mb-2">
        <strong><?php echo e(__('Add your properties and units.')); ?></strong>
        <?php echo e(__('Set up each property and the units inside it so you can assign tenants.')); ?>

    </li>
    <li class="mb-2">
        <strong><?php echo e(__('Add your tenants.')); ?></strong>
        <?php echo e(__('Record tenant details and link each tenant to a unit, with agreements and documents.')); ?>

    </li>
    <li class="mb-2">
        <strong><?php echo e(__('Create invoices and record payments.')); ?></strong>
        <?php echo e(__('Bill rent and other charges, then mark payments as they come in to see what is still due.')); ?>

    </li>
    <li class="mb-2">
        <strong><?php echo e(__('Handle maintenance and expenses.')); ?></strong>
        <?php echo e(__('Log maintenance requests, track them to completion and record what you spend.')); ?>

    </li>
    <li class="mb-2">
        <strong><?php echo e(__('Review your reports.')); ?></strong>
        <?php echo e(__('Use the dashboard and reports to see how your rentals are performing.')); ?>

    </li>
</ol>

<?php if(!empty($FAQs) && count($FAQs) > 0): ?>
    <h3 class="mb-3"><?php echo e(__('Frequently Asked Questions')); ?></h3>
    <div class="accordion accordion-flush" id="supportFaqs">
        <?php $__currentLoopData = $FAQs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="support-faq-h<?php echo e($faq->id); ?>">
                    <button class="accordion-button collapsed text-muted" type="button" data-bs-toggle="collapse"
                        data-bs-target="#support-faq-c<?php echo e($faq->id); ?>" aria-expanded="false"
                        aria-controls="support-faq-c<?php echo e($faq->id); ?>">
                        <b><?php echo e($faq->question); ?></b>
                    </button>
                </h2>
                <div id="support-faq-c<?php echo e($faq->id); ?>" class="accordion-collapse collapse"
                    aria-labelledby="support-faq-h<?php echo e($faq->id); ?>" data-bs-parent="#supportFaqs">
                    <div class="accordion-body"><?php echo $faq->description; ?></div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH /home/etheonbe/public_html/resources/views/Pages/info/support.blade.php ENDPATH**/ ?>