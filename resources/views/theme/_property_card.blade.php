{{-- Property card used on the home page and the properties page. $property, $user --}}
@php
    $facts = \App\Support\PropertySearch::facts($property, \App\Support\WebsiteSite::for($user)->featuredIds());
    $images = $facts['images'] ?: ['upload/property/thumbnail/default.jpg'];
    $link = \App\Support\WebsiteSite::propertyUrl($user, $property);
    $type = \App\Models\Property::types()[$property->type] ?? '';
    $place = trim(implode(', ', array_filter([$property->city, $property->state])));
@endphp
<div class="site-card h-100" data-address="{{ trim(implode(', ', array_filter([$property->address, $property->city, $property->state, $property->zip_code, $property->country]))) }}" data-name="{{ $property->name }}">
    <div class="site-card-img position-relative">
        <div class="site-slider" data-slider>
            @foreach ($images as $i => $img)
                <a href="{{ $link }}" class="site-slide" tabindex="{{ $i ? -1 : 0 }}">
                    <img src="{{ \App\Support\WebsiteImage::url($img, 640) }}" decoding="async" alt="{{ $property->name }}{{ $i ? ' – ' . __('photo') . ' ' . ($i + 1) : '' }}" loading="lazy">
                </a>
            @endforeach
        </div>
        @if (count($images) > 1)
            <button type="button" class="site-slider-btn prev" data-slide="-1" aria-label="{{ __('Previous photo') }}"><i class="fas fa-chevron-left"></i></button>
            <button type="button" class="site-slider-btn next" data-slide="1" aria-label="{{ __('Next photo') }}"><i class="fas fa-chevron-right"></i></button>
            <div class="site-slider-dots" aria-hidden="true">
                @foreach ($images as $i => $img)<span class="{{ $i ? '' : 'active' }}"></span>@endforeach
            </div>
        @endif
        <div class="site-tags">
            <span class="site-tag site-tag-{{ $property->listing_type === 'sell' ? 'sale' : 'rent' }}">{{ $property->listing_type === 'sell' ? __('For sale') : __('For rent') }}</span>
            @foreach ($facts['tags'] as [$key, $label])
                <span class="site-tag site-tag-{{ $key }}">{{ $label }}</span>
            @endforeach
        </div>
        <button type="button" class="site-fav-btn" data-fav="{{ $property->id }}" aria-pressed="false" aria-label="{{ __('Save :property', ['property' => $property->name]) }}"><i class="far fa-heart"></i><i class="fas fa-heart"></i></button>
        @if ($property->price)
            <span class="site-price-badge">{{ \App\Support\WebsiteSite::price($property->price) }}</span>
        @endif
    </div>
    <div class="site-card-body">
        @if ($type)<span class="site-card-type">{{ $type }}</span>@endif
        <h5 class="mb-1"><a href="{{ $link }}">{{ ucfirst($property->name) }}</a></h5>
        @if ($place)<p class="small text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i>{{ $place }}</p>@endif
        @if ($facts['beds'] !== '' || $facts['baths'] !== '' || $facts['units'])
            <ul class="site-facts">
                @if ($facts['beds'] !== '')<li title="{{ __('Bedrooms') }}"><i class="fas fa-bed"></i> {{ $facts['beds'] }} <span>{{ __('Beds') }}</span></li>@endif
                @if ($facts['baths'] !== '')<li title="{{ __('Bathrooms') }}"><i class="fas fa-bath"></i> {{ $facts['baths'] }} <span>{{ __('Baths') }}</span></li>@endif
                @if ($facts['units'])<li title="{{ __('Units') }}"><i class="fas fa-door-open"></i> {{ $facts['units'] }} <span>{{ __('Units') }}</span></li>@endif
            </ul>
        @endif
        <div class="d-flex justify-content-between align-items-center mt-2 gap-2">
            @if ($property->listing_type !== 'sell' && $facts['vacant'])
                <p class="site-card-avail m-0"><i class="fas fa-circle-check"></i> {{ __('Units available: :count', ['count' => $facts['vacant']]) }}</p>
            @else
                <span></span>
            @endif
            <label class="site-compare-check mb-0"><input type="checkbox" data-compare="{{ $property->id }}"> {{ __('Compare') }}</label>
        </div>
    </div>
</div>
