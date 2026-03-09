<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentAbsence extends Model
{
    use HasFactory;

    protected $table = 'student_absences';
    
    protected $fillable = [
        'student_id',
        'classe_id',
        'date',
        'status',
        'justification_reason',
        'justification_document',
        'notes',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'date' => 'date',
        'recorded_at' => 'datetime',
    ];

    // ──────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────

    /**
     * Get the student associated with this absence
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class associated with this absence
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Filter absences by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Filter absences between dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Filter by class
     */
    public function scopeByClasse($query, $classeId)
    {
        return $query->where('classe_id', $classeId);
    }

    /**
     * Filter by student
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Filter justified absences
     */
    public function scopeJustified($query)
    {
        return $query->whereNotNull('justification_reason');
    }

    /**
     * Filter unjustified absences
     */
    public function scopeUnjustified($query)
    {
        return $query->whereNull('justification_reason');
    }

    /**
     * Filter absent status
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Filter late status
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Filter present status
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Get the status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'absent' => 'danger',
            'late' => 'warning',
            'present' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Get whether the absence is justified
     */
    public function getIsJustifiedAttribute(): bool
    {
        return !is_null($this->justification_reason);
    }

    // ──────────────────────────────────────────────
    // Methods
    // ──────────────────────────────────────────────

    /**
     * Add justification to an absence
     */
    public function addJustification(string $reason, ?string $documentPath = null): void
    {
        $this->update([
            'justification_reason' => $reason,
            'justification_document' => $documentPath,
        ]);
    }

    /**
     * Get total absences count for a student in a class
     */
    public static function getTotalAbsencesForStudent($studentId, $classeId = null): int
    {
        $query = self::where('student_id', $studentId)->absent();
        
        if ($classeId) {
            $query->where('classe_id', $classeId);
        }
        
        return $query->count();
    }

    /**
     * Get justified absences count for a student
     */
    public static function getJustifiedAbsencesForStudent($studentId, $classeId = null): int
    {
        $query = self::where('student_id', $studentId)->justified();
        
        if ($classeId) {
            $query->where('classe_id', $classeId);
        }
        
        return $query->count();
    }

    /**
     * Get unjustified absences count for a student
     */
    public static function getUnjustifiedAbsencesForStudent($studentId, $classeId = null): int
    {
        $query = self::where('student_id', $studentId)->unjustified()->absent();
        
        if ($classeId) {
            $query->where('classe_id', $classeId);
        }
        
        return $query->count();
    }
}
