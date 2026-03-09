<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'       => 'boolean',
        'is_prof_principal' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classSubjectTeachers(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subject_teacher', 'teacher_id', 'subject_id')
                    ->withPivot('class_id')
                    ->distinct();
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'class_subject_teacher', 'teacher_id', 'class_id')
                    ->withPivot('subject_id')
                    ->distinct();
    }

    public function headClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'head_class_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(TeacherAvailability::class);
    }

    public function assignmentHistories(): HasMany
    {
        return $this->hasMany(TeacherAssignmentHistory::class, 'new_teacher_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(CourseMaterial::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrincipal($query)
    {
        return $query->where('is_prof_principal', true);
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? 'Enseignant';
    }
}
