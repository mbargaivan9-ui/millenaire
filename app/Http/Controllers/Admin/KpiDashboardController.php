<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\ClassSubjectTeacher;
use App\Models\ClassTermLock;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectCompletionTracking;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * KpiDashboardController
 *
 * Tableau de bord administrateur avec indicateurs clés de performance :
 * - Progression des saisies de notes
 * - Statistiques par classe / matière
 * - Alertes & anomalies
 * - Drag & drop affectations professeurs
 */
class KpiDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        // KPI principaux
        $kpis = $this->computeKpis($academicYear, $term);

        // Classes avec leur taux de complétion
        $classesCompletion = $this->getClassesCompletion($academicYear, $term);

        // Top/Bottom performers
        $topClasses    = $classesCompletion->sortByDesc('avg_grade')->take(5)->values();
        $bottomClasses = $classesCompletion->sortBy('avg_grade')->take(5)->values();

        // Alertes en attente
        $alerts = $this->getAlerts($academicYear, $term);

        // Répartition des notes (histogram)
        $gradeDistribution = $this->getGradeDistribution($academicYear, $term);

        // Matières avec le plus de notes critiques
        $criticalSubjects = $this->getCriticalSubjects($academicYear, $term);

        // Affectations (pour drag & drop)
        $classes  = Classe::with(['classSubjectTeachers.subject', 'classSubjectTeachers.teacher.user'])
            ->where('is_active', true)->get();
        $teachers = Teacher::with('user')->where('is_active', true)->get();

        return view('admin.dashboard.kpi', compact(
            'kpis',
            'classesCompletion',
            'topClasses',
            'bottomClasses',
            'alerts',
            'gradeDistribution',
            'criticalSubjects',
            'classes',
            'teachers',
            'academicYear',
            'term'
        ));
    }

    // ════════════════════════════════════════════════
    //  AJAX — Données temps réel pour le dashboard
    // ════════════════════════════════════════════════

    public function refreshKpis(Request $request): JsonResponse
    {
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        return response()->json([
            'kpis'               => $this->computeKpis($academicYear, $term),
            'classes_completion' => $this->getClassesCompletion($academicYear, $term)->values(),
            'grade_distribution' => $this->getGradeDistribution($academicYear, $term),
            'critical_subjects'  => $this->getCriticalSubjects($academicYear, $term),
            'generated_at'       => now()->format('H:i:s'),
        ]);
    }

    public function getClassDetails(Classe $classe, Request $request): JsonResponse
    {
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        $students = Student::where('classe_id', $classe->id)->where('is_active', true)->count();

        $summaries = BulletinSummary::where('class_id', $classe->id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->get();

        $avgGrade  = $summaries->avg('term_average');
        $passCount = $summaries->filter(fn($s) => $s->term_average >= 10)->count();

        $subjects = SubjectCompletionTracking::where('class_id', $classe->id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->with('classSubjectTeacher.subject', 'classSubjectTeacher.teacher.user')
            ->get()
            ->map(fn($t) => [
                'subject'     => $t->classSubjectTeacher?->subject?->name ?? 'N/A',
                'teacher'     => $t->classSubjectTeacher?->teacher?->user?->name ?? 'N/A',
                'completion'  => $t->completion_percentage,
                'filled'      => $t->filled_count,
                'total'       => $t->total_students,
                'last_entry'  => $t->last_entry_at?->diffForHumans(),
            ]);

        $isLocked = ClassTermLock::where('class_id', $classe->id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->where('is_locked', true)
            ->exists();

        return response()->json([
            'class_name'   => $classe->name,
            'students'     => $students,
            'avg_grade'    => round($avgGrade ?? 0, 2),
            'pass_rate'    => $students > 0 ? round(($passCount / $students) * 100, 1) : 0,
            'pass_count'   => $passCount,
            'is_locked'    => $isLocked,
            'subjects'     => $subjects,
        ]);
    }

    // ════════════════════════════════════════════════
    //  AJAX — Drag & drop affectation professeur
    // ════════════════════════════════════════════════

    public function assignTeacher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_subject_teacher_id' => 'required|integer|exists:class_subject_teacher,id',
            'teacher_id'               => 'required|integer|exists:teachers,id',
        ]);

        $cst = ClassSubjectTeacher::findOrFail($validated['class_subject_teacher_id']);
        $cst->update(['teacher_id' => $validated['teacher_id']]);

        $teacher = Teacher::with('user')->find($validated['teacher_id']);

        return response()->json([
            'success'      => true,
            'message'      => "Professeur {$teacher->user->name} affecté avec succès.",
            'teacher_name' => $teacher->user->name,
        ]);
    }

    public function unassignTeacher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_subject_teacher_id' => 'required|integer|exists:class_subject_teacher,id',
        ]);

        ClassSubjectTeacher::findOrFail($validated['class_subject_teacher_id'])
            ->update(['teacher_id' => null, 'is_active' => false]);

        return response()->json(['success' => true, 'message' => 'Professeur désaffecté.']);
    }

    // ════════════════════════════════════════════════
    //  EXPORT CSV — Récapitulatif global
    // ════════════════════════════════════════════════

    public function exportCsv(Request $request)
    {
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        $summaries = BulletinSummary::with(['student.user', 'classe'])
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->orderBy('class_id')
            ->orderByDesc('term_average')
            ->get();

        $filename = "recapitulatif_T{$term}_{$academicYear}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($summaries) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['Classe', 'Matricule', 'Élève', 'Moy. Séq.1', 'Moy. Séq.2', 'Moy. Trim.', 'Rang', 'Effectif', 'Appréciation', 'Statut'], ';');

            foreach ($summaries as $s) {
                fputcsv($handle, [
                    $s->classe?->name,
                    $s->student?->matricule,
                    $s->student?->user?->name,
                    $s->sequence1_average ?? '—',
                    $s->sequence2_average ?? '—',
                    $s->term_average ?? '—',
                    $s->rank ?? '—',
                    $s->total_students ?? '—',
                    $s->appreciation ?? '—',
                    $s->status,
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ════════════════════════════════════════════════
    //  MÉTHODES PRIVÉES
    // ════════════════════════════════════════════════

    private function computeKpis(string $academicYear, int $term): array
    {
        $totalStudents   = Student::where('is_active', true)->count();
        $totalClasses    = Classe::where('is_active', true)->count();
        $totalTeachers   = Teacher::where('is_active', true)->count();
        $totalSubjectCst = ClassSubjectTeacher::where('is_active', true)->count();

        // Complétion globale
        $totalEntries  = Student::where('is_active', true)->count() * ClassSubjectTeacher::where('is_active', true)->count() * 2; // 2 séquences
        $filledEntries = BulletinEntry::where('academic_year', $academicYear)
            ->where('term', $term)
            ->whereNotNull('score')
            ->count();

        $globalCompletion = $totalEntries > 0 ? round(($filledEntries / $totalEntries) * 100, 1) : 0;

        // Moyenne globale
        $globalAvg = BulletinSummary::where('academic_year', $academicYear)
            ->where('term', $term)
            ->whereNotNull('term_average')
            ->avg('term_average');

        // Taux de réussite (moy >= 10)
        $passCount = BulletinSummary::where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('term_average', '>=', 10)
            ->count();

        $bulletinsCount = BulletinSummary::where('academic_year', $academicYear)
            ->where('term', $term)
            ->whereNotNull('term_average')
            ->count();

        $passRate = $bulletinsCount > 0 ? round(($passCount / $bulletinsCount) * 100, 1) : 0;

        // Alertes notes critiques (< 7)
        $criticalAlerts = BulletinEntry::where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('score', '<', 7)
            ->whereNotNull('score')
            ->count();

        // Classes verrouillées
        $lockedClasses = ClassTermLock::where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('is_locked', true)
            ->count();

        return [
            'total_students'     => $totalStudents,
            'total_classes'      => $totalClasses,
            'total_teachers'     => $totalTeachers,
            'total_subject_cst'  => $totalSubjectCst,
            'global_completion'  => $globalCompletion,
            'filled_entries'     => $filledEntries,
            'global_avg'         => round($globalAvg ?? 0, 2),
            'pass_rate'          => $passRate,
            'pass_count'         => $passCount,
            'critical_alerts'    => $criticalAlerts,
            'locked_classes'     => $lockedClasses,
            'bulletins_count'    => $bulletinsCount,
        ];
    }

    private function getClassesCompletion(string $academicYear, int $term)
    {
        $classes = Classe::where('is_active', true)->get();

        return $classes->map(function (Classe $classe) use ($academicYear, $term) {
            $totalStudents = Student::where('classe_id', $classe->id)->where('is_active', true)->count();

            $completionData = SubjectCompletionTracking::where('class_id', $classe->id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->avg('completion_percentage');

            $avgGrade = BulletinSummary::where('class_id', $classe->id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->avg('term_average');

            $isLocked = ClassTermLock::where('class_id', $classe->id)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->where('is_locked', true)
                ->exists();

            return [
                'class_id'   => $classe->id,
                'class_name' => $classe->name,
                'students'   => $totalStudents,
                'completion' => round($completionData ?? 0, 1),
                'avg_grade'  => round($avgGrade ?? 0, 2),
                'is_locked'  => $isLocked,
            ];
        });
    }

    private function getAlerts(string $academicYear, int $term): array
    {
        // Matières avec 0% de complétion depuis plus de 7 jours
        $unstarted = ClassSubjectTeacher::where('is_active', true)
            ->whereDoesntHave('bulletinEntries', function ($q) use ($academicYear, $term) {
                $q->where('academic_year', $academicYear)->where('term', $term);
            })
            ->with(['classe', 'subject', 'teacher.user'])
            ->get()
            ->map(fn($cst) => [
                'type'    => 'warning',
                'message' => "Aucune note saisie : {$cst->subject?->name} — {$cst->classe?->name}",
                'teacher' => $cst->teacher?->user?->name ?? 'Non affecté',
            ])
            ->take(10)
            ->toArray();

        return $unstarted;
    }

    private function getGradeDistribution(string $academicYear, int $term): array
    {
        $ranges = [
            ['label' => '0-4', 'min' => 0,  'max' => 4,  'color' => '#dc3545'],
            ['label' => '5-7', 'min' => 5,  'max' => 7,  'color' => '#fd7e14'],
            ['label' => '8-9', 'min' => 8,  'max' => 9,  'color' => '#ffc107'],
            ['label' => '10-12', 'min' => 10, 'max' => 12, 'color' => '#20c997'],
            ['label' => '13-15', 'min' => 13, 'max' => 15, 'color' => '#0d6efd'],
            ['label' => '16-20', 'min' => 16, 'max' => 20, 'color' => '#198754'],
        ];

        foreach ($ranges as &$range) {
            $range['count'] = BulletinSummary::where('academic_year', $academicYear)
                ->where('term', $term)
                ->whereBetween('term_average', [$range['min'], $range['max']])
                ->count();
        }

        return $ranges;
    }

    private function getCriticalSubjects(string $academicYear, int $term): array
    {
        return BulletinEntry::select('subject_id', DB::raw('COUNT(*) as count'), DB::raw('AVG(score) as avg_score'))
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('score', '<', 10)
            ->whereNotNull('score')
            ->groupBy('subject_id')
            ->orderByDesc('count')
            ->take(5)
            ->with('subject')
            ->get()
            ->map(fn($e) => [
                'subject'   => $e->subject?->name ?? 'Inconnu',
                'count'     => $e->count,
                'avg_score' => round($e->avg_score, 2),
            ])
            ->toArray();
    }

    private function currentAcademicYear(): string
    {
        $year = now()->year;
        return now()->month >= 9 ? "{$year}-" . ($year + 1) : ($year - 1) . "-{$year}";
    }

    private function currentTerm(): int
    {
        $month = now()->month;
        if ($month >= 9  && $month <= 12) return 1;
        if ($month >= 1  && $month <= 3)  return 2;
        return 3;
    }
}
