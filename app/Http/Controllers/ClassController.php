<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = Classes::with([
            'students', 
            'profPrincipal', 
            'headTeacher.user',
            'headTeacher.classSubjectTeachers.subject',
            'subjects'
        ])
            ->withCount('students')
            ->paginate(15);
        
        return view('admin.classes.index', [
            'classes' => $classes
        ]);
    }
    
    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        
        return view('admin.classes.create', [
            'teachers' => $teachers,
            'subjects' => $subjects
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:classes',
            'section' => 'required|in:francophone,anglophone',
            'capacity' => 'nullable|integer|min:1|max:100',
            'prof_principal_id' => 'nullable|exists:users,id',
            'teacher_subject_id' => 'nullable|exists:subjects,id|required_if:prof_principal_id,!null'
        ]);
        
        // Créer la classe
        $class = Classes::create($validated);
        
        // Si un prof principal est assigné, créer les relations nécessaires
        if ($request->filled('prof_principal_id') && $request->filled('teacher_subject_id')) {
            $user = User::find($request->prof_principal_id);
            if ($user && $user->teacher) {
                // Mettre à jour prof_principal_id ET head_teacher_id
                $class->update([
                    'prof_principal_id' => $request->prof_principal_id,
                    'head_teacher_id' => $user->teacher->id
                ]);
                
                // Relier le professeur à cette classe et matière
                $user->teacher->classSubjectTeachers()->create([
                    'class_id' => $class->id,
                    'subject_id' => $request->teacher_subject_id
                ]);
            }
        }
        
        return redirect()->route('admin.classes.index')
                        ->with('success', 'Classe créée');
    }
    
    public function edit(Classes $class)
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        
        return view('admin.classes.edit', [
            'class' => $class,
            'teachers' => $teachers,
            'subjects' => $subjects
        ]);
    }
    
    public function update(Request $request, Classes $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:classes,name,' . $class->id,
            'section' => 'required|in:francophone,anglophone',
            'capacity' => 'nullable|integer|min:1|max:100',
            'prof_principal_id' => 'nullable|exists:users,id',
            'teacher_subject_id' => 'nullable|exists:subjects,id|required_if:prof_principal_id,!null'
        ]);
        
        $oldProfId = $class->prof_principal_id;
        $class->update($validated);
        
        // Supprimer l'ancienne entrée si le professeur principal a changé
        if ($oldProfId && $oldProfId !== $request->prof_principal_id) {
            $oldUser = User::find($oldProfId);
            if ($oldUser && $oldUser->teacher) {
                $oldUser->teacher->classSubjectTeachers()->where('class_id', $class->id)->delete();
            }
        }
        
        // Ajouter la nouvelle relation si nécessaire
        if ($request->filled('prof_principal_id') && $request->filled('teacher_subject_id')) {
            $newUser = User::find($request->prof_principal_id);
            if ($newUser && $newUser->teacher) {
                // Mettre à jour prof_principal_id ET head_teacher_id
                $class->update([
                    'prof_principal_id' => $request->prof_principal_id,
                    'head_teacher_id' => $newUser->teacher->id
                ]);
                
                // Vérifier si l'enregistrement existe déjà
                $exists = $newUser->teacher->classSubjectTeachers()
                    ->where('class_id', $class->id)
                    ->exists();
                
                if (!$exists) {
                    $newUser->teacher->classSubjectTeachers()->create([
                        'class_id' => $class->id,
                        'subject_id' => $request->teacher_subject_id
                    ]);
                } else {
                    $newUser->teacher->classSubjectTeachers()
                        ->where('class_id', $class->id)
                        ->update(['subject_id' => $request->teacher_subject_id]);
                }
            }
        } else if (!$request->filled('prof_principal_id')) {
            // Si prof principal est vide, nettoyer prof_principal_id ET head_teacher_id
            $class->update([
                'prof_principal_id' => null,
                'head_teacher_id' => null
            ]);
        }
        
        return redirect()->route('admin.classes.index')
                        ->with('success', 'Classe mise à jour');
    }
    
    public function destroy(Classes $class)
    {
        $class->delete();
        
        return redirect()->route('admin.classes.index')
                        ->with('success', 'Classe supprimée');
    }
}
