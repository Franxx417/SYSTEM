<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination views
        Paginator::useBootstrapFive();

        // Prefer our custom pagination view if present
        try {
            if (view()->exists('vendor.pagination.custom')) {
                Paginator::defaultView('vendor.pagination.custom');
            }
        } catch (\Throwable $e) {
            // If view resolution causes any issue, ignore and keep defaults
        }
    }
}
