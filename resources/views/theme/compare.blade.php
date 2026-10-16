@extends('theme.main')
@section('page-title', __('Compare properties') . ' – ' . \App\Support\WebsiteSite::for($user)->siteName)
@php \App\Support\WebsiteSite::for($user)->setMeta(['noindex' => 1]); @endphp
@section('content')
    <section class="site-page-head">
        <div class="container">
            <h1 class="site-page-title">{{ __('Compare properties') }}</h1>
            <p class="site-page-sub mb-0">{{ __('Up to 3 properties side by side.') }}</p>
        </div>
    </section>
    <section class="site-section pt-4">
        <div class="container">
            @if ($rows->count() < 2)
                <div class="site-empty site-empty-compare">
                    <i class="fas fa-scale-balanced"></i>
                    <h5>{{ __('Pick at least 2 properties to compare') }}</h5>
                    <p class="text-muted mb-3">{{ __('Tick "Compare" on the property cards, then press the Compare button at the bottom of the screen.') }}</p>
                    <a href="{{ route('property.home', ['code' => $user->code]) }}" class="ud-btn btn-thm2">{{ __('Browse properties') }}</a>
                </div>
            @else
                @php $cell = fn($v) => $v === '' || $v === null ? '–' : $v; @endphp
                <div class="table-responsive site-compare">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th scope="col" class="site-compare-label"><span class="visually-hidden">{{ __('Property') }}</span></th>
                                @foreach ($rows as $row)
                                    @php
                                        $p = $row['property'];
                                        $img = $row['facts']['images'][0] ?? 'upload/property/thumbnail/default.jpg';
                                        $link = \App\Support\WebsiteSite::propertyUrl($user, $p);
                                    @endphp
                                    <th scope="col">
                                        <a href="{{ $link }}" class="d-block mb-2"><img src="{{ \App\Support\WebsiteImage::url($img, 520) }}" alt="{{ $p->name }}" class="site-compare-img"></a>
                                        <a href="{{ $link }}" class="site-compare-name">{{ ucfirst($p->name) }}</a>
                                        <button type="button" class="btn btn-link btn-sm p-0 d-block text-muted" data-compare-remove="{{ $p->id }}">{{ __('Remove') }}</button>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr><th scope="row">{{ __('Price') }}</th>@foreach ($rows as $row)<td class="fw-bold">{{ $row['property']->price ? \App\Support\WebsiteSite::price($row['property']->price) : '–' }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Rent or buy') }}</th>@foreach ($rows as $row)<td>{{ $row['property']->listing_type === 'sell' ? __('For sale') : __('For rent') }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Type') }}</th>@foreach ($rows as $row)<td>{{ $cell(\App\Models\Property::types()[$row['property']->type] ?? '') }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Location') }}</th>@foreach ($rows as $row)<td>{{ $cell(trim(implode(', ', array_filter([$row['property']->city, $row['property']->state])))) }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Bedrooms') }}</th>@foreach ($rows as $row)<td>{{ $cell($row['facts']['beds']) }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Bathrooms') }}</th>@foreach ($rows as $row)<td>{{ $cell($row['facts']['baths']) }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Units') }}</th>@foreach ($rows as $row)<td>{{ $row['facts']['units'] ?: '–' }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Available') }}</th>@foreach ($rows as $row)<td>{{ $row['property']->listing_type === 'sell' ? '–' : $row['facts']['vacant'] }}</td>@endforeach</tr>
                            <tr><th scope="row">{{ __('Amenities') }}</th>@foreach ($rows as $row)<td>{{ $row['amenities'] ? implode(', ', $row['amenities']) : '–' }}</td>@endforeach</tr>
                            <tr><th scope="row"></th>@foreach ($rows as $row)<td><a href="{{ \App\Support\WebsiteSite::propertyUrl($user, $row['property']) }}#enquiry" class="ud-btn btn-thm2 btn-sm">{{ __('Enquire') }}</a></td>@endforeach</tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('script-page')
    <script>
        document.addEventListener('click', function(e) {
            var b = e.target.closest('[data-compare-remove]');
            if (!b) return;
            var id = parseInt(b.getAttribute('data-compare-remove'), 10);
            var ids = window.SiteUI.saved.get('cmp').filter(function(i) { return i !== id; });
            window.SiteUI.saved.set('cmp', ids);
            var params = new URLSearchParams(location.search);
            params.set('ids', (params.get('ids') || '').split(',').filter(function(i) { return parseInt(i, 10) !== id; }).join(','));
            location.search = params.toString();
        });
    </script>
@endpush
