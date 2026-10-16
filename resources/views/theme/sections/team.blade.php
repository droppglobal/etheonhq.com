@if (!empty($c['items']))
    <section class="site-section" id="{{ $sectionId }}">
        <div class="container wow fadeInUp">
            @include('theme.sections._heading')
            <div class="row justify-content-center">
                @foreach ($c['items'] as $person)
                    <div class="col-sm-6 col-lg-3 mb-4">
                        <div class="site-card text-center h-100 p-4">
                            @if (!empty($person['image_path']))
                                <img src="{{ \App\Support\WebsiteImage::url($person['image_path'], 400) }}" alt="{{ $person['name'] ?? '' }}" class="site-team-photo mb-3" loading="lazy">
                            @else
                                <span class="site-avatar site-avatar-lg mb-3">{{ mb_strtoupper(mb_substr($person['name'] ?? '?', 0, 1)) }}</span>
                            @endif
                            <h5 class="mb-1">{{ $person['name'] ?? '' }}</h5>
                            <p class="small text-muted mb-2">{{ $person['role'] ?? '' }}</p>
                            @if (!empty($person['phone']))<div class="small"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $person['phone']) }}"><i class="fas fa-phone-alt me-1"></i>{{ $person['phone'] }}</a></div>@endif
                            @if (!empty($person['email']))<div class="small"><a href="mailto:{{ $person['email'] }}"><i class="fas fa-envelope me-1"></i>{{ $person['email'] }}</a></div>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
