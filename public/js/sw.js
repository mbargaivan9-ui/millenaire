/**
 * ═══════════════════════════════════════════════════════
 *  Service Worker — Millenaire Connect PWA
 *  Fichier : public/sw.js
 *
 *  Stratégies de cache :
 *  - Assets statiques (CSS/JS/fonts) : Cache First
 *  - Pages HTML : Network First avec fallback offline
 *  - API calls : Network Only (sauf dashboard stats)
 *  - Images : Cache First avec expiration 7 jours
 * ═══════════════════════════════════════════════════════
 */

const CACHE_VERSION  = 'millenaire-v1.2';
const STATIC_CACHE   = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE  = `${CACHE_VERSION}-dynamic`;
const IMAGE_CACHE    = `${CACHE_VERSION}-images`;

const STATIC_ASSETS = [
    '/',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/badge-72.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js',
];

// Routes accessibles offline (depuis le cache dynamique)
const OFFLINE_ROUTES = [
    '/student/progress',
    '/parent/monitoring',
    '/teacher/bulletin',
];

// ════════════════════════════════════════════════
//  INSTALL — Mise en cache des assets statiques
// ════════════════════════════════════════════════

self.addEventListener('install', (event) => {
    console.log('[SW] Installation — Version:', CACHE_VERSION);

    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { credentials: 'same-origin' })));
        }).then(() => {
            self.skipWaiting(); // Activer immédiatement sans attendre
        })
    );
});

// ════════════════════════════════════════════════
//  ACTIVATE — Supprimer les anciens caches
// ════════════════════════════════════════════════

self.addEventListener('activate', (event) => {
    console.log('[SW] Activation');

    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter(name => name.startsWith('millenaire-') && name !== STATIC_CACHE
                        && name !== DYNAMIC_CACHE && name !== IMAGE_CACHE)
                    .map(name => {
                        console.log('[SW] Suppression ancien cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// ════════════════════════════════════════════════
//  FETCH — Interception des requêtes
// ════════════════════════════════════════════════

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Ignorer les requêtes non-GET
    if (event.request.method !== 'GET') return;

    // Ignorer les requêtes d'autres origines (sauf CDN connus)
    if (url.origin !== location.origin && !url.host.includes('cdn.jsdelivr.net')) {
        return;
    }

    // Stratégie selon le type de ressource
    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(event.request, STATIC_CACHE));

    } else if (isImage(url)) {
        event.respondWith(cacheFirstWithExpiry(event.request, IMAGE_CACHE, 7 * 24 * 60 * 60));

    } else if (isApiCall(url)) {
        event.respondWith(networkOnly(event.request));

    } else {
        event.respondWith(networkFirstWithOfflineFallback(event.request));
    }
});

// ════════════════════════════════════════════════
//  PUSH — Réception d'une notification push
// ════════════════════════════════════════════════

self.addEventListener('push', (event) => {
    let payload = {};
    try {
        payload = event.data?.json() ?? {};
    } catch (e) {
        payload = { title: 'Millenaire Connect', body: event.data?.text() ?? '' };
    }

    const title   = payload.title ?? 'Millenaire Connect';
    const options = {
        body:    payload.body ?? '',
        icon:    payload.icon ?? '/icons/icon-192.png',
        badge:   '/icons/badge-72.png',
        vibrate: [200, 100, 200],
        data:    { url: payload.url ?? '/' },
        actions: [
            { action: 'open',    title: 'Ouvrir',    icon: '/icons/check.png' },
            { action: 'dismiss', title: 'Ignorer', icon: '/icons/x.png' },
        ],
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ════════════════════════════════════════════════
//  NOTIFICATION CLICK — Navigation au clic
// ════════════════════════════════════════════════

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') return;

    const targetUrl = event.notification.data?.url ?? '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Si une fenêtre est déjà ouverte, naviguer dedans
            for (const client of clientList) {
                if (client.url.includes(location.origin) && 'focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }
            // Sinon ouvrir une nouvelle fenêtre
            return clients.openWindow(targetUrl);
        })
    );
});

// ════════════════════════════════════════════════
//  BACKGROUND SYNC — Réessayer les saisies offline
// ════════════════════════════════════════════════

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-bulletin-entries') {
        event.waitUntil(syncPendingEntries());
    }
});

