<?php

/**
 * Teacher\AttendanceController — Gestion des Présences
 *
 * Phase 3 — Appel en classe, historique absences
 * Notification temps-réel aux parents via Laravel Reverb
 *
 * @package App\Http\Controllers\Teacher
 */

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Classe;
use App\Models\ClassSubjectTeacher;
use App\Models\Student;
use App\Events\StudentAbsenceRecorded;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly NotificationService $notifService
    ) {}

    /**
     * Interface d'appel — liste des élèves avec statut aujourd'hui.
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        // Classes que l'enseignant gère
        $myClasses = Classe::whereHas('classSubjectTeachers', fn($q) => $q->where('teacher_id', $teacher->id))
            ->orWhere('head_teacher_id', $teacher->id)
            ->with('students.user')
            ->orderBy('name')
            ->get();

        $classId  = $request->get('class_id', $myClasses->first()?->id);
        $class    = $myClasses->firstWhere('id', $classId);
        $date     = $request->get('date', today()->toDateString());
        $subjectId = $request->get('subject_id');

        $students = $class?->students()->with('user', 'absences')->get() ?? collect();

        // Déjà fait l'appel aujourd'hui pour ce cours ?
        $todayAbsences = Absence::where('class_id', $classId)
            ->where('date', $date)
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->pluck('status', 'student_id');

        return view('teacher.attendance.index', compact(
            'myClasses', 'class', 'students', 'date', 'todayAbsences', 'subjectId'
        ));
    }

    /**
     * Enregistrer l'appel (AJAX batch).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'    => 'required|exists:classes,id',
            'date'        => 'required|date',
            'subject_id'  => 'nullable|exists:subjects,id',
            'statuses'    => 'required|array',
            'statuses.*'  => 'in:present,absent,late,excused',
        ]);

        $teacher = auth()->user()->teacher;
        $saved   = 0;

        foreach ($request->statuses as $studentId => $status) {
            $absence = Absence::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'class_id'   => $request->class_id,
                    'date'       => $request->date,
                    'subject_id' => $request->subject_id,
                ],
                [
                    'status'     => $status,
                    'teacher_id' => $teacher->id,
                    'justified'  => $status === 'excused',
                ]
            );

            // Notifier le parent si absent (non justifié)
            if (in_array($status, ['absent', 'late'])) {
                $student = Student::with('user', 'guardian.user')->find($studentId);
                if ($student) {
                    broadcast(new StudentAbsenceRecorded($student, $absence))->toOthers();
                    $this->notifService->sendAbsenceNotification($student, $absence);
                }
            }

            $saved++;
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties(['class_id' => $request->class_id, 'date' => $request->date, 'count' => $saved])
            ->log('Appel effectué');

        return response()->json([
            'success' => true,
            'saved'   => $saved,
            'message' => app()->getLocale() === 'fr' ? "$saved présences enregistrées." : "$saved attendances recorded.",
        ]);
    }

    /**
     * Historique des absences d'une classe.
     */
    public function history(Request $request, int $classId)
    {
        $class    = Classe::findOrFail($classId);
        $absences = Absence::where('class_id', $classId)
            ->with('student.user', 'subject')
            ->orderByDesc('date')
            ->paginate(30);

        return view('teacher.attendance.history', compact('class', 'absences'));
    }
}
