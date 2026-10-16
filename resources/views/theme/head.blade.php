@php
    $routeName = \Request::route()->getName();
    // owner's design, language, header/footer (also sets the page language for the visitor)
    $site = \App\Support\WebsiteSite::for($user);
    $siteName = $site->siteName;
@endphp

<!DOCTYPE html>
<html dir="{{ $site->rtl() ? 'rtl' : 'ltr' }}" lang="{{ ['arabic' => 'ar', 'danish' => 'da', 'dutch' => 'nl', 'english' => 'en', 'french' => 'fr', 'german' => 'de', 'italian' => 'it', 'japanese' => 'ja', 'polish' => 'pl', 'portuguese' => 'pt', 'russian' => 'ru', 'spanish' => 'es'][$site->lang] ?? 'en' }}">

<head>
    {{-- colour mode before first paint: the visitor's own choice, else the owner's setting --}}
    <script>
        (function () {
            var mode = @json($site->design['color_mode'] ?? 'light'), saved = null;
            try { saved = @json((bool) ($site->design['mode_switch'] ?? 0)) ? localStorage.getItem('site-mode-{{ $user->code }}') : null; } catch (e) {}
            var dark = saved ? saved === 'dark' : (mode === 'dark' || (mode === 'auto' && window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches));
            document.documentElement.classList.toggle('site-dark', dark);
        })();
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    @php
        $pageTitle = trim($__env->yieldContent('page-title')) ?: ($site->seo['title'] ?: $siteName);
        $pageDescription = $site->metaDescription();
        $canonical = $site->canonical();
        $shareImage = $site->shareImage();
        $langCodes = ['arabic' => 'ar', 'danish' => 'da', 'dutch' => 'nl', 'english' => 'en', 'french' => 'fr', 'german' => 'de', 'italian' => 'it', 'japanese' => 'ja', 'polish' => 'pl', 'portuguese' => 'pt', 'russian' => 'ru', 'spanish' => 'es'];
    @endphp
    <meta name="author" content="{{ $siteName }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    @if ($pageDescription)
        <meta name="description" content="{{ $pageDescription }}">
    @endif
    @if ($site->seo['keywords'])
        <meta name="keywords" content="{{ $site->seo['keywords'] }}">
    @endif
    @if (!empty($site->meta['noindex']))
        <meta name="robots" content="noindex, follow">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    @if ($site->seo['google_verification'])
        <meta name="google-site-verification" content="{{ $site->seo['google_verification'] }}">
    @endif

    <meta property="og:type" content="{{ $site->meta['type'] ?? 'website' }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    @if ($pageDescription)
        <meta property="og:description" content="{{ $pageDescription }}">
    @endif
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:locale" content="{{ $langCodes[$site->lang] ?? 'en' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    @if ($pageDescription)
        <meta name="twitter:description" content="{{ $pageDescription }}">
    @endif
    <meta name="twitter:image" content="{{ $shareImage }}">

    {{-- the business, for Google --}}
    @php
        $chrome = $site->chrome;
        $org = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateAgent',
            'name' => $siteName,
            'url' => $site->canonical(route('web.page', $user->code)),
            'logo' => $site->logo,
            'image' => $shareImage,
            'description' => $site->seo['description'] ?: null,
            'telephone' => ($chrome['footer_phone'] ?? '') ?: (($chrome['header_phone'] ?? '') ?: null),
            'email' => ($chrome['footer_email'] ?? '') ?: null,
            'address' => !empty($chrome['footer_address']) ? preg_replace('/\s+/', ' ', $chrome['footer_address']) : null,
            'sameAs' => array_values(array_filter([$chrome['fb_link'] ?? '', $chrome['twitter_link'] ?? '', $chrome['insta_link'] ?? '', $chrome['linkedin_link'] ?? '', $chrome['youtube_link'] ?? ''], fn($l) => $l && $l !== '#')) ?: null,
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @stack('schema')

    <!-- css file -->
    <link rel="stylesheet" href="{{ asset('assets/web/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/jquery-ui.min.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/web/css/ace-responsive-menu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/menu.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/slider.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/web/css/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" />


    <link rel="stylesheet" href="{{ asset('assets/css/plugins/notifier.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/web/css/ud-custom-spacing.css') }}">

    <!-- Responsive stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/web/css/responsive.css') }}">
    <!-- Title -->
    <!-- Favicon -->
    <link rel="icon" href="{{ $site->favicon }}">
    <link rel="apple-touch-icon" href="{{ $site->favicon }}">

 <link rel="stylesheet" id="Pstylesheet" href="{{ asset('assets/css/style-preset.css') }}" />
 
    @if (!empty($settings['color_type']) && $settings['color_type'] == 'custom' && !empty($settings['custom_color']))
        <style id="custom-accent-style">
            body[data-pc-preset="custom"] {
                --bs-primary-rgb: {{ str_replace(['--primary-rgb:', '--bs-primary-rgb:', ';'], '', $settings['custom_color']) }};
                --bs-secondary-rgb: {{ str_replace(['--primary-rgb:', '--bs-primary-rgb:', ';'], '', $settings['custom_color']) }};
            }
        </style>
    @endif
 

    @php $font = \App\Support\WebsiteSite::fonts()[$site->design['font']] ?? null; @endphp
    @if ($font && $font[2])
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family={{ $font[2] }}&display=swap" rel="stylesheet">
    @endif
    <style id="website-design">
        body.landing-page {
            --bs-primary-rgb: {{ \App\Support\WebsiteSite::rgb($site->design['primary_color']) }} !important;
            --bs-secondary-rgb: {{ \App\Support\WebsiteSite::rgb($site->design['secondary_color']) }} !important;
            --site-primary: {{ $site->design['primary_color'] }};
            --site-accent: {{ $site->design['secondary_color'] }};
            --site-radius: {{ ['rounded' => '8px', 'pill' => '50px', 'square' => '0'][$site->design['button_style']] ?? '8px' }};
        }
        @if ($font && $font[1])
            body.landing-page, body.landing-page h1, body.landing-page h2, body.landing-page h3, body.landing-page h4, body.landing-page h5,
            body.landing-page h6, body.landing-page p, body.landing-page a, body.landing-page .ff-heading, body.landing-page .ud-btn { font-family: {!! $font[1] !!} !important; }
        @endif
    </style>
    <link rel="stylesheet" href="{{ asset('assets/web/css/website.css') }}?v=3.7.13">
    @stack('css-page')


    <style>
        /* header.nav-homepage-style.at-home3 .ace-responsive-menu a.list-item.active { */
        .custom-active-style {
            color: #2ca58d !important;
            border: 1px solid #000;
            padding: 6px 24px;
            border-radius: 4px;
            /* background-color: #fff; */
            font-weight: 500;
            /* box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); */
            transition: all 0.3s ease;
        }
    </style>

    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
     <link rel="stylesheet" href="{{ asset('assets/web/css/accent-color.css') }}">
 


    @include('theme._analytics')
</head>
