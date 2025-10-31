<?php

return [

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | These values control UI behavior, pagination, and display limits
    | throughout the application. Adjust these to customize the user experience.
    |
    */

    'pagination' => [
        // Default number of items per page for most listings
        'default' => env('UI_PAGINATION_DEFAULT', 10),
        
        // Small lists (recent items, dashboard widgets)
        'small' => env('UI_PAGINATION_SMALL', 5),
        
        // Large lists (admin tables, reports)
        'large' => env('UI_PAGINATION_LARGE', 20),
        
        // Maximum items for dropdowns/autocomplete
        'autocomplete' => env('UI_PAGINATION_AUTOCOMPLETE', 200),
        
        // Recent activity/logs
        'recent' => env('UI_PAGINATION_RECENT', 10),
        
        // Active sessions list
        'sessions' => env('UI_PAGINATION_SESSIONS', 50),
    ],

    'cache' => [
        // Dashboard metrics cache duration (seconds)
        'dashboard' => env('UI_CACHE_DASHBOARD', 300), // 5 minutes
        
        // System metrics cache duration (seconds)
        'metrics' => env('UI_CACHE_METRICS', 60), // 1 minute
        
        // Dropdown data cache duration (seconds)
        'dropdown' => env('UI_CACHE_DROPDOWN', 3600), // 1 hour
    ],

    'auto_refresh' => [
        // Enable automatic metric refresh on overview tab
        'enabled' => env('UI_AUTO_REFRESH_ENABLED', true),
        
        // Auto-refresh interval in milliseconds (default: 30 seconds)
        'interval_ms' => env('UI_AUTO_REFRESH_INTERVAL_MS', 30000),
    ],

    'notifications' => [
        // Auto-dismiss duration for success messages (milliseconds)
        'success_duration_ms' => env('UI_NOTIFICATION_SUCCESS_MS', 5000),
        
        // Auto-dismiss duration for error messages (milliseconds)
        'error_duration_ms' => env('UI_NOTIFICATION_ERROR_MS', 5000),
        
        // Alert auto-dismiss duration (milliseconds)
        'alert_duration_ms' => env('UI_ALERT_DURATION_MS', 5000),
    ],

    'delays' => [
        // Page reload delay after successful action (milliseconds)
        'reload_ms' => env('UI_DELAY_RELOAD_MS', 1000),
        
        // Modal initialization delay (milliseconds)
        'modal_init_ms' => env('UI_DELAY_MODAL_INIT_MS', 100),
        
        // System restart delay after update (milliseconds)
        'system_restart_ms' => env('UI_DELAY_SYSTEM_RESTART_MS', 3000),
        
        // Session termination reload delay (milliseconds)
        'session_terminate_ms' => env('UI_DELAY_SESSION_TERMINATE_MS', 2000),
    ],

    'timeouts' => [
        // API call timeout (seconds)
        'api_seconds' => env('UI_TIMEOUT_API_SECONDS', 10),
        
        // Database operation timeout (seconds)
        'database_seconds' => env('UI_TIMEOUT_DATABASE_SECONDS', 30),
        
        // File upload timeout (seconds)
        'upload_seconds' => env('UI_TIMEOUT_UPLOAD_SECONDS', 60),
    ],

    'labels' => [
        // Application branding
        'app_subtitle' => env('UI_APP_SUBTITLE', 'Management System'),
        
        // Section headers
        'system_management' => 'System Management',
        'system_administration' => 'System Administration',
        
        // Common UI text
        'no_data' => 'No data available',
        'loading' => 'Loading...',
        'please_wait' => 'Please wait...',
    ],

];
