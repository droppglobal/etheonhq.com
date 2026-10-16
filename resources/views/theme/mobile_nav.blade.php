@php
    $routeName = \Request::route()->getName();
    $c = $site->chrome;
@endphp
<div id="page" class="mobilie_header_nav stylehome1">
    <div class="mobile-menu">
        <div class="header bdrb1">
            <div class="menu_and_widgets">
                <div class="mobile_menu_bar d-flex justify-content-between align-items-center">
                    <a class="mobile_logo" href="{{ route('web.page', $user->code) }}">
                        <img src="{{ $site->logo }}" alt="{{ $site->companyName }}" class="img-fluid" style="max-width: 200px; max-height: 48px;">
                    </a>
                    <div class="right-side text-end">
                        <a href="{{ route('web.saved', $user->code) }}" class="site-saved-link" aria-label="{{ __('Saved properties') }}"><i class="far fa-heart"></i><span class="site-saved-count" data-saved-count hidden>0</span></a>
                        @if (!empty($site->design['mode_switch']))
                            <button type="button" class="site-mode-btn" data-mode-switch="{{ $user->code }}" aria-label="{{ __('Switch light or dark mode') }}"><i class="fas fa-moon"></i><i class="fas fa-sun"></i></button>
                        @endif
                        <a class="menubar ml30" href="#menu"><img src="{{ asset('assets/web/images/mobile-dark-nav-icon.svg') }}" alt="{{ __('Menu') }}"></a>
                    </div>
                </div>
            </div>
            <div class="posr">
                <div class="mobile_menu_close_btn"><span class="far fa-times"></span></div>
            </div>
        </div>
    </div>
    <nav id="menu" class="">
        <ul>
            @include('theme._menu', ['linkClass' => ''])
            @if (!empty($c['header_button_text']) && !empty($c['header_button_link']))
                <li><a href="{{ $c['header_button_link'] }}">{{ $c['header_button_text'] }}</a></li>
            @endif
            @if (count($site->languages) > 1)
                @foreach ($site->languages as $code => $name)
                    <li><a href="{{ route('web.lang', [$user->code, $code]) }}">{{ $code === $site->lang ? '✓ ' : '' }}{{ $name }}</a></li>
                @endforeach
            @endif
        </ul>
    </nav>
</div>
