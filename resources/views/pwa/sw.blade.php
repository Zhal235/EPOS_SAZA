const CACHE_NAME = 'epos-saza-{{ $version }}';

// Aset statis lokal saja (CDN eksternal skip — CORS/opaque response tidak bisa di-cache)
const STATIC_ASSETS = [
    '/manifest.json',
];

// Install: cache aset statis + skip waiting langsung
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate: bersihkan cache versi lama + ambil alih semua tab
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch: network-first — selalu ambil dari server, cache hanya sebagai fallback offline
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Lewati request non-GET
    if (event.request.method !== 'GET') return;

    // Lewati Livewire update (harus selalu live)
    if (url.pathname.startsWith('/livewire/')) return;

    // Lewati request ke domain eksternal (CDN fonts, icons, dll)
    if (url.hostname !== self.location.hostname) return;

    // Network-first: ambil dari server, fallback ke cache jika offline
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request).then((cached) => {
                return cached || new Response('', {
                    status: 503,
                    statusText: 'Service Unavailable',
                });
            });
        })
    );
});
