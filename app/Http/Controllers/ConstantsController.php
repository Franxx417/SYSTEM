<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ConstantsService;

class ConstantsController extends Controller
{
    /**
     * Get public constants for frontend use
     */
    public function getPublicConstants(Request $request)
    {
        $publicConstants = [
            'notifications' => [
                'auto_dismiss_delay' => ConstantsService::get('notifications.auto_dismiss_delay', 5000),
                'max_notifications' => ConstantsService::get('notifications.max_notifications', 5),
            ],
            'monitoring' => [
                'auto_refresh_interval' => ConstantsService::get('monitoring.auto_refresh_interval', 30000),
            ],
            'ui' => [
                'table_responsive_breakpoint' => ConstantsService::get('ui.table_responsive_breakpoint', 991.98),
                'modal_max_width' => ConstantsService::get('ui.modal_max_width', 95),
                'sidebar_width' => ConstantsService::get('ui.sidebar_width', 240),
            ],
            'app' => [
                'name' => ConstantsService::get('app.name', 'Procurement System'),
                'version' => ConstantsService::get('app.version', '1.0.0'),
            ],
            'pagination' => [
                'default_limit' => ConstantsService::get('pagination.default_limit', 50),
                'dashboard_recent_limit' => ConstantsService::get('pagination.dashboard_recent_limit', 5),
                'dashboard_suppliers_limit' => ConstantsService::get('pagination.dashboard_suppliers_limit', 5),
                'dashboard_users_limit' => ConstantsService::get('pagination.dashboard_users_limit', 10),
                'dashboard_statuses_limit' => ConstantsService::get('pagination.dashboard_statuses_limit', 20),
                'logs_limit' => ConstantsService::get('pagination.logs_limit', 100),
            ],
            'statuses' => ConstantsService::getStatuses(),
            'roles' => ConstantsService::getRoles(),
        ];

        return response()->json($publicConstants);
    }

    /**
     * Get all constants for superadmin
     */
    public function getAllConstants(Request $request)
    {
        // Check if user is superadmin
        $auth = $request->session()->get('auth_user');
        if (!$auth || $auth['role'] !== ConstantsService::get('roles.superadmin', 'superadmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $allConstants = [
            'cache' => ConstantsService::getCategory('cache'),
            'pagination' => ConstantsService::getCategory('pagination'),
            'security' => ConstantsService::getCategory('security'),
            'roles' => ConstantsService::getCategory('roles'),
            'statuses' => ConstantsService::getCategory('statuses'),
            'database' => ConstantsService::getCategory('database'),
            'uploads' => ConstantsService::getCategory('uploads'),
            'monitoring' => ConstantsService::getCategory('monitoring'),
            'api' => ConstantsService::getCategory('api'),
            'notifications' => ConstantsService::getCategory('notifications'),
            'ui' => ConstantsService::getCategory('ui'),
            'app' => ConstantsService::getCategory('app'),
            'messages' => ConstantsService::getCategory('messages'),
            'http_codes' => ConstantsService::getCategory('http_codes'),
            'limits' => ConstantsService::getCategory('limits'),
            'backup' => ConstantsService::getCategory('backup'),
            'logging' => ConstantsService::getCategory('logging'),
        ];

        return response()->json($allConstants);
    }

    /**
     * Update a constant value
     */
    public function updateConstant(Request $request)
    {
        // Check if user is superadmin
        $auth = $request->session()->get('auth_user');
        if (!$auth || $auth['role'] !== ConstantsService::get('roles.superadmin', 'superadmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
            'category' => 'required|string',
        ]);

        $success = ConstantsService::set(
            $request->key,
            $request->value,
            $request->category
        );

        if ($success) {
            return response()->json(['message' => 'Constant updated successfully']);
        }

        return response()->json(['error' => 'Failed to update constant'], 500);
    }
}




