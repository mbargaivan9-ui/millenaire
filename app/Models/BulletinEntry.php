<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * BulletinEntry
 *
 * Modèle central du Bulletin Vivant.
 * Chaque instance représente la note d'un élève pour une matière, séquence et trimestre.
 *
 * @property int    $id
 * @property int    $student_id
 * @property int    $class_subject_teacher_id
 * @property int    $class_id
 * @property int    $subject_id
 * @property int    $term
 * @property int    $sequence
 * @property string $academic_year
 * @property float|null $score
 * @property float  $coefficient
 * @property bool   $excused_absence
 * @property string|null $teacher_comment
 * @property string|null $auto_appreciation
 * @property bool   $is_locked
 */
class BulletinEntry extends Model
{
    use HasFactory;

    protected $table = 'bulletin_entries';

    protected $fillable = [
        'student_id',
        'class_subject_teacher_id',
        'class_id',
        'subject_id',
        'term',
        'sequence',
        'academic_year',
        'score',
        'coefficient',
        'excused_absence',
        'teacher_comment',
        'auto_appreciation',
        'recorded_by',
        'recorded_at',
        'last_modified_by',
        'is_locked',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'score'           => 'float',
        'coefficient'     => 'float',
        'excused_absence' => 'boolean',
        'is_locked'       => 'boolean',
        'recorded_at'     => 'datetime',
        'locked_at'       => 'datetime',
    ];

    // ─────────────────────────────────────────────
    //  Relations
    // ─────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSubjectTeacher(): BelongsTo
    {
        return $this->belongsTo(ClassSubjectTeacher::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    // ─────────────────────────────────────────────
    //  Scopes
    // ─────────────────────────────────────────────

    public function scopeForTerm($query, int $term)
    {
        return $query->where('term', $term);
    }

    public function scopeForSequence($query, int $sequence)
    {
        return $query->where('sequence', $sequence);
    }

    public function scopeForAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeNotLocked($query)
    {
        return $query->where('is_locked', false);
    }

    // ─────────────────────────────────────────────
    //  Business methods
    // ─────────────────────────────────────────────

    /**
     * Vérifie si la note contribue au calcul (présente et non excusée).
     */
    public function countsInAverage(): bool
    {
        return $this->score !== null && ! $this->excused_absence;
    }

    /**
     * Pondération de la note (score × coefficient).
     */
    public function weightedScore(): float
    {
        return $this->countsInAverage() ? ($this->score * $this->coefficient) : 0.0;
    }
}
