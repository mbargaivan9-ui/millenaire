<?php

namespace App\Http\Controllers;

use App\Models\GuardianRelation;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    /**
     * Display a listing of guardians
     */
    public function index(Request $request)
    {
        $query = Guardian::with(['user', 'students']);
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $guardians = $query->paginate(20);
        
        return view('guardians.index', compact('guardians'));
    }

    /**
     * Show the form for creating a new guardian
     */
    public function create()
    {
        return view('guardians.create');
    }

    /**
     * Store a newly created guardian
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'relationship' => 'required|string|max:50',
            'profession' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
            'phone_professional' => 'nullable|string|max:20',
            'workplace_address' => 'nullable|string|max:255',
            'is_primary_contact' => 'boolean',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phoneNumber' => $validated['phone'],
            'role' => 'parent',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        Guardian::create([
            'user_id' => $user->id,
            'relationship' => $validated['relationship'],
            'profession' => $validated['profession'],
            'company' => $validated['company'],
            'phone_professional' => $validated['phone_professional'],
            'workplace_address' => $validated['workplace_address'],
            'is_primary_contact' => $validated['is_primary_contact'] ?? false,
            'is_active' => true,
        ]);

        return redirect()->route('guardians.index')
            ->with('success', 'Tuteur créé avec succès');
    }

    /**
     * Display the specified guardian
     */
    public function show(Guardian $guardian)
    {
        return view('guardians.show', compact('guardian'));
    }

    /**
     * Show the form for editing the specified guardian
     */
    public function edit(Guardian $guardian)
    {
        return view('guardians.edit', compact('guardian'));
    }

    /**
     * Update the specified guardian
     */
    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $guardian->user->id,
            'phone' => 'required|string|max:20',
            'relationship' => 'required|string|max:50',
            'profession' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:100',
            'phone_professional' => 'nullable|string|max:20',
            'workplace_address' => 'nullable|string|max:255',
            'is_primary_contact' => 'boolean',
        ]);

        $guardian->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phoneNumber' => $validated['phone'],
        ]);

        $guardian->update([
            'relationship' => $validated['relationship'],
            'profession' => $validated['profession'],
            'company' => $validated['company'],
            'phone_professional' => $validated['phone_professional'],
            'workplace_address' => $validated['workplace_address'],
            'is_primary_contact' => $validated['is_primary_contact'] ?? false,
        ]);

        return redirect()->route('guardians.index')
            ->with('success', 'Tuteur mis à jour avec succès');
    }

    /**
     * Remove the specified guardian
     */
    public function destroy(Guardian $guardian)
    {
        $guardian->update(['is_active' => false]);

        return redirect()->route('guardians.index')
            ->with('success', 'Tuteur désactivé avec succès');
    }

    /**
     * Assign student to guardian
     */
    public function assignStudent(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $guardian->students()->attach($validated['student_id']);

        return redirect()->back()
            ->with('success', 'Élève assigné au tuteur avec succès');
    }

    /**
     * Remove student from guardian
     */
    public function removeStudent(Guardian $guardian, Student $student)
    {
        $guardian->students()->detach($student->id);

        return redirect()->back()
            ->with('success', 'Élève supprimé du tuteur');
    }
}
