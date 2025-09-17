<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Testing PO creation with item_name...\n";

// Simulate form data
$testData = [
    'supplier_id' => DB::table('suppliers')->first()->supplier_id,
    'purpose' => 'Test PO with item name',
    'date_requested' => '2024-01-15',
    'delivery_date' => '2024-01-20',
    'items' => [
        [
            'item_name' => 'Test Item Name',
            'item_description' => 'Test Item Description',
            'quantity' => 2,
            'unit_price' => 100.00
        ]
    ]
];

echo "Test data:\n";
print_r($testData);

// Test validation
$request = new \App\Http\Requests\StorePurchaseOrderRequest();
$request->replace($testData);

try {
    $validated = $request->validated();
    echo "\nValidation passed!\n";
    echo "Validated items:\n";
    print_r($validated['items']);
} catch (Exception $e) {
    echo "\nValidation failed: " . $e->getMessage() . "\n";
}



