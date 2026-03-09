<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BulletinStructure
 * 
 * Modèle pour stocker la structure dynamique d'un bulletin extrait via OCR
 * Contient: colonnes matières, coefficients, formules de calcul, règles moyennes
 */
class BulletinStructure extends Model
{
    protected $table = 'bulletin_structures';
    protected $fillable = [
        'classe_id',
        'name',
        'description',
        'source_image_path',
        'structure_json',      // Contient: colonnes, coefs, formules, règles
        'calculation_rules',   // Formules de calcul: moyenne, rang, appréciation
        'is_active',
        'created_by',
        'updated_by',
        'ocr_confidence',      // Score OCR de confiance (0-100)
        'is_verified',         // Admin a verrafié la structure
    ];

    protected $casts = [
        'structure_json' => 'array',
        'calculation_rules' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
    ];

    /**
     * Relation avec la classe
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    /**
     * Relation avec l'utilisateur créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur modifié par
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Récupérer la structure JSON parsée
     */
    public function getStructure(): array
    {
        return is_array($this->structure_json) ? $this->structure_json : json_decode($this->structure_json, true) ?? [];
    }

    /**
     * Récupérer les règles de calcul
     */
    public function getCalculationRules(): array
    {
        return is_array($this->calculation_rules) ? $this->calculation_rules : json_decode($this->calculation_rules, true) ?? [];
    }

    /**
     * Extraire toutes les matières de la structure
     */
    public function getSubjects(): array
    {
        $structure = $this->getStructure();
        return $structure['subjects'] ?? [];
    }

    /**
     * Extraire tous les coefficients
     */
    public function getCoefficients(): array
    {
        $structure = $this->getStructure();
        return $structure['coefficients'] ?? [];
    }

    /**
     * Récupérer une formule de calcul
     */
    public function getCalculationFormula(string $formulaKey): ?string
    {
        $rules = $this->getCalculationRules();
        return $rules['formulas'][$formulaKey] ?? null;
    }

    /**
     * Scope: Actifs seulement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Vérifiés seulement
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
