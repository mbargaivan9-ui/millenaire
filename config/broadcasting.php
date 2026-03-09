<?php

/**
 * config/broadcasting.php — Configuration des canaux temps-réel
 *
 * Utilise Laravel Reverb (WebSocket natif Laravel)
 * pour le chat, notifications, présences en direct
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    | Options: reverb, pusher, ably, redis, log, null
    */
    'default' => env('BROADCAST_CONNECTION', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    */
    'connections' => [

        /**
         * Laravel Reverb — WebSocket natif (recommandé pour Millénaire)
         * Installation: php artisan reverb:install
         * Démarrage:    php artisan reverb:start
         */
        'reverb' => [
            'driver'  => 'reverb',
            'key'     => env('REVERB_APP_KEY'),
            'secret'  => env('REVERB_APP_SECRET'),
            'app_id'  => env('REVERB_APP_ID'),
            'options' => [
                'host'   => env('REVERB_HOST', 'localhost'),
                'port'   => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
            ],
        ],

        /**
         * Pusher (alternative cloud) — si Reverb non disponible
         */
        'pusher' => [
            'driver'  => 'pusher',
            'key'     => env('PUSHER_APP_KEY'),
            'secret'  => env('PUSHER_APP_SECRET'),
            'app_id'  => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'eu'),
                'useTLS'  => true,
            ],
        ],

        /**
         * Redis — pour les queues
         */
        'redis' => [
            'driver'     => 'redis',
            'connection' => env('BROADCAST_REDIS_CONNECTION', 'default'),
        ],

        /**
         * Log — debug uniquement
         */
        'log' => [
            'driver' => 'log',
        ],

        /**
         * Null — désactivé
         */
        'null' => [
            'driver' => 'null',
        ],
    ],

];
