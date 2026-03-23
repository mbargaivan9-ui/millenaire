<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BulletinNgConfig — Configuration d'une session de bulletin
 *
 * @property int    $id
 * @property int    $prof_principal_id
 * @property int    $class_id
 * @property string $langue             FR | EN
 * @property string $school_name
 * @property string $logo_path
 * @property string $delegation_fr
 * @property string $delegation_en
 * @property string $nom_classe
 * @property int    $effectif
 * @property int    $trimestre
 * @property int    $sequence
 * @property string $annee_academique
 * @property string $statut
 * @property bool   $notes_verrouillee
 */
class BulletinNgConfig extends Model
{
    protected $table = 'bulletin_ng_configs';

    protected $fillable = [
        'prof_principal_id', 'class_id', 'langue',
        'school_name', 'logo_path', 'delegation_fr', 'delegation_en',
        'nom_classe', 'effectif', 'trimestre', 'sequence', 'annee_academique',
        'statut', 'notes_verrouillee', 'notes_verrouillee_at',
    ];

    protected $casts = [
        'notes_verrouillee'    => 'boolean',
        'notes_verrouillee_at' => 'datetime',
        'effectif'             => 'integer',
        'trimestre'            => 'integer',
        'sequence'             => 'integer',
    ];

    // Statuts workflow
    const STATUT_CONFIG         = 'configuration';
    const STATUT_SAISIE_OUVERTE = 'saisie_ouverte';
    const STATUT_SAISIE_FERMEE  = 'saisie_fermee';
    const STATUT_CONDUITE       = 'conduite';
    const STATUT_GENERE         = 'genere';

    // ── Relations ─────────────────────────────────────────────────────────

    public function profPrincipal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prof_principal_id');
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(BulletinNgSubject::class, 'config_id')->orderBy('ordre');
    }

    public function students(): HasMany
    {
        return $this->hasMany(BulletinNgStudent::class, 'config_id')->orderBy('ordre');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BulletinNgNote::class, 'config_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isEN(): bool
    {
        return $this->langue === 'EN';
    }

    public function isSaisieOuverte(): bool
    {
        return $this->statut === self::STATUT_SAISIE_OUVERTE;
    }

    public function getTrimesteLabelAttribute(): string
    {
        return $this->isEN()
            ? match($this->trimestre) { 1 => '1st Term', 2 => '2nd Term', 3 => '3rd Term', default => "Term {$this->trimestre}" }
            : "{$this->trimestre}er Trimestre";
    }

    /**
     * Calcule les statistiques de la classe (moyennes, rangs, % réussite)
     */
    public function computeClassStats(): array
    {
        $students = $this->students()->with(['notes.subject'])->get();
        $subjects = $this->subjects;

        $avgs = [];
        foreach ($students as $student) {
            $totalPts  = 0;
            $totalCoef = 0;
            foreach ($subjects as $sub) {
                $note = $student->notes->firstWhere('ng_subject_id', $sub->id);
                if ($note && $note->note !== null) {
                    $totalPts  += $note->note * $sub->coefficient;
                    $totalCoef += $sub->coefficient;
                }
            }
            $avgs[$student->id] = $totalCoef > 0 ? $totalPts / $totalCoef : 0;
        }

        arsort($avgs);
        $ranks = [];
        $i = 1;
        foreach ($avgs as $sid => $avg) {
            $ranks[$sid] = $i++;
        }

        $avgValues = array_values($avgs);
        $passing   = count(array_filter($avgValues, fn($a) => $a >= 10));
        $total     = count($avgValues);

        return [
            'avgs'    => $avgs,
            'ranks'   => $ranks,
            'avg'     => $total > 0 ? array_sum($avgValues) / $total : 0,
            'max'     => $total > 0 ? max($avgValues) : 0,
            'min'     => $total > 0 ? min($avgValues) : 0,
            'passing' => $passing,
            'pct'     => $total > 0 ? round($passing / $total * 100) : 0,
        ];
    }
}
