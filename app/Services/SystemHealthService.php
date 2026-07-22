<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;

class SystemHealthService
{
    public function getHealthData(): array
    {
        return [
            'database' => $this->getDatabaseStatus(),
            'storage' => $this->getStorageUsage(),
            'cache' => $this->getCacheStatus(),
            'queue' => $this->getQueueStatus(),
            'versions' => $this->getVersionInfo(),
            'server' => $this->getServerInfo(),
            'session' => $this->getSessionInfo(),
        ];
    }

    public function getDatabaseStatus(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $latency = round((microtime(true) - $start) * 1000, 2);

            $dbPath = database_path('database.sqlite');
            $size = file_exists($dbPath) ? filesize($dbPath) : 0;

            return [
                'status' => 'healthy',
                'driver' => config('database.default'),
                'latency_ms' => $latency,
                'size_bytes' => $size,
                'size_formatted' => $this->formatBytes($size),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getStorageUsage(): array
    {
        $storagePath = storage_path();
        $totalSpace = disk_total_space($storagePath);
        $freeSpace = disk_free_space($storagePath);
        $usedSpace = $totalSpace - $freeSpace;

        $appSize = $this->getDirectorySize($storagePath);

        return [
            'status' => ($usedSpace / $totalSpace) > 0.9 ? 'warning' : 'healthy',
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'app_usage' => $this->formatBytes($appSize),
            'usage_percent' => round(($usedSpace / $totalSpace) * 100, 1),
        ];
    }

    public function getCacheStatus(): array
    {
        try {
            $start = microtime(true);
            Cache::put('health_check', true, 10);
            $working = Cache::get('health_check') === true;
            Cache::forget('health_check');
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => $working ? 'healthy' : 'error',
                'driver' => config('cache.default'),
                'latency_ms' => $latency,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'driver' => config('cache.default'),
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getQueueStatus(): array
    {
        try {
            $driver = config('queue.default');
            return [
                'status' => 'healthy',
                'driver' => $driver,
                'connections' => config('queue.connections', []),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getVersionInfo(): array
    {
        return [
            'laravel' => app()->version(),
            'php' => PHP_VERSION,
            'database' => $this->getDbVersion(),
        ];
    }

    public function getServerInfo(): array
    {
        return [
            'os' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
    }

    public function getSessionInfo(): array
    {
        return [
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime'),
            'encrypt' => config('session.encrypt'),
            'active_users' => User::where('status', 'active')->count(),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    private function getDirectorySize(string $path): int
    {
        $size = 0;
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    private function getDbVersion(): string
    {
        try {
            $result = DB::select('SELECT version() as ver');
            return $result[0]->ver ?? 'Unknown';
        } catch (\Exception) {
            return 'N/A';
        }
    }
}
