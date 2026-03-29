<?php

/**
 * SettingsHelper — Helpers pour les paramètres de la plateforme
 * 
 * Fournit des méthodes utiles pour accéder aux URLs des ressources
 * (logo, images, etc.) avec la bonne syntaxe Asset
 */

namespace App\Helpers;

use App\Models\EstablishmentSetting;
use Illuminate\Support\Facades\Storage;

class SettingsHelper
{
    /**
     * Obtient les paramètres de la plateforme
     */
    public static function getSettings(): EstablishmentSetting
    {
        return EstablishmentSetting::getInstance();
    }

    /**
     * Obtient l'URL du logo avec fallback
     */
    public static function logoUrl(): ?string
    {
        $settings = self::getSettings();
        $logoPath = $settings->logo_path ?? 'icons/icon-512.png';
        
        // Clean up the path
        if (stripos($logoPath, '/') === 0) {
            $logoPath = ltrim($logoPath, '/');
        }
        
        // Check if file exists - try both direct path and with storage prefix
        $fullPath = public_path($logoPath);
        $exists = file_exists($fullPath);
        
        // If logo path doesn't have storage prefix, try adding it
        if (!$exists && stripos($logoPath, 'storage/') !== 0) {
            $storageLogoPath = 'storage/' . $logoPath;
            if (file_exists(public_path($storageLogoPath))) {
                $logoPath = $storageLogoPath;
                $exists = true;
            }
        }
        
        return $exists ? asset($logoPath) : null;
    }

    /**
     * Obtient l'URL du favicon
     */
    public static function faviconUrl(): ?string
    {
        $settings = self::getSettings();
        
        if (!$settings->favicon_path) {
            return null;
        }

        return asset($settings->favicon_path);
    }

    /**
     * Obtient l'URL de l'image héro
     */
    public static function heroImageUrl(): ?string
    {
        $settings = self::getSettings();
        
        if (!$settings->hero_image) {
            return null;
        }

        return asset($settings->hero_image);
    }

    /**
     * Obtient l'URL de la photo du proviseur
     */
    public static function proviseurPhotoUrl(): ?string
    {
        $settings = self::getSettings();
        
        if (!$settings->proviseur_photo) {
            return null;
        }

        return asset($settings->proviseur_photo);
    }

    /**
     * Obtient l'URL de l'image de signature
     */
    public static function signatureUrl(): ?string
    {
        $settings = self::getSettings();
        
        if (!$settings->signature_image) {
            return null;
        }

        return asset($settings->signature_image);
    }

    /**
     * Obtient l'URL de l'image À Propos
     */
    public static function aboutImageUrl(): ?string
    {
        $settings = self::getSettings();
        
        if (!$settings->about_image) {
            return null;
        }

        return asset($settings->about_image);
    }

    /**
     * Obtient les URLs des images du carousel
     */
    public static function carouselImageUrls(): array
    {
        $settings = self::getSettings();
        
        if (!$settings->carousel_images) {
            return [];
        }

        $images = is_array($settings->carousel_images) 
            ? $settings->carousel_images 
            : json_decode($settings->carousel_images, true) ?? [];

        return array_map(fn($img) => asset($img), $images);
    }

    /**
     * Obtient les paramètres bruts
     */
    public static function get($key = null, $default = null)
    {
        $settings = self::getSettings();
        
        if ($key === null) {
            return $settings;
        }

        return $settings->$key ?? $default;
    }

    /**
     * Obtient la couleur primaire
     */
    public static function primaryColor(): string
    {
        return self::getSettings()->primary_color ?? '#0d9488';
    }

    /**
     * Obtient la couleur secondaire
     */
    public static function secondaryColor(): string
    {
        return self::getSettings()->secondary_color ?? '#0f766e';
    }

    /**
     * Obtient le nom de la plateforme
     */
    public static function platformName(): string
    {
        return self::getSettings()->platform_name ?? 'Millénaire Connect';
    }

    /**
     * Obtient le slogan
     */
    public static function slogan(): ?string
    {
        return self::getSettings()->slogan;
    }
}
