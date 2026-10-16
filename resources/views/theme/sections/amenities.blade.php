@if (isset($allAmenities) && count($allAmenities) > 0)
    <section class="site-amenities" id="{{ $sectionId }}">
        <div class="container">
            <div class="row align-items-center wow fadeInUp" data-wow-delay="300ms">
                <div class="col-lg-9">
                    <div class="main-title2">
                        <h2 class="title">{{ $c['Sec3_title'] ?? '' }}</h2>
                        <p class="paragraph">{{ $c['Sec3_info'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 wow fadeInUp" data-wow-delay="300ms">
                    <div class="dots_none slider-dib-sm slider-5-grid vam_nav_style owl-theme owl-carousel">
                        @foreach ($allAmenities as $amenity)
                            <div class="item">
                                <div class="feature-style1 mb30 bdrs16">
                                    <div class="feature-img bdrs16 overflow-hidden">
                                        <img class="loc-img" src="{{ asset(Storage::url('upload/amenity/')) . '/' . ($amenity->image ?: 'default.png') }}" alt="{{ $amenity->name }}" loading="lazy">
                                    </div>
                                    <div class="feature-content">
                                        <div class="top-area">
                                            <h4 class="title mb-1">{{ ucfirst($amenity->name) }}</h4>
                                            @php $amenityText = \Illuminate\Support\Str::limit(trim(strip_tags($amenity->description)), 50, '...'); @endphp
                                            @if ($amenityText !== '' && strcasecmp(rtrim($amenityText, '. '), trim($amenity->name)) !== 0)
                                                <h5 class="text">{{ $amenityText }}</h5>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
