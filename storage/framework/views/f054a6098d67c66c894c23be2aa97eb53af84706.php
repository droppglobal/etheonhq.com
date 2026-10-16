<head>
    <!-- Required meta tags-->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="ckeditor-upload-url" content="<?php echo e(route('ckeditor.upload')); ?>?_token=<?php echo e(csrf_token()); ?>">
    <meta name="author" content="<?php echo e(!empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME')); ?>">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(!empty($settings['app_name']) ? $settings['app_name'] : env('APP_NAME')); ?> - <?php echo $__env->yieldContent('page-title'); ?> </title>

    <meta name="title" content="<?php echo e($settings['meta_seo_title']); ?>">
    <meta name="keywords" content="<?php echo e($settings['meta_seo_keyword']); ?>">
    <meta name="description" content="<?php echo e($settings['meta_seo_description']); ?>">


    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(env('APP_URL')); ?>">
    <meta property="og:title" content="<?php echo e($settings['meta_seo_title']); ?>">
    <meta property="og:description" content="<?php echo e($settings['meta_seo_description']); ?>">
    <meta property="og:image"
        content="<?php echo e(!empty($settings['meta_seo_image']) ? fetch_file($settings['meta_seo_image'], 'upload/seo/') : '#'); ?>">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo e(env('APP_URL')); ?>">
    <meta property="twitter:title" content="<?php echo e($settings['meta_seo_title']); ?>">
    <meta property="twitter:description" content="<?php echo e($settings['meta_seo_description']); ?>">
    <meta property="twitter:image"
        content="<?php echo e(!empty($settings['meta_seo_image']) ? fetch_file($settings['meta_seo_image'], 'upload/seo/') : '#'); ?>">

    <!-- shortcut icon-->
    <link rel="icon"
        href="<?php echo e(!empty($settings['company_favicon']) ? fetch_file($settings['company_favicon'], 'upload/logo/') : '#'); ?>"
        type="image/x-icon">
    <link rel="shortcut icon"
        href="<?php echo e(!empty($settings['company_favicon']) ? fetch_file($settings['company_favicon'], 'upload/logo/') : '#'); ?>"
        type="image/x-icon">

    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/notifier.css')); ?>" />
    <!-- [Page specific CSS] start -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/datepicker-bs5.min.css')); ?>" />
    <!-- [Page specific CSS] end -->

    <!-- data tables css -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/dataTables.bootstrap5.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/plugins/buttons.bootstrap5.min.css')); ?>" />

    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
        id="main-font-link" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/phosphor/duotone/style.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/tabler-icons.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/feather.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/select2/select2.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/select2/select2.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/daterangepicker/daterangepicker.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/fontawesome.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/material.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>" id="main-style-link" />

    <?php if(!empty($settings['custom_color']) && $settings['color_type'] == 'custom'): ?>
        <link rel="stylesheet" id="Pstylesheet" href="<?php echo e(asset('assets/css/custom-color.css')); ?>" />
        <script src="<?php echo e(asset('js/theme-pre-color.js')); ?>"></script>
    <?php else: ?>
        <link rel="stylesheet" id="Pstylesheet" href="<?php echo e(asset('assets/css/style-preset.css')); ?>" />
    <?php endif; ?>

    <?php echo $__env->yieldPushContent('css-page'); ?>
    <link href="<?php echo e(asset('css/custom.css')); ?>" rel="stylesheet">
    
    <link rel="manifest" href="<?php echo e(route('pwa.manifest')); ?>">
    <meta name="theme-color" content="#2ca58d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="<?php echo e(asset('assets/images/pwa/icon-192.png')); ?>">
</head>


<?php /**PATH /home/etheonbe/public_html/resources/views/admin/head.blade.php ENDPATH**/ ?>