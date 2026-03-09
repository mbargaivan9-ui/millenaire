<?php

/**
 * Student\CourseController
 *
 * Ressources pédagogiques & E-Learning étudiant.
 * Phase 5 — Section 5.2
 *
 * @package App\Http\Controllers\Student
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseMaterial;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Page principale E-Learning.
     */
    public function index()
    {
        $student  = auth()->user()->student;
        $classId  = $student?->class_id;

        $subjects = Subject::whereHas('classes', fn($q) => $q->where('classes.id', $classId))
            ->orderBy('name')
            ->get();

        $materials = CourseMaterial::where('is_published', true)
            ->whereHas('classes', fn($q) => $q->where('classes.id', $classId))
            ->with('subject', 'teacher.user')
            ->orderByDesc('created_at')
            ->get();

        $quizzes = Quiz::where('class_id', $classId)
            ->where('is_published', true)
            ->with('subject', 'questions', 'submissions')
            ->withCount('questions')
            ->get();

        return view('student.e-learning', compact('subjects', 'materials', 'quizzes'));
    }

    /**
     * Afficher une ressource spécifique.
     */
    public function show(int $id)
    {
        $material = CourseMaterial::where('is_published', true)
            ->with('subject', 'teacher.user')
            ->findOrFail($id);

        // Vérifier accès
        $student = auth()->user()->student;
        $hasAccess = $material->classes()->where('classes.id', $student?->class_id)->exists();
        if (!$hasAccess) abort(403);

        // Logger la consultation
        activity()
            ->causedBy(auth()->user())
            ->performedOn($material)
            ->log('Ressource consultée');

        return view('student.course-detail', compact('material'));
    }

    /**
     * Télécharger un fichier PDF.
     */
    public function download(int $id)
    {
        $material = CourseMaterial::where('is_published', true)->findOrFail($id);

        $student   = auth()->user()->student;
        $hasAccess = $material->classes()->where('classes.id', $student?->class_id)->exists();
        if (!$hasAccess) abort(403);

        if (!$material->file_path || !Storage::exists($material->file_path)) {
            abort(404, 'Fichier introuvable.');
        }

        // Logger
        activity()->causedBy(auth()->user())->performedOn($material)->log('Fichier téléchargé');

        return Storage::download($material->file_path, $material->title . '.pdf');
    }
}
