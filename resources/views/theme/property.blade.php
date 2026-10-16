@extends('theme.main')
@section('page-title', __('Properties') . ' – ' . \App\Support\WebsiteSite::for($user)->siteName)
@section('content')
    @php
        $banner = App\Models\Additional::where('section', 'Section 3')->where('parent_id', $user->id)->first();
        $banner = !empty($banner->content_value) ? (json_decode($banner->content_value, true) ?: []) : [];
        $showBanner = empty($banner['section_enabled']) || $banner['section_enabled'] == 'active';
        $bannerImage = !empty($banner['sec3_banner_image_path']) ? asset(Storage::url($banner['sec3_banner_image_path'])) : '';
        \App\Support\WebsiteSite::for($user)->setMeta(['description' => $banner['sec3_sub_title'] ?? '', 'image' => $bannerImage,
            'noindex' => \App\Support\PropertySearch::active($f) ? 1 : null]); // filtered result pages are not indexed
    @endphp

    <section class="site-page-head {{ $showBanner && $bannerImage ? 'has-image' : '' }}" @if ($showBanner && $bannerImage) style="--head-image: url('{{ $bannerImage }}')" @endif>
        <div class="container">
            <h1 class="site-page-title">{{ $showBanner && !empty($banner['sec3_title']) ? $banner['sec3_title'] : __('Find Your Perfect Property') }}</h1>
            @if ($showBanner && !empty($banner['sec3_sub_title']))
                <p class="site-page-sub">{{ $banner['sec3_sub_title'] }}</p>
            @endif
            <div class="site-search-card">
                @include('theme._search_form', ['compact' => false])
            </div>
        </div>
    </section>

    <section class="site-section pt-4">
        <div class="container">
            <div class="d-flex justify-content-end mb-3">
                <div class="site-view-toggle" role="group" aria-label="{{ __('View') }}">
                    <button type="button" data-view="list" class="active" aria-pressed="true"><i class="fas fa-table-list me-1"></i>{{ __('List') }}</button>
                    <button type="button" data-view="map" aria-pressed="false"><i class="fas fa-map me-1"></i>{{ __('Map') }}</button>
                </div>
            </div>
            <div class="site-results-layout" id="results-layout">
                <div id="package-wrapper" class="site-results">
                    @include('theme.propertybox')
                </div>
                <div class="site-map-pane" aria-label="{{ __('Map') }}">
                    <span class="site-map-label" data-map-label></span>
                    <iframe title="{{ __('Map') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" data-map-frame></iframe>
                </div>
            </div>

            @if (!empty(\App\Support\WebsiteSite::visitors($user->id)['alerts']))
                {{-- new listing alerts: saves the current search --}}
                <div class="site-alert-box" id="alerts">
                    <div class="site-alert-icon"><i class="fas fa-bell"></i></div>
                    <div class="flex-grow-1">
                        <h2 class="h5 mb-1">{{ __('Get new listings by email') }}</h2>
                        <p class="text-muted mb-3">{{ __('We will email you when a new property matches your current search. You can stop the emails at any time.') }}</p>
                        <form method="POST" action="{{ route('web.alerts.store', $user->code) }}" class="row g-2 align-items-end" data-alert-form>
                            @csrf
                            <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                            <div class="col-md-4">
                                <label class="form-label" for="alert_name">{{ __('Name') }}</label>
                                <input type="text" class="form-control" id="alert_name" name="alert_name" maxlength="120" autocomplete="name">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="alert_email">{{ __('Email') }}</label>
                                <input type="email" class="form-control" id="alert_email" name="alert_email" required maxlength="191" autocomplete="email">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="ud-btn btn-thm2 w-100">{{ __('Notify me') }}</button>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="alert_consent" name="alert_consent" value="1" required>
                                    <label class="form-check-label small" for="alert_consent">{{ __('I agree to receive emails about new properties.') }}</label>
                                </div>
                            </div>
                            <div data-alert-filters hidden></div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('script-page')
    <script>
        (function() {
            var form = document.querySelector('[data-search-form]:not(.site-search-compact)');
            var box = document.getElementById('package-wrapper');
            if (!form || !box || !window.fetch) return;

            function load(url, push) {
                box.classList.add('is-loading');
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { if (!r.ok) throw r; return r.text(); })
                    .then(function(html) {
                        box.innerHTML = html;
                        box.classList.remove('is-loading');
                        if (push) window.history.pushState({ search: 1 }, '', url);
                        window.SiteUI && window.SiteUI.init(box);
                    })
                    .catch(function() { window.location = url; });
            }
            function formUrl() {
                var params = new URLSearchParams(new FormData(form));
                Array.from(params.keys()).forEach(function(k) { if (params.get(k) === '') params.delete(k); });
                var qs = params.toString();
                return form.action + (qs ? '?' + qs : '');
            }

            form.addEventListener('submit', function(e) { e.preventDefault(); load(formUrl(), true); });
            form.querySelectorAll('select, input[type=checkbox]').forEach(function(el) {
                el.addEventListener('change', function() { load(formUrl(), true); });
            });
            document.addEventListener('click', function(e) {
                var a = e.target.closest('#package-wrapper .page-link[href], [data-search-reset]');
                if (!a) return;
                e.preventDefault();
                if (a.hasAttribute('data-search-reset')) { form.reset(); form.querySelectorAll('input:not([type=checkbox]), select').forEach(function(el) { el.value = ''; }); form.querySelector('[name=sort]').value = 'newest'; }
                load(a.href, true);
                box.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            window.addEventListener('popstate', function() { window.location.reload(); });

            // the alert remembers the search currently shown
            var alertForm = document.querySelector('[data-alert-form]');
            if (alertForm) {
                alertForm.addEventListener('submit', function() {
                    var holder = alertForm.querySelector('[data-alert-filters]');
                    holder.innerHTML = '';
                    new FormData(form).forEach(function(value, key) {
                        if (value === '' || key === 'sort') return;
                        var i = document.createElement('input');
                        i.type = 'hidden'; i.name = key; i.value = value;
                        holder.appendChild(i);
                    });
                });
            }

            // list / map view: the map shows the property you point at (remembered in this browser)
            var layout = document.getElementById('results-layout'), frame = layout.querySelector('[data-map-frame]'),
                label = layout.querySelector('[data-map-label]'), shown = '', timer;
            function showOnMap(card) {
                if (!card || !card.dataset.address || card.dataset.address === shown) return;
                shown = card.dataset.address;
                frame.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(shown) + '&output=embed';
                label.textContent = card.dataset.name;
                box.querySelectorAll('.site-card.is-active').forEach(function(c) { c.classList.remove('is-active'); });
                card.classList.add('is-active');
            }
            function setView(view) {
                layout.classList.toggle('map-mode', view === 'map');
                document.querySelectorAll('[data-view]').forEach(function(b) {
                    var on = b.getAttribute('data-view') === view;
                    b.classList.toggle('active', on);
                    b.setAttribute('aria-pressed', on ? 'true' : 'false');
                });
                try { localStorage.setItem('site-view', view); } catch (e) {}
                if (view === 'map') { shown = ''; showOnMap(box.querySelector('.site-card')); }
            }
            document.querySelectorAll('[data-view]').forEach(function(b) {
                b.addEventListener('click', function() { setView(b.getAttribute('data-view')); });
            });
            ['mouseover', 'focusin'].forEach(function(type) {
                box.addEventListener(type, function(e) {
                    if (!layout.classList.contains('map-mode')) return;
                    var card = e.target.closest('.site-card');
                    clearTimeout(timer);
                    timer = setTimeout(function() { showOnMap(card); }, 250);
                });
            });
            new MutationObserver(function() { if (layout.classList.contains('map-mode')) { shown = ''; showOnMap(box.querySelector('.site-card')); } })
                .observe(box, { childList: true });
            try { if (localStorage.getItem('site-view') === 'map') setView('map'); } catch (e) {}
        })();
    </script>
@endpush
