<?php

/**
 * config/services.php — Configuration des services tiers
 *
 * Orange Money Cameroun, MTN MoMo, Mail, etc.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Mailgun Configuration
    |--------------------------------------------------------------------------
    */
    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    /*
    |--------------------------------------------------------------------------
    | Orange Money Cameroun
    |--------------------------------------------------------------------------
    | Docs: https://developer.orange.com/apis/om-webpay-cm
    */
    'orange_money' => [
        'api_base'      => env('ORANGE_MONEY_API_BASE', 'https://api.orange.com/orange-money-webpay/cm/v1'),
        'merchant_key'  => env('ORANGE_MONEY_MERCHANT_KEY', ''),
        'x_api_key'     => env('ORANGE_MONEY_X_API_KEY', ''),
        'sandbox'       => env('ORANGE_MONEY_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | MTN Mobile Money (MoMo) Cameroun
    |--------------------------------------------------------------------------
    | Docs: https://momodeveloper.mtn.com/
    */
    'mtn_momo' => [
        'api_base'         => env('MTN_MOMO_API_BASE', 'https://proxy.momoapi.mtn.com'),
        'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY', ''),
        'api_user'         => env('MTN_MOMO_API_USER', ''),
        'api_key'          => env('MTN_MOMO_API_KEY', ''),
        'target_env'       => env('MTN_MOMO_TARGET_ENV', 'sandbox'),
        'collection_url'   => '/collection/v1_0',
        'sandbox'          => env('MTN_MOMO_SANDBOX', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google / OAuth (for future SSO)
    |--------------------------------------------------------------------------
    */
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Postmark (transactional emails)
    |--------------------------------------------------------------------------
    */
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS S3 (file storage, optional)
    |--------------------------------------------------------------------------
    */
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'eu-west-1'),
    ],

];
