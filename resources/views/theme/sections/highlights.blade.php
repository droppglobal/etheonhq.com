@if (!empty($c['items']))
    <section class="our-features" id="{{ $sectionId }}">
        <div class="container wow fadeInUp">
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-title text-center">
                        <h2>{{ $c['Sec1_title'] ?? '' }}</h2>
                        <p class="text">{{ $c['Sec1_info'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                @foreach ($c['items'] as $item)
                    <div class="col-sm-6 col-lg-3 mb-4">
                        <div class="iconbox-style1 border-less p-0">
                            @if (!empty($item['image_path']))
                                <div class="icon before-none">
                                    <img src="{{ \App\Support\WebsiteImage::url($item['image_path'], 640) }}" alt="{{ $item['title'] ?? '' }}" class="activity-img" loading="lazy" />
                                </div>
                            @endif
                            <div class="details">
                                <h4 class="title mt10 mb-3">{{ $item['title'] ?? '' }}</h4>
                                <p class="text">{{ $item['info'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
