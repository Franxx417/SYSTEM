<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Seed the 4 test accounts, their login rows, and role mappings */
class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $roleTypeMap = DB::table('role_types')->pluck('role_type_id', 'user_role_type');

        $testUsers = [
            [
                'username' => 'admin',
                'password' => 'admin123',
                'name' => 'John Rey Dabaras',
                'role' => 'requestor',
                'position' => 'Procurement Manager',
                'department' => 'Procurement',
                'email' => 'admin@example.com',
            ],
    
            [
                'username' => 'auth_personnel',
                'password' => 'auth123',
                'name' => 'Von Ivan Punzalan Nasa LU',
                'role' => 'authorized_personnel',
                'position' => 'Authorized Personnel',
                'department' => 'Administration',
                'email' => 'auth_personnel@example.com',
            ],
        ];

        foreach ($testUsers as $acc) {
            $existing = DB::table('users')->where('email', $acc['email'])->first();
            if (!$existing) {
                $userId = (string) Str::uuid();
                DB::table('users')->insert([
                    'user_id' => $userId,
                    'name' => $acc['name'],
                    'email' => $acc['email'],
                    'position' => $acc['position'],
                    'department' => $acc['department'],
                ]);


                DB::table('login')->insert([
                    'login_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'username' => $acc['username'],
                    'password' => Hash::make($acc['password']),
                ]);

                DB::table('roles')->insert([
                    'role_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'role_type_id' => $roleTypeMap[$acc['role']],
                ]);
            }
        }
    }
}


