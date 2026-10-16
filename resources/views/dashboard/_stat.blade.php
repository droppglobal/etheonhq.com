{{-- stat card: icon, color, label, value; optional sub, url, col --}}
<div class="{{ $col ?? 'col-xl-3 col-md-6' }} d-flex">
    @if (!empty($url))<a href="{{ $url }}" class="text-reset d-flex w-100">@endif
    <div class="card border-0 w-100">
        <div class="card-body d-flex align-items-center gap-3">
            <div class="rounded-3 p-3 bg-{{ $color ?? 'primary' }} bg-opacity-10 flex-shrink-0">
                <i class="ti ti-{{ $icon }} f-20 text-{{ $color ?? 'primary' }}"></i>
            </div>
            <div style="min-width:0">
                <p class="text-muted mb-1 small fw-semibold text-uppercase">{{ $label }}</p>
                <h5 class="mb-0">{!! $value !!}</h5>
                @if (!empty($sub))<small class="text-muted d-block text-truncate">{!! $sub !!}</small>@endif
            </div>
        </div>
    </div>
    @if (!empty($url))</a>@endif
</div>
