<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Schedule Model — Emploi du Temps
 *
 * Créneaux horaires: matière + classe + enseignant + jour + heure
 */
class Schedule extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'day_of_week',   // 1=Monday … 6=Saturday
        'start_time',    // HH:MM
        'end_time',      // HH:MM
        'room',
        'academic_year',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active'   => 'boolean',
    ];

    // Day labels
    public static array $dayLabels = [
        'fr' => ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        'en' => ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
    ];

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
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Label du jour en français ou anglais.
     */
    public function dayLabel(string $locale = 'fr'): string
    {
        return self::$dayLabels[$locale][$this->day_of_week] ?? '—';
    }

    /**
     * Scope: créneaux du jour donné (1-6)
     */
    public function scopeForDay($query, int $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope: créneaux d'un enseignant
     */
    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}
