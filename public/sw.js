const CACHE_NAME = 'pctronic-v1';
const STATIC_ASSETS = [
  'css/style.css',
  'css/template.css',
  'js/app.js',
  'manifest.json',
  'assets/Logo.png',
  'assets/icon-192.png',
  'assets/icon-512.png',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
  'https://code.jquery.com/jquery-3.7.1.min.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
  'https://cdn.jsdelivr.net/npm/sweetalert2@11'
];

// Instalación: Pre-cachear activos estáticos
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS).catch((err) => {
        console.warn('Algunos activos no se pudieron precachear:', err);
      });
    })
  );
  self.skipWaiting();
});

// Activación: Limpieza de caches antiguos
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
    })
  );
  self.clients.claim();
});

// Fetch: Estrategia Network-First para páginas dinámicas, Cache-First para estáticos
self.addEventListener('fetch', (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // No interceptar peticiones POST ni peticiones no HTTP/HTTPS
  if (req.method !== 'GET' || !url.protocol.startsWith('http')) {
    return;
  }

  // Activos estáticos (imágenes, fuentes, css, js) -> Cache-first con fallback a red
  if (
    req.destination === 'style' ||
    req.destination === 'script' ||
    req.destination === 'image' ||
    req.destination === 'font'
  ) {
    event.respondWith(
      caches.match(req).then((cached) => {
        if (cached) return cached;
        return fetch(req).then((networkRes) => {
          if (networkRes && networkRes.status === 200) {
            const clone = networkRes.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, clone));
          }
          return networkRes;
        });
      })
    );
    return;
  }

  // Navegación (HTML de controladores) -> Network-first con fallback a caché
  event.respondWith(
    fetch(req).catch(() => {
      return caches.match(req).then((cached) => {
        if (cached) return cached;
        return new Response(
          '<div style="background:#0f172a;color:#fff;font-family:sans-serif;height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px;"><h2>Sin conexión</h2><p style="color:#94a3b8;">Verifica tu conexión a internet para continuar usando Starlink Control.</p><button onclick="location.reload()" style="background:#0d6efd;color:#fff;border:none;padding:10px 20px;border-radius:8px;margin-top:15px;cursor:pointer;">Reintentar</button></div>',
          { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      });
    })
  );
});

