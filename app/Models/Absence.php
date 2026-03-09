<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Absence Model
 *
 * Enregistrement des absences/retards par cours.
 * Statuts: present | absent | late | excused
 */
class Absence extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'teacher_id',
        'date',
        'status',       // present | absent | late | excused
        'justified',
        'justification',
        'notified_at',  // When parent was notified
    ];

    protected $casts = [
        'date'       => 'date',
        'justified'  => 'boolean',
        'notified_at'=> 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Alias for backward compatibility.
     */
    public function isAbsent(): bool
    {
        return in_array($this->status, ['absent', 'late']);
    }
}
