@php $total = max(1, array_sum($results->toArray())); @endphp
@foreach ($poll->options as $option)
    @php $count = $results[$option->id] ?? 0; $pct = round($count / $total * 100); @endphp
    <div class="mb-2">
        <div class="d-flex justify-content-between small" style="text-transform:none"><span class="{{ !empty($mine) && $mine == $option->id ? 'fw-bold' : '' }}">{{ $option->label }}{{ !empty($mine) && $mine == $option->id ? ' ✓' : '' }}</span><span>{{ $count }} ({{ $pct }}%)</span></div>
        <div class="progress" style="height:8px"><div class="progress-bar bg-secondary" style="width: {{ $pct }}%"></div></div>
    </div>
@endforeach
