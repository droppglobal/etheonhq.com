{{-- Search results: $properties (paginator), $user --}}
@php $properties->appends(request()->except('page')); @endphp
<p class="site-results-count mb-3" aria-live="polite">
    @if ($properties->total())
        {{ __(':from–:to of :total properties', ['from' => $properties->firstItem(), 'to' => $properties->lastItem(), 'total' => $properties->total()]) }}
    @endif
</p>
<div class="row">
    @forelse ($properties as $property)
        <div class="col-sm-6 col-lg-4 col-xxl-3 mb-4">
            @include('theme._property_card', ['property' => $property])
        </div>
    @empty
        <div class="col-12">
            <div class="site-empty">
                <i class="fas fa-magnifying-glass"></i>
                <h5>{{ __('No properties match your search') }}</h5>
                <p class="text-muted mb-3">{{ __('Try fewer filters or a different location.') }}</p>
                <a href="{{ route('property.home', ['code' => $user->code]) }}" class="ud-btn btn-thm2" data-search-reset>{{ __('Show all properties') }}</a>
            </div>
        </div>
    @endforelse
</div>

@if ($properties->hasPages())
    <nav class="mbp_pagination text-center" aria-label="{{ __('Pages') }}">
        <ul class="page_navigation">
            <li class="page-item {{ $properties->onFirstPage() ? 'disabled' : '' }}">
                @if ($properties->onFirstPage())
                    <span class="page-link"><span class="fas fa-angle-left"></span></span>
                @else
                    <a class="page-link" href="{{ $properties->previousPageUrl() }}" aria-label="{{ __('Previous') }}"><span class="fas fa-angle-left"></span></a>
                @endif
            </li>
            @foreach ($properties->getUrlRange(1, $properties->lastPage()) as $page => $url)
                <li class="page-item {{ $page == $properties->currentPage() ? 'active' : '' }}">
                    <a class="page-link" href="{{ $url }}" @if ($page == $properties->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                </li>
            @endforeach
            <li class="page-item {{ $properties->hasMorePages() ? '' : 'disabled' }}">
                @if ($properties->hasMorePages())
                    <a class="page-link" href="{{ $properties->nextPageUrl() }}" aria-label="{{ __('Next') }}"><span class="fas fa-angle-right"></span></a>
                @else
                    <span class="page-link"><span class="fas fa-angle-right"></span></span>
                @endif
            </li>
        </ul>
    </nav>
@endif
