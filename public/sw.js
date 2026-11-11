/**
 * SAPA Sekolah - Progressive Web App Service Worker
 * Cache Strategy: Stale-While-Revalidate for Assets, Network-First for Navigation
 * Version: v2.0
 */

const CACHE_NAME = 'sapasekolah-pwa-v2';

const PRECACHE_ASSETS = [
  '/',
  '/offline.html',
  '/manifest.json',
  '/pwa-icon-192.png',
  '/pwa-icon-512.png',
  '/pwa-maskable-512.png',
  '/apple-touch-icon.png',
  '/favicon-32x32.png',
  '/favicon-16x16.png',
  '/favicon.png',
  '/favicon.ico'
];

// Install Event: Precaching essential shell & offline fallback
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_ASSETS).catch((err) => {
        console.warn('Precache partial fallback:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// Activate Event: Clear outdated caches and claim clients immediately
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
  // Only process GET requests
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Skip non-http/https protocols (e.g. chrome-extension://)
  if (!url.protocol.startsWith('http')) return;

  // 1. Static Assets (Images, Fonts, CSS, JS, Vite build assets)
  // Strategy: Stale-While-Revalidate
  const isStaticAsset = 
    url.pathname.match(/\.(png|jpg|jpeg|svg|gif|webp|ico|woff|woff2|ttf|eot|css|js)$/i) ||
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/vendor/') ||
    url.pathname.startsWith('/storage/');

  if (isStaticAsset) {
    event.respondWith(
      caches.open(CACHE_NAME).then(async (cache) => {
        const cachedResponse = await cache.match(event.request);
        const fetchPromise = fetch(event.request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            cache.put(event.request, networkResponse.clone());
          }
          return networkResponse;
        }).catch(() => cachedResponse);

        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // 2. HTML Page Navigation Requests (e.g. Dashboard, Operasional, Laporan, Peta)
  // Strategy: Network-First with Cache Fallback and Offline Page Fallback
  if (event.request.mode === 'navigate' || event.request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(event.request)
        .then((networkResponse) => {
          // If response is successful, update cache
          if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseClone);
            });
          }
          return networkResponse;
        })
        .catch(async () => {
          // Check if page was cached before
          const cachedResponse = await caches.match(event.request);
          if (cachedResponse) return cachedResponse;

          // Return dedicated branded offline fallback
          const offlinePage = await caches.match('/offline.html');
          if (offlinePage) return offlinePage;

          // Fallback response if cache is missing
          return new Response(
            '<h1>Mode Offline SAPA Sekolah</h1><p>Koneksi internet terputus. Silakan muat ulang saat terhubung kembali.</p>',
            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
          );
        })
    );
    return;
  }

  // 3. Default API / Data Requests: Network First
  event.respondWith(
    fetch(event.request).catch(async () => {
      const cached = await caches.match(event.request);
      if (cached) return cached;
      return new Response(JSON.stringify({ error: 'Koneksi offline' }), {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      });
    })
  );
});
