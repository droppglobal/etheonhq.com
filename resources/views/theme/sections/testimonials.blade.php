@php
    $reviews = array_values($c['items'] ?? []);
    // approved reviews written by tenants come after the owner's own
    if (($c['include_reviews'] ?? 'active') === 'active' && !empty(\App\Support\WebsiteSite::visitors($user->id)['reviews'])) {
        foreach (\App\Models\WebsiteReview::where('parent_id', $user->id)->where('status', 'approved')->latest()->take(12)->get() as $r) {
            $reviews[] = ['review' => $r->review, 'name' => $r->name, 'tag' => $r->role ?: __('Tenant'), 'rating' => $r->rating];
        }
    }
@endphp
@if ($reviews)
    <section class="our-testimonial" id="{{ $sectionId }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mx-auto wow fadeInUp" data-wow-delay="300ms">
                    <div class="main-title text-center">
                        <h2>{{ $c['Sec7_title'] ?? '' }}</h2>
                        <p class="paragraph">{{ $c['Sec7_info'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 m-auto wow fadeInUp" data-wow-delay="500ms">
                    <div class="testimonial-style2">
                        <div class="tab-content">
                            @foreach ($reviews as $i => $review)
                                <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="{{ $sectionId }}-t{{ $i }}" role="tabpanel">
                                    <div class="testi-content text-center">
                                        <span class="icon fas fa-quote-left"></span>
                                        @if (!empty($review['rating']))
                                            <div class="site-stars mb-2" aria-label="{{ __(':count out of 5', ['count' => $review['rating']]) }}">
                                                @for ($s = 1; $s <= 5; $s++)<i class="{{ $s <= $review['rating'] ? 'fas' : 'far' }} fa-star"></i>@endfor
                                            </div>
                                        @endif
                                        <h4 class="testi-text">{{ $review['review'] ?? '' }}</h4>
                                        <h6 class="name">{{ $review['name'] ?? '' }}</h6>
                                        <p class="design">{{ $review['tag'] ?? '' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if (count($reviews) > 1)
                            <div class="tab-list">
                                <ul class="nav nav-pills justify-content-center gap-2" role="tablist">
                                    @foreach ($reviews as $i => $review)
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link p-1 rounded-circle border {{ $i === 0 ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#{{ $sectionId }}-t{{ $i }}" type="button" role="tab" aria-label="{{ $review['name'] ?? '' }}">
                                                @if (!empty($review['image_path']))
                                                    <img src="{{ \App\Support\WebsiteImage::url($review['image_path'], 120) }}" alt="{{ $review['name'] ?? '' }}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <span class="site-avatar">{{ mb_strtoupper(mb_substr($review['name'] ?? '?', 0, 1)) }}</span>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
