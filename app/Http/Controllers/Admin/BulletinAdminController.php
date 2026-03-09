<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Services\BulletinService;
use Illuminate\Http\Request;

/**
 * Admin\BulletinAdminController — Validation et Export Bulletins
 *
 * Phase 6 — Bulletins
 * Valider, rejeter, exporter bulletins soumis par Prof Principal
 */
class BulletinAdminController extends Controller
{
    public function __construct(private readonly BulletinService $bulletinService) {}

    public function index(Request $request)
    {
        $query = Bulletin::with('student.user', 'student.classe')
            ->orderByDesc('updated_at');

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('class_id')) $query->where('class_id', $request->class_id);

        $bulletins = $query->paginate(25)->withQueryString();
        $classes   = Classe::orderBy('name')->get();

        return view('admin.bulletins.index', compact('bulletins', 'classes'));
    }

    /**
     * Valider un bulletin soumis.
     */
    public function validate(int $id)
    {
        $bulletin = Bulletin::findOrFail($id);
        $bulletin->update([
            'status'       => 'published',
            'published_at' => now(),
            'validated_by' => auth()->id(),
        ]);

        // Notify student and parent
        $this->bulletinService->notifyBulletinPublished($bulletin);

        activity()->causedBy(auth()->user())->performedOn($bulletin)->log('Bulletin validé');

        return back()->with('success', app()->getLocale() === 'fr' ? 'Bulletin validé et publié.' : 'Bulletin validated and published.');
    }

    /**
     * Afficher un bulletin
     */
    public function show(int $id)
    {
        $bulletin = Bulletin::with('student.user', 'student.classe', 'marks.subject')->findOrFail($id);
        return view('admin.bulletin.show', compact('bulletin'));
    }

    /**
     * Publier un bulletin validé (visible aux parents/élèves)
     */
    public function publish(int $id)
    {
        $bulletin = Bulletin::findOrFail($id);
        $bulletin->update([
            'status'       => 'published',
            'published_at' => now(),
            'published_by' => auth()->id(),
        ]);

        // Notify student and parent
        $this->bulletinService->notifyBulletinPublished($bulletin);

        activity()->causedBy(auth()->user())->performedOn($bulletin)->log('Bulletin publié');

        return back()->with('success', app()->getLocale() === 'fr' ? 'Bulletin publié avec succès.' : 'Bulletin published successfully.');
    }
    {
        $bulletin = Bulletin::findOrFail($id);
        $bulletin->update(['status' => 'draft', 'rejection_reason' => $request->reason]);

        activity()->causedBy(auth()->user())->performedOn($bulletin)->log('Bulletin rejeté');

        return back()->with('success', 'Bulletin retourné pour correction.');
    }

    /**
     * Exporter tous les bulletins d'une classe en PDF.
     */
    public function exportClass(int $classId)
    {
        \App\Jobs\ExportClassBulletinsJob::dispatch($classId, auth()->id());

        return back()->with('success', app()->getLocale() === 'fr'
            ? 'Export lancé. Vous recevrez une notification quand le fichier est prêt.'
            : 'Export started. You will be notified when ready.');
    }
}
