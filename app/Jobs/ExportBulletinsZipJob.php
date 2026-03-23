<?php

namespace App\Jobs\Bulletin;

use App\Models\BulletinPdfExport;
use App\Models\SmartBulletin;
use App\Models\User;
use App\Notifications\Bulletin\ExportReadyNotification;
use App\Services\Bulletin\BulletinPdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use ZipArchive;

/**
 * ExportBulletinsZipJob
 *
 * Génère tous les PDFs d'une classe en ZIP et notifie le demandeur.
 * Queue dédiée : 'pdf-exports'
 *
 * Dispatche via:
 *   ExportBulletinsZipJob::dispatch($export)->onQueue('pdf-exports');
 */
class ExportBulletinsZipJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 600; // 10 minutes

    public function __construct(
        private readonly int $exportId
    ) {
        $this->onQueue('pdf-exports');
    }

    public function handle(BulletinPdfService $pdfService): void
    {
        /** @var BulletinPdfExport $export */
        $export = BulletinPdfExport::findOrFail($this->exportId);
        $export->update(['status' => BulletinPdfExport::STATUS_PROCESSING]);

        try {
            $bulletins = SmartBulletin::forClass($export->class_id)
                ->forTerm($export->term, $export->academic_year)
                ->with(['student', 'grades.subject'])
                ->get();

            $export->update(['total_bulletins' => $bulletins->count()]);

            // Créer le ZIP
            $zipFilename = "bulletins_{$export->class_id}_{$export->term}T_{$export->academic_year}_" . now()->format('Ymd_His') . ".zip";
            $zipPath     = "exports/zip/{$zipFilename}";
            $zipFullPath = Storage::disk('private')->path($zipPath);

            @mkdir(dirname($zipFullPath), 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException("Impossible de créer le ZIP : {$zipFullPath}");
            }

            $processed = 0;
            foreach ($bulletins as $bulletin) {
                try {
                    $pdfContent = $pdfService->generate($bulletin);
                    $filename   = $pdfService->buildFilename($bulletin);
                    $zip->addFromString($filename, $pdfContent);
                    $processed++;
                    $export->update(['processed_bulletins' => $processed]);
                } catch (\Throwable $e) {
                    Log::warning("ExportBulletinsZipJob: skipped bulletin {$bulletin->id}", ['error' => $e->getMessage()]);
                }
            }

            $zip->close();

            // Générer une URL signée valide 1h
            $signedUrl = URL::temporarySignedRoute(
                'bulletin.download-zip',
                now()->addHour(),
                ['export' => $export->id]
            );

            $export->update([
                'status'                => BulletinPdfExport::STATUS_COMPLETED,
                'zip_path'              => $zipPath,
                'signed_download_url'   => $signedUrl,
                'signed_url_expires_at' => now()->addHour(),
                'delete_after'          => now()->addDay(),
                'completed_at'          => now(),
            ]);

            // Notifier le demandeur
            $requester = User::find($export->requested_by);
            $requester?->notify(new ExportReadyNotification($export));

        } catch (\Throwable $e) {
            Log::error('ExportBulletinsZipJob failed', [
                'export_id' => $this->exportId,
                'error'     => $e->getMessage(),
            ]);

            $export->update([
                'status'        => BulletinPdfExport::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }
}
