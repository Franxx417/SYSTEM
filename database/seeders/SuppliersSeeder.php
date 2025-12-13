<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Seed a couple of suppliers for demo/testing */
class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Acme IT Supplies', 'address' => '123 Tech Park, Metro City', 'vat_type' => 'VAT', 'contact_person' => 'Mark Rivera', 'contact_number' => '0917-000-1111', 'tin_no' => '123-456-789'],
            ['name' => 'OfficeHub Trading', 'address' => 'Unit 8, Business Center, Quezon City', 'vat_type' => 'Non_VAT', 'contact_person' => 'Lara Cruz', 'contact_number' => '0917-000-2222', 'tin_no' => '234-567-890'],
        ];
        foreach ($suppliers as $s) {
            if (! DB::table('suppliers')->where('name', $s['name'])->exists()) {
                DB::table('suppliers')->insert([
                    'supplier_id' => (string) Str::uuid(),
                    ...$s,
                ]);
            }
        }
    }
}
