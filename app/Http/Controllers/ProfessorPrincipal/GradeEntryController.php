<?php

namespace App\Http\Controllers\ProfessorPrincipal;

use App\Http\Controllers\Controller;
use App\Models\BulletinTemplate;
use App\Models\TemplateSubjectAssignment;
use Illuminate\Support\Facades\Auth;

class GradeEntryController extends Controller
{
    /**
     * Display grade entry interface for a template
     */
    public function index(BulletinTemplate $template)
    {
        $this->authorize('manage', $template);

        $template->load([
            'classroom',
            'subjectAssignments' => fn($q) => $q->with('subject', 'teacher'),
            'studentBulletins' => fn($q) => $q->with(['student', 'grades.subject']),
        ]);

        // Check if template is published
        abort_if(!$template->is_validated, 403, 'Le modèle doit être validé avant la saisie des notes');

        $userSubject = $this->getUserSubject($template);

        return view('professor-principal.grades.entry', [
            'template' => $template,
            'userSubject' => $userSubject,
        ]);
    }

    /**
     * Get current user's assigned subject (for teachers)
     */
    private function getUserSubject(BulletinTemplate $template)
    {
        $user = Auth::user();

        // If professor principal or director, return null (can edit all)
        if (in_array($user->role, ['professor_principal', 'director'])) {
            return null;
        }

        // Otherwise, return user's assigned subject
        return TemplateSubjectAssignment::where('bulletin_template_id', $template->id)
            ->where('teacher_id', $user->id)
            ->first()?->subject;
    }

    /**
     * Display grade entry for a specific classroom
     */
    public function byClassroom($classroomId)
    {
        $user = Auth::user();

        // Get the latest active template for this classroom
        $template = BulletinTemplate::where('classroom_id', $classroomId)
            ->where('is_validated', true)
            ->latest('validated_at')
            ->first();

        abort_if(!$template, 404, 'Aucun modèle de bulletin actif trouvé');

        return $this->index($template);
    }

    /**
     * Get statistics for a template
     */
    public function getStats(BulletinTemplate $template)
    {
        $this->authorize('manage', $template);

        $bulletins = $template->studentBulletins()
            ->whereNotNull('general_average')
            ->get();

        if ($bulletins->isEmpty()) {
            return response()->json(['error' => 'Aucun bulletin avec moyennes'], 404);
        }

        $averages = $bulletins->pluck('general_average');

        return response()->json([
            'total' => $bulletins->count(),
            'completed' => $bulletins->where('status', 'complete')->count(),
            'average' => $averages->avg(),
            'min' => $averages->min(),
            'max' => $averages->max(),
            'median' => $this->calculateMedian($averages),
        ]);
    }

    /**
     * Export grades to CSV
     */
    public function exportCSV(BulletinTemplate $template)
    {
        $this->authorize('manage', $template);

        $bulletins = $template->studentBulletins()
            ->with(['student', 'grades.subject'])
            ->get();

        $csv = "Élève,Matricule,Classe,Moyenne Générale,Rang,Appréciation\n";

        foreach ($bulletins as $bulletin) {
            $csv .= sprintf(
                '"%s","%s","%s",%.2f,%d,"%s"' . "\n",
                $bulletin->student->name,
                $bulletin->student->matricule ?? '',
                $bulletin->student->classroom->name,
                $bulletin->general_average ?? 0,
                $bulletin->class_rank ?? 0,
                $bulletin->appreciation ?? ''
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="grades_' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Lock multiple bulletins
     */
    public function lockMultiple(BulletinTemplate $template)
    {
        $this->authorize('manage', $template);

        $bulletinIds = request()->input('bulletin_ids', []);

        if (empty($bulletinIds)) {
            return response()->json(['error' => 'Aucun bulletin sélectionné'], 400);
        }

        $locked = $template->studentBulletins()
            ->whereIn('id', $bulletinIds)
            ->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => Auth::id(),
            ]);

        return response()->json([
            'success' => true,
            'locked' => $locked,
            'message' => "$locked bulletin(s) verrouillé(s)",
        ]);
    }

    /**
     * Calculate median
     */
    private function calculateMedian($values)
    {
        $sorted = $values->sort()->values();
        $count = $sorted->count();
        $middle = intdiv($count, 2);

        if ($count % 2 === 0) {
            return ($sorted[$middle - 1] + $sorted[$middle]) / 2;
        }

        return $sorted[$middle];
    }
}
