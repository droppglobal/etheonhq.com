@php
    $routeName = \Request::route()->getName();
    $c = $site->chrome;
    $wa = $site->whatsappNumber();
@endphp

@if (!empty($c['header_phone']) || $wa)
    <div class="site-topbar d-none d-md-block">
        <div class="container d-flex justify-content-end gap-4 py-1 small">
            @if (!empty($c['header_phone']))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $c['header_phone']) }}"><i class="fas fa-phone-alt me-1"></i> {{ $c['header_phone'] }}</a>
            @endif
            @if ($wa)
                <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp me-1"></i> WhatsApp</a>
            @endif
        </div>
    </div>
@endif

<header class="header-nav nav-homepage-style at-home3 stricky main-menu border-0 ">
    <nav class="posr">
        <div class="container posr">
            <div class="row align-items-center justify-content-between">
                <div class="col-auto px-0 px-xl-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="logos">
                            <a class="header-logo logo1 landing-logo" href="{{ route('web.page', $user->code) }}">
                                <img src="{{ $site->logo }}" class="img-fluid site-logo" alt="{{ $site->companyName ?: __('Home') }}">
                            </a>
                            <a class="header-logo logo2 landing-logo" href="{{ route('web.page', $user->code) }}">
                                <img src="{{ $site->logo }}" class="img-fluid site-logo" alt="{{ $site->companyName ?: __('Home') }}">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-auto pe-0 pe-xl-3">
                    <div class="d-flex align-items-center">
                        <ul id="respMenu" class="ace-responsive-menu" data-menu-style="horizontal">
                            @include('theme._menu', ['linkClass' => 'list-item mr5'])
                        </ul>
                        @if (count($site->languages) > 1)
                            <div class="dropdown ms-2 site-lang">
                                <a class="dropdown-toggle small fw500" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="far fa-globe me-1"></i>{{ $site->languages[$site->lang] ?? '' }}</a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach ($site->languages as $code => $name)
                                        <li><a class="dropdown-item {{ $code === $site->lang ? 'active' : '' }}" href="{{ route('web.lang', [$user->code, $code]) }}">{{ $name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <a href="{{ route('web.saved', $user->code) }}" class="site-saved-link ms-2" aria-label="{{ __('Saved properties') }}"><i class="far fa-heart"></i><span class="site-saved-count" data-saved-count hidden>0</span></a>
                        @if (!empty($site->design['mode_switch']))
                            <button type="button" class="site-mode-btn ms-2" data-mode-switch="{{ $user->code }}" aria-label="{{ __('Switch light or dark mode') }}"><i class="fas fa-moon"></i><i class="fas fa-sun"></i></button>
                        @endif
                        @if (!empty($c['header_button_text']) && !empty($c['header_button_link']))
                            <a href="{{ $c['header_button_link'] }}" class="ud-btn btn-thm2 site-header-btn ms-3 d-none d-xl-inline-block">{{ $c['header_button_text'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
