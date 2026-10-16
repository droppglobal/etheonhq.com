@php
    $searchOptions = ($c['show_search'] ?? 'active') === 'active' ? \App\Support\PropertySearch::options($user->id) : null;
    if ($searchOptions && !$searchOptions['listings'] && !count($searchOptions['cities'])) {
        $searchOptions = null; // nothing listed yet
    }
@endphp
<section class="hero-home11 site-hero" id="{{ $sectionId }}">
    <div class="container">
        <div class="row align-items-center justify-content-between">
            <div class="{{ !empty($c['banner_image1_path']) ? 'col-lg-6' : 'col-lg-9' }} mb30-md">
                <div class="home11-hero-content">
                    <h1 class="title animate-up-2">{{ $c['title'] ?? '' }}</h1>
                    @if (!empty($c['sub_title']))
                        <p class="text animate-up-3 h4 text-muted mt-3">{{ $c['sub_title'] }}</p>
                    @endif
                    @if (!empty($c['button_text']))
                        <a href="{{ $c['button_link'] ?: route('property.home', ['code' => $user->code]) }}" class="ud-btn btn-thm2 mt-4">{{ $c['button_text'] }} <i class="fal fa-arrow-right-long"></i></a>
                    @endif
                </div>
            </div>
            @if (!empty($c['banner_image1_path']))
                <div class="col-lg-6">
                    <div class="home11-hero-img text-center text-xxl-end">
                        <img class="bdrs20 ban-img" src="{{ \App\Support\WebsiteImage::url($c['banner_image1_path'], 1200) }}" alt="{{ $c['title'] ?? '' }}">
                    </div>
                </div>
            @endif
        </div>
        @if ($searchOptions)
            <div class="site-search-card site-hero-search">
                @include('theme._search_form', ['compact' => true, 'options' => $searchOptions, 'f' => []])
            </div>
        @endif
    </div>
</section>
