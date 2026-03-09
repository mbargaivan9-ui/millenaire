<?php

namespace App\Console\Commands;

use App\Services\BulletinOCRService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SplFileInfo;

class TestOCRExtraction extends Command
{
    protected $signature = 'ocr:test {--file= : Path to test image} {--backend=auto : Force backend (tesseract|pytesseract|ocr.space|auto)}';
    protected $description = 'Test OCR extraction with various backends and file formats';

    protected BulletinOCRService $ocrService;

    public function __construct(BulletinOCRService $ocrService)
    {
        parent::__construct();
        $this->ocrService = $ocrService;
    }

    public function handle(): int
    {
        $this->info('='.str_repeat('=', 50).'=');
        $this->info('|'.str_repeat(' ', 50).'|');
        $this->info('|  OCR Extraction Testing Tool'.str_pad('', 20).'|');
        $this->info('|'.str_repeat(' ', 50).'|');
        $this->info('='.str_repeat('=', 50).'=');
        $this->newLine();

        // Step 1: Check installations
        $this->info('<fg=yellow>[STEP 1/5]</> Checking OCR Backend Availability...');
        $this->checkBackends();
        $this->newLine();

        // Step 2: Get test file
        $testFile = $this->getTestFile();
        if (!$testFile) {
            $this->error('No test file available');
            return 1;
        }
        $this->newLine();

        // Step 3: Create fake uploaded file
        $this->info('<fg=yellow>[STEP 3/5]</> Creating file instance...');
        $uploadedFile = $this->createUploadedFile($testFile);
        $this->info("<fg=green>✓</> File prepared: {$testFile->getFilename()} ({$testFile->getSize()} bytes)");
        $this->newLine();

        // Step 4: Perform OCR extraction
        $this->info('<fg=yellow>[STEP 4/5]</> Running OCR extraction...');
        $result = $this->performOCRExtraction($uploadedFile);
        $this->newLine();

        // Step 5: Display results
        $this->info('<fg=yellow>[STEP 5/5]</> Results...');
        $this->displayResults($result);
        $this->newLine();

        $this->info('<fg=green>✓ OCR Extraction Test Complete</>');
        return 0;
    }

    protected function checkBackends(): void
    {
        $this->line('Checking Tesseract...');
        if ($this->ocrService->isTesseractInstalled()) {
            $this->info('  <fg=green>✓</> Tesseract is installed');
        } else {
            $this->warn('  ✗ Tesseract not found');
        }

        $this->line('Checking Pytesseract...');
        if ($this->ocrService->isPytesseractAvailable()) {
            $this->info('  <fg=green>✓</> Pytesseract is available');
        } else {
            $this->warn('  ✗ Pytesseract not available');
        }

        $this->line('Checking OCR.Space configuration...');
        $apiKey = config('ocr.ocr_space.api_key');
        if ($apiKey) {
            $this->info('  <fg=green>✓</> OCR.Space API key configured');
        } else {
            $this->warn('  ✗ OCR.Space API key not configured');
        }
    }

    protected function getTestFile(): ?SplFileInfo
    {
        // Manual file path provided
        if ($filePath = $this->option('file')) {
            if (!file_exists($filePath)) {
                $this->error("File not found: $filePath");
                return null;
            }
            $this->info("<fg=yellow>[STEP 2/5]</> Using provided file: $filePath");
            return new SplFileInfo($filePath);
        }

        // Search for test files
        $this->info('<fg=yellow>[STEP 2/5]</> Searching for test files...');
        $searchPaths = [
            'resources/samples',
            'resources/images',
            'storage/test-images',
            'public/bulletin-samples',
        ];

        foreach ($searchPaths as $searchPath) {
            if (!is_dir($searchPath)) continue;

            $files = glob("$searchPath/*.{jpg,jpeg,png,pdf}", GLOB_BRACE);
            if (!empty($files)) {
                $file = reset($files);
                $this->info("  <fg=green>✓</> Found test file: $file");
                return new SplFileInfo($file);
            }
        }

        $this->warn('  No test files found in standard locations');
        $this->line('  Run one of:');
        $this->line('  - php artisan ocr:test --file=/path/to/image.jpg');
        $this->line('  - Place sample images in: resources/samples/');
        
        return null;
    }

    protected function createUploadedFile(SplFileInfo $file): UploadedFile
    {
        return new UploadedFile(
            $file->getRealPath(),
            $file->getFilename(),
            mime_content_type($file->getRealPath()),
            null,
            true
        );
    }

    protected function performOCRExtraction(UploadedFile $file): array
    {
        $backend = $this->option('backend');
        $startTime = microtime(true);

        try {
            $result = $this->ocrService->processFile($file, $backend);
            $duration = microtime(true) - $startTime;

            return [
                'success' => true,
                'duration' => $duration,
                'text' => $result['text'] ?? '',
                'confidence' => $result['confidence'] ?? 0,
                'method' => $result['method'] ?? 'unknown',
                'language' => $result['language'] ?? 'fra+eng',
                'error' => null,
            ];
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            return [
                'success' => false,
                'duration' => $duration,
                'text' => '',
                'confidence' => 0,
                'method' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function displayResults(array $result): void
    {
        if (!$result['success']) {
            $this->error("OCR extraction failed: {$result['error']}");
            return;
        }

        $table = [
            ['Metric', 'Value'],
            ['<fg=green>Status</>', '<fg=green>Success</>'],
            ['Method Used', $result['method']],
            ['Duration', sprintf('%.2f seconds', $result['duration'])],
            ['Confidence', sprintf('%d%%', $result['confidence'])],
            ['Language', $result['language']],
            ['Text Length', strlen($result['text']) . ' characters'],
            ['Min Confidence Threshold', config('ocr.min_confidence') . '%'],
        ];

        // Check if confidence is acceptable
        $isAcceptable = $this->ocrService->isConfidenceAcceptable($result['confidence']);
        $confidenceStatus = $isAcceptable ? '<fg=green>✓ ACCEPTABLE</>' : '<fg=red>✗ BELOW THRESHOLD</>';
        
        $this->table(['Metric', 'Value'], [
            ['Status', '<fg=green>Success</>'],
            ['Method Used', $result['method']],
            ['Duration', sprintf('%.2f seconds', $result['duration'])],
            ['Confidence', sprintf('%d%%', $result['confidence']) . " $confidenceStatus"],
            ['Language', $result['language']],
            ['Text Length', strlen($result['text']) . ' characters'],
            ['Threshold', config('ocr.min_confidence') . '%'],
        ]);

        $this->newLine();
        $this->info('Extracted Text (first 500 characters):');
        $this->line(str_repeat('-', 60));
        $this->line(substr($result['text'], 0, 500));
        if (strlen($result['text']) > 500) {
            $this->line('...[truncated]');
        }
        $this->line(str_repeat('-', 60));

        // Recommendations
        $this->newLine();
        $this->info('Recommendations:');
        if ($result['confidence'] < config('ocr.min_confidence')) {
            $this->warn('  • Confidence below threshold - consider better image quality');
            $this->warn('  • Ensure high contrast and brightness');
            $this->warn('  • Avoid shadows and reflections in scanned images');
        } else {
            $this->info('  <fg=green>✓</> Confidence is acceptable for processing');
        }

        if ($result['duration'] > config('ocr.tesseract.timeout', 30)) {
            $this->warn('  • Processing took longer than timeout - increase TESSERACT_TIMEOUT');
        } else {
            $this->info('  <fg=green>✓</> Processing completed within timeout');
        }
    }
}
