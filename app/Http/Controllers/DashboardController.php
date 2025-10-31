<?php
/**
 * DashboardController
 *
 * Routes users to role-specific dashboards and returns summary metrics
 * used by client-side polling for real-time updates.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\SystemMonitoringService;
use App\Services\ConstantsService;


class DashboardController extends Controller
{
    /**
     * Cache duration for dashboard metrics (in seconds)
     * Set to 5 minutes to balance freshness and performance
     */
    private function getCacheDuration(): int
    {
        return ConstantsService::get('cache.dashboard_duration', 300);
    }

    /**
     * Get cached metrics for a user to reduce database load
     */
    private function getCachedMetrics($userId, $role, $callback)
    {
        $cacheKey = "dashboard_metrics_{$role}_{$userId}";
        return Cache::remember($cacheKey, $this->getCacheDuration(), $callback);
    }

    public function index(Request $request)
    {
        // Pull the minimal user payload we stored on login
        $auth = $request->session()->get('auth_user');
        if (!$auth) {
            return redirect()->route('login');
        }

        // Role-specific datasets and metrics
        $data = ['auth' => $auth];
        $roles = ConstantsService::getRoles();
        $statuses = ConstantsService::getStatuses();

        if ($auth['role'] === $roles['requestor']) {
            // Recent POs for requestor
            $data['recentPOs'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
                ->where('po.requestor_id', $auth['user_id'])
                ->whereNotNull('st.status_name')
                ->select('po.purchase_order_no', 'po.purpose', 'st.status_name', 'po.total', 's.name as supplier_name')
                ->orderByDesc('po.created_at')->limit(5)->get();

            // Metrics - Use consistent query pattern with caching
            $data['metrics'] = $this->getCachedMetrics($auth['user_id'], 'requestor', function() use ($auth, $statuses) {
                return [
                    'my_total' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->whereNotNull('st.status_name')->count(),
                    'my_verified' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->where('st.status_name', $statuses['verified'])
                        ->whereNotNull('st.status_name')->count(),
                    'my_approved' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('po.requestor_id', $auth['user_id'])
                        ->where('st.status_name', $statuses['approved'])
                        ->whereNotNull('st.status_name')->count(),
                ];
            });
        } 
        elseif ($auth['role'] === $roles['superadmin']) {
            // Superadmin gets system-wide overview
            $data['recentPOs'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->whereNotNull('st.status_name')
                ->select('po.*', 'st.status_name', 'st.status_id')
                ->orderByDesc('po.created_at')->limit(5)->get();
            $data['suppliers'] = DB::table('suppliers')->orderBy('name')->limit(5)->get();
            $data['users'] = DB::table('users')
                ->leftJoin('login', 'users.user_id', '=', 'login.user_id')
                ->leftJoin('roles', 'roles.user_id', '=', 'users.user_id')
                ->leftJoin('role_types', 'role_types.role_type_id', '=', 'roles.role_type_id')
                ->select('users.name', 'users.position', 'users.email', 'users.created_at', 'login.username', 'role_types.user_role_type as role')
                ->groupBy('users.user_id', 'users.name', 'users.position', 'users.email', 'users.created_at', 'login.username', 'role_types.user_role_type')
                ->orderBy('users.created_at', 'desc')
                ->limit(5)
                ->get();

            $data['metrics'] = $this->getCachedMetrics($auth['user_id'], 'superadmin', function() {
                return [
                    'total_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->whereNotNull('st.status_name')->count(),
                    'pending_pos' => DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->where('st.status_name', 'Pending')
                        ->whereNotNull('st.status_name')->count(),
                    'suppliers' => DB::table('suppliers')->count(),
                    'users' => DB::table('users')->count(),
                ];
            });

            // Provide system performance metrics used by the overview tab
            try {
                $monitor = new SystemMonitoringService();
                $data['systemMetrics'] = $monitor->getSystemMetrics();
            } catch (\Throwable $e) {
                $data['systemMetrics'] = [];
            }
        }

        // Choose the correct Blade view for this role
        return match ($auth['role']) {
            'requestor' => view('dashboards.requestor', $data),
            'superadmin' => view('dashboards.superadmin', $data),
            default => view('dashboard', ['auth' => $auth]),
        };
    }

    /**
     * Lightweight JSON for live dashboard updates (counts and recent rows)
     */
    public function summary(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth) return response()->json(['error' => 'unauthenticated'], 401);

        $payload = ['role' => $auth['role']];

        if ($auth['role'] === 'requestor') {
            $payload['metrics'] = [
                'my_total' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', function($join) {
                        $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                             ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                    })
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->whereNotNull('st.status_name')->count(),
                'my_drafts' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', function($join) {
                        $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                             ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                    })
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->where('st.status_name', 'Pending')
                    ->whereNotNull('st.status_name')->count(),
                'my_verified' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', function($join) {
                        $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                             ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                    })
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->where('st.status_name', 'Verified')
                    ->whereNotNull('st.status_name')->count(),
                'my_approved' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', function($join) {
                        $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                             ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                    })
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->where('st.status_name', 'Approved')
                    ->whereNotNull('st.status_name')->count(),
            ];
            $payload['drafts'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', function($join) {
                    $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                         ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                })
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->where('po.requestor_id', $auth['user_id'])
                ->where('st.status_name', 'Pending')
                ->whereNotNull('st.status_name')
                ->orderByDesc('po.created_at')
                ->limit(5)
                ->select('po.purchase_order_no','po.purpose','po.total','st.status_name')
                ->get();
        }

        if ($auth['role'] === 'superadmin') {
            $payload['metrics'] = [
                'total_pos' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', function($join) {
                        $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                             ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                    })
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->whereNotNull('st.status_name')->count(),
                'pending_pos' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', function($join) {
                        $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                             ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                    })
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('st.status_name', 'Pending')
                    ->whereNotNull('st.status_name')->count(),
                'suppliers' => DB::table('suppliers')->count(),
                'users' => DB::table('users')->count(),
            ];
        }

        return response()->json($payload);
    }
}


