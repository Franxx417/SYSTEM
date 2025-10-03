<?php
/**
 * ApprovalController
 *
 * Workflow transitions for POs: verify, approve, reject, receive.
 * Each action updates the approvals table and status accordingly.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Approval actions for each role:
 * - Finance: verify
 * - Department head: approve
 * - Authorized personnel: receive
 */
class ApprovalController extends Controller
{
    /** Get current session user or 403 - SUPERADMIN HAS UNRESTRICTED ACCESS */
    private function auth(Request $request): array
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth) abort(403);
        return $auth;
    }

    /** Mark PO as Received - SUPERADMIN HAS UNRESTRICTED ACCESS */
    public function receive(Request $request, string $poId)
    {
        $auth = $this->auth($request);
        // SUPERADMIN HAS UNRESTRICTED ACCESS TO EVERYTHING
        if ($auth['role'] !== 'authorized_personnel' && $auth['role'] !== 'superadmin') {
            abort(403);
        }
        DB::table('approvals')->where('purchase_order_id', $poId)->update([
            'received_by_id' => $auth['user_id'],
            'received_at' => now(),
        ]);
        return back()->with('status','Marked as received');
    }
}



