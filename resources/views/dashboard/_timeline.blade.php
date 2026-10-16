{{-- list of ['date','time','icon','color','title','sub','url'] --}}
@forelse ($items as $item)
    <a href="{{ $item['url'] }}" class="d-flex align-items-center gap-3 py-2 border-bottom text-reset">
        <div class="text-center flex-shrink-0" style="width:46px">
            <div class="small text-muted text-uppercase">{{ \Carbon\Carbon::parse($item['date'])->format('M') }}</div>
            <div class="fw-bold fs-5 lh-1">{{ \Carbon\Carbon::parse($item['date'])->format('d') }}</div>
        </div>
        <div class="avtar avtar-s bg-light-{{ $item['color'] }} flex-shrink-0"><i class="ti ti-{{ $item['icon'] }}"></i></div>
        <div style="min-width:0">
            <div class="fw-semibold text-truncate">{{ $item['title'] }}</div>
            <small class="text-muted">{{ \Carbon\Carbon::parse($item['date'])->isToday() ? __('Today') : (\Carbon\Carbon::parse($item['date'])->isTomorrow() ? __('Tomorrow') : \Carbon\Carbon::parse($item['date'])->format('l')) }}{{ !empty($item['time']) ? ' · ' . substr($item['time'], 0, 5) : '' }}{{ !empty($item['sub']) ? ' · ' . $item['sub'] : '' }}</small>
        </div>
    </a>
@empty
    <p class="text-muted mb-0 py-2">{{ $empty ?? __('Nothing coming up.') }}</p>
@endforelse
