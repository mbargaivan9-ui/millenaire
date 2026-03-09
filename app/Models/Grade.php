<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'class_subject_teacher_id',
        'sequence',
        'term',
        'academic_year',
        'score',
        'coefficient',
        'teacher_comments',
        'excused_absence',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'coefficient' => 'decimal:2',
            'excused_absence' => 'boolean',
        ];
    }

    /**
     * Relation avec Student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relation avec Subject
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relation avec ClassSubjectTeacher
     */
    public function classSubjectTeacher(): BelongsTo
    {
        return $this->belongsTo(ClassSubjectTeacher::class);
    }

    /**
     * Get the teacher who recorded this grade
     */
    public function teacher()
    {
        return $this->classSubjectTeacher->teacher();
    }

    /**
     * Check if grade is passing (>= 10/20)
     */
    public function isPassing(): bool
    {
        return $this->score >= 10;
    }

    /**
     * Get grade letter (A, B, C, D, F)
     */
    public function getLetterGrade(): string
    {
        if (!$this->score) {
            return 'N/A';
        }

        return match (true) {
            $this->score >= 18 => 'A',
            $this->score >= 16 => 'B',
            $this->score >= 14 => 'C',
            $this->score >= 10 => 'D',
            default => 'F',
        };
    }
}
