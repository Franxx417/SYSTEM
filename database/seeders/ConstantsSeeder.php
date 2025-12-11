<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\ConstantsService;

class ConstantsSeeder extends Seeder
{
    /**
     * Seed the system_settings table with default constants
     */
    public function run(): void
    {
        $constants = [
            // Cache Configuration
            'cache.dashboard_duration' => ['value' => 300, 'category' => 'cache', 'type' => 'integer', 'description' => 'Dashboard cache duration in seconds'],
            'cache.metrics_duration' => ['value' => 3600, 'category' => 'cache', 'type' => 'integer', 'description' => 'Metrics cache duration in seconds'],
            'cache.settings_duration' => ['value' => 3600, 'category' => 'cache', 'type' => 'integer', 'description' => 'Settings cache duration in seconds'],

            // Pagination Limits
            'pagination.default_limit' => ['value' => 50, 'category' => 'pagination', 'type' => 'integer', 'description' => 'Default pagination limit'],
            'pagination.dashboard_recent_limit' => ['value' => 5, 'category' => 'pagination', 'type' => 'integer', 'description' => 'Dashboard recent items limit'],
            'pagination.dashboard_suppliers_limit' => ['value' => 5, 'category' => 'pagination', 'type' => 'integer', 'description' => 'Dashboard suppliers limit'],
            'pagination.dashboard_users_limit' => ['value' => 10, 'category' => 'pagination', 'type' => 'integer', 'description' => 'Dashboard users limit'],
            'pagination.dashboard_statuses_limit' => ['value' => 20, 'category' => 'pagination', 'type' => 'integer', 'description' => 'Dashboard statuses limit'],
            'pagination.logs_limit' => ['value' => 100, 'category' => 'pagination', 'type' => 'integer', 'description' => 'Logs display limit'],
            'pagination.table_chunk_size' => ['value' => 500, 'category' => 'pagination', 'type' => 'integer', 'description' => 'Table chunk size for bulk operations'],

            // Security Settings
            'security.session_timeout' => ['value' => 120, 'category' => 'security', 'type' => 'integer', 'description' => 'Session timeout in minutes'],
            'security.max_login_attempts' => ['value' => 5, 'category' => 'security', 'type' => 'integer', 'description' => 'Maximum login attempts before lockout'],
            'security.password_min_length' => ['value' => 8, 'category' => 'security', 'type' => 'integer', 'description' => 'Minimum password length'],
            'security.lockout_duration' => ['value' => 15, 'category' => 'security', 'type' => 'integer', 'description' => 'Account lockout duration in minutes'],
            'security.failed_login_threshold' => ['value' => 10, 'category' => 'security', 'type' => 'integer', 'description' => 'Failed login threshold for alerts'],

            // User Roles
            'roles.requestor' => ['value' => 'requestor', 'category' => 'roles', 'type' => 'string', 'description' => 'Requestor role identifier'],
            'roles.superadmin' => ['value' => 'superadmin', 'category' => 'roles', 'type' => 'string', 'description' => 'Superadmin role identifier'],

            // Status Names
            'statuses.pending' => ['value' => 'Pending', 'category' => 'statuses', 'type' => 'string', 'description' => 'Pending status name'],
            'statuses.verified' => ['value' => 'Verified', 'category' => 'statuses', 'type' => 'string', 'description' => 'Verified status name'],
            'statuses.approved' => ['value' => 'Approved', 'category' => 'statuses', 'type' => 'string', 'description' => 'Approved status name'],
            'statuses.received' => ['value' => 'Received', 'category' => 'statuses', 'type' => 'string', 'description' => 'Received status name'],
            'statuses.rejected' => ['value' => 'Rejected', 'category' => 'statuses', 'type' => 'string', 'description' => 'Rejected status name'],
            'statuses.draft' => ['value' => 'Draft', 'category' => 'statuses', 'type' => 'string', 'description' => 'Draft status name'],
            'statuses.default' => ['value' => 'Pending', 'category' => 'statuses', 'type' => 'string', 'description' => 'Default status name'],

            // Database Configuration
            'database.query_timeout' => ['value' => 30, 'category' => 'database', 'type' => 'integer', 'description' => 'Database query timeout in seconds'],
            'database.connection_timeout' => ['value' => 30, 'category' => 'database', 'type' => 'integer', 'description' => 'Database connection timeout in seconds'],
            'database.optimization_timeout' => ['value' => 30, 'category' => 'database', 'type' => 'integer', 'description' => 'Database optimization timeout in seconds'],

            // File Upload Limits
            'uploads.logo_max_size' => ['value' => 2048, 'category' => 'uploads', 'type' => 'integer', 'description' => 'Logo upload max size in KB'],
            'uploads.logo_allowed_types' => ['value' => 'png,jpg,jpeg,svg', 'category' => 'uploads', 'type' => 'string', 'description' => 'Logo allowed file types'],
            'uploads.backup_max_size' => ['value' => 10240, 'category' => 'uploads', 'type' => 'integer', 'description' => 'Backup upload max size in KB'],

            // System Monitoring
            'monitoring.auto_refresh_interval' => ['value' => 30000, 'category' => 'monitoring', 'type' => 'integer', 'description' => 'Auto refresh interval in milliseconds'],
            'monitoring.cpu_check_interval' => ['value' => 30, 'category' => 'monitoring', 'type' => 'integer', 'description' => 'CPU check interval in seconds'],
            'monitoring.memory_check_interval' => ['value' => 30, 'category' => 'monitoring', 'type' => 'integer', 'description' => 'Memory check interval in seconds'],
            'monitoring.disk_check_interval' => ['value' => 60, 'category' => 'monitoring', 'type' => 'integer', 'description' => 'Disk check interval in seconds'],

            // API Configuration
            'api.timeout' => ['value' => 30, 'category' => 'api', 'type' => 'integer', 'description' => 'API timeout in seconds'],
            'api.retry_attempts' => ['value' => 3, 'category' => 'api', 'type' => 'integer', 'description' => 'API retry attempts'],
            'api.rate_limit' => ['value' => 100, 'category' => 'api', 'type' => 'integer', 'description' => 'API rate limit per minute'],

            // Notification Settings
            'notifications.auto_dismiss_delay' => ['value' => 5000, 'category' => 'notifications', 'type' => 'integer', 'description' => 'Notification auto dismiss delay in milliseconds'],
            'notifications.max_notifications' => ['value' => 5, 'category' => 'notifications', 'type' => 'integer', 'description' => 'Maximum notifications to display'],

            // UI Configuration
            'ui.table_responsive_breakpoint' => ['value' => 991.98, 'category' => 'ui', 'type' => 'float', 'description' => 'Table responsive breakpoint in pixels'],
            'ui.modal_max_width' => ['value' => 95, 'category' => 'ui', 'type' => 'integer', 'description' => 'Modal max width percentage'],
            'ui.sidebar_width' => ['value' => 240, 'category' => 'ui', 'type' => 'integer', 'description' => 'Sidebar width in pixels'],

            // Application Settings
            'app.name' => ['value' => 'Procurement System', 'category' => 'app', 'type' => 'string', 'description' => 'Application name', 'is_public' => true],
            'app.version' => ['value' => '1.0.0', 'category' => 'app', 'type' => 'string', 'description' => 'Application version', 'is_public' => true],
            'app.timezone' => ['value' => 'UTC', 'category' => 'app', 'type' => 'string', 'description' => 'Application timezone', 'is_public' => true],
            'app.locale' => ['value' => 'en', 'category' => 'app', 'type' => 'string', 'description' => 'Application locale', 'is_public' => true],
            'app.maintenance_mode' => ['value' => false, 'category' => 'app', 'type' => 'boolean', 'description' => 'Maintenance mode status'],

            // Error Messages
            'messages.unauthorized' => ['value' => 'Unauthorized: Access denied', 'category' => 'messages', 'type' => 'string', 'description' => 'Unauthorized error message'],
            'messages.forbidden' => ['value' => 'Forbidden: Insufficient permissions', 'category' => 'messages', 'type' => 'string', 'description' => 'Forbidden error message'],
            'messages.not_found' => ['value' => 'Resource not found', 'category' => 'messages', 'type' => 'string', 'description' => 'Not found error message'],
            'messages.validation_failed' => ['value' => 'Validation failed', 'category' => 'messages', 'type' => 'string', 'description' => 'Validation failed error message'],
            'messages.server_error' => ['value' => 'Internal server error', 'category' => 'messages', 'type' => 'string', 'description' => 'Server error message'],
            'messages.database_error' => ['value' => 'Database operation failed', 'category' => 'messages', 'type' => 'string', 'description' => 'Database error message'],

            // HTTP Status Codes
            'http_codes.success' => ['value' => 200, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Success HTTP status code'],
            'http_codes.created' => ['value' => 201, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Created HTTP status code'],
            'http_codes.bad_request' => ['value' => 400, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Bad request HTTP status code'],
            'http_codes.unauthorized' => ['value' => 401, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Unauthorized HTTP status code'],
            'http_codes.forbidden' => ['value' => 403, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Forbidden HTTP status code'],
            'http_codes.not_found' => ['value' => 404, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Not found HTTP status code'],
            'http_codes.validation_error' => ['value' => 422, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Validation error HTTP status code'],
            'http_codes.server_error' => ['value' => 500, 'category' => 'http_codes', 'type' => 'integer', 'description' => 'Server error HTTP status code'],

            // System Limits
            'limits.max_file_size' => ['value' => 10, 'category' => 'limits', 'type' => 'integer', 'description' => 'Maximum file size in MB'],
            'limits.max_memory_usage' => ['value' => 128, 'category' => 'limits', 'type' => 'integer', 'description' => 'Maximum memory usage in MB'],
            'limits.max_execution_time' => ['value' => 300, 'category' => 'limits', 'type' => 'integer', 'description' => 'Maximum execution time in seconds'],
            'limits.max_upload_files' => ['value' => 10, 'category' => 'limits', 'type' => 'integer', 'description' => 'Maximum upload files'],

            // Backup Configuration
            'backup.retention_days' => ['value' => 30, 'category' => 'backup', 'type' => 'integer', 'description' => 'Backup retention days'],
            'backup.compression_level' => ['value' => 6, 'category' => 'backup', 'type' => 'integer', 'description' => 'Backup compression level'],
            'backup.chunk_size' => ['value' => 1024, 'category' => 'backup', 'type' => 'integer', 'description' => 'Backup chunk size in KB'],

            // Logging Configuration
            'logging.max_file_size' => ['value' => 10, 'category' => 'logging', 'type' => 'integer', 'description' => 'Log max file size in MB'],
            'logging.max_files' => ['value' => 5, 'category' => 'logging', 'type' => 'integer', 'description' => 'Log max files'],
            'logging.level' => ['value' => 'info', 'category' => 'logging', 'type' => 'string', 'description' => 'Log level'],
            'logging.retention_days' => ['value' => 30, 'category' => 'logging', 'type' => 'integer', 'description' => 'Log retention days'],
        ];

        foreach ($constants as $key => $config) {
            try {
                DB::table('system_settings')->updateOrInsert(
                    ['key' => $key],
                    array_merge($config, [
                        'updated_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            } catch (\Exception $e) {
                // Continue if table doesn't exist yet
                continue;
            }
        }
    }
}




