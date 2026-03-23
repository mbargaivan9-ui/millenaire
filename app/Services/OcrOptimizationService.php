<?php

namespace App\Services;

/**
 * OcrOptimizationService
 * Service pour l'optimisation des opérations OCR
 */
class OcrOptimizationService
{
    /**
     * Nettoyer le cache OCR
     */
    public function cleanCache(): void
    {
        // Implementation
    }

    /**
     * Optimiser les paramètres OCR
     */
    public function optimizeOcrSettings(): array
    {
        return [
            'enabled' => true,
            'cache_enabled' => true,
        ];
    }
}
