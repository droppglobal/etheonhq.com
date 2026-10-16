@php
    $query = \App\Models\Property::with('thumbnail')->where('parent_id', $user->id)->where('is_active', 1);
    if (($c['mode'] ?? 'latest') === 'selected' && !empty($c['property_ids'])) {
        $ids = array_map('intval', (array) $c['property_ids']);
        $featured = $query->whereIn('id', $ids)->get()->sortBy(fn($p) => array_search($p->id, $ids))->values();
    } else {
        $featured = $query->latest()->take(max(1, min(24, (int) ($c['count'] ?? 6))))->get();
    }
@endphp
@if ($featured->isNotEmpty())
    <section class="site-section" id="{{ $sectionId }}">
        <div class="container wow fadeInUp">
            @include('theme.sections._heading')
            <div class="row">
                @foreach ($featured as $property)
                    <div class="col-md-6 col-lg-4 mb-4">@include('theme._property_card', ['property' => $property])</div>
                @endforeach
            </div>
        </div>
    </section>
@endif
