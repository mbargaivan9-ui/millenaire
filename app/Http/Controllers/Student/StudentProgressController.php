<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BulletinEntry;
use App\Models\BulletinSummary;
use App\Models\Classe;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * StudentProgressController
 *
 * Espace élève : graphiques d'évolution et classement anonymisé.
 * - Visualisation de la progression par matière
 * - Graphiques de performance sur l'année
 * - Classement dans la classe (anonymisé : "Tu es 5ème sur 40")
 * - Objectifs et écarts
 */
class StudentProgressController extends Controller
{
    public function index(Request $request): View
    {
        $user    = Auth::user();
        $student = Student::where('user_id', $user->id)
            ->where('is_active', true)
            ->with('user', 'classe')
            ->firstOrFail();

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        $progressData = $this->buildProgressData($student, $term, $academicYear);
        $yearlyData   = $this->buildYearlyEvolution($student, $academicYear);

        return view('student.progress.dashboard', compact(
            'student',
            'progressData',
            'yearlyData',
            'term',
            'academicYear'
        ));
    }

    // ════════════════════════════════════════════════
    //  AJAX — Données de progression
    // ════════════════════════════════════════════════

    public function getProgressData(Request $request): JsonResponse
    {
        $user    = Auth::user();
        $student = Student::where('user_id', $user->id)->where('is_active', true)->firstOrFail();

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        return response()->json([
            'success'      => true,
            'progress'     => $this->buildProgressData($student, $term, $academicYear),
            'yearly'       => $this->buildYearlyEvolution($student, $academicYear),
        ]);
    }

    // ════════════════════════════════════════════════
    //  AJAX — Données pour un graphique Chart.js
    // ════════════════════════════════════════════════

    public function getChartData(Request $request): JsonResponse
    {
        $user    = Auth::user();
        $student = Student::where('user_id', $user->id)->where('is_active', true)->firstOrFail();

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $chartType    = $request->get('type', 'radar'); // radar | line | bar

        $term = $this->currentTerm();

        // Pour radar : scores par matière
        if ($chartType === 'radar') {
            $entries = BulletinEntry::where('student_id', $student->id)
                ->where('class_id', $student->classe_id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->with('subject')
                ->get()
                ->groupBy('subject_id');

            $labels = [];
            $values = [];
            $classAvgs = [];

            foreach ($entries as $subjectId => $subjectEntries) {
                $subject = $subjectEntries->first()->subject;
                $avg     = $subjectEntries->whereNotNull('score')->avg('score');

                $classAvg = BulletinEntry::where('class_id', $student->classe_id)
                    ->where('subject_id', $subjectId)
                    ->where('term', $term)
                    ->where('academic_year', $academicYear)
                    ->whereNotNull('score')
                    ->avg('score');

                $labels[]    = $subject?->name ?? 'N/A';
                $values[]    = round($avg ?? 0, 2);
                $classAvgs[] = round($classAvg ?? 0, 2);
            }

            return response()->json([
                'type'   => 'radar',
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Mes notes', 'data' => $values, 'color' => '#0d6efd'],
                    ['label' => 'Moy. classe', 'data' => $classAvgs, 'color' => '#adb5bd'],
                ],
            ]);
        }

        // Pour line : évolution séquence par séquence
        if ($chartType === 'line') {
            $labels = [];
            $values = [];

            for ($t = 1; $t <= 3; $t++) {
                for ($s = 1; $s <= 2; $s++) {
                    $labels[] = "T{$t}-S{$s}";

                    $avg = BulletinEntry::where('student_id', $student->id)
                        ->where('class_id', $student->classe_id)
                        ->where('term', $t)
                        ->where('sequence', $s)
                        ->where('academic_year', $academicYear)
                        ->whereNotNull('score')
                        ->avg('score');

                    $values[] = $avg ? round($avg, 2) : null;
                }
            }

            return response()->json([
                'type'   => 'line',
                'labels' => $labels,
                'datasets' => [
                    ['label' => 'Moyenne générale', 'data' => $values, 'color' => '#0d6efd'],
                ],
            ]);
        }

        // Pour bar : comparaison par matière
        $entries = BulletinEntry::where('student_id', $student->id)
            ->where('class_id', $student->classe_id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->with('subject')
            ->get()
            ->groupBy('subject_id');

        $labels    = [];
        $myScores  = [];
        $colors    = [];

        foreach ($entries as $subjectId => $subjectEntries) {
            $subject = $subjectEntries->first()->subject;
            $avg     = $subjectEntries->whereNotNull('score')->avg('score');

            $labels[]   = substr($subject?->name ?? 'N/A', 0, 10);
            $myScores[] = round($avg ?? 0, 2);
            $colors[]   = ($avg ?? 0) >= 10 ? '#198754' : '#dc3545';
        }

        return response()->json([
            'type'   => 'bar',
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Mes moyennes', 'data' => $myScores, 'colors' => $colors],
            ],
        ]);
    }

