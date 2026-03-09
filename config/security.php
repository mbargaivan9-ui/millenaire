<?php

/**
 * Production Security Configuration
 * 
 * Configure security headers and policies for production environment
 */

return [
    'headers' => [
        'enabled' => true,
        
        // Strict Transport Security
        'hsts' => [
            'enabled' => true,
            'max_age' => 31536000, // 1 year
            'include_subdomains' => true,
            'preload' => true,
        ],

        // Content Security Policy
        'csp' => [
            'enabled' => true,
            'directives' => [
                'default-src' => ["'self'"],
                'script-src' => ["'self'", "'unsafe-inline'"],
                'style-src' => ["'self'", "'unsafe-inline'"],
                'img-src' => ["'self'", 'data:', 'https:'],
                'font-src' => ["'self'", 'data:'],
                'connect-src' => ["'self'"],
                'frame-ancestors' => ["'none'"],
            ],
        ],

        // X-Frame-Options (Clickjacking protection)
        'x-frame-options' => 'DENY',

        // X-Content-Type-Options
        'x-content-type-options' => 'nosniff',

        // X-XSS-Protection
        'x-xss-protection' => '1; mode=block',

        // Referrer Policy
        'referrer-policy' => 'strict-origin-when-cross-origin',
    ],

    'rate_limiting' => [
        'enabled' => true,
        
        // Global rate limiter
        'global' => [
            'requests' => 60,
            'period' => 1, // minutes
        ],

        // API rate limiter
        'api' => [
            'requests' => 100,
            'period' => 1,
        ],

        // Authentication rate limiter
        'auth' => [
            'requests' => 5,
            'period' => 1,
        ],

        // File upload rate limiter
        'upload' => [
            'requests' => 10,
            'period' => 1,
        ],
    ],

    'password_policy' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'expiry_days' => 90,
        'history_count' => 5, // Remember last 5 passwords
    ],

    'session' => [
        'timeout_minutes' => 30,
        'secure_cookie' => true,
        'http_only' => true,
        'same_site' => 'strict',
    ],

    'database' => [
        'encryption' => false, // Use TLS instead
        'ssl_verify' => true,
    ],

    'file_upload' => [
        'max_size_mb' => 50,
        'allowed_types' => [
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ],
        'scan_for_viruses' => false, // Requires ClamAV
        'quarantine_suspicious' => true,
    ],

    'logging' => [
        'sensitive_fields' => [
            'password',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'cvv',
        ],
    ],

    'cors' => [
        'enabled' => true,
        'allowed_origins' => [
            'https://yourdomain.com',
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allow_credentials' => true,
    ],
];
