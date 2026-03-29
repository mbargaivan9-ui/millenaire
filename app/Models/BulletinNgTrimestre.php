<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BulletinNgTrimestre — Moyennes calculées par trimestre
 * 
 * Stockage des moyennes calculées et du classement par trimestre
 * pour éviter la recalculation à chaque requête.
 * 
 * @property int           $id
 * @property int           $config_id
 * @property int           $ng_student_id
 * @property int           $trimestre_number
 * @property float|null    $moyenne
 * @property int|null      $rang_classe
 * @property int|null      $effectif_total
 */
class BulletinNgTrimestre extends Model
{
    protected $table = 'bulletin_ng_trimestres';

    protected $fillable = [
        'config_id',
        'ng_student_id',
        'trimestre_number',
        'moyenne',
        'rang_classe',
        'effectif_total',
    ];

    protected $casts = [
        'moyenne'       => 'float',
        'rang_classe'   => 'integer',
        'effectif_total' => 'integer',
    ];

    /**
     * Relation vers BulletinNgConfig
     */
    public function config(): BelongsTo
    {
        return $this->belongsTo(BulletinNgConfig::class, 'config_id');
    }

    /**
     * Relation vers BulletinNgStudent
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(BulletinNgStudent::class, 'ng_student_id');
    }

    /**
     * Obtenir l'appréciation selon la moyenne
     */
    public function getAppreciation(string $langue = 'FR'): string
    {
        $moy = $this->moyenne ?? 0;
        
        if ($langue === 'EN') {
            return match (true) {
                $moy < 10  => 'Fail',
                $moy < 12  => 'Pass',
                $moy < 15  => 'Fairly Good',
                $moy < 17  => 'Good',
                default    => 'Excellent',
            };
        }
        
        return match (true) {
            $moy < 10  => 'Échec',
            $moy < 12  => 'Passable',
            $moy < 15  => 'Assez Bien',
            $moy < 17  => 'Bien',
            default    => 'Excellent',
        };
    }

    /**
     * Obtenir le statut du classement
     */
    public function getRankStatus(): string
    {
        if (! $this->rang_classe || ! $this->effectif_total) {
            return 'N/A';
        }
        return "{$this->rang_classe}/{$this->effectif_total}";
    }
}
