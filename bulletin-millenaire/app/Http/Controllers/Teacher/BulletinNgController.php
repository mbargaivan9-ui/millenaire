<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\BulletinNgConfig;
use App\Models\BulletinNgSubject;
use App\Models\BulletinNgStudent;
use App\Models\BulletinNgNote;
use App\Models\BulletinNgConduite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * BulletinNgController
 *
 * Gère le workflow complet du système de bulletins de dernière génération.
 * Accessible au prof principal (role: prof_principal, admin).
 *
 * Workflow :
 *   ÉTAPE 1 — Choix de la section (FR/EN)            → step1Section()
 *   ÉTAPE 2 — Configuration de la session            → step2Config()
 *   ÉTAPE 3 — Paramétrage des matières               → step3Subjects()
 *   ÉTAPE 4 — Enregistrement des élèves              → step4Students()
 *   ÉTAPE 5 — Ouverture & saisie des notes           → step5Notes()
 *   ÉTAPE 6 — Conduite & comportement                → step6Conduite()
 *   ÉTAPE 7 — Génération & export des bulletins      → step7Generate()
 */
class BulletinNgController extends Controller
{
    /* ═══════════════════════════════════════════════════════
     *  WIZARD — Navigation principale
     * ═══════════════════════════════════════════════════════ */

    /**
     * Point d'entrée : dashboard des sessions du prof principal
     */
    public function index()
    {
        // Note: Table bulletin_ng_configs not created yet
        // Return empty collection for now
        $configs = collect();

        return view('teacher.bulletin_ng.index', compact('configs'));
    }

    /**
     * ÉTAPE 1 — Formulaire de choix de section (FR/EN)
     */
    public function step1Section()
    {
        return view('teacher.bulletin_ng.step1_section');
    }

    /**
     * ÉTAPE 2 — Formulaire de configuration
     */
    public function step2Config(Request $request)
    {
        $langue = $request->query('langue', 'FR');
        $config = null;

        if ($request->query('config_id')) {
            $config = $this->getConfig($request->query('config_id'));
        }

        return view('teacher.bulletin_ng.step2_config', compact('langue', 'config'));
    }

    /**
     ÉTAPE 3 — Formulaire des matières
     */
    public function step3Subjects(string $config)
    {
        \Log::info('🔍 Step3 - Attempting to load config', [
            'configId' => $config,
            'configIdType' => gettype($config),
            'isEmpty' => empty(trim($config)),
            'sessionKey' => 'bulletin_ng_config_' . $config,
            'sessionHasKey' => session()->has('bulletin_ng_config_' . $config),
            'allSessionKeys' => array_filter(array_keys(session()->all()), fn($k) => str_contains($k, 'bulletin_ng')),
        ]);
        
        if (!$config || empty(trim($config))) {
            \Log::error('Step3 - Empty config ID');
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration ID is missing.');
        }

        $config = $this->getConfig($config);
        
        // Redirect if config not found
        if (!$config) {
            \Log::error('Step3 - Configuration not found after getConfig()');
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration not found. Please create a new configuration first.');
        }
        
        \Log::info('✅ Step3 - Config loaded successfully', ['configId' => $config->id]);
        
        // For session-based config, subjects will be empty
        $subjects = collect();
        
        if (property_exists($config, 'subjects') && $config->subjects) {
            // Ensure it's always a Collection, not an array
            $subjects = collect($config->subjects);
        }

        return view('teacher.bulletin_ng.step3_subjects', compact('config', 'subjects'));
    }

    /**
     ÉTAPE 4 — Gestion des élèves
     */
    public function step4Students(string $config)
    {
        $config = $this->getConfig($config);
        
        // Redirect if config not found
        if (!$config) {
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration not found. Please reconfigure.');
        }
        
        $students = collect();
        
        if (property_exists($config, 'students') && $config->students) {
            // Ensure it's always a Collection, not an array
            $students = collect($config->students);
        }

        return view('teacher.bulletin_ng.step4_students', compact('config', 'students'));
    }

