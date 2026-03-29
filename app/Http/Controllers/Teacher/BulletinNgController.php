<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\BulletinNgConfig;
use App\Models\BulletinNgSession;
use App\Models\BulletinNgSubject;
use App\Models\BulletinNgStudent;
use App\Models\BulletinNgNote;
use App\Models\BulletinNgConduite;
use App\Models\BulletinNgTrimestre;
use App\Services\BulletinCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * BulletinNgController — REFACTORED for Database Persistence
 *
 * Phase 3 Refactoring: Migrate from session PHP storage → Eloquent ORM
 * 
 * Workflow:
 *   ÉTAPE 1 — Choix section (FR/EN)        → step1Section()
 *   ÉTAPE 2 — Configuration                → step2Config() / storeConfig()
 *   ÉTAPE 3 — Matières + affectations      → step3Subjects() / storeSubjects()
 *   ÉTAPE 4 — Étudiants                    → step4Students() / (storeStudent via API)
 *   ÉTAPE 5 — Saisie notes + visibilité    → step5Notes() / publishToTeachers()
 *   ÉTAPE 6 — Conduite                     → step6Conduite()
 *   ÉTAPE 7 — Génération PDF               → step7Generate() / pdfStudent()
 */
class BulletinNgController extends Controller
{
    public function __construct(private BulletinCalculationService $calculationService)
    {
    }

    /* ═══════════════════════════════════════════════════════
     *  DASHBOARD & WIZARD ENTRY
     * ═══════════════════════════════════════════════════════ */

