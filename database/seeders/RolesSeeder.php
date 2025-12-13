<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Remove existing roles not tied to the two users
        \Illuminate\Support\Facades\DB::table('roles')->whereNotIn('user_id', ['87654321-0000-0000-0000-000000000001', '87654321-0000-0000-0000-000000000002'])->delete();

        // Map role_type names to IDs
        $requestorId = DB::table('role_types')->where('user_role_type', 'requestor')->value('role_type_id');
        $superadminId = DB::table('role_types')->where('user_role_type', 'superadmin')->value('role_type_id');

        $rows = [
            ['role_id' => '44444444-0000-0000-0000-000000000001', 'user_id' => '87654321-0000-0000-0000-000000000001', 'role_type_id' => $superadminId],
            ['role_id' => '44444444-0000-0000-0000-000000000002', 'user_id' => '87654321-0000-0000-0000-000000000002', 'role_type_id' => $requestorId],
        ];
        foreach ($rows as $r) {
            if (! DB::table('roles')->where('role_id', $r['role_id'])->exists()) {
                DB::table('roles')->insert($r);
            }
        }
    }
}
