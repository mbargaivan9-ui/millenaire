<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Exception;

/**
 * OCR Performance Optimization Service
 * 
 * Handles:
 * - Image resizing before OCR processing
 * - Caching of OCR results
 * - Result hashing for duplicate detection
 * - Memory optimization
 */
class OcrOptimizationService
{
    /**
     * Default image optimization settings
     */
    const DEFAULT_MAX_WIDTH = 2000;
    const DEFAULT_MAX_HEIGHT = 2000;
    const DEFAULT_JPEG_QUALITY = 85;
    const CACHE_TTL = 86400; // 24 hours
    const CACHE_NAMESPACE = 'ocr_results';

    /**
     * Optimize image before OCR processing
     * 
     * Handles:
     * - Resizing large images to optimal size
     * - Compression to reduce memory usage
     * - Orientation correction
     * - Format conversion if needed
     */
    public function optimizeImage(UploadedFile $file, array $options = []): UploadedFile
    {
        try {
            Log::info('OCR: Starting image optimization', [
                'original_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            $options = array_merge([
                'max_width' => config('ocr.optimization.max_width', self::DEFAULT_MAX_WIDTH),
                'max_height' => config('ocr.optimization.max_height', self::DEFAULT_MAX_HEIGHT),
                'quality' => config('ocr.optimization.jpeg_quality', self::DEFAULT_JPEG_QUALITY),
            ], $options);

            // Load image
            $image = Image::make($file->path());

            // Rotate if needed (fix EXIF orientation)
            $image = $this->fixOrientation($image);

            // Resize if necessary
            if ($image->width() > $options['max_width'] || 
                $image->height() > $options['max_height']) {
                $image->resize(
                    $options['max_width'],
                    $options['max_height'],
                    function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    }
                );

                Log::info('OCR: Image resized', [
                    'new_width' => $image->width(),
                    'new_height' => $image->height(),
                ]);
            }

            // Save optimized image to temporary location
            $tempPath = storage_path('temp/ocr_' . uniqid() . '.jpg');
            @mkdir(dirname($tempPath), 0777, true);
            
            $image->save($tempPath, $options['quality']);

            // Create a fake uploaded file from the optimized image
            $optimizedFile = new UploadedFile(
                $tempPath,
                $file->getClientOriginalName(),
                'image/jpeg',
                null,
                true
            );

            $optimizedSize = filesize($tempPath);
            $compression = round((1 - $optimizedSize / $file->getSize()) * 100);
            
            Log::info('OCR: Image optimization complete', [
                'original_size' => $file->getSize(),
                'optimized_size' => $optimizedSize,
                'compression_percent' => $compression,
            ]);

            return $optimizedFile;

        } catch (Exception $e) {
            Log::warning('OCR: Image optimization failed', [
                'error' => $e->getMessage(),
            ]);
            // Return original file if optimization fails
            return $file;
        }
    }

    /**
     * Fix image orientation based on EXIF data
     */
    protected function fixOrientation($image)
    {
        try {
            $exif = @exif_read_data($image->getCore()->getImageString() ?? '');
            
            if ($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                
                switch ($orientation) {
                    case 3:
                        $image->rotate(180);
                        break;
                    case 6:
                        $image->rotate(-90);
                        break;
                    case 8:
                        $image->rotate(90);
                        break;
                }
            }
        } catch (Exception $e) {
            Log::debug('OCR: Could not read EXIF data: ' . $e->getMessage());
        }

        return $image;
    }

    /**
     * Generate cache key for OCR result
     * 
     * Uses file hash to identify identical images
     * Even if uploaded with different names/times
     */
    public function generateCacheKey(UploadedFile $file): string
    {
        // Generate hash from first 1MB of file (faster than full file hash)
        $handle = fopen($file->path(), 'r');
        $chunk = fread($handle, 1024 * 1024);
        fclose($handle);
        
        $hash = hash('sha256', $chunk);
        
        return self::CACHE_NAMESPACE . ':' . $hash;
    }

    /**
     * Cache OCR extraction result
     * 
     * Stores both the extracted text and metadata
     */
    public function cacheResult(
        UploadedFile $file,
        array $result,
        int $ttl = self::CACHE_TTL
    ): void {
        try {
            $cacheKey = $this->generateCacheKey($file);
            
            // Store with metadata for validation
            $cacheData = array_merge($result, [
                'cached_at' => now()->toDateTimeString(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_hash' => hash_file('sha256', $file->path()),
            ]);

            Cache::put($cacheKey, $cacheData, $ttl);

            Log::info('OCR: Result cached', [
                'cache_key' => $cacheKey,
                'ttl_hours' => $ttl / 3600,
            ]);

        } catch (Exception $e) {
            Log::warning('OCR: Failed to cache result: ' . $e->getMessage());
        }
    }

    /**
     * Get cached OCR result if exists and valid
     * 
     * Returns null if:
     * - Cache expired
     * - File changed (hash mismatch)
     * - Cache not found
     */
    public function getCachedResult(UploadedFile $file): ?array
    {
        try {
            $cacheKey = $this->generateCacheKey($file);
            $cached = Cache::get($cacheKey);

            if (!$cached) {
                return null;
            }

            // Verify file hasn't changed
            $currentHash = hash_file('sha256', $file->path());
            if ($cached['file_hash'] !== $currentHash) {
                Cache::forget($cacheKey);
                return null;
            }

            Log::info('OCR: Using cached result', [
                'cache_key' => $cacheKey,
                'cached_age_minutes' => now()->diffInMinutes(
                    \Carbon\Carbon::parse($cached['cached_at'])
                ),
            ]);

            return $cached;

        } catch (Exception $e) {
            Log::debug('OCR: Failed to retrieve cache: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear expired OCR cache entries
     * 
     * Call this periodically (daily via scheduler)
     * to clean up old cached results
     */
    public function clearExpiredCache(): int
    {
        try {
            // Redis: Use Redis TTL (automatic)
            // File: Manually delete old files from storage/cache/ocr
            
            $cacheDir = storage_path('cache/ocr');
            if (!is_dir($cacheDir)) {
                return 0;
            }

            $files = glob($cacheDir . '/*');
            $deletedCount = 0;
            $expiryTime = now()->subHours(24)->timestamp;

            foreach ($files as $file) {
                if (filemtime($file) < $expiryTime) {
                    @unlink($file);
                    $deletedCount++;
                }
            }

            Log::info('OCR: Cache cleanup completed', [
                'deleted_files' => $deletedCount,
            ]);

            return $deletedCount;

        } catch (Exception $e) {
            Log::warning('OCR: Cache cleanup failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        try {
            $cacheDir = storage_path('cache/ocr');
            
            if (!is_dir($cacheDir)) {
                return [
                    'cache_dir' => $cacheDir,
                    'exists' => false,
                    'files' => 0,
                    'total_size_mb' => 0,
                ];
            }

            $files = glob($cacheDir . '/*');
            $totalSize = 0;

            foreach ($files as $file) {
                $totalSize += filesize($file);
            }

            return [
                'cache_dir' => $cacheDir,
                'exists' => true,
                'files' => count($files),
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'oldest_file_age_days' => $files ? 
                    ceil((time() - filemtime(min($files))) / 86400) : 0,
            ];

        } catch (Exception $e) {
            Log::warning('OCR: Failed to get cache stats: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Pre-process batch of images for parallel OCR
     * 
     * Useful for processing multiple bulletin pages at once
     * Returns optimized file paths for batch processing
     */
    public function optimizeBatch(array $files): array
    {
        $optimized = [];
        $startTime = microtime(true);

        foreach ($files as $index => $file) {
            try {
                $optimized[$index] = $this->optimizeImage($file);
            } catch (Exception $e) {
                Log::warning("OCR: Batch optimization failed for file $index: " . $e->getMessage());
                $optimized[$index] = $file; // Use original if optimization fails
            }
        }

        $duration = microtime(true) - $startTime;
        Log::info('OCR: Batch optimization completed', [
            'file_count' => count($files),
            'duration_seconds' => round($duration, 2),
            'avg_per_file' => round($duration / count($files), 2),
        ]);

        return $optimized;
    }

    /**
     * Monitor memory usage and warn if approaching limits
     */
    public function checkMemoryUsage(): array
    {
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        $usage = memory_get_usage(true);
        $percent = round(($usage / $limit) * 100);

        return [
            'limit_mb' => round($limit / 1024 / 1024),
            'usage_mb' => round($usage / 1024 / 1024),
            'percent' => $percent,
            'warning' => $percent > 80,
        ];
    }

    /**
     * Parse PHP memory limit string to bytes
     */
    protected function parseMemoryLimit(string $value): int
    {
        $value = trim($value);
        $last = strtoupper($value[-1]);
        $value = (int) $value;

        switch ($last) {
            case 'G': return $value * 1024 * 1024 * 1024;
            case 'M': return $value * 1024 * 1024;
            case 'K': return $value * 1024;
            default: return $value;
        }
    }
}
