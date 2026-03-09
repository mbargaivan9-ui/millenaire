<?php

namespace App\Services;

use App\Models\DynamicBulletinStructure;
use App\Models\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;
use Exception;

/**
 * Bulletin PDF Generator Service
 * 
 * Generates professional PDF bulletins from structured data
 * Supports single and bulk PDF generation
 */
class BulletinPDFGenerator
{
    private Dompdf $dompdf;
    private DynamicBulletinStructure $structure;
    private array $paperSize = ['A4', 'letter'];
    private string $currentPaperSize = 'A4';

    public function __construct()
    {
        $this->initializeDompdf();
    }

    /**
     * Initialize Dompdf with secure options
     */
    private function initializeDompdf(): void
    {
        $options = new Options();
        $options->set([
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isHtmlSafe' => true,
            'defaultFont' => 'Arial',
            'fontSize' => 10,
            'margin_top' => 15,
            'margin_right' => 10,
            'margin_bottom' => 15,
            'margin_left' => 10,
        ]);

        $this->dompdf = new Dompdf($options);
        $this->dompdf->setPaper($this->currentPaperSize, 'portrait');
    }

    /**
     * Generate PDF for single student bulletin
     * 
     * @param DynamicBulletinStructure $structure
     * @param User $student
     * @param array $grades Subject grades
     * @return string PDF binary content
     */
    public function generateBulletinPDF(
        DynamicBulletinStructure $structure,
        User $student,
        array $grades = []
    ): string {
        try {
            $this->structure = $structure;
            $this->structure->loadMissing(['fields', 'classe']);

            // Generate HTML content
            $html = $this->generateBulletinHTML($structure, $student, $grades);

            // Load HTML into Dompdf
            $this->dompdf->loadHtml($html);
            $this->dompdf->render();

            return $this->dompdf->output();
        } catch (Exception $e) {
            throw new Exception("PDF generation failed: {$e->getMessage()}");
        }
    }

