<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * EloquentStudentRepository
 * 
 * SOLID - Concrete implementation of StudentRepositoryInterface
 */
class EloquentStudentRepository implements StudentRepositoryInterface
{
    /**
     * Get a student by ID with relationships.
     *
     * @param int $id
     * @return Student|null
     */
    public function find(int $id): ?Student
    {
        return Student::with(['user', 'classe', 'guardians', 'grades'])
            ->find($id);
    }

    /**
     * Get all students in a class.
     *
     * @param int $classId
     * @return Collection
     */
    public function getByClass(int $classId): Collection
    {
        return Student::where('classe_id', $classId)
            ->with(['user', 'guardians'])
            ->orderBy('matricule')
            ->get();
    }

    /**
     * Search students by name or matricule.
     *
     * @param string $query
     * @param int|null $classId
     * @return Collection
     */
    public function search(string $query, ?int $classId = null): Collection
    {
        $studentQuery = Student::with('user');

        if ($classId) {
            $studentQuery->where('classe_id', $classId);
        }

        return $studentQuery->where(function ($q) use ($query) {
                $q->where('matricule', 'LIKE', "%{$query}%")
                  ->orWhereHas('user', function ($userQ) use ($query) {
                      $userQ->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('email', 'LIKE', "%{$query}%");
                  });
            })
            ->limit(20)
            ->get();
    }

    /**
     * Get students with critical grades (< 7/20) in a sequence.
     *
     * @param int $sequence
     * @param int $classId
     * @return Collection
     */
    public function getCriticalStudents(int $sequence, int $classId): Collection
    {
        return Student::where('classe_id', $classId)
            ->whereHas('grades', function ($query) use ($sequence) {
                $query->where('sequence', $sequence)
                      ->where('score', '<', 7);
            })
            ->with(['user', 'grades' => function ($query) use ($sequence) {
                $query->where('sequence', $sequence);
            }])
            ->get();
    }

    /**
     * Create a new student.
     *
     * @param array $data
     * @return Student
     */
    public function create(array $data): Student
    {
        return Student::create($data);
    }

    /**
     * Update a student.
     *
     * @param Student $student
     * @param array $data
     * @return Student
     */
    public function update(Student $student, array $data): Student
    {
        $student->update($data);
        return $student->refresh();
    }

    /**
     * Get students ordered by performance (overall grades average).
     * OPTIMIZED: Uses database-level aggregation to avoid loading all grades in PHP
     *
     * @param int $classId
     * @param int $sequence
     * @param string $order 'asc' or 'desc'
     * @return Collection
     */
    public function orderByPerformance(int $classId, int $sequence, string $order = 'desc'): Collection
    {
        // Use raw SQL to calculate averages at DB level, then sort by those averages
        $isDesc = strtolower($order) === 'desc';
        
        return Student::where('classe_id', $classId)
            ->with(['user', 'grades' => function ($query) use ($sequence) {
                $query->where('sequence', $sequence);
            }])
            ->addSelect(\Illuminate\Database\Query\Expression::raw(
                '(SELECT AVG(COALESCE(score, 0)) FROM grades WHERE grades.student_id = students.id AND grades.sequence = ' . intval($sequence) . ') as avg_score'
            ))
            ->orderBy('avg_score', $isDesc ? 'desc' : 'asc')
            ->get();
    }

    /**
     * Get students by financial status.
     *
     * @param string $status 'paid', 'partial', 'unpaid'
     * @param int|null $classId
     * @return Collection
     */
    public function getByFinancialStatus(string $status, ?int $classId = null): Collection
    {
        $query = Student::where('financial_status', $status);

        if ($classId) {
            $query->where('classe_id', $classId);
        }

        return $query->with(['user', 'guardians'])
            ->get();
    }
}