    /**
     ÉTAPE 5 — Grille de saisie des notes
     */
    public function step5Notes(string $configId)
    {
        $config = $this->getConfig($configId);
        
        // Redirect if config not found
        if (!$config) {
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration not found. Please reconfigure.');
        }
        
        $subjects = collect();
        $students = collect();
        $notes = collect();
        
        if (property_exists($config, 'subjects') && $config->subjects) {
            // Ensure it's always a Collection of objects with required properties
            $subjects = collect($config->subjects)->map(function($item) {
                return $this->ensureObjectProperties($item, 'subject');
            });
        }
        if (property_exists($config, 'students') && $config->students) {
            // Ensure it's always a Collection of objects with required properties
            $students = collect($config->students)->map(function($item) {
                return $this->ensureObjectProperties($item, 'student');
            });
        }
        
        // Get session data to calculate initial stats and load notes
        $sessionKey = 'bulletin_ng_config_' . $configId;
        $sessionData = session($sessionKey, []);
        
        // Load notes from session data
        $notesArray = $sessionData['notes'] ?? [];
        $notes = collect($notesArray)->mapWithKeys(function($value, $key) {
            return [$key => (object) ['note' => $value]];
        });
        
        // Calculate stats from session data
        $stats = $this->calculateStats($sessionData);

        return view('teacher.bulletin_ng.step5_notes', compact(
            'config', 'subjects', 'students', 'notes', 'stats'
        ));
    }

    /**
     ÉTAPE 6 — Conduite & comportement
     */
    public function step6Conduite(string $config)
    {
        $config = $this->getConfig($config);
        
        // Redirect if config not found
        if (!$config) {
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration not found. Please reconfigure.');
        }
        
        $students = collect();
        $conduites = collect();
        $stats = [];
        
        if (property_exists($config, 'students') && $config->students) {
            // Ensure it's always a Collection, not an array
            $students = collect($config->students);
        }

        return view('teacher.bulletin_ng.step6_conduite', compact(
            'config', 'students', 'conduites', 'stats'
        ));
    }

    /**
     ÉTAPE 7 — Liste des bulletins générés
     */
    public function step7Generate(string $config)
    {
        $config = $this->getConfig($config);
        
        // Redirect if config not found
        if (!$config) {
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration not found. Please reconfigure.');
        }
        
        $subjects = collect();
        $students = collect();
        $stats = [];
        
        if (property_exists($config, 'subjects') && $config->subjects) {
            // Ensure it's always a Collection, not an array
            $subjects = collect($config->subjects);
        }
        if (property_exists($config, 'students') && $config->students) {
            // Ensure it's always a Collection, not an array
            $students = collect($config->students);
            
            // Ensure each student has a conduite property (may be null)
            $students = $students->map(function($student) {
                if (!property_exists($student, 'conduite')) {
                    $student->conduite = null;
                }
                return $student;
            });
        }

        return view('teacher.bulletin_ng.step7_generate', compact(
            'config', 'subjects', 'students', 'stats'
        ));
    }

    /* ═══════════════════════════════════════════════════════
     *  ACTIONS — POST
     * ═══════════════════════════════════════════════════════ */

    /**
     * POST — Créer ou mettre à jour la configuration
     */
    public function storeConfig(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
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
            $logoPath = $request->file('logo')->store('bulletin_ng/logos', 'public');
        }

        // Temporary: Use session to store config data until bulletin_ng_configs table is created
        $configId = $request->input('config_id') ?? uniqid('session_');
        
        // Remove the logo file object from validated data (can't serialize UploadedFile)
        unset($validated['logo']);
        
        // Store in session
        $sessionData = array_merge($validated, [
            'prof_principal_id' => Auth::id(),
            'logo_path'         => $logoPath,
            'statut'            => 'configuration',
            'id'                => $configId,
        ]);
        
        $sessionKey = 'bulletin_ng_config_' . $configId;
        session([$sessionKey => $sessionData]);
        
        // Verify session was saved
        $verifySession = session()->has($sessionKey);
        $retrievedData = $verifySession ? session($sessionKey) : null;
        
        // Log for debugging
        \Log::info('⚡ StoreConfig - Config saved', [
            'configId' => $configId,
            'configIdType' => gettype($configId),
            'sessionKey' => $sessionKey,
            'sessionExists' => $verifySession,
            'retrievedSchoolName' => $retrievedData['school_name'] ?? null,
            'redirectRoute' => route('teacher.bulletin_ng.step3', $configId),
        ]);

