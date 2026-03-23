<?php

namespace App\Jobs\Bulletin;

use App\Models\SmartBulletinTemplate;
use App\Services\Bulletin\AiTemplateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * AnalyzeBulletinWithAI
 *
 * Pipeline :
 * 1. Récupérer le fichier uploadé (image ou PDF converti en image)
 * 2. Envoyer à l'API Claude Vision pour extraction JSON
 * 3. Générer le template HTML/CSS cloné
 * 4. Sauvegarder dans smart_bulletin_templates
 * 5. Notifier le prof principal (WebSocket Reverb)
 *
 * Queue: 'ai-processing' (dédié pour les tâches IA longues)
 */
class AnalyzeBulletinWithAI implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        private readonly int $templateId
    ) {
        $this->onQueue('ai-processing');
    }

    public function handle(AiTemplateService $aiService): void
    {
        $template = SmartBulletinTemplate::findOrFail($this->templateId);

        $template->update(['ai_status' => SmartBulletinTemplate::AI_PROCESSING]);

        try {
            // 1. Résoudre le chemin image
            $imagePath = $this->resolveImagePath($template);

            // 2. Analyser avec l'IA
            $structure = $aiService->analyzeBulletinImage($imagePath);

            // 3. Générer le HTML
            $html = $aiService->generateTemplateHtml($structure, $structure['layout'] ?? 'portrait');

            // 4. Sauvegarder
            $template->update([
                'ai_status'         => SmartBulletinTemplate::AI_COMPLETED,
                'ai_processed_at'   => now(),
                'parsed_structure'  => $structure,
                'template_html'     => $html,
                'layout'            => $structure['layout'] ?? 'portrait',
                'columns_count'     => $structure['columns_count'] ?? 1,
            ]);

            // 5. Notifier le prof en temps réel via Reverb
            $this->notifyCompletion($template);

            Log::info('AnalyzeBulletinWithAI: completed', ['template_id' => $this->templateId]);

        } catch (\Throwable $e) {
            Log::error('AnalyzeBulletinWithAI failed', [
                'template_id' => $this->templateId,
                'error'       => $e->getMessage(),
            ]);

            $template->update([
                'ai_status'       => SmartBulletinTemplate::AI_FAILED,
                'ai_error_message'=> $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }

    private function resolveImagePath(SmartBulletinTemplate $template): string
    {
        $filePath = Storage::disk('private')->path($template->original_file_path);

        // Si PDF → convertir en image via intervention/image ou spatie/pdf-to-image
        if ($template->original_file_type === 'pdf') {
            $imagePath = $this->convertPdfToImage($filePath);
        } else {
            $imagePath = $filePath;
        }

        if (!file_exists($imagePath)) {
            throw new \RuntimeException("Image introuvable : {$imagePath}");
        }

        return $imagePath;
    }

    private function convertPdfToImage(string $pdfPath): string
    {
        $outputPath = str_replace('.pdf', '_page1.jpg', $pdfPath);

        // Utiliser Ghostscript si disponible
        if (exec('which gs')) {
            exec("gs -dNOPAUSE -dBATCH -sDEVICE=jpeg -r150 -dFirstPage=1 -dLastPage=1 -sOutputFile={$outputPath} {$pdfPath} 2>/dev/null");
        } elseif (class_exists('Spatie\PdfToImage\Pdf')) {
            $pdf = new \Spatie\PdfToImage\Pdf($pdfPath);
            $pdf->saveImage($outputPath);
        } else {
            throw new \RuntimeException('Aucun outil de conversion PDF→Image disponible (gs ou spatie/pdf-to-image requis).');
        }

        return $outputPath;
    }

    private function notifyCompletion(SmartBulletinTemplate $template): void
    {
        try {
            // Broadcast via Laravel Reverb (WebSocket)
            broadcast(new \App\Events\Bulletin\AiAnalysisCompleted($template));
        } catch (\Throwable $e) {
            // Non bloquant : la notification temps réel est optionnelle
            Log::warning('Broadcast failed', ['error' => $e->getMessage()]);
        }
    }
}
