<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\SystemActivityLog;
use App\Models\SecuritySession;
use App\Models\SecurityAlert;
use Carbon\Carbon;

class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get security dashboard data
     */
    public function dashboard()
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = Cache::remember('security_dashboard', 300, function () {
            return [
                'active_sessions' => $this->getActiveSessions(),
                'recent_activities' => $this->getRecentActivities(),
                'security_alerts' => $this->getSecurityAlerts(),
                'statistics' => $this->getSecurityStatistics(),
            ];
        });

        return response()->json($data);
    }

    /**
     * Get active sessions
     */
    public function getActiveSessions()
    {
        return SecuritySession::active()
            ->with('user')
            ->orderBy('last_activity', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'username' => $session->username,
                    'ip_address' => $session->ip_address,
                    'last_activity' => $session->last_activity->diffForHumans(),
                    'login_at' => $session->login_at->format('Y-m-d H:i:s'),
                    'device_info' => $this->parseUserAgent($session->user_agent),
                    'location' => $session->location_data,
                ];
            });
    }

    /**
     * Get recent system activities
     */
    public function getRecentActivities($limit = 100)
    {
        return SystemActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'username' => $log->username,
                    'action' => $log->action,
                    'resource_type' => $log->resource_type,
                    'description' => $log->description,
                    'ip_address' => $log->ip_address,
                    'severity' => $log->severity,
                    'is_security_event' => $log->is_security_event,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get security alerts
     */
    public function getSecurityAlerts($onlyUnresolved = true)
    {
        $query = SecurityAlert::with(['user', 'resolvedBy']);
        
        if ($onlyUnresolved) {
            $query->unresolved();
        }
        
        return $query->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'alert_type' => $alert->alert_type,
                    'severity' => $alert->severity,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'username' => $alert->user->name ?? 'System',
                    'ip_address' => $alert->ip_address,
                    'is_resolved' => $alert->is_resolved,
                    'resolved_by' => $alert->resolvedBy->name ?? null,
                    'created_at' => $alert->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $alert->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get security statistics
     */
    public function getSecurityStatistics()
    {
        $now = now();
        $last24h = $now->copy()->subHours(24);
        $last7d = $now->copy()->subDays(7);

        return [
            'active_sessions_count' => SecuritySession::active()->count(),
            'total_users_online' => SecuritySession::active()->distinct('user_id')->count(),
            'activities_last_24h' => SystemActivityLog::where('created_at', '>=', $last24h)->count(),
            'security_events_last_24h' => SystemActivityLog::securityEvents()
                ->where('created_at', '>=', $last24h)->count(),
            'unresolved_alerts' => SecurityAlert::unresolved()->count(),
            'critical_alerts' => SecurityAlert::unresolved()->critical()->count(),
            'failed_logins_last_24h' => SystemActivityLog::where('action', 'failed_login')
                ->where('created_at', '>=', $last24h)->count(),
            'login_success_rate' => $this->calculateLoginSuccessRate($last7d),
            'top_active_users' => $this->getTopActiveUsers($last24h),
            'activity_by_hour' => $this->getActivityByHour($last24h),
        ];
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $sessionId = $request->input('session_id');
        $session = SecuritySession::find($sessionId);

        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }

        $session->terminate();

        SystemActivityLog::logSecurityEvent(
            'session_terminated_by_admin',
            "Admin terminated session for user {$session->username}",
            'medium',
            ['terminated_session_id' => $sessionId, 'admin_user' => $auth['name']]
        );

        return response()->json(['success' => true, 'message' => 'Session terminated successfully']);
    }

    /**
     * Force logout all users
     */
    public function forceLogoutAll(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $activeSessions = SecuritySession::active()->get();
        $count = 0;

        foreach ($activeSessions as $session) {
            // Don't terminate current admin session
            if ($session->id !== session()->getId()) {
                $session->terminate();
                $count++;
            }
        }

        SystemActivityLog::logSecurityEvent(
            'force_logout_all',
            "Admin forced logout of all users ({$count} sessions terminated)",
            'high',
            ['terminated_sessions_count' => $count, 'admin_user' => $auth['name']]
        );

        return response()->json([
            'success' => true, 
            'message' => "Successfully terminated {$count} active sessions"
        ]);
    }

    /**
     * Resolve security alert
     */
    public function resolveAlert(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $alertId = $request->input('alert_id');
        $notes = $request->input('notes');

        $alert = SecurityAlert::find($alertId);

        if (!$alert) {
            return response()->json(['error' => 'Alert not found'], 404);
        }

        $alert->resolve($auth['user_id'], $notes);

        return response()->json(['success' => true, 'message' => 'Alert resolved successfully']);
    }

    /**
     * Update security settings
     */
    public function updateSecuritySettings(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'session_timeout' => 'required|integer|min:5|max:1440',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'force_https' => 'boolean',
        ]);

        try {
            // Update settings in database or config
            DB::table('settings')->updateOrInsert(
                ['key' => 'security.session_timeout'],
                ['value' => $request->session_timeout]
            );
            
            DB::table('settings')->updateOrInsert(
                ['key' => 'security.max_login_attempts'],
                ['value' => $request->max_login_attempts]
            );
            
            DB::table('settings')->updateOrInsert(
                ['key' => 'security.force_https'],
                ['value' => $request->boolean('force_https') ? '1' : '0']
            );

            SystemActivityLog::logSecurityEvent(
                'security_settings_updated',
                'Security settings were updated by admin',
                'medium',
                [
                    'session_timeout' => $request->session_timeout,
                    'max_login_attempts' => $request->max_login_attempts,
                    'force_https' => $request->boolean('force_https'),
                    'admin_user' => $auth['name']
                ]
            );

            // Clear cache
            Cache::forget('security_dashboard');

            return response()->json(['success' => true, 'message' => 'Security settings updated successfully']);

        } catch (\Exception $e) {
            Log::error('Failed to update security settings: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update settings'], 500);
        }
    }

    /**
     * Get system logs with filtering
     */
    public function getSystemLogs(Request $request)
    {
        $auth = session('auth_user');
        
        if (!$auth || $auth['role'] !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = SystemActivityLog::with('user');

        // Apply filters
        if ($request->has('level') && $request->level) {
            $query->where('severity', $request->level);
        }

        if ($request->has('action') && $request->action) {
            $query->where('action', 'like', '%' . $request->action . '%');
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('security_only') && $request->security_only) {
            $query->securityEvents();
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'statistics' => [
                'total_logs' => SystemActivityLog::count(),
                'security_events' => SystemActivityLog::securityEvents()->count(),
                'critical_events' => SystemActivityLog::bySeverity('critical')->count(),
            ]
        ]);
    }

    // Helper methods
    private function calculateLoginSuccessRate($since)
    {
        $totalLogins = SystemActivityLog::whereIn('action', ['login_success', 'failed_login'])
            ->where('created_at', '>=', $since)
            ->count();

        if ($totalLogins === 0) return 100;

        $successfulLogins = SystemActivityLog::where('action', 'login_success')
            ->where('created_at', '>=', $since)
            ->count();

        return round(($successfulLogins / $totalLogins) * 100, 2);
    }

    private function getTopActiveUsers($since, $limit = 5)
    {
        return SystemActivityLog::select('username', DB::raw('COUNT(*) as activity_count'))
            ->where('created_at', '>=', $since)
            ->whereNotNull('username')
            ->groupBy('username')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getActivityByHour($since)
    {
        $hours = [];
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $hours[] = [
                'hour' => $hour->format('H:00'),
                'count' => SystemActivityLog::whereBetween('created_at', [
                    $hour->startOfHour(),
                    $hour->endOfHour()
                ])->count()
            ];
        }
        return $hours;
    }

    private function parseUserAgent($userAgent)
    {
        // Simple user agent parsing - in production, consider using a library
        $info = ['browser' => 'Unknown', 'os' => 'Unknown'];
        
        if (strpos($userAgent, 'Chrome') !== false) {
            $info['browser'] = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $info['browser'] = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $info['browser'] = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $info['browser'] = 'Edge';
        }

        if (strpos($userAgent, 'Windows') !== false) {
            $info['os'] = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $info['os'] = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $info['os'] = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $info['os'] = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $info['os'] = 'iOS';
        }

        return $info;
    }
}
