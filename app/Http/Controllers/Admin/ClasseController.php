<?php

/**
 * Admin\ClasseController — Gestion des Classes
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    public function index(Request $request)
    {
        $query = Classe::with('students', 'headTeacher.user', 'subjects')
            ->withCount('students')
            ->orderBy('name');

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->where('section', $request->input('section'));
        }

        $classes = $query->paginate(20)->appends($request->query());

        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('admin.classes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100|unique:classes',
            'section'  => 'required|in:francophone,anglophone',
            'capacity' => 'nullable|integer|min:1|max:100',
        ]);

        $class = Classe::create($data);

        activity()->causedBy(auth()->user())->performedOn($class)->log('Classe créée');

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe créée avec succès.');
    }

    public function edit(int $id)
    {
        $class = Classe::findOrFail($id);
        return view('admin.classes.edit', compact('class'));
    }

    public function update(Request $request, int $id)
    {
        $class = Classe::findOrFail($id);
        $data  = $request->validate([
            'name'     => "required|string|max:100|unique:classes,name,{$id}",
            'section'  => 'required|in:francophone,anglophone',
            'capacity' => 'nullable|integer',
        ]);

        $class->update($data);
        return redirect()->route('admin.classes.index')->with('success', 'Classe modifiée.');
    }

    public function destroy(int $id)
    {
        $class = Classe::findOrFail($id);

        // Prevent deletion if class has students
        if ($class->students()->count() > 0) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'Impossible de supprimer une classe qui contient des élèves.');
        }

        activity()->causedBy(auth()->user())->performedOn($class)->log('Classe supprimée');
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe supprimée avec succès.');
    }
}
