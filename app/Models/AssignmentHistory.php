<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentHistory extends Model
{
    use HasFactory;

    protected $table = 'assignment_histories';

    protected $fillable = [
        'old_teacher_id',
        'new_teacher_id',
        'class_id',
        'reason',
        'status',
        'assigned_at',
        'ended_at',
        'notes',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Relation with the old teacher (User model)
     */
    public function oldTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'old_teacher_id');
    }

    /**
     * Relation with the new teacher (User model)
     */
    public function newTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_teacher_id');
    }

    /**
     * Relation with the class
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Relation with the HR admin who made the assignment
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope to get active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->whereNull('ended_at');
    }

    /**
     * Scope to get completed assignments
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('ended_at');
    }

    /**
     * Scope to get assignments for a specific teacher
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('new_teacher_id', $teacherId)
                     ->orWhere('old_teacher_id', $teacherId);
    }

    /**
     * Scope to get assignments for a specific class
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Check if assignment is currently active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && is_null($this->ended_at);
    }

    /**
     * Mark assignment as ended
     */
    public function markAsEnded(?string $reason = null): void
    {
        $this->update([
            'status' => 'archived',
            'ended_at' => now(),
            'notes' => $reason ? ($this->notes . "\n\nEnded: " . $reason) : $this->notes,
        ]);
    }

    /**
     * Get the duration of the assignment in days
     */
    public function getDurationDays(): int
    {
        $endDate = $this->ended_at ?? now();
        return $this->assigned_at->diffInDays($endDate);
    }
}
