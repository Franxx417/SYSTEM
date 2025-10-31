<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * API Authentication Middleware
 * 
 * Handles authentication for API requests
 * Supports both session-based and token-based auth
 */
class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role = null)
    {
        // Get auth from session
        $auth = $request->session()->get('auth_user');
        
        // Check if user is authenticated
        if (!$auth || !isset($auth['user_id'])) {
            Log::warning('API: Unauthenticated access attempt', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHENTICATED',
                    'message' => 'Authentication required',
                    'status' => 401
                ]
            ], 401);
        }
        
        // Check role if specified
        if ($role && $auth['role'] !== $role && $auth['role'] !== 'superadmin') {
            Log::warning('API: Unauthorized access attempt', [
                'user_id' => $auth['user_id'],
                'role' => $auth['role'],
                'required_role' => $role,
                'path' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Insufficient permissions',
                    'status' => 403
                ]
            ], 403);
        }
        
        // Attach auth data to request for easy access
        $request->attributes->set('auth_user', $auth);
        
        return $next($request);
    }
}
