<?php

return [
    /**
     * Default payment provider
     */
    'default' => env('PAYMENT_PROVIDER', 'campay'),

    /**
     * Campay Configuration (Orange Money & MTN Mobile Money Aggregator)
     */
    'campay' => [
        'api_key' => env('CAMPAY_API_KEY', ''),
        'api_url' => env('CAMPAY_API_URL', 'https://api.campay.net'),
        'webhook_secret' => env('CAMPAY_WEBHOOK_SECRET', ''),
        'timeout' => 30,
    ],

    /**
     * Orange Money Direct Configuration
     */
    'orange' => [
        'api_key' => env('ORANGE_MONEY_API_KEY', ''),
        'api_url' => env('ORANGE_MONEY_API_URL', 'https://api.orange.com'),
        'merchant_id' => env('ORANGE_MONEY_MERCHANT_ID', ''),
        'webhook_secret' => env('ORANGE_MONEY_WEBHOOK_SECRET', ''),
        'timeout' => 30,
    ],

    /**
     * MTN Mobile Money Direct Configuration
     */
    'mtn' => [
        'api_key' => env('MTN_MONEY_API_KEY', ''),
        'api_url' => env('MTN_MONEY_API_URL', 'https://api.mtn.cm'),
        'merchant_id' => env('MTN_MONEY_MERCHANT_ID', ''),
        'webhook_secret' => env('MTN_MONEY_WEBHOOK_SECRET', ''),
        'timeout' => 30,
    ],

    /**
     * Payment Configuration
     */
    'payments' => [
        /**
         * Currency for transactions
         */
        'currency' => env('PAYMENT_CURRENCY', 'XAF'),

        /**
         * Minimum and maximum transaction amounts
         */
        'min_amount' => env('PAYMENT_MIN_AMOUNT', 1000),
        'max_amount' => env('PAYMENT_MAX_AMOUNT', 5000000),

        /**
         * Default school fees amount
         */
        'default_tuition_fee' => env('PAYMENT_DEFAULT_TUITION_FEE', 500000),

        /**
         * Payment receipt configuration
         */
        'receipt' => [
            'issuer' => env('APP_NAME', 'MillÃ©naire connect'),
            'show_qr' => true,
            'store_path' => 'receipts',
        ],

        /**
         * Retry configuration for failed payments
         */
        'retry' => [
            'enabled' => true,
            'max_attempts' => 3,
            'delay_minutes' => 5,
        ],

        /**
         * Payment status check interval (in minutes)
         */
        'status_check_interval' => 5,

        /**
         * IP whitelist for webhooks (empty = allow all)
         */
        'webhook_ips' => [
            '100.100.100.1', // Campay example
            // Add more IPs as needed
        ],
    ],

    /**
     * Payment purposes / categories
     */
    'purposes' => [
        'tuition_fees' => 'School Tuition Fees',
        'exam_fees' => 'Examination Fees',
        'uniform' => 'School Uniform',
        'books' => 'Books & Materials',
        'registration' => 'Registration Fee',
        'other' => 'Other',
    ],

    /**
     * Student financial status configuration
     */
    'financial_status' => [
        'paid' => [
            'label' => 'Fully Paid',
            'color' => 'success',
            'description' => 'All fees paid',
        ],
        'partial' => [
            'label' => 'Partially Paid',
            'color' => 'warning',
            'description' => 'Some fees paid',
        ],
        'unpaid' => [
            'label' => 'Unpaid',
            'color' => 'danger',
            'description' => 'No payments made',
        ],
        'overdue' => [
            'label' => 'Overdue',
            'color' => 'danger',
            'description' => 'Payment overdue',
        ],
    ],
];

