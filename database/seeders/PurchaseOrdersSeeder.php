<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

/** Seed sample purchase orders and items to enable dashboards/suggestions */
class PurchaseOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have users and suppliers
        $requestorUserId = DB::table('roles')
            ->join('role_types', 'roles.role_type_id', '=', 'role_types.role_type_id')
            ->where('role_types.user_role_type', 'requestor')
            ->value('roles.user_id');
        $supplierId = DB::table('suppliers')->value('supplier_id');
        if (!$requestorUserId || !$supplierId) {
            return;
        }

        $existing = DB::table('purchase_orders')->where('purchase_order_no', '1')->first();
        if (!$existing) {
            DB::table('purchase_orders')->insert([
                'requestor_id' => $requestorUserId,
                'supplier_id' => $supplierId,
                'purpose' => 'Replacement of old PCs',
                'purchase_order_no' => '1',
                'official_receipt_no' => null,
                'date_requested' => now()->toDateString(),
                'delivery_date' => now()->addDays(14)->toDateString(),
                'shipping_fee' => 6000,
                'discount' => 13543,
                'subtotal' => 257317,
                'total' => 288195.04,
            ]);
        }

        $poId = DB::table('purchase_orders')->where('purchase_order_no', '1')->value('purchase_order_id');

        // Create approval record for PO 1
        if ($poId && !DB::table('approvals')->where('purchase_order_id', $poId)->exists()) {
            $statusPending = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');
            if ($statusPending) {
                DB::table('approvals')->insert([
                    'approval_id' => \Illuminate\Support\Str::uuid(),
                    'purchase_order_id' => $poId,
                    'prepared_by_id' => $requestorUserId,
                    'prepared_at' => now(),
                    'status_id' => $statusPending,
                    'remarks' => 'Created',
                ]);
            }
        }

        // Items
        if ($poId) {
            if (!DB::table('items')->where('purchase_order_id', $poId)->exists()) {
                DB::table('items')->insert([
                    [
                        'purchase_order_id' => $poId,
                        'item_description' => 'Dell Optiplex 3060 core i5 8th gen',
                        'quantity' => 10,
                        'unit_price' => 20996.00,
                        'total_cost' => 209960.00,
                    ],
                    [
                        'purchase_order_id' => $poId,
                        'item_description' => 'AIWA 24 INC FHD IPS',
                        'quantity' => 10,
                        'unit_price' => 5490.00,
                        'total_cost' => 54900.00,
                    ],
                ]);
            }
        }

        // Additional historical orders to enrich suggestions/frequency
        $existing2 = DB::table('purchase_orders')->where('purchase_order_no', '2')->first();
        if (!$existing2) {
            DB::table('purchase_orders')->insert([
                'requestor_id' => $requestorUserId,
                'supplier_id' => $supplierId,
                'purpose' => 'Additional monitors for new hires',
                'purchase_order_no' => '2',
                'official_receipt_no' => null,
                'date_requested' => now()->subDays(10)->toDateString(),
                'delivery_date' => now()->subDays(3)->toDateString(),
                'shipping_fee' => 0,
                'discount' => 0,
                'subtotal' => 109800.00,
                'total' => 109800.00 * 1.12,
            ]);
            $po2 = DB::table('purchase_orders')->where('purchase_order_no','2')->value('purchase_order_id');
            
            // Create approval record for PO 2
            if ($po2 && !DB::table('approvals')->where('purchase_order_id', $po2)->exists()) {
                $statusPending = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');
                if ($statusPending) {
                    DB::table('approvals')->insert([
                        'approval_id' => \Illuminate\Support\Str::uuid(),
                        'purchase_order_id' => $po2,
                        'prepared_by_id' => $requestorUserId,
                        'prepared_at' => now(),
                        'status_id' => $statusPending,
                        'remarks' => 'Created',
                    ]);
                }
            }
            
            DB::table('items')->insert([
                [
                    'purchase_order_id' => $po2,
                    'item_description' => 'AIWA 24 INC FHD IPS',
                    'quantity' => 20,
                    'unit_price' => 5490.00,
                    'total_cost' => 109800.00,
                ],
            ]);
        }

        $supplier2 = DB::table('suppliers')->where('name', 'OfficeHub Trading')->value('supplier_id');
        if ($supplier2) {
            $existing3 = DB::table('purchase_orders')->where('purchase_order_no', '3')->first();
            if (!$existing3) {
                DB::table('purchase_orders')->insert([
                    'requestor_id' => $requestorUserId,
                    'supplier_id' => $supplier2,
                    'purpose' => 'Keyboards and mice replacement',
                    'purchase_order_no' => '3',
                    'official_receipt_no' => null,
                    'date_requested' => now()->subDays(20)->toDateString(),
                    'delivery_date' => now()->subDays(12)->toDateString(),
                    'shipping_fee' => 0,
                    'discount' => 0,
                    'subtotal' => 45000.00,
                    'total' => 50400.00,
                ]);
                $po3 = DB::table('purchase_orders')->where('purchase_order_no','3')->value('purchase_order_id');
                
                // Create approval record for PO 3
                if ($po3 && !DB::table('approvals')->where('purchase_order_id', $po3)->exists()) {
                    $statusPending = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');
                    if ($statusPending) {
                        DB::table('approvals')->insert([
                            'approval_id' => \Illuminate\Support\Str::uuid(),
                            'purchase_order_id' => $po3,
                            'prepared_by_id' => $requestorUserId,
                            'prepared_at' => now(),
                            'status_id' => $statusPending,
                            'remarks' => 'Created',
                        ]);
                    }
                }
                
                DB::table('items')->insert([
                    [
                        'purchase_order_id' => $po3,
                        'item_description' => 'Logitech K120 Keyboard',
                        'quantity' => 100,
                        'unit_price' => 250.00,
                        'total_cost' => 25000.00,
                    ],
                    [
                        'purchase_order_id' => $po3,
                        'item_description' => 'Logitech B100 Mouse',
                        'quantity' => 100,
                        'unit_price' => 200.00,
                        'total_cost' => 20000.00,
                    ],
                ]);
            }
        }

        // Create additional POs for testing with large datasets
        $this->createAdditionalPOs($requestorUserId, $supplierId);
    }

    private function createAdditionalPOs($requestorUserId, $supplierId)
    {
        $purposes = [
            'Office equipment upgrade',
            'IT infrastructure maintenance',
            'New employee workstation setup',
            'Software license renewal',
            'Hardware replacement',
            'Network equipment upgrade',
            'Security system installation',
            'Furniture procurement',
            'Cleaning supplies restock',
            'Emergency equipment purchase',
            'Project-specific requirements',
            'Department expansion',
            'Technology refresh',
            'Compliance requirements',
            'Operational needs',
            'Client project delivery',
            'Training materials',
            'Conference room setup',
            'Warehouse equipment',
            'Maintenance supplies',
        ];

        $suppliers = DB::table('suppliers')->get();
        $statuses = DB::table('statuses')->get();
        
        if ($suppliers->isEmpty() || $statuses->isEmpty()) {
            return;
        }

        // Create 50+ additional POs
        for ($i = 4; $i <= 60; $i++) {
            // Check if PO already exists
            $existing = DB::table('purchase_orders')->where('purchase_order_no', (string) $i)->first();
            if ($existing) {
                continue; // Skip if PO already exists
            }

            $poId = (string) \Illuminate\Support\Str::uuid();
            $poNo = (string) $i;
            $supplier = $suppliers->random();
            $status = $statuses->random();
            $purpose = $purposes[array_rand($purposes)];
            
            // Random dates within the last 6 months
            $dateRequested = now()->subDays(rand(0, 180))->toDateString();
            $deliveryDate = now()->subDays(rand(0, 150))->toDateString();
            
            // Random totals
            $subtotal = rand(5000, 100000) + (rand(0, 99) / 100);
            $shipping = rand(0, 2000) + (rand(0, 99) / 100);
            $discount = rand(0, 5000) + (rand(0, 99) / 100);
            $total = $subtotal + $shipping - $discount;
            
            // Create PO
            DB::table('purchase_orders')->insert([
                'purchase_order_id' => $poId,
                'requestor_id' => $requestorUserId,
                'supplier_id' => $supplier->supplier_id,
                'purpose' => $purpose,
                'purchase_order_no' => $poNo,
                'official_receipt_no' => rand(0, 1) ? 'OR-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) : null,
                'date_requested' => $dateRequested,
                'delivery_date' => $deliveryDate,
                'shipping_fee' => $shipping,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'total' => $total,
                'created_at' => $dateRequested,
                'updated_at' => $dateRequested,
            ]);
            
            // Create approval record
            DB::table('approvals')->insert([
                'approval_id' => (string) \Illuminate\Support\Str::uuid(),
                'purchase_order_id' => $poId,
                'prepared_by_id' => $requestorUserId,
                'prepared_at' => $dateRequested,
                'status_id' => $status->status_id,
                'remarks' => 'Generated for testing',
            ]);
        }
    }
}








