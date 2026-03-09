<?php

namespace App\Helpers;

class LevelHelper
{
    /**
     * Obtenir tous les niveaux francophones
     */
    public static function getFrancophoneLevels(): array
    {
        return [
            '6e' => '6ème',
            '5e' => '5ème',
            '4e' => '4ème',
            '3e' => '3ème',
            '2nde' => '2nde',
            '1ere' => '1ère',
            'terminale' => 'Terminale',
        ];
    }

    /**
     * Obtenir tous les niveaux anglophones
     */
    public static function getAnglophoneLevels(): array
    {
        return [
            'form1' => 'Form 1',
            'form2' => 'Form 2',
            'form3' => 'Form 3',
            'form4' => 'Form 4',
            'form5' => 'Form 5',
            'lower_sixth' => 'Lower Sixth',
            'upper_sixth' => 'Upper Sixth',
        ];
    }

    /**
     * Obtenir tous les niveaux (francophone + anglophone)
     */
    public static function getAllLevels(): array
    {
        return array_merge(self::getFrancophoneLevels(), self::getAnglophoneLevels());
    }

    /**
     * Obtenir les niveaux traduits selon la locale
     */
    public static function getLevelsForLocale(string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        
        if ($locale === 'en') {
            return array_merge(
                self::getAnglophoneLevels(),
                [
                    '6e' => 'Grade 7',
                    '5e' => 'Grade 8',
                    '4e' => 'Grade 9',
                    '3e' => 'Grade 10',
                    '2nde' => 'Grade 11',
                    '1ere' => 'Grade 12',
                    'terminale' => 'Grade 13',
                ]
            );
        }
        
        return self::getAllLevels();
    }

    /**
     * Obtenir la traduction d'un niveau
     */
    public static function getTranslation(string $level, string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $levels = self::getLevelsForLocale($locale);
        
        return $levels[$level] ?? $level;
    }

    /**
     * Vérifier si un niveau est francophone
     */
    public static function isFrancophone(string $level): bool
    {
        return in_array($level, array_keys(self::getFrancophoneLevels()));
    }

    /**
     * Vérifier si un niveau est anglophone
     */
    public static function isAnglophone(string $level): bool
    {
        return in_array($level, array_keys(self::getAnglophoneLevels()));
    }

    /**
     * Obtenir le système scolaire d'un niveau
     */
    public static function getSystem(string $level): ?string
    {
        if (self::isFrancophone($level)) {
            return 'francophone';
        }
        if (self::isAnglophone($level)) {
            return 'anglophone';
        }
        return null;
    }

    /**
     * Obtenir le groupe du niveau
     */
    public static function getGroup(string $level): ?string
    {
        $francoGroups = [
            '6e' => 'lower_secondary',
            '5e' => 'lower_secondary',
            '4e' => 'lower_secondary',
            '3e' => 'lower_secondary',
            '2nde' => 'upper_secondary',
            '1ere' => 'upper_secondary',
            'terminale' => 'upper_secondary',
        ];
        
        $angloGroups = [
            'form1' => 'lower_secondary',
            'form2' => 'lower_secondary',
            'form3' => 'lower_secondary',
            'form4' => 'upper_secondary',
            'form5' => 'upper_secondary',
            'lower_sixth' => 'sixth_form',
            'upper_sixth' => 'sixth_form',
        ];
        
        return $francoGroups[$level] ?? $angloGroups[$level] ?? null;
    }
}
