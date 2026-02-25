const CACHE_NAME = 'epos-saza-v1';

// Aset statis yang di-cache untuk offline/fast load
const STATIC_ASSETS = [
    '/manifest.json',
    'https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
];

// Install: cache aset statis
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

// Activate: bersihkan cache lama
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

// Fetch: network-first untuk halaman app, cache-first untuk aset statis
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Lewati request non-GET dan Livewire update
    if (event.request.method !== 'GET') return;
    if (url.pathname.startsWith('/livewire/')) return;

    // Cache-first untuk font & icon CDN
    if (url.hostname !== self.location.hostname) {
        event.respondWith(
            caches.match(event.request).then((cached) => {
                return cached || fetch(event.request).then((response) => {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                    return response;
                });
            })
        );
        return;
    }

    // Network-first untuk semua halaman app
    event.respondWith(
        fetch(event.request).catch(() => caches.match(event.request))
    );
});
