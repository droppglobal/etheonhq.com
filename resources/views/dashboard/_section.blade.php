{{-- dashboard section heading: title; optional sub, link, linkText --}}
<div class="d-flex align-items-end justify-content-between mb-2 mt-1">
    <div>
        <h6 class="mb-0 text-uppercase text-muted fw-bold small" style="letter-spacing:.05em">{{ $title }}</h6>
        @if (!empty($sub))<small class="text-muted">{{ $sub }}</small>@endif
    </div>
    @if (!empty($link))<a href="{{ $link }}" class="small">{{ $linkText ?? __('View all') }} <i class="ti ti-chevron-right"></i></a>@endif
</div>
