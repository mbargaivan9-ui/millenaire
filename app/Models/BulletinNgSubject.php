<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BulletinNgSubject — Matière d'une session de bulletin
 */
class BulletinNgSubject extends Model
{
    protected $table    = 'bulletin_ng_subjects';
    protected $fillable = ['config_id', 'user_id', 'nom', 'coefficient', 'nom_prof', 'ordre'];
    protected $casts    = ['coefficient' => 'float', 'ordre' => 'integer'];

    public function config(): BelongsTo
    {
        return $this->belongsTo(BulletinNgConfig::class, 'config_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
