<?php

namespace App\Repositories\Interfaces;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;

/**
 * GradeRepositoryInterface
 * 
 * SOLID - Repository Pattern for Grade data access
 * Dependency Inversion Principle: Depend on abstractions, not concrete implementations
 */
interface GradeRepositoryInterface
{
    /**
     * Create or update a grade record.
     *
     * @param array $criteria Criteria to find existing grade (student_id, subject_id, sequence, etc.)
     * @param array $data Data to create or update
     * @return Grade
     */
    public function createOrUpdate(array $criteria, array $data): Grade;

    /**
     * Get a grade by ID.
     *
     * @param int $id
     * @return Grade|null
     */
    public function find(int $id): ?Grade;

    /**
     * Get all grades for a student in a sequence.
     *
     * @param Student $student
     * @param int $sequence
     * @return \Illuminate\Support\Collection
     */
    public function getStudentSequenceGrades(Student $student, int $sequence);

    /**
     * Get all grades for a subject and sequence in a class.
     *
     * @param int $subjectId
     * @param int $sequence
     * @param int $classId
     * @return \Illuminate\Support\Collection
     */
    public function getClassSubjectSequenceGrades(int $subjectId, int $sequence, int $classId);

    /**
     * Get grades for a specific student and subject.
     *
     * @param int $studentId
     * @param int $subjectId
     * @return \Illuminate\Support\Collection
     */
    public function getStudentSubjectGrades(int $studentId, int $subjectId);

    /**
     * Update a grade.
     *
     * @param Grade $grade
     * @param array $data
     * @return Grade
     */
    public function update(Grade $grade, array $data): Grade;

    /**
     * Delete a grade.
     *
     * @param Grade $grade
     * @return bool
     */
    public function delete(Grade $grade): bool;

    /**
     * Lock grades for a sequence (prevent editing).
     *
     * @param int $sequence
     * @param int $classId
     * @return int Number of grades locked
     */
    public function lockSequenceGrades(int $sequence, int $classId): int;

    /**
     * Check if a sequence is locked for a class.
     *
     * @param int $sequence
     * @param int $classId
     * @return bool
     */
    public function isSequenceLocked(int $sequence, int $classId): bool;
}
