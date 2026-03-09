<?php

/**
 * OCR System Optimization Configuration
 * 
 * Configure image resizing, caching, and performance optimization
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    |
    | Configure how images are optimized before OCR processing
    | Reduces memory usage and improves processing speed
    |
    */
    'optimization' => [
        // Maximum width for images (larger images will be resized)
        'max_width' => env('OCR_MAX_WIDTH', 2000),

        // Maximum height for images (larger images will be resized)
        'max_height' => env('OCR_MAX_HEIGHT', 2000),

        // JPEG compression quality (0-100, higher = better quality but larger file)
        'jpeg_quality' => env('OCR_JPEG_QUALITY', 85),

        // PNG compression level (0-9, higher = smaller file but slower)
        'png_compression' => env('OCR_PNG_COMPRESSION', 6),

        // Enable image optimization before OCR
        'enabled' => env('OCR_OPTIMIZATION_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Cache OCR results to avoid re-processing identical images
    | Uses file hash to identify duplicate uploads
    |
    */
    'caching' => [
        // Enable result caching
        'enabled' => env('OCR_CACHING_ENABLED', true),

        // Cache driver (use Redis for better performance)
        'driver' => env('OCR_CACHE_DRIVER', 'redis'),

        // TTL in seconds (86400 = 24 hours)
        'ttl' => env('OCR_CACHE_TTL', 86400),

        // Cache directory path (for file-based caching)
        'path' => storage_path('cache/ocr'),

        // Maximum cache size in MB (0 = unlimited)
        'max_size_mb' => env('OCR_CACHE_MAX_SIZE', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch Processing
    |--------------------------------------------------------------------------
    |
    | Configuration for processing multiple files in parallel
    |
    */
    'batch' => [
        // Enable batch processing
        'enabled' => env('OCR_BATCH_ENABLED', true),

        // Maximum files in a single batch
        'max_files' => env('OCR_BATCH_MAX_FILES', 10),

        // Process files in parallel (requires queue)
        'parallel' => env('OCR_BATCH_PARALLEL', false),

        // Queue to use for batch jobs
        'queue' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Thresholds
    |--------------------------------------------------------------------------
    |
    | Warnings and limits for performance monitoring
    |
    */
    'thresholds' => [
        // Memory usage warning threshold (percent)
        'memory_warning_percent' => env('OCR_MEMORY_WARNING', 80),

        // Maximum processing time per file (seconds)
        'timeout_seconds' => env('OCR_TIMEOUT', 30),

        // Processing time warning threshold (seconds)
        'slow_processing_threshold' => env('OCR_SLOW_PROCESSING_THRESHOLD', 10),

        // File size warning threshold (MB)
        'large_file_threshold_mb' => env('OCR_LARGE_FILE_THRESHOLD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Temporary Files
    |--------------------------------------------------------------------------
    |
    | Management of temporary files created during processing
    |
    */
    'temp' => [
        // Temporary directory
        'path' => storage_path('temp'),

        // Delete temporary files after processing (hours)
        'retention_hours' => env('OCR_TEMP_RETENTION', 1),

        // Auto-delete on cleanup
        'auto_cleanup' => env('OCR_TEMP_AUTO_CLEANUP', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging & Monitoring
    |--------------------------------------------------------------------------
    |
    | Track performance metrics and issues
    |
    */
    'logging' => [
        // Log optimization operations
        'log_optimization' => env('OCR_LOG_OPTIMIZATION', true),

        // Log cache hits/misses
        'log_cache' => env('OCR_LOG_CACHE', true),

        // Log performance metrics
        'log_performance' => env('OCR_LOG_PERFORMANCE', true),

        // Performance metrics channel
        'channel' => env('OCR_METRICS_CHANNEL', 'single'),

        // Alert on slow processing (seconds)
        'alert_slow_threshold' => env('OCR_ALERT_SLOW_THRESHOLD', 20),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Query Optimization
    |--------------------------------------------------------------------------
    |
    | Cache frequently accessed data
    |
    */
    'db_optimization' => [
        // Cache BulletinStructure lookups
        'cache_structures' => env('OCR_CACHE_STRUCTURES', true),

        // Cache structure TTL (seconds)
        'structure_cache_ttl' => env('OCR_STRUCTURE_CACHE_TTL', 3600),

        // Use query select() to reduce data transfer
        'select_only_needed' => env('OCR_SELECT_ONLY_NEEDED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development & Testing
    |--------------------------------------------------------------------------
    |
    | Settings for development and testing environments
    |
    */
    'dev' => [
        // Disable optimization for debugging
        'disable_optimization' => env('OCR_DISABLE_OPTIMIZATION', false),

        // Disable caching for testing
        'disable_caching' => env('OCR_DISABLE_CACHING', false),

        // Keep temporary files for inspection
        'keep_temp_files' => env('OCR_KEEP_TEMP_FILES', false),

        // Verbose logging
        'verbose' => env('OCR_OPTIMIZATION_VERBOSE', false),
    ],
];
