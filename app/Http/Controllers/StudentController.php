<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Announcement;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = auth()->user();
        
        // Get student's grades
        $grades = $student->grades()
            ->with(['subject', 'assignment'])
            ->latest()
            ->take(10)
            ->get();
        
        $averageGrade = $grades->avg('average');
        
        // Get absences
        $absences = $student->absences()
            ->latest()
            ->take(5)
            ->get();
        
        $totalAbsences = $student->absences()->count();
        $justifiedAbsences = $student->absences()->where('type', 'justified')->count();
        $unjustifiedAbsences = $totalAbsences - $justifiedAbsences;
        
        // Get class announcements
        $announcements = Announcement::where('status', 'active')
            ->whereIn('target_audience', ['students', 'all'])
            ->latest()
            ->take(5)
            ->get();
        
        // Get pending payments
        $pendingPayments = $student->payments()
            ->where('status', 'pending')
            ->sum('amount');
        
        // Get bulletin
        $bulletin = $student->bulletins()->latest()->first();
        
        return view('student.dashboard', [
            'grades' => $grades,
            'averageGrade' => $averageGrade,
            'absences' => $absences,
            'totalAbsences' => $totalAbsences,
            'justifiedAbsences' => $justifiedAbsences,
            'unjustifiedAbsences' => $unjustifiedAbsences,
            'announcements' => $announcements,
            'pendingPayments' => $pendingPayments,
            'bulletin' => $bulletin,
            'class' => $student->class
        ]);
    }
    
    public function grades()
    {
        $student = auth()->user();
        
        $grades = $student->grades()
            ->with(['subject', 'assignment'])
            ->latest()
            ->get();
        
        $bySubject = $grades->groupBy('subject.name')
            ->map(function($subjectGrades) {
                return [
                    'average' => $subjectGrades->avg('average'),
                    'count' => $subjectGrades->count(),
                    'grades' => $subjectGrades
                ];
            });
        
        return view('student.grades.index', [
            'grades' => $grades,
            'bySubject' => $bySubject,
            'overallAverage' => $grades->avg('average')
        ]);
    }
    
    public function absences()
    {
        $student = auth()->user();
        
        $absences = $student->absences()
            ->with('subject')
            ->latest()
            ->get();
        
        return view('student.absences.index', [
            'absences' => $absences,
            'total' => $absences->count(),
            'justified' => $absences->where('type', 'justified')->count(),
            'unjustified' => $absences->where('type', 'unjustified')->count(),
            'late' => $absences->where('type', 'late')->count()
        ]);
    }
    
    public function schedule()
    {
        $student = auth()->user();
        $class = $student->class;
        
        if (!$class) {
            return view('student.schedule.empty');
        }
        
        $assignments = $class->assignments()
            ->with('subject')
            ->get();
        
        return view('student.schedule.index', [
            'assignments' => $assignments,
            'class' => $class
        ]);
    }
    
    public function bulletin()
    {
        $student = auth()->user();
        $bulletins = $student->bulletins()->latest()->get();
        
        return view('student.bulletins.index', [
            'bulletins' => $bulletins
        ]);
    }
    
    public function viewBulletin($id)
    {
        $student = auth()->user();
        $bulletin = $student->bulletins()->findOrFail($id);
        
        return view('student.bulletins.view', [
            'bulletin' => $bulletin,
            'student' => $student,
            'grades' => $student->grades()->where('created_at', '>=', $bulletin->created_at)->get()
        ]);
    }
}
