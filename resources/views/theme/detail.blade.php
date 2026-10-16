@extends('theme.main')
@php
    $site = \App\Support\WebsiteSite::for($user);
    $facts = \App\Support\PropertySearch::facts($property, $site->featuredIds());
    $photos = collect([optional($property->thumbnail)->image ? 'upload/property/thumbnail/' . $property->thumbnail->image : null])
        ->merge($property->propertyImages->map(fn($i) => $i->image ? 'upload/property/image/' . $i->image : null))
        ->filter()->unique()->values();
    if ($photos->isEmpty()) {
        $photos = collect(['upload/property/thumbnail/default.jpg']);
    }
    $type = \App\Models\Property::types()[$property->type] ?? '';
    $isRent = $property->listing_type !== 'sell';
    $place = trim(implode(', ', array_filter([$property->city, $property->state])));
    $fullAddress = trim(implode(', ', array_filter([$property->address, $property->city, $property->state, $property->zip_code, $property->country])));
    $mapLink = $property->map_link && preg_match('#^https?://#i', $property->map_link) ? $property->map_link : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($fullAddress);
    $phone = $site->chrome['header_phone'] ?? ($site->chrome['footer_phone'] ?? '');
    $wa = $site->whatsappNumber();
    $pageUrl = url()->current();
    $freeUnits = $units->filter(fn($u) => (string) $u->is_occupied === '0');
    $kitchens = $units->pluck('kitchen')->filter();
    $slots = \App\Support\ViewingSlots::free($user->id);
    $site->setMeta([
        'description' => $property->description ?: trim(($isRent ? __('For rent') : __('For sale')) . ' · ' . $place),
        'image' => asset(Storage::url($photos->first())),
        'type' => 'article',
    ]);
    $listing = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateListing',
        'name' => $property->name,
        'url' => $site->canonical(),
        'description' => $site->metaDescription(),
        'image' => $photos->take(6)->map(fn($p) => \App\Support\WebsiteSite::safeUrl(asset(Storage::url($p))))->values()->all(),
        'datePosted' => optional($property->created_at)->toDateString(),
        'offers' => $property->price ? ['@type' => 'Offer', 'price' => (float) $property->price, 'priceCurrency' => strtoupper(substr((string) (settingsById($user->id)['CURRENCY'] ?? 'USD'), 0, 3)) ?: 'USD',
            'availability' => $isRent && $freeUnits->isEmpty() && $units->isNotEmpty() ? 'https://schema.org/SoldOut' : 'https://schema.org/InStock'] : null,
        'address' => array_filter(['@type' => 'PostalAddress', 'streetAddress' => $property->address, 'addressLocality' => $property->city, 'addressRegion' => $property->state, 'postalCode' => $property->zip_code, 'addressCountry' => $property->country]),
    ]);
    $crumbs = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('Home'), 'item' => $site->canonical(route('web.page', $user->code))],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('Properties'), 'item' => $site->canonical(route('property.home', ['code' => $user->code]))],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $property->name],
    ]];
