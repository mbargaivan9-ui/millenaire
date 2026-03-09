<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Health Check Controller
 * 
 * Endpoint for monitoring and health checks in production
 * GET /health
 */
class HealthCheckController extends Controller
{
    /**
     * Perform health checks and return status
     */
    public function check(Request $request)
    {
        $checks = [];

        // 1. Application status
        $checks['application'] = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug') ? 'enabled' : 'disabled',
        ];

        // 2. Database connection
        $checks['database'] = $this->checkDatabase();

        // 3. Cache
        $checks['cache'] = $this->checkCache();

        // 4. Storage
        $checks['storage'] = $this->checkStorage();

        // 5. Queue (if configured)
        if (config('queue.default') !== 'sync') {
            $checks['queue'] = $this->checkQueue();
        }

        // 6. Memory usage
        $checks['memory'] = $this->checkMemory();

        // 7. Disk space
        $checks['disk_space'] = $this->checkDiskSpace();

        // Overall status
        $allHealthy = collect($checks)
            ->every(fn($check) => ($check['status'] ?? 'unhealthy') === 'healthy');

        $statusCode = $allHealthy ? 200 : 503;
        $overallStatus = [
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ];

        return response()->json($overallStatus, $statusCode);
    }

    /**
     * Detailed health report (requires authentication)
     */
    public function detailed(Request $request)
    {
        // Only accessible to authenticated admins
        $this->authorize('isAdmin');

        $report = [
            'generated_at' => now()->toIso8601String(),
            'application' => [
                'name' => config('app.name'),
                'environment' => config('app.env'),
                'version' => config('app.version'),
                'url' => config('app.url'),
                'debug' => config('app.debug'),
            ],
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
            ],
            'database' => $this->getDatabaseDetails(),
            'cache' => $this->getCacheDetails(),
            'storage' => $this->getStorageDetails(),
            'performance' => $this->getPerformanceMetrics(),
            'security' => $this->getSecurityStatus(),
        ];

        return response()->json($report);
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            $duration = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'message' => 'Database connection successful',
                'response_time_ms' => round($duration, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache functionality
     */
    private function checkCache(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                return [
                    'status' => 'healthy',
                    'message' => 'Cache is functional',
                    'driver' => config('cache.default'),
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Cache read/write mismatch',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Cache error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage availability
     */
    private function checkStorage(): array
    {
        try {
            $writeable = is_writable(storage_path());
            $logsWriteable = is_writable(storage_path('logs'));

            if ($writeable && $logsWriteable) {
                return [
                    'status' => 'healthy',
                    'message' => 'Storage is writable',
                    'storage_path' => storage_path(),
                ];
            } else {
                return [
                    'status' => 'unhealthy',
                    'message' => 'Storage directory not writable',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Storage error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue worker
     */
    private function checkQueue(): array
    {
        // This is a simple check - actual queue health monitoring is more complex
        return [
            'status' => 'healthy',
            'message' => 'Queue is configured',
            'driver' => config('queue.default'),
        ];
    }

    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        $memLimit = $this->parseBytes(ini_get('memory_limit'));
        $memUsed = memory_get_usage(true);
        $memPeak = memory_get_peak_usage(true);
        $percentUsed = round(($memUsed / $memLimit) * 100, 2);

        $status = $percentUsed > 90 ? 'warning' : 'healthy';
        $status = $percentUsed > 98 ? 'unhealthy' : $status;

        return [
            'status' => $status,
            'memory_limit' => $this->formatBytes($memLimit),
            'memory_used' => $this->formatBytes($memUsed),
            'memory_peak' => $this->formatBytes($memPeak),
            'percentage_used' => $percentUsed . '%',
        ];
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace(): array
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        $percentUsed = round(($usedSpace / $totalSpace) * 100, 2);

        $status = $percentUsed > 90 ? 'warning' : 'healthy';
        $status = $percentUsed > 95 ? 'unhealthy' : $status;

        return [
            'status' => $status,
            'total_space' => $this->formatBytes($totalSpace),
            'used_space' => $this->formatBytes($usedSpace),
            'free_space' => $this->formatBytes($freeSpace),
            'percentage_used' => $percentUsed . '%',
        ];
    }

    /**
     * Get database connection details
     */
    private function getDatabaseDetails(): array
    {
        try {
            $connection = DB::connection();
            $config = $connection->getConfig();

            return [
                'driver' => $config['driver'] ?? 'unknown',
                'host' => $config['host'] ?? 'localhost',
                'database' => $config['database'] ?? 'unknown',
                'port' => $config['port'] ?? 'default',
                'status' => 'connected',
            ];
        } catch (\Exception $e) {
            return ['status' => 'disconnected', 'error' => $e->getMessage()];
        }
    }

    /**
     * Get cache configuration details
     */
    private function getCacheDetails(): array
    {
        return [
            'driver' => config('cache.default'),
            'cache_key_prefix' => config('cache.prefix'),
            'ttl_default' => config('cache.ttl'),
        ];
    }

    /**
     * Get storage configuration details
     */
    private function getStorageDetails(): array
    {
        return [
            'default_disk' => config('filesystems.default'),
            'disks_configured' => array_keys(config('filesystems.disks')),
        ];
    }

    /**
     * Get performance metrics placeholder
     */
    private function getPerformanceMetrics(): array
    {
        return [
            'request_start' => microtime(true),
            'cache_backend' => config('cache.default'),
            'session_driver' => config('session.driver'),
        ];
    }

    /**
     * Get security status
     */
    private function getSecurityStatus(): array
    {
        return [
            'https_enabled' => config('app.url', '')[0] === 'https',
            'debug_mode' => config('app.debug'),
            'session_secure' => config('session.secure') ?? false,
            'csrf_protection' => true,
        ];
    }

    /**
     * Parse bytes string to integer
     */
    private function parseBytes($value): int
    {
        $value = trim($value);
        $last = strtoupper($value[strlen($value) - 1]);
        $value = (int)$value;

        switch ($last) {
            case 'G':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'M':
                $value *= 1024 * 1024;
                break;
            case 'K':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= 1 << (10 * $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
