# 📋 Système de Bulletins NG — Le Millenaire

Système de bulletin scolaire de dernière génération pour la plateforme Laravel Le Millenaire.
Bilingue Français / Anglais. 7 étapes wizard. Génération PDF.

## 📁 Structure des fichiers

```
bulletin-millenaire/
├── database/migrations/
│   └── 2026_03_22_000001_create_bulletin_ng_tables.php
├── app/
│   ├── Models/
│   │   ├── BulletinNgConfig.php
│   │   └── BulletinNgModels.php     ← Subject, Student, Note, Conduite
│   └── Http/Controllers/Teacher/
│       └── BulletinNgController.php
├── routes/
│   └── bulletin_ng_routes.php       ← À coller dans web.php (groupe teacher)
├── public/css/
│   └── bulletin_ng.css
└── resources/views/teacher/bulletin_ng/
    ├── index.blade.php              ← Dashboard sessions
    ├── step1_section.blade.php      ← Choix FR/EN
    ├── step2_config.blade.php       ← Configuration
    ├── step3_subjects.blade.php     ← Matières
    ├── step4_students.blade.php     ← Élèves
    ├── step5_notes.blade.php        ← Saisie notes (temps réel)
    ├── step6_conduite.blade.php     ← Conduite & comportement
    ├── step7_generate.blade.php     ← Bulletins générés
    ├── partials/
    │   ├── wizard_header.blade.php
    │   ├── subject_row.blade.php
    │   └── student_card.blade.php
    └── pdf/
        ├── bulletin.blade.php       ← Template PDF individuel
        └── all.blade.php            ← Export PDF tous les bulletins
```

## 🚀 Installation

### 1. Copier les fichiers dans votre projet Laravel

```bash
# Models
cp app/Models/BulletinNgConfig.php    votre-projet/app/Models/
cp app/Models/BulletinNgModels.php    votre-projet/app/Models/

# Controller
cp app/Http/Controllers/Teacher/BulletinNgController.php \
   votre-projet/app/Http/Controllers/Teacher/

# CSS
cp public/css/bulletin_ng.css  votre-projet/public/css/

# Vues
cp -r resources/views/teacher/bulletin_ng \
      votre-projet/resources/views/teacher/

# Migration
cp database/migrations/2026_03_22_000001_create_bulletin_ng_tables.php \
   votre-projet/database/migrations/
```

### 2. Ajouter les routes dans web.php

Ouvrir `routes/bulletin_ng_routes.php`, copier le contenu
dans le groupe `teacher` de votre `routes/web.php` :

```php
// Dans le groupe middleware teacher...
Route::prefix('bulletin-ng')->name('bulletin_ng.')->group(function () {
    // ... contenu de bulletin_ng_routes.php
});
```

Ajouter l'import en haut de web.php :
```php
use App\Http\Controllers\Teacher\BulletinNgController;
```

### 3. Lancer la migration

```bash
php artisan migrate
```

### 4. Vérifier DomPDF (pour les PDF)

DomPDF est déjà dans votre composer.json.
Si besoin : `composer require barryvdh/laravel-dompdf`

### 5. Storage link (pour les logos)

```bash
php artisan storage:link
```

## 🔗 URL d'accès

```
/teacher/bulletin-ng               ← Dashboard
/teacher/bulletin-ng/step1         ← Démarrer une session
```

## 🔐 Permissions requises

Le controller utilise le middleware `role:prof_principal,admin`
(déjà configuré dans votre plateforme).

## 📌 Notes importantes

- L'en-tête bilingue Cameroun est **toujours maintenu** sur le bulletin,
  quelle que soit la langue choisie (FR ou EN), conformément aux normes officielles.
- Les notes sont calculées en **temps réel** via AJAX à chaque saisie.
- Le verrouillage des notes est **irréversible** depuis l'interface (sécurité).

