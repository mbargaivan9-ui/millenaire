<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\GradeRepositoryInterface;
use App\Models\Grade;
use App\Models\Student;

/**
 * EloquentGradeRepository
 * 
 * SOLID - Concrete implementation of GradeRepositoryInterface
 * Handles all Grade-related database operations using Eloquent ORM
 */
class EloquentGradeRepository implements GradeRepositoryInterface
{
    /**
     * Create or update a grade record.
     *
     * @param array $criteria
     * @param array $data
     * @return Grade
     */
    public function createOrUpdate(array $criteria, array $data): Grade
    {
        return Grade::updateOrCreate($criteria, $data);
    }

    /**
     * Get a grade by ID.
     *
     * @param int $id
     * @return Grade|null
     */
    public function find(int $id): ?Grade
    {
        return Grade::find($id);
    }

    /**
     * Get all grades for a student in a sequence.
     *
     * @param Student $student
     * @param int $sequence
     * @return \Illuminate\Support\Collection
     */
    public function getStudentSequenceGrades(Student $student, int $sequence)
    {
        return $student->grades()
            ->where('sequence', $sequence)
            ->with('subject')
            ->get();
    }

    /**
     * Get all grades for a subject and sequence in a class.
     *
     * @param int $subjectId
     * @param int $sequence
     * @param int $classId
     * @return \Illuminate\Support\Collection
     */
    public function getClassSubjectSequenceGrades(int $subjectId, int $sequence, int $classId)
    {
        return Grade::where('subject_id', $subjectId)
            ->where('sequence', $sequence)
            ->whereHas('student', function ($query) use ($classId) {
                $query->where('classe_id', $classId);
            })
            ->with(['student', 'subject'])
            ->get();
    }

    /**
     * Get grades for a specific student and subject.
     *
     * @param int $studentId
     * @param int $subjectId
     * @return \Illuminate\Support\Collection
     */
    public function getStudentSubjectGrades(int $studentId, int $subjectId)
    {
        return Grade::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->with('subject')
            ->get();
    }

    /**
     * Update a grade.
     *
     * @param Grade $grade
     * @param array $data
     * @return Grade
     */
    public function update(Grade $grade, array $data): Grade
    {
        $grade->update($data);
        return $grade->refresh();
    }

    /**
     * Delete a grade.
     *
     * @param Grade $grade
     * @return bool
     */
    public function delete(Grade $grade): bool
    {
        return $grade->delete();
    }

    /**
     * Lock grades for a sequence (prevent editing).
     *
     * @param int $sequence
     * @param int $classId
     * @return int Number of grades locked
     */
    public function lockSequenceGrades(int $sequence, int $classId): int
    {
        return Grade::where('sequence', $sequence)
            ->whereHas('student', function ($query) use ($classId) {
                $query->where('classe_id', $classId);
            })
            ->update(['is_locked' => true, 'locked_at' => now()]);
    }

    /**
     * Check if a sequence is locked for a class.
     *
     * @param int $sequence
     * @param int $classId
     * @return bool
     */
    public function isSequenceLocked(int $sequence, int $classId): bool
    {
        return Grade::where('sequence', $sequence)
            ->where('is_locked', false)
            ->whereHas('student', function ($query) use ($classId) {
                $query->where('classe_id', $classId);
            })
            ->doesntExist();
    }
}
