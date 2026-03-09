<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\BulletinEntry;
use App\Models\BulletinSummary;
use App\Models\Classe;
use App\Models\ClassSubjectTeacher;
use App\Models\Student;
use App\Services\BulletinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * TeacherAdvancedController
 *
 * Fonctionnalités avancées de l'espace enseignant :
 * - Export PDF massif de tous les bulletins d'une classe
 * - Saisie en masse (copier-coller depuis Excel)
 * - Statistiques par matière (histogramme, écart-type)
 * - Saisie rapide via tableau (grille améliorée)
 * - Récapitulatif des saisies de l'enseignant
 */
class TeacherAdvancedController extends Controller
{
    public function __construct(
        private readonly BulletinService $bulletinService
    ) {}

    // ════════════════════════════════════════════════
    //  TABLEAU DE BORD ENSEIGNANT
    // ════════════════════════════════════════════════

    public function dashboard(Request $request): View
    {
        $user    = Auth::user();
        $teacher = $user->teacher;

        if (! $teacher && ! $user->isAdmin()) {
            abort(403);
        }

        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        // Matières enseignées
        $assignments = ClassSubjectTeacher::with(['classe', 'subject'])
            ->when(! $user->isAdmin(), fn($q) => $q->where('teacher_id', $teacher?->id))
            ->where('is_active', true)
            ->get();

        // Taux de complétion par matière
        $completionStats = $assignments->map(function (ClassSubjectTeacher $cst) use ($term, $academicYear) {
            $totalStudents = Student::where('classe_id', $cst->class_id)->where('is_active', true)->count();

            $filledSeq1 = BulletinEntry::where('class_subject_teacher_id', $cst->id)
                ->where('term', $term)->where('sequence', 1)
                ->where('academic_year', $academicYear)->whereNotNull('score')->count();

            $filledSeq2 = BulletinEntry::where('class_subject_teacher_id', $cst->id)
                ->where('term', $term)->where('sequence', 2)
                ->where('academic_year', $academicYear)->whereNotNull('score')->count();

            $avgScore = BulletinEntry::where('class_subject_teacher_id', $cst->id)
                ->where('term', $term)->where('academic_year', $academicYear)
                ->whereNotNull('score')->avg('score');

            return [
                'cst_id'       => $cst->id,
                'class'        => $cst->classe?->name,
                'subject'      => $cst->subject?->name,
                'total'        => $totalStudents,
                'seq1_filled'  => $filledSeq1,
                'seq2_filled'  => $filledSeq2,
                'seq1_pct'     => $totalStudents > 0 ? round(($filledSeq1 / $totalStudents) * 100) : 0,
                'seq2_pct'     => $totalStudents > 0 ? round(($filledSeq2 / $totalStudents) * 100) : 0,
                'avg_score'    => $avgScore ? round($avgScore, 2) : null,
            ];
        });

        return view('teacher.advanced.dashboard', compact(
            'assignments', 'completionStats', 'term', 'academicYear'
        ));
    }

    // ════════════════════════════════════════════════
    //  SAISIE EN MASSE (Import depuis tableau)
    // ════════════════════════════════════════════════

