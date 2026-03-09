<?php

namespace App\Http\Controllers;

use App\Models\CourseMaterial;
use App\Models\ClassSubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseMaterialController extends Controller
{
    /**
     * Display a listing of course materials
     */
    public function index(Request $request)
    {
        $query = CourseMaterial::with(['classSubjectTeacher.subject', 'classSubjectTeacher.teacher']);
        
        if ($request->has('subject_id')) {
            $query->whereHas('classSubjectTeacher', function ($q) {
                $q->where('subject_id', request()->subject_id);
            });
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $materials = $query->paginate(20);
        
        return view('course-materials.index', compact('materials'));
    }

    /**
     * Show the form for creating a new course material
     */
    public function create()
    {
        $classSubjectTeachers = ClassSubjectTeacher::with(['classe', 'subject', 'teacher'])->get();
        
        return view('course-materials.create', compact('classSubjectTeachers'));
    }

    /**
     * Store a newly created course material
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:pdf,video,document,presentation,exercise',
            'file' => 'nullable|file|max:102400',
            'external_link' => 'nullable|url',
            'upload_date' => 'required|date',
            'is_visible' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('course-materials', 'public');
        }

        $validated['is_visible'] = $request->has('is_visible');

        CourseMaterial::create($validated);

        return redirect()->route('course-materials.index')
            ->with('success', 'Matériel de cours créé avec succès');
    }

    /**
     * Display the specified course material
     */
    public function show(CourseMaterial $courseMaterial)
    {
        return view('course-materials.show', compact('courseMaterial'));
    }

    /**
     * Show the form for editing the specified course material
     */
    public function edit(CourseMaterial $courseMaterial)
    {
        $classSubjectTeachers = ClassSubjectTeacher::with(['classe', 'subject', 'teacher'])->get();
        
        return view('course-materials.edit', compact('courseMaterial', 'classSubjectTeachers'));
    }

    /**
     * Update the specified course material
     */
    public function update(Request $request, CourseMaterial $courseMaterial)
    {
        $validated = $request->validate([
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:pdf,video,document,presentation,exercise',
            'file' => 'nullable|file|max:102400',
            'external_link' => 'nullable|url',
            'upload_date' => 'required|date',
            'is_visible' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            if ($courseMaterial->file_path) {
                Storage::disk('public')->delete($courseMaterial->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('course-materials', 'public');
        }

        $validated['is_visible'] = $request->has('is_visible');

        $courseMaterial->update($validated);

        return redirect()->route('course-materials.index')
            ->with('success', 'Matériel de cours mis à jour avec succès');
    }

    /**
     * Remove the specified course material
     */
    public function destroy(CourseMaterial $courseMaterial)
    {
        if ($courseMaterial->file_path) {
            Storage::disk('public')->delete($courseMaterial->file_path);
        }

        $courseMaterial->delete();

        return redirect()->route('course-materials.index')
            ->with('success', 'Matériel de cours supprimé avec succès');
    }

    /**
     * Download course material
     */
    public function download(CourseMaterial $material)
    {
        if (!$material->file_path) {
            return redirect()->back()->with('error', 'Fichier non disponible');
        }

        return Storage::disk('public')->download($material->file_path);
    }
}
