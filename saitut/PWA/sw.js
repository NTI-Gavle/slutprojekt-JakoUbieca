const CACHE_NAME = 'freaky-quiz-pwa-v1';
const OFFLINE_URL = '/PWA/offline.html';
const PRECACHE_URLS = [
  '/',
  '/dashboard.php',
  '/quiz.php',
  '/profile.php',
  '/forum/index.php',
  '/PWA/manifest.json',
  '/PWA/assets/icons/icon-48.png',
  '/PWA/assets/icons/icon-72.png',
  '/PWA/assets/icons/icon-96.png',
  '/PWA/assets/icons/icon-144.png',
  '/PWA/assets/icons/icon-192.png',
  '/PWA/assets/icons/icon-512.png',
  '/css/dashboard.css',
  '/js/main.js',
  '/js/pwa.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(PRECACHE_URLS))
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', event => {
  const { request } = event;

  if (request.mode === 'navigate') {          // nav requests network first fallback to offline page
    event.respondWith(
      fetch(request).catch(() => caches.match(OFFLINE_URL))
    );
    return;
  }

  event.respondWith(                    // For other requests cache first, then network
    caches.match(request).then(response => response || fetch(request))
  );
});
