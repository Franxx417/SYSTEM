<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasUuids;

    protected $table = 'system_settings';

    protected $fillable = [
        'category',
        'key',
        'value',
        'type',
        'description',
        'validation_rules',
        'is_encrypted',
        'is_public',
        'sort_order',
        'updated_by',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_encrypted' => 'boolean',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    // Accessors
    public function getValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value; // Return original if decryption fails
            }
        }

        return $this->castValue($value);
    }

    // Mutators
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = $this->prepareValueForStorage($value);
        }
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('key');
    }

    // Methods
    private function castValue($value)
    {
        switch ($this->type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            case 'array':
                return is_array($value) ? $value : json_decode($value, true);
            default:
                return $value;
        }
    }

    private function prepareValueForStorage($value)
    {
        if (in_array($this->type, ['json', 'array']) && (is_array($value) || is_object($value))) {
            return json_encode($value);
        }

        return $value;
    }

    // Static methods for easy access
    public static function get($key, $default = null, $category = null)
    {
        $cacheKey = $category ? "setting_{$category}_{$key}" : "setting_{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $category) {
            $query = self::where('key', $key);

            if ($category) {
                $query->where('category', $category);
            }

            $setting = $query->first();

            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value, $category = 'general', $options = [])
    {
        $setting = self::updateOrCreate(
            ['category' => $category, 'key' => $key],
            array_merge([
                'value' => $value,
                'updated_by' => auth()->user()->user_id ?? null,
            ], $options)
        );

        // Clear cache
        $cacheKey = "setting_{$category}_{$key}";
        Cache::forget($cacheKey);
        Cache::forget("setting_{$key}");

        return $setting;
    }

    public static function getByCategory($category)
    {
        $cacheKey = "settings_category_{$category}";

        return Cache::remember($cacheKey, 3600, function () use ($category) {
            return self::byCategory($category)
                ->ordered()
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public static function getAllSettings()
    {
        return Cache::remember('all_settings', 3600, function () {
            return self::all()->groupBy('category')->map(function ($settings) {
                return $settings->pluck('value', 'key')->toArray();
            })->toArray();
        });
    }

    public static function clearCache()
    {
        Cache::flush(); // Clear all cache for simplicity
    }

    // Validation
    public function validateValue($value)
    {
        if (! $this->validation_rules) {
            return true;
        }

        $rules = $this->validation_rules;
        $validator = validator(['value' => $value], ['value' => $rules]);

        return $validator->passes();
    }

    // Default settings seeder
    public static function seedDefaults()
    {
        $defaults = [
            // User Management
            'user_management' => [
                'default_role' => ['value' => 'requestor', 'type' => 'string', 'description' => 'Default role for new users'],
                'require_email_verification' => ['value' => true, 'type' => 'boolean', 'description' => 'Require email verification for new accounts'],
                'password_min_length' => ['value' => 8, 'type' => 'integer', 'description' => 'Minimum password length'],
                'password_require_special' => ['value' => true, 'type' => 'boolean', 'description' => 'Require special characters in passwords'],
                'max_login_attempts' => ['value' => 5, 'type' => 'integer', 'description' => 'Maximum failed login attempts before lockout'],
                'lockout_duration' => ['value' => 15, 'type' => 'integer', 'description' => 'Account lockout duration in minutes'],
            ],

            // Security
            'security' => [
                'session_timeout' => ['value' => 120, 'type' => 'integer', 'description' => 'Session timeout in minutes'],
                'force_https' => ['value' => false, 'type' => 'boolean', 'description' => 'Force HTTPS connections'],
                'two_factor_enabled' => ['value' => false, 'type' => 'boolean', 'description' => 'Enable two-factor authentication'],
                'ip_whitelist' => ['value' => '', 'type' => 'string', 'description' => 'Comma-separated list of allowed IP addresses'],
                'security_headers' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable security headers'],
            ],

            // Notifications
            'notifications' => [
                'email_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable email notifications'],
                'admin_email' => ['value' => 'admin@example.com', 'type' => 'string', 'description' => 'Administrator email address'],
                'notification_frequency' => ['value' => 'immediate', 'type' => 'string', 'description' => 'Notification frequency (immediate, daily, weekly)'],
                'security_alerts' => ['value' => true, 'type' => 'boolean', 'description' => 'Send security alert notifications'],
                'system_maintenance' => ['value' => true, 'type' => 'boolean', 'description' => 'Send system maintenance notifications'],
            ],

            // Performance
            'performance' => [
                'cache_enabled' => ['value' => true, 'type' => 'boolean', 'description' => 'Enable application caching'],
                'cache_duration' => ['value' => 3600, 'type' => 'integer', 'description' => 'Default cache duration in seconds'],
                'max_file_size' => ['value' => 10, 'type' => 'integer', 'description' => 'Maximum file upload size in MB'],
                'pagination_limit' => ['value' => 50, 'type' => 'integer', 'description' => 'Default pagination limit'],
                'log_level' => ['value' => 'info', 'type' => 'string', 'description' => 'Application log level'],
            ],

            // Application
            'application' => [
                'app_name' => ['value' => 'Procurement System', 'type' => 'string', 'description' => 'Application name', 'is_public' => true],
                'app_version' => ['value' => '1.0.0', 'type' => 'string', 'description' => 'Application version', 'is_public' => true],
                'maintenance_mode' => ['value' => false, 'type' => 'boolean', 'description' => 'Enable maintenance mode'],
                'timezone' => ['value' => 'UTC', 'type' => 'string', 'description' => 'Application timezone', 'is_public' => true],
                'date_format' => ['value' => 'Y-m-d', 'type' => 'string', 'description' => 'Default date format', 'is_public' => true],
            ],
        ];

        foreach ($defaults as $category => $settings) {
            foreach ($settings as $key => $config) {
                self::updateOrCreate(
                    ['category' => $category, 'key' => $key],
                    [
                        'value' => $config['value'],
                        'type' => $config['type'],
                        'description' => $config['description'],
                        'is_public' => $config['is_public'] ?? false,
                    ]
                );
            }
        }
    }
}
