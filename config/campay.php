<?php

/**
 * config/campay.php
 *
 * Configuration CamPay Mobile Money (MTN/Orange Cameroun)
 * Variables à définir dans le fichier .env
 */
return [
    /*
    |--------------------------------------------------------------------------
    | URL de base de l'API CamPay
    |--------------------------------------------------------------------------
    | Démo : https://demo.campay.net/api
    | Production : https://www.campay.net/api
    */
    'base_url' => env('CAMPAY_BASE_URL', 'https://demo.campay.net/api'),

    /*
    |--------------------------------------------------------------------------
    | Identifiants CamPay
    |--------------------------------------------------------------------------
    | À obtenir sur : https://www.campay.net/
    */
    'username' => env('CAMPAY_USERNAME', ''),
    'password' => env('CAMPAY_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    | Secret partagé pour vérifier les callbacks CamPay.
    | À configurer dans le dashboard CamPay.
    */
    'webhook_secret' => env('CAMPAY_WEBHOOK_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Délai de polling (secondes)
    |--------------------------------------------------------------------------
    | Fréquence à laquelle le frontend vérifie le statut d'un paiement.
    */
    'polling_interval' => env('CAMPAY_POLLING_INTERVAL', 3),

    /*
    |--------------------------------------------------------------------------
    | Timeout maximum de polling (secondes)
    |--------------------------------------------------------------------------
    | Après ce délai, le paiement est considéré comme expiré côté client.
    */
    'polling_timeout' => env('CAMPAY_POLLING_TIMEOUT', 120),
];
