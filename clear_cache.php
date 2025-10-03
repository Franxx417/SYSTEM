<?php
/**
 * Cache Clearing Utility for Procurement System
 * 
 * This script provides a quick way to clear all application caches
 * without using artisan commands. Useful for troubleshooting timeout issues.
 * 
 * Usage: php clear_cache.php
 */

// Load Laravel bootstrap
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

echo "=================================================\n";
echo "  Procurement System - Cache Clearing Utility\n";
echo "=================================================\n\n";

// Clear all caches
echo "Clearing application cache...\n";
try {
    Cache::flush();
    echo "✓ Application cache cleared\n";
} catch (Exception $e) {
    echo "✗ Error clearing application cache: " . $e->getMessage() . "\n";
}

// Clear configuration cache
echo "\nClearing configuration cache...\n";
try {
    Artisan::call('config:clear');
    echo "✓ Configuration cache cleared\n";
} catch (Exception $e) {
    echo "✗ Error clearing config cache: " . $e->getMessage() . "\n";
}

// Clear view cache
echo "\nClearing view cache...\n";
try {
    Artisan::call('view:clear');
    echo "✓ View cache cleared\n";
} catch (Exception $e) {
    echo "✗ Error clearing view cache: " . $e->getMessage() . "\n";
}

// Clear route cache
echo "\nClearing route cache...\n";
try {
    Artisan::call('route:clear');
    echo "✓ Route cache cleared\n";
} catch (Exception $e) {
    echo "✗ Error clearing route cache: " . $e->getMessage() . "\n";
}

// Clear specific dashboard caches
echo "\nClearing dashboard metric caches...\n";
try {
    $patterns = [
        'dashboard_metrics_requestor_*',
        'dashboard_metrics_authorized_personnel_*',
        'dashboard_metrics_superadmin_*',
        'status_colors',
        'dynamic_status_css',
    ];
    
    foreach ($patterns as $pattern) {
        // For file cache, we need to manually clear
        if (config('cache.default') === 'file') {
            $cacheDir = storage_path('framework/cache/data');
            $files = glob($cacheDir . '/*/' . md5($pattern) . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        echo "✓ Cleared cache pattern: {$pattern}\n";
    }
    
    // Also try to forget specific keys
    Cache::forget('status_colors');
    Cache::forget('dynamic_status_css');
    
} catch (Exception $e) {
    echo "✗ Error clearing dashboard caches: " . $e->getMessage() . "\n";
}

// Optimize after clearing
echo "\nOptimizing application...\n";
try {
    Artisan::call('optimize:clear');
    echo "✓ Application optimized\n";
} catch (Exception $e) {
    echo "✗ Error optimizing: " . $e->getMessage() . "\n";
}

echo "\n=================================================\n";
echo "  Cache clearing completed!\n";
echo "=================================================\n\n";

echo "Next steps:\n";
echo "1. Restart your web server (nginx/apache)\n";
echo "2. Restart PHP-FPM if applicable\n";
echo "3. Test the application by logging in\n";
echo "4. Monitor the first load time (may be slower)\n";
echo "5. Subsequent loads should be fast (cached)\n\n";

echo "To monitor cache status:\n";
echo "  php artisan cache:table\n";
echo "  php artisan config:show cache\n\n";
