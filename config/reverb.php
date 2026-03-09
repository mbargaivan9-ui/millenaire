<?php

/**
 * config/reverb.php — Configuration Laravel Reverb (WebSocket)
 *
 * Reverb est le serveur WebSocket natif Laravel
 * Démarrage: php artisan reverb:start
 */

return [

    'servers' => [
        'reverb' => [
            'host'    => env('REVERB_SERVER_HOST', '0.0.0.0'),
            'port'    => env('REVERB_SERVER_PORT', 8080),
            'options' => [
                'tls' => [],
            ],
            'scaling' => [
                'enabled'    => env('REVERB_SCALING_ENABLED', false),
                'channel'    => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'connection' => env('REVERB_SCALING_CONNECTION', 'default'),
            ],
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', -1),
        ],
    ],

    'apps' => [
        [
            'key'                         => env('REVERB_APP_KEY', 'millenaire-connect-key'),
            'secret'                      => env('REVERB_APP_SECRET', 'millenaire-connect-secret'),
            'id'                          => env('REVERB_APP_ID', 'millenaire-connect'),
            'options'                     => [
                'host'    => env('REVERB_HOST', 'localhost'),
                'port'    => (int) env('REVERB_PORT', 8080),
                'scheme'  => env('REVERB_SCHEME', 'http'),
                'useTLS'  => env('REVERB_SCHEME', 'http') === 'https',
            ],
            'allowed_origins'             => explode(',', env('REVERB_ALLOWED_ORIGINS', '*')),
            'ping_interval'               => env('REVERB_PING_INTERVAL', 60),
            'max_message_size'            => env('REVERB_MAX_MESSAGE_SIZE', 10000),
        ],
    ],

];
