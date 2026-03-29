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
        $students = collect();
        $subjects = collect();
        
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
            // Ensure it's always a Collection of objects with required properties
            $students = collect($config->students)->map(function($item) {
                return $this->ensureObjectProperties($item, 'student');
            });
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
        
        // Get session data directly - this is the source of truth
        $sessionKey = 'bulletin_ng_config_' . $configId;
        $sessionData = session($sessionKey, []);
        
        \Log::info('📝 Step5Notes - Loading from session', [
            'configId' => $configId,
            'sessionKey' => $sessionKey,
            'hasSubjects' => isset($sessionData['subjects']),
            'subjectsCount' => isset($sessionData['subjects']) ? count($sessionData['subjects']) : 0,
            'hasStudents' => isset($sessionData['students']),
            'studentsCount' => isset($sessionData['students']) ? count($sessionData['students']) : 0,
            'firstSubjectData' => isset($sessionData['subjects'][0]) ? $sessionData['subjects'][0] : null,
            'firstStudentData' => isset($sessionData['students'][0]) ? $sessionData['students'][0] : null,
        ]);
        
        // Load subjects directly from session with IDs preserved
        $subjects = collect();
        $subjectsArray = $sessionData['subjects'] ?? [];
        if (!empty($subjectsArray)) {
            $subjects = collect($subjectsArray)->map(function($item) {
                // Item is already an array from session with all properties including ID
                // Convert to object, preserving all properties including ID
                $obj = is_array($item) ? (object) $item : $item;
                // Ensure it has essential properties, but DON'T override existing ID
                if (!isset($obj->id)) {
                    \Log::error('❌ Subject missing ID in session!', ['item' => $item]);
                    $obj->id = uniqid('subject_');
                }
                if (!isset($obj->nom)) $obj->nom = 'N/A';
                if (!isset($obj->coefficient)) $obj->coefficient = 1;
                if (!isset($obj->nom_prof)) $obj->nom_prof = null;
                return $obj;
            });
        }
        
        // Load students directly from session with IDs preserved
        $students = collect();
        $studentsArray = $sessionData['students'] ?? [];
        if (!empty($studentsArray)) {
            $students = collect($studentsArray)->map(function($item) {
                // Item is already an array from session with all properties including ID
                // Convert to object, preserving all properties including ID
                $obj = is_array($item) ? (object) $item : $item;
                // Ensure it has essential properties, but DON'T override existing ID
                if (!isset($obj->id)) {
                    \Log::error('❌ Student missing ID in session!', ['item' => $item]);
                    $obj->id = uniqid('student_');
                }
                if (!isset($obj->nom)) $obj->nom = 'N/A';
                if (!isset($obj->matricule)) $obj->matricule = 'N/A';
                return $obj;
            });
        }
        
        // Load notes from session data
        $notesArray = $sessionData['notes'] ?? [];
        $notes = collect($notesArray)->mapWithKeys(function($value, $key) {
            return [$key => (object) ['note' => $value]];
        });
        
        \Log::info('📊 Step5Notes - Data loaded', [
            'subjectsCount' => $subjects->count(),
            'studentsCount' => $students->count(),
            'notesCount' => $notes->count(),
        ]);
        
        // Calculate stats from session data
        $stats = $this->calculateStats($sessionData);

        return view('teacher.bulletin_ng.step5_notes', compact(
            'config', 'subjects', 'students', 'notes', 'stats'
        ));
    }

    /**
     ÉTAPE 6 — Conduite & comportement
     */
    public function step6Conduite(string $configId)
    {
        $config = $this->getConfig($configId);
        
        // Redirect if config not found
        if (!$config) {
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration not found. Please reconfigure.');
        }
        
        // Get session data directly - MUST USE SAME SOURCE AS STEP5!
        $sessionKey = 'bulletin_ng_config_' . $configId;
        $sessionData = session($sessionKey, []);
        
        \Log::info('📝 Step6Conduite - Loading from session', [
            'configId' => $configId,
            'sessionKey' => $sessionKey,
            'hasStudents' => isset($sessionData['students']),
            'studentsCount' => isset($sessionData['students']) ? count($sessionData['students']) : 0,
            'firstStudentData' => isset($sessionData['students'][0]) ? $sessionData['students'][0] : null,
        ]);
        
        // Load students directly from session with IDs preserved (same as step5)
        $students = collect();
        $studentsArray = $sessionData['students'] ?? [];
        if (!empty($studentsArray)) {
            $students = collect($studentsArray)->map(function($item) {
                // Item is already an array from session with all properties including ID
                $obj = is_array($item) ? (object) $item : $item;
                // Ensure it has essential properties, but DON'T override existing ID
                if (!isset($obj->id)) {
                    \Log::error('❌ Student missing ID in session!', ['item' => $item]);
                    $obj->id = uniqid('student_');
                }
                if (!isset($obj->nom)) $obj->nom = 'N/A';
                if (!isset($obj->matricule)) $obj->matricule = 'N/A';
                return $obj;
            });
        }
        
        // Load conduites from session
        $conductesArray = $sessionData['conduites'] ?? [];
        $conduites = collect($conductesArray)->mapWithKeys(function($value, $key) {
            return [$key => (object) $value];
        });
        
        // Calculate stats from session data (using Step 5's notes/subjects)
        $stats = $this->calculateStats($sessionData);

        return view('teacher.bulletin_ng.step6_conduite', compact(
            'config', 'students', 'conduites', 'stats'
        ));
    }

    /**
     ÉTAPE 7 — Liste des bulletins générés
     */
    public function step7Generate(string $configId)
    {
        $config = $this->getConfig($configId);
        
        // Redirect if config not found
        if (!$config) {
            return redirect()->route('teacher.bulletin_ng.step2')
                ->with('error', 'Configuration not found. Please reconfigure.');
        }
        
        // Get session data directly - MUST USE SAME SOURCE AS STEP5!
        $sessionKey = 'bulletin_ng_config_' . $configId;
        $sessionData = session($sessionKey, []);
        
        \Log::info('📝 Step7Generate - Loading from session', [
            'configId' => $configId,
            'sessionKey' => $sessionKey,
            'hasSubjects' => isset($sessionData['subjects']),
            'subjectsCount' => isset($sessionData['subjects']) ? count($sessionData['subjects']) : 0,
            'hasStudents' => isset($sessionData['students']),
            'studentsCount' => isset($sessionData['students']) ? count($sessionData['students']) : 0,
        ]);
        
        // Load subjects directly from session with IDs preserved (same as step5)
        $subjects = collect();
        $subjectsArray = $sessionData['subjects'] ?? [];
        if (!empty($subjectsArray)) {
            $subjects = collect($subjectsArray)->map(function($item) {
                $obj = is_array($item) ? (object) $item : $item;
                if (!isset($obj->id)) {
                    \Log::error('❌ Subject missing ID in session!', ['item' => $item]);
                    $obj->id = uniqid('subject_');
                }
                if (!isset($obj->nom)) $obj->nom = 'N/A';
                if (!isset($obj->coefficient)) $obj->coefficient = 1;
                if (!isset($obj->nom_prof)) $obj->nom_prof = null;
                return $obj;
            });
        }
        
        // Load students directly from session with IDs preserved (same as step5)
        $students = collect();
        $studentsArray = $sessionData['students'] ?? [];
        
        // Load conduites from session (same as step6)
        $conductesArray = $sessionData['conduites'] ?? [];
        $conduites = collect($conductesArray)->mapWithKeys(function($value, $key) {
            return [$key => (object) $value];
        });
        
        if (!empty($studentsArray)) {
            $students = collect($studentsArray)->map(function($item) use ($conduites) {
                $obj = is_array($item) ? (object) $item : $item;
                if (!isset($obj->id)) {
                    \Log::error('❌ Student missing ID in session!', ['item' => $item]);
                    $obj->id = uniqid('student_');
                }
                if (!isset($obj->nom)) $obj->nom = 'N/A';
                if (!isset($obj->matricule)) $obj->matricule = 'N/A';
                
                // Attach conduite if it exists for this student
                $obj->conduite = $conduites->get($obj->id) ?? null;
                
                return $obj;
            });
        }
        
        // Calculate stats from session data (using Step 5's notes/subjects)
        $stats = $this->calculateStats($sessionData);

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
        \Log::info('📝 StoreStudent - Attempting to add student', [
            'configId' => $config,
            'requestData' => $request->all(),
            'sessionKey' => 'bulletin_ng_config_' . $config,
            'sessionExists' => session()->has('bulletin_ng_config_' . $config),
        ]);
        
        try {
            $validated = $request->validate([
                'matricule'       => 'required|string|max:50',
                'nom'             => 'required|string|max:200',
                'date_naissance'  => 'nullable|date',
                'lieu_naissance'  => 'nullable|string|max:150',
                'sexe'            => 'required|in:M,F',
            ]);

            \Log::info('✅ Validation passed', ['validated' => $validated]);

            // Store in session
            $sessionKey = 'bulletin_ng_config_' . $config;
            $sessionData = session($sessionKey, []);
            
            \Log::info('📦 Current session data', [
                'hasStudents' => isset($sessionData['students']),
                'studentCount' => isset($sessionData['students']) ? count($sessionData['students']) : 0,
            ]);
            
            if (!isset($sessionData['students'])) {
                $sessionData['students'] = [];
            }

            $student = array_merge($validated, [
                'id' => uniqid('student_'),
                'ordre' => count($sessionData['students']),
            ]);

            $sessionData['students'][] = $student;
            session([$sessionKey => $sessionData]);
            
            \Log::info('✅ Student added successfully', [
                'studentId' => $student['id'],
                'newCount' => count($sessionData['students']),
            ]);

            return response()->json(['success' => true, 'student' => $student]);
            
        } catch (\Exception $e) {
            \Log::error('❌ Error adding student', [
                'error' => $e->getMessage(),
                'configId' => $config,
            ]);
            throw $e;
        }
    }

    /**
     * DELETE — Supprimer un élève
     */
    public function deleteStudent(string $config, string $studentId): JsonResponse
    {
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        if (isset($sessionData['students'])) {
            $sessionData['students'] = array_filter(
                $sessionData['students'],
                fn($s) => $s['id'] !== $studentId
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
        $validated = $request->validate([
            'ng_student_id' => 'required|string',
            'ng_subject_id' => 'required|string',
            'note'          => 'nullable|numeric|min:0|max:20',
        ]);

        // Store in session
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        \Log::info('💾 SaveNote DÉBUT', [
            'configId' => $config,
            'sessionKey' => $sessionKey,
            'sessionExists' => session()->has($sessionKey),
            'receivedStudentId' => $validated['ng_student_id'],
            'receivedSubjectId' => $validated['ng_subject_id'],
            'receivedNote' => $validated['note'],
        ]);

        if (!isset($sessionData['notes'])) {
            $sessionData['notes'] = [];
        }

        // Verify IDs exist in session
        $students = $sessionData['students'] ?? [];
        $subjects = $sessionData['subjects'] ?? [];
        
        $studentExists = false;
        $subjectExists = false;
        
        foreach ($students as $s) {
            $sid = is_array($s) ? ($s['id'] ?? null) : ($s?->id ?? null);
            if ($sid === $validated['ng_student_id']) {
                $studentExists = true;
                break;
            }
        }
        
        foreach ($subjects as $s) {
            $subid = is_array($s) ? ($s['id'] ?? null) : ($s?->id ?? null);
            if ($subid === $validated['ng_subject_id']) {
                $subjectExists = true;
                break;
            }
        }
        
        \Log::info('💾 SaveNote - Vérification IDs', [
            'studentCount' => count($students),
            'subjectCount' => count($subjects),
            'studentExists' => $studentExists,
            'subjectExists' => $subjectExists,
            'studentIds' => array_map(fn($s) => is_array($s) ? ($s['id'] ?? 'NO_ID') : ($s?->id ?? 'NO_ID'), 
                                      array_slice($students, 0, 2)),
            'subjectIds' => array_map(fn($s) => is_array($s) ? ($s['id'] ?? 'NO_ID') : ($s?->id ?? 'NO_ID'), 
                                      $subjects),
        ]);

        $noteKey = $validated['ng_student_id'] . '_' . $validated['ng_subject_id'];
        $sessionData['notes'][$noteKey] = $validated['note'];
        session([$sessionKey => $sessionData]);
        
        \Log::info('💾 SaveNote - Note stockée', [
            'noteKey' => $noteKey,
            'noteValue' => $validated['note'],
            'totalNotes' => count($sessionData['notes']),
        ]);

        // Calculate and return updated stats
        $stats = $this->calculateStats($sessionData);

        \Log::info('💾 SaveNote STATS RETOURNÉES', [
            'statsAvg' => $stats['avg'] ?? null,
            'statsCount' => count($stats['avgs'] ?? []),
            'statsRankCount' => count($stats['ranks'] ?? []),
            'firstThreeAvgs' => array_slice($stats['avgs'] ?? [], 0, 3),
        ]);

        return response()->json([
            'success' => true,
            'note'    => $validated['note'],
            'stats'   => $stats,
        ]);
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

        // Default empty stats if no students or subjects
        if (empty($students) || empty($subjects)) {
            \Log::warning('⚠️ CalculateStats: Missing students or subjects', [
                'studentsEmpty' => empty($students),
                'subjectsEmpty' => empty($subjects),
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
            if (!$studentId) {
                \Log::warning('⚠️ CalculateStats: Student missing ID', ['student' => $student]);
                continue;
            }

            $totalCoef = 0;
            $totalWeighted = 0;
            $hasNotes = false;
            $studentNotesDebug = [];

            foreach ($subjects as $subject) {
                $subjectId = is_array($subject) ? ($subject['id'] ?? null) : ($subject?->id ?? null);
                $coef = is_array($subject) ? ($subject['coefficient'] ?? 1) : ($subject?->coefficient ?? 1);
                
                if (!$subjectId) {
                    \Log::warning('⚠️ CalculateStats: Subject missing ID', ['subject' => $subject]);
                    continue;
                }

                $noteKey = "{$studentId}_{$subjectId}";
                $note = $notes[$noteKey] ?? null;
                $studentNotesDebug[$noteKey] = $note;

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
                \Log::info('📈 Student average calculated', [
                    'studentId' => $studentId,
                    'avg' => round($avg, 2),
                    'totalCoef' => $totalCoef,
                    'totalWeighted' => $totalWeighted,
                    'notes' => $studentNotesDebug,
                ]);
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

        \Log::info('📊 CalculateStats final result', [
            'classAvg' => round($classAvg, 2),
            'classMax' => round($classMax, 2),
            'classMin' => round($classMin, 2),
            'passing' => $passing,
            'totalStudents' => $totalStudents,
            'pct' => (int)$pct,
            'allNotesCount' => count($allNotes),
        ]);

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
     * POST — Verrouiller les notes
     */
    public function verrouillerNotes(string $config): JsonResponse
    {
        // Update session to lock notes
        $sessionKey = 'bulletin_ng_config_' . $config;
        $sessionData = session($sessionKey, []);
        
        \Log::info('🔒 Locking notes for config', [
            'configId' => $config,
            'sessionKey' => $sessionKey,
            'currentNotesVerrouillee' => $sessionData['notes_verrouillee'] ?? false,
        ]);
        
        // Set the lock flag in session
        $sessionData['notes_verrouillee'] = true;
        session([$sessionKey => $sessionData]);
        
        // Also update the database config if it exists
        try {
            $configModel = BulletinNgConfig::find($config);
            if ($configModel) {
                $configModel->notes_verrouillee = true;
                $configModel->notes_verrouillee_at = now();
                $configModel->save();
                \Log::info('🔒 Database config updated', [
                    'configId' => $config,
                    'notes_verrouillee' => $configModel->notes_verrouillee,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('⚠️ Could not update database config', [
                'error' => $e->getMessage(),
            ]);
        }
        
        \Log::info('🔒 Notes locked successfully', [
            'newNotesVerrouillee' => $sessionData['notes_verrouillee'],
            'sessionUpdated' => session()->has($sessionKey),
        ]);
        
        return response()->json(['success' => true]);
    }

    /**
     * POST — Sauvegarder la conduite d'un élève (AJAX)
     */
    public function saveConduite(Request $request, string $config, string $studentId): JsonResponse
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
        
        // Ensure profPrincipal is set
        if (!isset($config->profPrincipal) || empty($config->profPrincipal)) {
            $user = Auth::user();
            $config->profPrincipal = (object)['name' => $user?->name ?? 'N/A'];
        }
        $sessionKey = 'bulletin_ng_config_' . $configId;
        $sessionData = session($sessionKey, []);
        
        // Find student in session data
        $studentsArray = $sessionData['students'] ?? [];
        $studentData = null;
        foreach ($studentsArray as $item) {
            $obj = is_array($item) ? (object) $item : $item;
            if (isset($obj->id) && $obj->id === $studentId) {
                $studentData = $obj;
                break;
            }
        }
        
        if (!$studentData) {
            abort(404, 'Student not found');
        }
        
        // Ensure all student properties exist with defaults
        if (!isset($studentData->nom)) $studentData->nom = 'N/A';
        if (!isset($studentData->matricule)) $studentData->matricule = '';
        if (!isset($studentData->sexe)) $studentData->sexe = '';
        if (!isset($studentData->date_naissance)) $studentData->date_naissance = null;
        if (!isset($studentData->lieu_naissance)) $studentData->lieu_naissance = '';
        $subjectsArray = $sessionData['subjects'] ?? [];
        $subjects = collect($subjectsArray)->map(function($item) {
            $obj = is_array($item) ? (object) $item : $item;
            if (!isset($obj->id)) $obj->id = '';
            if (!isset($obj->nom)) $obj->nom = 'N/A';
            if (!isset($obj->coefficient)) $obj->coefficient = 1;
            if (!isset($obj->ordre)) $obj->ordre = 0;
            return $obj;
        })->sortBy('ordre');
        
        // Get notes for this student
        $notesArray = $sessionData['notes'] ?? [];
        $studentNotes = [];
        foreach ($notesArray as $key => $note) {
            if (strpos($key, $studentId . '_') === 0) {
                // Ensure note is converted to object
                if (is_array($note)) {
                    $noteObj = (object) $note;
                } elseif (is_object($note)) {
                    $noteObj = $note;
                } else {
                    // Skip scalar values
                    continue;
                }
                // Ensure ng_subject_id is set
                if (!isset($noteObj->ng_subject_id)) {
                    $noteObj->ng_subject_id = $noteObj->subject_id ?? '';
                }
                $studentNotes[] = $noteObj;
            }
        }
        $studentData->notes = collect($studentNotes);
        
        // Get conduite data and initialize with proper defaults
        $conductesArray = $sessionData['conduites'] ?? [];
        if (isset($conductesArray[$studentId])) {
            $conduite = (object) $conductesArray[$studentId];
        } else {
            $conduite = (object) [];
        }
        // Initialize all conduct properties with proper defaults
        if (!isset($conduite->tableau_honneur)) $conduite->tableau_honneur = false;
        if (!isset($conduite->encouragement)) $conduite->encouragement = false;
        if (!isset($conduite->felicitations)) $conduite->felicitations = false;
        if (!isset($conduite->blame_travail)) $conduite->blame_travail = false;
        if (!isset($conduite->avert_travail)) $conduite->avert_travail = 'Non';
        if (!isset($conduite->absences_totales)) $conduite->absences_totales = 0;
        if (!isset($conduite->absences_nj)) $conduite->absences_nj = 0;
        if (!isset($conduite->exclusion)) $conduite->exclusion = false;
        if (!isset($conduite->avert_conduite)) $conduite->avert_conduite = 'Non';
        if (!isset($conduite->blame_conduite)) $conduite->blame_conduite = 'Non';
        $studentData->conduite = $conduite;
        
        // Compute stats
        $stats = $this->calculateStats($sessionData);

        $pdf = Pdf::loadView('teacher.bulletin_ng.pdf.bulletin', [
            'config'   => $config,
            'student'  => $studentData,
            'subjects' => $subjects,
            'stats'    => $stats,
        ])->setPaper('a4', 'portrait');

        $filename = "bulletin_{$studentData->matricule}_{$config->trimestre}T_{$config->annee_academique}.pdf";
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
        
        // Ensure profPrincipal is set
        if (!isset($config->profPrincipal) || empty($config->profPrincipal)) {
            $user = Auth::user();
            $config->profPrincipal = $user ? $user->nom : 'N/A';
        }
        
        // Get session data
        $sessionKey = 'bulletin_ng_config_' . $configId;
        $sessionData = session($sessionKey, []);
        
        // Find student in session data
        $studentsArray = $sessionData['students'] ?? [];
        $studentData = null;
        foreach ($studentsArray as $item) {
            $obj = is_array($item) ? (object) $item : $item;
            if (isset($obj->id) && $obj->id === $studentId) {
                $studentData = $obj;
                break;
            }
        }
        
        if (!$studentData) {
            abort(404, 'Student not found');
        }
        
        // Ensure all student properties exist with defaults
        if (!isset($studentData->nom)) $studentData->nom = 'N/A';
        if (!isset($studentData->matricule)) $studentData->matricule = '';
        if (!isset($studentData->sexe)) $studentData->sexe = '';
        if (!isset($studentData->date_naissance)) $studentData->date_naissance = null;
        if (!isset($studentData->lieu_naissance)) $studentData->lieu_naissance = '';
        
        // Get subjects from session
        $subjectsArray = $sessionData['subjects'] ?? [];
        $subjects = collect($subjectsArray)->map(function($item) {
            $obj = is_array($item) ? (object) $item : $item;
            // Ensure all required properties exist
            if (!isset($obj->id)) $obj->id = '';
            if (!isset($obj->nom)) $obj->nom = 'N/A';
            if (!isset($obj->coefficient)) $obj->coefficient = 1;
            if (!isset($obj->ordre)) $obj->ordre = 0;
            return $obj;
        })->sortBy('ordre');
        
        // Get notes for this student
        $notesArray = $sessionData['notes'] ?? [];
        $studentNotes = [];
        foreach ($notesArray as $key => $note) {
            if (strpos($key, $studentId . '_') === 0) {
                // Ensure note is converted to object
                if (is_array($note)) {
                    $noteObj = (object) $note;
                } elseif (is_object($note)) {
                    $noteObj = $note;
                } else {
                    // Skip scalar values
                    continue;
                }
                // Ensure ng_subject_id is set properly
                if (!isset($noteObj->ng_subject_id)) {
                    $noteObj->ng_subject_id = $noteObj->subject_id ?? '';
                }
                $studentNotes[] = $noteObj;
            }
        }
        $studentData->notes = collect($studentNotes);
        
        // Get conduite for this student
        $conductesArray = $sessionData['conduites'] ?? [];
        if (isset($conductesArray[$studentId])) {
            $conduite = (object) $conductesArray[$studentId];
        } else {
            $conduite = (object) [];
        }
        // Initialize all conduct properties with proper defaults
        if (!isset($conduite->tableau_honneur)) $conduite->tableau_honneur = false;
        if (!isset($conduite->encouragement)) $conduite->encouragement = false;
        if (!isset($conduite->felicitations)) $conduite->felicitations = false;
        if (!isset($conduite->blame_travail)) $conduite->blame_travail = false;
        if (!isset($conduite->avert_travail)) $conduite->avert_travail = 'Non';
        if (!isset($conduite->absences_totales)) $conduite->absences_totales = 0;
        if (!isset($conduite->absences_nj)) $conduite->absences_nj = 0;
        if (!isset($conduite->exclusion)) $conduite->exclusion = false;
        if (!isset($conduite->avert_conduite)) $conduite->avert_conduite = 'Non';
        if (!isset($conduite->blame_conduite)) $conduite->blame_conduite = 'Non';
        $studentData->conduite = $conduite;
        
        // Compute stats
        $stats = $this->calculateStats($sessionData);

        return view('teacher.bulletin_ng.pdf.bulletin', [
            'config'   => $config,
            'student'  => $studentData,
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
    public function apiStudentNotes(string $config, string $studentId): JsonResponse
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
            
            // Ensure notes_verrouillee property exists with default value
            if (!isset($data['notes_verrouillee'])) {
                $data['notes_verrouillee'] = false;
            }
            
            return (object) $data;
        }

        // If it's numeric, try database
        if (is_numeric($configId)) {
            try {
                $config = BulletinNgConfig::where('prof_principal_id', Auth::id())
                    ->findOrFail($configId);
                
                // Ensure notes_verrouillee property exists with default value
                if (!isset($config->notes_verrouillee)) {
                    $config->notes_verrouillee = false;
                }
                
                return $config;
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

    /**
     * DEBUG — Endpoint pour diagnostiquer les problèmes en temps réel
     */
    public function debugStep5(string $configId): JsonResponse
    {
        $sessionKey = 'bulletin_ng_config_' . $configId;
        $sessionData = session($sessionKey, []);

        $students = $sessionData['students'] ?? [];
        $subjects = $sessionData['subjects'] ?? [];
        $notes = $sessionData['notes'] ?? [];

        // Vérifier la structure
        $studentIds = [];
        $subjectIds = [];
        $noteKeys = [];

        foreach ($students as $s) {
            $id = is_array($s) ? ($s['id'] ?? 'NO_ID') : ($s?->id ?? 'NO_ID');
            $studentIds[] = $id;
        }

        foreach ($subjects as $s) {
            $id = is_array($s) ? ($s['id'] ?? 'NO_ID') : ($s?->id ?? 'NO_ID');
            $subjectIds[] = $id;
        }

        $noteKeys = array_keys($notes);

        // Simuler un calcul
        $stats = $this->calculateStats($sessionData);

        return response()->json([
            'debug' => [
                'sessionKey' => $sessionKey,
                'sessionExists' => !empty($sessionData),
                'studentCount' => count($students),
                'subjectCount' => count($subjects),
                'noteCount' => count($notes),
                'studentIds' => $studentIds,
                'subjectIds' => $subjectIds,
                'noteKeys' => array_slice($noteKeys, 0, 5),
                'totalNoteKeys' => count($noteKeys),
            ],
            'stats' => $stats,
            'rawSession' => [
                'firstStudent' => isset($students[0]) ? (array) $students[0] : null,
                'firstSubject' => isset($subjects[0]) ? (array) $subjects[0] : null,
                'firstNote' => isset($noteKeys[0]) ? [$noteKeys[0] => $notes[$noteKeys[0]]] : null,
            ]
        ]);
    }
}
