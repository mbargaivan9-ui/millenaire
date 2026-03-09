<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAssignmentHistory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function oldTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'old_teacher_id');
    }

    public function newTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'new_teacher_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
