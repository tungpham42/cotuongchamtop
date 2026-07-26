// 1. Versioning: Change this version string to force a cache update
const CACHE_VERSION = "v2";
const CACHE_NAME = `game-pwa-${CACHE_VERSION}`;

// 2. Updated Asset List based on latest HTML/layout includes
const FILES_TO_CACHE = [
    "/",
    "/css/index_new.css?v=57",
    "/css/fa/css/all.min.css",
    "/js/theme-manager.js?v=1",
    "/js/scripts.js?v=18",
    "/js/manipulation.js",
    "/js/xiangqiboard.js",
    "/js/xiangqi.js?v=62",
    "/sound/nuocCo.mp3",
    "/sound/nuocCo.wav",
    "/sound/hetTran.mp3",
    "/sound/hetTran.wav",
    "/manifest.webmanifest?v=2",
];

/* Install Event: Cache files and force immediate activation */
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => {
                console.log("[Service Worker] Caching app shell");
                return cache.addAll(FILES_TO_CACHE);
            })
            .then(() => self.skipWaiting()) // Forces the waiting service worker to become active immediately
            .catch((error) =>
                console.error("[Service Worker] Pre-caching failed:", error),
            ),
    );
});

/* Activate Event: Clean up old caches to save user space */
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keyList) => {
                return Promise.all(
                    keyList.map((key) => {
                        // If the cache is a game-pwa cache but NOT the current version, delete it
                        if (key !== CACHE_NAME && key.includes("game-pwa")) {
                            console.log(
                                "[Service Worker] Removing old cache:",
                                key,
                            );
                            return caches.delete(key);
                        }
                    }),
                );
            })
            .then(() => self.clients.claim()), // Take control of all open clients immediately
    );
});

/* Fetch Event: Cache-First strategy with network fallback */
self.addEventListener("fetch", (event) => {
    // Only handle GET requests; POST requests (like Pushers/API calls) cannot be cached
    if (event.request.method !== "GET") return;

    event.respondWith(
        caches.match(event.request).then((response) => {
            // Return the cached response if found
            if (response) {
                return response;
            }

            // Otherwise, fetch from the network[cite: 3]
            return fetch(event.request)
                .then((networkResponse) => {
                    return networkResponse;
                })
                .catch((error) => {
                    // Graceful fallback if network is down and resource is un-cached
                    console.error(
                        "[Service Worker] Fetch failed; user might be offline.",
                        event.request.url,
                    );
                });
        }),
    );
});
