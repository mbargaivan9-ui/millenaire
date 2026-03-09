<?php

namespace App\Services;

use App\Models\{Grade, Student, Subject, ClassSubjectTeacher};
use Illuminate\Database\Eloquent\Collection;

/**
 * GradeService
 *
 * Service Class for Grade Management
 * Handles all grade-related business logic following SOLID principles
 *
 * Single Responsibility: Grade calculations, averages, and rankings
 */
class GradeService
{
    /**
     * Calculate average grade for a student in a specific subject
     *
     * @param Student $student
     * @param Subject $subject
     * @param int|null $term
     * @param int|null $academicYear
     * @return float
     */
    public function calculateSubjectAverage(
        Student $student,
        Subject $subject,
        int $term = null,
        int $academicYear = null
    ): float {
        $query = $student->grades()
            ->where('subject_id', $subject->id);

        if ($term) {
            $query->where('term', $term);
        }

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $totalCoefficient = 0;

        foreach ($grades as $grade) {
            if ($grade->score !== null && !$grade->excused_absence) {
                $totalScore += $grade->score * $grade->coefficient;
                $totalCoefficient += $grade->coefficient;
            }
        }

        return $totalCoefficient > 0 ? round($totalScore / $totalCoefficient, 2) : 0;
    }

    /**
     * Calculate overall average for a student in a specific term
     *
     * @param Student $student
     * @param int $term
     * @param int|null $academicYear
     * @return float
     */
    public function calculateTermAverage(
        Student $student,
        int $term,
        int $academicYear = null
    ): float {
        $query = $student->grades()
            ->where('term', $term);

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        $grades = $query->get();

        if ($grades->isEmpty()) {
            return 0;
        }

        $subjectAverages = [];
        $totalCoefficient = 0;

        // Group grades by subject
        $gradesBySubject = $grades->groupBy('subject_id');

        foreach ($gradesBySubject as $subjectId => $subjectGrades) {
            $subject = Subject::find($subjectId);

            if (!$subject) {
                continue;
            }

            $subjectTotal = 0;
            $subjectCoefficientCount = 0;

            foreach ($subjectGrades as $grade) {
                if ($grade->score !== null && !$grade->excused_absence) {
                    $subjectTotal += $grade->score * $grade->coefficient;
                    $subjectCoefficientCount += $grade->coefficient;
                }
            }

            if ($subjectCoefficientCount > 0) {
                $subjectAverage = $subjectTotal / $subjectCoefficientCount;
                $subjectAverages[$subjectId] = $subjectAverage;
                $totalCoefficient += $subject->coefficient;
            }
        }

        if (empty($subjectAverages) || $totalCoefficient === 0) {
            return 0;
        }

        $totalScore = 0;
        foreach ($subjectAverages as $subjectId => $average) {
            $subject = Subject::find($subjectId);
            $totalScore += $average * $subject->coefficient;
        }

        return round($totalScore / $totalCoefficient, 2);
    }

    /**
     * Calculate annual average for a student
     *
     * @param Student $student
     * @param int $academicYear
     * @return float
     */
    public function calculateAnnualAverage(Student $student, int $academicYear): float
    {
        $termAverages = [];
        $numberOfTerms = 3;

        for ($term = 1; $term <= $numberOfTerms; $term++) {
            $termAverage = $this->calculateTermAverage($student, $term, $academicYear);
            if ($termAverage > 0) {
                $termAverages[] = $termAverage;
            }
        }

        if (empty($termAverages)) {
            return 0;
        }

        return round(array_sum($termAverages) / count($termAverages), 2);
    }

    /**
     * Get ranked students in a class for a specific term
     *
     * @param int $classeId
     * @param int $term
     * @param int|null $academicYear
     * @return Collection
     */
    public function getRankedStudents(int $classeId, int $term, int $academicYear = null): Collection
    {
        $students = Student::where('classe_id', $classeId)
            ->where('is_active', true)
            ->get();

        $studentRankings = $students->map(function (Student $student) use ($term, $academicYear) {
            $average = $this->calculateTermAverage($student, $term, $academicYear);

            return [
                'student_id' => $student->id,
                'student' => $student,
                'average' => $average,
            ];
        });

        // Sort by average descending
        $studentRankings = $studentRankings->sortByDesc('average');

        // Add rank
        $ranked = collect();
        $currentRank = 1;
        $previousAverage = null;
        $tieCount = 0;

        foreach ($studentRankings as $index => $ranking) {
            if ($previousAverage !== $ranking['average'] && $previousAverage !== null) {
                $currentRank = $index + 1;
                $tieCount = 0;
            }

            $ranking['rank'] = $currentRank;
            $ranked->push($ranking);

            $previousAverage = $ranking['average'];
            $tieCount++;
        }

        return $ranked;
    }

