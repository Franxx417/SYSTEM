<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecuritySession extends Model
{
    protected $table = 'security_sessions';
    
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'user_id',
        'username',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
        'login_at',
        'is_active',
        'device_fingerprint',
        'location_data',
        'expires_at',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'login_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'location_data' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    // Methods
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function terminate()
    {
        $this->update(['is_active' => false]);
        
        // Log the session termination
        SystemActivityLog::logSecurityEvent(
            'session_terminated',
            "Session terminated for user {$this->username}",
            'low',
            ['session_id' => $this->id, 'ip_address' => $this->ip_address]
        );
    }

    public function updateActivity()
    {
        $this->update(['last_activity' => now()]);
    }

    // Static methods
    public static function createSession($sessionId, $userId, $username, $expiresIn = 7200)
    {
        return self::create([
            'id' => $sessionId,
            'user_id' => $userId,
            'username' => $username,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'device_fingerprint' => self::generateDeviceFingerprint(),
            'expires_at' => now()->addSeconds($expiresIn),
        ]);
    }

    public static function terminateAllForUser($userId)
    {
        $sessions = self::where('user_id', $userId)->where('is_active', true)->get();
        
        foreach ($sessions as $session) {
            $session->terminate();
        }
        
        return $sessions->count();
    }

    public static function cleanupExpiredSessions()
    {
        $expired = self::expired()->get();
        
        foreach ($expired as $session) {
            $session->update(['is_active' => false]);
        }
        
        return $expired->count();
    }

    private static function generateDeviceFingerprint()
    {
        $components = [
            request()->userAgent(),
            request()->header('Accept-Language'),
            request()->header('Accept-Encoding'),
        ];
        
        return hash('sha256', implode('|', array_filter($components)));
    }
}
