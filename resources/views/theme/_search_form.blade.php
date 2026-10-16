{{-- Property search. $user, $options (PropertySearch::options), $f (filters), $compact (banner version) --}}
@php
    $compact = $compact ?? false;
    $f = $f ?? [];
    $val = fn($k) => $f[$k] ?? '';
    $extraUsed = collect(['type', 'bedrooms', 'min_price', 'max_price', 'available'])->contains(fn($k) => ($f[$k] ?? '') !== '');
@endphp
<form method="GET" action="{{ route('property.home', ['code' => $user->code]) }}" class="site-search {{ $compact ? 'site-search-compact' : '' }} {{ $extraUsed ? 'is-open' : '' }}" role="search" data-search-form>
    <div class="row g-2 align-items-end">
        <div class="{{ $compact ? 'col-12 col-md-4' : 'col-12 col-md-6 col-lg-3' }}">
            <label class="form-label" for="ss-q-{{ $compact ? 'b' : 'p' }}">{{ __('Location or name') }}</label>
            <input type="search" class="form-control" id="ss-q-{{ $compact ? 'b' : 'p' }}" name="q" value="{{ $val('q') }}" placeholder="{{ __('City, street or property') }}" list="ss-cities-{{ $compact ? 'b' : 'p' }}">
            <datalist id="ss-cities-{{ $compact ? 'b' : 'p' }}">
                @foreach ($options['cities'] as $city)<option value="{{ $city }}">@endforeach
            </datalist>
        </div>
        @if (count($options['listings']) > 1)
            <div class="{{ $compact ? 'col-6 col-md-2' : 'col-6 col-md-3 col-lg-2' }}">
                <label class="form-label" for="ss-listing-{{ $compact ? 'b' : 'p' }}">{{ __('Rent or buy') }}</label>
                <select class="form-select" id="ss-listing-{{ $compact ? 'b' : 'p' }}" name="listing">
                    <option value="">{{ __('Any') }}</option>
                    <option value="rent" @selected($val('listing') === 'rent')>{{ __('For rent') }}</option>
                    <option value="sell" @selected($val('listing') === 'sell')>{{ __('For sale') }}</option>
                </select>
            </div>
        @endif
        @if (!$compact && count($options['types']) > 1)
            <div class="col-6 col-md-3 col-lg-2 site-search-extra">
                <label class="form-label" for="ss-type">{{ __('Property type') }}</label>
                <select class="form-select" id="ss-type" name="type">
                    <option value="">{{ __('Any') }}</option>
                    @foreach ($options['types'] as $key => $label)
                        <option value="{{ $key }}" @selected($val('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($options['max_bedrooms'] > 0)
            <div class="{{ $compact ? 'col-6 col-md-2' : 'col-6 col-md-3 col-lg-1 site-search-extra' }}">
                <label class="form-label" for="ss-beds-{{ $compact ? 'b' : 'p' }}">{{ __('Bedrooms') }}</label>
                <select class="form-select" id="ss-beds-{{ $compact ? 'b' : 'p' }}" name="bedrooms">
                    <option value="">{{ __('Any') }}</option>
                    @for ($b = 1; $b <= min(6, $options['max_bedrooms']); $b++)
                        <option value="{{ $b }}" @selected((string) $val('bedrooms') === (string) $b)>{{ $b }}+</option>
                    @endfor
                </select>
            </div>
        @endif
        @unless ($compact)
            <div class="col-6 col-md-3 col-lg-1 site-search-extra">
                <label class="form-label" for="ss-min">{{ __('Min price') }}</label>
                <input type="number" min="0" step="1" class="form-control" id="ss-min" name="min_price" value="{{ $val('min_price') }}" inputmode="numeric">
            </div>
        @endunless
        <div class="{{ $compact ? 'col-6 col-md-2' : 'col-6 col-md-3 col-lg-1 site-search-extra' }}">
            <label class="form-label" for="ss-max-{{ $compact ? 'b' : 'p' }}">{{ __('Max price') }}</label>
            <input type="number" min="0" step="1" class="form-control" id="ss-max-{{ $compact ? 'b' : 'p' }}" name="max_price" value="{{ $val('max_price') }}" inputmode="numeric">
        </div>
        @unless ($compact)
            <div class="col-12 d-md-none">
                <button type="button" class="site-search-more" data-search-more aria-expanded="{{ $extraUsed ? 'true' : 'false' }}">
                    <i class="fas fa-sliders me-1"></i><span class="when-closed">{{ __('More filters') }}</span><span class="when-open">{{ __('Fewer filters') }}</span>
                </button>
            </div>
        @endunless
        <div class="{{ $compact ? 'col-12 col-md-2' : 'col-12 col-md-3 col-lg-2' }}">
            <button type="submit" class="ud-btn btn-thm2 w-100 site-search-btn"><i class="fas fa-magnifying-glass me-1"></i> {{ __('Search') }}</button>
        </div>
    </div>
    @unless ($compact)
        <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
            <div class="form-check mb-0 site-search-extra">
                <input class="form-check-input" type="checkbox" value="1" id="ss-available" name="available" @checked($val('available'))>
                <label class="form-check-label" for="ss-available">{{ __('Only with free units') }}</label>
            </div>
            <div class="d-flex align-items-center gap-2 ms-md-auto">
                <label class="form-label mb-0 text-nowrap" for="ss-sort">{{ __('Sort by') }}</label>
                <select class="form-select form-select-sm" id="ss-sort" name="sort">
                    @foreach (\App\Support\PropertySearch::sorts() as $key => $label)
                        <option value="{{ $key }}" @selected(($val('sort') ?: 'newest') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if (\App\Support\PropertySearch::active($f))
                <a href="{{ route('property.home', ['code' => $user->code]) }}" class="small" data-search-reset>{{ __('Clear filters') }}</a>
            @endif
        </div>
    @endunless
</form>
