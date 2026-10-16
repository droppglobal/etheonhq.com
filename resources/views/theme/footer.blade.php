@php
    $c = $site->chrome;
    $socials = array_filter([
        'fab fa-facebook-f' => $c['fb_link'] ?? '', 'fab fa-twitter' => $c['twitter_link'] ?? '', 'fab fa-instagram' => $c['insta_link'] ?? '',
        'fab fa-linkedin-in' => $c['linkedin_link'] ?? '', 'fab fa-youtube' => $c['youtube_link'] ?? '',
    ], fn($l) => $l !== '' && $l !== '#');
    $routeName = \Request::route()->getName();
    $wa = $site->whatsappNumber();
    $tenantLinks = ($c['show_tenant_links'] ?? 'active') === 'active';
    $appLinks = array_filter(['android' => $c['app_android'] ?? '', 'ios' => $c['app_ios'] ?? ''], fn($l) => $l !== '' && $l !== '#');
    $legal = \App\Models\WebsitePage::where('parent_id', $user->id)->where('is_active', 1)
        ->whereIn('id', array_filter([(int) ($c['privacy_page'] ?? 0), (int) ($c['terms_page'] ?? 0)]))->get()->keyBy('id');
    $legalLinks = collect(['privacy_page' => __('Privacy policy'), 'terms_page' => __('Terms')])
        ->map(fn($label, $key) => isset($legal[(int) ($c[$key] ?? 0)]) ? [$label, route('web.custom.page', [$user->code, $legal[(int) $c[$key]]->slug])] : null)->filter();
    $hasContact = !empty($c['footer_address']) || !empty($c['footer_phone']) || !empty($c['footer_email']) || !empty($c['footer_hours']) || $wa;
@endphp
<section class="footer-style1 site-footer pt60 pb-0">
    <div class="container">
        <div class="row pb-4">
            <div class="{{ $tenantLinks || $appLinks ? 'col-lg-4' : 'col-lg-5' }} mb-4">
                <img src="{{ $site->logo }}" alt="{{ $site->companyName }}" class="site-footer-logo mb-3">
                @if (!empty($c['Sec8_info']))
                    <p class="text-white-light mb-3">{{ $c['Sec8_info'] }}</p>
                @endif
                @if ($socials)
                    <div class="social-style1">
                        @foreach ($socials as $icon => $link)
                            <a href="{{ $link }}" target="_blank" rel="noopener" class="me-2"><i class="{{ $icon }} list-inline-item"></i></a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="col-6 {{ $tenantLinks || $appLinks ? 'col-lg-2' : 'col-lg-3' }} mb-4">
                <h6 class="text-white mb-3">{{ __('Quick links') }}</h6>
                <ul class="list-unstyled site-footer-links mb-0">
                    @include('theme._menu', ['linkClass' => ''])
                </ul>
            </div>
            @if ($tenantLinks || $appLinks)
                <div class="col-6 col-lg-3 mb-4">
                    <h6 class="text-white mb-3">{{ __('For tenants') }}</h6>
                    @if ($tenantLinks)
                        <ul class="list-unstyled site-footer-links mb-3">
                            <li><a href="{{ \App\Support\WebsiteSite::appUrl('login') }}"><i class="fas fa-right-to-bracket me-2"></i>{{ __('Tenant login') }}</a></li>
                            <li><a href="{{ \App\Support\WebsiteSite::appUrl('invoice.index') }}"><i class="fas fa-credit-card me-2"></i>{{ __('Pay rent') }}</a></li>
                            <li><a href="{{ \App\Support\WebsiteSite::appUrl('maintenance-request.index') }}"><i class="fas fa-wrench me-2"></i>{{ __('Report a repair') }}</a></li>
                        </ul>
                    @endif
                    @if ($appLinks)
                        <div class="site-store-badges">
                            @if (!empty($appLinks['android']))
                                <a href="{{ $appLinks['android'] }}" target="_blank" rel="noopener" class="site-store-badge"><i class="fab fa-google-play"></i><span><small>{{ __('Get it on') }}</small>Google Play</span></a>
                            @endif
                            @if (!empty($appLinks['ios']))
                                <a href="{{ $appLinks['ios'] }}" target="_blank" rel="noopener" class="site-store-badge"><i class="fab fa-apple"></i><span><small>{{ __('Download on the') }}</small>App Store</span></a>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
            @if ($hasContact)
            <div class="col-sm-6 {{ $tenantLinks || $appLinks ? 'col-lg-3' : 'col-lg-4' }} mb-4">
                <h6 class="text-white mb-3">{{ __('Contact') }}</h6>
                <ul class="list-unstyled site-footer-contact mb-0">
                    @if (!empty($c['footer_address']))<li><i class="fas fa-map-marker-alt me-2"></i><span style="white-space: pre-line">{{ $c['footer_address'] }}</span></li>@endif
                    @if (!empty($c['footer_phone']))<li><i class="fas fa-phone-alt me-2"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $c['footer_phone']) }}">{{ $c['footer_phone'] }}</a></li>@endif
                    @if (!empty($c['footer_email']))<li><i class="fas fa-envelope me-2"></i><a href="mailto:{{ $c['footer_email'] }}">{{ $c['footer_email'] }}</a></li>@endif
                    @if (!empty($c['footer_hours']))<li><i class="fas fa-clock me-2"></i>{{ $c['footer_hours'] }}</li>@endif
                    @if ($wa)<li><i class="fab fa-whatsapp me-2"></i><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">WhatsApp</a></li>@endif
                </ul>
            </div>
            @endif
        </div>
    </div>
    <div class="container white-bdrt1 py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="copyright-text mb-0 text-white-light ff-heading small text-center text-md-start">
                {{ !empty($c['copyright']) ? $c['copyright'] : '© ' . date('Y') . ' ' . $site->siteName }}
            </p>
            @if ($legalLinks->isNotEmpty())
                <ul class="list-inline mb-0 small site-legal-links">
                    @foreach ($legalLinks as [$label, $url])
                        <li class="list-inline-item"><a href="{{ $url }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>

{{-- floating WhatsApp button --}}
@if ($wa && ($c['whatsapp_float'] ?? 'deactive') === 'active')
    <a href="https://wa.me/{{ $wa }}" class="site-wa-float" target="_blank" rel="noopener" aria-label="{{ __('Chat on WhatsApp') }}"><i class="fab fa-whatsapp"></i></a>
@endif

{{-- cookie notice; the choice is remembered in the visitor's browser --}}
@if (($c['cookie_banner'] ?? 'deactive') === 'active')
    @php
        $cookieDefault = 'We use cookies to keep the site working and to understand how it is used.';
        $cookieText = trim($c['cookie_text'] ?? '');
    @endphp
    <div class="site-cookie" role="region" aria-label="{{ __('Cookie notice') }}" data-cookie-notice="{{ $user->code }}" hidden>
        <p class="mb-0"><i class="fas fa-cookie-bite me-2"></i>{{ $cookieText !== '' && $cookieText !== $cookieDefault ? $cookieText : __($cookieDefault) }}
            @if ($legalLinks->has('privacy_page'))
                <a href="{{ $legalLinks['privacy_page'][1] }}">{{ __('Privacy policy') }}</a>
            @endif
        </p>
        <button type="button" class="ud-btn btn-thm2 site-cookie-ok">{{ __('OK') }}</button>
    </div>
@endif
