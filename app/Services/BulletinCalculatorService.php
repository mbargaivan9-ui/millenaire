<?php

namespace App\Services;

use App\Models\BulletinGrade;
use App\Models\StudentBulletin;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * BulletinCalculatorService
 * 
 * Calculates bulletin statistics in real-time:
 * - Subject averages (classe + composition)
 * - General average
 * - Class rankings
 * - Appreciations based on configurable scale
 * - Mentions based on institution settings
 * 
 * Triggered automatically on grade entry via Livewire events
 */
class BulletinCalculatorService
{
    // Default appreciation scale
    private array $appreciationScale = [
        ['min' => 16, 'max' => 20, 'label' => 'Très Bien'],
        ['min' => 14, 'max' => 15.99, 'label' => 'Bien'],
        ['min' => 12, 'max' => 13.99, 'label' => 'Assez Bien'],
        ['min' => 10, 'max' => 11.99, 'label' => 'Passable'],
        ['min' => 0, 'max' => 9.99, 'label' => 'Insuffisant'],
    ];

    /**
     * Calculate subject average for a student
     * 
     * Formula: (note_classe + note_composition) / 2
     */
    public function calculateSubjectAverage(BulletinGrade $grade): float
    {
        $noteClasse = $grade->note_classe ?? 0;
        $noteComposition = $grade->note_composition ?? 0;
        
        $average = ($noteClasse + $noteComposition) / 2;
        
        // Clamp between 0 and 20
        return min(20, max(0, round($average, 2)));
    }

    /**
     * Calculate general average for a student bulletin
     * 
     * Formula: Σ(subject_average × coefficient) / Σ(coefficients)
     */
    public function calculateGeneralAverage(StudentBulletin $bulletin): float
    {
        $grades = $bulletin->grades()
            ->with('subject')
            ->get();

        if ($grades->isEmpty()) {
            return 0;
        }

        $totalWeighted = 0;
        $totalCoefficients = 0;

        foreach ($grades as $grade) {
            $average = $grade->subject_average ?? $this->calculateSubjectAverage($grade);
            $coefficient = $grade->subject->coefficient ?? 1;
            
            $totalWeighted += $average * $coefficient;
            $totalCoefficients += $coefficient;
        }

        if ($totalCoefficients == 0) {
            return 0;
        }

        return min(20, max(0, round($totalWeighted / $totalCoefficients, 2)));
    }

    /**
     * Calculate class ranking using DENSE_RANK()
     * 
     * Returns an array of [student_id => rank]
     */
    public function calculateClassRankings(StudentBulletin $firstBulletin): array
    {
        // Get all bulletins for the same class and period
        $bulletins = StudentBulletin::where('classroom_id', $firstBulletin->classroom_id)
            ->where('academic_year', $firstBulletin->academic_year)
            ->where('trimester', $firstBulletin->trimester)
            ->with('student')
            ->get();

        if ($bulletins->isEmpty()) {
            return [];
        }

        // Sort by general_average descending
        $sorted = $bulletins->sortByDesc('general_average');

        // Apply DENSE_RANK logic
        $rankings = [];
        $currentRank = 1;
        $previousAverage = null;

        foreach ($sorted as $bulletin) {
            if ($previousAverage !== null && $bulletin->general_average < $previousAverage) {
                $currentRank++;
            }
            
            $rankings[$bulletin->id] = $currentRank;
            $previousAverage = $bulletin->general_average;
        }

        return $rankings;
    }

    /**
     * Get appreciation label based on average
     */
    public function getAppreciation(float $average, ?array $customScale = null): string
    {
        $scale = $customScale ?? $this->appreciationScale;

        foreach ($scale as $tier) {
            if ($average >= $tier['min'] && $average <= $tier['max']) {
                return $tier['label'];
            }
        }

        return 'Non classé';
    }

