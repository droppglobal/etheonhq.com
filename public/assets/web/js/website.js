/* Owner website: photo sliders on property cards and gentle scroll animations. */
(function () {
    'use strict';

    var rtl = document.documentElement.dir === 'rtl';

    function initSlider(card) {
        var track = card.querySelector('[data-slider]');
        if (!track || track.dataset.ready) return;
        track.dataset.ready = '1';
        var slides = track.children;
        var dots = card.querySelectorAll('.site-slider-dots span');

        function current() {
            return Math.round(Math.abs(track.scrollLeft) / track.clientWidth);
        }
        function go(i) {
            i = (i + slides.length) % slides.length;
            track.scrollTo({ left: (rtl ? -1 : 1) * i * track.clientWidth, behavior: 'smooth' });
        }
        card.querySelectorAll('[data-slide]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                go(current() + parseInt(btn.getAttribute('data-slide'), 10));
            });
        });
        var t;
        track.addEventListener('scroll', function () {
            clearTimeout(t);
            t = setTimeout(function () {
                var i = current();
                dots.forEach(function (d, n) { d.classList.toggle('active', n === i); });
            }, 60);
        }, { passive: true });
    }

    // full-screen photo viewer for [data-lightbox] blocks
    function initLightbox(box) {
        if (box.dataset.ready) return;
        box.dataset.ready = '1';
        var links = Array.prototype.slice.call(box.querySelectorAll('[data-lightbox-item]'));
        if (!links.length) return;
        var urls = links.map(function (a) { return a.getAttribute('href'); });
        var index = 0, overlay, img, count, lastFocus;

        function build() {
            overlay = document.createElement('div');
            overlay.className = 'site-lightbox';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');
            overlay.innerHTML = '<button type="button" class="site-lb-close" aria-label="Close"><i class="fas fa-xmark"></i></button>' +
                '<button type="button" class="site-lb-prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>' +
                '<img alt=""><button type="button" class="site-lb-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>' +
                '<span class="site-lb-count"></span>';
            img = overlay.querySelector('img');
            count = overlay.querySelector('.site-lb-count');
            overlay.querySelector('.site-lb-close').onclick = close;
            overlay.querySelector('.site-lb-prev').onclick = function () { show(index - 1); };
            overlay.querySelector('.site-lb-next').onclick = function () { show(index + 1); };
            overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
            var x0 = null;
            overlay.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
            overlay.addEventListener('touchend', function (e) {
                if (x0 === null) return;
                var dx = e.changedTouches[0].clientX - x0;
                if (Math.abs(dx) > 40) show(index + ((dx < 0) !== rtl ? 1 : -1));
                x0 = null;
            });
            if (urls.length < 2) { overlay.querySelector('.site-lb-prev').hidden = true; overlay.querySelector('.site-lb-next').hidden = true; }
            document.body.appendChild(overlay);
        }
        function show(i) {
            index = (i + urls.length) % urls.length;
            img.src = urls[index];
            count.textContent = (index + 1) + ' / ' + urls.length;
        }
        function key(e) {
            if (e.key === 'Escape') close();
            if (e.key === 'ArrowRight') show(index + (rtl ? -1 : 1));
            if (e.key === 'ArrowLeft') show(index + (rtl ? 1 : -1));
        }
        function open(i) {
            if (!overlay) build();
            lastFocus = document.activeElement;
            show(i);
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
            document.addEventListener('keydown', key);
            overlay.querySelector('.site-lb-close').focus();
        }
        function close() {
            overlay.classList.remove('open');
            document.body.style.overflow = '';
            document.removeEventListener('keydown', key);
            if (lastFocus) lastFocus.focus();
        }
        links.forEach(function (a, i) {
            a.addEventListener('click', function (e) { e.preventDefault(); open(i); });
        });
        box.querySelectorAll('[data-lightbox-open]').forEach(function (b) {
            b.addEventListener('click', function () { open(0); });
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-copy-link]');
        if (!btn || !navigator.clipboard) return;
        navigator.clipboard.writeText(btn.getAttribute('data-copy-link')).then(function () {
            if (window.notifier) window.notifier.show('', btn.getAttribute('data-copied'), 'success', window.successImg, 2500);
        });
    });

    // phones: show / hide the extra search filters
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-search-more]');
        if (!btn) return;
        var open = btn.closest('form').classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // visitor's light/dark switch (remembered per website)
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-mode-switch]');
        if (!btn) return;
        var dark = !document.documentElement.classList.contains('site-dark');
        document.documentElement.classList.toggle('site-dark', dark);
        try { localStorage.setItem('site-mode-' + btn.getAttribute('data-mode-switch'), dark ? 'dark' : 'light'); } catch (err) {}
    });

    // saved properties ("fav") and the compare list ("cmp"), kept in this browser per website
    var body = document.body, code = body.getAttribute('data-site') || 'site';
    var saved = {
        get: function (list) {
            try { return (JSON.parse(localStorage.getItem('site-' + list + '-' + code)) || []).map(Number).filter(Boolean); } catch (e) { return []; }
        },
        set: function (list, ids) {
            try { localStorage.setItem('site-' + list + '-' + code, JSON.stringify(ids)); } catch (e) {}
            sync(document);
        }
    };

    function sync(root) {
        var fav = saved.get('fav'), cmp = saved.get('cmp');
        root.querySelectorAll('[data-fav]').forEach(function (b) {
            var on = fav.indexOf(Number(b.getAttribute('data-fav'))) !== -1;
            b.classList.toggle('on', on);
            b.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        root.querySelectorAll('[data-compare]').forEach(function (c) { c.checked = cmp.indexOf(Number(c.getAttribute('data-compare'))) !== -1; });
        document.querySelectorAll('[data-saved-count]').forEach(function (n) { n.textContent = fav.length; n.hidden = !fav.length; });
        tray(cmp);
    }

    function tray(cmp) {
        var bar = document.getElementById('site-compare-bar');
        if (!cmp.length || document.querySelector('.site-compare, .site-empty-compare')) { if (bar) bar.hidden = true; return; }
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'site-compare-bar';
            bar.className = 'site-compare-bar';
            bar.innerHTML = '<a class="ud-btn btn-thm2"></a><button type="button" class="btn btn-link"></button>';
            bar.querySelector('button').textContent = body.getAttribute('data-label-clear');
            bar.querySelector('button').onclick = function () { saved.set('cmp', []); };
            document.body.appendChild(bar);
        }
        bar.hidden = false;
        var a = bar.querySelector('a');
        a.textContent = body.getAttribute('data-label-compare') + ' (' + cmp.length + ')';
        a.href = body.getAttribute('data-compare-url') + '?ids=' + cmp.join(',');
        a.classList.toggle('disabled', cmp.length < 2);
    }

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-fav]');
        if (!b) return;
        e.preventDefault();
        var id = Number(b.getAttribute('data-fav')), ids = saved.get('fav'), at = ids.indexOf(id);
        if (at === -1) ids.unshift(id); else ids.splice(at, 1);
        saved.set('fav', ids);
        document.dispatchEvent(new CustomEvent('site:saved', { detail: { list: 'fav', id: id, on: at === -1 } }));
    });
    document.addEventListener('change', function (e) {
        var c = e.target.closest('[data-compare]');
        if (!c) return;
        var id = Number(c.getAttribute('data-compare')), ids = saved.get('cmp').filter(function (i) { return i !== id; });
        if (c.checked) {
            if (ids.length >= 3) { c.checked = false; if (window.notifier) window.notifier.show('', body.getAttribute('data-label-max'), 'error', window.errorImg, 3000); return; }
            ids.push(id);
        }
        saved.set('cmp', ids);
    });

    window.SiteUI = {
        saved: saved,
        init: function (root) {
            (root || document).querySelectorAll('.site-card-img').forEach(initSlider);
            (root || document).querySelectorAll('[data-lightbox]').forEach(initLightbox);
            sync(root || document);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.SiteUI.init(document);

        // cookie notice: shown until the visitor presses OK
        var notice = document.querySelector('[data-cookie-notice]');
        if (notice) {
            var key = 'site-cookies-' + notice.getAttribute('data-cookie-notice'), seen = null;
            try { seen = localStorage.getItem(key); } catch (e) {}
            if (!seen) notice.hidden = false;
            notice.querySelector('.site-cookie-ok').addEventListener('click', function () {
                try { localStorage.setItem(key, '1'); } catch (e) {}
                notice.hidden = true;
                if (window.siteAnalytics) window.siteAnalytics();
            });
        }

        // fade-in on scroll; skipped for visitors who prefer less motion
        var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (calm || typeof WOW === 'undefined') {
            document.documentElement.classList.add('no-wow');
            return;
        }
        new WOW({ offset: 0, mobile: true, live: false }).init();
    });
})();
