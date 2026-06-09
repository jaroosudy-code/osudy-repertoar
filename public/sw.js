const CACHE = 'osudy-v2';

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE)
            .then(c => c.addAll(['/logo.gif']))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', e => {
    // Vymaž staré cache verzie
    e.waitUntil(
        caches.keys()
            .then(keys => Promise.all(
                keys.filter(k => k !== CACHE).map(k => caches.delete(k))
            ))
            .then(() => clients.claim())
    );
});

self.addEventListener('fetch', e => {
    const req = e.request;

    // Len GET požiadavky
    if (req.method !== 'GET') return;

    // Len vlastná doména
    if (!req.url.startsWith(self.location.origin)) return;

    const url = new URL(req.url);

    // Statické súbory (CSS, JS, fonty, obrázky) — cache first
    const isStatic = ['style', 'script', 'font', 'image'].includes(req.destination)
        || url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icons/');

    if (isStatic) {
        e.respondWith(
            caches.match(req).then(cached => {
                if (cached) return cached;
                return fetch(req).then(resp => {
                    caches.open(CACHE).then(c => c.put(req, resp.clone()));
                    return resp;
                });
            })
        );
        return;
    }

    // HTML stránky — network first, cache fallback
    e.respondWith(
        fetch(req)
            .then(resp => {
                if (resp.ok) {
                    caches.open(CACHE).then(c => c.put(req, resp.clone()));
                }
                return resp;
            })
            .catch(() => caches.match(req))
    );
});
