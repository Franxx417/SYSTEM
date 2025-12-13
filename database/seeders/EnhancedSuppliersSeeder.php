<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnhancedSuppliersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            // Technology Companies
            ['name' => 'Microsoft Philippines', 'vat_type' => 'VAT', 'address' => '6750 Ayala Avenue, Makati City', 'contact_person' => 'John Santos', 'contact_number' => '+63-2-8888-9999', 'tin_no' => '123456789'],
            ['name' => 'Apple Philippines', 'vat_type' => 'VAT', 'address' => 'Glorietta 5, Makati City', 'contact_person' => 'Maria Cruz', 'contact_number' => '+63-2-7777-8888', 'tin_no' => '234567890'],
            ['name' => 'Dell Technologies PH', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Robert Lim', 'contact_number' => '+63-2-6666-7777', 'tin_no' => '345678901'],
            ['name' => 'HP Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Lisa Garcia', 'contact_number' => '+63-2-5555-6666', 'tin_no' => '456789012'],
            ['name' => 'Lenovo Philippines', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'David Chen', 'contact_number' => '+63-2-4444-5555', 'tin_no' => '567890123'],

            // Office Supplies
            ['name' => 'Office Depot Philippines', 'vat_type' => 'VAT', 'address' => 'SM Megamall, Mandaluyong City', 'contact_person' => 'Anna Reyes', 'contact_number' => '+63-2-3333-4444', 'tin_no' => '678901234'],
            ['name' => 'Staples Philippines', 'vat_type' => 'VAT', 'address' => 'Robinsons Place, Manila', 'contact_person' => 'Michael Torres', 'contact_number' => '+63-2-2222-3333', 'tin_no' => '789012345'],
            ['name' => 'Pilot Pen Philippines', 'vat_type' => 'VAT', 'address' => 'Quezon Avenue, Quezon City', 'contact_person' => 'Sarah Lopez', 'contact_number' => '+63-2-1111-2222', 'tin_no' => '890123456'],
            ['name' => '3M Philippines', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'James Wilson', 'contact_number' => '+63-2-0000-1111', 'tin_no' => '901234567'],
            ['name' => 'Canon Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Avenue, Pasig City', 'contact_person' => 'Jennifer Lee', 'contact_number' => '+63-2-9999-0000', 'tin_no' => '012345678'],

            // Furniture & Equipment
            ['name' => 'IKEA Philippines', 'vat_type' => 'VAT', 'address' => 'Mall of Asia, Pasay City', 'contact_person' => 'Carlos Rodriguez', 'contact_number' => '+63-2-8888-0000', 'tin_no' => '123456780'],
            ['name' => 'Office Furniture Solutions', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Elena Martinez', 'contact_number' => '+63-2-7777-0000', 'tin_no' => '234567801'],
            ['name' => 'Steelcase Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Antonio Silva', 'contact_number' => '+63-2-6666-0000', 'tin_no' => '345678012'],
            ['name' => 'Herman Miller PH', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Carmen Flores', 'contact_number' => '+63-2-5555-0000', 'tin_no' => '456789023'],
            ['name' => 'Knoll Philippines', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'Rafael Gomez', 'contact_number' => '+63-2-4444-0000', 'tin_no' => '567890134'],

            // Electronics & IT
            ['name' => 'Samsung Philippines', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Kim Park', 'contact_number' => '+63-2-3333-0000', 'tin_no' => '678901245'],
            ['name' => 'LG Electronics PH', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Jin Kim', 'contact_number' => '+63-2-2222-0000', 'tin_no' => '789012356'],
            ['name' => 'Sony Philippines', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Yuki Tanaka', 'contact_number' => '+63-2-1111-0000', 'tin_no' => '890123467'],
            ['name' => 'Panasonic Philippines', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'Hiroshi Sato', 'contact_number' => '+63-2-0000-0000', 'tin_no' => '901234578'],
            ['name' => 'Toshiba Philippines', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Akira Yamamoto', 'contact_number' => '+63-2-9999-9999', 'tin_no' => '012345689'],

            // Networking & Communications
            ['name' => 'Cisco Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Mark Thompson', 'contact_number' => '+63-2-8888-8888', 'tin_no' => '123456790'],
            ['name' => 'Juniper Networks PH', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Lisa Anderson', 'contact_number' => '+63-2-7777-7777', 'tin_no' => '234567801'],
            ['name' => 'Aruba Networks PH', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'David Brown', 'contact_number' => '+63-2-6666-6666', 'tin_no' => '345678012'],
            ['name' => 'Fortinet Philippines', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Sarah Davis', 'contact_number' => '+63-2-5555-5555', 'tin_no' => '456789023'],
            ['name' => 'Palo Alto Networks PH', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Michael Wilson', 'contact_number' => '+63-2-4444-4444', 'tin_no' => '567890134'],

            // Software & Services
            ['name' => 'Oracle Philippines', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Jennifer Taylor', 'contact_number' => '+63-2-3333-3333', 'tin_no' => '678901245'],
            ['name' => 'IBM Philippines', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'Robert Moore', 'contact_number' => '+63-2-2222-2222', 'tin_no' => '789012356'],
            ['name' => 'SAP Philippines', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Elena Jackson', 'contact_number' => '+63-2-1111-1111', 'tin_no' => '890123467'],
            ['name' => 'Salesforce Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Carlos White', 'contact_number' => '+63-2-0000-0000', 'tin_no' => '901234578'],
            ['name' => 'Adobe Philippines', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Carmen Harris', 'contact_number' => '+63-2-9999-9999', 'tin_no' => '012345689'],

            // Security & Surveillance
            ['name' => 'Axis Communications PH', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'Rafael Martin', 'contact_number' => '+63-2-8888-8888', 'tin_no' => '123456790'],
            ['name' => 'Hikvision Philippines', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Kim Thompson', 'contact_number' => '+63-2-7777-7777', 'tin_no' => '234567801'],
            ['name' => 'Dahua Technology PH', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Jin Garcia', 'contact_number' => '+63-2-6666-6666', 'tin_no' => '345678012'],
            ['name' => 'Bosch Security PH', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Yuki Martinez', 'contact_number' => '+63-2-5555-5555', 'tin_no' => '456789023'],
            ['name' => 'Honeywell Philippines', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'Hiroshi Robinson', 'contact_number' => '+63-2-4444-4444', 'tin_no' => '567890134'],

            // Electrical & Power
            ['name' => 'Schneider Electric PH', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Akira Clark', 'contact_number' => '+63-2-3333-3333', 'tin_no' => '678901245'],
            ['name' => 'ABB Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Mark Rodriguez', 'contact_number' => '+63-2-2222-2222', 'tin_no' => '789012356'],
            ['name' => 'Siemens Philippines', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Lisa Lewis', 'contact_number' => '+63-2-1111-1111', 'tin_no' => '890123467'],
            ['name' => 'Eaton Philippines', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'David Lee', 'contact_number' => '+63-2-0000-0000', 'tin_no' => '901234578'],
            ['name' => 'Legrand Philippines', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Sarah Walker', 'contact_number' => '+63-2-9999-9999', 'tin_no' => '012345689'],

            // HVAC & Climate
            ['name' => 'Carrier Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Michael Hall', 'contact_number' => '+63-2-8888-8888', 'tin_no' => '123456790'],
            ['name' => 'Daikin Philippines', 'vat_type' => 'VAT', 'address' => 'Eastwood City, Quezon City', 'contact_person' => 'Jennifer Allen', 'contact_number' => '+63-2-7777-7777', 'tin_no' => '234567801'],
            ['name' => 'Mitsubishi Electric PH', 'vat_type' => 'VAT', 'address' => 'Ayala Avenue, Makati City', 'contact_person' => 'Robert Young', 'contact_number' => '+63-2-6666-6666', 'tin_no' => '345678012'],
            ['name' => 'LG Air Conditioning PH', 'vat_type' => 'VAT', 'address' => 'BGC, Taguig City', 'contact_person' => 'Elena King', 'contact_number' => '+63-2-5555-5555', 'tin_no' => '456789023'],
            ['name' => 'Trane Philippines', 'vat_type' => 'VAT', 'address' => 'Ortigas Center, Pasig City', 'contact_person' => 'Carlos Wright', 'contact_number' => '+63-2-4444-4444', 'tin_no' => '567890134'],

            // Non-VAT Suppliers
            ['name' => 'Local Office Supplies', 'vat_type' => 'Non-VAT', 'address' => 'Quezon City', 'contact_person' => 'Maria Santos', 'contact_number' => '+63-2-3333-3333', 'tin_no' => '678901245'],
            ['name' => 'Small Business Solutions', 'vat_type' => 'Non-VAT', 'address' => 'Manila', 'contact_person' => 'Juan Cruz', 'contact_number' => '+63-2-2222-2222', 'tin_no' => '789012356'],
            ['name' => 'Family Business Corp', 'vat_type' => 'Non-VAT', 'address' => 'Makati City', 'contact_person' => 'Ana Reyes', 'contact_number' => '+63-2-1111-1111', 'tin_no' => '890123467'],
            ['name' => 'Startup Supplies Inc', 'vat_type' => 'Non-VAT', 'address' => 'Taguig City', 'contact_person' => 'Pedro Garcia', 'contact_number' => '+63-2-0000-0000', 'tin_no' => '901234578'],
            ['name' => 'Community Store', 'vat_type' => 'Non-VAT', 'address' => 'Pasig City', 'contact_person' => 'Luz Martinez', 'contact_number' => '+63-2-9999-9999', 'tin_no' => '012345689'],

            // VAT Exempt Suppliers
            ['name' => 'Government Supplier A', 'vat_type' => 'VAT Exempt', 'address' => 'Quezon City', 'contact_person' => 'Jose Dela Cruz', 'contact_number' => '+63-2-8888-8888', 'tin_no' => '123456790'],
            ['name' => 'Educational Materials Co', 'vat_type' => 'VAT Exempt', 'address' => 'Manila', 'contact_person' => 'Rosa Lopez', 'contact_number' => '+63-2-7777-7777', 'tin_no' => '234567801'],
            ['name' => 'Healthcare Supplies PH', 'vat_type' => 'VAT Exempt', 'address' => 'Makati City', 'contact_person' => 'Dr. Maria Flores', 'contact_number' => '+63-2-6666-6666', 'tin_no' => '345678012'],
            ['name' => 'Religious Organization', 'vat_type' => 'VAT Exempt', 'address' => 'Taguig City', 'contact_person' => 'Fr. Antonio Santos', 'contact_number' => '+63-2-5555-5555', 'tin_no' => '456789023'],
            ['name' => 'Charity Foundation', 'vat_type' => 'VAT Exempt', 'address' => 'Pasig City', 'contact_person' => 'Sister Carmen', 'contact_number' => '+63-2-4444-4444', 'tin_no' => '567890134'],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insert([
                'supplier_id' => (string) Str::uuid(),
                'name' => $supplier['name'],
                'vat_type' => $supplier['vat_type'],
                'address' => $supplier['address'],
                'contact_person' => $supplier['contact_person'],
                'contact_number' => $supplier['contact_number'],
                'tin_no' => $supplier['tin_no'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
