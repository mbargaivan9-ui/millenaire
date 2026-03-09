<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAbsence;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Affiche le dashboard des absences des enseignants
     */
    public function index(Request $request)
    {
        $query = TeacherAbsence::with('teacher.user');

        // Filtrage par enseignant
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        // Filtrage par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtrage par statut d'approbation
        if ($request->filled('approval_status')) {
            if ($request->approval_status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->approval_status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        // Filtrage par période
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $attendances = $query->orderByDesc('date')->paginate(50);
        $teachers = Teacher::with('user')->get();

        // Statistiques
        $stats = [
            'total_records' => TeacherAbsence::count(),
            'absences' => TeacherAbsence::where('status', 'absent')->count(),
            'justified' => TeacherAbsence::where('status', 'justified')->count(),
            'pending' => TeacherAbsence::where('is_approved', false)->count(),
            'approved' => TeacherAbsence::where('is_approved', true)->count(),
        ];

        return view('admin.attendance.index', compact('attendances', 'teachers', 'stats'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $teachers = Teacher::with('user')->get();
        return view('admin.attendance.create', compact('teachers'));
    }

    /**
     * Crée un enregistrement d'absence
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

        return redirect()->route('admin.attendance.index')
                        ->with('success', 'Absence enregistrée');
    }

    /**
     * Édite un enregistrement
     */
    public function edit(TeacherAbsence $attendance)
    {
        $attendance->load(['teacher.user']);
        $teachers = Teacher::with('user')->get();
        return view('admin.attendance.edit', compact('attendance', 'teachers'));
    }

    /**
     * Met à jour un enregistrement
     */
    public function update(Request $request, TeacherAbsence $attendance)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,justified,medical_leave,authorized_leave',
            'reason' => 'nullable|string|max:500',
            'justification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_approved' => 'nullable|boolean',
        ]);

        // Traiter le fichier de justification
        if ($request->hasFile('justification_document')) {
            $path = $request->file('justification_document')
                ->store('teacher_absences', 'public');
            $validated['justification_document'] = $path;
        }

        // Gérer l'approbation
        if ($request->has('is_approved')) {
            $validated['is_approved'] = true;
            $validated['approved_by'] = auth()->user()->name;
            $validated['approved_at'] = now();
        } else {
            $validated['is_approved'] = false;
        }

        $attendance->update($validated);

        return redirect()->route('admin.attendance.index')
                        ->with('success', 'Absence mise à jour');
    }

    /**
     * Supprime un enregistrement
     */
    public function destroy(TeacherAbsence $attendance)
    {
        $attendance->delete();
        return redirect()->route('admin.attendance.index')
                        ->with('success', 'Absence supprimée');
    }

    /**
     * Import d'absences en masse
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'absent_teachers' => 'required|array',
            'absent_teachers.*' => 'exists:teachers,id',
            'status' => 'required|in:absent,late,justified,medical_leave,authorized_leave',
            'reason' => 'nullable|string|max:500',
        ]);

        $count = 0;
        foreach ($validated['absent_teachers'] as $teacherId) {
            TeacherAbsence::create([
                'teacher_id' => $teacherId,
                'date' => $validated['date'],
                'status' => $validated['status'],
                'reason' => $validated['reason'] ?? null,
                'recorded_by' => auth()->user()->name,
                'recorded_at' => now(),
            ]);
            $count++;
        }

        return redirect()->back()
                        ->with('success', "{$count} absences enregistrées");
    }

    /**
     * Approuver une absence
     */
    public function approve(TeacherAbsence $attendance)
    {
        $attendance->update([
            'is_approved' => true,
            'approved_by' => auth()->user()->name,
            'approved_at' => now(),
        ]);

        return redirect()->back()
                        ->with('success', 'Absence approuvée');
    }

    /**
     * Rejeter une absence
     */
    public function reject(TeacherAbsence $attendance)
    {
        $attendance->update([
            'is_approved' => false,
        ]);

        return redirect()->back()
                        ->with('success', 'Absence rejetée');
    }

    /**
     * Rapport d'assiduité
     */
    public function report(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        $summary = [];
        $teachers = Teacher::with('user')->get();

        foreach ($teachers as $teacher) {
            $total = TeacherAbsence::whereBetween('date', [$startDate, $endDate])
                ->where('teacher_id', $teacher->id)
                ->count();

            $absences = TeacherAbsence::whereBetween('date', [$startDate, $endDate])
                ->where('teacher_id', $teacher->id)
                ->where('status', 'absent')
                ->count();

            $justified = TeacherAbsence::whereBetween('date', [$startDate, $endDate])
                ->where('teacher_id', $teacher->id)
                ->where('status', 'justified')
                ->count();

            if ($total > 0) {
                $summary[] = [
                    'teacher' => $teacher,
                    'total' => $total,
                    'absences' => $absences,
                    'justified' => $justified,
                    'rate' => round(($absences / $total) * 100, 2),
                ];
            }
        }

        // Sort by absence rate
        usort($summary, function ($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });

        return view('admin.attendance.report', compact('summary', 'startDate', 'endDate'));
    }
}