@endphp
@push('schema')
    <script type="application/ld+json">{!! json_encode($listing, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode($crumbs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('page-title', ucfirst($property->name) . ($place ? ', ' . $place : '') . ' – ' . $site->siteName)
@section('content')
    <section class="site-detail-head">
        <div class="container">
            <nav aria-label="{{ __('Breadcrumb') }}" class="site-crumbs">
                <a href="{{ route('web.page', $user->code) }}">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('property.home', ['code' => $user->code]) }}">{{ __('Properties') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ ucfirst($property->name) }}</span>
            </nav>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div>
                    <div class="site-tags position-static mb-2">
                        <span class="site-tag site-tag-{{ $isRent ? 'rent' : 'sale' }}">{{ $isRent ? __('For rent') : __('For sale') }}</span>
                        @foreach ($facts['tags'] as [$key, $label])
                            <span class="site-tag site-tag-{{ $key }}">{{ $label }}</span>
                        @endforeach
                    </div>
                    <h1 class="site-detail-title">{{ ucfirst($property->name) }}</h1>
                    @if ($place)<p class="text-muted mb-0"><i class="fas fa-map-marker-alt me-1"></i>{{ $place }}</p>@endif
                </div>
                @if ($property->price)
                    <div class="site-detail-price">
                        <small>{{ $isRent ? __('Rent') : __('Price') }}</small>
                        <strong>{{ \App\Support\WebsiteSite::price($property->price) }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- photos --}}
    <div class="container" data-lightbox>
        <div class="site-gallery site-gallery-{{ min(5, $photos->count()) }}">
            @foreach ($photos->take(5) as $i => $photo)
                <a href="{{ asset(Storage::url($photo)) }}" class="site-gallery-item" data-lightbox-item="{{ $i }}" aria-label="{{ __('Open photo :n of :total', ['n' => $i + 1, 'total' => $photos->count()]) }}">
                    <img src="{{ \App\Support\WebsiteImage::url($photo, $i ? 640 : 1280) }}" decoding="async" alt="{{ $property->name }} – {{ __('photo') }} {{ $i + 1 }}" {{ $i ? 'loading=lazy' : '' }}>
                    @if ($i === 4 && $photos->count() > 5)
                        <span class="site-gallery-more">+{{ $photos->count() - 5 }}</span>
                    @endif
                </a>
            @endforeach
            @foreach ($photos->slice(5) as $i => $photo)
                <a href="{{ asset(Storage::url($photo)) }}" class="d-none" data-lightbox-item="{{ $i }}" tabindex="-1" aria-hidden="true"></a>
            @endforeach
        </div>
        @if ($photos->count() > 1)
            <button type="button" class="site-gallery-all" data-lightbox-open><i class="fas fa-images me-1"></i> {{ __('Show all photos (:count)', ['count' => $photos->count()]) }}</button>
        @endif
    </div>

    <section class="site-section pt-4 site-detail-body">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    {{-- key facts --}}
                    <ul class="site-keyfacts">
                        @if ($type)<li><i class="fas fa-building"></i><span>{{ __('Type') }}</span><strong>{{ $type }}</strong></li>@endif
                        @if ($facts['beds'] !== '')<li><i class="fas fa-bed"></i><span>{{ __('Bedrooms') }}</span><strong>{{ $facts['beds'] }}</strong></li>@endif
                        @if ($facts['baths'] !== '')<li><i class="fas fa-bath"></i><span>{{ __('Bathrooms') }}</span><strong>{{ $facts['baths'] }}</strong></li>@endif
                        @if ($kitchens->count())<li><i class="fas fa-utensils"></i><span>{{ __('Kitchens') }}</span><strong>{{ $kitchens->min() == $kitchens->max() ? $kitchens->max() : $kitchens->min() . '–' . $kitchens->max() }}</strong></li>@endif
                        @if ($facts['units'])<li><i class="fas fa-door-open"></i><span>{{ __('Units') }}</span><strong>{{ $facts['units'] }}</strong></li>@endif
                        @if ($isRent && $facts['units'])<li><i class="fas fa-circle-check"></i><span>{{ __('Available') }}</span><strong>{{ $facts['vacant'] }}</strong></li>@endif
                    </ul>

                    @if (trim(strip_tags((string) $property->description)) !== '')
                        <div class="site-block">
                            <h2 class="site-block-title">{{ __('About this property') }}</h2>
                            <div class="property-description">{!! \App\Support\WebsiteSite::cleanHtml($property->description) !!}</div>
                        </div>
                    @endif

                    @if ($selectedAmenities->count())
                        <div class="site-block">
                            <h2 class="site-block-title">{{ __('Amenities') }}</h2>
                            <div class="row g-3">
                                @foreach ($selectedAmenities as $amenity)
                                    <div class="col-6 col-md-4">
                                        <div class="site-amenity">
                                            @if ($amenity->image)
                                                <img src="{{ fetch_file('upload/amenity/' . $amenity->image) }}" alt="" loading="lazy">
                                            @else
                                                <i class="fas fa-check"></i>
                                            @endif
                                            <span>{{ $amenity->name }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($selectedAdvantages->count())
                        <div class="site-block">
                            <h2 class="site-block-title">{{ __('Advantages') }}</h2>
                            <ul class="site-checklist">
                                @foreach ($selectedAdvantages as $advantage)
                                    <li><i class="fas fa-check-circle"></i> {{ $advantage->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($units->isNotEmpty())
                        <div class="site-block">
                            <h2 class="site-block-title">{{ __('Units') }}</h2>
                            <div class="row g-3">
                                @foreach ($units as $unit)
                                    @php $free = (string) $unit->is_occupied === '0'; @endphp
                                    <div class="col-md-6">
                                        <div class="site-unit {{ $free ? 'is-free' : '' }}">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h3 class="h6 mb-0">{{ ucfirst($unit->name) }}</h3>
                                                @if ($isRent)
                                                    <span class="site-unit-status">{{ $free ? __('Available') : __('Let') }}</span>
                                                @endif
                                            </div>
                                            <ul class="site-facts border-0 pt-0">
                                                <li><i class="fas fa-bed"></i> {{ (int) $unit->bedroom }} <span>{{ __('Beds') }}</span></li>
                                                <li><i class="fas fa-bath"></i> {{ (int) $unit->baths }} <span>{{ __('Baths') }}</span></li>
                                                <li><i class="fas fa-utensils"></i> {{ (int) $unit->kitchen }} <span>{{ __('Kitchen') }}</span></li>
                                            </ul>
                                            @if ($isRent && $unit->rent > 0)
                                                <p class="site-unit-rent mb-0">{{ \App\Support\WebsiteSite::price($unit->rent) }} @if ($unit->rent_type)<small>/ {{ __(ucfirst($unit->rent_type)) }}</small>@endif</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($fullAddress)
                        <div class="site-block">
                            <h2 class="site-block-title">{{ __('Location') }}</h2>
                            <p class="mb-3"><i class="fas fa-map-marker-alt me-1"></i> {{ $fullAddress }}</p>
                            <div class="site-map-frame">
                                <iframe src="https://maps.google.com/maps?q={{ urlencode($fullAddress) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('Map') }}"></iframe>
                            </div>
                            <a href="{{ $mapLink }}" target="_blank" rel="noopener" class="d-inline-block mt-2 small">{{ __('Open in Google Maps') }} <i class="fas fa-up-right-from-square ms-1"></i></a>
                        </div>
                    @endif
                </div>

                {{-- enquiry box --}}
                <div class="col-lg-4">
                    <aside class="site-enquiry" id="enquiry">
                        @if ($property->price)
                            <p class="site-enquiry-price mb-1">{{ \App\Support\WebsiteSite::price($property->price) }}</p>
                        @endif
                        @if ($isRent && $freeUnits->isNotEmpty())
                            <p class="site-card-avail mt-0 mb-3"><i class="fas fa-circle-check"></i> {{ __('Units available: :count', ['count' => $freeUnits->count()]) }}</p>
                        @endif
                        <h2 class="h5 mb-3">{{ __('Interested in this property?') }}</h2>
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        {{ Form::open(['route' => ['web.enquiry', 'code' => $code], 'method' => 'post']) }}
                        {{ Form::hidden('property_id', $property->id) }}
                        <div style="position:absolute;left:-9999px" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <div class="mb-2">
                            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                            {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'name']) }}
                        </div>
                        <div class="mb-2">
                            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                            {{ Form::email('email', null, ['class' => 'form-control', 'required' => 'required', 'autocomplete' => 'email']) }}
                        </div>
                        <div class="mb-2">
                            {{ Form::label('phone', __('Phone'), ['class' => 'form-label']) }}
                            {{ Form::tel('phone', null, ['class' => 'form-control', 'autocomplete' => 'tel']) }}
                        </div>
                        @if ($units->count() > 1)
                            <div class="mb-2">
                                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                                {{ Form::select('unit_id', ['' => __('Any unit')] + $units->pluck('name', 'id')->toArray(), null, ['class' => 'form-select']) }}
                            </div>
                        @endif
                        @if ($slots)
                            {{-- free viewing times from the owner's viewing hours --}}
                            <fieldset class="site-slots mb-2" data-slots='@json($slots)'>
                                <legend class="form-label mb-1">{{ __('Book a viewing (optional)') }}</legend>
                                <input type="hidden" name="viewing_date" value="{{ old('viewing_date') }}">
                                <input type="hidden" name="viewing_time" value="{{ old('viewing_time') }}">
                                <div class="site-slot-days" role="listbox" aria-label="{{ __('Day') }}">
                                    @foreach ($slots as $date => $times)
                                        @php $d = \Carbon\Carbon::parse($date)->locale(app()->getLocale()); @endphp
                                        <button type="button" class="site-slot-day" data-date="{{ $date }}" aria-pressed="false">
                                            <small>{{ $d->isoFormat('ddd') }}</small><strong>{{ $d->format('j') }}</strong><small>{{ $d->isoFormat('MMM') }}</small>
                                        </button>
                                    @endforeach
                                </div>
                                <div class="site-slot-times" aria-live="polite" data-hint="{{ __('Pick a day to see free times.') }}"><p class="small text-muted mb-0">{{ __('Pick a day to see free times.') }}</p></div>
                                <p class="small mb-0 mt-1 site-slot-chosen" hidden><i class="fas fa-calendar-check me-1"></i><span></span> <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-slot-clear>{{ __('Clear') }}</button></p>
                            </fieldset>
                        @else
                            <div class="row g-2 mb-2">
                                <div class="col-7">
                                    {{ Form::label('viewing_date', __('Viewing date'), ['class' => 'form-label']) }}
                                    {{ Form::date('viewing_date', null, ['class' => 'form-control', 'min' => date('Y-m-d')]) }}
                                </div>
                                <div class="col-5">
                                    {{ Form::label('viewing_time', __('Time'), ['class' => 'form-label']) }}
                                    {{ Form::time('viewing_time', null, ['class' => 'form-control']) }}
                                </div>
                            </div>
                        @endif
                        <div class="mb-3">
                            {{ Form::label('message', __('Message'), ['class' => 'form-label']) }}
                            {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 3, 'style' => 'height:auto']) }}
                        </div>
                        <button type="submit" class="ud-btn btn-thm2 w-100">{{ __('Send Enquiry') }}</button>
                        {{ Form::close() }}

                        @if ($isRent && $freeUnits->isNotEmpty())
                            <a href="{{ route('web.apply', ['code' => $code, 'id' => \Crypt::encrypt($property->id)]) }}" class="ud-btn btn-white2 w-100 mt-2">{{ __('Apply Now') }}</a>
                        @endif

                        @if ($phone || $wa)
                            <div class="site-enquiry-contact">
                                @if ($phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"><i class="fas fa-phone-alt"></i> {{ $phone }}</a>@endif
                                @if ($wa)<a href="https://wa.me/{{ $wa }}?text={{ urlencode(__('Hello, I am interested in :property', ['property' => $property->name]) . ' ' . $pageUrl) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>@endif
                            </div>
                        @endif
                        <div class="site-share">
                            <span>{{ __('Share') }}:</span>
                            <a href="https://wa.me/?text={{ urlencode($property->name . ' ' . $pageUrl) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($pageUrl) }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="mailto:?subject={{ rawurlencode($property->name) }}&body={{ rawurlencode($pageUrl) }}" aria-label="{{ __('Email') }}"><i class="fas fa-envelope"></i></a>
                            <button type="button" data-copy-link="{{ $pageUrl }}" data-copied="{{ __('Link copied') }}" aria-label="{{ __('Copy link') }}"><i class="fas fa-link"></i></button>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    @if ($similar->isNotEmpty())
        <section class="site-section pt-0">
            <div class="container">
                <h2 class="site-block-title mb-4">{{ __('Similar properties') }}</h2>
                <div class="row">
                    @foreach ($similar as $item)
                        <div class="col-md-6 col-lg-4 mb-4">@include('theme._property_card', ['property' => $item])</div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- phones: quick actions at the bottom of the screen --}}
    <div class="site-mobile-bar d-lg-none">
        @if ($phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}"><i class="fas fa-phone-alt"></i><span>{{ __('Call') }}</span></a>@endif
        @if ($wa)<a href="https://wa.me/{{ $wa }}?text={{ urlencode(__('Hello, I am interested in :property', ['property' => $property->name]) . ' ' . $pageUrl) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i><span>WhatsApp</span></a>@endif
        <a href="#enquiry" class="is-main"><i class="fas fa-envelope"></i><span>{{ __('Enquire') }}</span></a>
    </div>
