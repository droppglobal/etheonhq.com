<section class="site-section" id="{{ $sectionId }}">
    <div class="container wow fadeInUp">
        @include('theme.sections._heading')
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-4">
                <div class="site-card h-100 p-4">
                    <ul class="list-unstyled site-contact-list mb-0">
                        @if (!empty($c['address']))<li><i class="fas fa-map-marker-alt"></i><span style="white-space: pre-line">{{ $c['address'] }}</span></li>@endif
                        @if (!empty($c['phone']))<li><i class="fas fa-phone-alt"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $c['phone']) }}">{{ $c['phone'] }}</a></li>@endif
                        @if (!empty($c['email']))<li><i class="fas fa-envelope"></i><a href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a></li>@endif
                        @if (!empty($c['hours']))<li><i class="fas fa-clock"></i>{{ $c['hours'] }}</li>@endif
                    </ul>
                    <a href="{{ route('contact.home', ['code' => $user->code]) }}" class="ud-btn btn-thm2 mt-3">{{ __('Send us a message') }}</a>
                </div>
            </div>
            <div class="col-lg-8">
                @if (!empty($c['address']))
                    <div class="site-map h-100">
                        <iframe src="https://maps.google.com/maps?q={{ urlencode(preg_replace('/\s+/', ' ', $c['address'])) }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('Map') }}"></iframe>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