    // ════════════════════════════════════════════════
    //  PRIVÉ
    // ════════════════════════════════════════════════

    private function buildProgressData(Student $student, int $term, string $academicYear): array
    {
        $summary = BulletinSummary::where('student_id', $student->id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->first();

        // Classement anonymisé
        $rank        = $summary?->rank;
        $total       = $summary?->total_students;
        $rankDisplay = $rank && $total ? "Tu es {$rank}ème sur {$total}" : null;

        // Percentile
        $percentile = null;
        if ($rank && $total) {
            $percentile = round((($total - $rank) / $total) * 100);
        }

        // Matières par matière
        $subjectData = $this->getSubjectData($student->id, $student->classe_id, $term, $academicYear);

        // Points forts / faibles
        $sorted     = collect($subjectData)->sortByDesc('average');
        $strengths  = $sorted->filter(fn($s) => $s['average'] >= 12)->take(3)->values();
        $weaknesses = $sorted->filter(fn($s) => $s['average'] !== null && $s['average'] < 10)->sortBy('average')->take(3)->values();

        return [
            'term_average'  => $summary?->term_average,
            'seq1_average'  => $summary?->sequence1_average,
            'seq2_average'  => $summary?->sequence2_average,
            'rank'          => $rank,
            'total'         => $total,
            'rank_display'  => $rankDisplay,
            'percentile'    => $percentile,
            'appreciation'  => $summary?->appreciation,
            'subjects'      => $subjectData,
            'strengths'     => $strengths,
            'weaknesses'    => $weaknesses,
            'is_passing'    => ($summary?->term_average ?? 0) >= 10,
        ];
    }

    private function buildYearlyEvolution(Student $student, string $academicYear): array
    {
        $data = [];
        for ($term = 1; $term <= 3; $term++) {
            $summary = BulletinSummary::where('student_id', $student->id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->first();

            // Moyenne de la classe pour ce trimestre
            $classAvg = BulletinSummary::where('class_id', $student->classe_id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->avg('term_average');

            $data[] = [
                'term'        => $term,
                'label'       => "Trimestre {$term}",
                'student_avg' => $summary?->term_average,
                'class_avg'   => $classAvg ? round($classAvg, 2) : null,
                'seq1'        => $summary?->sequence1_average,
                'seq2'        => $summary?->sequence2_average,
                'rank'        => $summary?->rank,
                'total'       => $summary?->total_students,
            ];
        }
        return $data;
    }

    private function getSubjectData(int $studentId, int $classId, int $term, string $academicYear): array
    {
        $entries = BulletinEntry::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->with('subject')
            ->get()
            ->groupBy('subject_id');

        $subjects = [];
        foreach ($entries as $subjectId => $subjectEntries) {
            $subject = $subjectEntries->first()->subject;
            $seq1    = $subjectEntries->firstWhere('sequence', 1);
            $seq2    = $subjectEntries->firstWhere('sequence', 2);

            $average = null;
            if ($seq1?->score !== null && $seq2?->score !== null) {
                $average = round(($seq1->score + $seq2->score) / 2, 2);
            } elseif ($seq1?->score !== null) {
                $average = $seq1->score;
            } elseif ($seq2?->score !== null) {
                $average = $seq2->score;
            }

            // Rang dans la classe pour cette matière
            $classScores = BulletinEntry::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->whereNotNull('score')
                ->pluck('score', 'student_id');

            $subjectRank = null;
            if ($average !== null && $classScores->count() > 0) {
                $betterCount = $classScores->filter(fn($s) => $s > $average)->count();
                $subjectRank = $betterCount + 1;
            }

            $subjects[] = [
                'subject_name' => $subject?->name ?? 'N/A',
                'coefficient'  => $subject?->coefficient ?? 1,
                'seq1'         => $seq1?->score,
                'seq2'         => $seq2?->score,
                'average'      => $average,
                'rank'         => $subjectRank,
                'class_count'  => $classScores->count(),
                'is_passing'   => $average !== null && $average >= 10,
                'appreciation' => $this->getAppreciation($average),
            ];
        }

        return $subjects;
    }

    private function getAppreciation(?float $score): ?string
    {
        if ($score === null) return null;
        if ($score >= 18) return 'Excellent';
        if ($score >= 16) return 'Très bien';
        if ($score >= 14) return 'Bien';
        if ($score >= 12) return 'Assez bien';
        if ($score >= 10) return 'Passable';
        if ($score >= 8)  return 'Insuffisant';
        return 'Très insuffisant';
    }

    private function currentAcademicYear(): string
    {
        $year = now()->year;
        return now()->month >= 9 ? "{$year}-" . ($year + 1) : ($year - 1) . "-{$year}";
    }

    private function currentTerm(): int
    {
        $month = now()->month;
        if ($month >= 9 && $month <= 12) return 1;
        if ($month >= 1 && $month <= 3)  return 2;
        return 3;
    }
}
