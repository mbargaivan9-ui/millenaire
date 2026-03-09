<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\User;
use App\Models\Classes;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignmentHistory;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        // Base query for assignments
        $query = Assignment::with(['teacher', 'class', 'subject']);
        
        // Apply filters
        if ($request->filled('search_teacher')) {
            $search = $request->input('search_teacher');
            $query->whereHas('teacher.user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->filled('filter_class')) {
            $query->where('class_id', $request->input('filter_class'));
        }
        
        if ($request->filled('filter_subject')) {
            $query->where('subject_id', $request->input('filter_subject'));
        }
        
        if ($request->filled('filter_status')) {
            $status = $request->input('filter_status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        $assignments = $query->get();
        
        $classes = Classe::with(['students', 'headTeacher.user'])->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $teachers = Teacher::with(['user', 'subjects'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
        $history = TeacherAssignmentHistory::with(['oldTeacher.user', 'newTeacher.user', 'class', 'changedBy'])
            ->latest('changed_at')
            ->take(20)
            ->get();
        $recentAssignments = Assignment::with(['teacher', 'class'])->latest()->take(10)->get();
        
        // Classes without any assignments
        $classIds = Assignment::distinct()->pluck('class_id')->toArray();
        $unassignedClasses = Classes::whereNotIn('id', $classIds)->get();
        
        return view('admin.assignments.index', [
            'assignments' => $assignments,
            'classes' => $classes,
            'subjects' => $subjects,
            'teachers' => $teachers,
            'history' => $history,
            'recentAssignments' => $recentAssignments,
            'unassignedClasses' => $unassignedClasses
        ]);
    }
    
    public function create()
    {
        $teachers = User::where('role', 'teacher')->get();
        $classes = Classes::all();
        $subjects = Subject::all();
        
        return view('admin.assignments.form', [
            'teachers' => $teachers,
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prof_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'schedule' => 'nullable|string',
            'room' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        Assignment::create($validated + [
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        return redirect()->route('admin.assignments.index')
                        ->with('success', 'Affectation créée');
    }
    
    public function edit(Assignment $assignment)
    {
        $teachers = User::where('role', 'teacher')->get();
        $classes = Classes::all();
        $subjects = Subject::all();
        
        return view('admin.assignments.form', [
            'assignment' => $assignment,
            'teachers' => $teachers,
            'classes' => $classes,
            'subjects' => $subjects
        ]);
    }
    
    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'prof_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'schedule' => 'nullable|string',
            'room' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        $assignment->update($validated + [
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        return redirect()->route('admin.assignments.index')
                        ->with('success', 'Affectation mise à jour');
    }
    
    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        
        return redirect()->route('admin.assignments.index')
                        ->with('success', 'Affectation supprimée');
    }

    /**
     * API: Get statistics about assignments
     */
    public function statistics()
    {
        $totalClasses = Classes::count();
        $assignedClasses = Assignment::distinct('class_id')->count();
        $unassignedClasses = $totalClasses - $assignedClasses;
        $assignmentRate = $totalClasses > 0 ? round(($assignedClasses / $totalClasses) * 100) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_classes' => $totalClasses,
                'assigned_classes' => $assignedClasses,
                'unassigned_classes' => $unassignedClasses,
                'assignment_rate' => $assignmentRate,
            ]
        ]);
    }

    /**
     * API: Get available teachers for assignment
     */
    public function availableTeachers()
    {
        $teachers = User::where('role', 'teacher')
            ->where('is_active', true)
            ->select('id', 'name')
            ->with(['classSubjectTeachers' => function($q) {
                $q->select('teacher_id', 'class_id')->distinct();
            }])
            ->get()
            ->map(function($teacher) {
                $classCount = $teacher->classSubjectTeachers->count() ?? 0;
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'current_class' => $classCount > 0 ? $classCount . ' classe(s)' : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $teachers
        ]);
    }

    /**
     * Grid view for assignments in a specific class
     */
    public function grid(Classe $class = null)
    {
        if ($class) {
            // Show assignments for specific class
            $assignments = Assignment::where('class_id', $class->id)
                ->with(['teacher', 'class', 'subject'])
                ->get();
            
            return view('admin.assignments.grid', [
                'assignments' => $assignments,
                'class' => $class,
                'teachers' => Teacher::with(['user', 'subjects'])->where('is_active', true)->get(),
                'subjects' => Subject::orderBy('name')->get()
            ]);
        }
        
        // Show all assignments in grid format
        return redirect()->route('admin.assignments.index');
    }

    /**
     * API: Add multiple assignments for a teacher
     * POST /admin/api/assignments/add-multiple
     * Body: { teacher_id, class_ids: [], subject_ids: [], assignment_type: 'primary' }
     */
    public function addMultiple(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_ids' => 'required|array|min:1',
            'class_ids.*' => 'exists:classes,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
            'assignment_type' => 'string|default:primary',
            'schedule' => 'nullable|string',
            'room' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $count = 0;
            $errors = [];
            
            foreach ($validated['class_ids'] as $classId) {
                foreach ($validated['subject_ids'] as $subjectId) {
                    try {
                        // Check if this combination already exists
                        $exists = Assignment::where([
                            'prof_id' => $validated['teacher_id'],
                            'class_id' => $classId,
                            'subject_id' => $subjectId,
                        ])->first();

                        if (!$exists) {
                            Assignment::create([
                                'prof_id' => $validated['teacher_id'],
                                'class_id' => $classId,
                                'subject_id' => $subjectId,
                                'assignment_type' => $validated['assignment_type'],
                                'schedule' => $validated['schedule'] ?? null,
                                'room' => $validated['room'] ?? null,
                                'notes' => $validated['notes'] ?? null,
                                'is_active' => true,
                            ]);
                            $count++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Classe {$classId} / Matière {$subjectId}: {$e->getMessage()}";
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "✓ {$count} affectation(s) créée(s)",
                'added_count' => $count,
                'errors' => $errors,
                'has_errors' => !empty($errors)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Remove a specific assignment
     * POST /admin/api/assignments/remove
     * Body: { assignment_id } or { teacher_id, class_id, subject_id }
     */
    public function removeAssignment(Request $request)
    {
        try {
            $assignment = null;

            // Find assignment by ID or by combo
            if ($request->filled('assignment_id')) {
                $assignment = Assignment::find($request->input('assignment_id'));
            } else {
                $validated = $request->validate([
                    'teacher_id' => 'required|exists:users,id',
                    'class_id' => 'required|exists:classes,id',
                    'subject_id' => 'required|exists:subjects,id',
                ]);

                $assignment = Assignment::where([
                    'prof_id' => $validated['teacher_id'],
                    'class_id' => $validated['class_id'],
                    'subject_id' => $validated['subject_id']
                ])->first();
            }

            if (!$assignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Affectation non trouvée'
                ], 404);
            }

            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => '✓ Affectation supprimée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Get all classes where a teacher is assigned
     * GET /admin/api/assignments/teacher/{teacher}/classes
     */
    public function getTeacherClasses(User $teacher)
    {
        try {
            $classes = Assignment::where('prof_id', $teacher->id)
                ->with('class')
                ->distinct()
                ->pluck('class')
                ->unique('id')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $classes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * API: Get all teachers assigned to a class
     * GET /admin/api/assignments/class/{class}/teachers
     */
    public function getClassTeachers(Classes $class)
    {
        try {
            $assignments = Assignment::where('class_id', $class->id)
                ->with(['teacher', 'subject'])
                ->get()
                ->groupBy('prof_id');

            $teachers = $assignments->map(function ($group) {
                $first = $group->first();
                return [
                    'id' => $first->teacher->id,
                    'name' => $first->teacher->name,
                    'email' => $first->teacher->email,
                    'subjects' => $group->pluck('subject.name')->unique(),
                    'assignment_count' => $group->count(),
                    'assignments' => $group->map(fn($a) => [
                        'id' => $a->id,
                        'subject_id' => $a->subject_id,
                        'subject_name' => $a->subject->name,
                        'assignment_type' => $a->assignment_type,
                        'room' => $a->room,
                        'schedule' => $a->schedule,
                    ])
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $teachers,
                'total' => $teachers->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 422);
        }
    }
}
