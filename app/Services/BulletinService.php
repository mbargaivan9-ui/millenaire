<?php

/**
 * BulletinService
 *
 * Génération PDF des bulletins, QR Code de vérification, export en masse
 * Phase 6 — Section 7.x — Système de Bulletins Scolaires
 *
 * @package App\Services
 */

namespace App\Services;

use App\Models\Bulletin;
use App\Models\Student;
use App\Models\Classe;
use App\Models\EstablishmentSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BulletinService
{
    public function __construct(
        private readonly GradeCalculationService $gradeService
    ) {}

    /**
     * Générer le PDF d'un bulletin individuel.
     *
     * @return string Path du fichier PDF généré
     */
    public function generatePDF(Bulletin $bulletin): string
    {
        $bulletin->load([
            'student.user',
            'student.classe',
            'marks.subject',
            'marks.teacher.user',
            'class',
        ]);

        $settings  = EstablishmentSetting::getInstance();
        $student   = $bulletin->student;
        $class     = $bulletin->class;
        $isAnglophone = $class?->section === 'anglophone';

        // Récupérer les données pour le PDF
        $marks    = $bulletin->marks->sortBy('subject.name');
        $moyenne  = $bulletin->moyenne;
        $rang     = $bulletin->rang;

        // Calcul moyenne et statistiques par matière
        $subjectStats = [];
        foreach ($marks as $mark) {
            $classAvg = $this->gradeService->computeSubjectClassAverage(
                $mark->subject_id, $bulletin->class_id, $bulletin->term, $bulletin->sequence
            );
            $subjectStats[$mark->subject_id] = [
                'class_avg'    => $classAvg,
                'appreciation' => $this->gradeService->suggestAppreciation($mark->score ?? 0),
                'anglophone'   => $isAnglophone
                    ? $this->gradeService->convertToAngloponeGrade(
                        $mark->score ?? 0,
                        $settings->anglophone_grading ?? 'letter'
                    )
                    : null,
            ];
        }

        // Générer le QR code de vérification
        $verifyUrl  = route('bulletin.verify', $bulletin->verification_token);
        $qrCodeSvg  = QrCode::format('svg')->size(80)->generate($verifyUrl);

        // Radar chart data pour Chart.js (SVG inline)
        $chartData = $this->buildChartData($marks);

        // Charger le template de bulletin selon la section
        $template = $isAnglophone
            ? 'pdf.bulletin-anglophone'
            : 'pdf.bulletin-francophone';

        // Fallback au template générique si le spécifique n'existe pas
        if (!view()->exists($template)) {
            $template = 'pdf.bulletin';
        }

        $pdf = Pdf::loadView($template, [
            'bulletin'     => $bulletin,
            'student'      => $student,
            'class'        => $class,
            'marks'        => $marks,
            'subjectStats' => $subjectStats,
            'moyenne'      => $moyenne,
            'rang'         => $rang,
            'settings'     => $settings,
            'qrCodeSvg'    => $qrCodeSvg,
            'verifyUrl'    => $verifyUrl,
            'chartData'    => $chartData,
            'isAnglophone' => $isAnglophone,
            'term'         => $bulletin->term,
            'sequence'     => $bulletin->sequence,
            'appreciation' => $moyenne !== null ? $this->gradeService->suggestAppreciation((float)$moyenne) : null,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'dpi'                  => 150,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false,
            'defaultFont'          => 'sans-serif',
        ]);

        // Chemin de sauvegarde
        $path = "bulletins/{$bulletin->class_id}/term{$bulletin->term}/seq{$bulletin->sequence}/{$student->matricule}.pdf";
        Storage::disk('local')->put($path, $pdf->output());

        // Mettre à jour le chemin PDF dans la BDD
        $bulletin->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Générer les bulletins PDF de toute une classe (pour le Job batch).
     *
     * @return array{generated: int, failed: int, paths: string[]}
     */
    public function exportClassBulletins(int $classId, int $term, int $sequence): array
    {
        $class    = Classe::with('students.user')->findOrFail($classId);
        $generated = 0;
        $failed    = 0;
        $paths     = [];

        foreach ($class->students as $student) {
            $bulletin = Bulletin::where([
                'student_id' => $student->id,
                'class_id'   => $classId,
                'term'       => $term,
                'sequence'   => $sequence,
            ])->first();

            if (!$bulletin || $bulletin->status !== 'published') {
                continue;
            }

            try {
                $path    = $this->generatePDF($bulletin);
                $paths[] = $path;
                $generated++;
            } catch (\Throwable $e) {
                \Log::error("[BulletinService] PDF generation failed for student {$student->id}: " . $e->getMessage());
                $failed++;
            }
        }

        return ['generated' => $generated, 'failed' => $failed, 'paths' => $paths];
    }

    /**
     * Créer ou régénérer le token de vérification QR pour un bulletin.
     */
    public function ensureVerificationToken(Bulletin $bulletin): string
    {
        if (!$bulletin->verification_token) {
            $token = Str::random(32);
            $bulletin->update(['verification_token' => $token]);
        }

        return $bulletin->verification_token;
    }

    /**
     * Créer un ZIP de tous les PDF d'une classe.
     */
    public function createClassZip(int $classId, int $term, int $sequence): ?string
    {
        $directory = "bulletins/{$classId}/term{$term}/seq{$sequence}";
        $files     = Storage::disk('local')->files($directory);

        if (empty($files)) return null;

        $zipPath = storage_path("app/exports/bulletins_class{$classId}_T{$term}S{$sequence}.zip");

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Cannot create ZIP file at {$zipPath}");
        }

        foreach ($files as $file) {
            $fullPath = Storage::disk('local')->path($file);
            $zip->addFile($fullPath, basename($file));
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Construire les données pour le graphe radar (Chart.js SVG).
     *
     * @return array{labels: string[], scores: float[], max: int}
     */
    private function buildChartData($marks): array
    {
        $labels = [];
        $scores = [];

        foreach ($marks->take(8) as $mark) {
            $labels[] = Str::limit($mark->subject?->name ?? '?', 12);
            $scores[] = (float)($mark->score ?? 0);
        }

        return [
            'labels' => $labels,
            'scores' => $scores,
            'max'    => 20,
        ];
    }

    /**
     * Transition de statut du bulletin.
     * draft → submitted → validated → published
     */
    public function transition(Bulletin $bulletin, string $newStatus, int $byUserId): void
    {
        $allowed = [
            'draft'     => 'submitted',
            'submitted' => 'validated',
            'validated' => 'published',
        ];

        if (($allowed[$bulletin->status] ?? null) !== $newStatus) {
            throw new \DomainException("Invalid status transition: {$bulletin->status} → {$newStatus}");
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'published') {
            $updates['published_at'] = now();
            $updates['published_by'] = $byUserId;

            // Ensure token exists for QR
            $this->ensureVerificationToken($bulletin);

            // Generate PDF
            $this->generatePDF($bulletin->fresh());

            // Notify parents
            app(NotificationService::class)->sendBulletinNotification($bulletin->fresh());
        }

        $bulletin->update($updates);

        activity()
            ->causedBy(\App\Models\User::find($byUserId))
            ->performedOn($bulletin)
            ->withProperties(['new_status' => $newStatus])
            ->log("Bulletin {$newStatus}");
    }
}
