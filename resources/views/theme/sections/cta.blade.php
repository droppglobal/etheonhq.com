<section class="site-section site-cta-section" id="{{ $sectionId }}">
    <div class="container">
        <div class="site-cta site-cta-{{ $c['style'] ?? 'brand' }} wow fadeInUp">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <h2 class="mb-2">{{ $c['title'] ?? '' }}</h2>
                    @if (!empty($c['info']))<p class="mb-0">{{ $c['info'] }}</p>@endif
                </div>
                @if (!empty($c['button_text']))
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ $c['button_link'] ?: route('property.home', ['code' => $user->code]) }}" class="ud-btn site-cta-btn">{{ $c['button_text'] }} <i class="fal fa-arrow-right-long"></i></a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
