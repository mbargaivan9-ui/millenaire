<?php

namespace App\Repositories\Interfaces;

use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * StudentRepositoryInterface
 * 
 * SOLID - Repository Pattern for Student data access
 */
interface StudentRepositoryInterface
{
    /**
     * Get a student by ID with relationships.
     *
     * @param int $id
     * @return Student|null
     */
    public function find(int $id): ?Student;

    /**
     * Get all students in a class.
     *
     * @param int $classId
     * @return Collection
     */
    public function getByClass(int $classId): Collection;

    /**
     * Search students by name or matricule.
     *
     * @param string $query
     * @param int|null $classId Filter by class (optional)
     * @return Collection
     */
    public function search(string $query, ?int $classId = null): Collection;

    /**
     * Get students with critical grades (< 7/20) in a sequence.
     *
     * @param int $sequence
     * @param int $classId
     * @return Collection
     */
    public function getCriticalStudents(int $sequence, int $classId): Collection;

    /**
     * Create a new student.
     *
     * @param array $data
     * @return Student
     */
    public function create(array $data): Student;

    /**
     * Update a student.
     *
     * @param Student $student
     * @param array $data
     * @return Student
     */
    public function update(Student $student, array $data): Student;

    /**
     * Get students ordered by performance (overall grades average).
     *
     * @param int $classId
     * @param int $sequence
     * @param string $order 'asc' or 'desc'
     * @return Collection
     */
    public function orderByPerformance(int $classId, int $sequence, string $order = 'desc'): Collection;

    /**
     * Get students by financial status.
     *
     * @param string $status 'paid', 'partial', 'unpaid'
     * @param int|null $classId
     * @return Collection
     */
    public function getByFinancialStatus(string $status, ?int $classId = null): Collection;
}
