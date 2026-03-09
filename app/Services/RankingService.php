<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Classe;

/**
 * RankingService
 * 
 * SOLID - Single Responsibility Principle
 * Handles all ranking and classification calculations
 */
class RankingService
{
    /**
     * 🔥 Calculate real-time rank (Live Bulletin feature)
     * Rank of a student for a subject in a specific sequence
     * 
     * @param Student $student
     * @param Subject $subject
     * @param int $sequence
     * @return int
     */
    public function calculateRank(Student $student, Subject $subject, int $sequence): int
    {
        $classId = $student->classe_id;

        // Get all grades for this subject/sequence in the class, ordered descending
        $grades = Grade::where('subject_id', $subject->id)
            ->where('sequence', $sequence)
            ->whereHas('student', function ($query) use ($classId) {
                $query->where('classe_id', $classId);
            })
            ->orderBy('score', 'desc')
            ->pluck('score', 'student_id');

        // Calculate rank
        $rank = 1;
        foreach ($grades as $studentId => $score) {
            if ($studentId == $student->id) {
                break;
            }
            $rank++;
        }

        return $rank;
    }

    /**
     * Calculate overall rank (all subjects combined)
     * Based on weighted average of all grades
     * 
     * @param Student $student
     * @param int $sequence
     * @return int
     */
    public function calculateOverallRank(Student $student, int $sequence): int
    {
        $classId = $student->classe_id;

        // Get all students in the class with their grades
        $students = Student::where('classe_id', $classId)
            ->with(['grades' => function ($query) use ($sequence) {
                $query->where('sequence', $sequence)->with('subject');
            }])
            ->get();

        // Calculate weighted averages
        $averages = [];
        $gradeCalcService = app(GradeCalculationService::class);

        foreach ($students as $s) {
            $average = $gradeCalcService->calculateOverallAverage($s, $sequence);
            $averages[$s->id] = $average ?? 0;
        }

        // Sort by average descending
        arsort($averages);

        // Find the rank
        $rank = 1;
        foreach ($averages as $studentId => $average) {
            if ($studentId == $student->id) {
                break;
            }
            $rank++;
        }

        return $rank;
    }

    /**
     * Get students ranked by subject performance
     * 
     * @param Subject $subject
     * @param Classe $classe
     * @param int $sequence
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRankedStudents(Subject $subject, Classe $classe, int $sequence, int $limit = 10)
    {
        return Grade::where('subject_id', $subject->id)
            ->where('sequence', $sequence)
            ->whereHas('student', function ($query) use ($classe) {
                $query->where('classe_id', $classe->id);
            })
            ->with('student')
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($grade, $index) {
                $grade->rank = $index + 1;
                return $grade;
            });
    }

    /**
     * Get students ranked by overall average
     * 
     * @param Classe $classe
     * @param int $sequence
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRankedStudentsByOverall(Classe $classe, int $sequence, int $limit = 10)
    {
        $gradeCalcService = app(GradeCalculationService::class);

        $students = Student::where('classe_id', $classe->id)
            ->with(['grades' => function ($query) use ($sequence) {
                $query->where('sequence', $sequence)->with('subject');
            }])
            ->get()
            ->map(function ($student) use ($gradeCalcService, $sequence) {
                $student->average = $gradeCalcService->calculateOverallAverage($student, $sequence) ?? 0;
                return $student;
            })
            ->sortByDesc('average')
            ->take($limit)
            ->values();

        // Add rank to each student
        return $students->map(function ($student, $index) {
            $student->rank = $index + 1;
            return $student;
        });
    }

    /**
     * Get rank distribution (pie chart data)
     * How many students are in each rank bracket
     * 
     * @param Classe $classe
     * @param Subject $subject
     * @param int $sequence
     * @return array
     */
    public function getRankDistribution(Classe $classe, Subject $subject, int $sequence): array
    {
        $totalStudents = Student::where('classe_id', $classe->id)->count();

        return [
            'top_10_percent' => (int) ceil($totalStudents * 0.1),
            'top_25_percent' => (int) ceil($totalStudents * 0.25),
            'top_50_percent' => (int) ceil($totalStudents * 0.5),
            'bottom_25_percent' => (int) ceil($totalStudents * 0.25),
            'total_students' => $totalStudents,
        ];
    }

    /**
     * Get percentile rank (what percentage of students scored below this student)
     * 
     * @param Student $student
     * @param Subject $subject
     * @param int $sequence
     * @return float Percentile (0-100)
     */
    public function getPercentileRank(Student $student, Subject $subject, int $sequence): float
    {
        $rank = $this->calculateRank($student, $subject, $sequence);

        $totalStudents = Student::where('classe_id', $student->classe_id)->count();

        if ($totalStudents === 0) {
            return 0;
        }

        return round((($totalStudents - $rank) / $totalStudents) * 100, 2);
    }

    /**
     * Check if student's trend is improving
     * 
     * @param Student $student
     * @param Subject $subject
     * @param int $currentSequence
     * @return bool
     */
    public function isRankingImproving(Student $student, Subject $subject, int $currentSequence): bool
    {
        $previousSequence = max(1, $currentSequence - 1);

        $currentRank = $this->calculateRank($student, $subject, $currentSequence);
        $previousRank = $this->calculateRank($student, $subject, $previousSequence);

        // Lower rank number = better ranking
        return $currentRank < $previousRank;
    }
}
