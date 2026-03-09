# 🎓 Millénaire Connect — Plateforme de Gestion Scolaire

**Collège Millénphp artisan migrate --seedaire Bilingue — Douala, Cameroun**

Application Laravel 12 bilingue (FR/EN) complète pour la digitalisation de l'établissement scolaire.

---

## 🚀 Installation Rapide

```bash
# 1. Cloner / extraire le projet
cd millenaire_connect

# 2. Installer les dépendances
composer install
npm install

# 3. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans .env
# DB_CONNECTION=mysql
# DB_DATABASE=millenaire_connect
# DB_USERNAME=root
# DB_PASSWORD=votre_mot_de_passe

# 5. Migrer et seeder


# 6. Lancer le serveur
php artisan serve
npm run dev
```

---

## 👤 Comptes de Démonstration

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@millenaire.cm | Admin@2025! |
| Enseignant | teacher@millenaire.cm | Teacher@2025! |
| Parent | parent@millenaire.cm | Parent@2025! |
| Élève | student@millenaire.cm | Student@2025! |

---

## 🏗️ Architecture

### Rôles & Espaces
- **Admin** `/admin/*` — Gestion complète de l'établissement
- **Enseignant** `/teacher/*` — Saisie notes, bulletins, absences, quiz
- **Parent** `/parent/*` — Suivi enfants, paiements, RDV
- **Élève** `/student/*` — Notes, cours, quiz, emploi du temps

### Technologies
- **Backend**: Laravel 12, PHP 8.2+
- **Base de données**: MySQL (SQLite pour dev)
- **Frontend**: Bootstrap 5.3, Chart.js, Alpine.js
- **Temps réel**: Laravel Reverb (WebSockets)
- **PDF**: DomPDF
- **Paiements**: Orange Money, MTN MoMo (CamPay)
- **PWA**: Service Worker, Push Notifications

### Fonctionnalités Principales
1. 📊 Dashboard KPI avec graphiques (admin, enseignant, parent, élève)
2. 📋 Bulletins scolaires avec saisie en grille et OCR
3. 💳 Paiements Mobile Money (Orange/MTN)
4. 💬 Messagerie temps réel (Laravel Reverb)
5. 📅 Emploi du temps et absences
6. 📚 E-learning (quiz, cours, matériaux)
7. 🔔 Notifications Push (PWA)
8. 🌐 Bilingue Français/Anglais

---

## 📁 Structure

```
app/
  Http/Controllers/   94 contrôleurs
  Models/             62 modèles
  Services/           37 services
  Middleware/         10 middleware
database/
  migrations/         53 migrations
  seeders/            17 seeders
resources/views/      213 templates Blade
routes/
  web.php             517 lignes
  api.php             222 lignes
```

---

## 🔧 Configuration Production

Voir `.env.example` pour toutes les variables d'environnement.

Pour les paiements Mobile Money, configurer dans `.env`:
```
CAMPAY_APP_USERNAME=votre_username
CAMPAY_APP_PASSWORD=votre_password
ORANGE_API_KEY=votre_cle
MTN_API_KEY=votre_cle
```

---

*Développé pour le Collège Millénaire Bilingue — 2025/2026*

