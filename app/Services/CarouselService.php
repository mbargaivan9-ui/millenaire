<?php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\CarouselServiceInterface;
use App\Contracts\ImageResolverInterface;
use App\DTOs\CarouselImageDTO;
use Illuminate\Filesystem\Filesystem;

/**
 * Service pour la gestion des carousels
 * Implémente les interfaces Carousel et ImageResolver
 * Suit les principes SOLID
 * 
 * @author Laravel 12 - Millénaire Connect
 */
class CarouselService implements CarouselServiceInterface, ImageResolverInterface
{
    public function __construct(private Filesystem $files)
    {
    }

    /**
     * Obtient les images du carousel à partir des paramètres
     * @param array $settings - Les paramètres contenant carousel_images
     * @return CarouselImageDTO[] - Tableau d'images du carousel
     */
    public function getFromSettings(array $settings): array
    {
        $raw = $settings['carousel_images'] ?? [];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        $images = [];
        foreach ($raw as $item) {
            $url = $this->resolveUrl($item);
            $images[] = new CarouselImageDTO($url, null, null, null);
        }

        return $images;
    }

    /**
     * Résout l'URL d'une image avec support multi-formats
     * Tries: URL absolue -> fichier local -> SVG -> placeholder
     * 
     * @param string|null $path - Le chemin ou URL de base
     * @return string|null - L'URL résolue ou null
     */
    public function resolveUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // URL absolue
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $trim = ltrim($path, '/');

        // Resolve les chemins publics avec robustesse
        $publicBase = $this->getPublicPath();
        $storageAppPublic = $this->getStoragePath();

        $candidates = [
            $publicBase . DIRECTORY_SEPARATOR . $trim,
            $publicBase . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $trim,
            $publicBase . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $trim,
            $publicBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $trim,
            $storageAppPublic . DIRECTORY_SEPARATOR . $trim,
        ];

        foreach ($candidates as $candidate) {
            if ($this->files->exists($candidate)) {
                return $this->convertToUrl($candidate, $publicBase, $storageAppPublic);
            }
        }

        // Chercher des variantes SVG si le fichier n'existe pas
        $noExt = preg_replace('/\.[^.]+$/', '', $trim);
        $svgCandidates = [
            $publicBase . DIRECTORY_SEPARATOR . $noExt . '.svg',
            $publicBase . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $noExt . '.svg',
            $storageAppPublic . DIRECTORY_SEPARATOR . $noExt . '.svg',
        ];

        foreach ($svgCandidates as $sc) {
            if ($this->files->exists($sc)) {
                return $this->convertToUrl($sc, $publicBase, $storageAppPublic);
            }
        }

        return null;
    }

    /**
     * Crée une image SVG placeholder en base64
     * 
     * @param string $title - Le titre de l'image
     * @param string $gradient - Le dégradé CSS
     * @return string - SVG en data URI
     */
    public function createPlaceholder(string $title, string $gradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'): string
    {
        // Remplacer les caractères spéciaux pour éviter les problèmes d'encodage
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="1200" height="600" fill="url(#grad)"/>
    <text x="50%" y="50%" font-size="42" fill="white" text-anchor="middle" 
          dominant-baseline="middle" font-weight="bold">
        $title
    </text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Obtient le chemin public avec robustesse
     * @return string
     */
    private function getPublicPath(): string
    {
        if (function_exists('public_path') && is_object(app()) && method_exists(app(), 'publicPath')) {
            return public_path();
        }

        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Obtient le chemin storage avec robustesse
     * @return string
     */
    private function getStoragePath(): string
    {
        if (is_object(app()) && method_exists(app(), 'storagePath')) {
            return storage_path('app/public');
        }

        $projectRoot = dirname(__DIR__, 2);
        return $projectRoot . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Convertit un chemin de fichier en URL
     * @param string $candidate - Le chemin du fichier
     * @param string $publicBase - Le chemin public de base
     * @param string $storageBase - Le chemin storage de base
     * @return string - L'URL convertie
     */
    private function convertToUrl(string $candidate, string $publicBase, string $storageBase): string
    {
        $publicBaseNormalized = str_replace('\\', '/', rtrim($publicBase, DIRECTORY_SEPARATOR));
        $candidateNormalized = str_replace('\\', '/', $candidate);

        if (str_starts_with($candidateNormalized, $publicBaseNormalized)) {
            $relative = ltrim(str_replace($publicBaseNormalized, '', $candidateNormalized), '/');
            return asset($relative);
        }

        $storageBaseNormalized = str_replace('\\', '/', rtrim($storageBase, DIRECTORY_SEPARATOR));
        if (str_starts_with($candidateNormalized, $storageBaseNormalized)) {
            $relative = ltrim(str_replace($storageBaseNormalized, '', $candidateNormalized), '/');
            return asset('storage/' . $relative);
        }

        return asset($candidate);
    }
}
