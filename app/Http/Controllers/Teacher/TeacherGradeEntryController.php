<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\BulletinNgSession;
use App\Models\BulletinNgNote;
use App\Models\BulletinNgStudent;
use App\Models\BulletinNgSubject;
use App\Services\BulletinCalculationService;
use App\Services\BulletinVisibilityService;
use App\Events\BulletinNoteWasSaved;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Db;
use Illuminate\Support\Facades\Log;

/**
 * TeacherGradeEntryController — Interface de saisie des notes pour enseignants
 * 
 * Permet aux enseignants affiliés de saisir les notes de leurs matières
 * dans les sessions qui leur sont visibles.
 * 
 * Routes:
 *   GET  /teacher/grades/bulletin-ng                    → index()
 *   GET  /teacher/grades/bulletin-ng/{session}/form     → editForm()
 *   POST /teacher/grades/bulletin-ng/{session}/save     → saveGrade()
 *   GET  /teacher/grades/bulletin-ng/{session}/progress → getProgress()
 */
class TeacherGradeEntryController extends Controller
{
    public function __construct(
        private BulletinVisibilityService $visibilityService,
        private BulletinCalculationService $calculationService
    ) {
    }

    /**
     * Dashboard — Afficher les sessions visibles pour ce prof
     * 
     * GET /teacher/grades/bulletin-ng
     */
    public function index()
    {
        $userId = Auth::id();

        // Récupérer sessions visibles
        $sessions = $this->visibilityService->getVisibleSessionsForTeacher($userId);

        // Charger config + subjects pour chaque session
        $sessions = $sessions->map(function ($session) use ($userId) {
            $session->config = $session->config()->first();
            $session->mySubjects = $this->visibilityService->getTeacherSubjectsInSession($userId, $session->id);
            // Calculer % completion pour chaque matière
            $session->mySubjects->each(function ($subject) use ($session) {
                $totalStudents = BulletinNgStudent::where('config_id', $session->config_id)
                    ->where('is_active', true)
                    ->count();
                $filledGrades = BulletinNgNote::where('ng_subject_id', $subject->id)
                    ->where('session_id', $session->id)
                    ->whereNotNull('note')
                    ->count();
                $subject->completion = $totalStudents > 0 ? round($filledGrades / $totalStudents * 100) : 0;
            });
            return $session;
        });

        return view('teacher.grade_entry.index', compact('sessions'));
    }

    /**
     * Formulaire de saisie des notes
     * 
     * GET /teacher/grades/bulletin-ng/{session}/form
     * 
     * Affiche une grille avec les étudiants et les notes à saisir
     * pour une ou plusieurs matières du prof
     */
    public function editForm(BulletinNgSession $session)
    {
        $userId = Auth::id();

        // Vérifier accès
        if (! $this->visibilityService->canTeacherViewSession($userId, $session->id)) {
            abort(403, 'Accès refusé à cette session');
        }

        // Vérifier que session est ouverte
        if (! $session->isEntryOpen()) {
            abort(403, 'La saisie pour cette session n\'est pas ouverte');
        }

        // Récupérer les matières du prof dans cette session
        $mySubjects = $this->visibilityService->getTeacherSubjectsInSession($userId, $session->id);
        if ($mySubjects->isEmpty()) {
            abort(403, 'Vous n\'avez aucune matière affiliée à cette session');
        }

        // Récupérer les étudiants actifs
        $students = BulletinNgStudent::where('config_id', $session->config_id)
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        // Pour chaque sujet du prof, charger les notes existantes
        $gradesBySubject = [];
        foreach ($mySubjects as $subject) {
            $notes = BulletinNgNote::where('ng_subject_id', $subject->id)
                ->where('session_id', $session->id)
                ->get()
                ->keyBy('ng_student_id');
            $gradesBySubject[$subject->id] = $notes;
        }

        return view('teacher.grade_entry.form', compact(
            'session',
            'mySubjects',
            'students',
            'gradesBySubject'
        ));
    }