    /**
     * Check if student is passing (average >= 10)
     *
     * @param float $average
     * @return bool
     */
    public function isPassing(float $average): bool
    {
        return $average >= 10;
    }

    /**
     * Get performance evaluation
     *
     * @param float $average
     * @return string
     */
    public function getPerformanceLevel(float $average): string
    {
        return match (true) {
            $average >= 18 => 'Excellent',
            $average >= 16 => 'Très Bien',
            $average >= 14 => 'Bien',
            $average >= 12 => 'Correct',
            $average >= 10 => 'Passable',
            default => 'Faible',
        };
    }

    /**
     * Record a grade for a student
     *
     * @param Student $student
     * @param ClassSubjectTeacher $classSubjectTeacher
     * @param int $sequence
     * @param int $term
     * @param int $academicYear
     * @param float|null $score
     * @param string|null $comments
     * @return Grade
     */
    public function recordGrade(
        Student $student,
        ClassSubjectTeacher $classSubjectTeacher,
        int $sequence,
        int $term,
        int $academicYear,
        float $score = null,
        string $comments = null
    ): Grade {
        $grade = Grade::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $classSubjectTeacher->subject_id,
                'class_subject_teacher_id' => $classSubjectTeacher->id,
                'sequence' => $sequence,
                'term' => $term,
                'academic_year' => $academicYear,
            ],
            [
                'score' => $score,
                'coefficient' => $classSubjectTeacher->subject->coefficient,
                'teacher_comments' => $comments,
            ]
        );

        return $grade;
    }

    /**
     * Bulk record grades from an array
     *
     * @param array $gradesData
     * @return array
     */
    public function bulkRecordGrades(array $gradesData): array
    {
        $results = [];

        foreach ($gradesData as $gradeData) {
            $student = Student::find($gradeData['student_id']);
            $classSubjectTeacher = ClassSubjectTeacher::find($gradeData['class_subject_teacher_id']);

            if (!$student || !$classSubjectTeacher) {
                $results[] = [
                    'success' => false,
                    'message' => 'Student or ClassSubjectTeacher not found',
                ];
                continue;
            }

            $grade = $this->recordGrade(
                $student,
                $classSubjectTeacher,
                $gradeData['sequence'],
                $gradeData['term'],
                $gradeData['academic_year'],
                $gradeData['score'] ?? null,
                $gradeData['comments'] ?? null
            );

            $results[] = [
                'success' => true,
                'grade_id' => $grade->id,
                'message' => 'Grade recorded successfully',
            ];
        }

        return $results;
    }

    /**
     * Get grade statistics for a class
     *
     * @param int $classeId
     * @param int $term
     * @param int|null $academicYear
     * @return array
     */
    public function getClassStatistics(int $classeId, int $term, int $academicYear = null): array
    {
        $rankedStudents = $this->getRankedStudents($classeId, $term, $academicYear);

        if ($rankedStudents->isEmpty()) {
            return [
                'total_students' => 0,
                'average_class' => 0,
                'highest_average' => 0,
                'lowest_average' => 0,
                'passing_count' => 0,
                'failing_count' => 0,
            ];
        }

        $averages = $rankedStudents->pluck('average');
        $passingCount = $averages->filter(fn ($avg) => $avg >= 10)->count();

        return [
            'total_students' => $rankedStudents->count(),
            'average_class' => round($averages->average(), 2),
            'highest_average' => $averages->max(),
            'lowest_average' => $averages->min(),
            'passing_count' => $passingCount,
            'failing_count' => $rankedStudents->count() - $passingCount,
            'passing_rate' => round(($passingCount / $rankedStudents->count()) * 100, 2),
        ];
    }
}
