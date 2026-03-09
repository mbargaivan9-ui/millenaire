<?php

/**
 * Student\QuizController
 *
 * Quiz interactifs étudiant.
 * Phase 5 — Section 5.2.2
 *
 * @package App\Http\Controllers\Student
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /** Liste des quiz disponibles */
    public function index()
    {
        $student = auth()->user()->student;
        $quizzes = Quiz::where('class_id', $student?->class_id)
            ->where('is_published', true)
            ->with('subject', 'questions')
            ->withCount('questions')
            ->get();

        return view('student.e-learning', ['quizzes' => $quizzes, 'materials' => collect(), 'subjects' => collect()]);
    }

    /** Démarrer un quiz */
    public function start(int $id)
    {
        $student = auth()->user()->student;
        $quiz    = Quiz::where('class_id', $student?->class_id)
            ->where('is_published', true)
            ->with('questions')
            ->findOrFail($id);

        // Vérifier si déjà soumis
        $existing = QuizSubmission::where(['quiz_id' => $id, 'student_id' => $student->id])->first();
        if ($existing) {
            return redirect()->route('student.quiz.result', $existing->id);
        }

        // Vérifier disponibilité
        if ($quiz->available_from && now()->lt($quiz->available_from)) abort(403, 'Quiz non disponible.');
        if ($quiz->available_until && now()->gt($quiz->available_until)) abort(403, 'Quiz expiré.');

        return view('student.quiz-take', compact('quiz'));
    }

    /** Soumettre un quiz */
    public function submit(Request $request, int $id): JsonResponse
    {
        $student = auth()->user()->student;
        $quiz    = Quiz::with('questions')->findOrFail($id);

        // Empêcher double soumission
        $existing = QuizSubmission::where(['quiz_id' => $id, 'student_id' => $student->id])->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Déjà soumis', 'submission_id' => $existing->id]);
        }

        $answers     = $request->input('answers', []);
        $totalPoints = 0;
        $earnedPoints = 0;
        $gradedAnswers = [];

        foreach ($quiz->questions as $question) {
            $studentAnswer = $answers[$question->id] ?? null;
            $points        = $question->points ?? 1;
            $totalPoints  += $points;

            // Évaluation automatique MCQ et True/False
            $isCorrect = false;
            if (in_array($question->type, ['multiple_choice', 'true_false'])) {
                $isCorrect = (string)$studentAnswer === (string)$question->correct_answer;
                if ($isCorrect) $earnedPoints += $points;
            }
            // Short answer: marqué manuellement par l'enseignant (is_graded = false)

            $gradedAnswers[$question->id] = [
                'answer'     => $studentAnswer,
                'is_correct' => $isCorrect,
                'points'     => $isCorrect ? $points : 0,
            ];
        }

        $hasShortAnswer = $quiz->questions->where('type', 'short_answer')->isNotEmpty();

        $submission = QuizSubmission::create([
            'quiz_id'      => $id,
            'student_id'   => $student->id,
            'answers'      => $gradedAnswers,
            'score'        => $earnedPoints,
            'total_points' => $totalPoints,
            'is_graded'    => !$hasShortAnswer,
            'submitted_at' => now(),
        ]);

        return response()->json([
            'success'       => true,
            'submission_id' => $submission->id,
            'score'         => $earnedPoints,
            'total'         => $totalPoints,
            'percentage'    => $totalPoints > 0 ? round($earnedPoints / $totalPoints * 100) : 0,
            'passed'        => $totalPoints > 0 && ($earnedPoints / $totalPoints * 100) >= ($quiz->pass_score ?? 50),
        ]);
    }

    /** Voir le résultat d'un quiz */
    public function result(int $submissionId)
    {
        $student    = auth()->user()->student;
        $submission = QuizSubmission::where('student_id', $student->id)
            ->with('quiz.questions')
            ->findOrFail($submissionId);

        return view('student.quiz-result', compact('submission'));
    }
}
