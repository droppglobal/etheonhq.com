@php $photos = collect($c['items'] ?? [])->filter(fn($p) => !empty($p['image_path']))->values(); @endphp
@if ($photos->isNotEmpty())
    <section class="site-section" id="{{ $sectionId }}">
        <div class="container wow fadeInUp">
            @include('theme.sections._heading')
            <div class="site-gallery">
                @foreach ($photos as $photo)
                    <a href="{{ asset(Storage::url($photo['image_path'])) }}" class="site-gallery-item" target="_blank" rel="noopener" title="{{ $photo['caption'] ?? '' }}">
                        <img src="{{ \App\Support\WebsiteImage::url($photo['image_path'], 640) }}" alt="{{ $photo['caption'] ?? '' }}" loading="lazy">
                        @if (!empty($photo['caption']))<span>{{ $photo['caption'] }}</span>@endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
