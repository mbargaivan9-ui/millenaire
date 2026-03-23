<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GradeScale — Barème d'appréciation configurable par établissement.
 *
 * Exemple de barème par défaut :
 *  0–9.99   → Insuffisant  (#FF4444)
 *  10–12.99 → Assez Bien   (#FF9900)
 *  13–15.99 → Bien         (#00AA44)
 *  16–18.99 → Très Bien    (#0066CC)
 *  19–20    → Excellent    (#8800CC)
 */
class GradeScale extends Model
{
    protected $table = 'grade_scales';

    protected $fillable = [
        'establishment_setting_id',
        'min_value',
        'max_value',
        'label',
        'color_hex',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'min_value'  => 'float',
        'max_value'  => 'float',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function establishmentSetting(): BelongsTo
    {
        return $this->belongsTo(EstablishmentSetting::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Retourne le label + couleur pour une moyenne donnée.
     * Utilise les barèmes de l'établissement, ou le barème global si null.
     */
    public static function getAppreciationFor(float $average, ?int $establishmentSettingId = null): array
    {
        $scale = static::query()
            ->when($establishmentSettingId, fn($q) => $q->where('establishment_setting_id', $establishmentSettingId))
            ->where('min_value', '<=', $average)
            ->where('max_value', '>=', $average)
            ->where('is_active', true)
            ->first();

        if (!$scale) {
            return ['label' => 'N/A', 'color' => '#999999'];
        }

        return ['label' => $scale->label, 'color' => $scale->color_hex];
    }
}
