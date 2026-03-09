<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizSubmission;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Teacher\QuizController — Gestion Quiz par l'Enseignant
 *
 * Phase 5 — Section 5.2.2
 * Créer quiz, ajouter questions MCQ/Vrai-Faux/Réponse courte
 * Voir résultats et corriger manuellement les réponses ouvertes
 */
class QuizController extends Controller
{
    /**
     * Liste des quiz créés par l'enseignant.
     */
    public function index()
    {
        $teacher = auth()->user()->teacher;

        $quizzes = Quiz::where('teacher_id', $teacher?->id)
            ->with('subject', 'classe')
            ->withCount(['questions', 'submissions'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('teacher.quiz.index', compact('quizzes'));
    }

    /**
     * Formulaire création quiz.
     */
    public function create()
    {
        $teacher  = auth()->user()->teacher;
        $subjects = Subject::whereHas('classSubjectTeachers', fn($q) => $q->where('teacher_id', $teacher?->id))->get();
        $classes  = Classe::whereHas('classSubjectTeachers', fn($q) => $q->where('teacher_id', $teacher?->id))->get();

        return view('teacher.quiz.create', compact('subjects', 'classes'));
    }

    /**
     * Enregistrer un nouveau quiz.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string|max:1000',
            'subject_id'         => 'required|exists:subjects,id',
            'class_id'           => 'required|exists:classes,id',
            'time_limit_minutes' => 'required|integer|min:5|max:180',
            'pass_score'         => 'nullable|integer|min:0|max:100',
            'available_from'     => 'nullable|date',
            'available_until'    => 'nullable|date|after:available_from',
            'is_published'       => 'boolean',
        ]);

        $quiz = Quiz::create(array_merge($data, [
            'teacher_id'   => auth()->user()->teacher?->id,
            'is_published' => $request->boolean('is_published'),
        ]));

        activity()->causedBy(auth()->user())->performedOn($quiz)->log('Quiz créé');

        return redirect()->route('teacher.quiz.questions', $quiz->id)
            ->with('success', app()->getLocale() === 'fr' ? 'Quiz créé. Ajoutez maintenant des questions.' : 'Quiz created. Add questions now.');
    }

    /**
     * Interface ajout de questions (AJAX).
     */
    public function questions(int $id)
    {
        $quiz = Quiz::where('teacher_id', auth()->user()->teacher?->id)
            ->with('questions')
            ->findOrFail($id);

        return view('teacher.quiz.questions', compact('quiz'));
    }

    /**
     * Ajouter une question (AJAX POST).
     */
    public function addQuestion(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'question'       => 'required|string',
            'type'           => 'required|in:multiple_choice,true_false,short_answer',
            'options'        => 'nullable|array',
            'correct_answer' => 'nullable|string',
            'points'         => 'required|integer|min:1|max:100',
        ]);

        $quiz = Quiz::where('teacher_id', auth()->user()->teacher?->id)->findOrFail($id);

        $question = $quiz->questions()->create([
            'question'       => $request->question,
            'type'           => $request->type,
            'options'        => $request->options ? json_encode($request->options) : null,
            'correct_answer' => $request->correct_answer,
            'points'         => $request->points,
            'sort_order'     => $quiz->questions()->count() + 1,
        ]);

        return response()->json(['success' => true, 'question_id' => $question->id]);
    }

    /**
     * Voir les résultats d'un quiz.
     */
    public function results(int $id)
    {
        $quiz = Quiz::where('teacher_id', auth()->user()->teacher?->id)
            ->with('questions')
            ->withCount('submissions')
            ->findOrFail($id);

        $submissions = QuizSubmission::where('quiz_id', $id)
            ->with('student.user')
            ->orderByDesc('score')
            ->get();

        return view('teacher.quiz.results', compact('quiz', 'submissions'));
    }

    /**
     * Corriger manuellement une réponse courte.
     */
    public function gradeSubmission(Request $request, int $submissionId): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|integer',
            'points'      => 'required|integer|min:0',
            'feedback'    => 'nullable|string|max:500',
        ]);

        $submission = QuizSubmission::where('quiz_id', function($q) {
            $q->whereHas('quiz', fn($qq) => $qq->where('teacher_id', auth()->user()->teacher?->id));
        })->findOrFail($submissionId);

        $answers = $submission->answers ?? [];
        if (isset($answers[$request->question_id])) {
            $answers[$request->question_id]['points']   = $request->points;
            $answers[$request->question_id]['feedback'] = $request->feedback;
            $answers[$request->question_id]['graded']   = true;
        }

        // Recalculate total score
        $newScore = collect($answers)->sum('points');
        $allGraded = collect($answers)->every(fn($a) => $a['graded'] ?? false);

        $submission->update([
            'answers'   => $answers,
            'score'     => $newScore,
            'is_graded' => $allGraded,
        ]);

        return response()->json(['success' => true, 'new_score' => $newScore]);
    }

    /**
     * Publier un quiz (le rendre accessible aux élèves).
     */
    public function publish(int $id)
    {
        $teacher = auth()->user()->teacher;
        $quiz    = \App\Models\Quiz::where('teacher_id', $teacher->id)->findOrFail($id);

        $quiz->update(['is_published' => true, 'published_at' => now()]);

        return back()->with('success', app()->getLocale() === 'fr' ? 'Quiz publié.' : 'Quiz published.');
    }
}
