<?php
    $routeName = \Request::route()->getName();
    $routeParameters = request()->route()->parameters;
    $settings = settings();
    $user = \App\Models\User::find(1);
    \App::setLocale($user->lang);
    $menus = \App\Models\Page::where('enabled', 1)->get();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(env('APP_NAME')); ?></title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

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
    <meta property="og:image" content="<?php echo e(!empty($settings['meta_seo_image']) ? fetch_file($settings['meta_seo_image'],'upload/seo') : '#'); ?>">

    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo e(env('APP_URL')); ?>">
    <meta property="twitter:title" content="<?php echo e($settings['meta_seo_title']); ?>">
    <meta property="twitter:description" content="<?php echo e($settings['meta_seo_description']); ?>">
    <meta property="twitter:image"
        content="<?php echo e(!empty($settings['meta_seo_image']) ? fetch_file($settings['meta_seo_image'],'upload/seo') : '#'); ?>">


    <link rel="icon" href="<?php echo e(!empty($settings['company_favicon']) ? fetch_file($settings['company_favicon'],'upload/logo') : '#'); ?>"
        type="image/x-icon" />
    <link href="<?php echo e(asset('assets/css/plugins/animate.min.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(asset('assets/css/plugins/swiper-bundle.css')); ?>" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
        id="main-font-link" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/phosphor/duotone/style.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/tabler-icons.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/feather.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/fontawesome.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/fonts/material.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/owl.carousel.min.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>" id="main-style-link" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style-preset.css')); ?>" />
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/landing.css')); ?>" />
    <link href="<?php echo e(asset('css/custom.css')); ?>" rel="stylesheet">
    <style>
        .ck.ck-reset_all {
            display: none;
        }

        .ck .ck-widget:hover {
            outline-color: transparent;
        }

        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border: 0;
            border-color: transparent;
            outline: 0;
        }

        .ck.ck-editor__main:focus-visible,
        .ck .ck-widget:hover {
            outline-color: transparent !important;
        }
    </style>
</head>

<body class="landing-page" data-pc-preset="<?php echo e($settings['accent_color']); ?>" data-pc-sidebar-theme="light"
    data-pc-sidebar-caption="<?php echo e($settings['sidebar_caption']); ?>" data-pc-direction="<?php echo e($settings['theme_layout']); ?>"
    data-pc-theme="<?php echo e($settings['theme_mode']); ?>">

    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ Main Content ] start -->
    <nav class="navbar navbar-expand-lg navbar-light card rounded-0 fixed-top">
        <div class="container">
            <a class="navbar-brand landing-logo" href="<?php echo e(route('home')); ?>">
                <img src="<?php echo e(!empty($settings['logo']) ? fetch_file($settings['logo'],'upload/logo') : '#'); ?>" alt="logo" class="img-fluid " />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto f-w-600">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>"><?php echo e(__('Home')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>#pricing"><?php echo e(__('Pricing')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link me-2" href="<?php echo e(route('home')); ?>#features"><?php echo e(__('Features')); ?></a>
                    </li>
                    <?php
                        $HomePage = App\Models\HomePage::where('section', 'Section 0')->first();
                    ?>
                    <?php if(!empty($HomePage->content_value)): ?>
                        <?php
                            $HomePage = json_decode($HomePage->content_value, true);
                            $active_menus = !empty($HomePage['menu_pages']) ? $HomePage['menu_pages'] : [];
                        ?>
                        <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(in_array($menu->id, $active_menus)): ?>
                                <li class="nav-item">
                                    <a class="nav-link me-2 <?php echo e(!empty($routeParameters['slug']) && $menu->slug == $routeParameters['slug'] ? 'active' : ''); ?>"
                                        href="<?php echo e(route('page', $menu->slug)); ?>"><?php echo e($menu->title); ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link me-2" href="<?php echo e(route('login')); ?>"><?php echo e(__('Login')); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-secondary" href="<?php echo e(route('register')); ?>">
                            <?php echo e(__('Get Started')); ?>

                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="front-header-image">
        <div class="bg-img-overlay" style="background-image: url('../assets/images/pages/header-bg.jpg')"></div>
        <div class="privacy-details">
            <h1 class="text-center text-white pt-5 f-46"><?php echo e($page->title); ?></h1>
            <p class="text-center text-white pt-3 f-16"><?php echo e(__('Last updated on')); ?>

                <?php echo e(dateFormat($page->updated_at)); ?></p>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-sm-12">
                    <div class="card border">
                        <div class="card-body">
                            <?php echo $page->content; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Required Js -->
    <script src="<?php echo e(asset('js/jquery.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/popper.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/simplebar.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/bootstrap.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/fonts/custom-font.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/pcoded.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/feather.min.js')); ?>"></script>

    <script>
        font_change('Roboto');
    </script>

    <!-- [Page Specific JS] start -->
    <script src="<?php echo e(asset('assets/js/plugins/wow.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/swiper-bundle.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/plugins/ckeditor/classic/ckeditor.js')); ?>"></script>
    <script src="<?php echo e(asset('js/custom.js')); ?>"></script>
    <script>
        // Start [ Menu hide/show on scroll ]
        let ost = 0;
        document.addEventListener('scroll', function() {
            let cOst = document.documentElement.scrollTop;
            if (cOst == 0) {
                document.querySelector('.navbar').classList.add('top-nav-collapse');
            } else if (cOst > ost) {
                document.querySelector('.navbar').classList.add('top-nav-collapse');
                document.querySelector('.navbar').classList.remove('default');
            } else {
                document.querySelector('.navbar').classList.add('default');
                document.querySelector('.navbar').classList.remove('top-nav-collapse');
            }
            ost = cOst;
        });
        // End [ Menu hide/show on scroll ]
        var wow = new WOW({
            animateClass: 'animated'
        });
        wow.init();
        const app_Swiper = new Swiper('.app-slider', {
            loop: true,
            slidesPerView: '1.2',
            centeredSlides: true,
            spaceBetween: 20,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev'
            }
        });
        const choose_Swiper = new Swiper('.choose-slider', {
            direction: 'vertical',
            loop: true,
            centeredSlides: true,
            slidesPerView: '4',
            autoplay: {
                delay: 2500,
                disableOnInteraction: false
            }
        });
        const frameworks_Swiper = new Swiper('.frameworks-slider', {
            loop: true,
            centeredSlides: true,
            spaceBetween: 24,
            slidesPerView: 2,
            pagination: {
                el: '.swiper-pagination',
                dynamicBullets: true,
                clickable: true
            },
            breakpoints: {
                640: {
                    slidesPerView: 2
                },
                768: {
                    slidesPerView: 4
                },
                1024: {
                    slidesPerView: 5
                }
            }
        });
    </script>
    <!-- [Page Specific JS] end -->
</body>

</html>
<?php /**PATH /home/etheonbe/public_html/resources/views/Pages/page.blade.php ENDPATH**/ ?>