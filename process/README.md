# 💬 Système de Chat Complet — Millenaire Connect
## Design identique à FlexAdmin — 4 interfaces (Admin, Enseignant, Parent, Élève)

---

## 📁 Structure des fichiers

```
chat_millenaire/
├── app/
│   ├── Http/Controllers/Chat/
│   │   └── ChatController.php          ← Contrôleur principal (tous les rôles)
│   └── Policies/
│       └── ConversationPolicy.php      ← Autorisations Laravel
│
├── database/migrations/
│   └── 2026_03_10_000001_chat_system_complete.php
│
├── resources/views/chat/
│   ├── index.blade.php                 ← Vue principale du chat
│   └── _integration_guide.blade.php   ← Guide d'intégration (sidebar + topbar)
│
└── routes/
    └── chat.php                        ← Routes du chat
```

---

## ⚡ Installation (5 étapes)

### Étape 1 — Copier les fichiers

```bash
# Contrôleur
cp chat_millenaire/app/Http/Controllers/Chat/ChatController.php \
   app/Http/Controllers/Chat/ChatController.php

# Policy
cp chat_millenaire/app/Policies/ConversationPolicy.php \
   app/Policies/ConversationPolicy.php

# Vue
mkdir -p resources/views/chat
cp chat_millenaire/resources/views/chat/index.blade.php \
   resources/views/chat/index.blade.php

# Routes
cp chat_millenaire/routes/chat.php routes/chat.php

# Migration
cp chat_millenaire/database/migrations/2026_03_10_000001_chat_system_complete.php \
   database/migrations/
```

### Étape 2 — Ajouter les routes dans web.php

Ouvrir `routes/web.php` et ajouter à la fin, DANS le groupe middleware auth :

```php
// Chat Millenaire
require base_path('routes/chat.php');
```

Ou ajouter manuellement (voir `_integration_guide.blade.php`).

### Étape 3 — Enregistrer la Policy

Dans `app/Providers/AuthServiceProvider.php` :

```php
use App\Models\Conversation;
use App\Policies\ConversationPolicy;

protected $policies = [
    Conversation::class => ConversationPolicy::class,
];
```

### Étape 4 — Exécuter la migration

```bash
php artisan migrate
```

### Étape 5 — Mettre à jour la sidebar et la topbar

Ouvrir `resources/views/layouts/partials/sidebar.blade.php` :
- Chercher l'ancien lien `route('messages')` et le remplacer par le bloc du guide d'intégration.

Ouvrir `resources/views/layouts/partials/topbar.blade.php` :
- Ajouter le badge de messagerie dans la zone `topbar-actions`.

Ouvrir `resources/views/layouts/app.blade.php` :
- Ajouter le script de polling du badge avant `</body>`.

---

## ✅ Fonctionnalités incluses

### Interface
- ✅ Design identique à FlexAdmin (couleur teal #1abc9c)
- ✅ Split-view : liste conversations + zone de chat
- ✅ Responsive mobile (sidebar toggleable)
- ✅ Filtres : Tous | Non lus | Groupes
- ✅ Recherche de conversations en temps réel
- ✅ Avatars avec initiales (fallback automatique)
- ✅ Indicateur online/offline
- ✅ Badge non-lu (sidebar + topbar)
- ✅ Séparateurs de dates (Aujourd'hui, Hier, date)

### Messages
- ✅ Messages texte + pièces jointes
- ✅ Envoi avec Entrée (Shift+Entrée = nouvelle ligne)
- ✅ Bulles colorées : teal pour soi, gris pour les autres
- ✅ Statut "Vu" avec icône ✓ verte
- ✅ Suppression de messages
- ✅ Réactions emoji (👍 ❤️ 😂 😮 😢 🙏)
- ✅ Images affichées inline (clic pour agrandir)
- ✅ Fichiers PDF/DOC avec téléchargement
- ✅ Indicateur de frappe (typing dots)
- ✅ Picker emoji pour la saisie

### Backend
- ✅ Polling AJAX toutes les 3 secondes (sans WebSocket)
- ✅ Création de conversations privées / groupes / classes
- ✅ Vérification de conversation existante (pas de doublon)
- ✅ Permissions selon le rôle (ChatPermissionService)
- ✅ Marquer comme lu automatiquement
- ✅ Upload de fichiers (max 20MB)
- ✅ Badge non-lu rafraîchi toutes les 30s sur toutes les pages

### Sécurité
- ✅ Vérification de participation avant chaque action
- ✅ CSRF protection
- ✅ Rate limiting (60 req/min)
- ✅ ConversationPolicy Laravel
- ✅ Permissions par rôle (admin / enseignant / parent / élève)

---

## 🔧 Optionnel : WebSocket temps réel

Pour remplacer le polling par du temps réel (0 délai) :

```bash
# Option A : Laravel Reverb (gratuit, inclus dans Laravel 11)
php artisan reverb:install
php artisan reverb:start

# Option B : Pusher
composer require pusher/pusher-php-server
```

Dans `.env` :
```
BROADCAST_DRIVER=reverb
# ou
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=xxx
PUSHER_APP_KEY=xxx
PUSHER_APP_SECRET=xxx
```

Décommenter dans `ChatController.php` :
```php
broadcast(new \App\Events\MessageSent($message))->toOthers();
```

---

## 🎨 Personnalisation des couleurs

Les variables CSS sont en haut du fichier `chat/index.blade.php` :

```css
:root {
    --chat-primary:       #1abc9c;  /* Teal principal */
    --chat-primary-dark:  #16a085;  /* Teal foncé */
    --chat-primary-light: #e8faf6;  /* Teal clair */
    --chat-bubble-mine:   #1abc9c;  /* Couleur bulle envoyée */
    --chat-bubble-other:  #f3f4f6;  /* Couleur bulle reçue */
}
```

---

## 📱 Compatibilité

| Interface | Fonctionne ? |
|-----------|-------------|
| Admin (admin/censeur/intendant) | ✅ |
| Enseignant (professeur/prof_principal) | ✅ |
| Parent | ✅ |
| Élève (student) | ✅ |
| Mobile responsive | ✅ |
| Sans WebSocket (polling) | ✅ |
| Avec Reverb/Pusher | ✅ (décommenter) |
