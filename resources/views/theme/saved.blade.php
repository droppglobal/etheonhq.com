@extends('theme.main')
@section('page-title', __('Saved properties') . ' – ' . \App\Support\WebsiteSite::for($user)->siteName)
@php \App\Support\WebsiteSite::for($user)->setMeta(['noindex' => 1]); @endphp
@section('content')
    <section class="site-page-head">
        <div class="container">
            <h1 class="site-page-title"><i class="fas fa-heart me-2 text-danger"></i>{{ __('Saved properties') }}</h1>
            <p class="site-page-sub mb-0">{{ __('Properties you saved on this device. Tick "Compare" on up to 3 to see them side by side.') }}</p>
        </div>
    </section>
    <section class="site-section pt-4">
        <div class="container">
            <div id="saved-list" class="site-results is-loading" data-cards-url="{{ route('web.cards', $user->code) }}"></div>
            <div id="saved-empty" class="site-empty" hidden>
                <i class="far fa-heart"></i>
                <h5>{{ __('No saved properties yet') }}</h5>
                <p class="text-muted mb-3">{{ __('Tap the heart on any property to keep it here.') }}</p>
                <a href="{{ route('property.home', ['code' => $user->code]) }}" class="ud-btn btn-thm2">{{ __('Browse properties') }}</a>
            </div>
        </div>
    </section>
@endsection

@push('script-page')
    <script>
        (function() {
            var list = document.getElementById('saved-list'), empty = document.getElementById('saved-empty');
            var ids = window.SiteUI.saved.get('fav');
            if (!ids.length) { list.hidden = true; empty.hidden = false; return; }
            fetch(list.dataset.cardsUrl + '?ids=' + ids.join(','), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    // forget properties that were removed or hidden by the owner
                    window.SiteUI.saved.set('fav', ids.filter(function(id) { return data.ids.indexOf(id) !== -1; }));
                    if (!data.ids.length) { list.hidden = true; empty.hidden = false; return; }
                    list.innerHTML = data.html;
                    list.classList.remove('is-loading');
                    window.SiteUI.init(list);
                });
            // un-saving on this page removes the card
            document.addEventListener('site:saved', function(e) {
                if (e.detail.list !== 'fav' || e.detail.on) return;
                var card = list.querySelector('[data-fav="' + e.detail.id + '"]');
                if (card) card.closest('[class*="col-"]').remove();
                if (!list.querySelector('.site-card')) { list.hidden = true; empty.hidden = false; }
            });
        })();
    </script>
@endpush
