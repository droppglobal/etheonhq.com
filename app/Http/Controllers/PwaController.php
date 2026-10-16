<?php

namespace App\Http\Controllers;

/**
 * Installable web app (PWA): manifest, service worker and offline page.
 * Served from the app root so the service worker can control every page.
 */
class PwaController extends Controller
{
    public function manifest()
    {
        $settings = settings(1);
        $name = trim((string) ($settings['app_name'] ?? '')) ?: (trim((string) ($settings['application_name'] ?? '')) ?: config('app.name', 'Smart Tenant'));
        $base = rtrim(url('/'), '/') . '/';
        return response()->json([
            'name' => $name,
            'short_name' => mb_substr($name, 0, 12),
            'start_url' => $base . 'home',
            'scope' => $base,
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#2ca58d',
            'icons' => [
                ['src' => asset('assets/images/pwa/icon-192.png'), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => asset('assets/images/pwa/icon-512.png'), 'sizes' => '512x512', 'type' => 'image/png'],
                ['src' => asset('assets/images/pwa/icon-512-maskable.png'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ])->header('Content-Type', 'application/manifest+json');
    }

    public function serviceWorker()
    {
        $offline = route('pwa.offline');
        $version = 'st-v3.4';
        $icon = asset('assets/images/pwa/icon-192.png');
        $js = <<<JS
const CACHE = '{$version}';
const OFFLINE_URL = '{$offline}';

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.add(new Request(OFFLINE_URL, { cache: 'reload' }))));
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))));
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // pages always come from the network (they hold private data); offline page if the network is down
  if (req.mode === 'navigate') {
    event.respondWith(fetch(req).catch(() => caches.match(OFFLINE_URL)));
    return;
  }
  // static assets: cache first, refresh in the background
  if (/\.(css|js|png|jpg|jpeg|svg|gif|webp|woff2?|ttf)$/.test(url.pathname) && !url.pathname.includes('/storage/')) {
    event.respondWith(caches.open(CACHE).then((cache) => cache.match(req).then((hit) => {
      const network = fetch(req).then((res) => { if (res.ok) cache.put(req, res.clone()); return res; }).catch(() => hit);
      return hit || network;
    })));
  }
});

// push notifications: show them, and open (or focus) the linked page on click
self.addEventListener('push', (event) => {
  let data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) { data = { body: event.data ? event.data.text() : '' }; }
  event.waitUntil(self.registration.showNotification(data.title || 'Smart Tenant', {
    body: data.body || '',
    icon: '{$icon}',
    badge: '{$icon}',
    data: { url: data.url || self.registration.scope },
  }));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const target = event.notification.data && event.notification.data.url ? event.notification.data.url : self.registration.scope;
  event.waitUntil(clients.matchAll({ type: 'window', includeUncontrolled: true }).then((list) => {
    for (const client of list) {
      if (client.url === target && 'focus' in client) return client.focus();
    }
    return clients.openWindow(target);
  }));
});
JS;
        return response($js, 200)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', parse_url(url('/'), PHP_URL_PATH) ?: '/')
            ->header('Cache-Control', 'no-cache');
    }

    public function offline()
    {
        return response()->view('pwa.offline');
    }
}
