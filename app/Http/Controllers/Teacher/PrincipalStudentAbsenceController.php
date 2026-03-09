<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentAbsence;
use App\Models\Classe;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrincipalStudentAbsenceController extends Controller
{
    /**
     * Liste des absences des élèves de la classe
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        
        // Récupérer la classe du professeur principal
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();
        
        $query = StudentAbsence::with('student.user')
            ->where('classe_id', $classe->id)
            ->orderByDesc('date');

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
        $students = $classe->students()->with('user')->get();

        // Statistiques
        $stats = [
            'total_records' => StudentAbsence::where('classe_id', $classe->id)->count(),
            'absences' => StudentAbsence::where('classe_id', $classe->id)->where('status', 'absent')->count(),
            'late' => StudentAbsence::where('classe_id', $classe->id)->where('status', 'late')->count(),
            'justified' => StudentAbsence::where('classe_id', $classe->id)->whereNotNull('justification_reason')->count(),
            'unjustified' => StudentAbsence::where('classe_id', $classe->id)->whereNull('justification_reason')->count(),
        ];

        return view('teacher.student_absences.index', compact('absences', 'students', 'classe', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();
        $students = $classe->students()->with('user')->get();
        
        return view('teacher.student_absences.create', compact('classe', 'students'));
    }

    /**
     * Enregistrer une absence
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'justification_reason' => 'nullable|string|max:500',
            'justification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Vérifier que l'élève appartient à la classe
        DB::table('classe_students')
            ->where('classe_id', $classe->id)
            ->where('student_id', $validated['student_id'])
            ->firstOrFail();

        // Traiter le fichier de justification
        if ($request->hasFile('justification_document')) {
            $path = $request->file('justification_document')
                ->store('student_absences', 'public');
            $validated['justification_document'] = $path;
        }

        $validated['classe_id'] = $classe->id;
        $validated['recorded_by'] = $user->name;
        $validated['recorded_at'] = now();

        StudentAbsence::create($validated);

        return redirect()->route('teacher.student_absences.index')
            ->with('success', __('absences.student_absence_recorded'));
    }

    /**
     * Afficher le détail d'une absence
     */
    public function show($absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $absence = StudentAbsence::with('student.user')
            ->where('classe_id', $classe->id)
            ->findOrFail($absence);

        return view('teacher.student_absences.show', compact('absence', 'classe'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $absence = StudentAbsence::with('student.user')
            ->where('classe_id', $classe->id)
            ->findOrFail($absence);

        $students = $classe->students()->with('user')->get();

        return view('teacher.student_absences.edit', compact('absence', 'classe', 'students'));
    }

    /**
     * Mettre à jour une absence
     */
    public function update(Request $request, $absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $absence = StudentAbsence::where('classe_id', $classe->id)->findOrFail($absence);

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

        return redirect()->route('teacher.student_absences.show', $absence->id)
            ->with('success', __('absences.absence_updated'));
    }

    /**
     * Supprimer une absence
     */
    public function destroy($absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $absence = StudentAbsence::where('classe_id', $classe->id)->findOrFail($absence);
        $absence->delete();

        return redirect()->route('teacher.student_absences.index')
            ->with('success', __('absences.absence_deleted'));
    }

    /**
     * Générer un rapport d'absences
     */
    public function report(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $query = StudentAbsence::with('student.user')
            ->where('classe_id', $classe->id);

        // Filtres
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $absences = $query->orderBy('date')->get();

        // Statistiques par élève
        $studentStats = DB::table('student_absences')
            ->where('classe_id', $classe->id)
            ->groupBy('student_id')
            ->select('student_id', 
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_count'),
                DB::raw('SUM(CASE WHEN justification_reason IS NOT NULL THEN 1 ELSE 0 END) as justified_count')
            )
            ->get();

        if ($request->format === 'pdf') {
            return $this->generatePdfReport($absences, $classe, $studentStats);
        }

        return view('teacher.student_absences.report', compact('absences', 'classe', 'studentStats'));
    }

    /**
     * Enregistrement en masse (formulaire)
     */
    public function bulkCreateForm()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();
        $students = $classe->students()->with('user')->get();

        return view('teacher.student_absences.bulk_create', compact('classe', 'students'));
    }

    /**
     * Enregistrement en masse (traitement)
     */
    public function bulkCreate(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $validated = $request->validate([
            'date' => 'required|date',
            'absences' => 'required|array',
            'absences.*.student_id' => 'required|exists:students,id',
            'absences.*.status' => 'required|in:present,absent,late',
        ]);

        $recordedBy = $user->name;
        $recordedAt = now();

        foreach ($validated['absences'] as $absence) {
            StudentAbsence::create([
                'classe_id' => $classe->id,
                'student_id' => $absence['student_id'],
                'date' => $validated['date'],
                'status' => $absence['status'],
                'recorded_by' => $recordedBy,
                'recorded_at' => $recordedAt,
            ]);
        }

        return redirect()->route('teacher.student_absences.index')
            ->with('success', __('absences.bulk_absences_recorded'));
    }

    /**
     * Générer un rapport PDF
     */
    private function generatePdfReport($absences, $classe, $studentStats)
    {
        // À implémenter avec une bibliothèque PDF si nécessaire
        return view('teacher.student_absences.report_pdf', compact('absences', 'classe', 'studentStats'));
    }

    /**
     * Justifier une absence
     */
    public function justify(Request $request, $absence)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();
        $classe = Classe::where('prof_principal_id', $teacher->id)->firstOrFail();

        $absence = StudentAbsence::where('classe_id', $classe->id)->findOrFail($absence);

        $validated = $request->validate([
            'justification_reason' => 'required|string|max:500',
            'justification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('justification_document')) {
            $path = $request->file('justification_document')
                ->store('student_absences', 'public');
            $validated['justification_document'] = $path;
        }

        $absence->update($validated);

        return redirect()->route('teacher.student_absences.show', $absence->id)
            ->with('success', __('absences.absence_justified'));
    }
}
