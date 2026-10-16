@extends('theme.main')
@php
    $site = \App\Support\WebsiteSite::for($user);
    $image = asset(Storage::url('upload/blog/image/' . ($blog->image ?: 'default.png')));
    $site->setMeta(['description' => $blog->content, 'image' => $image, 'type' => 'article']);
    $article = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => \Illuminate\Support\Str::limit($blog->title, 110, ''),
        'image' => [$image],
        'datePublished' => optional($blog->created_at)->toIso8601String(),
        'dateModified' => optional($blog->updated_at)->toIso8601String(),
        'author' => ['@type' => 'Organization', 'name' => $site->siteName],
        'publisher' => ['@type' => 'Organization', 'name' => $site->siteName, 'logo' => ['@type' => 'ImageObject', 'url' => $site->logo]],
        'mainEntityOfPage' => $site->canonical(),
    ];
@endphp
@section('page-title', ucfirst($blog->title) . ' – ' . $site->siteName)
@push('schema')
    <script type="application/ld+json">{!! json_encode($article, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush
@section('content')
    <article class="site-section site-blog-post">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9 col-xl-8">
                    <nav aria-label="{{ __('Breadcrumb') }}" class="site-crumbs">
                        <a href="{{ route('web.page', $user->code) }}">{{ __('Home') }}</a>
                        <span aria-hidden="true">/</span>
                        <a href="{{ route('blog.home', ['code' => $user->code]) }}">{{ __('Blog') }}</a>
                    </nav>
                    <h1 class="site-detail-title">{{ ucfirst($blog->title) }}</h1>
                    <p class="text-muted"><i class="far fa-calendar me-1"></i><time datetime="{{ optional($blog->created_at)->toDateString() }}">{{ dateFormat($blog->created_at) }}</time></p>
                    <img src="{{ \App\Support\WebsiteImage::url('upload/blog/image/' . ($blog->image ?: 'default.png'), 1200) }}" alt="{{ $blog->title }}" class="site-blog-image">
                    <div class="site-page-content mt-4">{!! \App\Support\WebsiteSite::cleanHtml($blog->content) !!}</div>
                </div>
            </div>
            @if ($recent->isNotEmpty())
                <div class="row justify-content-center mt-5">
                    <div class="col-lg-9 col-xl-8">
                        <h2 class="site-block-title">{{ __('More from our blog') }}</h2>
                        <div class="row">
                            @foreach ($recent as $post)
                                <div class="col-md-4 mb-3">
                                    <a href="{{ route('blog.detail', ['code' => $user->code, 'slug' => $post->slug]) }}" class="site-card h-100 d-block">
                                        <span class="site-card-img d-block"><img src="{{ \App\Support\WebsiteImage::url('upload/blog/image/' . ($post->image ?: 'default.png'), 480) }}" alt="" loading="lazy"></span>
                                        <span class="site-card-body d-block">
                                            <small class="text-muted d-block mb-1">{{ dateFormat($post->created_at) }}</small>
                                            <strong>{{ $post->title }}</strong>
                                        </span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </article>
@endsection
