<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * BulletinSummary
 *
 * Résumé calculé d'un bulletin pour un élève, un trimestre et une année.
 * Contient les moyennes, le rang et le statut de validation.
 */
class BulletinSummary extends Model
{
    use HasFactory;

    protected $table = 'bulletin_summaries';

    protected $fillable = [
        'student_id',
        'class_id',
        'term',
        'academic_year',
        'sequence1_average',
        'sequence2_average',
        'term_average',
        'annual_average',
        'rank',
        'total_students',
        'rank_display',
        'status',
        'appreciation',
        'general_observation',
        'principal_teacher_comment',
        'validated_by',
        'validated_at',
        'locked_by',
        'locked_at',
        'pdf_path',
        'pdf_generated_at',
    ];

    protected $casts = [
        'sequence1_average' => 'float',
        'sequence2_average' => 'float',
        'term_average'      => 'float',
        'annual_average'    => 'float',
        'rank'              => 'integer',
        'total_students'    => 'integer',
        'validated_at'      => 'datetime',
        'locked_at'         => 'datetime',
        'pdf_generated_at'  => 'datetime',
    ];

    // ─────────────────────────────────────────────
    //  Relations
    // ─────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ─────────────────────────────────────────────
    //  Scopes
    // ─────────────────────────────────────────────

    public function scopeForClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForTerm($query, int $term)
    {
        return $query->where('term', $term);
    }

    public function scopeForYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // ─────────────────────────────────────────────
    //  Business methods
    // ─────────────────────────────────────────────

    public function isLocked(): bool
    {
        return in_array($this->status, ['locked', 'validated', 'published']);
    }

    public function isPassing(): bool
    {
        return ($this->term_average ?? 0) >= 10;
    }
}
