<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Attendance Model — alias/aggregate for daily attendance stats
 * Maps to the absences table via scoped queries
 */
class Attendance extends Model
{
    protected $table = 'absences'; // Reuse absences table

    protected $fillable = [
        'student_id', 'class_id', 'subject_id',
        'teacher_id', 'date', 'status', 'justified',
    ];

    protected $casts = [
        'date'      => 'date',
        'justified' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Scope: present today
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope: absent
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope: late
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }
}
