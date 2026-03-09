<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classe extends Model
{
    protected $table = 'classes';

    protected $guarded = [];

    protected $casts = [
        'capacity' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'classe_id');
    }

    public function headTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'head_teacher_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'class_id', 'subject_id')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_subject_teacher', 'class_id', 'teacher_id')
                    ->withPivot('subject_id')
                    ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function bulletins(): HasMany
    {
        return $this->hasMany(Bulletin::class, 'class_id');
    }

    public function classSubjectTeachers(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'class_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFramcophone($query)
    {
        return $query->where('section', 'francophone');
    }

    public function scopeAnglophone($query)
    {
        return $query->where('section', 'anglophone');
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function getIsAngloAttribute(): bool
    {
        return $this->section === 'anglophone';
    }
}
