<?php

namespace App\Console\Commands;

use App\Services\OcrOptimizationService;
use Illuminate\Console\Command;

class OcrCacheCleanup extends Command
{
    protected $signature = 'ocr:cache-cleanup {--dry-run : Show what would be deleted without deleting}';
    protected $description = 'Clean up expired OCR cache entries and temporary files';

    protected OcrOptimizationService $optimizationService;

    public function __construct(OcrOptimizationService $optimizationService)
    {
        parent::__construct();
        $this->optimizationService = $optimizationService;
    }

    public function handle(): int
    {
        $this->info('Starting OCR cache cleanup...');
        $this->newLine();

        $dryRun = $this->option('dry-run');

        // Get current cache stats
        $this->info('Current Cache Statistics:');
        $stats = $this->optimizationService->getCacheStats();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Cache Directory', $stats['cache_dir'] ?? 'N/A'],
                ['Files Cached', $stats['files'] ?? 0],
                ['Total Size', ($stats['total_size_mb'] ?? 0) . ' MB'],
                ['Oldest File Age', ($stats['oldest_file_age_days'] ?? 0) . ' days'],
            ]
        );
        $this->newLine();

        // Clean up expired cache
        $this->info('Cleaning up expired cache entries...');
        
        if ($dryRun) {
            $this->warn('DRY RUN: No files will be deleted');
        }

        $deletedCount = $dryRun ? 0 : $this->optimizationService->clearExpiredCache();
        
        $this->info("Deleted: $deletedCount expired cache file(s)");
        $this->newLine();

        // Clean up temporary files
        $this->info('Cleaning up temporary OCR files...');
        $tempDir = storage_path('temp');
        
        if (is_dir($tempDir)) {
            $tempFiles = glob($tempDir . '/ocr_*');
            $tempDeleted = 0;
            
            foreach ($tempFiles as $file) {
                // Delete temp files older than 1 hour
                if (time() - filemtime($file) > 3600) {
                    if (!$dryRun) {
                        @unlink($file);
                    }
                    $tempDeleted++;
                }
            }
            
            $this->info("Deleted: $tempDeleted temporary file(s)");
        }

        $this->newLine();

        // Get updated cache stats
        $this->info('Updated Cache Statistics:');
        $newStats = $this->optimizationService->getCacheStats();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Files Remaining', $newStats['files'] ?? 0],
                ['Total Size', ($newStats['total_size_mb'] ?? 0) . ' MB'],
            ]
        );

        // Summary
        $this->newLine();
        if ($dryRun) {
            $this->warn('This was a DRY RUN. No files were actually deleted.');
            $this->info('Run without --dry-run flag to actually clean up cache.');
        } else {
            $this->info('✓ OCR cache cleanup completed successfully');
        }

        return Command::SUCCESS;
    }
}
