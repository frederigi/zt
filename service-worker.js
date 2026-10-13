/* PWA do catálogo público Zebra de Touca. Atualize o sufixo ao alterar o cache. */
const CACHE_NAME = 'zebra-de-touca-pwa-v1';
const APP_BASE = new URL('./', self.registration.scope);
const OFFLINE_URL = new URL('offline.html', APP_BASE).href;
const PRECACHE_URLS = [
    'offline.html',
    'assets/css/estilo.css',
    'assets/js/main.js',
    'img/logo.jpeg',
    'assets/pwa/icon-192.png',
    'assets/pwa/icon-512.png'
].map((path) => new URL(path, APP_BASE).href);

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key.startsWith('zebra-de-touca-pwa-') && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

function isExcludedPath(pathname) {
    const scopePath = new URL(APP_BASE).pathname;
    const pathWithinScope = pathname.startsWith(scopePath)
        ? pathname.slice(scopePath.length)
        : pathname.replace(/^\/+/, '');
    return pathWithinScope === 'api'
        || pathWithinScope.startsWith('api/')
        || pathWithinScope === 'public/index.php'
        || pathWithinScope.startsWith('public/index.php/')
        || pathWithinScope === 'admin'
        || pathWithinScope.startsWith('admin/');
}

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin || isExcludedPath(url.pathname)) return;

    // HTML PHP é sempre consultado no servidor; sem rede, mostramos apenas o aviso offline.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const offline = await caches.match(OFFLINE_URL);
                return offline || Response.error();
            })
        );
        return;
    }

    // Somente os recursos estáticos explicitamente precacheados usam cache offline.
    if (PRECACHE_URLS.includes(url.href)) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                if (response.ok && response.type === 'basic') {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }
                return response;
            }))
        );
    }
});
