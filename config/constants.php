<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Constants
    |--------------------------------------------------------------------------
    |
    | This file contains all application constants that were previously
    | hardcoded throughout the codebase. These values can be overridden
    | by environment variables or database settings.
    |
    */

    // Cache Configuration
    'cache' => [
        'dashboard_duration' => env('CACHE_DASHBOARD_DURATION', 300), // 5 minutes
        'metrics_duration' => env('CACHE_METRICS_DURATION', 3600), // 1 hour
        'settings_duration' => env('CACHE_SETTINGS_DURATION', 3600), // 1 hour
    ],

    // Pagination Limits
    'pagination' => [
        'default_limit' => env('PAGINATION_DEFAULT_LIMIT', 50),
        'dashboard_recent_limit' => env('DASHBOARD_RECENT_LIMIT', 5),
        'dashboard_suppliers_limit' => env('DASHBOARD_SUPPLIERS_LIMIT', 5),
        'dashboard_users_limit' => env('DASHBOARD_USERS_LIMIT', 10),
        'dashboard_statuses_limit' => env('DASHBOARD_STATUSES_LIMIT', 20),
        'logs_limit' => env('LOGS_LIMIT', 100),
        'table_chunk_size' => env('TABLE_CHUNK_SIZE', 500),
    ],

    // Security Settings
    'security' => [
        'session_timeout' => env('SECURITY_SESSION_TIMEOUT', 120), // minutes
        'max_login_attempts' => env('SECURITY_MAX_LOGIN_ATTEMPTS', 5),
        'password_min_length' => env('SECURITY_PASSWORD_MIN_LENGTH', 8),
        'lockout_duration' => env('SECURITY_LOCKOUT_DURATION', 15), // minutes
        'failed_login_threshold' => env('SECURITY_FAILED_LOGIN_THRESHOLD', 10),
    ],

    // User Roles
    'roles' => [
        'requestor' => env('ROLE_REQUESTOR', 'requestor'),
        'superadmin' => env('ROLE_SUPERADMIN', 'superadmin'),
    ],

    // Status Names
    'statuses' => [
        'pending' => env('STATUS_PENDING', 'Pending'),
        'verified' => env('STATUS_VERIFIED', 'Verified'),
        'approved' => env('STATUS_APPROVED', 'Approved'),
        'received' => env('STATUS_RECEIVED', 'Received'),
        'rejected' => env('STATUS_REJECTED', 'Rejected'),
        'draft' => env('STATUS_DRAFT', 'Draft'),
        'default' => env('STATUS_DEFAULT', 'Pending'),
    ],

    // Database Configuration
    'database' => [
        'query_timeout' => env('DB_QUERY_TIMEOUT', 30),
        'connection_timeout' => env('DB_CONNECTION_TIMEOUT', 30),
        'optimization_timeout' => env('DB_OPTIMIZATION_TIMEOUT', 30),
    ],

    // File Upload Limits
    'uploads' => [
        'logo_max_size' => env('UPLOAD_LOGO_MAX_SIZE', 2048), // KB
        'logo_allowed_types' => env('UPLOAD_LOGO_TYPES', 'png,jpg,jpeg,svg'),
        'backup_max_size' => env('UPLOAD_BACKUP_MAX_SIZE', 10240), // KB
    ],

    // System Monitoring
    'monitoring' => [
        'auto_refresh_interval' => env('MONITORING_AUTO_REFRESH', 30000), // milliseconds
        'cpu_check_interval' => env('MONITORING_CPU_INTERVAL', 30), // seconds
        'memory_check_interval' => env('MONITORING_MEMORY_INTERVAL', 30), // seconds
        'disk_check_interval' => env('MONITORING_DISK_INTERVAL', 60), // seconds
    ],

    // API Configuration
    'api' => [
        'timeout' => env('API_TIMEOUT', 30),
        'retry_attempts' => env('API_RETRY_ATTEMPTS', 3),
        'rate_limit' => env('API_RATE_LIMIT', 100), // requests per minute
    ],

    // Notification Settings
    'notifications' => [
        'auto_dismiss_delay' => env('NOTIFICATION_AUTO_DISMISS', 5000), // milliseconds
        'max_notifications' => env('NOTIFICATION_MAX_COUNT', 5),
    ],

    // UI Configuration
    'ui' => [
        'table_responsive_breakpoint' => env('UI_TABLE_BREAKPOINT', 991.98),
        'modal_max_width' => env('UI_MODAL_MAX_WIDTH', 95), // percentage
        'sidebar_width' => env('UI_SIDEBAR_WIDTH', 240), // pixels
    ],

    // Application Settings
    'app' => [
        'name' => env('APP_NAME', 'Procurement System'),
        'version' => env('APP_VERSION', '1.0.0'),
        'timezone' => env('APP_TIMEZONE', 'UTC'),
        'locale' => env('APP_LOCALE', 'en'),
        'maintenance_mode' => env('APP_MAINTENANCE_MODE', false),
    ],

    // Error Messages
    'messages' => [
        'unauthorized' => env('MSG_UNAUTHORIZED', 'Unauthorized: Access denied'),
        'forbidden' => env('MSG_FORBIDDEN', 'Forbidden: Insufficient permissions'),
        'not_found' => env('MSG_NOT_FOUND', 'Resource not found'),
        'validation_failed' => env('MSG_VALIDATION_FAILED', 'Validation failed'),
        'server_error' => env('MSG_SERVER_ERROR', 'Internal server error'),
        'database_error' => env('MSG_DATABASE_ERROR', 'Database operation failed'),
    ],

    // HTTP Status Codes
    'http_codes' => [
        'success' => 200,
        'created' => 201,
        'bad_request' => 400,
        'unauthorized' => 401,
        'forbidden' => 403,
        'not_found' => 404,
        'validation_error' => 422,
        'server_error' => 500,
    ],

    // System Limits
    'limits' => [
        'max_file_size' => env('LIMIT_MAX_FILE_SIZE', 10), // MB
        'max_memory_usage' => env('LIMIT_MAX_MEMORY', 128), // MB
        'max_execution_time' => env('LIMIT_MAX_EXECUTION_TIME', 300), // seconds
        'max_upload_files' => env('LIMIT_MAX_UPLOAD_FILES', 10),
    ],

    // Backup Configuration
    'backup' => [
        'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'compression_level' => env('BACKUP_COMPRESSION_LEVEL', 6),
        'chunk_size' => env('BACKUP_CHUNK_SIZE', 1024), // KB
    ],

    // Logging Configuration
    'logging' => [
        'max_file_size' => env('LOG_MAX_FILE_SIZE', 10), // MB
        'max_files' => env('LOG_MAX_FILES', 5),
        'level' => env('LOG_LEVEL', 'info'),
        'retention_days' => env('LOG_RETENTION_DAYS', 30),
    ],
];




