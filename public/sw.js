const CACHE_NAME = 'eduscreen-v2'; // Cache-Version erhöht, um Neucaching zu erzwingen
const OFFLINE_URL = 'offline.html';

// Das 'install'-Event: Cacht unsere Offline-Seite
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.add(OFFLINE_URL))
    );
});

// Das 'activate'-Event: Räumt alte Caches auf
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    // Sorge dafür, dass der neue SW sofort die Kontrolle übernimmt
    self.clients.claim();
});

// Das 'fetch'-Event: Fängt alle Netzwerkanfragen ab
self.addEventListener('fetch', (event) => {
    // WICHTIG: Ignoriere alle Anfragen, die keine GET-Anfragen sind.
    // Das lässt unseren POST-Upload und den CSRF-Mechanismus ungestört passieren.
    if (event.request.method !== 'GET') {
        return; // Anweisung, den Request so zu behandeln, als gäbe es keinen Service Worker.
    }

    // Für GET-Anfragen: "Network first, falling back to offline page"-Strategie
    event.respondWith(
        fetch(event.request)
            .catch(() => {
                // Wenn das Netzwerk fehlschlägt, zeige die Offline-Seite.
                return caches.match(OFFLINE_URL);
            })
    );
});