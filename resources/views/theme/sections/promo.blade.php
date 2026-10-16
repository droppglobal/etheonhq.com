<section class="home11-cta-3 at-home13" id="{{ $sectionId }}">
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-6 col-lg-8 wow fadeInLeft">
                <div class="cta-style3">
                    <h2 class="cta-title">{{ $c['Sec6_title'] ?? '' }}</h2>
                    <p class="cta-text">{{ $c['Sec6_info'] ?? '' }}</p>
                    @if (!empty($c['sec6_btn_name']))
                        <a href="{{ $c['sec6_btn_link'] ?: route('contact.home', ['code' => $user->code]) }}" class="ud-btn btn-dark default-box-shadow1">{{ $c['sec6_btn_name'] }} <i class="fal fa-arrow-right-long"></i></a>
                    @endif
                </div>
            </div>
            @if (!empty($c['banner_image2_path']))
                <div class="col-md-6 col-lg-4 wow fadeIn">
                    <img class="home11-ctaimg-v3 d-none d-md-block" src="{{ \App\Support\WebsiteImage::url($c['banner_image2_path'], 900) }}" alt="" loading="lazy">
                </div>
            @endif
        </div>
    </div>
</section>
