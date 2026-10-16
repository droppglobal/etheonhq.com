@php $site = \App\Support\WebsiteSite::for($user); @endphp
@include('theme.head')

{{-- @dd($settings) --}}

<body class="landing-page site-style-{{ $site->design['layout_style'] ?? 'classic' }} site-buttons-{{ $site->design['button_style'] ?? 'rounded' }} {{ $site->rtl() ? 'site-rtl' : '' }}"
    data-pc-preset="{{ !empty($settings['color_type']) && $settings['color_type'] == 'custom' ? 'custom' : $settings['accent_color'] }}"
    data-site="{{ $user->code }}" data-saved-url="{{ route('web.saved', $user->code) }}" data-compare-url="{{ route('web.compare', $user->code) }}"
    data-label-compare="{{ __('Compare') }}" data-label-clear="{{ __('Clear') }}" data-label-max="{{ __('You can compare up to 3 properties.') }}"
    data-pc-sidebar-theme="light" data-pc-sidebar-caption="{{ $settings['sidebar_caption'] }}"
    data-pc-direction="{{ $settings['theme_layout'] }}" data-pc-theme="{{ $settings['theme_mode'] }}">

    <a href="#main-content" class="site-skip">{{ __('Skip to content') }}</a>
    <div class="wrapper ovh">
        <div class="preloader"></div>


        <!-- Main Header Nav -->
        @include('theme.header')


        <div class="hiddenbar-body-ovelay"></div>

        <!-- Mobile Nav  -->
        @include('theme.mobile_nav')



        <div class="body_content" id="main-content" tabindex="-1">
            @yield('content')

            <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
        </div>


    </div>
    <!-- Wrapper End -->


    @include('theme.footer')
    @include('theme.scripts')

    <div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>


</body>

</html>
