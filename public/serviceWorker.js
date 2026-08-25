// 1. Versioning: Change this version string to force a cache update
const CACHE_VERSION = "v7";
const CACHE_NAME = `game-pwa-${CACHE_VERSION}`;

// 2. Updated Asset List based on latest HTML/layout includes
const FILES_TO_CACHE = [
    "/",
    "/css/index_new.css?v=60",
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
    "/manifest.webmanifest?v=3",
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

/* Fetch Event:
   - Navigation requests (HTML documents, e.g. "/"): Network-First.
     This is the fix for the homepage-caching bug: "/" used to be
     pre-cached once on install and then handed back from that single
     snapshot forever, because a cache hit was returned without ever
     re-checking the network. Now the network is always tried first,
     so visitors get the current homepage, and the cache is only used
     as a fallback when they're offline.
   - Everything else (CSS/JS/sounds — versioned via ?v= or otherwise
     safe to cache): Cache-First, unchanged from before. */
self.addEventListener("fetch", (event) => {
    // Only handle GET requests; POST requests (like Pushers/API calls) cannot be cached
    if (event.request.method !== "GET") return;

    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request)
                .then((networkResponse) => {
                    if (networkResponse.ok) {
                        const responseClone = networkResponse.clone();
                        event.waitUntil(
                            caches
                                .open(CACHE_NAME)
                                .then((cache) =>
                                    cache.put(event.request, responseClone),
                                ),
                        );
                    }
                    return networkResponse;
                })
                .catch(() => {
                    console.error(
                        "[Service Worker] Navigation fetch failed; serving cached page if available.",
                        event.request.url,
                    );
                    return caches.match(event.request);
                }),
        );
        return;
    }

    event.respondWith(
        caches.match(event.request).then((response) => {
            // Return the cached response if found
            if (response) {
                return response;
            }

            // Otherwise, fetch from the network
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
