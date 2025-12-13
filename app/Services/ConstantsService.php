<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConstantsService
{
    /**
     * Get a constant value with fallback hierarchy:
     * 1. Database settings (highest priority)
     * 2. Environment variables
     * 3. Config constants (lowest priority)
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "constant_{$key}";

        return Cache::remember($cacheKey, config('constants.cache.settings_duration', 3600), function () use ($key, $default) {
            // Try database settings first
            if (Schema::hasTable('system_settings')) {
                try {
                    $dbValue = DB::table('system_settings')
                        ->where('key', $key)
                        ->value('value');

                    if ($dbValue !== null) {
                        return self::castValue($dbValue, $key);
                    }
                } catch (\Exception $e) {
                    // Ignore database errors, fall back to config
                }
            }

            // Try environment variable
            $envKey = strtoupper(str_replace('.', '_', $key));
            $envValue = env($envKey);
            if ($envValue !== null) {
                return self::castValue($envValue, $key);
            }

            // Try config constants
            $configValue = config("constants.{$key}");
            if ($configValue !== null) {
                return $configValue;
            }

            return $default;
        });
    }

    /**
     * Set a constant value in database
     */
    public static function set(string $key, $value, string $category = 'general'): bool
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return false;
            }

            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'category' => $category,
                    'value' => $value,
                    'type' => self::getValueType($value),
                    'updated_by' => auth()->user()->user_id ?? null,
                    'updated_at' => now(),
                ]
            );

            // Clear cache
            Cache::forget("constant_{$key}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get multiple constants at once
     */
    public static function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::get($key);
        }

        return $result;
    }

    /**
     * Get all constants for a category
     */
    public static function getCategory(string $category): array
    {
        $cacheKey = "constants_category_{$category}";

        return Cache::remember($cacheKey, config('constants.cache.settings_duration', 3600), function () use ($category) {
            if (! Schema::hasTable('system_settings')) {
                return [];
            }

            try {
                return DB::table('system_settings')
                    ->where('category', $category)
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Exception $e) {
                return [];
            }
        });
    }

    /**
     * Clear all constant caches
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Get role constants
     */
    public static function getRoles(): array
    {
        return [
            'requestor' => self::get('roles.requestor', 'requestor'),
            'superadmin' => self::get('roles.superadmin', 'superadmin'),
        ];
    }

    /**
     * Get status constants
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => self::get('statuses.pending', 'Pending'),
            'verified' => self::get('statuses.verified', 'Verified'),
            'approved' => self::get('statuses.approved', 'Approved'),
            'received' => self::get('statuses.received', 'Received'),
            'rejected' => self::get('statuses.rejected', 'Rejected'),
            'draft' => self::get('statuses.draft', 'Draft'),
            'default' => self::get('statuses.default', 'Pending'),
        ];
    }

    /**
     * Get pagination limits
     */
    public static function getPaginationLimits(): array
    {
        return [
            'default' => self::get('pagination.default_limit', 50),
            'dashboard_recent' => self::get('pagination.dashboard_recent_limit', 5),
            'dashboard_suppliers' => self::get('pagination.dashboard_suppliers_limit', 5),
            'dashboard_users' => self::get('pagination.dashboard_users_limit', 10),
            'dashboard_statuses' => self::get('pagination.dashboard_statuses_limit', 20),
            'logs' => self::get('pagination.logs_limit', 100),
            'table_chunk' => self::get('pagination.table_chunk_size', 500),
        ];
    }

    /**
     * Get security settings
     */
    public static function getSecuritySettings(): array
    {
        return [
            'session_timeout' => self::get('security.session_timeout', 120),
            'max_login_attempts' => self::get('security.max_login_attempts', 5),
            'password_min_length' => self::get('security.password_min_length', 8),
            'lockout_duration' => self::get('security.lockout_duration', 15),
            'failed_login_threshold' => self::get('security.failed_login_threshold', 10),
        ];
    }

    /**
     * Get cache durations
     */
    public static function getCacheDurations(): array
    {
        return [
            'dashboard' => self::get('cache.dashboard_duration', 300),
            'metrics' => self::get('cache.metrics_duration', 3600),
            'settings' => self::get('cache.settings_duration', 3600),
        ];
    }

    /**
     * Get HTTP status codes
     */
    public static function getHttpCodes(): array
    {
        return [
            'success' => self::get('http_codes.success', 200),
            'created' => self::get('http_codes.created', 201),
            'bad_request' => self::get('http_codes.bad_request', 400),
            'unauthorized' => self::get('http_codes.unauthorized', 401),
            'forbidden' => self::get('http_codes.forbidden', 403),
            'not_found' => self::get('http_codes.not_found', 404),
            'validation_error' => self::get('http_codes.validation_error', 422),
            'server_error' => self::get('http_codes.server_error', 500),
        ];
    }

    /**
     * Get error messages
     */
    public static function getMessages(): array
    {
        return [
            'unauthorized' => self::get('messages.unauthorized', 'Unauthorized: Access denied'),
            'forbidden' => self::get('messages.forbidden', 'Forbidden: Insufficient permissions'),
            'not_found' => self::get('messages.not_found', 'Resource not found'),
            'validation_failed' => self::get('messages.validation_failed', 'Validation failed'),
            'server_error' => self::get('messages.server_error', 'Internal server error'),
            'database_error' => self::get('messages.database_error', 'Database operation failed'),
        ];
    }

    /**
     * Cast value to appropriate type based on key
     */
    private static function castValue($value, string $key)
    {
        // Boolean values
        if (in_array($key, ['security.force_password_change', 'security.enable_2fa', 'app.maintenance_mode'])) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        // Integer values
        if (strpos($key, 'limit') !== false || strpos($key, 'timeout') !== false ||
            strpos($key, 'duration') !== false || strpos($key, 'size') !== false ||
            strpos($key, 'attempts') !== false || strpos($key, 'threshold') !== false) {
            return (int) $value;
        }

        // Float values
        if (strpos($key, 'percentage') !== false || strpos($key, 'ratio') !== false) {
            return (float) $value;
        }

        return $value;
    }

    /**
     * Get value type for database storage
     */
    private static function getValueType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_int($value)) {
            return 'integer';
        } elseif (is_float($value)) {
            return 'float';
        } elseif (is_array($value) || is_object($value)) {
            return 'json';
        }

        return 'string';
    }
}
