const CACHE_NAME = 'sapasekolah-pwa-v1';
const PRECACHE_ASSETS = [
  '/',
  '/manifest.json',
  '/logo-sapa-sekolah.png',
  '/favicon.png',
  '/favicon.ico'
];

// Install Event - Precache critical shell assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

// Activate Event - Clean old caches
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

// Fetch Event - Network First with Cache Fallback for smooth UX
self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Bypass non-http or non-origin requests
  if (!url.protocol.startsWith('http')) return;

  // Static Assets (Images, Icons, Fonts, CSS, JS): Stale-While-Revalidate
  if (
    url.pathname.match(/\.(png|jpg|jpeg|svg|gif|webp|woff|woff2|ttf|eot|css|js)$/i) ||
    url.pathname.startsWith('/build/') ||
    url.pathname.startsWith('/vendor/')
  ) {
    event.respondWith(
      caches.open(CACHE_NAME).then((cache) => {
        return cache.match(event.request).then((cachedResponse) => {
          const fetchPromise = fetch(event.request).then((networkResponse) => {
            if (networkResponse && networkResponse.status === 200) {
              cache.put(event.request, networkResponse.clone());
            }
            return networkResponse;
          }).catch(() => cachedResponse);

          return cachedResponse || fetchPromise;
        });
      })
    );
    return;
  }

  // HTML Page Navigation: Network First with Cache Fallback
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Cache successful responses for offline view
        if (response.status === 200 && response.type === 'basic') {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
        }
        return response;
      })
      .catch(async () => {
        const cached = await caches.match(event.request);
        if (cached) return cached;

        // Return basic offline fallback if completely unreachable
        return new Response(
          `<!DOCTYPE html>
          <html lang="id">
          <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Mode Offline - SAPA Sekolah</title>
            <style>
              body { font-family: system-ui, -apple-system, sans-serif; background: #020617; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; text-align: center; }
              .card { background: #0f172a; border: 1px solid #1e293b; border-radius: 24px; padding: 32px 24px; max-width: 380px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); }
              .icon { width: 54px; height: 54px; background: rgba(239, 68, 68, 0.15); color: #f87171; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px; font-weight: bold; }
              h1 { font-size: 18px; margin: 0 0 8px 0; font-weight: 800; }
              p { font-size: 13px; color: #94a3b8; line-height: 1.5; margin: 0 0 20px 0; }
              button { background: #ef4444; color: #ffffff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer; width: 100%; transition: opacity 0.2s; }
              button:hover { opacity: 0.9; }
            </style>
          </head>
          <body>
            <div class="card">
              <div class="icon">⚡</div>
              <h1>Koneksi Lapangan Terputus</h1>
              <p>Perangkat Anda sedang offline atau sinyal di lokasi lemah. Data yang sudah dibuka sebelumnya tetap tersimpan.</p>
              <button onclick="window.location.reload()">Coba Muat Ulang</button>
            </div>
          </body>
          </html>`,
          {
            headers: { 'Content-Type': 'text/html; charset=utf-8' },
          }
        );
      })
  );
});