    /**
     * Reçoit un tableau de notes JSON (copier-coller ou import CSV)
     * Format: [{ student_id, score, sequence }, ...]
     */
    public function bulkSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_subject_teacher_id' => 'required|integer|exists:class_subject_teacher,id',
            'term'                     => 'required|integer|in:1,2,3',
            'sequence'                 => 'required|integer|in:1,2',
            'academic_year'            => 'required|string',
            'entries'                  => 'required|array|min:1|max:200',
            'entries.*.student_id'     => 'required|integer|exists:students,id',
            'entries.*.score'          => 'nullable|numeric|min:0|max:20',
            'entries.*.comment'        => 'nullable|string|max:300',
        ]);

        $cst = ClassSubjectTeacher::findOrFail($validated['class_subject_teacher_id']);

        // Autorisation
        $user = Auth::user();
        if (! $user->isAdmin() && $cst->teacher_id !== $user->teacher?->id) {
            return response()->json(['error' => 'Non autorisé pour cette matière.'], 403);
        }

        if ($this->bulletinService->isClassLocked($cst->class_id, $validated['term'], $validated['academic_year'])) {
            return response()->json(['error' => 'La classe est verrouillée.'], 422);
        }

        $saved   = 0;
        $errors  = [];
        $results = [];

        DB::transaction(function () use ($validated, $cst, $user, &$saved, &$errors, &$results) {
            foreach ($validated['entries'] as $index => $entryData) {
                try {
                    $entryData['class_subject_teacher_id'] = $validated['class_subject_teacher_id'];
                    $entryData['term']          = $validated['term'];
                    $entryData['sequence']      = $validated['sequence'];
                    $entryData['academic_year'] = $validated['academic_year'];

                    $result = $this->bulletinService->saveEntryAndRecalculate($entryData, $user);
                    $results[] = array_merge(['student_id' => $entryData['student_id']], $result);
                    $saved++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'row'     => $index + 1,
                        'student' => $entryData['student_id'],
                        'message' => $e->getMessage(),
                    ];
                }
            }
        });

        return response()->json([
            'success'     => true,
            'saved'       => $saved,
            'errors'      => $errors,
            'results'     => $results,
            'message'     => "{$saved} notes enregistrées" . (count($errors) > 0 ? " ({$errors} erreurs)" : '.'),
        ]);
    }

    // ════════════════════════════════════════════════
    //  STATISTIQUES PAR MATIÈRE
    // ════════════════════════════════════════════════

    public function getSubjectStats(ClassSubjectTeacher $cst, Request $request): JsonResponse
    {
        $term         = (int) $request->get('term', 1);
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());

        $scores = BulletinEntry::where('class_subject_teacher_id', $cst->id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->whereNotNull('score')
            ->pluck('score')
            ->map(fn($s) => (float) $s);

        if ($scores->isEmpty()) {
            return response()->json(['error' => 'Aucune note saisie.'], 404);
        }

        $avg     = $scores->avg();
        $min     = $scores->min();
        $max     = $scores->max();
        $count   = $scores->count();
        $median  = $this->median($scores->toArray());
        $stdDev  = $this->stdDev($scores->toArray(), $avg);
        $passRate = $scores->filter(fn($s) => $s >= 10)->count() / $count * 100;

        // Histogramme (tranches de 2)
        $histogram = [];
        for ($i = 0; $i < 20; $i += 2) {
            $histogram[] = [
                'label' => "{$i}-" . ($i + 2),
                'count' => $scores->filter(fn($s) => $s >= $i && $s < $i + 2)->count(),
            ];
        }

        return response()->json([
            'count'     => $count,
            'avg'       => round($avg, 2),
            'min'       => $min,
            'max'       => $max,
            'median'    => round($median, 2),
            'std_dev'   => round($stdDev, 2),
            'pass_rate' => round($passRate, 1),
            'histogram' => $histogram,
        ]);
    }

    // ════════════════════════════════════════════════
    //  EXPORT PDF MASSIF — Tous les bulletins d'une classe
    // ════════════════════════════════════════════════

    public function exportClasseBulletins(Classe $classe, Request $request): \Illuminate\Http\Response
    {
        $term         = (int) $request->get('term', 1);
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());

        if (! $this->canAccessClass($classe)) {
            abort(403, 'Accès non autorisé à cette classe.');
        }

        $students = Student::where('classe_id', $classe->id)
            ->where('is_active', true)
            ->with('user')
            ->orderBy('id')
            ->get();

        $bulletinsData = [];
        foreach ($students as $student) {
            $bulletinsData[] = $this->bulletinService->getFullBulletinData(
                $student->id, $classe->id, $term, $academicYear
            );
        }

        // Générer le HTML pour tous les bulletins
        $html = view('teacher.bulletin.pdf-export', [
            'classe'        => $classe,
            'bulletinsData' => $bulletinsData,
            'term'          => $term,
            'academicYear'  => $academicYear,
        ])->render();

        // Dans un vrai projet, on utiliserait DomPDF ou Browsershot
        // Ici on retourne le HTML pour que le navigateur l'imprime
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    // ════════════════════════════════════════════════
    //  RÉCAPITULATIF SAISIES ENSEIGNANT
    // ════════════════════════════════════════════════

    public function mySummary(Request $request): JsonResponse
    {
        $teacher      = Auth::user()->teacher;
        $academicYear = $request->get('academic_year', $this->currentAcademicYear());
        $term         = (int) $request->get('term', $this->currentTerm());

        if (! $teacher) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $csts = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->with(['classe', 'subject'])
            ->get();

        $summary = $csts->map(function (ClassSubjectTeacher $cst) use ($term, $academicYear) {
            $totalStudents = Student::where('classe_id', $cst->class_id)->where('is_active', true)->count();
            $savedCount    = BulletinEntry::where('class_subject_teacher_id', $cst->id)
                ->where('term', $term)->where('academic_year', $academicYear)
                ->whereNotNull('score')->count();
            $lastSaved = BulletinEntry::where('class_subject_teacher_id', $cst->id)
                ->where('term', $term)->where('academic_year', $academicYear)
                ->max('recorded_at');

            return [
                'classe'          => $cst->classe?->name,
                'subject'         => $cst->subject?->name,
                'total_expected'  => $totalStudents * 2, // 2 séquences
                'saved'           => $savedCount,
                'completion'      => $totalStudents > 0 ? round(($savedCount / ($totalStudents * 2)) * 100) : 0,
                'last_saved'      => $lastSaved ? \Carbon\Carbon::parse($lastSaved)->diffForHumans() : 'Jamais',
            ];
        });

        return response()->json(['data' => $summary]);
    }

    // ════════════════════════════════════════════════
    //  HELPERS PRIVÉS
    // ════════════════════════════════════════════════

    private function canAccessClass(Classe $classe): bool
    {
        $user = Auth::user();
        if ($user->isAdmin()) return true;

        $teacher = $user->teacher;
        if (! $teacher) return false;

        return ClassSubjectTeacher::where('class_id', $classe->id)
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->exists()
            || $classe->prof_principal_id === $user->id;
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $mid   = (int) floor($count / 2);
        return $count % 2 ? $values[$mid] : (($values[$mid - 1] + $values[$mid]) / 2);
    }

    private function stdDev(array $values, float $avg): float
    {
        $variance = array_sum(array_map(fn($v) => pow($v - $avg, 2), $values)) / count($values);
        return sqrt($variance);
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
