<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BulletinNgStudent — Élève d'une session de bulletin
 */
class BulletinNgStudent extends Model
{
    protected $table    = 'bulletin_ng_students';
    protected $fillable = [
        'config_id', 'student_id', 'matricule', 'nom',
        'date_naissance', 'lieu_naissance', 'sexe', 'ordre',
    ];
    protected $casts = ['date_naissance' => 'date'];

    public function config(): BelongsTo
    {
        return $this->belongsTo(BulletinNgConfig::class, 'config_id');
    }

    public function originalStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BulletinNgNote::class, 'ng_student_id');
    }

    public function conduite(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BulletinNgConduite::class, 'ng_student_id');
    }

    /** Moyenne pondérée de l'élève */
    public function getMoyenneAttribute(): float
    {
        $totalPts  = 0;
        $totalCoef = 0;
        foreach ($this->notes as $note) {
            if ($note->note !== null) {
                $totalPts  += $note->note * $note->subject->coefficient;
                $totalCoef += $note->subject->coefficient;
            }
        }
        return $totalCoef > 0 ? round($totalPts / $totalCoef, 2) : 0;
    }

    /** Appréciation selon la langue */
    public function getAppreciation(string $langue = 'FR'): string
    {
        $moy = $this->getMoyenneAttribute();
        if ($langue === 'EN') {
            return match(true) {
                $moy < 10  => 'Fail',
                $moy < 12  => 'Pass',
                $moy < 15  => 'Fairly Good',
                $moy < 17  => 'Good',
                default    => 'Excellent',
            };
        }
        return match(true) {
            $moy < 10  => 'Échec',
            $moy < 12  => 'Passable',
            $moy < 15  => 'Assez Bien',
            $moy < 17  => 'Bien',
            default    => 'Excellent',
        };
    }
}
