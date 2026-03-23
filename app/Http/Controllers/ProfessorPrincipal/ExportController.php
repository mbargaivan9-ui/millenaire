<?php

namespace App\Http\Controllers\ProfessorPrincipal;

use App\Http\Controllers\Controller;
use App\Models\BulletinTemplate;
use App\Models\StudentBulletin;
use App\Services\BulletinExportService;
use App\Jobs\ExportBulletinsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    protected BulletinExportService $exportService;

    public function __construct(BulletinExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Show export options form
     */
    public function showForm(BulletinTemplate $template)
    {
        $this->authorize('export', $template);

        $bulletins = $template->studentBulletins()
            ->with('student')
            ->orderBy('id')
            ->get();

        return view('professor-principal.export.form', [
            'template' => $template,
            'bulletins' => $bulletins,
        ]);
    }

    /**
     * Start export job (async)
     */
    public function startExport(Request $request, BulletinTemplate $template)
    {
        $this->authorize('export', $template);

        $validated = $request->validate([
            'export_type' => 'required|in:classroom,selected',
            'bulletin_ids' => 'nullable|array|min:1',
            'bulletin_ids.*' => 'integer|exists:student_bulletins,id',
        ]);

        try {
            // Verify selected bulletins belong to this template
            if ($validated['export_type'] === 'selected' && !empty($validated['bulletin_ids'])) {
                $count = StudentBulletin::whereIn('id', $validated['bulletin_ids'])
                    ->where('bulletin_template_id', $template->id)
                    ->count();

                if ($count !== count($validated['bulletin_ids'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Certains bulletins sélectionnés sont invalides',
                    ], 422);
                }
            }

            // Dispatch async job
            $job = new ExportBulletinsJob(
                $template,
                $validated['export_type'],
                $validated['bulletin_ids'] ?? null,
                Auth::id()
            );

            dispatch($job);

            return response()->json([
                'success' => true,
                'message' => 'Export lancé, veuillez patienter...',
                'status_url' => route('prof-principal.export.status', $template),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get export progress status
     */
    public function getStatus(BulletinTemplate $template)
    {
        $this->authorize('export', $template);

        $cacheKey = "export_progress_{$template->id}_" . Auth::id();
        $progress = cache()->get($cacheKey);

        return response()->json(
            $progress ?? [
                'status' => 'pending',
                'message' => 'En attente...',
                'percentage' => 0,
            ]
        );
    }

    /**
     * Download export file
     */
    public function download(Request $request, BulletinTemplate $template, $filename)
    {
        $this->authorize('export', $template);

        // Security: validate filename format and prevent directory traversal
        if (!preg_match('/^(bulletin|bulletins)_[a-z0-9_\-]+\.(pdf|zip)$/i', $filename)) {
            abort(400, 'Invalid filename');
        }

        // Check file exists
        if (!$this->exportService->fileExists($filename)) {
            abort(404, 'File not found or expired');
        }

        // Log download
        activity()
            ->performedOn($template)
            ->withProperties(['filename' => $filename])
            ->log('Bulletin export downloaded');

        // Serve download
        return $this->exportService->streamDownload($filename);
    }

    /**
     * Export single bulletin (synchronous, smaller operation)
     */
    public function exportSingle(StudentBulletin $bulletin)
    {
        $this->authorize('export', $bulletin);

        try {
            $filename = $this->exportService->generateFilename($bulletin);
            $path = $this->exportService->exportSingleBulletinToPDF($bulletin);

            // Log activity
            activity()
                ->performedOn($bulletin)
                ->withProperties(['filename' => $filename])
                ->log('Single bulletin exported to PDF');

            return $this->exportService->streamDownload(basename($path));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get classroom export statistics
     */
    public function getExportStats(BulletinTemplate $template)
    {
        $this->authorize('export', $template);

        $bulletins = $template->studentBulletins()->count();
        $completedBulletins = $template->studentBulletins()->where('status', 'complete')->count();

        // Estimate export size
        $estimatedSize = $completedBulletins * 0.5; // Rough estimate: 500KB per PDF

        return response()->json([
            'total_bulletins' => $bulletins,
            'completed_bulletins' => $completedBulletins,
            'export_ready' => $completedBulletins > 0,
            'estimated_size_mb' => round($estimatedSize / 1024, 2),
            'message' => $completedBulletins > 0
                ? "$completedBulletins bulletins prêts à exporter"
                : 'Aucun bulletin complété',
        ]);
    }

    /**
     * Cleanup old exports (for automated maintenance)
     */
    public function cleanup()
    {
        // This should be called by an admin only
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        try {
            $deleted = $this->exportService->cleanupOldExports(24); // Delete files older than 24 hours

            return response()->json([
                'success' => true,
                'message' => "$deleted old export files deleted",
                'deleted_count' => $deleted,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
