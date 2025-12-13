<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Seed the role_types reference table (role code list) */
class RoleTypesSeeder extends Seeder
{
    public function run(): void
    {
        $roleTypes = [
            'requestor',
            'superadmin',
        ];
        // Note: do not delete extras here to avoid FK conflicts; RolesSeeder cleans up roles
        foreach ($roleTypes as $type) {
            if (! DB::table('role_types')->where('user_role_type', $type)->exists()) {
                DB::table('role_types')->insert([
                    'role_type_id' => (string) Str::uuid(),
                    'user_role_type' => $type,
                ]);
            }
        }
    }
}
