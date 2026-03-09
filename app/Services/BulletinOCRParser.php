<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class BulletinOCRParser
{
    /**
     * API configuration
     */
    protected string $ocrProvider = 'ocr-space'; // 'ocr-space', 'tesseract', 'google-vision'
    protected string $ocrApiKey = '';
    protected bool $useOfflineOcr = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ocrApiKey = config('services.ocr_space.key', 'K87899142186');
        $this->useOfflineOcr = config('services.ocr_space.offline', false);
    }

    /**
     * Parse a file (Image, PDF) and extract text
     * 
     * @param  string $filePath Path to the file in storage
     * @return array Extracted OCR data with structure
     */
    public function parseFile(string $filePath): array
    {
        try {
            // Check file exists
            if (!Storage::disk('public')->exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }

            // Get file info
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $fileSize = Storage::disk('public')->size($filePath);

            // Validate file
            $this->validateFile($extension, $fileSize);

            // Get full path for processing
            $fullPath = Storage::disk('public')->path($filePath);

            Log::info('OCR Processing started', [
                'file' => $filePath,
                'size' => $fileSize,
                'extension' => $extension
            ]);

            // Extract text using OCR
            $extractedText = $this->extractText($fullPath, $extension);

            // Parse structured data from extracted text
            return [
                'raw_text' => $extractedText,
                'subjects' => $this->extractSubjects($extractedText),
                'coefficients' => $this->extractCoefficients($extractedText),
                'metadata' => $this->extractMetadata($extractedText),
                'table_data' => $this->detectTableStructure($extractedText),
                'success' => true,
            ];

        } catch (Exception $e) {
            Log::error('OCR Processing failed', [
                'file' => $filePath,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage(),
                'success' => false,
            ];
        }
    }

    /**
     * Extract text from file using OCR API
     * 
     * @param  string $fullPath
     * @param  string $extension
     * @return string Extracted text
     */
    protected function extractText(string $fullPath, string $extension): string
    {
        // For now, use OCR.space API (free tier available)
        if ($this->ocrProvider === 'ocr-space') {
            return $this->parseWithOcrSpace($fullPath);
        }

        // Fallback to simple file reading for testing
        return $this->parseWithFallback($fullPath);
    }

    /**
     * Parse using OCR.space API
     * 
     * @param  string $fullPath
     * @return string
     */
    protected function parseWithOcrSpace(string $fullPath): string
    {
        try {
            $response = Http::timeout(30)
                ->post('https://api.ocr.space/parse', [
                    'apikey' => $this->ocrApiKey,
                    'filetype' => pathinfo($fullPath, PATHINFO_EXTENSION) === 'pdf' ? 'PDF' : 'image',
                    'isOverlayRequired' => false,
                    'language' => 'fre', // French language
                ], ['file' => fopen($fullPath, 'r')]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['ParsedText'] ?? '';
            }

            Log::warning('OCR.space API failed', ['status' => $response->status()]);
            return '';

        } catch (Exception $e) {
            Log::error('OCR.space API error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Fallback parsing for testing/offline mode
     * 
     * @param  string $fullPath
     * @return string
     */
    protected function parseWithFallback(string $fullPath): string
    {
        // For testing, return sample bulletin structure
        return $this->generateSampleBulletinText();
    }

    /**
     * Extract subjects from OCR text
     * Common French subject names to look for
     */
    protected function extractSubjects(string $text): array
    {
        $subjects = [];
        $commonSubjects = [
            'Français' => 'French',
            'Mathématiques' => 'Mathematics',
            'Maths' => 'Mathematics',
            'Anglais' => 'English',
            'Espagnol' => 'Spanish',
            'Allemand' => 'German',
            'Sciences' => 'Sciences',
            'Physique' => 'Physics',
            'Chimie' => 'Chemistry',
            'Histoire' => 'History',
            'Géographie' => 'Geography',
            'Histoire-Géographie' => 'History-Geography',
            'Éducation Physique' => 'PE',
            'EPS' => 'PE',
            'Technologie' => 'Technology',
            'Informatique' => 'IT',
            'Arts Plastiques' => 'Arts',
            'Art' => 'Arts',
            'Musique' => 'Music',
            'SVT' => 'Life Sciences',
            'Sciences de la Vie' => 'Life Sciences',
        ];

        foreach ($commonSubjects as $frenchName => $englishName) {
            if (stripos($text, $frenchName) !== false) {
                $subjects[] = [
                    'name' => $frenchName,
                    'english_name' => $englishName,
                    'found' => true,
                ];
            }
        }

        return $subjects;
    }

    /**
     * Extract coefficients from text
     */
    protected function extractCoefficients(string $text): array
    {
        $coefficients = [];

        // Look for patterns like "coef 2", "coeff: 3", "coefficient: 4"
        $pattern = '/(?:coef|coefficient)[:\s]+(\d+(?:[.,]\d+)?)/i';
        preg_match_all($pattern, $text, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $coef) {
                $normalizedCoef = (int) str_replace(',', '.', $coef);
                $coefficients[] = $normalizedCoef;
            }
        }

        return array_unique($coefficients);
    }

    /**
     * Extract metadata (academic year, term, etc)
     */
    protected function extractMetadata(string $text): array
    {
        $metadata = [];

        // Academic year pattern: "2024/2025" or "Année 2024-2025"
        if (preg_match('/(\d{4})[\/-](\d{4})/', $text, $matches)) {
            $metadata['academic_year'] = "{$matches[1]}/{$matches[2]}";
        }

        // Semester pattern
        if (preg_match('/(?:semestre|trimestre|term)\s+([1-3])/i', $text, $matches)) {
            $metadata['term'] = "T{$matches[1]}";
        } else {
            $metadata['term'] = 'T1'; // Default to first term
        }

        // Period type
        if (preg_match('/(?:premier|first)\s+(?:semestre|trimestre)/i', $text)) {
            $metadata['period_type'] = '1er';
        } elseif (preg_match('/(?:deuxième|second|2nd)\s+(?:semestre|trimestre)/i', $text)) {
            $metadata['period_type'] = '2nd';
        } elseif (preg_match('/(?:troisième|third|3rd)\s+(?:semestre|trimestre)/i', $text)) {
            $metadata['period_type'] = '3e';
        } else {
            $metadata['period_type'] = null;
        }

        return $metadata;
    }

    /**
     * Detect table structure in OCR text
     * Returns info about detected tables
     */
    protected function detectTableStructure(string $text): array
    {
        // Simple detection: look for multiple columns with consistent structure
        $hasTable = false;
        $rowCount = 0;
        $columnCount = 0;

        // Count lines that might be table rows
        $lines = explode("\n", $text);
        $alignedLines = array_filter($lines, function($line) {
            return strlen(trim($line)) > 10 && substr_count($line, ' ') > 2;
        });

        if (count($alignedLines) > 3) {
            $hasTable = true;
            $rowCount = count($alignedLines);
            $columnCount = 4; // Typical bulletin: matière, note, coeff, calc
        }

        return [
            'has_table' => $hasTable,
            'row_count' => $rowCount,
            'column_count' => $columnCount,
            'confidence' => $hasTable ? 0.7 : 0.0, // Confidence level of detection
        ];
    }

    /**
     * Validate file before processing
     */
    protected function validateFile(string $extension, int $fileSize): void
    {
        $maxSize = 5 * 1024 * 1024; // 5MB
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Allowed: " . implode(', ', $allowedExtensions));
        }

        if ($fileSize > $maxSize) {
            throw new Exception("File size exceeds 5MB limit");
        }
    }

    /**
     * Generate sample bulletin text for testing
     * Simulates OCR output from a real bulletin
     */
    protected function generateSampleBulletinText(): string
    {
        return <<<'EOT'
BULLETIN SCOLAIRE
Année 2024/2025
Classe: 6ème A
Semestre: 1er

RELEVÉ DE NOTES

Matière              Note    Coef    Calcul
─────────────────────────────────────────
Français             16/20   2       32
Mathématiques        14/20   2       28
Anglais              13/20   1       13
Sciences             15/20   2       30
Histoire-Géographie  14/20   1       14
EPS                  17/20   1       17
Technologie          16/20   1       16
Art Plastique        15/20   1       15

─────────────────────────────────────────
MOYENNE GÉNÉRALE     15.1/20
RANG DE CLASSE       5
─────────────────────────────────────────

Appréciation: Bon élève, travail régulier
Conseils: Continuer dans cette direction
EOT;
    }
}
