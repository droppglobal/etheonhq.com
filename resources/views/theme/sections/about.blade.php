<section class="site-section site-about" id="{{ $sectionId }}">
    <div class="container">
        <div class="row align-items-center g-5 wow fadeInUp">
            <div class="{{ !empty($c['about_image_path']) ? 'col-lg-6' : 'col-lg-8 mx-auto' }}">
                <div class="main-title mb30">
                    <h2 class="title">{{ $c['Sec4_title'] ?? '' }}</h2>
                </div>
                <div class="why-chose-list">
                    @foreach ($c['items'] ?? [] as $item)
                        <div class="list-one d-flex align-items-start mb30">
                            <span class="list-icon flex-shrink-0 flaticon-badge"></span>
                            <div class="list-content flex-grow-1 ml20">
                                <h4 class="mb-1">{{ $item['title'] ?? '' }}</h4>
                                <p class="text mb-0 fz15">{{ $item['subtitle'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @if (!empty($c['about_image_path']))
                <div class="col-lg-6">
                    <img class="site-about-img" src="{{ \App\Support\WebsiteImage::url($c['about_image_path'], 900) }}" alt="{{ $c['Sec4_title'] ?? '' }}" loading="lazy">
                </div>
            @endif
        </div>
    </div>
</section>
