<?php
/**
 * DepartmentHeadController
 *
 * Lists POs awaiting department head approval and supports queue display.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentHeadController extends Controller
{
    /** List POs awaiting department head approval */
    public function queue(Request $request)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || $auth['role'] !== 'department_head') abort(403);

        $rows = DB::table('approvals as ap')
            ->join('purchase_orders as po','po.purchase_order_id','=','ap.purchase_order_id')
            ->join('statuses as st','st.status_id','=','ap.status_id')
            ->where('st.status_name','Verified')
            ->whereNull('ap.approved_at')
            ->select('po.purchase_order_id','po.purchase_order_no','po.purpose','po.total')
            ->orderByDesc('po.created_at')
            ->paginate(10);

        return view('dept.queue', ['rows' => $rows, 'auth' => $auth]);
    }
}





