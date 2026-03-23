<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $guarded = [];

    // ─── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'classe_id');
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian', 'student_id', 'guardian_id')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    public function bulletins(): HasMany
    {
        return $this->hasMany(Bulletin::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function quizSubmissions(): HasMany
    {
        return $this->hasMany(QuizSubmission::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeInClass($query, int $classId)
    {
        return $query->where('class_id', $classId);
    }

    // ─── Accessors ────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? ($this->first_name . ' ' . $this->last_name);
    }

    public function getDisplayMatAttribute(): string
    {
        return $this->matricule ?? 'N/A';
    }

    // ─── Payment Calculation Methods ───────────────────────────────────────

    /**
     * Get total amount due for this student (pending + processing)
     */
    public function getTotalAmountDue(): int
    {
        return (int) $this->payments()
            ->whereIn('status', ['pending', 'processing'])
            ->sum('amount');
    }

    /**
     * Get total amount paid by this student (success + completed)
     */
    public function getTotalAmountPaid(): int
    {
        return (int) $this->payments()
            ->whereIn('status', ['success', 'completed'])
            ->sum('amount');
    }

    /**
     * Get financial status for this student
     */
    public function getFinancialStatus(): string
    {
        $due = $this->getTotalAmountDue();
        $paid = $this->getTotalAmountPaid();

        if ($due == 0 && $paid > 0) {
            return 'paid';
        } elseif ($due > 0) {
            return 'pending';
        } else {
            return 'none';
        }
    }
}
