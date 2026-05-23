const CACHE_NAME = 'freaky-quiz-pwa-v3';
const OFFLINE_URL = 'PWA/offline.html';

const PRECACHE_URLS = [
  'dashboard.php',
  'quiz.php',
  'profile.php',
  'forum/index.php',
  'PWA/manifest.json',
  'PWA/offline.html',
  'PWA/assets/icons/icon-48.png',
  'PWA/assets/icons/icon-72.png',
  'PWA/assets/icons/icon-96.png',
  'PWA/assets/icons/icon-144.png',
  'PWA/assets/icons/icon-192.png',
  'PWA/assets/icons/icon-512.png',
  'css/style.css',
  'css/dashboard.css',
  'css/quiz.css',
  'css/profile.css',
  'js/pwa.js',
  'js/main.js',
  'js/quiz.js?v=3',
  'js/effects.js',
  'js/share.js',
  'js/leaderboard.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {                 // adding resources to the cache during installation skipping incorrect URLs
      return Promise.allSettled(
        PRECACHE_URLS.map(url => cache.add(url).catch(err => console.warn('Cache miss:', url, err)))
      );
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);
  if (request.method !== 'GET') return;

  const isApi = url.pathname.includes('/php/') || url.pathname.includes('/forum/php/') || url.pathname.includes('/admin/');
  const isCacheableApi = url.pathname.includes('get_questions.php') || url.pathname.includes('get_leaderboard.php');

  if (isApi && !isCacheableApi) return;

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then(resp => {
          const clone = resp.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
          return resp;
        })
        .catch(() => {
          return caches.match(request).then(cachedResp => {
            if (cachedResp) return cachedResp;

            if (url.pathname.includes('quiz.php')) {
              return caches.match('quiz.php');
            }
            return caches.match(OFFLINE_URL);
          });
        })
    );
    return;
  }

  event.respondWith(
    caches.match(request).then(resp => resp || fetch(request).then(networkResp => {
      const clone = networkResp.clone();
      caches.open(CACHE_NAME).then(cache => cache.put(request, clone));
      return networkResp;
    }))
  );
});
