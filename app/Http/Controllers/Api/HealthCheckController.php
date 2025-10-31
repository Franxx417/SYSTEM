<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Health Check Controller
 * 
 * Provides system health monitoring endpoints
 * Used for service monitoring and alerting
 */
class HealthCheckController extends Controller
{
    use ApiResponseTrait;
    
    /**
     * Basic health check
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->successResponse([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'uptime' => $this->getUptime()
        ], 'Service is healthy');
    }
    
    /**
     * Detailed health check
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailed()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'memory' => $this->checkMemory()
        ];
        
        $overallStatus = $this->determineOverallStatus($checks);
        
        return response()->json([
            'success' => true,
            'status' => $overallStatus,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
            'uptime' => $this->getUptime(),
            'version' => '1.0.0'
        ], $overallStatus === 'healthy' ? 200 : 503);
    }
    
    /**
     * Check database connectivity
     *
     * @return array
     */
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => 'healthy',
                'response_time_ms' => $duration,
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check cache functionality
     *
     * @return array
     */
    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            $value = 'test_' . rand(1000, 9999);
            
            $start = microtime(true);
            Cache::put($key, $value, 10);
            $retrieved = Cache::get($key);
            Cache::forget($key);
            $duration = round((microtime(true) - $start) * 1000, 2);
            
            if ($retrieved === $value) {
                return [
                    'status' => 'healthy',
                    'response_time_ms' => $duration,
                    'message' => 'Cache working properly'
                ];
            }
            
            return [
                'status' => 'degraded',
                'message' => 'Cache value mismatch'
            ];
            
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'message' => 'Cache check failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check storage availability
     *
     * @return array
     */
    private function checkStorage(): array
    {
        try {
            $path = storage_path('logs');
            $writable = is_writable($path);
            $readable = is_readable($path);
            
            if ($writable && $readable) {
                return [
                    'status' => 'healthy',
                    'message' => 'Storage is accessible',
                    'writable' => true,
                    'readable' => true
                ];
            }
            
            return [
                'status' => 'degraded',
                'message' => 'Storage has limited access',
                'writable' => $writable,
                'readable' => $readable
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'message' => 'Storage check failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check memory usage
     *
     * @return array
     */
    private function checkMemory(): array
    {
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');
            
            // Convert memory limit to bytes
            $limit = $this->convertToBytes($memoryLimit);
            $usagePercent = ($memoryUsage / $limit) * 100;
            
            $status = 'healthy';
            if ($usagePercent > 90) {
                $status = 'critical';
            } elseif ($usagePercent > 75) {
                $status = 'degraded';
            }
            
            return [
                'status' => $status,
                'usage_bytes' => $memoryUsage,
                'usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'limit' => $memoryLimit,
                'usage_percent' => round($usagePercent, 2),
                'message' => "Memory usage at {$usagePercent}%"
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'message' => 'Memory check failed',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get system uptime
     *
     * @return string
     */
    private function getUptime(): string
    {
        try {
            $uptime = file_get_contents('/proc/uptime');
            $seconds = (int)explode(' ', $uptime)[0];
            
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            
            return "{$days}d {$hours}h {$minutes}m";
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
    
    /**
     * Determine overall status from checks
     *
     * @param array $checks
     * @return string
     */
    private function determineOverallStatus(array $checks): string
    {
        foreach ($checks as $check) {
            if ($check['status'] === 'unhealthy' || $check['status'] === 'critical') {
                return 'unhealthy';
            }
        }
        
        foreach ($checks as $check) {
            if ($check['status'] === 'degraded') {
                return 'degraded';
            }
        }
        
        return 'healthy';
    }
    
    /**
     * Convert memory limit string to bytes
     *
     * @param string $value
     * @return int
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int)$value;
        
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
}
