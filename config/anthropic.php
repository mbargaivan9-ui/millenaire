<?php

/**
 * Configuration Anthropic Claude Haiku API
 * 
 * config/anthropic.php
 */

return [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20241022'),
    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 8000),
    'temperature' => (float) env('ANTHROPIC_TEMPERATURE', 0.1),
    'timeout' => (int) env('ANTHROPIC_TIMEOUT', 60),
    'retry_attempts' => (int) env('ANTHROPIC_RETRY_ATTEMPTS', 3),
    'rate_limit_per_min' => (int) env('ANTHROPIC_RATE_LIMIT_PER_MIN', 10),
];
