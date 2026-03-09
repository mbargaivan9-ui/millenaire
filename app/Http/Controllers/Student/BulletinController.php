<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Models\Mark;
use App\Services\GradeCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Student\BulletinController — Accès Bulletins Étudiant
 */
class BulletinController extends Controller
{
    public function __construct(private readonly GradeCalculationService $gradeService) {}

    /**
     * Lister tous les bulletins de l'étudiant - Affichage en lecture seule.
     */
    public function index()
    {
        $student = auth()->user()->student;
        abort_unless($student, 403, 'Aucun enregistrement étudiant trouvé.');

        $bulletins = Bulletin::where('student_id', $student->id)
            ->where('status', 'published')
            ->with('classe')
            ->orderByDesc('term')
            ->orderByDesc('sequence')
            ->paginate(10);

        return view('student.bulletin.index', compact('bulletins', 'student'));
    }

    /**
     * Afficher un bulletin complet (lecture seule pour l'étudiant).
     */
    public function show(int $id)
    {
        $student = auth()->user()->student;
        $bulletin = Bulletin::where('student_id', $student?->id)
            ->where('status', 'published')
            ->with('student.user', 'student.classe', 'marks.subject')
            ->findOrFail($id);

        abort_unless($bulletin->status === 'published', 403, 'Ce bulletin n\'est pas encore disponible.');

        $totalStudents = $bulletin->student->classe?->students()->count() ?? 0;
        $classMoyenne  = $this->gradeService->getClassAverages($bulletin->classe, $bulletin->term, $bulletin->sequence);

        return view('student.bulletin.show', compact('bulletin', 'totalStudents', 'classMoyenne'));
    }

    /**
     * Télécharger le PDF d'un bulletin (only own bulletins).
     */
    public function pdf(int $id)
    {
        $student  = auth()->user()->student;
        $bulletin = Bulletin::where('student_id', $student?->id)
            ->where('status', 'published')
            ->with('student.user', 'student.classe.headTeacher.user')
            ->findOrFail($id);

        $marks = Mark::where([
            'student_id' => $student->id,
            'class_id'   => $bulletin->class_id,
            'term'       => $bulletin->term,
            'sequence'   => $bulletin->sequence,
        ])->with('subject')->get();

        $classMoyenne  = $this->gradeService->getClassAverages($bulletin->classe, $bulletin->term, $bulletin->sequence);
        $totalStudents = $bulletin->student->classe?->students()->count() ?? 0;

        $pdf = Pdf::loadView('pdf.bulletin', compact('bulletin', 'marks', 'classMoyenne', 'totalStudents'))
            ->setPaper('a4', 'portrait');

        $filename = "bulletin-{$student->matricule}-t{$bulletin->term}-s{$bulletin->sequence}.pdf";

        return $pdf->download($filename);
    }
}
