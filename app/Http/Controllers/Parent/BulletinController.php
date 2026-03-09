<?php

/**
 * Parent\BulletinController — Consultation des bulletins (côté parent)
 *
 * @package App\Http\Controllers\Parent
 */

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Services\BulletinService;
use Barryvdh\DomPDF\Facade\Pdf;

class BulletinController extends Controller
{
    public function __construct(
        private readonly BulletinService $bulletinService
    ) {}

    /**
     * Lister tous les bulletins des enfants du parent.
     */
    public function index()
    {
        $guardian = auth()->user()->guardian;
        abort_unless($guardian, 403, app()->getLocale() === 'fr' ? 'Aucun dossier de tuteur trouvé.' : 'No guardian record found.');

        $bulletins = Bulletin::whereIn('student_id', $guardian->students()->pluck('id'))
            ->where('status', 'published')
            ->with('student.user', 'student.classe')
            ->orderByDesc('term')
            ->orderByDesc('sequence')
            ->paginate(15);

        return view('parent.bulletin.index', compact('bulletins'));
    }

    /**
     * Afficher un bulletin (lecture seule pour le parent).
     */
    public function show(int $id)
    {
        $bulletin = Bulletin::with('student.user', 'student.classe', 'marks.subject')
            ->findOrFail($id);

        // Vérifier que ce bulletin appartient à un enfant du parent connecté
        $guardian = auth()->user()->guardian;
        abort_unless(
            $guardian && $bulletin->student->guardian_id === $guardian->id,
            403,
            app()->getLocale() === 'fr' ? 'Accès non autorisé.' : 'Unauthorized.'
        );

        abort_unless($bulletin->status === 'published', 403, app()->getLocale() === 'fr' ? 'Ce bulletin n\'est pas encore disponible.' : 'This report card is not yet available.');

        $totalStudents = $bulletin->student->classe?->students()->count() ?? 0;
        $classMoyenne  = $this->bulletinService->getClassAverage($bulletin->student->classe_id, $bulletin->term, $bulletin->sequence);

        return view('parent.bulletin.show', compact('bulletin', 'totalStudents', 'classMoyenne'));
    }

    /**
     * Télécharger le bulletin en PDF.
     */
    public function pdf(int $id)
    {
        $bulletin = Bulletin::with('student.user', 'student.classe', 'marks.subject')
            ->findOrFail($id);

        $guardian = auth()->user()->guardian;
        abort_unless(
            $guardian && $bulletin->student->guardian_id === $guardian->id,
            403
        );
        abort_unless($bulletin->status === 'published', 403);

        $totalStudents = $bulletin->student->classe?->students()->count() ?? 0;
        $marks         = $bulletin->marks()->with('subject')->get();
        $classMoyenne  = number_format(
            $this->bulletinService->getClassAverage($bulletin->student->classe_id, $bulletin->term, $bulletin->sequence),
            2
        );

        $pdf = Pdf::loadView('pdf.bulletin', compact('bulletin', 'marks', 'totalStudents', 'classMoyenne'))
            ->setPaper('a4', 'portrait');

        $filename = sprintf(
            'bulletin-%s-T%d-S%d.pdf',
            str_replace(' ', '_', $bulletin->student->user->name),
            $bulletin->term,
            $bulletin->sequence
        );

        return $pdf->download($filename);
    }
}
