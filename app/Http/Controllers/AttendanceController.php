<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\ClassSubjectTeacher;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['student', 'classSubjectTeacher.subject']);
        
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }
        
        $attendances = $query->latest()->paginate(20);
        
        return view('attendance.index', compact('attendances'));
    }

    /**
     * Show the form for creating a new attendance record
     */
    public function create()
    {
        $students = Student::with('user')->get();
        $classSubjectTeachers = ClassSubjectTeacher::with(['classe', 'subject', 'teacher'])->get();
        
        return view('attendance.create', compact('students', 'classSubjectTeachers'));
    }

    /**
     * Store a newly created attendance record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,justified',
            'reason' => 'nullable|string|max:500',
        ]);

        $validated['recorded_by'] = auth()->id();
        $validated['recorded_at'] = now();

        Attendance::create($validated);

        return redirect()->route('attendance.index')
            ->with('success', 'Enregistrement d\'assiduité créé avec succès');
    }

    /**
     * Display the specified attendance record
     */
    public function show(Attendance $attendance)
    {
        return view('attendance.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified attendance record
     */
    public function edit(Attendance $attendance)
    {
        $students = Student::with('user')->get();
        $classSubjectTeachers = ClassSubjectTeacher::with(['classe', 'subject', 'teacher'])->get();
        
        return view('attendance.edit', compact('attendance', 'students', 'classSubjectTeachers'));
    }

    /**
     * Update the specified attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,justified',
            'reason' => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return redirect()->route('attendance.index')
            ->with('success', 'Enregistrement d\'assiduité mis à jour avec succès');
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()->route('attendance.index')
            ->with('success', 'Enregistrement d\'assiduité supprimé avec succès');
    }

    /**
     * Bulk create attendance records
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,justified',
            'attendances.*.reason' => 'nullable|string|max:500',
        ]);

        $recordedBy = auth()->id();
        $recordedAt = now();
        $classSubjectTeacherId = $validated['class_subject_teacher_id'];
        $date = $validated['date'];

        foreach ($validated['attendances'] as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['student_id'],
                    'class_subject_teacher_id' => $classSubjectTeacherId,
                    'date' => $date,
                ],
                [
                    'status' => $record['status'],
                    'reason' => $record['reason'] ?? null,
                    'recorded_by' => $recordedBy,
                    'recorded_at' => $recordedAt,
                ]
            );
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Enregistrements d\'assiduité créés avec succès');
    }

    /**
     * Generate attendance report
     */
    public function report()
    {
        $attendances = Attendance::with(['student.user', 'classSubjectTeacher.subject'])
            ->latest()
            ->paginate(50);

        return view('attendance.report', compact('attendances'));
    }
}