        return redirect()->route('teacher.bulletin_ng.step3', $configId)
            ->with('success', 'Configuration enregistrée avec succès.');
    }

    /**
     * POST — Enregistrer les matières
     */
    public function storeSubjects(Request $request, string $config): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'subjects'              => 'required|array|min:1',
            'subjects.*.nom'        => 'required|string|max:150',
            'subjects.*.coefficient'=> 'required|numeric|min:0.5|max:20',
            'subjects.*.nom_prof'   => 'nullable|string|max:150',
        ]);

        // Store subjects in session with IDs
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        // Generate IDs for each subject if they don't exist
        $subjects = $request->input('subjects', []);
        foreach ($subjects as &$subject) {
            if (!isset($subject['id'])) {
                $subject['id'] = uniqid('subject_');
            }
        }
        unset($subject); // unset reference to avoid side effects
        
        $sessionData['subjects'] = $subjects;
        session([$sessionKey => $sessionData]);

        return redirect()->route('teacher.bulletin_ng.step4', $config)
            ->with('success', 'Matières enregistrées.');
    }

    /**
     * POST — Ajouter un élève
     */
    public function storeStudent(Request $request, string $config): JsonResponse
    {
        $validated = $request->validate([
            'matricule'       => 'required|string|max:50',
            'nom'             => 'required|string|max:200',
            'date_naissance'  => 'nullable|date',
            'lieu_naissance'  => 'nullable|string|max:150',
            'sexe'            => 'required|in:M,F',
        ]);

        // Store in session
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        if (!isset($sessionData['students'])) {
            $sessionData['students'] = [];
        }

        $student = array_merge($validated, [
            'id' => uniqid('student_'),
            'ordre' => count($sessionData['students']),
        ]);

        $sessionData['students'][] = $student;
        session([$sessionKey => $sessionData]);

        return response()->json(['success' => true, 'student' => $student]);
    }

    /**
     * DELETE — Supprimer un élève
     */
    public function deleteStudent(string $config, int $studentId): JsonResponse
    {
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        if (isset($sessionData['students'])) {
            $sessionData['students'] = array_filter(
                $sessionData['students'],
                fn($s) => $s['id'] !== "student_$studentId"
            );
            session([$sessionKey => $sessionData]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * POST — Ouvrir la saisie des notes (rendre accessible aux autres profs)
     */
    public function ouvrirSaisie(string $config): JsonResponse
    {
        // For session-based configs, just acknowledge it
        // (no persistent state needed in session)
        return response()->json(['success' => true, 'message' => 'Saisie ouverte.']);
    }

    /**
     * POST — Sauvegarder une note (AJAX temps réel)
     */
    public function saveNote(Request $request, string $config): JsonResponse
    {
        $request->validate([
            'ng_student_id' => 'required|string',
            'ng_subject_id' => 'required|string',
            'note'          => 'nullable|numeric|min:0|max:20',
        ]);

        // Store in session
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        if (!isset($sessionData['notes'])) {
            $sessionData['notes'] = [];
        }

        $noteKey = $request->ng_student_id . '_' . $request->ng_subject_id;
        $sessionData['notes'][$noteKey] = $request->note;
        session([$sessionKey => $sessionData]);

        // Calculate and return updated stats
        $stats = $this->calculateStats($sessionData);

        return response()->json([
            'success' => true,
            'note'    => $request->note,
            'stats'   => $stats,
        ]);
    }

    /**
     * POST — Verrouiller les notes
     */
    public function verrouillerNotes(string $config): JsonResponse
    {
        // For session-based configs, just acknowledge it
        return response()->json(['success' => true]);
    }

    /**
     * POST — Sauvegarder la conduite d'un élève (AJAX)
     */
    public function saveConduite(Request $request, string $config, int $studentId): JsonResponse
    {
        $validated = $request->validate([
            'tableau_honneur'  => 'boolean',
            'encouragement'    => 'boolean',
            'felicitations'    => 'boolean',
            'blame_travail'    => 'boolean',
            'avert_travail'    => 'string',
            'absences_totales' => 'integer|min:0',
            'absences_nj'      => 'integer|min:0',
            'exclusion'        => 'boolean',
            'avert_conduite'   => 'string',
            'blame_conduite'   => 'string',
        ]);

        // Store in session
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        if (!isset($sessionData['conduites'])) {
            $sessionData['conduites'] = [];
        }

        $sessionData['conduites'][$studentId] = $validated;
        session([$sessionKey => $sessionData]);

        return response()->json(['success' => true]);
    }

    /**
     * POST — Finaliser conduite → passer à génération
     */
    public function finaliserConduite(string $config): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('teacher.bulletin_ng.step7', $config)
            ->with('success', 'Conduites enregistrées. Génération des bulletins...');
    }

    /* ═══════════════════════════════════════════════════════
     *  PDF — Génération et export
     * ═══════════════════════════════════════════════════════ */

    /**
     * Génère le PDF du bulletin d'un élève
     */
    public function pdfStudent(string $configId, string $studentId)
    {
        // Get config from session or database
        $config = $this->getConfig($configId);
        if (!$config) {
            abort(404, 'Configuration not found');
        }
        
        // Try database first, fall back to session
        $student = null;
        
        // If config is an object with students array (session-based)
        if (property_exists($config, 'students') && $config->students) {
            $students = collect($config->students);
            $student = $students->firstWhere('id', $studentId);
            
            if ($student) {
                // Ensure student has required properties
                $student = (object) $student;
                // Load conduite if available
                if (!property_exists($student, 'conduite')) {
                    $student->conduite = null;
                }
                // Load notes if available
                if (!property_exists($student, 'notes')) {
                    $student->notes = collect();
                }
            }
        }
        
        // Fall back to database query if session-based lookup failed
        if (!$student) {
            if (is_numeric($configId) && is_numeric($studentId)) {
                $student = BulletinNgStudent::where('config_id', (int)$configId)
                    ->with(['notes.subject', 'conduite'])
                    ->findOrFail((int)$studentId);
            } else {
                abort(404, 'Student not found');
            }
        }

        // Get subjects
        $subjects = collect();
        if (property_exists($config, 'subjects') && $config->subjects) {
            $subjects = collect($config->subjects)->sortBy('ordre');
        } else {
            $subjects = $config->subjects()->orderBy('ordre')->get();
        }
        
        // Compute stats
        $stats = property_exists($config, 'computeClassStats') 
            ? $config->computeClassStats() 
            : [];

        $pdf = Pdf::loadView('teacher.bulletin_ng.pdf.bulletin', [
            'config'   => $config,
            'student'  => $student,
            'subjects' => $subjects,
            'stats'    => $stats,
        ])->setPaper('a4', 'portrait');

        $filename = "bulletin_{$student->matricule}_{$config->trimestre}T_{$config->annee_academique}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Génère un PDF avec tous les bulletins de la classe
     */
    public function pdfAll(int $configId)
    {
        $config   = $this->findConfig($configId);
        $subjects = $config->subjects()->orderBy('ordre')->get();
        $students = $config->students()->orderBy('ordre')
            ->with(['notes.subject', 'conduite'])
            ->get();
        $stats = $config->computeClassStats();

        $pdf = Pdf::loadView('teacher.bulletin_ng.pdf.all', compact(
            'config', 'subjects', 'students', 'stats'
        ))->setPaper('a4', 'portrait');

        $filename = "bulletins_{$config->nom_classe}_{$config->trimestre}T_{$config->annee_academique}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Prévisualisation HTML d'un bulletin individuel
     */
    public function previewStudent(string $configId, string $studentId)
    {
        // Get config from session or database
        $config = $this->getConfig($configId);
        if (!$config) {
            abort(404, 'Configuration not found');
        }
        
        // Try database first, fall back to session
        $student = null;
        
        // If config is an object with students array (session-based)
        if (property_exists($config, 'students') && $config->students) {
            $students = collect($config->students);
            $student = $students->firstWhere('id', $studentId);
            
            if ($student) {
                // Ensure student has required properties
                $student = (object) $student;
                // Load conduite if available
                if (!property_exists($student, 'conduite')) {
                    $student->conduite = null;
                }
                // Load notes if available
                if (!property_exists($student, 'notes')) {
                    $student->notes = collect();
                }
            }
        }
        
        // Fall back to database query if session-based lookup failed
        if (!$student) {
            if (is_numeric($configId) && is_numeric($studentId)) {
                $student = BulletinNgStudent::where('config_id', (int)$configId)
                    ->with(['notes.subject', 'conduite'])
                    ->findOrFail((int)$studentId);
            } else {
                abort(404, 'Student not found');
            }
        }

        // Get subjects
        $subjects = collect();
        if (property_exists($config, 'subjects') && $config->subjects) {
            $subjects = collect($config->subjects)->sortBy('ordre');
        } else {
            $subjects = $config->subjects()->orderBy('ordre')->get();
        }
        
        // Compute stats
        $stats = property_exists($config, 'computeClassStats') 
            ? $config->computeClassStats() 
            : [];

        return view('teacher.bulletin_ng.pdf.bulletin', [
            'config'   => $config,
            'student'  => $student,
            'subjects' => $subjects,
            'stats'    => $stats,
            'preview'  => true,
        ]);
    }

    /* ═══════════════════════════════════════════════════════
     *  API JSON — Pour le JavaScript temps réel
     * ═══════════════════════════════════════════════════════ */

    /**
     * GET — Statistiques classe complètes (JSON)
     */
    public function apiStats(string $config): JsonResponse
    {
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        // Return basic stats from session data
        return response()->json([
            'total_students' => count($sessionData['students'] ?? []),
            'total_subjects' => count($sessionData['subjects'] ?? []),
            'notes_saisies' => count($sessionData['notes'] ?? []),
        ]);
    }

    /**
     * GET — Toutes les notes d'un élève (JSON)
     */
    public function apiStudentNotes(string $config, int $studentId): JsonResponse
    {
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        $notes = [];
        if (isset($sessionData['notes'])) {
            foreach ($sessionData['notes'] as $key => $note) {
                if (strpos($key, $studentId . '_') === 0) {
                    $notes[] = $note;
                }
            }
        }

        return response()->json($notes);
    }

    /* ═══════════════════════════════════════════════════════
     *  Helpers privés
     * ═══════════════════════════════════════════════════════ */

    private function findConfig(int $id): BulletinNgConfig
    {
        return BulletinNgConfig::where('prof_principal_id', Auth::id())
            ->findOrFail($id);
    }

    /**
     * Helper: Get config from session or database
     * Accepts string (session ID like "session_xxxxx") or int (database ID)
     * Returns a session-based object that mimics BulletinNgConfig, or actual BulletinNgConfig model
     */
    private function getConfig($configId)
    {
        // Validate configId
        if (!$configId) {
            \Log::warning('BulletinNgController: getConfig called with empty configId');
            return null;
        }

        // First check session
        $sessionKey = 'bulletin_ng_config_' . $configId;
        if (session()->has($sessionKey)) {
            $data = session($sessionKey);
            
            // Add computed trimestre_label if not present
            if (!isset($data['trimestre_label']) && isset($data['trimestre'])) {
                $data['trimestre_label'] = $this->getTrimesterLabel($data['trimestre']);
            }
            
            return (object) $data;
        }

        // If it's numeric, try database
        if (is_numeric($configId)) {
            try {
                return BulletinNgConfig::where('prof_principal_id', Auth::id())
                    ->findOrFail($configId);
            } catch (\Exception $e) {
                \Log::warning('BulletinNgController: Config not found in DB - configId: ' . $configId);
                return null;
            }
        }

        // Config not found
        \Log::warning('BulletinNgController: Config not found in session or DB - configId: ' . $configId);
        return null;
    }

    /**
     * Helper: Get label for a trimester number
     */
    private function getTrimesterLabel(int $trimester): string
    {
        $labels = [
            1 => 'Trimestre 1 (Sep-Nov)',
            2 => 'Trimestre 2 (Dec-Mar)',
            3 => 'Trimestre 3 (Apr-Jun)',
        ];

        return $labels[$trimester] ?? "Trimestre $trimester";
    }

    /**
     * Helper: Extract numeric config ID or generate session-based reference
     * Used when storing data that needs a 'config_id' field
     */
    private function resolveConfigId($configId): int
    {
        // If it's numeric, return as int
        if (is_numeric($configId)) {
            return (int) $configId;
        }

        // If it's a session ID (starts with 'session_'), generate a hash
        if (strpos($configId, 'session_') === 0) {
            // For session-based configs, we hash to get a pseudo ID
            // This allows us to store references in the database when needed
            return crc32($configId) & 0x7fffffff; // Ensure positive int
        }

        return 0; // Fallback
    }

    /**
     * Helper: Calculate stats from session data
     */
    private function calculateStats(array $sessionData): array
    {
        $notes = $sessionData['notes'] ?? [];
        $students = $sessionData['students'] ?? [];
        $subjects = $sessionData['subjects'] ?? [];

        \Log::info('📊 CalculateStats invoked', [
            'notesCount' => count($notes),
            'studentsCount' => count($students),
            'subjectsCount' => count($subjects),
            'firstSubject' => isset($subjects[0]) ? (is_array($subjects[0]) ? $subjects[0] : (array) $subjects[0]) : null,
            'firstStudent' => isset($students[0]) ? (is_array($students[0]) ? $students[0] : (array) $students[0]) : null,
            'firstNote' => $notes ? array_slice($notes, 0, 1) : null,
        ]);

        // Default empty stats if no data
        if (empty($students) || empty($subjects) || empty($notes)) {
            \Log::warning('⚠️ CalculateStats: Missing data', [
                'studentsEmpty' => empty($students),
                'subjectsEmpty' => empty($subjects),
                'notesEmpty' => empty($notes),
            ]);
            return [
                'avg'      => 0,
                'pct'      => 0,
                'max'      => 0,
                'min'      => 0,
                'passing'  => 0,
                'avgs'     => [],
                'ranks'    => [],
            ];
        }

        $studentAverages = [];
        $allNotes = [];

        // Calculate average for each student
        foreach ($students as $student) {
            $studentId = is_array($student) ? ($student['id'] ?? null) : ($student?->id ?? null);
            if (!$studentId) continue;

            $totalCoef = 0;
            $totalWeighted = 0;
            $hasNotes = false;

            foreach ($subjects as $subject) {
                $subjectId = is_array($subject) ? ($subject['id'] ?? null) : ($subject?->id ?? null);
                $coef = is_array($subject) ? ($subject['coefficient'] ?? 1) : ($subject?->coefficient ?? 1);
                
                if (!$subjectId) continue;

                $noteKey = "{$studentId}_{$subjectId}";
                $note = $notes[$noteKey] ?? null;

                if ($note !== null && $note !== '') {
                    $note = (float) $note;
                    $allNotes[] = $note;
                    $totalCoef += $coef;
                    $totalWeighted += $note * $coef;
                    $hasNotes = true;
                }
            }

            // Calculate weighted average
            if ($hasNotes && $totalCoef > 0) {
                $avg = $totalWeighted / $totalCoef;
                $studentAverages[$studentId] = round($avg, 2);
            } else {
                $studentAverages[$studentId] = 0;
            }
        }

        // Calculate rank for each student
        $rankedAverages = collect($studentAverages)
            ->filter(fn($avg) => $avg > 0)
            ->sortDesc()
            ->keys()
            ->toArray();

        $ranks = [];
        foreach ($rankedAverages as $rank => $studentId) {
            $ranks[$studentId] = $rank + 1;
        }

        // Calculate class-wide stats
        $classAvg = count($allNotes) > 0 ? array_sum($allNotes) / count($allNotes) : 0;
        $classMax = count($allNotes) > 0 ? max($allNotes) : 0;
        $classMin = count($allNotes) > 0 ? min($allNotes) : 0;
        $passing = count(array_filter($studentAverages, fn($avg) => $avg >= 10));
        $totalStudents = max(1, count($students));
        $pct = $totalStudents > 0 ? round(($passing / $totalStudents) * 100) : 0;

        return [
            'avg'      => round($classAvg, 2),
            'pct'      => (int)$pct,
            'max'      => round($classMax, 2),
            'min'      => round($classMin, 2),
            'passing'  => $passing,
            'avgs'     => $studentAverages,
            'ranks'    => $ranks,
        ];
    }

    /**
     * Helper: Ensure object has all required properties with defaults
     */
    private function ensureObjectProperties($item, $type = 'subject'): object
    {
        $obj = is_array($item) ? (object) $item : $item;
        
        if ($type === 'subject') {
            // Required subject properties
            if (!isset($obj->id)) $obj->id = uniqid('subject_');
            if (!isset($obj->nom)) $obj->nom = 'N/A';
            if (!isset($obj->coefficient)) $obj->coefficient = 1;
            if (!isset($obj->nom_prof)) $obj->nom_prof = null;
        } elseif ($type === 'student') {
            // Required student properties
            if (!isset($obj->id)) $obj->id = uniqid('student_');
            if (!isset($obj->nom)) $obj->nom = 'N/A';
            if (!isset($obj->matricule)) $obj->matricule = 'N/A';
        }
        
        return $obj;
    }
}
