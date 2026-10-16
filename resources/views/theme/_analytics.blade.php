{{-- Owner's Google Analytics / Meta Pixel. With the cookie notice on, they start only after the visitor presses OK. --}}
@php
    $gaId = $site->seo['ga_id'] ?? '';
    $pixelId = $site->seo['pixel_id'] ?? '';
    $needsConsent = ($site->chrome['cookie_banner'] ?? 'deactive') === 'active';
@endphp
@if ($gaId || $pixelId)
    <script>
        window.siteAnalytics = function () {
            if (window.siteAnalyticsStarted) return;
            window.siteAnalyticsStarted = true;
            @if ($gaId)
                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://www.googletagmanager.com/gtag/js?id=' + @json($gaId);
                document.head.appendChild(s);
                window.dataLayer = window.dataLayer || [];
                window.gtag = function () { dataLayer.push(arguments); };
                gtag('js', new Date());
                gtag('config', @json($gaId));
            @endif
            @if ($pixelId)
                !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', @json($pixelId));
                fbq('track', 'PageView');
            @endif
        };
        @if ($needsConsent)
            try { if (localStorage.getItem('site-cookies-{{ $user->code }}')) window.siteAnalytics(); } catch (e) {}
        @else
            window.siteAnalytics();
        @endif
    </script>
@endif
