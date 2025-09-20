<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "Creating a test PO manually...\n";

try {
    DB::transaction(function () {
        // Get a supplier
        $supplier = DB::table('suppliers')->first();
        if (!$supplier) {
            echo "No suppliers found\n";
            return;
        }
        
        // Get a user
        $user = DB::table('users')->first();
        if (!$user) {
            echo "No users found\n";
            return;
        }
        
        // Create PO
        $poId = (string) Str::uuid();
        $poNo = 'TEST-' . time();
        
        DB::table('purchase_orders')->insert([
            'purchase_order_id' => $poId,
            'requestor_id' => $user->user_id,
            'supplier_id' => $supplier->supplier_id,
            'purpose' => 'Test PO with item_name',
            'purchase_order_no' => $poNo,
            'official_receipt_no' => null,
            'date_requested' => '2024-01-15',
            'delivery_date' => '2024-01-20',
            'shipping_fee' => 0.00,
            'discount' => 0.00,
            'subtotal' => 200.00,
            'total' => 200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create item with item_name
        DB::table('items')->insert([
            'item_id' => (string) Str::uuid(),
            'purchase_order_id' => $poId,
            'item_name' => 'Test Item Name',
            'item_description' => 'Test Item Description',
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_cost' => 200.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Create approval record
        $status = DB::table('statuses')->where('status_name', 'Pending')->first();
        if ($status) {
            DB::table('approvals')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $poId,
                'prepared_by_id' => $user->user_id,
                'prepared_at' => now(),
                'status_id' => $status->status_id,
                'remarks' => 'Test creation',
            ]);
        }
        
        echo "Created PO: $poNo\n";
    });
    
    // Check if item_name was saved
    $item = DB::table('items')->where('item_description', 'Test Item Description')->first();
    if ($item) {
        echo "Item found: Name='" . ($item->item_name ?? 'NULL') . "', Desc='" . $item->item_description . "'\n";
    } else {
        echo "Item not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