async function syncPendingEntries() {
    // Récupérer les notes mises en file d'attente (IndexedDB)
    const pending = await getPendingFromIdb();
    for (const entry of pending) {
        try {
            await fetch('/teacher/bulletin/entry/save', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': entry.csrf },
                body:    JSON.stringify(entry.data),
            });
            await removeFromIdb(entry.id);
            console.log('[SW] Note synchronisée:', entry.id);
        } catch (e) {
            console.warn('[SW] Sync échouée pour entry:', entry.id);
        }
    }
}

// ════════════════════════════════════════════════
//  STRATÉGIES DE CACHE
// ════════════════════════════════════════════════

async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) return cached;

    const response = await fetch(request);
    const cache    = await caches.open(cacheName);
    cache.put(request, response.clone());
    return response;
}

async function networkFirstWithOfflineFallback(request) {
    try {
        const response = await fetch(request);

        // Mettre en cache pour usage offline
        const cache = await caches.open(DYNAMIC_CACHE);
        cache.put(request, response.clone());

        return response;
    } catch (error) {
        // Réseau indisponible → chercher dans le cache
        const cached = await caches.match(request);
        if (cached) return cached;

        // Page offline générique
        const offlinePage = await caches.match('/offline');
        return offlinePage ?? new Response('Hors ligne. Reconnectez-vous.', {
            headers: { 'Content-Type': 'text/html; charset=UTF-8' },
        });
    }
}

async function cacheFirstWithExpiry(request, cacheName, maxAgeSeconds) {
    const cache  = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) {
        const cachedDate = new Date(cached.headers.get('date') ?? 0);
        const age        = (Date.now() - cachedDate.getTime()) / 1000;

        if (age < maxAgeSeconds) return cached;
    }

    try {
        const response = await fetch(request);
        cache.put(request, response.clone());
        return response;
    } catch {
        return cached ?? new Response('', { status: 503 });
    }
}

async function networkOnly(request) {
    return fetch(request).catch(() => new Response(
        JSON.stringify({ error: 'Hors ligne. Requête impossible sans connexion.' }),
        { status: 503, headers: { 'Content-Type': 'application/json' } }
    ));
}

// ════════════════════════════════════════════════
//  HELPERS
// ════════════════════════════════════════════════

function isStaticAsset(url) {
    return /\.(css|js|woff2?|ttf|eot)(\?.*)?$/.test(url.pathname)
        || url.host.includes('cdn.jsdelivr.net');
}

function isImage(url) {
    return /\.(png|jpg|jpeg|gif|svg|webp|ico)(\?.*)?$/.test(url.pathname);
}

function isApiCall(url) {
    return url.pathname.startsWith('/api/')
        || url.pathname.includes('/ajax/')
        || (url.pathname.includes('/entry/save'))
        || url.searchParams.has('json');
}

// ── IndexedDB helpers pour le sync offline ──
async function getPendingFromIdb() {
    return new Promise((resolve) => {
        const req = indexedDB.open('millenaire-offline', 1);
        req.onsuccess = () => {
            const db  = req.result;
            if (!db.objectStoreNames.contains('pending')) return resolve([]);
            const tx  = db.transaction('pending', 'readonly');
            const all = tx.objectStore('pending').getAll();
            all.onsuccess = () => resolve(all.result);
        };
        req.onerror = () => resolve([]);
    });
}

async function removeFromIdb(id) {
    return new Promise((resolve) => {
        const req = indexedDB.open('millenaire-offline', 1);
        req.onsuccess = () => {
            const db = req.result;
            db.transaction('pending', 'readwrite').objectStore('pending').delete(id);
            resolve();
        };
    });
}
