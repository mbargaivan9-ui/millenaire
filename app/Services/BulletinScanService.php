<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\GdDriver;
use Exception;

/**
 * BulletinScanService
 * 
 * Service responsible for processing uploaded bulletin images:
 * 1. Image preprocessing (rotation, grayscale conversion, binarization)
 * 2. OCR text extraction via Tesseract
 * 3. Structured data extraction and hOCR coordinate detection
 * 
 * @package App\Services
 */
class BulletinScanService
{
    private ImageManager $imageManager;
    private string $tesseractPath;
    private string $tessDataPrefix;
    private string $ocrLanguage;
    private int $minConfidence;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new GdDriver());
        $this->tesseractPath = config('ocr.tesseract.path', 'tesseract');
        $this->tessDataPrefix = config('ocr.tesseract.tessdata_prefix', '');
        $this->ocrLanguage = config('ocr.tesseract.language', 'fra+eng');
        $this->minConfidence = config('ocr.min_confidence', 60);
        
        Log::info('BulletinScanService initialized', [
            'tesseract_path' => $this->tesseractPath,
            'language' => $this->ocrLanguage,
            'min_confidence' => $this->minConfidence,
        ]);
    }

    /**
     * Process an uploaded bulletin image through the complete OCR pipeline
     * 
     * @param UploadedFile $imageFile
     * @return array OcrResult with text, confidence, and structured data
     * 
     * @throws Exception
     */
    public function processImage(UploadedFile $imageFile): array
    {
        try {
            // Validation
            $this->validateImageFile($imageFile);
            
            // Step 1: Store original
            $originalPath = $this->storeOriginalImage($imageFile);
            Log::info('Bulletin image stored', ['path' => $originalPath]);
            
            // Step 2: Preprocess image
            $preprocessedPath = $this->preprocessImage($originalPath);
            Log::info('Image preprocessed', ['path' => $preprocessedPath]);
            
            // Step 3: Run Tesseract OCR
            $ocrResult = $this->runTesseractOCR($preprocessedPath);
            Log::info('Tesseract OCR completed', [
                'confidence' => $ocrResult['confidence'],
                'text_length' => strlen($ocrResult['text']),
            ]);
            
            // Step 4: Extract structured data
            $structuredData = $this->extractStructuredData($ocrResult['text']);
            
            // Step 5: Validate with assistant
            if ($ocrResult['confidence'] < $this->minConfidence) {
                Log::warning('Low OCR confidence', [
                    'confidence' => $ocrResult['confidence'],
                    'recommended_reprocess' => true,
                ]);
            }
            
            return [
                'status' => 'success',
                'raw_text' => $ocrResult['text'],
                'confidence_score' => $ocrResult['confidence'],
                'blocks' => $ocrResult['blocks'] ?? [],
                'structured_data' => $structuredData,
                'original_image_path' => $originalPath,
                'preprocessed_image_path' => $preprocessedPath,
                'hocr_coordinates' => $ocrResult['coordinates'] ?? [],
            ];
            
        } catch (Exception $e) {
            Log::error('OCR processing failed', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_code' => $e->getCode(),
            ];
        }
    }

    /**
     * Validate uploaded image file
     */
    private function validateImageFile(UploadedFile $file): void
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and PDF allowed.');
        }
        
        if ($file->getSize() > $maxSize) {
            throw new Exception('File too large. Maximum 10MB allowed.');
        }
    }

    /**
     * Store original uploaded image
     */
    private function storeOriginalImage(UploadedFile $file): string
    {
        $path = Storage::disk('local')->putFile('ocr/images/originals', $file);
        return Storage::disk('local')->path($path);
    }

    /**
     * Preprocess image: convert to grayscale, enhance contrast, deskew
     * 
     * Uses Intervention Image library for manipulation
     */
    private function preprocessImage(string $imagePath): string
    {
        try {
            $image = $this->imageManager->read($imagePath);
            
            // Step 1: Auto-rotate if needed
            $image->rotate(-5); // Slight rotation correction
            
            // Step 2: Convert to grayscale for better OCR
            $image->greyscale();
            
            // Step 3: Enhance contrast
            $image->contrast(15);
            
            // Step 4: Increase DPI for Tesseract (1200dpi is optimal)
            // Note: Intervention Image doesn't directly set DPI, so we scale up
            $originalWidth = $image->width();
            if ($originalWidth < 2400) {
                // Scale to 2400px width (approx 300dpi equivalent)
                $image->scale(width: 2400);
            }
            
            // Save preprocessed version
            $preprocessedPath = Storage::disk('local')->path('ocr/images/preprocessed/' . basename($imagePath, '.*') . '_processed.png');
            @mkdir(dirname($preprocessedPath), 0755, true);
            
            $image->toPng()->save($preprocessedPath);
            
            Log::info('Image preprocessing completed', [
                'original_width' => $originalWidth,
                'final_width' => $image->width(),
            ]);
            
            return $preprocessedPath;
            
        } catch (Exception $e) {
            Log::error('Image preprocessing failed', [
                'error' => $e->getMessage(),
            ]);
            return $imagePath; // Fallback to original if preprocessing fails
        }
    }

    /**
     * Run Tesseract OCR on preprocessed image
     * 
     * Returns both plain text and hOCR data for coordinate detection
     */
    private function runTesseractOCR(string $imagePath): array
    {
        try {
            // PSM 3: Fully automatic page segmentation with OSD (Orientation and Script Detection)
            $cmd = [
                escapeshellarg($this->tesseractPath),
                escapeshellarg($imagePath),
                'stdout',
                '-l ' . escapeshellarg($this->ocrLanguage),
                '--psm 3', // Auto page segmentation
                '--oem 3', // Use both legacy and LSTM OCR
            ];
            
            if ($this->tessDataPrefix) {
                $cmd[] = '--tessdata-dir ' . escapeshellarg($this->tessDataPrefix);
            }
            
            $commandLine = implode(' ', $cmd);
            Log::info('Running Tesseract', ['command' => $commandLine]);
            
            // Execute Tesseract
            exec($commandLine, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new Exception('Tesseract failed with code: ' . $returnCode);
            }
            
            $rawText = implode("\n", $output);
            
            // Try to get confidence and hOCR data
            $confidenceScore = $this->extractConfidenceScore($imagePath);
            
            // Get hOCR for coordinate detection
            $hocrData = $this->getHOCRData($imagePath);
            
            return [
                'text' => $rawText,
                'confidence' => $confidenceScore,
                'blocks' => $this->parseTextBlocks($rawText),
                'coordinates' => $hocrData,
            ];
            
        } catch (Exception $e) {
            Log::error('Tesseract OCR execution failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Extract confidence score from Tesseract's confidence data
     */
    private function extractConfidenceScore(string $imagePath): float
    {
        try {
            // Generate confidence file
            $baseImagePath = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . pathinfo($imagePath, PATHINFO_FILENAME);
            
            $cmd = [
                escapeshellarg($this->tesseractPath),
                escapeshellarg($imagePath),
                escapeshellarg($baseImagePath),
                'tsv',
                '-l ' . escapeshellarg($this->ocrLanguage),
                '--psm 3',
            ];
            
            exec(implode(' ', $cmd) . ' 2>/dev/null', $output);
            
            if (!file_exists($baseImagePath . '.tsv')) {
                return 75.0; // Default if confidence extraction fails
            }
            
            // Parse TSV to extract confidence values
            $tsvContent = file_get_contents($baseImagePath . '.tsv');
            $lines = explode("\n", $tsvContent);
            $confidences = [];
            
            foreach (array_slice($lines, 1) as $line) {
                $parts = explode("\t", $line);
                if (isset($parts[10]) && is_numeric($parts[10])) {
                    $confidences[] = (int)$parts[10];
                }
            }
            
            // Calculate average confidence
            $score = !empty($confidences) ? array_sum($confidences) / count($confidences) : 75.0;
            
            // Cleanup
            @unlink($baseImagePath . '.tsv');
            
            return min(100, round($score, 2));
            
        } catch (Exception $e) {
            Log::warning('Confidence score extraction failed', ['error' => $e->getMessage()]);
            return 75.0; // Default fallback
        }
    }

    /**
     * Get hOCR data for coordinate detection
     * hOCR contains bbox (bounding box) information for each word
     */
    private function getHOCRData(string $imagePath): array
    {
        try {
            $baseImagePath = pathinfo($imagePath, PATHINFO_DIRNAME) . '/' . pathinfo($imagePath, PATHINFO_FILENAME);
            
            $cmd = [
                escapeshellarg($this->tesseractPath),
                escapeshellarg($imagePath),
                escapeshellarg($baseImagePath),
                'hocr',
                '-l ' . escapeshellarg($this->ocrLanguage),
                '--psm 3',
            ];
            
            exec(implode(' ', $cmd) . ' 2>/dev/null', $output);
            
            if (!file_exists($baseImagePath . '.hocr')) {
                return [];
            }
            
            $hocrContent = file_get_contents($baseImagePath . '.hocr');
            $coordinates = $this->parseHOCRCoordinates($hocrContent);
            
            // Cleanup
            @unlink($baseImagePath . '.hocr');
            
            return $coordinates;
            
        } catch (Exception $e) {
            Log::warning('hOCR extraction failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Parse hOCR HTML to extract bbox coordinates
     * Format: bbox x1 y1 x2 y2
     */
    private function parseHOCRCoordinates(string $hocrContent): array
    {
        $coordinates = [];
        
        // Extract bbox from title attributes
        if (preg_match_all('/title=".*?bbox (\d+) (\d+) (\d+) (\d+).*?"[^>]*>([^<]*)</i', $hocrContent, $matches)) {
            for ($i = 0; $i < count($matches[0]); $i++) {
                if (!empty(trim($matches[5][$i]))) {
                    $coordinates[] = [
                        'text' => trim($matches[5][$i]),
                        'bbox' => [
                            'x1' => (int)$matches[1][$i],
                            'y1' => (int)$matches[2][$i],
                            'x2' => (int)$matches[3][$i],
                            'y2' => (int)$matches[4][$i],
                        ],
                    ];
                }
            }
        }
        
        return $coordinates;
    }

    /**
     * Parse raw OCR text into logical blocks (header, body, footer)
     */
    private function parseTextBlocks(string $rawText): array
    {
        $lines = explode("\n", trim($rawText));
        
        return [
            'raw_lines' => $lines,
            'total_lines' => count($lines),
            'non_empty_lines' => count(array_filter($lines, fn($l) => !empty(trim($l)))),
        ];
    }

    /**
     * Extract structured data from OCR text
     * Identify: header, student info, subjects, footer
     */
    private function extractStructuredData(string $rawText): array
    {
        $lines = array_filter(
            explode("\n", $rawText),
            fn($l) => !empty(trim($l))
        );
        
        return [
            'identified_patterns' => [
                'has_school_header' => $this->detectSchoolName($rawText),
                'has_student_info' => $this->detectStudentInfo($rawText),
                'has_subjects_table' => $this->detectSubjectsTable($rawText),
                'has_grades' => $this->detectGrades($rawText),
                'has_calculations' => $this->detectCalculations($rawText),
                'has_signatures' => $this->detectSignatures($rawText),
            ],
            'total_lines_processed' => count($lines),
            'ready_for_claude_analysis' => true,
        ];
    }

    // Detection helpers
    private function detectSchoolName(string $text): bool
    {
        return preg_match('/(école|collège|lycée|school|college)/i', $text) > 0;
    }

    private function detectStudentInfo(string $text): bool
    {
        return preg_match('/(nom|prénom|matricule|classe|student|name)/i', $text) > 0;
    }

    private function detectSubjectsTable(string $text): bool
    {
        return preg_match('/(mathématiques|français|anglais|sciences|histoire|subject)/i', $text) > 0;
    }

    private function detectGrades(string $text): bool
    {
        return preg_match('/\b(1[0-9]|20|[0-9])\b/m', $text) > 0;
    }

    private function detectCalculations(string $text): bool
    {
        return preg_match('/(moyenne|rang|total|average|rank)/i', $text) > 0;
    }

    private function detectSignatures(string $text): bool
    {
        return preg_match('/(signature|principal|director|teacher|professeur)/i', $text) > 0;
    }
}
