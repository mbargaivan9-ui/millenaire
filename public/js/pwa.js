/**
 * ═══════════════════════════════════════════════════════
 *  pwa.js — Client PWA Millenaire Connect
 *  Fichier : public/js/pwa.js
 *
 *  Fonctionnalités :
 *  - Enregistrement du Service Worker
 *  - Gestion de l'installation (prompt A2HS)
 *  - Abonnement aux notifications push (VAPID)
 *  - Mode offline : file d'attente des saisies
 *  - Bannière "Installer l'application"
 * ═══════════════════════════════════════════════════════
 */

// ════════════════════════════════════════════════
//  1. ENREGISTREMENT DU SERVICE WORKER
// ════════════════════════════════════════════════

let swRegistration = null;

async function registerServiceWorker() {
    if (! ('serviceWorker' in navigator)) {
        console.log('PWA: Service Worker non supporté par ce navigateur.');
        return;
    }

    try {
        swRegistration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
        console.log('PWA: Service Worker enregistré', swRegistration);

        // Écouter les mises à jour
        swRegistration.addEventListener('updatefound', () => {
            const newWorker = swRegistration.installing;
            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    showUpdateBanner();
                }
            });
        });

    } catch (error) {
        console.error('PWA: Erreur enregistrement SW:', error);
    }
}

// ════════════════════════════════════════════════
//  2. INSTALL PROMPT (A2HS — Add to Home Screen)
// ════════════════════════════════════════════════

let deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredInstallPrompt = e;

    // Afficher le bouton d'installation si pas encore installé
    const alreadyInstalled = localStorage.getItem('pwa_installed');
    if (! alreadyInstalled) {
        showInstallBanner();
    }
});

window.addEventListener('appinstalled', () => {
    localStorage.setItem('pwa_installed', '1');
    hideInstallBanner();
    console.log('PWA: Application installée avec succès !');
});

async function triggerInstall() {
    if (! deferredInstallPrompt) return;

    deferredInstallPrompt.prompt();
    const { outcome } = await deferredInstallPrompt.userChoice;

    if (outcome === 'accepted') {
        localStorage.setItem('pwa_installed', '1');
        hideInstallBanner();
    }

    deferredInstallPrompt = null;
}

// ════════════════════════════════════════════════
//  3. NOTIFICATIONS PUSH (VAPID)
// ════════════════════════════════════════════════

async function subscribeToPush() {
    if (! swRegistration || ! ('PushManager' in window)) {
        console.log('PWA: Push non supporté.');
        return false;
    }

    // Vérifier la permission
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        console.log('PWA: Permission de notification refusée.');
        return false;
    }

    try {
        // Récupérer la clé VAPID publique depuis le serveur
        const keyRes  = await fetch('/push/vapid-key');
        const keyData = await keyRes.json();

        if (! keyData.publicKey) {
            console.log('PWA: Clé VAPID non configurée.');
            return false;
        }

        // S'abonner au service push
        const subscription = await swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(keyData.publicKey),
        });

        // Envoyer la souscription au serveur
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        await fetch('/push/subscribe', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(subscription.toJSON()),
        });

        console.log('PWA: Abonnement push réussi.');
        updatePushButton(true);
        return true;

    } catch (error) {
        console.error('PWA: Erreur abonnement push:', error);
        return false;
    }
}

async function unsubscribeFromPush() {
    if (! swRegistration) return;

    const subscription = await swRegistration.pushManager.getSubscription();
    if (! subscription) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    await fetch('/push/unsubscribe', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body:    JSON.stringify({ endpoint: subscription.endpoint }),
    });

    await subscription.unsubscribe();
    updatePushButton(false);
    console.log('PWA: Désabonnement push réussi.');
}

async function checkPushSubscription() {
    if (! swRegistration) return false;
    const sub = await swRegistration.pushManager.getSubscription();
    return !! sub;
}

// ════════════════════════════════════════════════
//  4. MODE OFFLINE — File d'attente des saisies
// ════════════════════════════════════════════════

const DB_NAME    = 'millenaire-offline';
const DB_VERSION = 1;
const STORE_NAME = 'pending';

let idb = null;

function openIdb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, DB_VERSION);
        req.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (! db.objectStoreNames.contains(STORE_NAME)) {
                db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    });
}

/**
 * Mettre en file une note à synchroniser ultérieurement.
 * Appelé automatiquement quand la saisie échoue hors ligne.
 */
