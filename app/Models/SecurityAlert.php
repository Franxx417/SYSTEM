<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SecurityAlert extends Model
{
    use HasUuids;

    protected $table = 'security_alerts';
    
    public $timestamps = false;
    
    protected $fillable = [
        'alert_type',
        'severity',
        'title',
        'description',
        'user_id',
        'ip_address',
        'metadata',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by', 'user_id');
    }

    // Scopes
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // Methods
    public function resolve($resolvedBy, $notes = null)
    {
        $this->update([
            'is_resolved' => true,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);

        // Log the resolution
        SystemActivityLog::logSecurityEvent(
            'alert_resolved',
            "Security alert '{$this->title}' was resolved",
            'low',
            ['alert_id' => $this->id, 'alert_type' => $this->alert_type]
        );
    }

    // Static methods for creating alerts
    public static function createAlert($type, $title, $description, $severity = 'medium', $metadata = [])
    {
        $alert = self::create([
            'alert_type' => $type,
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'user_id' => auth()->user()->user_id ?? null,
            'ip_address' => request()->ip(),
            'metadata' => $metadata,
        ]);

        // Also log as security event
        SystemActivityLog::logSecurityEvent(
            'security_alert_created',
            "Security alert created: {$title}",
            $severity,
            array_merge($metadata, ['alert_id' => $alert->id])
        );

        return $alert;
    }

    public static function failedLoginAlert($username, $ip, $attempts)
    {
        return self::createAlert(
            'failed_login',
            'Multiple Failed Login Attempts',
            "User '{$username}' has {$attempts} failed login attempts from IP {$ip}",
            $attempts >= 5 ? 'high' : 'medium',
            ['username' => $username, 'ip_address' => $ip, 'attempts' => $attempts]
        );
    }

    public static function suspiciousActivityAlert($description, $metadata = [])
    {
        return self::createAlert(
            'suspicious_activity',
            'Suspicious Activity Detected',
            $description,
            'high',
            $metadata
        );
    }

    public static function privilegeEscalationAlert($userId, $action)
    {
        return self::createAlert(
            'privilege_escalation',
            'Potential Privilege Escalation',
            "User attempted unauthorized action: {$action}",
            'critical',
            ['user_id' => $userId, 'action' => $action]
        );
    }
}
