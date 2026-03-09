<?php

namespace App\Services;

use App\Models\Bulletin;
use App\Models\Mark;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

/**
 * BulletinPdfService — Génération PDF Bulletins avec QR Code
 * 
 * Phase 6 — Bulletins
 * Gère:
 * - Génération PDF avec tous les éléments (logo, photo étudiant, tableau notes, graphiques, QR code)
 * - QR code pointant vers /bulletin/verify/{token}
 * - Signature numérique du directeur
 */
class BulletinPdfService
{
    public function __construct(
        private readonly GradeCalculationService $gradeService
    ) {}

    /**
     * Générer le PDF complet d'un bulletin (avec QR code).
     * 
     * @param Bulletin $bulletin
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(Bulletin $bulletin)
    {
        // Charger les données nécessaires
        $student      = $bulletin->student;
        $classe       = $student->classe;
        $marks        = $bulletin->marks()->with('subject')->get();
        $totalStudents = $classe?->students()->count() ?? 0;
        
        // Calculer les moyennes
        $classAverages = $this->gradeService->getClassAverages($classe, $bulletin->term, $bulletin->sequence);
        $classMoyenne  = !empty($classAverages) ? array_sum($classAverages) / count($classAverages) : null;
        
        // Générer le QR code en tant qu'image base64
        $qrCodeData = $this->generateQrCodeBase64($bulletin);
        
        // Charger la signature du directeur depuis les paramètres
        $settings = \App\Models\EstablishmentSetting::getInstance();
        $directorSignature = $settings->director_signature_path 
            ? Storage::disk('public')->url($settings->director_signature_path)
            : null;
        
        // Préparer les données pour la vue
        $data = [
            'bulletin'          => $bulletin,
            'student'           => $student,
            'classe'            => $classe,
            'marks'             => $marks,
            'totalStudents'     => $totalStudents,
            'classMoyenne'      => $classMoyenne,
            'qrCodeData'        => $qrCodeData,
            'directorSignature' => $directorSignature,
            'establishmentName' => $settings->name ?? 'Établissement',
            'establishmentLogo' => $settings->logo ? Storage::disk('public')->url($settings->logo) : null,
        ];
        
        // Générer le PDF
        $pdf = Pdf::loadView('pdf.bulletin', $data)
            ->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Télécharger le PDF d'un bulletin.
     */
    public function downloadPdf(Bulletin $bulletin)
    {
        $pdf = $this->generatePdf($bulletin);
        
        $filename = sprintf(
            'bulletin-%s-t%d-s%d.pdf',
            str_slug($bulletin->student->user->name ?? 'etudiant'),
            $bulletin->term,
            $bulletin->sequence
        );
        
        return $pdf->download($filename);
    }

    /**
     * Générer le QR code en tant qu'image base64 (pour insérer dans le PDF).
     * 
     * Le QR code pointe vers : /bulletin/verify/{token}
     */
    private function generateQrCodeBase64(Bulletin $bulletin): string
    {
        $verifyUrl = route('bulletin.verify', $bulletin->verification_token);
        
        // Générer le QR code
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(0)
            ->generate($verifyUrl);
        
        // Convertir en base64 pour insérer dans le PDF
        return 'data:image/png;base64,' . base64_encode($qrCode);
    }

    /**
     * Exporter tous les bulletins d'une classe en ZIP.
     * Utilisé par le Job ExportClassBulletinsJob.
     */
    public function exportClassBulletinsAsZip(int $classId, int $termOptional = null, int $sequenceOptional = null)
    {
        $bulletins = Bulletin::where('class_id', $classId)
            ->where('status', 'published')
            ->with('student.user')
            ->get();
        
        if ($termOptional !== null) {
            $bulletins = $bulletins->where('term', $termOptional);
        }
        
        if ($sequenceOptional !== null) {
            $bulletins = $bulletins->where('sequence', $sequenceOptional);
        }
        
        // Créer un dossier temporaire
        $zipPath = storage_path('app/temp_exports/bulletins_' . time() . '.zip');
        
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }
        
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        
        foreach ($bulletins as $bulletin) {
            $pdf = $this->generatePdf($bulletin);
            
            $filename = sprintf(
                'bulletin-%s-t%d-s%d.pdf',
                str_slug($bulletin->student->user->name ?? 'etudiant'),
                $bulletin->term,
                $bulletin->sequence
            );
            
            // Ajouter le PDF au ZIP
            $zip->addFromString($filename, $pdf->output());
        }
        
        $zip->close();
        
        return $zipPath;
    }
}
