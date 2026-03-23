<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/* ─────────────────────────────────────────────────────────────────
 *  BulletinNgSubject — Matière d'une session de bulletin
 * ───────────────────────────────────────────────────────────────── */
class BulletinNgSubject extends Model
{
    protected $table    = 'bulletin_ng_subjects';
    protected $fillable = ['config_id', 'nom', 'coefficient', 'nom_prof', 'ordre'];
    protected $casts    = ['coefficient' => 'float', 'ordre' => 'integer'];

    public function config(): BelongsTo
    {
        return $this->belongsTo(BulletinNgConfig::class, 'config_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BulletinNgNote::class, 'ng_subject_id');
    }

    /** Rang d'un élève dans cette matière parmi tous les élèves */
    public function getRankForStudent(int $studentId): int
    {
        $notes = $this->notes()->orderByDesc('note')->pluck('ng_student_id')->values();
        $pos   = $notes->search($studentId);
        return $pos !== false ? $pos + 1 : 0;
    }
}


/* ─────────────────────────────────────────────────────────────────
 *  BulletinNgStudent — Élève d'une session de bulletin
 * ───────────────────────────────────────────────────────────────── */
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


/* ─────────────────────────────────────────────────────────────────
 *  BulletinNgNote — Note d'un élève pour une matière
 * ───────────────────────────────────────────────────────────────── */
class BulletinNgNote extends Model
{
    protected $table    = 'bulletin_ng_notes';
    protected $fillable = [
        'config_id', 'ng_student_id', 'ng_subject_id',
        'note', 'saisie_par', 'saisie_at',
    ];
    protected $casts = [
        'note'      => 'float',
        'saisie_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(BulletinNgStudent::class, 'ng_student_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(BulletinNgSubject::class, 'ng_subject_id');
    }

    public function saiseur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }
}


/* ─────────────────────────────────────────────────────────────────
 *  BulletinNgConduite — Conduite & comportement d'un élève
 * ───────────────────────────────────────────────────────────────── */
class BulletinNgConduite extends Model
{
    protected $table    = 'bulletin_ng_conduites';
    protected $fillable = [
        'config_id', 'ng_student_id',
        'tableau_honneur', 'encouragement', 'felicitations', 'blame_travail', 'avert_travail',
        'absences_totales', 'absences_nj', 'exclusion', 'avert_conduite', 'blame_conduite',
    ];
    protected $casts = [
        'tableau_honneur'  => 'boolean',
        'encouragement'    => 'boolean',
        'felicitations'    => 'boolean',
        'blame_travail'    => 'boolean',
        'exclusion'        => 'boolean',
        'absences_totales' => 'integer',
        'absences_nj'      => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(BulletinNgStudent::class, 'ng_student_id');
    }
}
