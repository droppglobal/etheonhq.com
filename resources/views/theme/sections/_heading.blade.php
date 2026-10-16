{{-- Section heading used by the newer sections. $c --}}
@if (!empty($c['title']) || !empty($c['info']))
    <div class="main-title text-center mb-4">
        @if (!empty($c['title']))<h2>{{ $c['title'] }}</h2>@endif
        @if (!empty($c['info']))<p class="text">{{ $c['info'] }}</p>@endif
    </div>
@endif
