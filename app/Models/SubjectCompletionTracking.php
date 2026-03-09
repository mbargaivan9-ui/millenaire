<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectCompletionTracking extends Model
{
    protected $table = 'subject_completion_tracking';

    protected $fillable = [
        'class_subject_teacher_id',
        'class_id',
        'term',
        'sequence',
        'academic_year',
        'total_students',
        'filled_count',
        'completion_percentage',
        'last_entry_at',
    ];

    protected $casts = [
        'completion_percentage' => 'float',
        'last_entry_at'         => 'datetime',
    ];

    public function classSubjectTeacher(): BelongsTo
    {
        return $this->belongsTo(ClassSubjectTeacher::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }
}
