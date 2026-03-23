<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentAbsence;
use App\Models\Classe;
use App\Models\Teacher;
use App\Models\ClassSubjectTeacher;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StudentAbsenceController extends Controller
{
    /**
     * Liste des absences des élèves des classes enseignées
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        if ($classIds->isEmpty()) {
            return view('teacher.student_absences.index', [
                'absences' => collect(),
                'students' => collect(),
                'classes' => collect(),
                'stats' => [],
            ]);
        }

        $query = StudentAbsence::with('student.user', 'classe')
            ->whereIn('classe_id', $classIds)
            ->orderByDesc('date');

        // Filtrer par classe
        if ($request->filled('class_id')) {
            $query->where('classe_id', $request->class_id);
        }

        // Filtrer par élève
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par date
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date);
            $query->whereDate('date', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date);
            $query->whereDate('date', '<=', $endDate);
        }

        // Filtrer par justification
        if ($request->filled('justification_status')) {
            if ($request->justification_status === 'justified') {
                $query->whereNotNull('justification_reason');
            } elseif ($request->justification_status === 'unjustified') {
                $query->whereNull('justification_reason');
            }
        }

        $absences = $query->paginate(50);
        
        // Récupérer les classes et étudiants pour les filtres
        $classes = Classe::whereIn('id', $classIds)->get();
        $students = collect();
        
        if ($request->filled('class_id')) {
            $students = Classe::find($request->class_id)->students()->with('user')->get();
        }

        // Statistiques
        $stats = [
            'total_records' => StudentAbsence::whereIn('classe_id', $classIds)->count(),
            'absences' => StudentAbsence::whereIn('classe_id', $classIds)->where('status', 'absent')->count(),
            'late' => StudentAbsence::whereIn('classe_id', $classIds)->where('status', 'late')->count(),
            'justified' => StudentAbsence::whereIn('classe_id', $classIds)->whereNotNull('justification_reason')->count(),
            'unjustified' => StudentAbsence::whereIn('classe_id', $classIds)->whereNull('justification_reason')->count(),
        ];

        return view('teacher.student_absences.index', compact('absences', 'students', 'classes', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $classes = Classe::whereIn('id', $classIds)->get();
        $students = collect();

        if ($request->filled('class_id')) {
            $students = Classe::find($request->class_id)->students()->with('user')->get();
        }
        
        return view('teacher.student_absences.create', compact('classes', 'students'));
    }

    /**
     * Enregistrer une absence
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $validated = $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'justification_reason' => 'nullable|string|max:500',
            'justification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Vérifier que la classe est enseignée par ce professeur
        abort_if(!$classIds->contains($validated['classe_id']), 403);

        // Vérifier que l'élève apartient à la classe
        DB::table('classe_students')
            ->where('classe_id', $validated['classe_id'])
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();

        // Traiter le fichier de justification
        if ($request->hasFile('justification_document')) {
            $path = $request->file('justification_document')
                ->store('student_absences', 'public');
            $validated['justification_document'] = $path;
        }

        $validated['recorded_by'] = $user->name;
        $validated['recorded_at'] = now();

        StudentAbsence::create($validated);

        return redirect()->route('teacher.student-absences.index')
            ->with('success', __('absences.student_absence_recorded'));
    }

    /**
     * Afficher le détail d'une absence
     */
    public function show($absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $absence = StudentAbsence::with('student.user', 'classe')
            ->whereIn('classe_id', $classIds)
            ->findOrFail($absence);

        return view('teacher.student_absences.show', compact('absence'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $absence = StudentAbsence::with('student.user', 'classe')
            ->whereIn('classe_id', $classIds)
            ->findOrFail($absence);

        return view('teacher.student_absences.edit', compact('absence'));
    }

    /**
     * Mettre à jour une absence
     */
    public function update(Request $request, $absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $absence = StudentAbsence::whereIn('classe_id', $classIds)->findOrFail($absence);

        $validated = $request->validate([
            'status' => 'required|in:present,absent,late',
            'justification_reason' => 'nullable|string|max:500',
            'justification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Traiter le fichier de justification
        if ($request->hasFile('justification_document')) {
            $path = $request->file('justification_document')
                ->store('student_absences', 'public');
            $validated['justification_document'] = $path;
        }

        $absence->update($validated);

        return redirect()->route('teacher.student-absences.show', $absence->id)
            ->with('success', __('absences.absence_updated'));
    }

    /**
     * Supprimer une absence
     */
    public function destroy($absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $absence = StudentAbsence::whereIn('classe_id', $classIds)->findOrFail($absence);
        $absence->delete();

        return redirect()->route('teacher.student-absences.index')
            ->with('success', __('absences.absence_deleted'));
    }

    /**
     * Générer un rapport d'absences
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $query = StudentAbsence::with('student.user', 'classe')
            ->whereIn('classe_id', $classIds);

        // Filtres
        if ($request->filled('class_id')) {
            $query->where('classe_id', $request->class_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $absences = $query->orderBy('date')->get();

        // Statistiques par élève
        $studentStats = DB::table('student_absences')
            ->whereIn('classe_id', $classIds)
            ->groupBy('student_id')
            ->select('student_id', 
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_count'),
                DB::raw('SUM(CASE WHEN justification_reason IS NOT NULL THEN 1 ELSE 0 END) as justified_count')
            )
            ->get();

        if ($request->format === 'pdf') {
            return $this->generatePdfReport($absences, $studentStats);
        }

        $classes = Classe::whereIn('id', $classIds)->get();

        return view('teacher.student_absences.report', compact('absences', 'classes', 'studentStats'));
    }

    /**
     * Enregistrement en masse (formulaire)
     */
    public function bulkCreateForm()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $classes = Classe::whereIn('id', $classIds)->get();

        return view('teacher.student_absences.bulk_create', compact('classes'));
    }

    /**
     * Enregistrement en masse (traitement)
     */
    public function bulkCreate(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer les classes que le professeur enseigne
        $classIds = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('class_id');

        // Ajouter la classe si professeur principal
        $headTeachClass = Classe::where('prof_principal_id', $teacher->id)->pluck('id');
        $classIds = $classIds->merge($headTeachClass)->unique();

        $validated = $request->validate([
            'classe_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'absences' => 'required|array',
            'absences.*.student_id' => 'required|exists:students,id',
            'absences.*.status' => 'required|in:present,absent,late',
        ]);

        // Vérifier que la classe est enseignée par ce professeur
        abort_if(!$classIds->contains($validated['classe_id']), 403);

        $recordedBy = $user->name;
        $recordedAt = now();

        foreach ($validated['absences'] as $absence) {
            StudentAbsence::create([
                'classe_id' => $validated['classe_id'],
                'student_id' => $absence['student_id'],
                'date' => $validated['date'],
                'status' => $absence['status'],
                'recorded_by' => $recordedBy,
                'recorded_at' => $recordedAt,
            ]);
        }

        return redirect()->route('teacher.student-absences.index')
            ->with('success', __('absences.bulk_absences_recorded'));
    }

    /**
     * Générer un rapport PDF
     */
    protected function generatePdfReport($absences, $studentStats)
    {
        // TODO: Implémenter la génération PDF
        return response(['message' => 'PDF generation not yet implemented'], 501);
    }
}
