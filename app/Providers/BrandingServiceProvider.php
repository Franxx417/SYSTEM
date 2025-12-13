<?php

namespace App\Providers;

use App\Services\BrandingService;
use Illuminate\Support\ServiceProvider;

class BrandingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BrandingService::class, function ($app) {
            return new BrandingService;
        });

        $this->app->alias(BrandingService::class, 'branding');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share branding data with all views
        view()->composer('*', function ($view) {
            try {
                $branding = app(BrandingService::class);
                $view->with('brandingService', $branding);
                $view->with('brandSettings', $branding->getAll());
            } catch (\Throwable $e) {
                // Fail silently to prevent breaking views - provide defaults
                $view->with('brandingService', null);
                $view->with('brandSettings', []);
            }
        });
    }
}
