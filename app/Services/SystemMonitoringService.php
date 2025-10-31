<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ConstantsService;

class SystemMonitoringService
{
    /**
     * Get comprehensive system performance metrics
     */
    public function getSystemMetrics(): array
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
            'network' => $this->getNetworkActivity(),
            'database' => $this->getDatabaseMetrics(),
            'php' => $this->getPhpMetrics(),
            'system_load' => $this->getSystemLoad(),
            'uptime' => $this->getSystemUptime(),
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * Get CPU usage percentage
     */
    private function getCpuUsage(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return $this->getWindowsCpuUsage();
            } else {
                return $this->getLinuxCpuUsage();
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to get CPU usage: ' . $e->getMessage());
            return [
                'usage_percent' => 0,
                'cores' => 1,
                'status' => 'unavailable'
            ];
        }
    }

    /**
     * Get Windows CPU usage
     */
    private function getWindowsCpuUsage(): array
    {
        try {
            // Use WMI to get CPU usage on Windows
            $output = shell_exec('wmic cpu get loadpercentage /value 2>nul');
            if ($output) {
                preg_match('/LoadPercentage=(\d+)/', $output, $matches);
                $cpuUsage = isset($matches[1]) ? (int)$matches[1] : 0;
            } else {
                $cpuUsage = 0;
            }

            // Get number of cores
            $coreOutput = shell_exec('wmic cpu get NumberOfCores /value 2>nul');
            $cores = 1;
            if ($coreOutput && preg_match('/NumberOfCores=(\d+)/', $coreOutput, $matches)) {
                $cores = (int)$matches[1];
            }

            return [
                'usage_percent' => $cpuUsage,
                'cores' => $cores,
                'status' => 'active'
            ];
        } catch (\Throwable $e) {
            return [
                'usage_percent' => 0,
                'cores' => 1,
                'status' => 'error'
            ];
        }
    }

    /**
     * Get Linux CPU usage
     */
    private function getLinuxCpuUsage(): array
    {
        try {
            $load = sys_getloadavg();
            $cores = $this->getCpuCores();
            $cpuUsage = $cores > 0 ? min(100, ($load[0] / $cores) * 100) : 0;

            return [
                'usage_percent' => round($cpuUsage, 2),
                'cores' => $cores,
                'load_average' => $load,
                'status' => 'active'
            ];
        } catch (\Throwable $e) {
            return [
                'usage_percent' => 0,
                'cores' => 1,
                'status' => 'error'
            ];
        }
    }

    /**
     * Get number of CPU cores
     */
    private function getCpuCores(): int
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('wmic cpu get NumberOfCores /value 2>nul');
                if ($output && preg_match('/NumberOfCores=(\d+)/', $output, $matches)) {
                    return (int)$matches[1];
                }
            } else {
                $cores = shell_exec('nproc 2>/dev/null');
                if ($cores) {
                    return (int)trim($cores);
                }
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }
        return 1;
    }

    /**
     * Get memory usage information
     */
    private function getMemoryUsage(): array
    {
        try {
            $phpMemory = $this->getPhpMemoryUsage();
            $systemMemory = $this->getSystemMemoryUsage();

            return [
                'php' => $phpMemory,
                'system' => $systemMemory,
                'status' => 'active'
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to get memory usage: ' . $e->getMessage());
            return [
                'php' => ['used' => 0, 'limit' => 0, 'usage_percent' => 0],
                'system' => ['used' => 0, 'total' => 0, 'usage_percent' => 0],
                'status' => 'unavailable'
            ];
        }
    }

    /**
     * Get PHP memory usage
     */
    private function getPhpMemoryUsage(): array
    {
        $used = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        return [
            'used' => $used,
            'used_formatted' => $this->formatBytes($used),
            'peak' => $peak,
            'peak_formatted' => $this->formatBytes($peak),
            'limit' => $limit,
            'limit_formatted' => $this->formatBytes($limit),
            'usage_percent' => $limit > 0 ? round(($used / $limit) * 100, 2) : 0
        ];
    }

    /**
     * Get system memory usage
     */
    private function getSystemMemoryUsage(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return $this->getWindowsMemoryUsage();
            } else {
                return $this->getLinuxMemoryUsage();
            }
        } catch (\Throwable $e) {
            return [
                'used' => 0,
                'total' => 0,
                'free' => 0,
                'usage_percent' => 0
            ];
        }
    }

    /**
     * Get Windows memory usage
     */
    private function getWindowsMemoryUsage(): array
    {
        try {
            $output = shell_exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value 2>nul');
            $total = 0;
            $free = 0;

            if ($output) {
                if (preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $matches)) {
                    $total = (int)$matches[1] * 1024; // Convert from KB to bytes
                }
                if (preg_match('/FreePhysicalMemory=(\d+)/', $output, $matches)) {
                    $free = (int)$matches[1] * 1024; // Convert from KB to bytes
                }
            }

            $used = $total - $free;
            $usagePercent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

            return [
                'used' => $used,
                'used_formatted' => $this->formatBytes($used),
                'total' => $total,
                'total_formatted' => $this->formatBytes($total),
                'free' => $free,
                'free_formatted' => $this->formatBytes($free),
                'usage_percent' => $usagePercent
            ];
        } catch (\Throwable $e) {
            return [
                'used' => 0,
                'total' => 0,
                'free' => 0,
                'usage_percent' => 0
            ];
        }
    }

    /**
     * Get Linux memory usage
     */
    private function getLinuxMemoryUsage(): array
    {
        try {
            $meminfo = file_get_contents('/proc/meminfo');
            $lines = explode("\n", $meminfo);
            $memory = [];

            foreach ($lines as $line) {
                if (preg_match('/^(\w+):\s+(\d+)\s+kB/', $line, $matches)) {
                    $memory[$matches[1]] = (int)$matches[2] * 1024; // Convert to bytes
                }
            }

            $total = $memory['MemTotal'] ?? 0;
            $free = ($memory['MemFree'] ?? 0) + ($memory['Buffers'] ?? 0) + ($memory['Cached'] ?? 0);
            $used = $total - $free;
            $usagePercent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

            return [
                'used' => $used,
                'used_formatted' => $this->formatBytes($used),
                'total' => $total,
                'total_formatted' => $this->formatBytes($total),
                'free' => $free,
                'free_formatted' => $this->formatBytes($free),
                'usage_percent' => $usagePercent
            ];
        } catch (\Throwable $e) {
            return [
                'used' => 0,
                'total' => 0,
                'free' => 0,
                'usage_percent' => 0
            ];
        }
    }

    /**
     * Get disk usage information
     */
    private function getDiskUsage(): array
    {
        try {
            $path = base_path();
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            $usagePercent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

            return [
                'used' => $used,
                'used_formatted' => $this->formatBytes($used),
                'total' => $total,
                'total_formatted' => $this->formatBytes($total),
                'free' => $free,
                'free_formatted' => $this->formatBytes($free),
                'usage_percent' => $usagePercent,
                'path' => $path,
                'status' => 'active'
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to get disk usage: ' . $e->getMessage());
            return [
                'used' => 0,
                'total' => 0,
                'free' => 0,
                'usage_percent' => 0,
                'status' => 'unavailable'
            ];
        }
    }

    /**
     * Get network activity (simplified for web applications)
     */
    private function getNetworkActivity(): array
    {
        try {
            // For web applications, we can track database connections and active sessions
            $activeConnections = $this->getActiveConnections();
            $networkStatus = $this->checkNetworkConnectivity();

            return [
                'active_connections' => $activeConnections,
                'database_connectivity' => $networkStatus['database'],
                'external_connectivity' => $networkStatus['external'],
                'status' => 'active'
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to get network activity: ' . $e->getMessage());
            return [
                'active_connections' => 0,
                'database_connectivity' => false,
                'external_connectivity' => false,
                'status' => 'unavailable'
            ];
        }
    }

    /**
     * Get active database connections
     */
    private function getActiveConnections(): int
    {
        try {
            // For SQL Server, get active connections
            $result = DB::select("SELECT COUNT(*) as count FROM sys.dm_exec_sessions WHERE is_user_process = 1");
            return $result[0]->count ?? 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Check network connectivity
     */
    private function checkNetworkConnectivity(): array
    {
        $database = false;
        $external = false;

        try {
            // Test database connectivity
            DB::connection()->getPdo();
            $database = true;
        } catch (\Throwable $e) {
            // Database connection failed
        }

        try {
            // Test external connectivity (optional)
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            $result = @file_get_contents('http://www.google.com', false, $context);
            $external = $result !== false;
        } catch (\Throwable $e) {
            // External connectivity failed
        }

        return [
            'database' => $database,
            'external' => $external
        ];
    }

    /**
     * Get database-specific metrics
     */
    private function getDatabaseMetrics(): array
    {
        try {
            $connectionStatus = $this->checkDatabaseConnection();
            $queryPerformance = $this->getDatabasePerformance();
            $size = $this->getDatabaseSize();

            return [
                'connection_status' => $connectionStatus,
                'performance' => $queryPerformance,
                'size' => $size,
                'status' => 'active'
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to get database metrics: ' . $e->getMessage());
            return [
                'connection_status' => false,
                'performance' => ['avg_query_time' => 0],
                'size' => ['total' => 0],
                'status' => 'unavailable'
            ];
        }
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get database performance metrics
     */
    private function getDatabasePerformance(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $queryTime = (microtime(true) - $start) * 1000; // Convert to milliseconds

            return [
                'avg_query_time' => round($queryTime, 2),
                'last_query_time' => round($queryTime, 2)
            ];
        } catch (\Throwable $e) {
            return [
                'avg_query_time' => 0,
                'last_query_time' => 0
            ];
        }
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): array
    {
        try {
            $result = DB::select("
                SELECT 
                    CAST(SUM(CAST(FILEPROPERTY(name, 'SpaceUsed') AS bigint) * 8.0 / 1024) AS DECIMAL(15,2)) AS size_mb
                FROM sys.database_files
                WHERE type = 0
            ");

            $sizeMB = $result[0]->size_mb ?? 0;
            $sizeBytes = $sizeMB * 1024 * 1024;

            return [
                'total' => $sizeBytes,
                'total_formatted' => $this->formatBytes($sizeBytes),
                'size_mb' => $sizeMB
            ];
        } catch (\Throwable $e) {
            return [
                'total' => 0,
                'total_formatted' => '0 B',
                'size_mb' => 0
            ];
        }
    }

    /**
     * Get PHP-specific metrics
     */
    private function getPhpMetrics(): array
    {
        return [
            'version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'opcache_enabled' => extension_loaded('opcache') && ini_get('opcache.enable'),
            'extensions' => [
                'pdo_sqlsrv' => extension_loaded('pdo_sqlsrv'),
                'sqlsrv' => extension_loaded('sqlsrv'),
                'mbstring' => extension_loaded('mbstring'),
                'fileinfo' => extension_loaded('fileinfo')
            ]
        ];
    }

    /**
     * Get system load average
     */
    private function getSystemLoad(): array
    {
        try {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                return [
                    '1min' => $load[0] ?? 0,
                    '5min' => $load[1] ?? 0,
                    '15min' => $load[2] ?? 0,
                    'status' => 'active'
                ];
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }

        return [
            '1min' => 0,
            '5min' => 0,
            '15min' => 0,
            'status' => 'unavailable'
        ];
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $output = shell_exec('wmic os get LastBootUpTime /value 2>nul');
                if ($output && preg_match('/LastBootUpTime=(\d{14})/', $output, $matches)) {
                    $bootTime = \DateTime::createFromFormat('YmdHis', $matches[1]);
                    if ($bootTime) {
                        $uptime = time() - $bootTime->getTimestamp();
                        return [
                            'seconds' => $uptime,
                            'formatted' => $this->formatUptime($uptime),
                            'status' => 'active'
                        ];
                    }
                }
            } else {
                $uptime = file_get_contents('/proc/uptime');
                if ($uptime) {
                    $seconds = (int)floatval(trim($uptime));
                    return [
                        'seconds' => $seconds,
                        'formatted' => $this->formatUptime($seconds),
                        'status' => 'active'
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Ignore errors
        }

        return [
            'seconds' => 0,
            'formatted' => 'Unknown',
            'status' => 'unavailable'
        ];
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int)$limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Format uptime seconds to human readable format
     */
    private function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';

        return empty($parts) ? '< 1m' : implode(' ', $parts);
    }
}
