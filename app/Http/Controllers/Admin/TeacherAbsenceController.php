<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAbsence;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherAbsenceController extends Controller
{
    /**
     * Liste des absences des enseignants
     */
    public function index(Request $request)
    {
        $query = TeacherAbsence::with('teacher.user')
            ->orderByDesc('date');

        // Filtrer par professeur
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
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

        // Filtrer par approbation
        if ($request->filled('approval_status')) {
            if ($request->approval_status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->approval_status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        $absences = $query->paginate(50);
        $teachers = Teacher::with('user')->get();

        // Statistiques
        $stats = [
            'total_records' => TeacherAbsence::count(),
            'absences' => TeacherAbsence::where('status', 'absent')->count(),
            'justified' => TeacherAbsence::where('status', 'justified')->count(),
            'pending' => TeacherAbsence::where('is_approved', false)->count(),
            'approved' => TeacherAbsence::where('is_approved', true)->count(),
        ];

        return view('admin.teacher_absences.index', compact('absences', 'teachers', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $teachers = Teacher::with('user')->get();
        
        return view('admin.teacher_absences.create', compact('teachers'));
    }

    /**
     * Enregistrer une absence
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,justified,medical_leave,authorized_leave',
            'reason' => 'nullable|string|max:500',
            'justification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Traiter le fichier de justification
        if ($request->hasFile('justification_document')) {
            $path = $request->file('justification_document')
                ->store('teacher_absences', 'public');
            $validated['justification_document'] = $path;
        }

        $validated['recorded_by'] = auth()->user()->name;
        $validated['recorded_at'] = now();

        TeacherAbsence::create($validated);

        return redirect()->route('admin.teacher_absences.index')
            ->with('success', __('teacher_absences.created_success'));
    }

    /**
     * Afficher la page d'édition
     */
    public function edit(TeacherAbsence $teacherAbsence)
    {
        $teachers = Teacher::with('user')->get();
        
        return view('admin.teacher_absences.edit', compact('teacherAbsence', 'teachers'));
    }

    /**
     * Mettre à jour une absence
     */
    public function update(Request $request, TeacherAbsence $teacherAbsence)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,justified,medical_leave,authorized_leave',
            'reason' => 'nullable|string|max:500',
            'justification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Traiter le nouveau fichier de justification
        if ($request->hasFile('justification_document')) {
            if ($teacherAbsence->justification_document) {
                \Storage::disk('public')->delete($teacherAbsence->justification_document);
            }
            $path = $request->file('justification_document')
                ->store('teacher_absences', 'public');
            $validated['justification_document'] = $path;
        }

        $teacherAbsence->update($validated);

        return redirect()->route('admin.teacher_absences.index')
            ->with('success', __('teacher_absences.updated_success'));
    }

    /**
     * Supprimer une absence
     */
    public function destroy(TeacherAbsence $teacherAbsence)
    {
        if ($teacherAbsence->justification_document) {
            \Storage::disk('public')->delete($teacherAbsence->justification_document);
        }

        $teacherAbsence->delete();

        return redirect()->route('admin.teacher_absences.index')
            ->with('success', __('teacher_absences.deleted_success'));
    }

    /**
     * Approuver une absence
     */
    public function approve(TeacherAbsence $teacherAbsence)
    {
        $teacherAbsence->approve(auth()->user()->name);

        return back()->with('success', __('teacher_absences.approved_success'));
    }

    /**
     * Rejeter une absence
     */
    public function reject(TeacherAbsence $teacherAbsence)
    {
        $teacherAbsence->reject();

        return back()->with('success', __('teacher_absences.rejected_success'));
    }

    /**
     * Rapport d'assiduité des enseignants
     */
    public function report(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date) 
            : Carbon::now()->startOfMonth();
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date) 
            : Carbon::now()->endOfMonth();

        $summary = [];
        $teachers = Teacher::with('user')->get();

        foreach ($teachers as $teacher) {
            $total = TeacherAbsence::whereBetween('date', [$startDate, $endDate])
                ->forTeacher($teacher->id)
                ->count();

            if ($total > 0) {
                $absences = TeacherAbsence::whereBetween('date', [$startDate, $endDate])
                    ->forTeacher($teacher->id)
                    ->where('status', 'absent')
                    ->count();

                $justified = TeacherAbsence::whereBetween('date', [$startDate, $endDate])
                    ->forTeacher($teacher->id)
                    ->where('status', 'justified')
                    ->count();

                $summary[] = [
                    'teacher' => $teacher,
                    'total_days' => $total,
                    'absences' => $absences,
                    'justified' => $justified,
                    'rate' => round(($absences / $total) * 100, 2),
                ];
            }
        }

        // Trier par taux d'absence
        usort($summary, function ($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });

        return view('admin.teacher_absences.report', compact('summary', 'startDate', 'endDate'));
    }

    /**
     * Import en masse des absences
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'absent_teachers' => 'required|array',
            'absent_teachers.*' => 'exists:teachers,id',
            'status' => 'required|in:absent,justified,medical_leave,authorized_leave',
            'reason' => 'nullable|string',
        ]);

        $count = 0;
        foreach ($validated['absent_teachers'] as $teacherId) {
            TeacherAbsence::createOrUpdate(
                ['teacher_id' => $teacherId, 'date' => $validated['date']],
                [
                    'status' => $validated['status'],
                    'reason' => $validated['reason'] ?? null,
                    'recorded_by' => auth()->user()->name,
                    'recorded_at' => now(),
                ]
            );
            $count++;
        }

        return redirect()->back()
            ->with('success', "$count " . __('teacher_absences.bulk_created'));
    }
}
