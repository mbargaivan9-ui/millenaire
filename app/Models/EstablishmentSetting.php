<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstablishmentSetting extends Model
{
    protected $table = 'establishment_settings';
    
    protected $fillable = [
        'logo_path',
        'about_title',
        'about_description',
        'proviseur_name',
        'proviseur_bio',
        'proviseur_photo',
        'carousel_images',
        'phone',
        'email',
        'address',
    ];

    protected $casts = [
        'carousel_images' => 'array',
    ];

    /**
     * Get or create the establishment settings singleton
     */
    public static function getInstance()
    {
        try {
            return self::firstOrCreate(
                ['id' => 1],
                [
                    'logo_path' => 'img/logo-Millénaire connect.png',
                    'about_title' => 'É€ Propos de Millénaire connect',
                    'proviseur_name' => 'Monsieur Jean Dupont',
                    'carousel_images' => [
                        'img/carousel-1.svg',
                        'img/carousel-2.svg',
                        'img/carousel-3.svg',
                    ],
                ]
            );
        } catch (\Exception $e) {
            // Return a default instance when database is unavailable
            return new self([
                'id' => 1,
                'logo_path' => 'img/logo-Millénaire connect.png',
                'about_title' => 'Bienvenue É  Millénaire Connect',
                'about_description' => 'Excellence académique pour un avenir brillant.',
                'proviseur_name' => 'Monsieur Jean Dupont',
                'proviseur_bio' => 'Directeur de l\'établissement',
                'carousel_images' => [
                    'img/carousel-1.svg',
                    'img/carousel-2.svg',
                    'img/carousel-3.svg',
                ],
                'phone' => '+33 1 23 45 67 89',
                'email' => 'contact@millenaire.edu',
                'address' => 'Adresse de l\'établissement',
            ]);
        }
    }

    /**
     * Relation with exam results
     */
    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}

