@php $logos = collect($c['items'] ?? [])->filter(fn($l) => !empty($l['image_path']))->values(); @endphp
@if ($logos->isNotEmpty())
    <section class="site-section" id="{{ $sectionId }}">
        <div class="container wow fadeInUp">
            @if (!empty($c['title']))<p class="text-center text-muted text-uppercase small fw500 mb-4">{{ $c['title'] }}</p>@endif
            <div class="site-logos">
                @foreach ($logos as $logo)
                    @if (!empty($logo['link']))<a href="{{ $logo['link'] }}" target="_blank" rel="noopener">@endif
                    <img src="{{ \App\Support\WebsiteImage::url($logo['image_path'], 320) }}" alt="{{ $logo['name'] ?? '' }}" title="{{ $logo['name'] ?? '' }}" loading="lazy">
                    @if (!empty($logo['link']))</a>@endif
                @endforeach
            </div>
        </div>
    </section>
@endif
