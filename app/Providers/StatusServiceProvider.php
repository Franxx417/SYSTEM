<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\StatusConfigManager;
use App\Helpers\StatusHelper;

/**
 * Status Service Provider
 * Registers status-related services and Blade directives
 */
class StatusServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(StatusConfigManager::class, function ($app) {
            return new StatusConfigManager();
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Register Blade directives for status helpers
        Blade::directive('statusIndicator', function ($expression) {
            return "<?php echo App\Helpers\StatusHelper::statusIndicator({$expression}); ?>";
        });

        Blade::directive('statusBadge', function ($expression) {
            return "<?php echo App\Helpers\StatusHelper::statusBadge({$expression}); ?>";
        });

        Blade::directive('statusClass', function ($expression) {
            return "<?php echo App\Helpers\StatusHelper::getStatusClass({$expression}); ?>";
        });

        // Register a view composer ONLY for views that need status colors
        // This prevents unnecessary overhead on every view load
        view()->composer([
            'dashboards.*',
            'po.*',
            'partials.status-display',
            'approvals.*'
        ], function ($view) {
            // Cache status colors for 1 hour to reduce database queries
            $statusColors = cache()->remember('status_colors', 3600, function () {
                $statusManager = app(StatusConfigManager::class);
                return $statusManager->getStatusColors();
            });
            $view->with('statusColors', $statusColors);
        });

        // Add dynamic CSS route with extended caching
        if ($this->app->runningInConsole() === false) {
            \Illuminate\Support\Facades\Route::get('/css/dynamic-status.css', function () {
                // Cache generated CSS for 24 hours
                $css = cache()->remember('dynamic_status_css', 86400, function () {
                    $statusManager = app(StatusConfigManager::class);
                    return $statusManager->generateStatusCss();
                });
                
                return response($css)
                    ->header('Content-Type', 'text/css')
                    ->header('Cache-Control', 'public, max-age=86400');
            })->name('dynamic.status.css');
        }
    }
}
