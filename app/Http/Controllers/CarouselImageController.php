<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class CarouselImageController extends Controller
{
    /**
     * Génère une image de carousel par défaut
     * GET /carousel-image/{number}
     */
    public function generate($number = 1)
    {
        $publicPath = public_path('images');
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir($publicPath)) {
            mkdir($publicPath, 0755, true);
        }

        $slides = [
            1 => [
                'filename' => 'carousel-1.jpg',
                'color1' => '#0d9488',
                'color2' => '#14b8a6',
                'title' => 'Excellence Académique',
                'subtitle' => 'Transformez votre établissement',
            ],
            2 => [
                'filename' => 'carousel-2.jpg',
                'color1' => '#3b82f6',
                'color2' => '#60a5fa',
                'title' => 'Communication Sécurisée',
                'subtitle' => 'Connectez votre communauté',
            ],
            3 => [
                'filename' => 'carousel-3.jpg',
                'color1' => '#f59e0b',
                'color2' => '#fbbf24',
                'title' => 'Gestion Complète',
                'subtitle' => 'Tout en un seul endroit',
            ],
        ];

        if (!isset($slides[$number])) {
            $number = 1;
        }

        $slide = $slides[$number];
        $filePath = $publicPath . '/' . $slide['filename'];

        // Si l'image existe déjà, la servir
        if (file_exists($filePath)) {
            return response()->file($filePath);
        }

        // Vérifier si Intervention Image est disponible
        if (!class_exists('Intervention\Image\ImageManagerStatic')) {
            // Créer une image simple en SVG
            return $this->generateSVGImage($slide);
        }

        // Créer l'image avec Intervention
        try {
            $image = Image::canvas(1200, 500, $slide['color1']);
            
            // Ajouter un dégradé simple (rectangle semi-transparent)
            $color2 = $slide['color2'];
            
            $image->text($slide['title'], 600, 200, function ($font) {
                $font->file(public_path('fonts/Roboto-Bold.ttf'));
                $font->size(60);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('center');
            });

            $image->text($slide['subtitle'], 600, 300, function ($font) {
                $font->file(public_path('fonts/Roboto-Regular.ttf'));
                $font->size(28);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('center');
            });

            // Sauvegarder l'image
            $image->save($filePath, 85);
            
            return response()->file($filePath);
        } catch (\Exception $e) {
            // Fallback sur SVG
            return $this->generateSVGImage($slide);
        }
    }

    /**
     * Génère une image SVG comme fallback
     */
    private function generateSVGImage($slide)
    {
        $svg = <<<SVG
<svg width="1200" height="500" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad{$slide['filename']}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$slide['color1']};stop-opacity:1" />
            <stop offset="100%" style="stop-color:{$slide['color2']};stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="1200" height="500" fill="url(#grad{$slide['filename']})"/>
    <text x="600" y="200" font-family="Arial, sans-serif" font-size="60" font-weight="bold" fill="white" text-anchor="middle">
        {$slide['title']}
    </text>
    <text x="600" y="300" font-family="Arial, sans-serif" font-size="28" fill="white" text-anchor="middle">
        {$slide['subtitle']}
    </text>
</svg>
SVG;

        return response($svg)
            ->header('Content-Type', 'image/svg+xml');
    }
}
