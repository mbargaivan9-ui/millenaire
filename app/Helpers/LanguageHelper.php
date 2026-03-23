<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Multilingue Helper Service
 * Service d'aide pour gérer le multilingue de la plateforme
 */
class LanguageHelper
{
    /**
     * Langues supportées
     */
    public const SUPPORTED_LANGUAGES = ['fr', 'en'];
    public const DEFAULT_LANGUAGE = 'fr';

    /**
     * Obtenir la locale actuelle
     */
    public static function getCurrentLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Obtenir la locale actuelle (code court)
     */
    public static function getLocaleCode(): string
    {
        $locale = self::getCurrentLocale();
        return explode('_', $locale)[0];
    }

    /**
     * Vérifier si la langue actuelle est le français
     */
    public static function isFrench(): bool
    {
        return self::getLocaleCode() === 'fr';
    }

    /**
     * Vérifier si la langue actuelle est l'anglais
     */
    public static function isEnglish(): bool
    {
        return self::getLocaleCode() === 'en';
    }

    /**
     * Définir la locale
     */
    public static function setLocale(string $locale): void
    {
        if (in_array($locale, self::SUPPORTED_LANGUAGES)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            auth()->user()?->update(['preferred_language' => $locale]);
        }
    }

    /**
     * Obtenir le nom de la langue
     */
    public static function getLanguageName(string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();

        return match ($locale) {
            'fr' => 'Français',
            'en' => 'English',
            default => 'Unknown'
        };
    }

    /**
     * Obtenir la liste des langues avec leurs métadonnées
     */
    public static function getAvailableLanguages(): array
    {
        return [
            'fr' => [
                'name' => 'Français',
                'code' => 'FR',
                'flag' => '🇫🇷',
                'direction' => 'ltr',
            ],
            'en' => [
                'name' => 'English',
                'code' => 'EN',
                'flag' => '🇺🇸',
                'direction' => 'ltr',
            ],
        ];
    }

    /**
     * Obtenir les métadonnées d'une langue
     */
    public static function getLanguageInfo(string $locale = null): array
    {
        $locale = $locale ?? self::getCurrentLocale();
        $languages = self::getAvailableLanguages();

        return $languages[$locale] ?? [];
    }

    /**
     * Traduire un texte
     */
    public static function translate(string $key, array $replace = [], $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();

        return __($key, $replace, $locale);
    }

    /**
     * Traduire une clé avec un fallback
     */
    public static function transChoice(string $key, int $count, array $replace = [], $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLocale();

        return trans_choice($key, $count, $replace, $locale);
    }

    /**
     * Obtenir l'URL pour changer la langue
     */
    public static function getLanguageSwitchUrl(string $locale): string
    {
        return route('lang.switch', $locale);
    }

    /**
     * Obtenir le direction du texte (ltr/rtl)
     */
    public static function getTextDirection(): string
    {
        return self::getLanguageInfo()['direction'] ?? 'ltr';
    }

    /**
     * Vérifier si la langue est RTL
     */
    public static function isRtl(): bool
    {
        return self::getTextDirection() === 'rtl';
    }

    /**
     * Obtenir l'attribut HTML lang
     */
    public static function getHtmlLang(): string
    {
        return str_replace('_', '-', self::getCurrentLocale());
    }

    /**
     * Obtenir l'attribut HTML dir
     */
    public static function getHtmlDir(): string
    {
        return self::getTextDirection();
    }
}