@endsection

@push('script-page')
    <script>
        (function() {
            var box = document.querySelector('[data-slots]');
            if (!box) return;
            var slots = JSON.parse(box.getAttribute('data-slots')), dateIn = box.querySelector('[name=viewing_date]'), timeIn = box.querySelector('[name=viewing_time]'),
                times = box.querySelector('.site-slot-times'), chosen = box.querySelector('.site-slot-chosen');
            function pickDay(date) {
                box.querySelectorAll('.site-slot-day').forEach(function(b) { var on = b.dataset.date === date; b.classList.toggle('active', on); b.setAttribute('aria-pressed', on); });
                times.innerHTML = '';
                (slots[date] || []).forEach(function(t) {
                    var b = document.createElement('button');
                    b.type = 'button'; b.className = 'site-slot-time'; b.textContent = t; b.setAttribute('aria-pressed', 'false');
                    b.onclick = function() { pick(date, t, b); };
                    times.appendChild(b);
                });
            }
            function pick(date, t, btn) {
                dateIn.value = date; timeIn.value = t;
                times.querySelectorAll('.site-slot-time').forEach(function(b) { var on = b === btn; b.classList.toggle('active', on); b.setAttribute('aria-pressed', on); });
                var day = box.querySelector('.site-slot-day[data-date="' + date + '"]');
                chosen.querySelector('span').textContent = day.innerText.replace(/\s+/g, ' ').trim() + ', ' + t;
                chosen.hidden = false;
            }
            box.querySelectorAll('.site-slot-day').forEach(function(b) { b.addEventListener('click', function() { pickDay(b.dataset.date); }); });
            box.querySelector('[data-slot-clear]').addEventListener('click', function() {
                dateIn.value = ''; timeIn.value = ''; chosen.hidden = true;
                times.querySelectorAll('.site-slot-time').forEach(function(b) { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
            });
            // keep the earlier choice after a failed submit
            if (dateIn.value && slots[dateIn.value]) {
                pickDay(dateIn.value);
                var btn = Array.prototype.find.call(times.children, function(b) { return b.textContent === timeIn.value; });
                if (btn) pick(dateIn.value, timeIn.value, btn); else { timeIn.value = ''; }
            }
        })();
    </script>
@endpush
