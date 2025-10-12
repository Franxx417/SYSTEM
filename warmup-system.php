<?php
/**
 * System Warmup Script
 * 
 * This script prevents 504 Gateway Time-out errors by pre-warming the application
 * before users access it. Run this after deployment or server restart.
 * 
 * Usage:
 *   php warmup-system.php
 */

echo "==============================================\n";
echo "   Laravel System Warmup & Cache Builder\n";
echo "==============================================\n\n";

// Ensure we're in the right directory
if (!file_exists('artisan')) {
    echo "❌ Error: Must be run from Laravel root directory\n";
    exit(1);
}

$startTime = microtime(true);

// Function to run artisan commands
function runArtisan($command, $description) {
    echo "⏳ {$description}...\n";
    $output = [];
    $returnVar = 0;
    exec("php artisan {$command} 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "✅ {$description} - DONE\n\n";
        return true;
    } else {
        echo "⚠️  {$description} - WARNING\n";
        echo implode("\n", $output) . "\n\n";
        return false;
    }
}

// Step 1: Clear all existing caches
echo "Step 1: Clearing Existing Caches\n";
echo "─────────────────────────────────\n";
runArtisan('config:clear', 'Clearing config cache');
runArtisan('cache:clear', 'Clearing application cache');
runArtisan('route:clear', 'Clearing route cache');
runArtisan('view:clear', 'Clearing compiled views');
runArtisan('clear-compiled', 'Clearing compiled classes');

// Step 2: Optimize Composer Autoloader
echo "\nStep 2: Optimizing Composer Autoloader\n";
echo "───────────────────────────────────────\n";
echo "⏳ Optimizing composer autoloader...\n";
exec('composer dump-autoload --optimize --no-dev 2>&1', $output, $returnVar);
if ($returnVar === 0) {
    echo "✅ Composer autoloader optimized - DONE\n\n";
} else {
    echo "⚠️  Composer optimization skipped (run manually if needed)\n\n";
}

// Step 3: Build Laravel Caches
echo "\nStep 3: Building Laravel Caches\n";
echo "───────────────────────────────\n";
runArtisan('config:cache', 'Building config cache');
runArtisan('route:cache', 'Building route cache');
runArtisan('view:cache', 'Compiling all Blade views');

// Step 4: Build Application Caches (Pre-warm critical caches)
echo "\nStep 4: Pre-warming Application Caches\n";
echo "──────────────────────────────────────\n";
echo "⏳ Pre-warming status colors cache...\n";

// Create a simple bootstrap to warm up application caches
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

try {
    // Warm up status colors cache
    $statusManager = $app->make(App\Services\StatusConfigManager::class);
    cache()->remember('status_colors', 3600, function () use ($statusManager) {
        return $statusManager->getStatusColors();
    });
    echo "✅ Status colors cache - WARMED\n";
    
    // Warm up dynamic CSS cache
    cache()->remember('dynamic_status_css', 86400, function () use ($statusManager) {
        return $statusManager->generateStatusCss();
    });
    echo "✅ Dynamic CSS cache - WARMED\n\n";
    
} catch (Exception $e) {
    echo "⚠️  Application cache warmup skipped: " . $e->getMessage() . "\n\n";
}

// Step 5: Verify Storage Permissions
echo "\nStep 5: Checking Storage Permissions\n";
echo "────────────────────────────────────\n";
$storageWritable = is_writable('storage/framework/cache');
$logsWritable = is_writable('storage/logs');

if ($storageWritable && $logsWritable) {
    echo "✅ Storage directories are writable\n\n";
} else {
    echo "⚠️  Storage permission issues detected:\n";
    if (!$storageWritable) echo "   - storage/framework/cache is not writable\n";
    if (!$logsWritable) echo "   - storage/logs is not writable\n";
    echo "\n   Run: chmod -R 775 storage bootstrap/cache\n";
    echo "   Or:  chown -R www-data:www-data storage bootstrap/cache\n\n";
}

// Step 6: System Information
echo "\nStep 6: System Information\n";
echo "─────────────────────────\n";
$phpVersion = phpversion();
$memoryLimit = ini_get('memory_limit');
$maxExecution = ini_get('max_execution_time');

echo "PHP Version: {$phpVersion}\n";
echo "Memory Limit: {$memoryLimit}\n";
echo "Max Execution Time: {$maxExecution}s\n";

// Check for opcache
$opcacheEnabled = extension_loaded('opcache') && ini_get('opcache.enable');
if ($opcacheEnabled) {
    echo "OPcache: ✅ ENABLED\n";
} else {
    echo "OPcache: ⚠️  DISABLED (recommended for production)\n";
}

// Summary
$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo "\n==============================================\n";
echo "   Warmup Complete in {$duration}s\n";
echo "==============================================\n\n";

echo "✅ Your application is now warmed up and ready!\n";
echo "\nNext Steps:\n";
echo "  1. Access your application in a browser\n";
echo "  2. First page load should be fast (< 5 seconds)\n";
echo "  3. Subsequent loads will be even faster (cached)\n";
echo "\nIf you still experience 504 errors:\n";
echo "  1. Check nginx error logs: /var/log/nginx/error.log\n";
echo "  2. Review TIMEOUT_FIX_GUIDE.md\n";
echo "  3. Increase nginx/PHP-FPM timeouts to 180s or 300s\n\n";
