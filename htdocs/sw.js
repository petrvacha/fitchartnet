var CACHE_NAME = 'fitchart-v1';
var urlsToCache = [
    '/css/style-new.min.css',
    '/css/style-front.min.css',
    '/js/script-new.min.js',
    '/images/logo.svg',
    '/android-chrome-192x192.png',
    '/offline'
];

self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', function(event) {
    var request = event.request;
    if (request.method !== 'GET') return;

    event.respondWith(
        // NETWORK-FIRST STRATEGY
        fetch(request)
            .then(function(fetchResponse) {
                // If response is OK and not a redirect, cache it
                if (fetchResponse.status === 200 && !fetchResponse.redirected) {
                    var responseToCache = fetchResponse.clone();
                    caches.open(CACHE_NAME).then(function(cache) {
                        cache.put(request, responseToCache);
                    });
                }
                return fetchResponse;
            })
            .catch(function() {
                // OFFLINE FALLBACK
                return caches.match(request).then(function(cachedResponse) {
                    if (cachedResponse) return cachedResponse;
                    
                    // If we don't have the page cached, show /offline for HTML requests
                    if (request.headers.get('accept').includes('text/html')) {
                        return caches.match('/offline');
                    }
                });
            })
    );
});

self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});
