<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Grade;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        
        $assignments = $user->assignments()
            ->with(['class', 'subject', 'grades'])
            ->get();
        
        $totalStudents = $assignments->flatMap->class->unique('id')->count();
        
        $recentGrades = Grade::whereIn('assignment_id', $assignments->pluck('id'))
            ->latest()
            ->take(5)
            ->get();
        
        $classesCount = $assignments
            ->groupBy('class_id')
            ->count();
        
        return view('teacher.dashboard', [
            'assignments' => $assignments,
            'totalStudents' => $totalStudents,
            'classesCount' => $classesCount,
            'recentGrades' => $recentGrades
        ]);
    }
    
    public function assignments()
    {
        $assignments = auth()->user()->assignments()
            ->with(['class', 'subject'])
            ->get();
        
        return view('teacher.assignments.index', [
            'assignments' => $assignments
        ]);
    }
    
    public function assignmentDetails(Assignment $assignment)
    {
        $this->authorize('viewOwn', $assignment);
        
        $students = $assignment->class->students;
        $grades = $assignment->grades;
        
        return view('teacher.assignments.details', [
            'assignment' => $assignment,
            'students' => $students,
            'grades' => $grades
        ]);
    }
    
    public function saveGrades(Request $request, Assignment $assignment)
    {
        $this->authorize('viewOwn', $assignment);
        
        $validated = $request->validate([
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.homework' => 'nullable|numeric|min:0|max:20',
            'grades.*.classwork' => 'nullable|numeric|min:0|max:20',
            'grades.*.exam' => 'nullable|numeric|min:0|max:20',
            'grades.*.comment' => 'nullable|string'
        ]);
        
        foreach ($validated['grades'] as $gradeData) {
            $gradeData['assignment_id'] = $assignment->id;
            $gradeData['subject_id'] = $assignment->subject_id;
            $gradeData['graded_by'] = auth()->id();
            $gradeData['graded_at'] = now();
            
            // Calculate average
            if (isset($gradeData['homework']) || isset($gradeData['classwork']) || isset($gradeData['exam'])) {
                $components = array_filter([
                    $gradeData['homework'] ?? null,
                    $gradeData['classwork'] ?? null,
                    $gradeData['exam'] ?? null
                ]);
                $gradeData['average'] = count($components) > 0 
                    ? array_sum($components) / count($components) 
                    : null;
                
                $gradeData['status'] = $gradeData['average'] >= 10 ? 'pass' : 'fail';
            }
            
            Grade::updateOrCreate(
                [
                    'student_id' => $gradeData['student_id'],
                    'assignment_id' => $assignment->id
                ],
                $gradeData
            );
        }
        
        return redirect()->route('teacher.assignments.details', $assignment)
                        ->with('success', 'Grades sauvegardés');
    }
}
