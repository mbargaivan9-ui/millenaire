# 🎓 SchoolPay — Module de Paiement Mobile Money
### Orange Money & MTN Mobile Money — Millénaire Connect

---

## 📁 Structure du Module

```
payment_module/
├── app/
│   ├── Events/Payment/
│   │   └── PaymentCompleted.php          ← Event broadcast temps réel
│   ├── Http/Controllers/Payment/
│   │   └── SchoolPayController.php       ← Contrôleur principal
│   ├── Models/
│   │   └── MobilePayment.php             ← Modèle Eloquent
│   ├── Notifications/Payment/
│   │   ├── AdminPaymentAlertNotification.php
│   │   ├── PaymentFailedNotification.php
│   │   └── PaymentSuccessNotification.php
│   ├── Providers/
│   │   └── SchoolPayServiceProvider.php  ← À enregistrer
│   └── Services/Payment/
│       ├── MtnMomoService.php
│       ├── OrangeMoneyService.php
│       ├── PaymentOrchestrator.php       ← Service central
│       └── ReceiptService.php
├── config/
│   └── schoolpay.php                     ← Configuration
├── database/migrations/
│   └── 2024_01_15_000001_create_mobile_payments_table.php
├── public/
│   ├── css/payment/schoolpay.css         ← Styles (hérite du design system)
│   └── js/payment/schoolpay.js           ← JS flow paiement
├── resources/views/payment/
│   ├── admin/
│   │   └── dashboard.blade.php           ← Dashboard admin temps réel
│   ├── parent/
│   │   └── index.blade.php               ← Interface parent step-by-step
│   └── receipt.blade.php                 ← Page reçu
└── routes/
    └── schoolpay.php                     ← Routes du module
```

---

## ⚡ Intégration en 7 étapes

### Étape 1 — Copier les fichiers

Copiez chaque dossier dans le dossier correspondant de votre projet Laravel :

```bash
# Depuis la racine de votre projet Laravel :

# Modèle
cp payment_module/app/Models/MobilePayment.php              app/Models/

# Contrôleur
cp payment_module/app/Http/Controllers/Payment/SchoolPayController.php \
   app/Http/Controllers/Payment/

# Services
cp -r payment_module/app/Services/Payment/                  app/Services/

# Notifications
cp -r payment_module/app/Notifications/Payment/             app/Notifications/

# Event
mkdir -p app/Events/Payment
cp payment_module/app/Events/Payment/PaymentCompleted.php   app/Events/Payment/

# Service Provider
cp payment_module/app/Providers/SchoolPayServiceProvider.php app/Providers/

# Config
cp payment_module/config/schoolpay.php                      config/

# Migration
cp payment_module/database/migrations/*.php                 database/migrations/

# Vues
cp -r payment_module/resources/views/payment/              resources/views/

# Assets CSS & JS
cp payment_module/public/css/payment/schoolpay.css         public/css/payment/
cp payment_module/public/js/payment/schoolpay.js           public/js/payment/

# Routes
cp payment_module/routes/schoolpay.php                     routes/
```

---

### Étape 2 — Enregistrer le Service Provider

**Laravel 11+ (bootstrap/providers.php) :**
```php
return [
    // ... providers existants
    App\Providers\SchoolPayServiceProvider::class,
];
```

**Laravel 10 et avant (config/app.php) :**
```php
'providers' => [
    // ... providers existants
    App\Providers\SchoolPayServiceProvider::class,
],
```

---

### Étape 3 — Ajouter les routes

Dans **routes/web.php**, à la fin du fichier, ajoutez :

```php
// ── SchoolPay — Module de Paiement ──
require __DIR__ . '/schoolpay.php';
```

---

### Étape 4 — Exécuter la migration

```bash
php artisan migrate
```

Cela créera les tables :
- `mobile_payments` — transactions principales
- `payment_admin_notifications` — alertes admins

---

### Étape 5 — Variables d'environnement (.env)

Ajoutez dans votre `.env` :

```env
# ── Mode sandbox (true = simulation, false = production) ──
PAYMENT_SANDBOX=true

# ── Orange Money Cameroun (Production) ──
ORANGE_MONEY_API_KEY=votre_cle_api_orange
ORANGE_MONEY_MERCHANT_KEY=votre_merchant_key
ORANGE_MONEY_WEBHOOK_SECRET=votre_webhook_secret

# ── MTN Mobile Money Cameroun (Production) ──
MTN_MOMO_SUBSCRIPTION_KEY=votre_subscription_key
MTN_MOMO_API_USER=votre_api_user_uuid
MTN_MOMO_API_KEY=votre_api_key
MTN_MOMO_CALLBACK_KEY=votre_callback_key
MTN_MOMO_ENVIRONMENT=sandbox

# ── Comptes de réception de l'établissement ──
SCHOOL_ORANGE_ACCOUNT=237690000000
SCHOOL_MTN_ACCOUNT=237670000000
```

---

