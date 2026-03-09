<?php

/**
 * ExportClassBulletinsJob
 *
 * Job de queue pour la génération en masse des bulletins PDF d'une classe.
 * Dispatché via: ExportClassBulletinsJob::dispatch($classId, $term, $sequence, $requestedByUserId)
 *
 * Phase 6 — Section 7.4 — Export en masse
 *
 * @package App\Jobs
 */

namespace App\Jobs;

use App\Models\Classe;
use App\Models\User;
use App\Services\BulletinService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Cache, Storage};

class ExportClassBulletinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre de tentatives en cas d'échec.
     */
    public int $tries = 2;

    /**
     * Timeout: 10 minutes (génération PDF intensive).
     */
    public int $timeout = 600;

    public function __construct(
        public readonly int $classId,
        public readonly int $term,
        public readonly int $sequence,
        public readonly int $requestedByUserId
    ) {}

    public function handle(BulletinService $bulletinService): void
    {
        $class = Classe::findOrFail($this->classId);

        // Mettre à jour le statut en cache (progress tracking)
        $cacheKey = "export_progress_{$this->classId}_{$this->term}_{$this->sequence}";
        Cache::put($cacheKey, ['status' => 'processing', 'progress' => 0], now()->addHour());

        try {
            $result = $bulletinService->exportClassBulletins(
                $this->classId,
                $this->term,
                $this->sequence
            );

            Cache::put($cacheKey, [
                'status'    => 'generating_zip',
                'progress'  => 90,
                'generated' => $result['generated'],
                'failed'    => $result['failed'],
            ], now()->addHour());

            // Créer le ZIP
            $zipPath = $bulletinService->createClassZip($this->classId, $this->term, $this->sequence);

            Cache::put($cacheKey, [
                'status'    => 'done',
                'progress'  => 100,
                'generated' => $result['generated'],
                'failed'    => $result['failed'],
                'zip_path'  => $zipPath ? basename($zipPath) : null,
                'download_url' => $zipPath ? route('admin.bulletin.download-export', [
                    'class_id' => $this->classId,
                    'term'     => $this->term,
                    'sequence' => $this->sequence,
                ]) : null,
            ], now()->addHours(2));

            // Notifier l'utilisateur qui a lancé l'export
            $user = User::find($this->requestedByUserId);
            if ($user) {
                $user->notify(new \App\Notifications\ExportReadyNotification([
                    'class_name'  => $class->name,
                    'term'        => $this->term,
                    'sequence'    => $this->sequence,
                    'generated'   => $result['generated'],
                    'failed'      => $result['failed'],
                    'download_url' => Cache::get($cacheKey)['download_url'] ?? null,
                ]));
            }

            \Log::info("[ExportClassBulletinsJob] Class {$this->classId} T{$this->term}S{$this->sequence}: {$result['generated']} generated, {$result['failed']} failed.");

        } catch (\Throwable $e) {
            Cache::put($cacheKey, [
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ], now()->addHour());

            \Log::error("[ExportClassBulletinsJob] Failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gérer l'échec du job.
     */
    public function failed(\Throwable $exception): void
    {
        $cacheKey = "export_progress_{$this->classId}_{$this->term}_{$this->sequence}";
        Cache::put($cacheKey, [
            'status' => 'failed',
            'error'  => $exception->getMessage(),
        ], now()->addHour());

        \Log::error("[ExportClassBulletinsJob] Job failed after retries: " . $exception->getMessage());
    }
}
