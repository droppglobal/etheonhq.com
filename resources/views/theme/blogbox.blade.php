<div class="row">
    @foreach ($blogs as $blog)
        <div class="col-sm-6 col-xl-3">
            <div class="blog-style1">
                <div class="blog-img" >
                    <a href="{{ route('blog.detail', ['code' => $user->code, 'slug' => $blog->slug]) }}">
                        <img class="location-img" src="{{ \App\Support\WebsiteImage::url('upload/blog/image/' . ($blog->image ?: 'default.png'), 640) }}" alt="{{ $blog->title }}" loading="lazy" decoding="async">
                    </a>
                </div>
                <div class="blog-content">
                    <a class="date" href="">{{ dateformat($blog->created_at) }}</a>
                    <h4 class="title mt-1">
                        <a href="{{ route('blog.detail', ['code' => $user->code, 'slug' => $blog->slug]) }}">{{ $blog->title }}</a>
                    </h4>
                    <p class="text mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($blog->content), 50, '...') }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="mbp_pagination text-center">
        @if ($blogs->hasPages())
            <ul class="page_navigation">
                @if ($blogs->onFirstPage())
                    <li class="page-item disabled"><span class="page-link"><span class="fas fa-angle-left"></span></span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $blogs->previousPageUrl() }}"><span class="fas fa-angle-left"></span></a></li>
                @endif

                @foreach ($blogs->links()->elements[0] as $page => $url)
                    @if (is_string($page))
                        <li class="page-item disabled"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item {{ $page == $blogs->currentPage() ? 'active' : '' }}">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                @if ($blogs->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $blogs->nextPageUrl() }}"><span class="fas fa-angle-right"></span></a></li>
                @else
                    <li class="page-item disabled"><span class="page-link"><span class="fas fa-angle-right"></span></span></li>
                @endif
            </ul>
        @endif

        <p class="mt10 mb-0 pagination_page_count text-center">
            {{ ($blogs->currentPage() - 1) * $blogs->perPage() + 1 }} –
            {{ min($blogs->currentPage() * $blogs->perPage(), $blogs->total()) }}
            of {{ $blogs->total() }} blog{{ $blogs->total() > 1 ? 's' : '' }} available
        </p>
    </div>
</div>
