<?php

/**
 * config/schoolpay.php
 *
 * Configuration du système de paiement School Pay.
 * À copier dans config/ de votre projet Laravel.
 *
 * Variables .env à ajouter :
 * ─────────────────────────────────────────────────────────────
 *  PAYMENT_SANDBOX=true
 *
 *  # Orange Money Cameroun
 *  ORANGE_MONEY_API_KEY=
 *  ORANGE_MONEY_MERCHANT_KEY=
 *  ORANGE_MONEY_WEBHOOK_SECRET=
 *
 *  # MTN Mobile Money Cameroun
 *  MTN_MOMO_SUBSCRIPTION_KEY=
 *  MTN_MOMO_API_USER=
 *  MTN_MOMO_API_KEY=
 *  MTN_MOMO_CALLBACK_KEY=
 *  MTN_MOMO_ENVIRONMENT=sandbox
 *
 *  # Comptes de réception établissement
 *  SCHOOL_ORANGE_ACCOUNT=237XXXXXXXXX
 *  SCHOOL_MTN_ACCOUNT=237XXXXXXXXX
 * ─────────────────────────────────────────────────────────────
 */

return [

    /*
    |─────────────────────────────────────────────────────────────
    | Mode Sandbox
    |─────────────────────────────────────────────────────────────
    | true  = simulation complète, pas de vrai débit
    | false = production réelle (clés API obligatoires)
    */
    'sandbox' => env('PAYMENT_SANDBOX', true),

    /*
    |─────────────────────────────────────────────────────────────
    | Devise & Limites
    |─────────────────────────────────────────────────────────────
    */
    'currency'   => 'XAF',
    'min_amount' => 500,
    'max_amount' => 5_000_000,

    /*
    |─────────────────────────────────────────────────────────────
    | Frais de service
    |─────────────────────────────────────────────────────────────
    */
    'service_fee_rate' => 0.015, // 1.5%

    /*
    |─────────────────────────────────────────────────────────────
    | Expiration
    |─────────────────────────────────────────────────────────────
    */
    'expiry_minutes' => 10,

    /*
    |─────────────────────────────────────────────────────────────
    | Orange Money Cameroun
    |─────────────────────────────────────────────────────────────
    */
    'orange' => [
        'api_key'         => env('ORANGE_MONEY_API_KEY'),
        'merchant_key'    => env('ORANGE_MONEY_MERCHANT_KEY'),
        'webhook_secret'  => env('ORANGE_MONEY_WEBHOOK_SECRET'),
        'api_base'        => 'https://api.orange.com/orange-money-webpay/cm/v1',
        'account'         => env('SCHOOL_ORANGE_ACCOUNT'),
    ],

    /*
    |─────────────────────────────────────────────────────────────
    | MTN Mobile Money Cameroun
    |─────────────────────────────────────────────────────────────
    */
    'mtn' => [
        'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
        'api_user'         => env('MTN_MOMO_API_USER'),
        'api_key'          => env('MTN_MOMO_API_KEY'),
        'callback_key'     => env('MTN_MOMO_CALLBACK_KEY'),
        'environment'      => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
        'api_base'         => 'https://sandbox.momodeveloper.mtn.com',
        'account'          => env('SCHOOL_MTN_ACCOUNT'),
    ],

    /*
    |─────────────────────────────────────────────────────────────
    | Établissement
    |─────────────────────────────────────────────────────────────
    */
    'school' => [
        'name'    => env('APP_NAME', 'Millénaire Connect'),
        'address' => env('SCHOOL_ADDRESS', 'Douala, Cameroun'),
        'logo'    => env('SCHOOL_LOGO', '/assets/img/logo.png'),
    ],
];
