<section class="site-listings" id="{{ $sectionId }}">
    <div class="container">
        <div class="row align-items-center wow fadeInUp">
            <div class="col-lg-7">
                <div class="main-title mb30-lg">
                    <h2 class="title">{{ $c['Sec5_title'] ?? '' }}</h2>
                    <p class="paragraph">{{ $c['Sec5_info'] ?? '' }}</p>
                </div>
            </div>
            <div class="col-lg-5">
                @if (count($listingTypes) > 1)
                    <div class="navpill-style2 at-home6 mb50-lg">
                        <ul class="nav nav-pills mb20 justify-content-lg-end" role="tablist">
                            @foreach ($listingTypes as $key => $type)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $key == 0 ? 'active' : '' }} fw500 dark-color" id="pills-{{ $type }}-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-{{ $type }}" type="button" role="tab">{{ $type === 'sell' ? __('For sale') : __('For rent') }}</button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        @if (count($listingTypes))
            <div class="tab-content ha">
                @foreach ($listingTypes as $key => $type)
                    <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}" id="pills-{{ $type }}" role="tabpanel">
                        <div class="row">
                            @foreach ($propertiesByType[$type]->take(6) as $property)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    @include('theme._property_card', ['property' => $property])
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-2">
                <a href="{{ route('property.home', ['code' => $user->code]) }}" class="ud-btn btn-white2">{{ __('View all properties') }} <i class="fal fa-arrow-right-long"></i></a>
            </div>
        @else
            <p class="text-center text-muted">{{ __('New properties are coming soon.') }}</p>
        @endif
    </div>
</section>
