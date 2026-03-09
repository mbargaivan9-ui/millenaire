<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\User;
use App\Models\Subject;
use App\Models\Assignment;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::with(['student', 'subject', 'assignment']);
        
        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $grades = $query->latest()->paginate(20);
        
        $students = User::where('role', 'student')->get();
        $subjects = Subject::all();
        
        return view('admin.grades.index', [
            'grades' => $grades,
            'students' => $students,
            'subjects' => $subjects
        ]);
    }
    
    public function create(Request $request)
    {
        $students = User::where('role', 'student')->get();
        $subjects = Subject::all();
        $assignments = Assignment::all();
        
        return view('admin.grades.form', [
            'students' => $students,
            'subjects' => $subjects,
            'assignments' => $assignments
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'assignment_id' => 'nullable|exists:assignments,id',
            'homework' => 'nullable|numeric|min:0|max:20',
            'classwork' => 'nullable|numeric|min:0|max:20',
            'exam' => 'nullable|numeric|min:0|max:20',
            'comment' => 'nullable|string'
        ]);
        
        // Calculate average
        $components = array_filter([
            $validated['homework'] ?? null,
            $validated['classwork'] ?? null,
            $validated['exam'] ?? null
        ]);
        
        $validated['average'] = count($components) > 0 
            ? array_sum($components) / count($components) 
            : null;
        
        $validated['status'] = ($validated['average'] ?? 0) >= 10 ? 'pass' : 'fail';
        $validated['graded_by'] = auth()->id();
        $validated['graded_at'] = now();
        
        Grade::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id']
            ],
            $validated
        );
        
        return redirect()->route('admin.grades.index')
                        ->with('success', 'Note enregistrée');
    }
    
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'grades' => 'required|array'
        ]);
        
        $assignment = Assignment::findOrFail($validated['assignment_id']);
        
        foreach ($validated['grades'] as $studentId => $gradeData) {
            $components = array_filter([
                $gradeData['homework'] ?? null,
                $gradeData['classwork'] ?? null,
                $gradeData['exam'] ?? null
            ]);
            
            $average = count($components) > 0 
                ? array_sum($components) / count($components) 
                : null;
            
            Grade::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'assignment_id' => $assignment->id
                ],
                [
                    'subject_id' => $assignment->subject_id,
                    'homework' => $gradeData['homework'] ?? null,
                    'classwork' => $gradeData['classwork'] ?? null,
                    'exam' => $gradeData['exam'] ?? null,
                    'average' => $average,
                    'status' => ($average ?? 0) >= 10 ? 'pass' : 'fail',
                    'comment' => $gradeData['comment'] ?? null,
                    'graded_by' => auth()->id(),
                    'graded_at' => now()
                ]
            );
        }
        
        return redirect()->route('teacher.assignments.details', $assignment)
                        ->with('success', 'Grades sauvegardés');
    }
}
