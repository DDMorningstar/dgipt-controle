/* Contrôle Notes de Perception — cache hors ligne */
const CACHE = 'npc-v22';
const PRECACHE = [
  'index.php',
  'manifest.json',
  'logo-dgipt.jpg',
  'icon-192.png',
  'icon-512.png',
  'apple-touch-icon.png',
  'icon.svg',
  'vendor/xlsx.full.min.js',
  'vendor/html5-qrcode.min.js'
];

function scopeUrl(path){
  return new URL(path, self.registration.scope).href;
}

function fetchTimeout(req, ms){
  const ctrl = new AbortController();
  const t = setTimeout(() => ctrl.abort(), ms);
  return fetch(req, { signal: ctrl.signal, credentials: 'same-origin', cache: 'reload' })
    .finally(() => clearTimeout(t));
}

async function putOk(cache, request, response){
  if (!response || !response.ok) return response;
  try { await cache.put(request, response.clone()); } catch (e) {}
  return response;
}

async function precache(){
  const cache = await caches.open(CACHE);
  const list = PRECACHE.concat(['./', 'index.php']);
  for (const path of list){
    const url = scopeUrl(path);
    try {
      const res = await fetch(url, { cache: 'reload', credentials: 'same-origin' });
      if (res && res.ok) await cache.put(url, res.clone());
    } catch (e) {}
  }
}

async function matchShell(){
  const cache = await caches.open(CACHE);
  const candidates = [
    scopeUrl('index.php'),
    scopeUrl('./'),
    self.registration.scope,
    scopeUrl('')
  ];
  for (const url of candidates){
    const hit = await cache.match(url, { ignoreSearch: true });
    if (hit) return hit;
  }
  const all = await cache.matchAll();
  return all.find(r => {
    const t = r.headers.get('content-type') || '';
    return t.includes('text/html');
  }) || null;
}

async function fromCache(req){
  const cache = await caches.open(CACHE);
  return (await cache.match(req, { ignoreSearch: true }))
    || (await cache.match(req.url.split('?')[0]))
    || null;
}

self.addEventListener('install', event => {
  event.waitUntil(precache().then(() => self.skipWaiting()));
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});

self.addEventListener('fetch', event => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  if (url.pathname.endsWith('/api.php') || url.pathname.endsWith('/admin.php')) return;
  if (url.origin !== self.location.origin) return;

  const isNav = req.mode === 'navigate'
    || (req.headers.get('accept') || '').includes('text/html');

  if (isNav){
    event.respondWith((async () => {
      const cached = await matchShell();
      if (cached){
        event.waitUntil((async () => {
          try {
            const res = await fetchTimeout(req, 2500);
            if (res && res.ok){
              const cache = await caches.open(CACHE);
              await cache.put(scopeUrl('index.php'), res.clone());
              await cache.put(scopeUrl('./'), res.clone());
              await cache.put(req, res.clone());
            }
          } catch (e) {}
        })());
        return cached;
      }
      try {
        const res = await fetchTimeout(req, 8000);
        if (res && res.ok){
          const cache = await caches.open(CACHE);
          await cache.put(scopeUrl('index.php'), res.clone());
          await cache.put(scopeUrl('./'), res.clone());
          await cache.put(req, res.clone());
        }
        return res;
      } catch (e) {
        return new Response(
          '<!DOCTYPE html><meta charset="utf-8"><title>Hors ligne</title><body style="font-family:sans-serif;padding:2rem">Application hors ligne. Ouvrez-la une fois avec le réseau, puis réessayez.</body>',
          { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      }
    })());
    return;
  }

  event.respondWith((async () => {
    const cached = await fromCache(req);
    if (cached){
      event.waitUntil(
        fetchTimeout(req, 2500).then(res => caches.open(CACHE).then(c => putOk(c, req, res))).catch(() => {})
      );
      return cached;
    }
    try {
      const res = await fetchTimeout(req, 8000);
      const cache = await caches.open(CACHE);
      await putOk(cache, req, res);
      return res;
    } catch (e) {
      if (url.pathname.match(/\.(js|css|jpg|jpeg|png|svg|webp|woff2?|json)$/i)){
        return new Response('', { status: 504 });
      }
      throw e;
    }
  })());
});
