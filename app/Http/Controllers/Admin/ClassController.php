<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\LevelHelper;
use App\Models\Classe;
use App\Models\User;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Liste des classes
     */
    public function index()
    {
        $classes = Classe::with('profPrincipal:id,name')
            ->orderBy('level')
            ->paginate(20);

        return view('admin.classes.index', compact('classes'));
    }

    /**
     * Voir les détails d'une classe
     */
    public function show(Classe $classe)
    {
        $classe->load('students', 'classSubjectTeachers.teacher.user', 'classSubjectTeachers.subject', 'profPrincipal.user');

        return view('admin.classes.show', compact('classe'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $profPrincipals = User::where('role', 'prof_principal')->get();
        $levels = LevelHelper::getAllLevels();

        return view('admin.classes.create', compact('profPrincipals', 'levels'));
    }

    /**
     * Crée une classe
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:classes',
            'level' => 'required|string',
            'section' => 'nullable|string|max:2',
            'capacity' => 'required|integer|min:10|max:100',
            'prof_principal_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        Classe::create($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe créée avec succès');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Classe $classe)
    {
        $profPrincipals = User::where('role', 'prof_principal')->get();
        $levels = LevelHelper::getAllLevels();

        return view('admin.classes.edit', compact('classe', 'profPrincipals', 'levels'));
    }

    /**
     * Met à jour une classe
     */
    public function update(Request $request, Classe $classe)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:classes,name,' . $classe->id,
            'level' => 'required|string',
            'section' => 'nullable|string|max:2',
            'capacity' => 'required|integer|min:10|max:100',
            'prof_principal_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $classe->update($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe mise à jour');
    }

    /**
     * Supprime une classe
     */
    public function destroy(Classe $classe)
    {
        $studentCount = $classe->students()->count();
        
        if ($studentCount > 0) {
            return back()->with('error', __('messages.class_has_students', ['count' => $studentCount], 'fr'));
        }

        $className = $classe->name;
        $classe->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', __('messages.class_deleted', [], 'fr') . ' (' . $className . ')');
    }
}
