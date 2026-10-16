@extends('layouts.app')
@section('page-title')
    {{ __('Website') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Website') }}</li>
@endsection
@php
    $defs = \App\Support\WebsiteSections::definitions();
    $chrome = $sections->firstWhere('section', \App\Support\WebsiteSections::CHROME);
    $pageSections = $sections->reject(fn($s) => $s->section === \App\Support\WebsiteSections::CHROME)->values();
    $activeTab = request('tab', $chrome && request('section') == $chrome->id ? 'chrome' : 'sections');
    $selectedId = (int) request('section', optional($pageSections->first())->id);
    $canEdit = \Gate::check('edit front home page');
    $decoded = $sections->mapWithKeys(fn($s) => [$s->id => \App\Support\WebsiteSections::decode($s)]);
    $translationsFor = function ($s) use ($contentLang) {
        $t = json_decode((string) $s->translations, true) ?: [];
        return $contentLang ? ($t[$contentLang] ?? []) : [];
    };
    $fonts = \App\Support\WebsiteSite::fonts();
@endphp
@push('css-page')
    <style>
        .fh-section-list .list-group-item { cursor: pointer; }
        .fh-section-list .list-group-item.active { background: rgba(var(--bs-primary-rgb), .08); color: inherit; border-color: rgba(var(--bs-primary-rgb), .25); }
        .fh-section-list .list-group-item.fh-off .fh-name { opacity: .5; }
        .fh-thumb { width: 56px; height: 42px; object-fit: cover; }
        .fh-item.ui-sortable-helper, .fh-section-list .ui-sortable-helper { box-shadow: 0 6px 18px rgba(0,0,0,.12); }
        .fh-placeholder { border: 2px dashed rgba(var(--bs-primary-rgb), .35); border-radius: 8px; height: 54px; margin-bottom: 8px; }
        #fh-preview-frame { width: 100%; height: 75vh; border: 1px solid #e5e8ec; border-radius: 8px; transition: width .2s; background: #fff; }
        .fh-style-option input { display: none; }
        .fh-style-option .card { cursor: pointer; border: 2px solid transparent; }
        .fh-style-option input:checked + .card { border-color: rgb(var(--bs-primary-rgb)); }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body pb-0">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="mb-0">{{ __('Your website') }}</h5>
                            <a href="{{ $websiteUrl }}" target="_blank" class="small">{{ $websiteUrl }}</a>
                        </div>
                        <div class="ms-auto d-flex flex-wrap gap-2 align-items-center">
                            @if (count($languages) > 1)
                                <form method="GET" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="tab" value="{{ in_array($activeTab, ['design', 'visitors', 'seo']) ? 'sections' : $activeTab }}">
                                    <label class="small text-muted mb-0 text-nowrap"><i class="ti ti-language"></i> {{ __('Text language') }}</label>
                                    <select name="content_lang" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 170px;">
                                        <option value="">{{ __('Default') }} ({{ \App\Support\WebsiteSite::languageName($design['site_language']) }})</option>
                                        @foreach ($languages as $code => $name)
                                            @continue($code === $design['site_language'])
                                            <option value="{{ $code }}" {{ $contentLang === $code ? 'selected' : '' }}>{{ \App\Support\WebsiteSite::languageName($code) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            <button type="button" class="btn btn-sm btn-light-secondary" data-bs-toggle="modal" data-bs-target="#fh-preview"><i class="ti ti-eye"></i> {{ __('Preview') }}</button>
                            <a href="{{ $websiteUrl }}" target="_blank" class="btn btn-sm btn-secondary"><i class="ti ti-external-link"></i> {{ __('Open website') }}</a>
                        </div>
                    </div>
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link {{ $activeTab === 'sections' ? 'active' : '' }}" data-bs-toggle="tab" href="#fh-tab-sections"><i class="ti ti-layout-rows"></i> {{ __('Home page sections') }}</a></li>
                        <li class="nav-item"><a class="nav-link {{ $activeTab === 'chrome' ? 'active' : '' }}" data-bs-toggle="tab" href="#fh-tab-chrome"><i class="ti ti-layout-navbar"></i> {{ __('Header & Footer') }}</a></li>
                        <li class="nav-item"><a class="nav-link {{ $activeTab === 'design' ? 'active' : '' }}" data-bs-toggle="tab" href="#fh-tab-design"><i class="ti ti-palette"></i> {{ __('Design') }}</a></li>
                        <li class="nav-item"><a class="nav-link {{ $activeTab === 'visitors' ? 'active' : '' }}" data-bs-toggle="tab" href="#fh-tab-visitors"><i class="ti ti-users"></i> {{ __('Visitors') }}
                            @if ($pendingReviews)<span class="badge bg-danger ms-1">{{ $pendingReviews }}</span>@endif</a></li>
                        <li class="nav-item"><a class="nav-link {{ $activeTab === 'seo' ? 'active' : '' }}" data-bs-toggle="tab" href="#fh-tab-seo"><i class="ti ti-world-search"></i> {{ __('SEO & Analytics') }}</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('website-pages.index') }}"><i class="ti ti-file-text"></i> {{ __('Pages') }}</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        {{-- home page sections --}}
                        <div class="tab-pane fade {{ $activeTab === 'sections' ? 'show active' : '' }}" id="fh-tab-sections">
                            <div class="row">
                                <div class="col-lg-4 mb-3">
                                    <p class="small text-muted mb-2">{{ __('Drag to change the order on your home page. Use the switch to show or hide a section.') }}</p>
                                    <ul class="list-group fh-section-list" id="fh-section-list">
                                        @foreach ($pageSections as $s)
                                            @php $def = $defs[$s->section]; $on = \App\Support\WebsiteSections::enabled($decoded[$s->id]); @endphp
                                            <li class="list-group-item d-flex align-items-center gap-2 {{ $s->id === $selectedId ? 'active' : '' }} {{ $on ? '' : 'fh-off' }}" data-id="{{ $s->id }}" data-target="#fh-form-{{ $s->id }}">
                                                @if ($canEdit)<span class="fh-drag text-muted" style="cursor: move;" title="{{ __('Drag to reorder') }}"><i class="ti ti-grip-vertical"></i></span>@endif
                                                <i class="ti {{ $def['icon'] }} f-18 text-secondary"></i>
                                                <span class="fh-name flex-grow-1 text-truncate">{{ $decoded[$s->id]['name'] ?? __($def['title']) }}
                                                    @if (!empty($def['new']))<span class="badge bg-light-primary ms-1">{{ __('New') }}</span>@endif
                                                </span>
                                                @if ($canEdit)
                                                    <div class="form-check form-switch mb-0" title="{{ __('Show on the website') }}">
                                                        <input class="form-check-input fh-toggle" type="checkbox" role="switch" {{ $on ? 'checked' : '' }} data-url="{{ route('front-home.toggle', $s->id) }}">
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-lg-8">
                                    @foreach ($pageSections as $s)
                                        <div class="fh-form-wrap {{ $s->id === $selectedId ? '' : 'd-none' }}" id="fh-form-{{ $s->id }}">
                                            <h5 class="mb-3"><i class="ti {{ $defs[$s->section]['icon'] }}"></i> {{ __($defs[$s->section]['title']) }}</h5>
                                            @include('front-home._form', ['section' => $s, 'def' => $defs[$s->section], 'c' => $decoded[$s->id], 'tr' => $translationsFor($s)])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- header & footer --}}
                        <div class="tab-pane fade {{ $activeTab === 'chrome' ? 'show active' : '' }}" id="fh-tab-chrome">
                            @if ($chrome)
                                <div class="row"><div class="col-xl-9">
                                    <p class="small text-muted">{{ __('Your logo and favicon come from Settings > General. Pages you create under "Pages" can be added to the menu automatically.') }}</p>
                                    @include('front-home._form', ['section' => $chrome, 'def' => $defs[$chrome->section], 'c' => $decoded[$chrome->id], 'tr' => $translationsFor($chrome)])
                                </div></div>
                            @endif
                        </div>

                        {{-- design --}}
                        <div class="tab-pane fade {{ $activeTab === 'design' ? 'show active' : '' }}" id="fh-tab-design">
                            <form method="POST" action="{{ route('front-home.design') }}" class="row">
                                @csrf
                                <div class="col-xl-9">
                                    <h6 class="mb-3">{{ __('Layout style') }}</h6>
                                    <div class="row">
                                        @foreach (['classic' => [__('Classic'), __('The original look: images beside the text, soft shapes.')], 'modern' => [__('Modern'), __('Bigger headings, rounded cards with shadows, more space.')], 'minimal' => [__('Minimal'), __('Clean and flat: white background, thin borders, no shadows.')]] as $value => [$name, $text])
                                            <label class="col-md-4 fh-style-option">
                                                <input type="radio" name="layout_style" value="{{ $value }}" {{ $design['layout_style'] === $value ? 'checked' : '' }}>
                                                <div class="card mb-3"><div class="card-body p-3"><h6 class="mb-1">{{ $name }}</h6><p class="small text-muted mb-0">{{ $text }}</p></div></div>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="form-label">{{ __('Main colour') }}</label>
                                            <input type="color" class="form-control form-control-color w-100" name="primary_color" value="{{ $design['primary_color'] }}">
                                            <small class="text-muted">{{ __('Headings and buttons') }}</small>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="form-label">{{ __('Accent colour') }}</label>
                                            <input type="color" class="form-control form-control-color w-100" name="secondary_color" value="{{ $design['secondary_color'] }}">
                                            <small class="text-muted">{{ __('Icons, links and highlights') }}</small>
                                        </div>
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="form-label">{{ __('Font') }}</label>
                                            <select class="form-select" name="font">
                                                @foreach ($fonts as $value => $font)
                                                    <option value="{{ $value }}" {{ $design['font'] === $value ? 'selected' : '' }}>{{ __($font[0]) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-12 form-group mb-3">
                                            <label class="form-label d-block">{{ __('Button style') }}</label>
                                            @foreach (['rounded' => __('Rounded'), 'pill' => __('Pill'), 'square' => __('Square')] as $value => $name)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="button_style" id="bs_{{ $value }}" value="{{ $value }}" {{ $design['button_style'] === $value ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="bs_{{ $value }}">
                                                        <span class="btn btn-sm btn-secondary" style="pointer-events:none; border-radius: {{ ['rounded' => '8px', 'pill' => '50px', 'square' => '0'][$value] }}">{{ $name }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="form-label" for="color_mode">{{ __('Colour mode') }}</label>
                                            <select class="form-select" name="color_mode" id="color_mode">
                                                @foreach (['light' => __('Light'), 'dark' => __('Dark'), 'auto' => __('Follow the visitor\'s device')] as $value => $name)
                                                    <option value="{{ $value }}" {{ $design['color_mode'] === $value ? 'selected' : '' }}>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-8 form-group mb-3">
                                            <label class="form-label d-block">&nbsp;</label>
                                            <div class="form-check form-switch mt-1">
                                                <input type="hidden" name="mode_switch" value="0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="mode_switch" name="mode_switch" value="1" {{ !empty($design['mode_switch']) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="mode_switch">{{ __('Show a light/dark switch to visitors') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="mt-2 mb-3">{{ __('Languages') }}</h6>
                                    <div class="row">
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="form-label">{{ __('Default language') }}</label>
                                            <select class="form-select" name="site_language">
                                                @foreach ($languages as $code => $name)
                                                    <option value="{{ $code }}" {{ $design['site_language'] === $code ? 'selected' : '' }}>{{ \App\Support\WebsiteSite::languageName($code) }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">{{ __('The language your section texts are written in.') }}</small>
                                        </div>
                                        <div class="col-md-8 form-group mb-3">
                                            <label class="form-label">{{ __('Visitors can switch to') }}</label>
                                            <div>
                                                @foreach ($languages as $code => $name)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" name="site_languages[]" id="sl_{{ $code }}" value="{{ $code }}" {{ in_array($code, $design['site_languages']) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="sl_{{ $code }}">{{ \App\Support\WebsiteSite::languageName($code) }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <small class="text-muted">{{ __('A language switcher appears in the menu when you pick more than one. Write the texts per language with "Text language" at the top.') }}</small>
                                        </div>
                                    </div>
                                    @if ($canEdit)
                                        <div class="text-end"><button type="submit" class="btn btn-secondary"><i class="ti ti-device-floppy"></i> {{ __('Save') }}</button></div>
                                    @endif
                                </div>
                            </form>
                        </div>

                        {{-- search engines, sharing, analytics, own domain --}}
                        <div class="tab-pane fade {{ $activeTab === 'seo' ? 'show active' : '' }}" id="fh-tab-seo">
                            <form method="POST" action="{{ route('front-home.seo') }}" enctype="multipart/form-data" class="row">
                                @csrf
                                <div class="col-xl-9">
                                    <h6 class="mb-1"><i class="ti ti-brand-google"></i> {{ __('Search engines and link previews') }}</h6>
                                    <p class="small text-muted">{{ __('Shown by Google and when someone shares your website on WhatsApp or Facebook. Property and page titles are filled in automatically.') }}</p>
                                    <div class="row">
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-label" for="seo_title">{{ __('Website title') }}</label>
                                            <input type="text" class="form-control" id="seo_title" name="seo_title" maxlength="70" value="{{ old('seo_title', $seo['title']) }}" placeholder="{{ __('e.g. Acme Homes – Flats to rent in Miami') }}">
                                            <small class="text-muted">{{ __('Up to 60 characters works best.') }}</small>
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-label" for="seo_keywords">{{ __('Keywords') }}</label>
                                            <input type="text" class="form-control" id="seo_keywords" name="seo_keywords" maxlength="255" value="{{ old('seo_keywords', $seo['keywords']) }}" placeholder="{{ __('flats to rent, Miami, property management') }}">
                                        </div>
                                        <div class="col-12 form-group mb-3">
                                            <label class="form-label" for="seo_description">{{ __('Description') }}</label>
                                            <textarea class="form-control" id="seo_description" name="seo_description" rows="2" maxlength="320">{{ old('seo_description', $seo['description']) }}</textarea>
                                            <small class="text-muted">{{ __('One or two sentences, up to 160 characters works best.') }}</small>
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-label" for="share_image">{{ __('Share image') }}</label>
                                            <div class="d-flex align-items-center gap-2">
                                                @if ($seo['share_image'])<img src="{{ asset(Storage::url($seo['share_image'])) }}" class="rounded border" style="height:44px" alt="">@endif
                                                <input type="file" accept="image/*" class="form-control" id="share_image" name="share_image">
                                            </div>
                                            @if ($seo['share_image'])
                                                <div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="share_image_remove" value="1" id="share_image_remove"><label class="form-check-label small" for="share_image_remove">{{ __('Remove this image') }}</label></div>
                                            @endif
                                            <small class="text-muted">{{ __('1200 × 630 pixels. Your logo is used when empty.') }}</small>
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-label" for="google_verification">{{ __('Google Search Console code') }}</label>
                                            <input type="text" class="form-control" id="google_verification" name="google_verification" value="{{ old('google_verification', $seo['google_verification']) }}" placeholder='<meta name="google-site-verification" content="…">'>
                                            <small class="text-muted">{{ __('Paste the HTML tag or just the code.') }}</small>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="alert alert-light border small mb-0">
                                                <i class="ti ti-sitemap"></i> {{ __('Your sitemap for Google') }}:
                                                <a href="{{ route('web.sitemap', $loginUser->code) }}" target="_blank">{{ route('web.sitemap', $loginUser->code) }}</a>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h6 class="mb-1"><i class="ti ti-chart-line"></i> {{ __('Analytics') }}</h6>
                                    <p class="small text-muted">{{ __('When the cookie notice is on, these only start after the visitor presses OK.') }}
                                        <a href="{{ route('website-stats') }}">{{ __('See your website stats') }}</a></p>
                                    <div class="row">
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-label" for="ga_id">{{ __('Google Analytics ID') }}</label>
                                            <input type="text" class="form-control" id="ga_id" name="ga_id" value="{{ old('ga_id', $seo['ga_id']) }}" placeholder="G-ABC123XYZ">
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label class="form-label" for="pixel_id">{{ __('Meta (Facebook) Pixel ID') }}</label>
                                            <input type="text" class="form-control" id="pixel_id" name="pixel_id" value="{{ old('pixel_id', $seo['pixel_id']) }}" placeholder="123456789012345" inputmode="numeric">
                                        </div>
                                    </div>

                                    <hr>
                                    <h6 class="mb-1"><i class="ti ti-world"></i> {{ __('Your own domain') }}</h6>
                                    @if ($domainAllowed)
                                        @php $appHost = parse_url(config('app.url'), PHP_URL_HOST); @endphp
                                        <p class="small text-muted">{{ __('Show your website at your own address, like www.acmehomes.com.') }}</p>
                                        <div class="row">
                                            <div class="col-md-6 form-group mb-3">
                                                <label class="form-label" for="domain">{{ __('Domain') }}</label>
                                                <input type="text" class="form-control" id="domain" name="domain" value="{{ old('domain', $seo['domain']) }}" placeholder="www.example.com">
                                            </div>
                                            <div class="col-md-6 form-group mb-3">
                                                <label class="form-label d-block">&nbsp;</label>
                                                @if ($seo['domain'])
                                                    <button type="button" class="btn btn-light-secondary" id="domain-check" data-url="{{ route('front-home.domain-check') }}"><i class="ti ti-plug-connected"></i> {{ __('Check domain') }}</button>
                                                    <span class="small ms-2" id="domain-check-result"></span>
                                                @endif
                                            </div>
                                            <div class="col-12">
                                                <ol class="small text-muted ps-3">
                                                    <li>{{ __('At your domain company, add a DNS record:') }} <code>A</code> {{ __('pointing to') }} <code>{{ gethostbyname($appHost) }}</code> {{ __('or') }} <code>CNAME</code> {{ __('pointing to') }} <code>{{ $appHost }}</code>.</li>
                                                    <li>{{ __('Save the domain here, then press "Check domain". DNS changes can take a few hours.') }}</li>
                                                    <li>{{ __('Ask your provider to switch on HTTPS (SSL) for the domain.') }}</li>
                                                </ol>
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="domain_redirect" name="domain_redirect" value="1" {{ $seo['domain_redirect'] ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="domain_redirect">{{ __('My domain works – send visitors of the old address there') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <p class="small text-muted">{{ __('Your plan does not include your own domain. Upgrade your plan to use an address like www.acmehomes.com.') }}</p>
                                    @endif

                                    @if ($canEdit)
                                        <div class="text-end"><button type="submit" class="btn btn-secondary"><i class="ti ti-device-floppy"></i> {{ __('Save') }}</button></div>
                                    @endif
                                </div>
                            </form>
                        </div>

                        {{-- visitor features --}}
                        <div class="tab-pane fade {{ $activeTab === 'visitors' ? 'show active' : '' }}" id="fh-tab-visitors">
                            <form method="POST" action="{{ route('front-home.visitors') }}" class="row">
                                @csrf
                                <div class="col-xl-9">
                                    <h6 class="mb-1"><i class="ti ti-calendar-event"></i> {{ __('Book a viewing') }}</h6>
                                    <p class="small text-muted">{{ __('Visitors pick a free time on the property page. Booked times disappear for everyone, and each booking appears under Leads > Viewings.') }}</p>
                                    <div class="form-check form-switch mb-3">
                                        <input type="hidden" name="viewings" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="viewings" name="viewings" value="1" {{ $visitors['viewings'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="viewings">{{ __('Let visitors book a viewing time') }}</label>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 form-group mb-3">
                                            <label class="form-label d-block">{{ __('Days') }}</label>
                                            @foreach ([1 => __('Mon'), 2 => __('Tue'), 3 => __('Wed'), 4 => __('Thu'), 5 => __('Fri'), 6 => __('Sat'), 7 => __('Sun')] as $day => $dayName)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="viewing_days[]" id="vd{{ $day }}" value="{{ $day }}" {{ in_array($day, (array) $visitors['viewing_days']) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="vd{{ $day }}">{{ $dayName }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="col-6 col-md-3 form-group mb-3">
                                            <label class="form-label" for="viewing_from">{{ __('From') }}</label>
                                            <input type="time" class="form-control" id="viewing_from" name="viewing_from" value="{{ $visitors['viewing_from'] }}" required>
                                        </div>
                                        <div class="col-6 col-md-3 form-group mb-3">
                                            <label class="form-label" for="viewing_to">{{ __('To') }}</label>
                                            <input type="time" class="form-control" id="viewing_to" name="viewing_to" value="{{ $visitors['viewing_to'] }}" required>
                                        </div>
                                        <div class="col-6 col-md-2 form-group mb-3">
                                            <label class="form-label" for="viewing_slot">{{ __('Each viewing') }}</label>
                                            <select class="form-select" id="viewing_slot" name="viewing_slot">
                                                @foreach ([15, 20, 30, 45, 60, 90, 120] as $min)
                                                    <option value="{{ $min }}" {{ (int) $visitors['viewing_slot'] === $min ? 'selected' : '' }}>{{ __(':count min', ['count' => $min]) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-6 col-md-2 form-group mb-3">
                                            <label class="form-label" for="viewing_ahead">{{ __('Days ahead') }}</label>
                                            <input type="number" min="1" max="60" class="form-control" id="viewing_ahead" name="viewing_ahead" value="{{ $visitors['viewing_ahead'] }}">
                                        </div>
                                        <div class="col-6 col-md-2 form-group mb-3">
                                            <label class="form-label" for="viewing_per_slot">{{ __('Bookings per time') }}</label>
                                            <input type="number" min="1" max="20" class="form-control" id="viewing_per_slot" name="viewing_per_slot" value="{{ $visitors['viewing_per_slot'] }}">
                                        </div>
                                    </div>

                                    <hr>
                                    <h6 class="mb-1"><i class="ti ti-bell"></i> {{ __('New listing alerts') }}</h6>
                                    <p class="small text-muted">{{ __('Visitors leave their email on the properties page and get an email when a new property matches their search. Each sign-up is added to your Leads.') }}
                                        @if ($alertCount)<br><strong>{{ __('Active alerts: :count', ['count' => $alertCount]) }}</strong>@endif
                                    </p>
                                    <div class="form-check form-switch mb-3">
                                        <input type="hidden" name="alerts" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="alerts" name="alerts" value="1" {{ $visitors['alerts'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="alerts">{{ __('Offer email alerts for new listings') }}</label>
                                    </div>

                                    <hr>
                                    <h6 class="mb-1"><i class="ti ti-star"></i> {{ __('Tenant reviews') }}</h6>
                                    <p class="small text-muted">{{ __('Your tenants can write a review from their account. You decide which ones appear in the Testimonials section.') }}</p>
                                    <div class="form-check form-switch mb-2">
                                        <input type="hidden" name="reviews" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="reviews" name="reviews" value="1" {{ $visitors['reviews'] ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reviews">{{ __('Ask tenants for reviews') }}</label>
                                    </div>
                                    <a href="{{ route('website-reviews.index') }}" class="btn btn-sm btn-light-secondary mb-3"><i class="ti ti-star"></i> {{ __('Manage reviews') }}
                                        @if ($pendingReviews)<span class="badge bg-danger ms-1">{{ $pendingReviews }}</span>@endif</a>

                                    @if ($canEdit)
                                        <div class="text-end"><button type="submit" class="btn btn-secondary"><i class="ti ti-device-floppy"></i> {{ __('Save') }}</button></div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- preview --}}
    <div class="modal fade" id="fh-preview" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title">{{ __('Preview') }}</h6>
                    <div class="btn-group btn-group-sm ms-3" role="group">
                        <button type="button" class="btn btn-light-secondary fh-device active" data-width="100%"><i class="ti ti-device-desktop"></i> {{ __('Desktop') }}</button>
                        <button type="button" class="btn btn-light-secondary fh-device" data-width="768px"><i class="ti ti-device-tablet"></i> {{ __('Tablet') }}</button>
                        <button type="button" class="btn btn-light-secondary fh-device" data-width="390px"><i class="ti ti-device-mobile"></i> {{ __('Phone') }}</button>
                    </div>
                    <small class="text-muted ms-3 d-none d-md-inline">{{ __('Shows your saved changes.') }}</small>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <iframe id="fh-preview-frame" data-src="{{ $websiteUrl }}"></iframe>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script>
        $(function() {
            var csrf = '{{ csrf_token() }}';

            // is the owner's domain pointing here yet?
            $('#domain-check').on('click', function() {
                var out = $('#domain-check-result').removeClass('text-success text-danger').text('…');
                $.getJSON($(this).data('url'), function(r) {
                    if (r.ok) {
                        out.addClass('text-success').text(@json(__('Your domain points to this server.')));
                    } else {
                        out.addClass('text-danger').text(r.domain_ip ? @json(__('Your domain points to another server:')) + ' ' + r.domain_ip : @json(__('Your domain is not set up yet.')));
                    }
                });
            });

            // pick a section to edit
            $('#fh-section-list').on('click', '.list-group-item', function(e) {
                if ($(e.target).closest('.form-check, .fh-drag').length) return;
                $('#fh-section-list .list-group-item').removeClass('active');
                $(this).addClass('active');
                $('.fh-form-wrap').addClass('d-none');
                $($(this).data('target')).removeClass('d-none');
                history.replaceState(null, '', '?' + $.param($.extend({}, Object.fromEntries(new URLSearchParams(location.search)), {section: $(this).data('id'), tab: 'sections'})));
            });

            @if ($canEdit)
                // drag & drop order of the sections
                $('#fh-section-list').sortable({
                    handle: '.fh-drag', placeholder: 'fh-placeholder', axis: 'y',
                    update: function() {
                        var ids = $('#fh-section-list .list-group-item').map(function() { return $(this).data('id'); }).get();
                        $.post('{{ route('front-home.order') }}', {_token: csrf, ids: ids})
                            .done(function() { notifier.show('{{ __('Success') }}', '{{ __('Order saved.') }}', 'success', successImg, 2500); })
                            .fail(function() { notifier.show('{{ __('Error') }}', '{{ __('Something went wrong.') }}', 'error', errorImg, 4000); });
                    }
                });

                // show / hide straight from the list
                $('.fh-toggle').on('change', function() {
                    var $t = $(this), on = $t.is(':checked'), $li = $t.closest('.list-group-item');
                    $.post($t.data('url'), {_token: csrf, enabled: on ? 1 : 0}).done(function() {
                        $li.toggleClass('fh-off', !on);
                        $($li.data('target')).find('.fh-enabled-input').prop('checked', on);
                    }).fail(function() { $t.prop('checked', !on); });
                });
            @endif

            // repeatable items: add, remove, reorder
            var counter = Date.now();
            function renumber($wrap) {
                $wrap.find('.fh-count').text($wrap.find('.fh-items .fh-item').length);
            }
            $('.fh-items').sortable({ handle: '.fh-handle', placeholder: 'fh-placeholder', axis: 'y' });
            $(document).on('click', '.fh-add-item', function() {
                var $form = $(this).closest('form'), html = $form.find('.fh-item-template').html().replace(/__INDEX__/g, 'n' + (counter++));
                var $item = $(html);
                $form.find('.fh-items').append($item);
                renumber($form);
                $item.find('input, textarea').filter(':visible').first().trigger('focus');
            });
            $(document).on('click', '.fh-remove-item', function() {
                var $form = $(this).closest('form');
                $(this).closest('.fh-item').remove();
                renumber($form);
            });

            // image chooser: preview the new file, or clear the earlier one
            $(document).on('change', '.fh-image input[type=file]', function() {
                var file = this.files && this.files[0], $box = $(this).closest('.fh-image');
                if (!file) return;
                $box.find('.fh-thumb').attr('src', URL.createObjectURL(file)).removeClass('d-none');
            });
            $(document).on('click', '.fh-clear-image', function() {
                var $box = $(this).closest('.fh-image');
                $box.find('.fh-path').val('');
                $box.find('.fh-thumb').attr('src', '').addClass('d-none');
                $box.find('input[type=file]').val('');
                $(this).addClass('d-none');
            });

            // preview with device sizes; loads when opened so it shows the latest save
            $('#fh-preview').on('shown.bs.modal', function() {
                var $f = $('#fh-preview-frame');
                $f.attr('src', $f.data('src') + (($f.data('src').indexOf('?') > -1) ? '&' : '?') + 't=' + Date.now());
            });
            $('.fh-device').on('click', function() {
                $('.fh-device').removeClass('active');
                $(this).addClass('active');
                $('#fh-preview-frame').css('width', $(this).data('width'));
            });
        });
    </script>
@endpush
