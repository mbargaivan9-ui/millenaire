<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\BulletinEntry;
use App\Models\BulletinSummary;
use App\Models\Classe;
use App\Models\ClassTermLock;
use App\Models\Notification;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * ParentMonitoringController
 *
 * Espace parent : monitoring en temps réel de la performance de l'enfant.
 * - Tableau de bord avec notes et progression
 * - Graphiques d'évolution par matière
 * - Alertes de notes critiques
 * - Comparaison anonymisée avec la classe
 */
class ParentMonitoringController extends Controller
{
    // ════════════════════════════════════════════════
    //  DASHBOARD PARENT
    // ════════════════════════════════════════════════

    public function index(Request $request): View
    {
        $user = Auth::user();

        // Récupérer tous les enfants liés à ce parent
        $children = $this->getParentChildren($user->id);

        if ($children->isEmpty()) {
            return view('parent.monitoring.no-children');
        }

        // Enfant sélectionné (par défaut : le premier)
        $selectedStudentId = $request->get('student_id', $children->first()->id);
        $selectedStudent   = $children->firstWhere('id', $selectedStudentId) ?? $children->first();

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        $dashboardData = $this->buildDashboardData($selectedStudent, $term, $academicYear);

        // Notifications non lues
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->where('category', 'grade_alert')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('parent.monitoring.dashboard', compact(
            'children',
            'selectedStudent',
            'dashboardData',
            'notifications',
            'term',
            'academicYear'
        ));
    }

    // ════════════════════════════════════════════════
    //  AJAX — Données temps réel pour un enfant
    // ════════════════════════════════════════════════

    public function getChildData(Student $student, Request $request): JsonResponse
    {
        // Vérifier que l'élève appartient bien à ce parent
        if (! $this->isParentOfStudent(Auth::id(), $student->id)) {
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        return response()->json([
            'success' => true,
            'data'    => $this->buildDashboardData($student, $term, $academicYear),
        ]);
    }

    // ════════════════════════════════════════════════
    //  AJAX — Graphique évolution sur l'année
    // ════════════════════════════════════════════════

    public function getEvolutionChart(Student $student, Request $request): JsonResponse
    {
        if (! $this->isParentOfStudent(Auth::id(), $student->id)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());

        // Moyennes par trimestre
        $trimesterData = [];
        for ($term = 1; $term <= 3; $term++) {
            $summary = BulletinSummary::where('student_id', $student->id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->first();

            $classAvg = BulletinSummary::where('class_id', $student->classe_id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->avg('term_average');

            $trimesterData[] = [
                'term'          => "Trimestre {$term}",
                'student_avg'   => $summary?->term_average,
                'class_avg'     => $classAvg ? round($classAvg, 2) : null,
                'seq1_avg'      => $summary?->sequence1_average,
                'seq2_avg'      => $summary?->sequence2_average,
                'rank'          => $summary?->rank,
                'total'         => $summary?->total_students,
            ];
        }

        // Données par matière (trimestre courant)
        $currentTerm = $this->currentTerm();
        $subjects    = $this->getSubjectBreakdown($student->id, $student->classe_id, $currentTerm, $academicYear);

        return response()->json([
            'trimester_evolution' => $trimesterData,
            'subject_breakdown'   => $subjects,
        ]);
    }

    // ════════════════════════════════════════════════
    //  AJAX — Marquer notifications comme lues
    // ════════════════════════════════════════════════

    public function markNotificationsRead(Request $request): JsonResponse
    {
        Notification::where('user_id', Auth::id())
            ->where('category', 'grade_alert')
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    // ════════════════════════════════════════════════
    //  PRIVÉ : Construction des données dashboard
    // ════════════════════════════════════════════════

    private function buildDashboardData(Student $student, int $term, string $academicYear): array
    {
        $student->load('user', 'classe');

        // Résumé trimestre courant
        $summary = BulletinSummary::where('student_id', $student->id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->first();

        // Notes par matière
        $subjectBreakdown = $this->getSubjectBreakdown($student->id, $student->classe_id, $term, $academicYear);

        // Comparaison classe (anonymisée)
        $classAvg = BulletinSummary::where('class_id', $student->classe_id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->avg('term_average');

        // Matières critiques (< 8)
        $criticalSubjects = collect($subjectBreakdown)->filter(fn($s) => $s['average'] !== null && $s['average'] < 8);

        // Amélioration par rapport au trimestre précédent
        $prevSummary = null;
        if ($term > 1) {
            $prevSummary = BulletinSummary::where('student_id', $student->id)
                ->where('term', $term - 1)
                ->where('academic_year', $academicYear)
                ->first();
        }

        $trend = null;
        if ($summary?->term_average && $prevSummary?->term_average) {
            $diff  = $summary->term_average - $prevSummary->term_average;
            $trend = [
                'value'     => round($diff, 2),
                'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'stable'),
                'label'     => $diff > 0 ? "+{$diff}" : "{$diff}",
            ];
        }

        $isLocked = ClassTermLock::where('class_id', $student->classe_id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->where('is_locked', true)
            ->exists();

        return [
            'student' => [
                'id'        => $student->id,
                'name'      => $student->user->name,
                'matricule' => $student->matricule,
                'classe'    => $student->classe?->name,
            ],
            'summary' => [
                'term_average'     => $summary?->term_average,
                'seq1_average'     => $summary?->sequence1_average,
                'seq2_average'     => $summary?->sequence2_average,
                'rank'             => $summary?->rank,
                'total_students'   => $summary?->total_students,
                'rank_display'     => $summary?->rank_display ?? '—',
                'appreciation'     => $summary?->appreciation,
                'status'           => $summary?->status ?? 'draft',
            ],
            'class_avg'         => round($classAvg ?? 0, 2),
            'subject_breakdown' => $subjectBreakdown,
            'critical_subjects' => $criticalSubjects->values(),
            'trend'             => $trend,
            'is_locked'         => $isLocked,
            'is_passing'        => ($summary?->term_average ?? 0) >= 10,
        ];
    }

    private function getSubjectBreakdown(int $studentId, int $classId, int $term, string $academicYear): array
    {
        $entries = BulletinEntry::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->with('subject', 'classSubjectTeacher.teacher.user')
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

            // Comparaison avec la moyenne de la classe pour cette matière
            $classSubjectAvg = BulletinEntry::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->whereNotNull('score')
                ->avg('score');

            $subjects[] = [
                'subject_id'       => $subjectId,
                'subject_name'     => $subject?->name ?? 'N/A',
                'coefficient'      => $subject?->coefficient ?? 1,
                'seq1_score'       => $seq1?->score,
                'seq2_score'       => $seq2?->score,
                'average'          => $average,
                'class_avg'        => $classSubjectAvg ? round($classSubjectAvg, 2) : null,
                'above_class'      => $average !== null && $classSubjectAvg !== null && $average >= $classSubjectAvg,
                'teacher_comment'  => $seq2?->teacher_comment ?? $seq1?->teacher_comment,
                'appreciation'     => $this->getAppreciation($average),
                'status'           => $average === null ? 'pending' : ($average >= 10 ? 'pass' : 'fail'),
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

    private function getParentChildren(int $userId)
    {
        // Via la relation parent (guardian)
        return Student::whereHas('guardians', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('is_active', true)->with('user', 'classe')->get();
    }

    private function isParentOfStudent(int $userId, int $studentId): bool
    {
        return Student::where('id', $studentId)
            ->whereHas('guardians', fn($q) => $q->where('user_id', $userId))
            ->exists();
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
