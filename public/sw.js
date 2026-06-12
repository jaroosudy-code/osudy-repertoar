const CACHE = 'osudy-v7';

self.addEventListener('install', () => self.skipWaiting());

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;
    const url = new URL(event.request.url);
    if (url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/chat/') ||
        url.pathname === '/logout' ||
        url.pathname === '/settings/invisible') return;

    event.respondWith((async () => {
        const key = event.request.url;
        const cache = await caches.open(CACHE);

        // 1. Skús sieť
        try {
            const fresh = await fetch(event.request);
            if (fresh && fresh.status === 200) {
                await cache.put(key, fresh.clone());
            }
            return fresh;
        } catch (_) {
            // Offline – vráť z cache
            const cached = await cache.match(key);
            if (cached) return cached;
            return new Response(
                '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{font-family:sans-serif;background:#0f172a;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;flex-direction:column;gap:16px;text-align:center;padding:24px}</style></head><body><p style="font-size:3rem">📵</p><h2>Offline</h2><p>Táto stránka nebola uložená.<br>Navštívte ju najprv s internetom.</p></body></html>',
                { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
            );
        }
    })());
});
