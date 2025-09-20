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


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Pull the minimal user payload we stored on login
        $auth = $request->session()->get('auth_user');
        if (!$auth) {
            return redirect()->route('login');
        }

        // Role-specific datasets and metrics
        $data = ['auth' => $auth];

        if ($auth['role'] === 'requestor') {
            $data['myDrafts'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->where('po.requestor_id', $auth['user_id'])
                ->where('st.status_name', 'Pending')
                ->whereNotNull('st.status_name') // Only show POs with status
                ->select('po.purchase_order_no', 'po.purpose', 'st.status_name', 'po.total')
                ->limit(5)->get();
            $data['recentPOs'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->whereNotNull('st.status_name') // Only show POs with status
                ->orderByDesc('po.created_at')->limit(5)->get();

            // Metrics
            $data['metrics'] = [
                'my_total' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->whereNotNull('st.status_name')->count(),
                'my_drafts' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->where('st.status_name', 'Pending')
                    ->whereNotNull('st.status_name')->count(),
                'my_verified' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->where('st.status_name', 'Verified')
                    ->whereNotNull('st.status_name')->count(),
                'my_approved' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->where('st.status_name', 'Approved')
                    ->whereNotNull('st.status_name')->count(),
            ];
        } 
        elseif ($auth['role'] === 'authorized_personnel') {
            $data['recentApproved'] = DB::table('approvals as ap')
                ->join('purchase_orders as po', 'po.purchase_order_id', '=', 'ap.purchase_order_id')
                ->join('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->where('st.status_name', 'Approved')
                ->select('po.purchase_order_id','po.purchase_order_no', 'po.purpose', 'po.total')
                ->limit(5)->get();
            $data['suppliers'] = DB::table('suppliers')->orderBy('name')->limit(5)->get();

            $data['metrics'] = [
                'approved' => DB::table('approvals as ap')->join('statuses as st', 'st.status_id', '=', 'ap.status_id')->where('st.status_name','Approved')->count(),
                'received' => DB::table('approvals')->whereNotNull('received_at')->count(),
                'suppliers' => DB::table('suppliers')->count(),
                'users' => DB::table('users')->count(),
            ];
        }
        elseif ($auth['role'] === 'superadmin') {
            // Superadmin gets system-wide overview
            $data['recentPOs'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->whereNotNull('st.status_name')
                ->orderByDesc('po.created_at')->limit(5)->get();
            $data['suppliers'] = DB::table('suppliers')->orderBy('name')->limit(5)->get();
            $data['users'] = DB::table('users')->join('login', 'users.user_id', '=', 'login.user_id')->select('users.name', 'login.username', 'users.position')->limit(5)->get();

            $data['metrics'] = [
                'total_pos' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->whereNotNull('st.status_name')->count(),
                'pending_pos' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('st.status_name', 'Pending')
                    ->whereNotNull('st.status_name')->count(),
                'suppliers' => DB::table('suppliers')->count(),
                'users' => DB::table('users')->count(),
            ];
        }

        // Choose the correct Blade view for this role
        return match ($auth['role']) {
            'requestor' => view('dashboards.requestor', $data),
            'authorized_personnel' => view('dashboards.superadmin', $data),
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
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->where('po.requestor_id', $auth['user_id'])
                    ->whereNotNull('st.status_name')->count(),
                'my_drafts' => DB::table('approvals as ap')->join('purchase_orders as po','po.purchase_order_id','=','ap.purchase_order_id')->join('statuses as st','st.status_id','=','ap.status_id')->where('po.requestor_id',$auth['user_id'])->where('st.status_name','Pending')->count(),
                'my_verified' => DB::table('approvals as ap')->join('purchase_orders as po','po.purchase_order_id','=','ap.purchase_order_id')->join('statuses as st','st.status_id','=','ap.status_id')->where('po.requestor_id',$auth['user_id'])->where('st.status_name','Verified')->count(),
                'my_approved' => DB::table('approvals as ap')->join('purchase_orders as po','po.purchase_order_id','=','ap.purchase_order_id')->join('statuses as st','st.status_id','=','ap.status_id')->where('po.requestor_id',$auth['user_id'])->where('st.status_name','Approved')->count(),
            ];
            $payload['drafts'] = DB::table('purchase_orders as po')
                ->leftJoin('approvals as ap','ap.purchase_order_id','=','po.purchase_order_id')
                ->leftJoin('statuses as st','st.status_id','=','ap.status_id')
                ->where('po.requestor_id', $auth['user_id'])
                ->where('st.status_name', 'Pending')
                ->whereNotNull('st.status_name') // Only show POs with status
                ->orderByDesc('po.created_at')
                ->limit(5)
                ->select('po.purchase_order_no','po.purpose','po.total','st.status_name')
                ->get();
        }

        if ($auth['role'] === 'authorized_personnel') {
            $payload['metrics'] = [
                'approved' => DB::table('approvals as ap')->join('statuses as st','st.status_id','=','ap.status_id')->where('st.status_name','Approved')->count(),
                'received' => DB::table('approvals')->whereNotNull('received_at')->count(),
                'suppliers' => DB::table('suppliers')->count(),
                'users' => DB::table('users')->count(),
            ];
        }

        if ($auth['role'] === 'superadmin') {
            $payload['metrics'] = [
                'total_pos' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                    ->whereNotNull('st.status_name')->count(),
                'pending_pos' => DB::table('purchase_orders as po')
                    ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
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