### Étape 6 — Ajouter les liens dans la navigation

Dans **resources/views/layouts/partials/sidebar.blade.php**, ajoutez :

```blade
{{-- Paiement — section Parent --}}
@if(auth()->user()?->role === 'parent')
<a href="{{ route('schoolpay.parent.index') }}"
   class="sidebar-item {{ request()->routeIs('schoolpay.parent.*') ? 'active' : '' }}">
  <span class="sidebar-icon"><i data-lucide="credit-card"></i></span>
  <span class="sidebar-label">Payer la scolarité</span>
</a>
@endif

{{-- Finance — section Admin/Intendant --}}
@if(auth()->user()?->isAdmin() || auth()->user()?->role === 'intendant')
<a href="{{ route('schoolpay.admin.dashboard') }}"
   class="sidebar-item {{ request()->routeIs('schoolpay.admin.*') ? 'active' : '' }}">
  <span class="sidebar-icon"><i data-lucide="banknote"></i></span>
  <span class="sidebar-label">Paiements Mobile Money</span>
</a>
@endif
```

---

### Étape 7 — Vider les caches

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

---

## 🌐 URLs du Module

| URL | Description | Rôle |
|-----|-------------|------|
| `/parent/schoolpay` | Interface de paiement parent | parent |
| `/admin/schoolpay` | Dashboard admin temps réel | admin, intendant |
| `/payment/receipt/{ref}` | Page reçu | authentifié |
| `POST /parent/schoolpay/initiate` | AJAX — initier paiement | parent |
| `GET /parent/schoolpay/poll/{ref}` | AJAX — polling statut | parent |
| `POST /webhooks/schoolpay/orange` | Webhook Orange Money | public |
| `POST /webhooks/schoolpay/mtn` | Webhook MTN MoMo | public |

---

## 🔑 Obtenir les clés API

### Orange Money Cameroun
1. Rendez-vous sur [developer.orange.com](https://developer.orange.com)
2. Créez un compte développeur
3. Créez une application → activez "Orange Money Webpay CM"
4. Récupérez : `API Key`, `Merchant Key`
5. Configurez le Webhook URL : `https://votre-domaine.com/webhooks/schoolpay/orange`

### MTN Mobile Money Cameroun
1. Rendez-vous sur [momodeveloper.mtn.com](https://momodeveloper.mtn.com)
2. Créez un compte → souscrivez à "Collection"
3. Récupérez votre `Subscription Key`
4. Créez un API User via l'API Sandbox :
   ```bash
   curl -X POST https://sandbox.momodeveloper.mtn.com/v1_0/apiuser \
     -H "X-Reference-Id: {UUID}" \
     -H "Ocp-Apim-Subscription-Key: {YOUR_KEY}" \
     -H "Content-Type: application/json" \
     -d '{"providerCallbackHost": "votre-domaine.com"}'
   ```
5. Configurez le Callback URL : `https://votre-domaine.com/webhooks/schoolpay/mtn`

---

## 🧪 Test en Sandbox

Avec `PAYMENT_SANDBOX=true`, le système simule :
- **90%** des paiements réussis
- **10%** des paiements échoués
- Délai réaliste de **5–10 secondes** (comme la vraie API)
- Code USSD factice généré automatiquement
- Modal de progression animé

**Aucun débit réel en mode sandbox.**

---

## 🏗️ Architecture

```
Parent → SchoolPayController::initiate()
           ↓
    PaymentOrchestrator::initiate()
           ↓
    OrangeMoneyService / MtnMomoService
           ↓ (sandbox ou production)
    MobilePayment créé en BDD (status: pending)
           ↓
    Polling AJAX toutes les 2s → poll()
           ↓
    PaymentOrchestrator::finalize()
           ↓
    ├── MobilePayment mis à jour (status: success)
    ├── PaymentSuccessNotification → Parent (mail + DB)
    ├── AdminPaymentAlertNotification → Admins (DB + broadcast)
    ├── PaymentCompleted event → Dashboard admin (polling)
    └── Reçu PDF généré
```

---

## 🎨 Design System

Le module hérite **exactement** du design system Millénaire Connect :
- **Couleur primaire** : `#0d9488` (teal)
- **Typographie** : Plus Jakarta Sans
- **Dark mode** : automatique via `[data-theme="dark"]`
- **Variables CSS** : utilise les variables `--primary`, `--surface`, `--border`, etc.
- **Layout** : compatible sidebar + topbar existants

---

## ✅ Checklist de mise en production

- [ ] `PAYMENT_SANDBOX=false` dans `.env`
- [ ] Clés API Orange Money renseignées
- [ ] Clés API MTN MoMo renseignées
- [ ] Webhooks configurés chez les opérateurs
- [ ] Certificat SSL actif (requis par les APIs)
- [ ] `php artisan queue:work` lancé (pour les notifications)
- [ ] `php artisan storage:link` exécuté (pour les reçus PDF)
- [ ] Comptes de réception établissement configurés