    /**
     * Update all calculated fields for a bulletin
     * Call this after grades are entered/modified
     */
    public function updateBulletinCalculations(StudentBulletin $bulletin): void
    {
        try {
            DB::beginTransaction();

            // Update subject averages
            foreach ($bulletin->grades as $grade) {
                $average = $this->calculateSubjectAverage($grade);
                
                $grade->update([
                    'subject_average' => $average,
                    'appreciation' => $this->getAppreciation($average),
                ]);
            }

            // Update general average
            $generalAverage = $this->calculateGeneralAverage($bulletin);
            $appreciation = $this->getAppreciation($generalAverage);

            // Get ranking
            $rankings = $this->calculateClassRankings($bulletin);
            $classRank = $rankings[$bulletin->id] ?? null;

            // Update bulletin
            $bulletin->update([
                'general_average' => $generalAverage,
                'class_rank' => $classRank,
                'appreciation' => $appreciation,
                'status' => $this->determineStatus($bulletin),
            ]);

            // Update subject ranks
            $this->updateSubjectRankings($bulletin);

            DB::commit();

            Log::info('Bulletin calculations updated', [
                'bulletin_id' => $bulletin->id,
                'general_average' => $generalAverage,
                'class_rank' => $classRank,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulletin calculation failed', [
                'bulletin_id' => $bulletin->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update subject-level rankings (rank within each subject)
     */
    private function updateSubjectRankings(StudentBulletin $bulletin): void
    {
        $grades = $bulletin->grades()->with('subject')->get();

        foreach ($grades as $grade) {
            // Get all grades for this subject in this class/period
            $subjectGrades = BulletinGrade::whereHas('bulletin', function ($q) use ($bulletin) {
                $q->where('classroom_id', $bulletin->classroom_id)
                  ->where('academic_year', $bulletin->academic_year)
                  ->where('trimester', $bulletin->trimester);
            })
            ->where('subject_id', $grade->subject_id)
            ->with('bulletin')
            ->get()
            ->sortByDesc('subject_average');

            // Assign ranks
            $rank = 1;
            $previousAverage = null;
            $ranks = [];

            foreach ($subjectGrades as $g) {
                if ($previousAverage !== null && $g->subject_average < $previousAverage) {
                    $rank++;
                }
                
                $ranks[$g->id] = $rank;
                $previousAverage = $g->subject_average;
            }

            // Update this grade's rank
            if (isset($ranks[$grade->id])) {
                $grade->update(['subject_rank' => $ranks[$grade->id]]);
            }
        }
    }

    /**
     * Determine bulletin status based on completion
     */
    private function determineStatus(StudentBulletin $bulletin): string
    {
        $grades = $bulletin->grades;
        
        if ($grades->isEmpty()) {
            return 'draft';
        }

        $entered = $grades->filter(fn($g) => $g->note_classe !== null || $g->note_composition !== null)->count();
        $total = $grades->count();

        if ($entered === 0) {
            return 'draft';
        } elseif ($entered < $total) {
            return 'partial';
        } else {
            return 'complete';
        }
    }

    /**
     * Calculate class averages (for reporting)
     */
    public function calculateClassAverages(string $classroomId, string $academicYear, int $trimester): array
    {
        $bulletins = StudentBulletin::where('classroom_id', $classroomId)
            ->where('academic_year', $academicYear)
            ->where('trimester', $trimester)
            ->get();

        $averages = [];
        foreach ($bulletins->groupBy('classroom_id') as $classItems) {
            $generalAverages = $classItems->pluck('general_average')->filter(fn($v) => $v > 0);
            
            $averages = [
                'class_average' => $generalAverages->avg(),
                'best_average' => $generalAverages->max(),
                'worst_average' => $generalAverages->min(),
                'median_average' => $this->calculateMedian($generalAverages->toArray()),
            ];
        }

        return $averages;
    }

    /**
     * Helper: Calculate median of array
     */
    private function calculateMedian(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        sort($values);
        $count = count($values);
        $middle = intdiv($count, 2);

        if ($count % 2 === 1) {
            return $values[$middle];
        }

        return ($values[$middle - 1] + $values[$middle]) / 2;
    }

    /**
     * Set custom appreciation scale
     */
    public function setAppreciationScale(array $scale): void
    {
        // Validate scale
        foreach ($scale as $tier) {
            if (!isset($tier['min'], $tier['max'], $tier['label'])) {
                throw new Exception('Invalid appreciation scale format');
            }
        }

        // Sort by min value
        usort($scale, fn($a, $b) => $a['min'] <=> $b['min']);
        
        $this->appreciationScale = $scale;
    }
}
