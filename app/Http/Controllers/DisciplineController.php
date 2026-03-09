<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class DisciplineController extends Controller
{
    /**
     * Display a listing of discipline records
     */
    public function index(Request $request)
    {
        $query = Discipline::with(['student.user', 'recordedBy']);
        
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $disciplines = $query->latest()->paginate(20);
        
        return view('discipline.index', compact('disciplines'));
    }

    /**
     * Show the form for creating a new discipline record
     */
    public function create()
    {
        $students = Student::with('user')->get();
        
        return view('discipline.create', compact('students'));
    }

    /**
     * Store a newly created discipline record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:warning,detention,suspension,expulsion',
            'reason' => 'required|string|max:500',
            'description' => 'nullable|string',
            'incident_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'resolution' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,active,resolved',
        ]);

        $validated['recorded_by'] = auth()->id();

        Discipline::create($validated);

        return redirect()->route('discipline.index')
            ->with('success', 'Enregistrement disciplinaire créé avec succès');
    }

    /**
     * Display the specified discipline record
     */
    public function show(Discipline $discipline)
    {
        return view('discipline.show', compact('discipline'));
    }

    /**
     * Show the form for editing the specified discipline record
     */
    public function edit(Discipline $discipline)
    {
        $students = Student::with('user')->get();
        
        return view('discipline.edit', compact('discipline', 'students'));
    }

    /**
     * Update the specified discipline record
     */
    public function update(Request $request, Discipline $discipline)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:warning,detention,suspension,expulsion',
            'reason' => 'required|string|max:500',
            'description' => 'nullable|string',
            'incident_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'resolution' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,active,resolved',
        ]);

        $discipline->update($validated);

        return redirect()->route('discipline.index')
            ->with('success', 'Enregistrement disciplinaire mis à jour avec succès');
    }

    /**
     * Remove the specified discipline record
     */
    public function destroy(Discipline $discipline)
    {
        $discipline->delete();

        return redirect()->route('discipline.index')
            ->with('success', 'Enregistrement disciplinaire supprimé avec succès');
    }

    /**
     * Get discipline statistics
     */
    public function statistics()
    {
        $byType = Discipline::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();
        
        $byStatus = Discipline::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $recent = Discipline::with(['student.user', 'recordedBy'])
            ->latest()
            ->take(10)
            ->get();
        
        return view('discipline.statistics', compact('byType', 'byStatus', 'recent'));
    }
}
