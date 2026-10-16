@php
    $icons = ['flaticon-working', 'flaticon-star-2', 'flaticon-file', 'flaticon-rocket-1'];
    $stats = collect($c['items'] ?? [])->map(function ($item) use ($user) {
        $source = $item['source'] ?? 'manual';
        $value = $source === 'manual' ? ($item['number'] ?? '') : \App\Support\WebsiteSections::statValue($user->id, $source);
        return ['title' => $item['title'] ?? '', 'value' => $value, 'suffix' => $item['suffix'] ?? ''];
    })->filter(fn($s) => $s['title'] !== '' || $s['value'] !== '');
    $col = max(3, intdiv(12, max(1, min(4, $stats->count()))));
@endphp
@if ($stats->isNotEmpty())
    <section class="home11-funfact bdrs12 mx-auto maxw1700" id="{{ $sectionId }}">
        <div class="container">
            <div class="row">
                <div class="col-xl-10 mx-auto">
                    <div class="row justify-content-center wow fadeInUp" data-wow-delay="300ms">
                        @foreach ($stats->values() as $i => $stat)
                            <div class="col-6 col-md-{{ $col }} mb-3">
                                <div class="funfact_one mb20-sm text-center">
                                    <span class="icon text-white {{ $icons[$i % 4] }}"></span>
                                    <div class="details">
                                        <ul class="ps-0 mb-1 d-flex justify-content-center">
                                            <li><div class="timer text-white">{{ $stat['value'] }}{{ $stat['suffix'] }}</div></li>
                                        </ul>
                                        <p class="text text-white mb-0">{{ $stat['title'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
