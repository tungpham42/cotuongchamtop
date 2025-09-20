var cacheName = 'game-pwa'; 
var filesToCache = [
  '/css/index.css',
  '/css/fa/css/all.min.css',
  '/js/scripts.js',
  '/js/manipulation.js',
  '/js/xiangqiboard.js',
  '/js/xiangqi.js',
  '/sound/nuocCo.mp3',
  '/sound/nuocCo.wav',
  '/sound/hetTran.mp3',
  '/sound/hetTran.wav'
];
self.addEventListener('install', function(e) {
  e.waitUntil(
    caches.open(cacheName).then(function(cache) {
      return cache.addAll(filesToCache);
    })
  );
});
/* Serve cached content when offline */
self.addEventListener('fetch', function(e) {
  e.respondWith(
    caches.match(e.request).then(function(response) {
      return response || fetch(e.request);
    })
  );
});