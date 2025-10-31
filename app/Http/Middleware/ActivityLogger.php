<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemActivityLog;
use App\Models\SecuritySession;
use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Cache;

class ActivityLogger
{
    /**
     * Handle an incoming request and log activity
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users and specific routes
        if ($this->shouldLog($request)) {
            $this->logActivity($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be logged
     */
    private function shouldLog(Request $request): bool
    {
        // Skip logging for certain routes
        $skipRoutes = [
            'dashboard.summary',
            'security.dashboard',
            '_debugbar',
            'telescope',
        ];

        $routeName = $request->route()?->getName();
        
        if (in_array($routeName, $skipRoutes)) {
            return false;
        }

        // Skip static assets
        if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*')) {
            return false;
        }

        return true;
    }

    /**
     * Log the activity
     */
    private function logActivity(Request $request, $response): void
    {
        try {
            $auth = session('auth_user');
            $method = $request->method();
            $route = $request->route();
            $routeName = $route?->getName() ?? 'unknown';
            $uri = $request->getRequestUri();

            // Determine action based on HTTP method and route
            $action = $this->determineAction($method, $routeName, $uri);
            
            // Determine resource type and ID
            [$resourceType, $resourceId] = $this->extractResourceInfo($route, $request);

            // Create activity log
            $logData = [
                'action' => $action,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'description' => $this->generateDescription($action, $resourceType, $method, $uri),
                'metadata' => [
                    'method' => $method,
                    'route' => $routeName,
                    'uri' => $uri,
                    'status_code' => $response->getStatusCode(),
                    'request_size' => strlen($request->getContent()),
                    'response_size' => strlen($response->getContent()),
                ],
            ];

            // Determine severity
            $logData['severity'] = $this->determineSeverity($method, $response->getStatusCode(), $action);

            // Check if this is a security event
            $logData['is_security_event'] = $this->isSecurityEvent($action, $method, $response->getStatusCode());

            SystemActivityLog::logActivity($logData);

            // Update session activity if user is authenticated
            if ($auth && session()->getId()) {
                $this->updateSessionActivity($request);
            }

            // Check for suspicious activity
            $this->checkSuspiciousActivity($request, $auth, $action);

        } catch (\Exception $e) {
            // Don't let logging errors break the application
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Determine the action based on request details
     */
    private function determineAction(string $method, string $routeName, string $uri): string
    {
        // Map common route patterns to actions
        $actionMap = [
            'login' => 'login_attempt',
            'logout' => 'logout',
            'dashboard' => 'view_dashboard',
            'suppliers.store' => 'create_supplier',
            'suppliers.update' => 'update_supplier',
            'suppliers.destroy' => 'delete_supplier',
            'po.store' => 'create_purchase_order',
            'po.update' => 'update_purchase_order',
            'status.store' => 'create_status',
            'status.update' => 'update_status',
            'status.destroy' => 'delete_status',
        ];

        if (isset($actionMap[$routeName])) {
            return $actionMap[$routeName];
        }

        // Fallback to method-based actions
        switch ($method) {
            case 'GET':
                return str_contains($uri, 'dashboard') ? 'view_dashboard' : 'view_page';
            case 'POST':
                return 'create_resource';
            case 'PUT':
            case 'PATCH':
                return 'update_resource';
            case 'DELETE':
                return 'delete_resource';
            default:
                return 'unknown_action';
        }
    }

    /**
     * Extract resource information from route
     */
    private function extractResourceInfo($route, Request $request): array
    {
        if (!$route) {
            return [null, null];
        }

        $parameters = $route->parameters();
        $routeName = $route->getName() ?? '';

        // Extract resource type from route name
        $resourceType = null;
        if (str_contains($routeName, 'suppliers')) {
            $resourceType = 'supplier';
        } elseif (str_contains($routeName, 'po') || str_contains($routeName, 'purchase')) {
            $resourceType = 'purchase_order';
        } elseif (str_contains($routeName, 'status')) {
            $resourceType = 'status';
        } elseif (str_contains($routeName, 'users')) {
            $resourceType = 'user';
        } elseif (str_contains($routeName, 'items')) {
            $resourceType = 'item';
        }

        // Extract resource ID from route parameters
        $resourceId = null;
        foreach (['id', 'supplier', 'po', 'status', 'user', 'item'] as $param) {
            if (isset($parameters[$param])) {
                $resourceId = $parameters[$param];
                break;
            }
        }

        return [$resourceType, $resourceId];
    }

    /**
     * Generate human-readable description
     */
    private function generateDescription(string $action, ?string $resourceType, string $method, string $uri): string
    {
        $descriptions = [
            'login_attempt' => 'User attempted to log in',
            'logout' => 'User logged out',
            'view_dashboard' => 'User viewed dashboard',
            'create_supplier' => 'User created a new supplier',
            'update_supplier' => 'User updated supplier information',
            'delete_supplier' => 'User deleted a supplier',
            'create_purchase_order' => 'User created a new purchase order',
            'update_purchase_order' => 'User updated purchase order',
            'create_status' => 'User created a new status',
            'update_status' => 'User updated status information',
            'delete_status' => 'User deleted a status',
        ];

        if (isset($descriptions[$action])) {
            return $descriptions[$action];
        }

        // Generate generic description
        $actionWord = str_replace('_', ' ', $action);
        if ($resourceType) {
            return "User performed {$actionWord} on {$resourceType}";
        }

        return "User performed {$method} request to {$uri}";
    }

    /**
     * Determine log severity
     */
    private function determineSeverity(string $method, int $statusCode, string $action): string
    {
        // Critical actions
        if (in_array($action, ['delete_supplier', 'delete_status', 'force_logout_all'])) {
            return 'high';
        }

        // Security-related actions
        if (in_array($action, ['login_attempt', 'logout', 'session_terminated'])) {
            return 'medium';
        }

        // Error responses
        if ($statusCode >= 500) {
            return 'high';
        } elseif ($statusCode >= 400) {
            return 'medium';
        }

        // Modification actions
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Check if this is a security event
     */
    private function isSecurityEvent(string $action, string $method, int $statusCode): bool
    {
        $securityActions = [
            'login_attempt',
            'logout',
            'session_terminated',
            'force_logout_all',
            'delete_supplier',
            'delete_status',
        ];

        if (in_array($action, $securityActions)) {
            return true;
        }

        // Failed authentication/authorization
        if (in_array($statusCode, [401, 403])) {
            return true;
        }

        return false;
    }

    /**
     * Update session activity
     */
    private function updateSessionActivity(Request $request): void
    {
        $sessionId = session()->getId();
        $session = SecuritySession::find($sessionId);

        if ($session) {
            $session->updateActivity();
        }
    }

    /**
     * Check for suspicious activity patterns
     */
    private function checkSuspiciousActivity(Request $request, ?array $auth, string $action): void
    {
        $ip = $request->ip();
        $now = now();

        // Check for rapid requests from same IP
        $cacheKey = "requests_per_ip_{$ip}";
        $requestCount = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $requestCount + 1, 60); // 1 minute window

        if ($requestCount > 100) { // More than 100 requests per minute
            SecurityAlert::suspiciousActivityAlert(
                "Excessive requests from IP {$ip} ({$requestCount} requests in 1 minute)",
                ['ip_address' => $ip, 'request_count' => $requestCount, 'action' => $action]
            );
        }

        // Check for failed login attempts
        if ($action === 'login_attempt' && !$auth) {
            $failedAttempts = SystemActivityLog::where('action', 'failed_login')
                ->where('ip_address', $ip)
                ->where('created_at', '>=', $now->subMinutes(15))
                ->count();

            if ($failedAttempts >= 5) {
                SecurityAlert::failedLoginAlert('unknown', $ip, $failedAttempts);
            }
        }

        // Check for privilege escalation attempts
        if ($auth && in_array($action, ['delete_supplier', 'delete_status', 'force_logout_all'])) {
            if ($auth['role'] !== 'superadmin') {
                SecurityAlert::privilegeEscalationAlert($auth['user_id'], $action);
            }
        }
    }
}
