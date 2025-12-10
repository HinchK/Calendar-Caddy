self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open('loma-club-v1').then((cache) => {
            return cache.addAll([
                '/',
                '/dashboard',
                '/events',
                '/images/loma-club-am.jpeg',
                // Add other assets here
            ]);
        })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});
