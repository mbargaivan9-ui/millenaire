<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OCR Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour le système OCR du projet Millenaire
    | Supporte: OCR.Space (cloud) et Tesseract (local)
    |
    */

    // Backend OCR par défaut: 'ocr.space' ou 'tesseract'
    'backend' => env('OCR_BACKEND', 'tesseract'),

    // ──────── OCR.Space (Cloud API) ────────
    'ocr_space' => [
        'api_url' => 'https://api.ocr.space/parse/image',
        'api_key' => env('OCR_SPACE_API_KEY', 'K87899142586'),
        'timeout' => 60,
    ],

    // ──────── Tesseract (Local) ────────
    'tesseract' => [
        // Chemin vers l'exécutable tesseract
        // Windows: C:/Program Files/Tesseract-OCR/tesseract.exe (slashes /  not \)
        // Linux: /usr/bin/tesseract
        // Mac: /usr/local/bin/tesseract
        'path' => env('TESSERACT_PATH', 'C:/Program Files/Tesseract-OCR/tesseract.exe'),

        // Langues (fra = French, eng = English)
        'language' => env('OCR_LANGUAGE', 'fra+eng'),

        // Options additionnelles
        'options' => [
            '--psm' => 6,  // Page Segmentation Mode
            '--oem' => 3,  // OCR Engine Mode
        ],
    ],

    // ──────── Pytesseract (Python + Tesseract) ────────
    'pytesseract' => [
        'enabled' => env('PYTESSERACT_ENABLED', true),
        'language' => env('OCR_LANGUAGE', 'fra+eng'),
    ],

    // ──────── Confiance Minimale ────────
    'min_confidence' => env('OCR_MIN_CONFIDENCE', 60),

    // ──────── Timeout ────────
    'timeout' => env('OCR_TIMEOUT', 60),

    // ──────── Langues Disponibles ────────
    'languages' => [
        'fra' => 'Français',
        'eng' => 'Anglais',
        'deu' => 'Allemand',
        'spa' => 'Espagnol',
        'ita' => 'Italien',
        'por' => 'Portugais',
    ],
];
