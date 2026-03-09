<?php

/**
 * Teacher\MaterialController — Ressources Pédagogiques
 *
 * Phase 5 — Section 5.2 — E-Learning Teacher Side
 * Upload PDF, vidéo YouTube/Vimeo, PowerPoint
 *
 * @package App\Http\Controllers\Teacher
 */

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassSubjectTeacher;
use App\Models\CourseMaterial;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Liste des ressources publiées par cet enseignant.
     */
    public function index()
    {
        $teacher   = auth()->user()->teacher;
        $materials = CourseMaterial::where('teacher_id', $teacher->id)
            ->with('subject', 'classes')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('teacher.materials.index', compact('materials'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $teacher  = auth()->user()->teacher;
        $subjects = Subject::whereHas('classSubjectTeachers', fn($q) => $q->where('teacher_id', $teacher->id))
            ->orderBy('name')->get();
        $classes  = \App\Models\Classe::whereHas('classSubjectTeachers', fn($q) => $q->where('teacher_id', $teacher->id))
            ->orderBy('name')->get();

        return view('teacher.materials.create', compact('subjects', 'classes'));
    }

    /**
     * Enregistrer une nouvelle ressource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:pdf,video,powerpoint,link',
            'subject_id'   => 'required|exists:subjects,id',
            'class_ids'    => 'required|array|min:1',
            'class_ids.*'  => 'exists:classes,id',
            'description'  => 'nullable|string|max:1000',
            'file'         => 'nullable|file|mimes:pdf,ppt,pptx|max:20480',
            'video_url'    => 'nullable|url',
            'external_url' => 'nullable|url',
            'is_published' => 'boolean',
        ]);

        $teacher    = auth()->user()->teacher;
        $filePath   = null;
        $fileSize   = null;

        if ($request->hasFile('file') && $request->file->isValid()) {
            $filePath = $request->file('file')->store("materials/teacher-{$teacher->id}", 'private');
            $fileSize = $request->file('file')->getSize();
        }

        $material = CourseMaterial::create([
            'teacher_id'   => $teacher->id,
            'subject_id'   => $request->subject_id,
            'title'        => $request->title,
            'type'         => $request->type,
            'description'  => $request->description,
            'file_path'    => $filePath,
            'file_size'    => $fileSize,
            'video_url'    => $request->video_url,
            'external_url' => $request->external_url,
            'is_published' => $request->boolean('is_published'),
        ]);

        // Attach classes
        $material->classes()->sync($request->class_ids);

        activity()->causedBy(auth()->user())->performedOn($material)->log('Ressource créée');

        return redirect()->route('teacher.materials.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Ressource publiée.' : 'Resource published.');
    }

    /**
     * Supprimer une ressource.
     */
    public function destroy(int $id)
    {
        $material = CourseMaterial::where('teacher_id', auth()->user()->teacher?->id)->findOrFail($id);

        if ($material->file_path) {
            Storage::disk('private')->delete($material->file_path);
        }

        $material->delete();

        return back()->with('success', app()->getLocale() === 'fr' ? 'Ressource supprimée.' : 'Resource deleted.');
    }
}