    /**
     * Generate HTML for bulletin
     */
    private function generateBulletinHTML(
        DynamicBulletinStructure $structure,
        User $student,
        array $grades = []
    ): string {
        // Get fields grouped by type
        $subjectFields = $structure->fields()->where('field_type', 'subject')->orderBy('display_order')->get();
        $calculatedFields = $structure->fields()->whereIn('field_type', ['average', 'rank', 'appreciation'])->orderBy('display_order')->get();

        // Calculate values if not provided
        if (empty($grades)) {
            $grades = $this->generateSampleGrades($subjectFields);
        }

        $calculations = $this->calculateBulletinValues($subjectFields, $grades, $structure);

        return view('bulletins.pdf.bulletin-template', [
            'structure' => $structure,
            'student' => $student,
            'grades' => $grades,
            'subjectFields' => $subjectFields,
            'calculatedFields' => $calculatedFields,
            'calculations' => $calculations,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->render();
    }

    /**
     * Generate sample grades for testing
     */
    private function generateSampleGrades($subjectFields): array
    {
        $grades = [];
        foreach ($subjectFields as $field) {
            $min = $field->min_value ?? 0;
            $max = $field->max_value ?? 20;
            $grades[$field->field_name] = rand($min * 100, $max * 100) / 100;
        }
        return $grades;
    }

    /**
     * Calculate bulletin values (average, rank, etc)
     */
    private function calculateBulletinValues(
        $subjectFields,
        array $grades,
        DynamicBulletinStructure $structure
    ): array {
        $calculations = [];

        // Get coefficient sum for weighted average
        $coefficientSum = $subjectFields->sum('coefficient') ?: 1;
        $totalPoints = 0;

        // Calculate total weighted points
        foreach ($subjectFields as $field) {
            $grade = $grades[$field->field_name] ?? 0;
            $totalPoints += $grade * $field->coefficient;
        }

        // Weighted average
        $average = $totalPoints / $coefficientSum;
        $calculations['average'] = round($average, 2);

        // Determine appreciation
        if ($average >= 16) {
            $calculations['appreciation'] = 'Excellent travail';
        } elseif ($average >= 14) {
            $calculations['appreciation'] = 'Très bien';
        } elseif ($average >= 12) {
            $calculations['appreciation'] = 'Bien';
        } elseif ($average >= 10) {
            $calculations['appreciation'] = 'Satisfaisant';
        } else {
            $calculations['appreciation'] = 'À améliorer';
        }

        return $calculations;
    }

    /**
     * Generate bulk PDFs for all students in a class
     * 
     * @param DynamicBulletinStructure $structure
     * @param array $bulkGrades [studentId => [subjectName => grade]]
     * @return array Array of [studentId => PDF binary]
     */
    public function generateBulkBulletins(
        DynamicBulletinStructure $structure,
        array $bulkGrades = []
    ): array {
        try {
            $this->structure = $structure;
            $structure->loadMissing(['classe.members', 'fields']);

            $students = $structure->classe->members()->whereHas('roles', function ($query) {
                $query->where('name', 'student');
            })->get();

            $pdfs = [];

            foreach ($students as $student) {
                $grades = $bulkGrades[$student->id] ?? [];
                $pdfContent = $this->generateBulletinPDF($structure, $student, $grades);
                
                // Store in array with student ID as key
                $filename = $this->generateFilename($structure, $student);
                $pdfs[$filename] = $pdfContent;
            }

            return $pdfs;
        } catch (Exception $e) {
            throw new Exception("Bulk PDF generation failed: {$e->getMessage()}");
        }
    }

    /**
     * Generate filename for bulletin
     */
    private function generateFilename(DynamicBulletinStructure $structure, User $student): string
    {
        $term = $structure->metadata['term'] ?? 'S1';
        $year = $structure->metadata['academic_year'] ?? date('Y');
        $class = $structure->classe->name ?? 'Unknown';
        $studentName = strtolower(str_replace([' ', '_'], '-', "{$student->first_name} {$student->last_name}"));

        return "Bulletin_{$class}_{$term}_{$year}_{$studentName}.pdf";
    }

    /**
     * Download single bulletin as PDF
     * 
     * @return response
     */
    public function downloadBulletin(DynamicBulletinStructure $structure, User $student, array $grades = []): \Symfony\Component\HttpFoundation\Response
    {
        $pdfContent = $this->generateBulletinPDF($structure, $student, $grades);
        $filename = $this->generateFilename($structure, $student);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Download bulk bulletins as ZIP
     */
    public function downloadBulkBulletins(DynamicBulletinStructure $structure, array $bulkGrades = []): \Symfony\Component\HttpFoundation\Response
    {
        $pdfs = $this->generateBulkBulletins($structure, $bulkGrades);

        if (count($pdfs) === 1) {
            $filename = array_key_first($pdfs);
            $content = reset($pdfs);

            return response($content)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        }

        // Multiple files - create ZIP
        return $this->createZipArchive($structure, $pdfs);
    }

    /**
     * Create ZIP archive of multiple PDFs
     */
    private function createZipArchive(DynamicBulletinStructure $structure, array $pdfs): \Symfony\Component\HttpFoundation\Response
    {
        $zipFile = tempnam(sys_get_temp_dir(), 'bulletins_');
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE) !== true) {
            throw new Exception('Cannot create ZIP file');
        }

        foreach ($pdfs as $filename => $content) {
            $zip->addFromString($filename, $content);
        }

        $zip->close();

        $zipContent = file_get_contents($zipFile);
        unlink($zipFile);

        $term = $structure->metadata['term'] ?? 'S1';
        $year = $structure->metadata['academic_year'] ?? date('Y');
        $class = str_slug($structure->classe->name ?? 'bulletins');
        $zipName = "Bulletins_{$class}_{$term}_{$year}.zip";

        return response($zipContent)
            ->header('Content-Type', 'application/zip')
            ->header('Content-Disposition', "attachment; filename=\"{$zipName}\"")
            ->header('Content-Length', strlen($zipContent));
    }

    /**
     * Save bulk PDFs to storage
     */
    public function saveBulletinsToStorage(
        DynamicBulletinStructure $structure,
        array $bulkGrades = []
    ): array {
        try {
            $pdfs = $this->generateBulkBulletins($structure, $bulkGrades);
            $savedFiles = [];

            $storagePath = "bulletins/{$structure->id}/" . now()->format('Y-m-d');

            foreach ($pdfs as $filename => $content) {
                $path = "{$storagePath}/{$filename}";
                \Illuminate\Support\Facades\Storage::disk('public')->put($path, $content);
                $savedFiles[$filename] = asset("storage/{$path}");
            }

            return $savedFiles;
        } catch (Exception $e) {
            throw new Exception("Failed to save bulletins: {$e->getMessage()}");
        }
    }

    /**
     * Set paper size
     */
    public function setPaperSize(string $size): self
    {
        if (!in_array($size, $this->paperSize)) {
            throw new Exception("Invalid paper size. Allowed: " . implode(', ', $this->paperSize));
        }
        $this->currentPaperSize = $size;
        $this->dompdf->setPaper($size, 'portrait');
        return $this;
    }

    /**
     * Preview bulletin as HTML
     */
    public function previewHTML(
        DynamicBulletinStructure $structure,
        User $student,
        array $grades = []
    ): string {
        return $this->generateBulletinHTML($structure, $student, $grades);
    }
}
