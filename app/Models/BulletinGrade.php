<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * BulletinGrade
 * 
 * Grade entry for a student in a specific subject
 * Contains both class and composition grades, with auto-calculated average
 */
class BulletinGrade extends Model
{
    use SoftDeletes;

    protected $table = 'bulletin_grades';

    protected $fillable = [
        'bulletin_id',
        'subject_id',
        'teacher_id',
        'note_classe',
        'note_composition',
        'subject_average',
        'subject_rank',
        'appreciation',
        'is_locked',
        'entered_at',
        'entered_by',
    ];

    protected $casts = [
        'note_classe' => 'float',
        'note_composition' => 'float',
        'subject_average' => 'float',
        'subject_rank' => 'integer',
        'is_locked' => 'boolean',
        'entered_at' => 'datetime',
    ];

    // Relations
    public function bulletin(): BelongsTo
    {
        return $this->belongsTo(StudentBulletin::class, 'bulletin_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    // Scopes
    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    // Helpers
    public function isComplete(): bool
    {
        return $this->note_classe !== null && $this->note_composition !== null;
    }

    public function hasAverage(): bool
    {
        return $this->subject_average !== null;
    }
}
