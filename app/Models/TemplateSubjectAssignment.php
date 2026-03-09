<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TemplateSubjectAssignment
 * 
 * Pivot model that assigns teachers to subjects within a template
 * Controls permissions for grade entry and appreciation
 */
class TemplateSubjectAssignment extends Model
{
    protected $table = 'template_subject_assignments';

    public $timestamps = true;

    protected $fillable = [
        'template_id',
        'subject_id',
        'teacher_id',
        'can_enter_grades',
        'can_enter_appreciation',
        'access_granted_at',
        'access_granted_by',
    ];

    protected $casts = [
        'can_enter_grades' => 'boolean',
        'can_enter_appreciation' => 'boolean',
        'access_granted_at' => 'datetime',
    ];

    // Relations
    public function template(): BelongsTo
    {
        return $this->belongsTo(BulletinTemplate::class, 'template_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'access_granted_by');
    }

    // Scopes
    public function scopeByTemplate($query, $templateId)
    {
        return $query->where('template_id', $templateId);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('access_granted_at');
    }

    // Helpers
    public function hasGradePermission(): bool
    {
        return $this->can_enter_grades;
    }

    public function hasAppreciationPermission(): bool
    {
        return $this->can_enter_appreciation;
    }

    public function isActive(): bool
    {
        return $this->access_granted_at !== null;
    }
}
