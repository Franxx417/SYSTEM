<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait SuperAdminAccess
{
    /**
     * Check authentication with SUPERADMIN UNRESTRICTED ACCESS
     * Superadmin can access everything, other roles are checked normally
     */
    protected function checkAuth(Request $request, $allowedRoles = []): array
    {
        $auth = $request->session()->get('auth_user');
        if (! $auth) {
            abort(403, 'Authentication required');
        }

        // SUPERADMIN HAS UNRESTRICTED ACCESS TO EVERYTHING
        if ($auth['role'] === 'superadmin') {
            return $auth;
        }

        // For non-superadmin users, check allowed roles
        if (! empty($allowedRoles)) {
            $allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
            if (! in_array($auth['role'], $allowedRoles)) {
                abort(403, 'Insufficient permissions');
            }
        }

        return $auth;
    }

    /**
     * Check if current user is superadmin
     */
    protected function isSuperAdmin(Request $request): bool
    {
        $auth = $request->session()->get('auth_user');

        return $auth && $auth['role'] === 'superadmin';
    }

    /**
     * Get current authenticated user
     */
    protected function getAuthUser(Request $request): ?array
    {
        return $request->session()->get('auth_user');
    }
}
