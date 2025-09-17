<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersAndLoginSeeder extends Seeder
{
    public function run(): void
    {
        // Keep only two users: requestor and superadmin
        // Clean up any other existing users/roles
        DB::table('roles')->whereNotIn('user_id', ['87654321-0000-0000-0000-000000000001','87654321-0000-0000-0000-000000000002'])->delete();
        DB::table('login')->whereNotIn('user_id', ['87654321-0000-0000-0000-000000000001','87654321-0000-0000-0000-000000000002'])->delete();
        DB::table('users')->whereNotIn('user_id', ['87654321-0000-0000-0000-000000000001','87654321-0000-0000-0000-000000000002'])->delete();

        $users = [
            ['user_id' => '87654321-0000-0000-0000-000000000001','name' => 'John Doe','email' => 'john.doe@procurement.com','position' => 'System Administrator','department' => 'IT Department','username' => 'superadmin','password' => 'superadmin123'],
            ['user_id' => '87654321-0000-0000-0000-000000000002','name' => 'Requestor User','email' => 'requestor@procurement.com','position' => 'Requestor','department' => 'Procurement','username' => 'requestor','password' => 'requestor123'],
        ];

        foreach ($users as $u) {
            if (!DB::table('users')->where('user_id', $u['user_id'])->exists()) {
                DB::table('users')->insert([
                    'user_id' => $u['user_id'],
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'position' => $u['position'],
                    'department' => $u['department'],
                ]);
            }
            if (!DB::table('login')->where('user_id', $u['user_id'])->exists()) {
                DB::table('login')->insert([
                    'login_id' => (string) Str::uuid(),
                    'user_id' => $u['user_id'],
                    'username' => $u['username'],
                    'password' => Hash::make($u['password']),
                ]);
            }
        }
    }
}


