<?php

namespace App\Contracts;

use App\DTOs\CarouselImageDTO;

/**
 * Interface pour le service de carousel
 * Suit le Interface Segregation Principle du SOLID
 * 
 * @author Laravel 12 - Millénaire Connect
 */
interface ImageResolverInterface
{
    /**
     * Résout une URL d'image à partir d'un chemin
     * Supporte les URLs absolues, chemins relatifs, et fallbacks
     * 
     * @param string|null $path - Le chemin ou URL de base
     * @return string|null - L'URL résolue ou null
     */
    public function resolveUrl(?string $path): ?string;

    /**
     * Crée une image SVG placeholder en base64
     * 
     * @param string $title - Le titre de l'image
     * @param string $gradient - Le dégradé CSS
     * @return string - SVG en data URI
     */
    public function createPlaceholder(string $title, string $gradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'): string;
}
