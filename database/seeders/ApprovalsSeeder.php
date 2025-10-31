<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Seed the approval chain for a seeded purchase order */
class ApprovalsSeeder extends Seeder
{
    public function run(): void
    {
        $poId = DB::table('purchase_orders')->where('purchase_order_no', '1')->value('purchase_order_id');
        if (!$poId) return;

        $preparedBy = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('role_types.user_role_type', 'requestor')
            ->value('roles.user_id');
        $receivedBy = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('role_types.user_role_type', 'superadmin')
            ->value('roles.user_id');

        $statusApproved = DB::table('statuses')->where('status_name', 'Approved')->value('status_id');
        $statusDraft = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');

        if (!DB::table('approvals')->where('purchase_order_id', $poId)->exists()) {
            DB::table('approvals')->insert([
                'purchase_order_id' => $poId,
                'prepared_by_id' => $preparedBy,
                'prepared_at' => now(),
                'received_by_id' => $receivedBy,
                'received_at' => null,
                'status_id' => $statusApproved ?: $statusDraft,
                'remarks' => 'Initial PO from seeder',
            ]);
        }
    }
}