    /**
     * Sauvegarder une note (AJAX) — POST
     * 
     * POST /teacher/grades/bulletin-ng/{session}/save
     * Body: { student_id, subject_id, sequence_number, note }
     * 
     * Retourne JSON avec statut et stats mises à jour
     */
    public function saveGrade(Request $request, BulletinNgSession $session): JsonResponse
    {
        $userId = Auth::id();

        // Validation de base
        if (! $request->has(['student_id', 'subject_id', 'sequence_number', 'note'])) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        $subjectId = $request->integer('subject_id');
        $noteValue = $request->input('note');

        // Vérifier que prof peut éditer cette note
        if (! $this->visibilityService->canTeacherEditGrade($userId, $subjectId, $session->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé: vous ne pouvez pas saisir les notes de cette matière',
            ], 403);
        }

        try {
            // Valider la note
            if ($noteValue !== '' && $noteValue !== null) {
                $note = floatval($noteValue);
                if ($note < 0 || $note > 20) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La note doit être entre 0 et 20',
                    ], 422);
                }
            } else {
                $note = null;
            }

            // Upsert note en BD
            $gradeRecord = BulletinNgNote::updateOrCreate(
                [
                    'config_id' => $session->config_id,
                    'ng_student_id' => $request->integer('student_id'),
                    'ng_subject_id' => $subjectId,
                    'session_id' => $session->id,
                ],
                [
                    'sequence_number' => $request->integer('sequence_number'),
                    'note' => $note,
                    'saisie_par' => $userId,
                    'saisie_at' => now(),
                ]
            );

            // Dispatcher event pour recalc stats
            BulletinNoteWasSaved::dispatch($gradeRecord, Auth::user());

            Log::info('Grade saved', [
                'session_id' => $session->id,
                'subject_id' => $subjectId,
                'student_id' => $request->integer('student_id'),
                'note' => $note,
                'saved_by' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Note sauvegardée avec succès',
                'note_id' => $gradeRecord->id,
                'note_value' => $gradeRecord->note,
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving grade', [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir le statut de progression de la saisie
     * 
     * GET /teacher/grades/bulletin-ng/{session}/progress
     * 
     * Retourne JSON avec % completion par sujet et globale
     */
    public function getProgress(BulletinNgSession $session): JsonResponse
    {
        $userId = Auth::id();

        // Vérifier accès
        if (! $this->visibilityService->canTeacherViewSession($userId, $session->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé',
            ], 403);
        }

        $mySubjects = $this->visibilityService->getTeacherSubjectsInSession($userId, $session->id);

        $totalStudents = BulletinNgStudent::where('config_id', $session->config_id)
            ->where('is_active', true)
            ->count();

        if ($totalStudents === 0) {
            return response()->json([
                'success' => true,
                'subjects' => [],
                'overall_completion' => 0,
            ]);
        }

        $subjects = $mySubjects->map(function ($subject) use ($session, $totalStudents) {
            $filledGrades = BulletinNgNote::where('ng_subject_id', $subject->id)
                ->where('session_id', $session->id)
                ->whereNotNull('note')
                ->count();

            $percentage = round($filledGrades / $totalStudents * 100);

            return [
                'subject_id' => $subject->id,
                'nom' => $subject->nom,
                'nom_prof' => $subject->nom_prof,
                'grades_entered' => $filledGrades,
                'total_students' => $totalStudents,
                'percentage' => $percentage,
                'status' => match (true) {
                    $percentage === 0 => 'Not Started',
                    $percentage < 50 => 'In Progress',
                    $percentage < 100 => 'Nearly Complete',
                    default => 'Complete',
                },
            ];
        });

        // Calc overall completion
        $totalGrades = BulletinNgNote::where('config_id', $session->config_id)
            ->where('session_id', $session->id)
            ->whereNotNull('note')
            ->count();
        $possibleGrades = $totalStudents * $mySubjects->count();
        $overallCompletion = $possibleGrades > 0 ? round($totalGrades / $possibleGrades * 100) : 0;

        return response()->json([
            'success' => true,
            'subjects' => $subjects->values(),
            'overall_completion' => $overallCompletion,
            'last_updated' => now(),
        ]);
    }
}
