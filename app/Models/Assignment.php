<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $table = 'assignments';
    protected $fillable = [
        'prof_id',
        'class_id',
        'subject_id',
        'assignment_type',
        'schedule',
        'room',
        'notes',
        'is_active',
        'can_teach_multiple_classes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'can_teach_multiple_classes' => 'boolean',
    ];

    /**
     * Get the teacher assigned to this assignment
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prof_id');
    }

    /**
     * Get the class for this assignment
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the subject for this assignment
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Scope: Get only active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get assignments for a specific teacher
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('prof_id', $teacherId);
    }

    /**
     * Scope: Get assignments for a specific class
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope: Get assignments for a specific subject
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Check if a teacher is already assigned to a class for a subject
     */
    public static function isTeacherAssignedToClassSubject($teacherId, $classId, $subjectId)
    {
        return static::where([
            'prof_id' => $teacherId,
            'class_id' => $classId,
            'subject_id' => $subjectId
        ])->exists();
    }

    /**
     * Get all assignments for a teacher in a class
     */
    public static function getTeacherClassAssignments($teacherId, $classId)
    {
        return static::where([
            'prof_id' => $teacherId,
            'class_id' => $classId
        ])->get();
    }

    /**
     * Get all teachers assigned to a specific class
     */
    public static function getClassTeachers($classId)
    {
        return static::where('class_id', $classId)
            ->with('teacher')
            ->get()
            ->pluck('teacher')
            ->unique('id');
    }

    /**
     * Get all classes where a teacher is assigned
     */
    public static function getTeacherClasses($teacherId)
    {
        return static::where('prof_id', $teacherId)
            ->with('class')
            ->get()
            ->pluck('class')
            ->unique('id');    }
}