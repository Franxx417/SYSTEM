<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SystemActivityLog extends Model
{
    use HasUuids;

    protected $table = 'system_activity_logs';
    
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'username',
        'action',
        'resource_type',
        'resource_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
        'session_id',
        'severity',
        'is_security_event',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_security_event' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Scopes
    public function scopeSecurityEvents($query)
    {
        return $query->where('is_security_event', true);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // Static methods for logging
    public static function logActivity($data)
    {
        return self::create(array_merge([
            'user_id' => auth()->user()->user_id ?? null,
            'username' => auth()->user()->name ?? 'System',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
        ], $data));
    }

    public static function logSecurityEvent($action, $description, $severity = 'medium', $metadata = [])
    {
        return self::logActivity([
            'action' => $action,
            'description' => $description,
            'severity' => $severity,
            'is_security_event' => true,
            'metadata' => $metadata,
        ]);
    }

    public static function logUserAction($action, $resourceType = null, $resourceId = null, $description = null)
    {
        return self::logActivity([
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'description' => $description ?? "User performed {$action}" . ($resourceType ? " on {$resourceType}" : ''),
        ]);
    }
}
