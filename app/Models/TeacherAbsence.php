<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TeacherAbsence extends Model
{
    use HasFactory;

    protected $table = 'teacher_absences';
    
    protected $fillable = [
        'teacher_id',
        'date',
        'status',
        'reason',
        'justification_document',
        'recorded_by',
        'recorded_at',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'recorded_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_approved' => 'boolean',
    ];

    // ──────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function teacherUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id', 'teacher_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeJustified($query)
    {
        return $query->where('status', 'justified');
    }

    public function scopeUnjustified($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeAbsent($query)
    {
        return $query->whereIn('status', ['absent', 'late']);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('date', now()->year);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // ──────────────────────────────────────────────
    // Méthodes
    // ──────────────────────────────────────────────

    /**
     * Approuver l'absence
     */
    public function approve(string $approvedBy): void
    {
        $this->update([
            'is_approved' => true,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Rejeter l'absence
     */
    public function reject(): void
    {
        $this->update([
            'is_approved' => false,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Vérifier si l'absence est justifiée
     */
    public function isJustified(): bool
    {
        return in_array($this->status, ['justified', 'medical_leave', 'authorized_leave']);
    }

    /**
     * Vérifier si l'absence en attente d'approbation
     */
    public function isPending(): bool
    {
        return !$this->is_approved;
    }

    /**
     * Obtenir la raison lisible
     */
    public function getReadableStatus(): string
    {
        return match($this->status) {
            'present' => 'Présent',
            'absent' => 'Absent',
            'late' => 'Retard',
            'justified' => 'Justifié',
            'medical_leave' => 'Congé Médical',
            'authorized_leave' => 'Congé Autorisé',
            default => 'Inconnu',
        };
    }

    /**
     * Obtenir la classe CSS pour le statut
     */
    public function getStatusClass(): string
    {
        return match($this->status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'justified' => 'info',
            'medical_leave' => 'secondary',
            'authorized_leave' => 'secondary',
            default => 'light',
        };
    }
}
