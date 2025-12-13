<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Seed workflow statuses (Draft, Verified, Approved, Received, Rejected) */
class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['status_name' => 'Pending', 'description' => 'Created by requestor, awaiting verification'],
            ['status_name' => 'Verified', 'description' => 'Verified by finance controller'],
            ['status_name' => 'Approved', 'description' => 'Approved by department head'],
            ['status_name' => 'Received', 'description' => 'Received by authorized personnel'],
            ['status_name' => 'Rejected', 'description' => 'Rejected at any stage'],
        ];
        foreach ($statuses as $status) {
            if (! DB::table('statuses')->where('status_name', $status['status_name'])->exists()) {
                $guid = (string) Str::uuid();
                DB::table('statuses')->insert([
                    'status_id' => $guid,
                    'status_name' => $status['status_name'],
                    'description' => $status['description'],
                ]);
            }
        }
    }
}
