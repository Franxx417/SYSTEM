<?php

/**
 * Debug script for SuperAdmin functionality
 * This script provides detailed error logs and troubleshooting information
 */

require_once __DIR__ . '/vendor/autoload.php';

class SuperAdminDebugger
{
    private $logFile;
    private $baseUrl;
    
    public function __construct($baseUrl = 'http://localhost:8000')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->logFile = __DIR__ . '/storage/logs/superadmin_debug.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log debug information
     */
    private function log($message, $level = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] {$message}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        echo $logEntry;
    }
    
    /**
     * Check database connectivity
     */
    public function checkDatabase()
    {
        $this->log("=== DATABASE CONNECTIVITY CHECK ===");
        
        try {
            // Check if we can connect to the database
            $response = $this->makeRequest('GET', '/api/superadmin/database/table-info');
            
            if ($response['success']) {
                $this->log("✅ Database connection successful");
                $this->log("Found " . count($response['data']) . " tables");
                
                foreach ($response['data'] as $table) {
                    $status = $table['status'] ?? 'Unknown';
                    $count = $table['count'] ?? 'N/A';
                    $this->log("  - {$table['name']}: {$count} records, Status: {$status}");
                    
                    if (isset($table['error'])) {
                        $this->log("    ERROR: {$table['error']}", 'ERROR');
                    }
                }
            } else {
                $this->log("❌ Database connection failed: " . ($response['error'] ?? 'Unknown error'), 'ERROR');
                $this->log("HTTP Code: " . ($response['http_code'] ?? 'N/A'), 'ERROR');
            }
        } catch (Exception $e) {
            $this->log("❌ Database check exception: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Check API endpoints
     */
    public function checkApiEndpoints()
    {
        $this->log("\n=== API ENDPOINTS CHECK ===");
        
        $endpoints = [
            'GET /api/superadmin/metrics' => 'System Metrics',
            'GET /api/superadmin/database/table-info' => 'Database Info',
            'GET /api/superadmin/logs/recent' => 'Recent Logs',
            'POST /api/superadmin/users/reset-password' => 'Password Reset',
            'POST /api/superadmin/branding/update' => 'Branding Update'
        ];
        
        foreach ($endpoints as $endpoint => $description) {
            $this->log("Testing: {$description}");
            
            list($method, $path) = explode(' ', $endpoint, 2);
            
            $testData = null;
            if ($method === 'POST') {
                switch ($path) {
                    case '/api/superadmin/users/reset-password':
                        $testData = ['user_id' => 'test-user-id'];
                        break;
                    case '/api/superadmin/branding/update':
                        $testData = ['app_name' => 'Test App'];
                        break;
                }
            }
            
            $response = $this->makeRequest($method, $path, $testData);
            
            if ($response['success']) {
                $this->log("  ✅ {$description}: OK");
            } else {
                $this->log("  ❌ {$description}: " . ($response['error'] ?? 'Failed'), 'ERROR');
                $this->log("     HTTP Code: " . ($response['http_code'] ?? 'N/A'), 'ERROR');
            }
        }
    }
    
    /**
     * Check file permissions and paths
     */
    public function checkFileSystem()
    {
        $this->log("\n=== FILESYSTEM CHECK ===");
        
        $paths = [
            'storage/logs' => 'Log Directory',
            'storage/app/public' => 'Public Storage',
            'storage/app/public/branding' => 'Branding Directory',
            'storage/logs/laravel.log' => 'Laravel Log File'
        ];
        
        foreach ($paths as $path => $description) {
            $fullPath = __DIR__ . '/' . $path;
            
            if (file_exists($fullPath)) {
                $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
                $readable = is_readable($fullPath) ? 'Yes' : 'No';
                $writable = is_writable($fullPath) ? 'Yes' : 'No';
                
                $this->log("✅ {$description}: Exists");
                $this->log("   Path: {$fullPath}");
                $this->log("   Permissions: {$perms}, Readable: {$readable}, Writable: {$writable}");
            } else {
                $this->log("❌ {$description}: Missing", 'ERROR');
                $this->log("   Expected path: {$fullPath}", 'ERROR');
            }
        }
    }
    
    /**
     * Check Laravel configuration
     */
    public function checkConfiguration()
    {
        $this->log("\n=== CONFIGURATION CHECK ===");
        
        // Check if .env file exists
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $this->log("✅ .env file exists");
            
            // Check key configuration values
            $envContent = file_get_contents($envFile);
            $configs = [
                'APP_ENV' => 'Application Environment',
                'APP_DEBUG' => 'Debug Mode',
                'DB_CONNECTION' => 'Database Connection',
                'DB_HOST' => 'Database Host',
                'DB_DATABASE' => 'Database Name'
            ];
            
            foreach ($configs as $key => $description) {
                if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
                    $value = trim($matches[1]);
                    $this->log("  {$description}: {$value}");
                } else {
                    $this->log("  {$description}: Not set", 'WARNING');
                }
            }
        } else {
            $this->log("❌ .env file missing", 'ERROR');
        }
        
        // Check if vendor directory exists
        if (is_dir(__DIR__ . '/vendor')) {
            $this->log("✅ Vendor directory exists");
        } else {
            $this->log("❌ Vendor directory missing - run 'composer install'", 'ERROR');
        }
    }
    
    /**
     * Check route definitions
     */
    public function checkRoutes()
    {
        $this->log("\n=== ROUTE DEFINITIONS CHECK ===");
        
        $routeFiles = [
            'routes/web.php' => 'Web Routes',
            'routes/api.php' => 'API Routes'
        ];
        
        foreach ($routeFiles as $file => $description) {
            $fullPath = __DIR__ . '/' . $file;
            
            if (file_exists($fullPath)) {
                $this->log("✅ {$description}: Exists");
                
                $content = file_get_contents($fullPath);
                
                // Check for SuperAdmin routes
                $superadminRoutes = [
                    'superadmin' => 'SuperAdmin prefix',
                    'SuperAdminController' => 'SuperAdmin controller',
                    'resetUserPasswordApi' => 'Password reset API',
                    'getDatabaseInfo' => 'Database info API',
                    'getRecentLogsApi' => 'Logs API',
                    'updateBrandingApi' => 'Branding API'
                ];
                
                foreach ($superadminRoutes as $pattern => $routeDesc) {
                    if (strpos($content, $pattern) !== false) {
                        $this->log("  ✅ {$routeDesc}: Found");
                    } else {
                        $this->log("  ❌ {$routeDesc}: Missing", 'WARNING');
                    }
                }
            } else {
                $this->log("❌ {$description}: Missing", 'ERROR');
            }
        }
    }
    
    /**
     * Generate comprehensive debug report
     */
    public function generateReport()
    {
        $this->log("🔍 SUPERADMIN DEBUG REPORT");
        $this->log("Generated: " . date('Y-m-d H:i:s'));
        $this->log("Base URL: {$this->baseUrl}");
        $this->log(str_repeat("=", 60));
        
        $this->checkConfiguration();
        $this->checkFileSystem();
        $this->checkRoutes();
        $this->checkDatabase();
        $this->checkApiEndpoints();
        
        $this->log("\n" . str_repeat("=", 60));
        $this->log("🏁 DEBUG REPORT COMPLETE");
        $this->log("Log file: {$this->logFile}");
        
        return $this->logFile;
    }
    
    /**
     * Make HTTP request with detailed error logging
     */
    private function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-Requested-With: XMLHttpRequest'
            ]
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => "cURL Error: {$error}",
                'http_code' => $httpCode
            ];
        }
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return $decoded ?: ['success' => true];
        } else {
            return [
                'success' => false,
                'error' => $decoded['error'] ?? "HTTP {$httpCode}",
                'http_code' => $httpCode,
                'response_body' => $response
            ];
        }
    }
}

// Run the debugger if this script is executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $debugger = new SuperAdminDebugger();
    $logFile = $debugger->generateReport();
    
    echo "\n📋 Debug report saved to: {$logFile}\n";
    echo "You can review this file for detailed troubleshooting information.\n";
}