    /**
     * Dashboard principal — Afficher toutes les sessions du prof
     * 
     * GET /teacher/bulletin-ng
     */
    public function index()
    {
        $userId = Auth::id();

        // Récupérer toutes les configs où prof est prof_principal
        $configs = BulletinNgConfig::where('prof_principal_id', $userId)
            ->with(['subjects', 'students', 'sessions'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pour chaque config, enrichir avec info de sessions
        $configs->each(function ($config) {
            $config->sessions_count = $config->sessions()->count();
            $config->latest_session = $config->sessions()
                ->orderBy('created_at', 'desc')
                ->first();
            $config->completion_percent = $this->getConfigCompletion($config);
        });

        return view('teacher.bulletin_ng.index', compact('configs'));
    }

    /**
     * ÉTAPE 1 — Choix de la section (FR/EN)
     */
    public function step1Section()
    {
        return view('teacher.bulletin_ng.step1_section');
    }

    /**
     * ÉTAPE 2 — Formulaire de configuration
     * 
     * GET /teacher/bulletin-ng/step2
     */
    public function step2Config(Request $request)
    {
        $langue = $request->query('langue', 'FR');
        $configId = $request->query('config_id');
        $config = null;

        if ($configId) {
            $config = BulletinNgConfig::find($configId);
            if ($config && $config->prof_principal_id !== Auth::id() && ! Auth::user()->is_admin) {
                abort(403, 'Access denied');
            }
        }

        return view('teacher.bulletin_ng.step2_config', compact('langue', 'config'));
    }

    /**
     * Sauvegarder la configuration
     * 
     * POST /teacher/bulletin-ng/store-config
     */
    public function storeConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'config_id'        => 'nullable|exists:bulletin_ng_configs,id',
            'langue'           => 'required|in:FR,EN',
            'school_name'      => 'required|string|max:200',
            'delegation_fr'    => 'nullable|string|max:200',
            'delegation_en'    => 'nullable|string|max:200',
            'nom_classe'       => 'required|string|max:100',
            'effectif'         => 'required|integer|min:1',
            'trimestre'        => 'required|integer|in:1,2,3',
            'sequence'         => 'required|integer|in:1,2,3,4,5,6',
            'annee_academique' => 'required|string|max:9',
            'logo'             => 'nullable|image|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            // Delete old logo if updating
            if ($request->filled('config_id')) {
                $oldConfig = BulletinNgConfig::find($request->input('config_id'));
                if ($oldConfig && $oldConfig->logo_path) {
                    Storage::disk('public')->delete($oldConfig->logo_path);
                }
            }
            $logoPath = $request->file('logo')->store('bulletin_ng/logos', 'public');
        }

        try {
            $config = BulletinNgConfig::updateOrCreate(
                ['id' => $request->input('config_id')],
                array_merge($validated, [
                    'prof_principal_id' => Auth::id(),
                    'logo_path'         => $logoPath,
                    'statut'            => BulletinNgConfig::STATUT_CONFIG,
                ])
            );

            Log::info('Config saved', ['config_id' => $config->id, 'user_id' => Auth::id()]);

            return response()->json([
                'success' => true,
                'config_id' => $config->id,
                'message' => 'Configuration enregistrée.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving config', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  ÉTAPE 3 — MATIÈRES
     * ═══════════════════════════════════════════════════════ */

    /**
     * ÉTAPE 3 — Formulaire des matières
     * 
     * GET /teacher/bulletin-ng/{config}/step3
     */
    public function step3Subjects(BulletinNgConfig $config)
    {
        // Vérifier accès
        $this->authorizeConfig($config);

        $subjects = $config->subjects()->orderBy('ordre')->get();

        return view('teacher.bulletin_ng.step3_subjects', compact('config', 'subjects'));
    }

    /**
     * POST — Sauvegarder les matières
     */
    public function storeSubjects(Request $request, BulletinNgConfig $config): JsonResponse
    {
        $this->authorizeConfig($config);

        $validated = $request->validate([
            'subjects'               => 'required|array|min:1',
            'subjects.*.id'          => 'nullable|exists:bulletin_ng_subjects,id',
            'subjects.*.nom'         => 'required|string|max:150',
            'subjects.*.coefficient' => 'required|numeric|min:0.5|max:20',
            'subjects.*.nom_prof'    => 'nullable|string|max:150',
            'subjects.*.user_id'     => 'nullable|exists:users,id',
        ]);

        try {
            DB::transaction(function () use ($config, $validated) {
                // Supprimer les matières non listées
                $existingIds = collect($validated['subjects'])
                    ->where('id', '!=', null)
                    ->pluck('id')
                    ->toArray();

                $config->subjects()
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                // Upsert matières
                foreach ($validated['subjects'] as $ordre => $subjectData) {
                    BulletinNgSubject::updateOrCreate(
                        ['id' => $subjectData['id'] ?? null],
                        [
                            'config_id'   => $config->id,
                            'nom'         => $subjectData['nom'],
                            'coefficient' => $subjectData['coefficient'],
                            'nom_prof'    => $subjectData['nom_prof'],
                            'user_id'     => $subjectData['user_id'] ?? null,
                            'ordre'       => $ordre,
                        ]
                    );
                }
            });

            Log::info('Subjects saved', ['config_id' => $config->id]);

            return response()->json(['success' => true, 'message' => 'Matières enregistrées.']);
        } catch (\Exception $e) {
            Log::error('Error saving subjects', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  ÉTAPE 4 — ÉTUDIANTS
     * ═══════════════════════════════════════════════════════ */

    /**
     * ÉTAPE 4 — Gestion des élèves
     * 
     * GET /teacher/bulletin-ng/{config}/step4
     */
    public function step4Students(BulletinNgConfig $config)
    {
        $this->authorizeConfig($config);

        $students = $config->students()
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        return view('teacher.bulletin_ng.step4_students', compact('config', 'students'));
    }

    /**
     * POST — Ajouter un élève
     * 
     * POST /teacher/bulletin-ng/{config}/students
     */
    public function storeStudent(Request $request, BulletinNgConfig $config): JsonResponse
    {
        $this->authorizeConfig($config);

        $validated = $request->validate([
            'matricule'      => 'required|string|max:50',
            'nom'            => 'required|string|max:200',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:150',
            'sexe'           => 'required|in:M,F',
        ]);

        try {
            $student = $config->students()->create(array_merge($validated, [
                'matricule_original' => $validated['matricule'],
                'is_active'          => true,
                'ordre'              => $config->students()->count(),
            ]));

            Log::info('Student added', ['config_id' => $config->id, 'student_id' => $student->id]);

            return response()->json(['success' => true, 'student' => $student]);
        } catch (\Exception $e) {
            Log::error('Error adding student', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE — Supprimer un élève
     * 
     * DELETE /teacher/bulletin-ng/{config}/students/{student}
     */
    public function deleteStudent(BulletinNgConfig $config, BulletinNgStudent $student): JsonResponse
    {
        $this->authorizeConfig($config);

        if ($student->config_id !== $config->id) {
            abort(403);
        }

        $student->delete();
        return response()->json(['success' => true]);
    }

    /* ═══════════════════════════════════════════════════════
     *  ÉTAPE 5 — SAISIE NOTES & VISIBILITÉ
     * ═══════════════════════════════════════════════════════ */

    /**
     * ÉTAPE 5 — Grille de saisie des notes
     * 
     * GET /teacher/bulletin-ng/{config}/step5
     */
    public function step5Notes(BulletinNgConfig $config)
    {
        $this->authorizeConfig($config);

        $subjects = $config->subjects()->orderBy('ordre')->get();
        $students = $config->students()->where('is_active', true)->orderBy('ordre')->get();
        
        // Récupérer ou créer session pour cette séquence
        $session = $config->sessions()
            ->where('trimestre_number', $config->trimestre)
            ->where('sequence_number', $config->sequence)
            ->first();

        if (! $session) {
            $session = $config->sessions()->create([
                'trimestre_number' => $config->trimestre,
                'sequence_number'  => $config->sequence,
                'statut'           => BulletinNgSession::STATUS_DRAFT,
            ]);
        }

        // Charger les notes existantes — grouper par {student_id}_{subject_id} pour accès facile
        $notesCollection = $session->notes()
            ->with(['student', 'subject'])
            ->get();
        
        // Créer un tableau clé = "{student_id}_{subject_id}" pour accès O(1) dans la vue
        $notes = [];
        foreach ($notesCollection as $note) {
            $key = "{$note->ng_student_id}_{$note->ng_subject_id}";
            $notes[$key] = $note;
        }

        // ✅ CRITICAL: Pass session to calculateStats for proper filtering
        $stats = $this->calculateStats($config, $session);

        return view('teacher.bulletin_ng.step5_notes', compact(
            'config', 'session', 'subjects', 'students', 'notes', 'stats'
        ));
    }

    /**
     * NOUVELLE MÉTHODE — Rendre la session visible aux enseignants
     * 
     * POST /teacher/bulletin-ng/{session}/publish
     */
    public function publishToTeachers(BulletinNgSession $session): JsonResponse
    {
        // Vérifier accès
        if ($session->config->prof_principal_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403);
        }

        $session->publishToTeachers();

        Log::info('Session published', ['session_id' => $session->id]);

        return response()->json(['success' => true, 'message' => 'Session visible aux enseignants.']);
    }

    /**
     * Sauvegarder une note via AJAX
     * 
     * POST /teacher/bulletin-ng/{config}/save-note
     */
    public function saveNote(Request $request, BulletinNgConfig $config): JsonResponse
    {
        $this->authorizeConfig($config);

        $validated = $request->validate([
            'ng_student_id' => 'required|exists:bulletin_ng_students,id',
            'ng_subject_id' => 'required|exists:bulletin_ng_subjects,id',
            'note'          => 'nullable|numeric|min:0|max:20',
        ]);

        try {
            // Trouver ou créer session pour cette séquence
            $session = $config->sessions()
                ->where('trimestre_number', $config->trimestre)
                ->where('sequence_number', $config->sequence)
                ->firstOrCreate([], [
                    'statut' => BulletinNgSession::STATUS_DRAFT,
                ]);

            // Upsert la note
            $note = BulletinNgNote::updateOrCreate(
                [
                    'config_id'      => $config->id,
                    'ng_student_id'  => $validated['ng_student_id'],
                    'ng_subject_id'  => $validated['ng_subject_id'],
                    'session_id'     => $session->id,
                ],
                [
                    'sequence_number' => $config->sequence,
                    'note'            => $validated['note'],
                    'saisie_par'      => Auth::id(),
                    'saisie_at'       => now(),
                ]
            );

            // Dispatcher event pour recalc (listener va déclencher)
            \App\Events\BulletinNoteWasSaved::dispatch($note, Auth::user());

            // ✅ IMPORTANT: Pass session to calculateStats for proper filtering
            $stats = $this->calculateStats($config, $session);

            return response()->json([
                'success' => true,
                'note_id' => $note->id,
                'stats'   => $stats  // ✅ CRITICAL: Include stats for real-time update
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving note', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  ÉTAPE 6 — CONDUITE & COMPORTEMENT
     * ═══════════════════════════════════════════════════════ */

    /**
     * ÉTAPE 6 — Conduite & comportement
     * 
     * GET /teacher/bulletin-ng/{config}/step6
     */
    public function step6Conduite(BulletinNgConfig $config)
    {
        $this->authorizeConfig($config);

        $students = $config->students()->where('is_active', true)->orderBy('ordre')->get();
        $conduites = $config->conduites()->get()->keyBy('ng_student_id');
        
        // ✅ CRITICAL: Find session and pass to calculateStats for proper student data
        $session = $config->sessions()
            ->where('trimestre_number', $config->trimestre)
            ->where('sequence_number', $config->sequence)
            ->first();

        // Calculate and pass stats with student averages and rankings
        $stats = $this->calculateStats($config, $session);

        return view('teacher.bulletin_ng.step6_conduite', compact('config', 'students', 'conduites', 'stats'));
    }

    /**
     * Sauvegarder conduite
     * 
     * POST /teacher/bulletin-ng/{config}/students/{student}/conduite
     */
    public function saveConduite(Request $request, BulletinNgConfig $config, BulletinNgStudent $student): JsonResponse
    {
        $this->authorizeConfig($config);

        $validated = $request->validate([
            'tableau_honneur'  => 'boolean',
            'encouragement'    => 'boolean',
            'felicitations'    => 'boolean',
            'blame_travail'    => 'boolean',
            'avert_travail'    => 'string|max:50',
            'absences_totales' => 'integer|min:0',
            'absences_nj'      => 'integer|min:0',
            'exclusion'        => 'boolean',
            'avert_conduite'   => 'string|max:50',
            'blame_conduite'   => 'string|max:50',
        ]);

        try {
            BulletinNgConduite::updateOrCreate(
                ['config_id' => $config->id, 'ng_student_id' => $student->id],
                $validated
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * NOUVELLE — Finaliser toutes les conduites (bulk)
     * 
     * POST /teacher/bulletin-ng/{config}/finalize-conduct
     */
    public function finalizeConduite(Request $request, BulletinNgConfig $config)
    {
        $this->authorizeConfig($config);

        $conduites = $request->input('conduct', []);

        try {
            DB::transaction(function () use ($config, $conduites) {
                foreach ($conduites as $studentId => $data) {
                    BulletinNgConduite::updateOrCreate(
                        ['config_id' => $config->id, 'ng_student_id' => $studentId],
                        [
                            'tableau_honneur'  => (bool)($data['tableau_honneur'] ?? false),
                            'encouragement'    => (bool)($data['encouragement'] ?? false),
                            'felicitations'    => (bool)($data['felicitations'] ?? false),
                            'blame_travail'    => (bool)($data['blame_travail'] ?? false),
                            'avert_travail'    => $data['avert_travail'] ?? 'Non',
                            'absences_totales' => (int)($data['absences_totales'] ?? 0),
                            'absences_nj'      => (int)($data['absences_nj'] ?? 0),
                            'exclusion'        => (bool)($data['exclusion'] ?? false),
                            'avert_conduite'   => $data['avert_conduite'] ?? 'Non',
                            'blame_conduite'   => $data['blame_conduite'] ?? 'Non',
                        ]
                    );
                }
            });

            Log::info('Conduites finalized', ['config_id' => $config->id]);

            return redirect()->route('teacher.bulletin_ng.step7', $config->id)
                ->with('success', 'Conduites enregistrées avec succès.');
        } catch (\Exception $e) {
            Log::error('Error finalizing conduites', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withErrors(['message' => $e->getMessage()])
                ->withInput();
        }
    }

    /* ═══════════════════════════════════════════════════════
     *  ÉTAPE 7 — GÉNÉRATION & EXPORT PDF
     * ═══════════════════════════════════════════════════════ */

    /**
     * ÉTAPE 7 — Aperçu & génération
     * 
     * GET /teacher/bulletin-ng/{config}/step7
     */
    public function step7Generate(BulletinNgConfig $config)
    {
        $this->authorizeConfig($config);

        // Charger les étudiants avec toutes les relations
        $students = $config->students()
            ->where('is_active', true)
            ->with(['trimestres', 'conduite', 'notes'])
            ->orderBy('ordre')
            ->get();
        
        $subjects = $config->subjects()->orderBy('ordre')->get();

        // Charger les trimestres pour les étudiants
        $trimestres = $config->trimestres()->get()->groupBy('ng_student_id');

        // ✅ FIX: Load notes filtered by current session to prevent data mixing
        $session = $config->sessions()
            ->where('trimestre_number', $config->trimestre)
            ->where('sequence_number', $config->sequence)
            ->latest()
            ->first();
        
        $notesQuery = BulletinNgNote::whereIn('ng_student_id', $students->pluck('id'))
            ->where('config_id', $config->id);
        
        // ✅ FIX: Filter by session if it exists
        if ($session) {
            $notesQuery = $notesQuery->where('session_id', $session->id);
        }
        
        $notes = $notesQuery->get()->groupBy('ng_student_id');

        // Calculer les statistiques de la classe
        $stats = $this->calculateClassStats($config, $students);

        return view('teacher.bulletin_ng.step7_generate', compact(
            'config', 'students', 'subjects', 'trimestres', 'stats', 'notes'
        ));
    }

    /**
     * Calculer les statistiques de classe pour l'affichage - USES EXACT SAME CONTROLLER METHOD AS STEP 5
     * This calls step5Notes() method to ensure identical calculation
     * 
     * @param BulletinNgConfig $config
     * @param Collection $students  
     * @return array
     */
    private function calculateClassStats(BulletinNgConfig $config, $students): array
    {
        // Get the current session
        $session = $config->sessions()
            ->where('trimestre_number', $config->trimestre)
            ->where('sequence_number', $config->sequence)
            ->latest()
            ->first();

        // ✅ USE EXACT SAME CALCULATION AS STEP 5
        // Call calculateStats which has the proven correct logic
        $stats = $this->calculateStats($config, $session);

        // Add 'complete' flag for Step 7
        $stats['complete'] = $stats['avg'] > 0;

        return $stats;
    }

    /**
     * Générer PDF pour un élève
     * 
     * GET /teacher/bulletin-ng/{config}/students/{student}/pdf
     */
    public function pdfStudent(BulletinNgConfig $config, BulletinNgStudent $student)
    {
        $this->authorizeConfig($config);

        if ($student->config_id !== $config->id) {
            abort(403);
        }

        // Charger les matières avec les professeurs
        $subjects = $config->subjects()
            ->with('teacher')
            ->orderBy('ordre')
            ->get();

        // Charger la conduite
        $conduite = $student->conduite()->first() ?? new BulletinNgConduite();

        // Charger toutes les notes de l'étudiant
        $allNotes = BulletinNgNote::where('ng_student_id', $student->id)
            ->where('config_id', $config->id)
            ->with('subject')
            ->get();

        // Préparer les détails des notes par matière
        $courseDetails = [];
        foreach ($subjects as $subject) {
            $subjectNotes = $allNotes->where('ng_subject_id', $subject->id);
            
            // ✅ FIX: Calculate sequences based on trimestre
            if ($config->trimestre === 1) {
                $seq1Notes = $subjectNotes->where('sequence_number', 1)->pluck('note');
                $seq2Notes = $subjectNotes->where('sequence_number', 2)->pluck('note');
            } elseif ($config->trimestre === 2) {
                $seq1Notes = $subjectNotes->where('sequence_number', 3)->pluck('note');
                $seq2Notes = $subjectNotes->where('sequence_number', 4)->pluck('note');
            } else {
                // Trimestre 3
                $seq1Notes = $subjectNotes->where('sequence_number', 5)->pluck('note');
                $seq2Notes = $subjectNotes->where('sequence_number', 6)->pluck('note');
            }
            
            $seq1 = $seq1Notes->isNotEmpty() ? $seq1Notes->average() : 0;
            $seq2 = $seq2Notes->isNotEmpty() ? $seq2Notes->average() : 0;
            
            // ✅ FIX: Calculate composite for all trimesters
            $composite = ($seq1 > 0 || $seq2 > 0) ? round(($seq1 + $seq2) / 2, 2) : 0;
            
            // Moyenne générale pour le trimestre
            $moyenne = $this->calculationService->calculateTrimesterAverage(
                $student->id,
                $config->trimestre,
                $config->id
            );
            
            // Total pondéré
            $total = $composite * $subject->coefficient;
            
            // Appréciation basée sur la note
            $appreciation = match(true) {
                $composite >= 16 => 'Excellent',
                $composite >= 14 => 'Très Bien',
                $composite >= 12 => 'Bien',
                $composite >= 10 => 'Assez Bien',
                $composite >= 8  => 'Passable',
                default          => 'Faible'
            };
            
            // ✅ FIX: Calculate subject rank instead of hardcoding '-'
            $subjectRank = $this->calculateSubjectRank($subject, $student->id, $config->trimestre, $config);
            
            $courseDetails[$subject->id] = [
                'seq1'         => round($seq1, 2),
                'seq2'         => round($seq2, 2),
                'composite'    => round($composite, 2),
                'moyenne'      => round($moyenne, 2),
                'total'        => round($total, 2),
                'rang'         => $subjectRank,
                'appreciation' => $appreciation,
            ];
        }

        // Données de résumé
        $trimestres = $student->trimestres()->get();
        $currentTrimData = $trimestres->where('trimestre_number', $config->trimestre)->first();
        
        $summaryData = [
            'student_avg'   => $currentTrimData->moyenne ?? 0,
            'student_rank'  => $currentTrimData->rang_classe ?? '-',
            'status'        => ($currentTrimData->moyenne ?? 0) >= 10 ? 'ACQUIS' : 'NON ACQUIS',
            'class_avg'     => $this->calculateClassAverage($config),
            'success_rate'  => $this->calculateSuccessRate($config),
            'max_avg'       => $this->calculateMaxAverage($config),
            'min_avg'       => $this->calculateMinAverage($config),
        ];

        // ✅ FIX: Ensure logo is properly retrieved
        $logoUrl = null;
        if ($config && $config->logo_path) {
            // For PDF generation, use file path instead of asset URL
            $logoPath = storage_path('app/public/' . $config->logo_path);
            if (file_exists($logoPath)) {
                $logoUrl = $logoPath;  // Use file path for DomPDF
            }
        }

        $pdf = Pdf::loadView('teacher.bulletin_ng.pdf.bulletin_professionnel', [
            'student'       => $student,
            'config'        => $config,
            'subjects'      => $subjects,
            'conduite'      => $conduite,
            'courseDetails' => $courseDetails,
            'summaryData'   => $summaryData,
            'logoUrl'       => $logoUrl,
        ]);

        $filename = "bulletin_{$student->nom}_{$config->trimestre}_{$config->sequence}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Calculer la moyenne de classe
     */
    private function calculateClassAverage(BulletinNgConfig $config): float
    {
        $students = $config->students()->where('is_active', true)->pluck('id');
        $averages = [];
        
        foreach ($students as $studentId) {
            $avg = $this->calculationService->calculateTrimesterAverage(
                $studentId,
                $config->trimestre,
                $config->id
            );
            $averages[] = $avg;
        }
        
        return !empty($averages) ? round(array_sum($averages) / count($averages), 2) : 0;
    }

    /**
     * Calculer le taux de réussite
     */
    private function calculateSuccessRate(BulletinNgConfig $config): int
    {
        $students = $config->students()->where('is_active', true)->pluck('id');
        $passing = 0;
        $total = count($students);
        
        foreach ($students as $studentId) {
            $avg = $this->calculationService->calculateTrimesterAverage(
                $studentId,
                $config->trimestre,
                $config->id
            );
            if ($avg >= 10) {
                $passing++;
            }
        }
        
        return $total > 0 ? (int)(($passing / $total) * 100) : 0;
    }

    /**
     * Calculer la moyenne maximale de classe
     */
    private function calculateMaxAverage(BulletinNgConfig $config): float
    {
        $students = $config->students()->where('is_active', true)->pluck('id');
        $averages = [];
        
        foreach ($students as $studentId) {
            $avg = $this->calculationService->calculateTrimesterAverage(
                $studentId,
                $config->trimestre,
                $config->id
            );
            $averages[] = $avg;
        }
        
        return !empty($averages) ? round(max($averages), 2) : 0;
    }

    /**
     * Calculer la moyenne minimale de classe
     */
    private function calculateMinAverage(BulletinNgConfig $config): float
    {
        $students = $config->students()->where('is_active', true)->pluck('id');
        $averages = [];
        
        foreach ($students as $studentId) {
            $avg = $this->calculationService->calculateTrimesterAverage(
                $studentId,
                $config->trimestre,
                $config->id
            );
            $averages[] = $avg;
        }
        
        return !empty($averages) ? round(min($averages), 2) : 0;
    }

    /**
     * Afficher un aperçu du bulletin (web, non-PDF)
     * 
     * GET /teacher/bulletin-ng/{config}/students/{student}/preview
     */
    public function previewStudent(BulletinNgConfig $config, BulletinNgStudent $student)
    {
        $this->authorizeConfig($config);

        if ($student->config_id !== $config->id) {
            abort(403);
        }

        // Charger les matières avec les professeurs
        $subjects = $config->subjects()
            ->with('teacher')
            ->orderBy('ordre')
            ->get();

        // Charger la conduite
        $conduite = $student->conduite()->first() ?? new BulletinNgConduite();

        // Charger toutes les notes de l'étudiant
        $allNotes = BulletinNgNote::where('ng_student_id', $student->id)
            ->where('config_id', $config->id)
            ->with('subject')
            ->get();

        // Préparer les détails des notes par matière
        $courseDetails = [];
        foreach ($subjects as $subject) {
            $subjectNotes = $allNotes->where('ng_subject_id', $subject->id);
            
            // ✅ FIX: Calculate sequences based on trimestre
            if ($config->trimestre === 1) {
                $seq1Notes = $subjectNotes->where('sequence_number', 1)->pluck('note');
                $seq2Notes = $subjectNotes->where('sequence_number', 2)->pluck('note');
            } elseif ($config->trimestre === 2) {
                $seq1Notes = $subjectNotes->where('sequence_number', 3)->pluck('note');
                $seq2Notes = $subjectNotes->where('sequence_number', 4)->pluck('note');
            } else {
                // Trimestre 3
                $seq1Notes = $subjectNotes->where('sequence_number', 5)->pluck('note');
                $seq2Notes = $subjectNotes->where('sequence_number', 6)->pluck('note');
            }
            
            $seq1 = $seq1Notes->isNotEmpty() ? $seq1Notes->average() : 0;
            $seq2 = $seq2Notes->isNotEmpty() ? $seq2Notes->average() : 0;
            
            // ✅ FIX: Calculate composite for all trimesters
            $composite = ($seq1 > 0 || $seq2 > 0) ? round(($seq1 + $seq2) / 2, 2) : 0;
            
            // Moyenne générale pour le trimestre
            $moyenne = $this->calculationService->calculateTrimesterAverage(
                $student->id,
                $config->trimestre,
                $config->id
            );
            
            // Total pondéré
            $total = $composite * $subject->coefficient;
            
            // Appréciation basée sur la note
            $appreciation = match(true) {
                $composite >= 16 => 'Excellent',
                $composite >= 14 => 'Très Bien',
                $composite >= 12 => 'Bien',
                $composite >= 10 => 'Assez Bien',
                $composite >= 8  => 'Passable',
                default          => 'Faible'
            };
            
            // ✅ FIX: Calculate subject rank instead of hardcoding '-'
            $subjectRank = $this->calculateSubjectRank($subject, $student->id, $config->trimestre, $config);
            
            $courseDetails[$subject->id] = [
                'seq1'         => round($seq1, 2),
                'seq2'         => round($seq2, 2),
                'composite'    => round($composite, 2),
                'moyenne'      => round($moyenne, 2),
                'total'        => round($total, 2),
                'rang'         => $subjectRank,
                'appreciation' => $appreciation,
            ];
        }

        // Données de résumé
        $trimestres = $student->trimestres()->get();
        $currentTrimData = $trimestres->where('trimestre_number', $config->trimestre)->first();
        
        $summaryData = [
            'student_avg'   => $currentTrimData->moyenne ?? 0,
            'student_rank'  => $currentTrimData->rang_classe ?? '-',
            'status'        => ($currentTrimData->moyenne ?? 0) >= 10 ? 'ACQUIS' : 'NON ACQUIS',
            'class_avg'     => $this->calculateClassAverage($config),
            'success_rate'  => $this->calculateSuccessRate($config),
            'max_avg'       => $this->calculateMaxAverage($config),
            'min_avg'       => $this->calculateMinAverage($config),
        ];

        // ✅ FIX: Ensure logo is properly retrieved
        $logoUrl = null;
        if ($config && $config->logo_path) {
            // For PDF generation, use file path instead of asset URL
            $logoPath = storage_path('app/public/' . $config->logo_path);
            if (file_exists($logoPath)) {
                $logoUrl = $logoPath;  // Use file path for DomPDF
            }
        }

        return view('teacher.bulletin_ng.pdf.bulletin_professionnel', [
            'student'       => $student,
            'config'        => $config,
            'subjects'      => $subjects,
            'conduite'      => $conduite,
            'courseDetails' => $courseDetails,
            'summaryData'   => $summaryData,
            'logoUrl'       => $logoUrl,
            'preview'       => true,
        ]);
    }

    /**
     * NOUVELLE — Fermer/Verrouiller la saisie
     * 
     * POST /teacher/bulletin-ng/{session}/lock
     */
    public function lockNotes(BulletinNgSession $session): JsonResponse
    {
        if ($session->config->prof_principal_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403);
        }

        $session->closeSaisie();

        Log::info('Session locked', ['session_id' => $session->id]);

        return response()->json(['success' => true, 'message' => 'Saisie fermée.']);
    }

    /* ═══════════════════════════════════════════════════════
     *  HELPERS
     * ═══════════════════════════════════════════════════════ */

    /**
     * Vérifier accès à la config
     */
    protected function authorizeConfig(BulletinNgConfig $config): void
    {
        if ($config->prof_principal_id !== Auth::id() && ! Auth::user()->is_admin) {
            abort(403, 'Access denied');
        }
    }

    /**
     * Calculer % completion pour une config
     */
    protected function getConfigCompletion(BulletinNgConfig $config): int
    {
        $totalStudents = $config->students()->where('is_active', true)->count();
        if ($totalStudents === 0) {
            return 0;
        }

        $totalSubjects = $config->subjects()->count();
        if ($totalSubjects === 0) {
            return 0;
        }

        $totalNotes = $config->notes()
            ->whereNotNull('note')
            ->count();

        $possibleNotes = $totalStudents * $totalSubjects;

        return $possibleNotes > 0 ? round($totalNotes / $possibleNotes * 100) : 0;
    }

    /**
     * Calculer stats pour la config - USES WEIGHTED AVERAGES from BulletinCalculationService
     * @param BulletinNgConfig $config
     * @param BulletinNgSession|null $session - If null, will find current session by trimestre+sequence
     */
    protected function calculateStats(BulletinNgConfig $config, ?BulletinNgSession $session = null): array
    {
        $students = $config->students()->where('is_active', true)->get();
        $totalStudentsCount = $students->count();
        $subjects = $config->subjects()->count();
        
        // ✅ FIX: Use BulletinCalculationService for WEIGHTED averages (not raw notes)
        $studentAverages = $this->getStudentAverages($config);

        if (empty($studentAverages)) {
            return [
                'avg'             => 0,
                'pct'             => 0,
                'max'             => 0,
                'min'             => 0,
                'passing'         => 0,
                'total_students'  => $totalStudentsCount,
                'total_subjects'  => $subjects,
                'total_notes'     => 0,
                'avgs'            => [],
                'ranks'           => [],
            ];
        }

        // ✅ FIX: Calculate class average from weighted student averages (not raw notes)
        $classAverage = array_sum($studentAverages) / count($studentAverages);
        
        // ✅ FIX: Count students with average >= 10 (passing) from weighted averages
        $passingStudents = 0;
        foreach ($studentAverages as $avg_grade) {
            if ($avg_grade >= 10) {
                $passingStudents++;
            }
        }
        $pct = count($studentAverages) > 0 ? round(($passingStudents / count($studentAverages)) * 100) : 0;

        // ✅ FIX: Use proven ranking method
        $ranks = $this->calculateStudentRanks($studentAverages);

        // ✅ FIX: Get min/max from weighted averages
        $sortedAverages = collect($studentAverages)->sortByDesc(function ($val) {
            return $val;
        })->values();
        
        $maxStudentAvg = $sortedAverages->count() > 0 ? $sortedAverages->first() : 0;
        $minStudentAvg = $sortedAverages->count() > 0 ? $sortedAverages->last() : 0;
        
        // Count total notes entered (for progress indication)
        if ($session) {
            $totalNotes = $session->notes()->whereNotNull('note')->count();
        } else {
            $totalNotes = $config->notes()->whereNotNull('note')->count();
        }

        return [
            'avg'             => round($classAverage, 2),  // ✅ Weighted
            'pct'             => $pct,
            'max'             => round($maxStudentAvg, 2),  // ✅ From weighted averages
            'min'             => round($minStudentAvg, 2),  // ✅ From weighted averages
            'passing'         => $passingStudents,
            'total_students'  => $totalStudentsCount,
            'total_subjects'  => $subjects,
            'total_notes'     => $totalNotes,
            'avgs'            => $studentAverages,
            'ranks'           => $ranks,
        ];
    }

    /**
     * Helper: Calculate individual student WEIGHTED averages using BulletinCalculationService
     * This ensures consistency with PDF calculations
     * ✅ FIX: Uses proper weighted averages (grade × coefficient) / sum(coefficients)
     */
    protected function getStudentAverages(BulletinNgConfig $config, $session = null): array
    {
        $studentAverages = [];
        $students = $config->students()->where('is_active', true)->get();
        
        foreach ($students as $student) {
            // ✅ FIX: Use BulletinCalculationService for weighted averages
            // This calls calculateTrimesterAverage() which properly weights by coefficient
            $avg = $this->calculationService->calculateTrimesterAverage(
                $student->id,
                $config->trimestre,
                $config->id
            );
            $studentAverages[$student->id] = $avg;
        }
        
        return $studentAverages;
    }

    /**
     * Helper: Calculate student rankings based on averages
     */
    protected function calculateStudentRanks(array $studentAverages): array
    {
        // Sort students by average (descending)
        $sorted = collect($studentAverages)->sortByDesc(function ($avg) {
            return $avg;
        });

        // Assign ranks (handling ties properly)
        $ranks = [];
        $previousAvg = null;
        $rank = 1;
        $count = 0;

        foreach ($sorted as $studentId => $avg) {
            $count++;
            // If average is different from previous, update rank
            if ($previousAvg !== null && $avg < $previousAvg) {
                $rank = $count;
            }
            $ranks[$studentId] = $rank;
            $previousAvg = $avg;
        }

        return $ranks;
    }

    /**
     * Calculate subject-level rank for a student
     * 
     * @param BulletinNgSubject $subject
     * @param int $studentId  
     * @param int $trimestre
     * @param BulletinNgConfig $config
     * @return int Rank (1 = highest score)
     */
    protected function calculateSubjectRank(BulletinNgSubject $subject, int $studentId, int $trimestre, BulletinNgConfig $config): int
    {
        // Get all students in the class
        $allStudents = $config->students()->where('is_active', true)->pluck('id');
        
        // Get all notes for this subject for this trimestre
        $allNotes = BulletinNgNote::where('config_id', $config->id)
            ->where('ng_subject_id', $subject->id)
            ->whereIn('ng_student_id', $allStudents)
            ->with('student')
            ->get();

        // Calculate composite score for each student
        $composites = [];
        foreach ($allStudents as $stdId) {
            $studentNotes = $allNotes->where('ng_student_id', $stdId);
            
            // Get sequences based on trimestre
            if ($trimestre === 1) {
                $seq1Notes = $studentNotes->where('sequence_number', 1)->pluck('note');
                $seq2Notes = $studentNotes->where('sequence_number', 2)->pluck('note');
            } elseif ($trimestre === 2) {
                $seq1Notes = $studentNotes->where('sequence_number', 3)->pluck('note');
                $seq2Notes = $studentNotes->where('sequence_number', 4)->pluck('note');
            } else {
                // Trimestre 3
                $seq1Notes = $studentNotes->where('sequence_number', 5)->pluck('note');
                $seq2Notes = $studentNotes->where('sequence_number', 6)->pluck('note');
            }
            
            $seq1 = $seq1Notes->isNotEmpty() ? $seq1Notes->average() : 0;
            $seq2 = $seq2Notes->isNotEmpty() ? $seq2Notes->average() : 0;
            
            $composite = ($seq1 > 0 || $seq2 > 0) ? round(($seq1 + $seq2) / 2, 2) : 0;
            
            $composites[$stdId] = $composite;
        }

        // Sort composites descending and assign ranks
        $sorted = collect($composites)->sortByDesc(function ($val) {
            return $val;
        });

        $rank = 1;
        $previousComposite = null;
        $count = 0;

        foreach ($sorted as $stdId => $composite) {
            $count++;
            if ($previousComposite !== null && $composite < $previousComposite) {
                $rank = $count;
            }
            if ($stdId === $studentId) {
                return $rank;
            }
            $previousComposite = $composite;
        }

        return '-';
    }
}