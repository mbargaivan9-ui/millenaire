<?php

/**
 * config/webpush.php
 *
 * Configuration Web Push Notifications (VAPID)
 * Variables à définir dans le fichier .env
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Sujet VAPID (identifie l'émetteur)
    |--------------------------------------------------------------------------
    | URL ou email du responsable de l'application.
    */
    'subject' => env('VAPID_SUBJECT', 'mailto:admin@millenaire.cm'),

    /*
    |--------------------------------------------------------------------------
    | Clé VAPID publique (partagée avec le navigateur)
    |--------------------------------------------------------------------------
    | Générée avec : php artisan webpush:vapid
    | Ou sur : https://web-push-codelab.glitch.me/
    */
    'vapid_public_key' => env('VAPID_PUBLIC_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Clé VAPID privée (secrète, côté serveur uniquement)
    |--------------------------------------------------------------------------
    */
    'vapid_private_key' => env('VAPID_PRIVATE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | TTL des notifications (secondes)
    |--------------------------------------------------------------------------
    | Durée pendant laquelle le serveur push conserve la notification
    | si l'appareil est hors ligne. 0 = livraison immédiate uniquement.
    */
    'ttl' => env('VAPID_TTL', 86400), // 24 heures

    /*
    |--------------------------------------------------------------------------
    | Urgence des notifications
    |--------------------------------------------------------------------------
    | very-low | low | normal | high
    */
    'urgency' => env('VAPID_URGENCY', 'normal'),
];
