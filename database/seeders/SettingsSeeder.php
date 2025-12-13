<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'app.name', 'value' => 'Procurement'],
            ['key' => 'branding.logo_path', 'value' => null],
            ['key' => 'company_logo', 'value' => null],
            ['key' => 'company_name', 'value' => 'Global Agility'],

            // Database configuration settings
            ['key' => 'db.host', 'value' => env('DB_HOST', 'localhost')],
            ['key' => 'db.port', 'value' => env('DB_PORT', '1433')],
            ['key' => 'db.database', 'value' => env('DB_DATABASE', 'Database')],
            ['key' => 'db.username', 'value' => env('DB_USERNAME', 'Admin')],
            ['key' => 'db.password', 'value' => env('DB_PASSWORD', '122002')],
            ['key' => 'db.encrypt', 'value' => env('DB_ENCRYPT', 'no')],
            ['key' => 'db.trust_server_certificate', 'value' => env('DB_TRUST_SERVER_CERTIFICATE', 'true')],
            ['key' => 'db.connection_pooling', 'value' => env('DB_CONNECTION_POOLING', 'true')],
            ['key' => 'db.multiple_active_result_sets', 'value' => env('DB_MULTIPLE_ACTIVE_RESULT_SETS', 'false')],
            ['key' => 'db.query_timeout', 'value' => env('DB_QUERY_TIMEOUT', '30')],
            ['key' => 'db.timeout', 'value' => env('DB_TIMEOUT', '30')],
        ];
        foreach ($defaults as $row) {
            if (! DB::table('settings')->where('key', $row['key'])->exists()) {
                DB::table('settings')->insert($row + ['created_at' => now(), 'updated_at' => now()]);
            }
        }
    }
}
