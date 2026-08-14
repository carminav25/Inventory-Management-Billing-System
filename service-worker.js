/* Service Worker: Caches static assets and serves them offline */
const VERSION = 'v2';
const CACHE_NAME = `isu-merch-billing-${VERSION}`;

// Derive base path from the location of the service-worker script so this works
// whether the app is served at site root or in a subdirectory.
const BASE_PATH = (function(){
    const p = self.location.pathname;
    return p.substring(0, p.lastIndexOf('/') + 1);
})();

// Files to precache (add common static assets used by the app)
const FILES_TO_CACHE = [
    BASE_PATH, // base folder
    BASE_PATH + 'index.php',
    BASE_PATH + 'manifest.json',

    // CSS
    BASE_PATH + 'assets/css/style.css',
    BASE_PATH + 'assets/css/dashboard.css',
    BASE_PATH + 'assets/css/admin-ui.css',
    BASE_PATH + 'assets/css/cards.css',
    BASE_PATH + 'assets/css/tables.css',
    BASE_PATH + 'assets/css/signup.css',

    // JS
    BASE_PATH + 'assets/js/dashboard.js',
    BASE_PATH + 'assets/js/signup.js',

    // Images / icons
    BASE_PATH + 'assets/images/logo.png',
    BASE_PATH + 'assets/images/icon-192.png',
    BASE_PATH + 'assets/images/icon-512.png'
];

// During install, cache the application shell
self.addEventListener('install', event => {
    console.log('Service Worker installing, caching static assets...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(FILES_TO_CACHE))
            .catch(err => console.error('Precache failed:', err))
    );
    self.skipWaiting();
});

// Activate: clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker activated');
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
        ))
    );
    self.clients.claim();
});

// Utility: put response into runtime cache
function putInCache(request, response) {
    if (!response || response.status !== 200) return;
    const copy = response.clone();
    caches.open(CACHE_NAME).then(cache => cache.put(request, copy));
}

// Fetch handler with different strategies:
// - navigation (HTML): network-first, fallback to cache (cached index)
// - CSS/JS: stale-while-revalidate (serve cache if available, update in background)
// - images/fonts: cache-first
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const requestURL = new URL(event.request.url);
    const isSameOrigin = requestURL.origin === self.location.origin;

    // Navigation requests (HTML pages)
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Put a copy in the cache for offline fallback
                    putInCache(event.request, response);
                    return response;
                })
                .catch(() => caches.match(BASE_PATH + 'index.php'))
        );
        return;
    }

    // For same-origin CSS/JS: stale-while-revalidate
    if (isSameOrigin && (requestURL.pathname.endsWith('.css') || requestURL.pathname.endsWith('.js'))) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                const networkFetch = fetch(event.request)
                    .then(networkResponse => { putInCache(event.request, networkResponse); return networkResponse; })
                    .catch(() => null);

                return cached || networkFetch;
            })
        );
        return;
    }

    // Images and fonts: cache-first
    if (isSameOrigin && (requestURL.pathname.match(/\.(png|jpg|jpeg|gif|webp|svg|ico)$/) || requestURL.pathname.match(/\.(woff2?|ttf|otf)$/))) {
        event.respondWith(
            caches.match(event.request).then(cached => cached || fetch(event.request).then(networkResponse => {
                putInCache(event.request, networkResponse);
                return networkResponse;
            }).catch(() => cached))
        );
        return;
    }

    // Default: try cache, then network
    event.respondWith(
        caches.match(event.request).then(cached => cached || fetch(event.request).then(networkResponse => {
            // Optionally cache API responses? Avoid caching dynamic JSON by default.
            return networkResponse;
        }))
    );
});