async function queueOfflineEntry(entryData) {
    const db = idb || (idb = await openIdb());
    const tx = db.transaction(STORE_NAME, 'readwrite');
    const store = tx.objectStore(STORE_NAME);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    store.add({
        data:        entryData,
        csrf:        csrfToken,
        queued_at:   new Date().toISOString(),
    });

    // Demander une synchronisation en arrière-plan
    if (swRegistration && 'sync' in swRegistration) {
        await swRegistration.sync.register('sync-bulletin-entries');
    }

    showOfflineToast();
}

/**
 * Compter les entrées en attente de synchronisation.
 */
async function getPendingCount() {
    const db = idb || (idb = await openIdb());
    return new Promise((resolve) => {
        const req = db.transaction(STORE_NAME, 'readonly').objectStore(STORE_NAME).count();
        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => resolve(0);
    });
}

// ════════════════════════════════════════════════
//  5. UI HELPERS
// ════════════════════════════════════════════════

function showInstallBanner() {
    const banner = document.getElementById('pwaInstallBanner');
    if (banner) banner.classList.remove('d-none');
}

function hideInstallBanner() {
    const banner = document.getElementById('pwaInstallBanner');
    if (banner) banner.classList.add('d-none');
}

function showUpdateBanner() {
    const toast = document.createElement('div');
    toast.id = 'pwaUpdateToast';
    toast.style.cssText = `
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        background: #0d6efd; color: #fff; padding: 12px 24px; border-radius: 50px;
        box-shadow: 0 4px 20px rgba(0,0,0,.2); z-index: 9999; font-size: .9rem;
        display: flex; align-items: center; gap: 12px; white-space: nowrap;
    `;
    toast.innerHTML = `
        <span>✨ Nouvelle version disponible !</span>
        <button onclick="location.reload()" style="background:rgba(255,255,255,.2);border:none;color:#fff;padding:4px 12px;border-radius:20px;cursor:pointer;font-size:.85rem">
            Mettre à jour
        </button>
    `;
    document.body.appendChild(toast);
}

function updatePushButton(isSubscribed) {
    const btn = document.getElementById('pushToggleBtn');
    if (! btn) return;

    if (isSubscribed) {
        btn.textContent = '🔔 Notifications activées';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success');
    } else {
        btn.textContent = '🔕 Activer les notifications';
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }
}

function showOfflineToast() {
    const t = document.createElement('div');
    t.className = 'toast align-items-center text-white bg-warning border-0 show position-fixed bottom-0 start-0 m-3';
    t.style.zIndex = 9999;
    t.innerHTML = `
        <div class="d-flex">
            <div class="toast-body text-dark">
                📶 Hors ligne — note enregistrée localement, synchronisation automatique dès reconnexion.
            </div>
            <button type="button" class="btn-close me-2 m-auto" onclick="this.closest('.toast').remove()"></button>
        </div>
    `;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 6000);
}

// ════════════════════════════════════════════════
//  6. DÉTECTION CONNEXION
// ════════════════════════════════════════════════

window.addEventListener('online', () => {
    console.log('PWA: Reconnecté.');
    document.getElementById('offlineIndicator')?.classList.add('d-none');

    // Tenter la synchronisation
    if (swRegistration?.sync) {
        swRegistration.sync.register('sync-bulletin-entries');
    }
});

window.addEventListener('offline', () => {
    console.log('PWA: Connexion perdue.');
    const indicator = document.getElementById('offlineIndicator');
    if (indicator) {
        indicator.classList.remove('d-none');
    } else {
        const el = document.createElement('div');
        el.id = 'offlineIndicator';
        el.style.cssText = `
            position: fixed; top: 0; left: 0; right: 0; background: #dc3545;
            color: #fff; text-align: center; padding: 8px; font-size: .85rem; z-index: 10000;
        `;
        el.textContent = '📶 Vous êtes hors ligne — les données seront synchronisées à la reconnexion.';
        document.body.prepend(el);
    }
});

// ════════════════════════════════════════════════
//  7. HELPERS CRYPTO
// ════════════════════════════════════════════════

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = window.atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

// ════════════════════════════════════════════════
//  INIT — Démarrage automatique
// ════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', async () => {
    await registerServiceWorker();

    // Initialiser le bouton de push si présent
    if (swRegistration) {
        const isSubscribed = await checkPushSubscription();
        updatePushButton(isSubscribed);
    }

    // Vérifier si il y a des notes en attente
    const pending = await getPendingCount();
    if (pending > 0) {
        console.log(`PWA: ${pending} note(s) en attente de synchronisation.`);
        const badge = document.getElementById('offlineBadge');
        if (badge) {
            badge.textContent = pending;
            badge.classList.remove('d-none');
        }
    }
});

// Exposer les fonctions globalement pour les templates
window.pwa = {
    triggerInstall,
    subscribeToPush,
    unsubscribeFromPush,
    queueOfflineEntry,
    getPendingCount,
};
