<!doctype html>
@php
    $settings = settings();

@endphp
<html lang="en">
<!-- [Head] start -->
@include('admin.head')

<!-- [Head] end -->
<!-- [Body] Start -->
<style>
    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        z-index: 999;
        max-height: 300px;
        overflow-y: auto;
    }

    .search-item {
        padding: 10px 15px;
        cursor: pointer;
    }

    .search-item:hover {
        background: #f5f5f5;
    }

    .search-item small {
        display: block;
        color: #888;
    }

    .search-item.active {
        background-color: #f0f4ff;
        cursor: pointer;
    }
</style>

<body
    data-pc-preset="{{ !empty($settings['color_type']) && $settings['color_type'] == 'custom' ? 'custom' : $settings['accent_color'] }}"
    data-pc-sidebar-theme="light" data-pc-sidebar-caption="{{ $settings['sidebar_caption'] }}"
    data-pc-direction="{{ $settings['theme_layout'] }}" data-pc-theme="{{ $settings['theme_mode'] }}">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->
    <!-- [ Sidebar Menu ] start -->
    @include('admin.menu')
    <!-- [ Sidebar Menu ] end -->
    <!-- [ Header Topbar ] start -->
    @include('admin.header')
    <!-- [ Header ] end -->
    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="pc-content">
            <!-- [ breadcrumb ] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="page-header-title">
                                <h5 class="m-b-10"> @yield('page-title')</h5>
                            </div>
                        </div>
                        <div class="col-auto">
                            <ul class="breadcrumb">
                                @yield('breadcrumb')
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [ breadcrumb ] end -->


            <!-- [ Main Content ] start -->
            @include('admin.content')

            <!-- [ Main Content ] end -->
        </div>
    </div>

    <!-- [ Main Content ] end -->
    @include('admin.footer')

    <div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="body">
                </div>
            </div>
        </div>
    </div>
    <script>
        const input = document.getElementById('globalSearch');
        const dropdown = document.getElementById('searchDropdown');
        let timeout = null;
        let activeIndex = -1; // track focused item

        input.addEventListener('input', function() {
            let query = this.value.trim();
            clearTimeout(timeout);
            activeIndex = -1; // reset on new search

            if (query.length === 0) {
                dropdown.classList.add('d-none');
                dropdown.innerHTML = '';
                return;
            }

            timeout = setTimeout(() => {
                fetch(`{{ route('global.search') }}?q=${encodeURIComponent(query)}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Server error');
                        return res.json();
                    })
                    .then(data => {
                        let html = '';
                        if (!data.length) {
                            html = `<div class="search-item text-muted">No results found</div>`;
                        } else {
                            data.forEach(item => {
                                html += `
                            <div class="search-item" data-url="${item.url}" tabindex="-1">
                                <strong>${item.label}</strong>
                                <small>${item.sub ?? ''}</small>
                            </div>
                        `;
                            });
                        }
                        dropdown.innerHTML = html;
                        dropdown.classList.remove('d-none');
                    })
                    .catch(err => {
                        console.error(err);
                        dropdown.innerHTML =
                            `<div class="search-item text-danger">Error loading results</div>`;
                        dropdown.classList.remove('d-none');
                    });
            }, 300);
        });

        // ✅ Keyboard navigation
        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.search-item[data-url]');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = (activeIndex + 1) % items.length;
                updateActiveItem(items);

            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = (activeIndex - 1 + items.length) % items.length;
                updateActiveItem(items);

            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && items[activeIndex]) {
                    window.location.href = items[activeIndex].dataset.url;
                }

            } else if (e.key === 'Escape') {
                dropdown.classList.add('d-none');
                activeIndex = -1;
                input.blur();
            }
        });

        function updateActiveItem(items) {
            items.forEach((item, i) => {
                item.classList.toggle('active', i === activeIndex);
                if (i === activeIndex) {
                    item.scrollIntoView({
                        block: 'nearest'
                    }); // handles overflow scroll
                }
            });
        }

        // Mouse click → redirect
        dropdown.addEventListener('click', function(e) {
            let item = e.target.closest('.search-item');
            if (item && item.dataset.url) {
                window.location.href = item.dataset.url;
            }
        });

        // ✅ Mouse hover syncs activeIndex
        dropdown.addEventListener('mouseover', function(e) {
            const item = e.target.closest('.search-item[data-url]');
            if (!item) return;
            const items = [...dropdown.querySelectorAll('.search-item[data-url]')];
            activeIndex = items.indexOf(item);
            updateActiveItem(items);
        });

        // Click outside → close
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.header-search')) {
                dropdown.classList.add('d-none');
                activeIndex = -1;
            }
        });
    </script>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('{{ route('pwa.sw') }}', { scope: '{{ rtrim(parse_url(url('/'), PHP_URL_PATH) ?: '', '/') }}/' }).then(setupPush).catch(function() {});
        });
    }

    // push notifications: one switch in the profile menu, per browser / phone
    function setupPush(registration) {
        var toggle = document.getElementById('push-toggle');
        if (!toggle || !('PushManager' in window) || !('Notification' in window) || !window.isSecureContext) return;
        var text = document.getElementById('push-toggle-text');
        var csrf = '{{ csrf_token() }}';
        var labels = { on: @json(__('Notifications on · turn off')), off: @json(__('Turn on notifications')), blocked: @json(__('Notifications blocked in browser settings')) };
        var post = function(url, body) {
            return fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body || {}) });
        };
        var keyToBytes = function(key) {
            var b64 = (key + '='.repeat((4 - key.length % 4) % 4)).replace(/-/g, '+').replace(/_/g, '/');
            return Uint8Array.from(atob(b64), function(c) { return c.charCodeAt(0); });
        };
        var render = function(sub) {
            toggle.classList.remove('d-none');
            text.textContent = Notification.permission === 'denied' ? labels.blocked : (sub ? labels.on : labels.off);
            toggle.dataset.on = sub ? '1' : '';
        };
        registration.pushManager.getSubscription().then(function(sub) {
            if (sub) post('{{ route('push.subscribe') }}', sub.toJSON()); // keep the server copy fresh
            render(sub);
        });
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (Notification.permission === 'denied') return;
            registration.pushManager.getSubscription().then(function(sub) {
                if (sub) {
                    return post('{{ route('push.unsubscribe') }}', { endpoint: sub.endpoint }).then(function() { return sub.unsubscribe(); }).then(function() { render(null); });
                }
                return fetch('{{ route('push.key') }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(function(r) { return r.json(); }).then(function(res) {
                    if (!res.key) throw new Error(res.error || 'no key');
                    return registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyToBytes(res.key) });
                }).then(function(newSub) {
                    return post('{{ route('push.subscribe') }}', newSub.toJSON()).then(function() {
                        render(newSub);
                        post('{{ route('push.test') }}');
                    });
                });
            }).catch(function() { render(null); });
        });
    }
</script>
</body>
<!-- [Body] end -->

</html>
