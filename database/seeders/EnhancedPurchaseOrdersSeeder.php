<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnhancedPurchaseOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users and suppliers
        $users = DB::table('users')->get();
        $suppliers = DB::table('suppliers')->get();
        $statuses = DB::table('statuses')->get();

        if ($users->isEmpty() || $suppliers->isEmpty() || $statuses->isEmpty()) {
            return;
        }

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

        $itemCategories = [
            // Computer Hardware
            ['name' => 'Dell Optiplex 7090', 'description' => 'Intel Core i7-11700, 16GB RAM, 512GB SSD, Windows 11 Pro', 'price_range' => [2000, 3000]],
            ['name' => 'HP EliteBook 850 G8', 'description' => 'Intel Core i5-1135G7, 8GB RAM, 256GB SSD, 15.6" Display', 'price_range' => [1500, 2500]],
            ['name' => 'MacBook Pro 16" M1 Pro', 'description' => 'Apple M1 Pro chip, 16GB RAM, 512GB SSD, 16" Liquid Retina XDR', 'price_range' => [3000, 4000]],
            ['name' => 'Surface Laptop 4', 'description' => 'Intel Core i7-1185G7, 16GB RAM, 512GB SSD, 13.5" Touch Display', 'price_range' => [1800, 2800]],

            // Monitors
            ['name' => 'Dell UltraSharp U2720Q', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, USB-C, IPS Panel', 'price_range' => [400, 600]],
            ['name' => 'LG 27UN850-W', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, USB-C, HDR10, IPS Panel', 'price_range' => [350, 550]],
            ['name' => 'Samsung Odyssey G7', 'description' => '32" QHD Gaming Monitor, 2560x1440, 240Hz, 1ms, Curved VA Panel', 'price_range' => [500, 700]],

            // Peripherals
            ['name' => 'Logitech MX Master 3', 'description' => 'Wireless Mouse, 4000 DPI, 70-day battery, USB-C charging', 'price_range' => [80, 120]],
            ['name' => 'Corsair K95 RGB Platinum', 'description' => 'Mechanical Gaming Keyboard, Cherry MX Speed switches, RGB backlighting', 'price_range' => [150, 250]],
            ['name' => 'SteelSeries Arctis 7', 'description' => 'Wireless Gaming Headset, 2.4GHz, 24-hour battery, 7.1 surround sound', 'price_range' => [100, 200]],

            // Office Furniture
            ['name' => 'Herman Miller Aeron Chair', 'description' => 'Ergonomic Office Chair, Size B, PostureFit SL, Adjustable arms', 'price_range' => [800, 1200]],
            ['name' => 'Steelcase Leap V2', 'description' => 'Ergonomic Office Chair, LiveBack technology, Adjustable lumbar support', 'price_range' => [600, 900]],
            ['name' => 'IKEA Markus Chair', 'description' => 'Office Chair, Black, Adjustable height, 10-year warranty', 'price_range' => [100, 200]],

            // Networking
            ['name' => 'Cisco Catalyst 2960-X', 'description' => '24-Port Gigabit Ethernet Switch, Layer 3, PoE+ support', 'price_range' => [500, 800]],
            ['name' => 'Ubiquiti UniFi Dream Machine', 'description' => 'All-in-one Security Gateway, 4-port switch, WiFi 6, 1.3Gbps throughput', 'price_range' => [300, 500]],

            // Printers
            ['name' => 'HP LaserJet Pro M404dn', 'description' => 'Monochrome Laser Printer, 38ppm, Duplex printing, Network ready', 'price_range' => [200, 400]],
            ['name' => 'Canon imageCLASS MF445dw', 'description' => 'All-in-one Laser Printer, 28ppm, Duplex, WiFi, Mobile printing', 'price_range' => [150, 300]],

            // Storage
            ['name' => 'Western Digital My Book 4TB', 'description' => 'External Hard Drive, USB 3.0, 4TB capacity, Automatic backup', 'price_range' => [80, 120]],
            ['name' => 'Samsung T7 Portable SSD 1TB', 'description' => 'Portable SSD, USB 3.2 Gen 2, 1TB capacity, 1050MB/s read', 'price_range' => [100, 150]],

            // Office Supplies
            ['name' => 'Pilot G2 Retractable Pen', 'description' => 'Gel ink pen, 0.7mm, Blue ink, 12-pack', 'price_range' => [10, 20]],
            ['name' => 'Moleskine Classic Notebook', 'description' => 'Hard cover notebook, Large, Ruled, 240 pages', 'price_range' => [15, 25]],
            ['name' => 'Post-it Notes 3x3', 'description' => 'Self-stick notes, 3x3 inches, Yellow, 12 pads', 'price_range' => [5, 15]],
        ];

        // Create 100+ purchase orders
        for ($i = 1; $i <= 120; $i++) {
            $poId = (string) Str::uuid();
            $poNo = (string) $i;
            $user = $users->random();
            $supplier = $suppliers->random();
            $status = $statuses->random();
            $purpose = $purposes[array_rand($purposes)];

            // Random dates within the last 6 months
            $dateRequested = now()->subDays(rand(0, 180))->toDateString();
            $deliveryDate = now()->subDays(rand(0, 150))->toDateString();

            // Create 1-5 items per PO
            $itemCount = rand(1, 5);
            $subtotal = 0;
            $items = [];

            for ($j = 0; $j < $itemCount; $j++) {
                $itemTemplate = $itemCategories[array_rand($itemCategories)];
                $quantity = rand(1, 10);
                $unitPrice = rand($itemTemplate['price_range'][0], $itemTemplate['price_range'][1]) + (rand(0, 99) / 100);
                $totalCost = $quantity * $unitPrice;
                $subtotal += $totalCost;

                $items[] = [
                    'item_id' => (string) Str::uuid(),
                    'purchase_order_id' => $poId,
                    'item_name' => $itemTemplate['name'],
                    'item_description' => $itemTemplate['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_cost' => $totalCost,
                    'created_at' => $dateRequested,
                    'updated_at' => $dateRequested,
                ];
            }

            // Calculate shipping and discount
            $shipping = rand(0, 100) + (rand(0, 99) / 100);
            $discount = rand(0, 50) + (rand(0, 99) / 100);
            $total = $subtotal + $shipping - $discount;

            // Create PO
            DB::table('purchase_orders')->insert([
                'purchase_order_id' => $poId,
                'requestor_id' => $user->user_id,
                'supplier_id' => $supplier->supplier_id,
                'purpose' => $purpose,
                'purchase_order_no' => $poNo,
                'official_receipt_no' => rand(0, 1) ? 'OR-'.str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) : null,
                'date_requested' => $dateRequested,
                'delivery_date' => $deliveryDate,
                'shipping_fee' => $shipping,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'total' => $total,
                'created_at' => $dateRequested,
                'updated_at' => $dateRequested,
            ]);

            // Create items
            foreach ($items as $item) {
                DB::table('items')->insert($item);
            }

            // Create approval record
            DB::table('approvals')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $poId,
                'prepared_by_id' => $user->user_id,
                'prepared_at' => $dateRequested,
                'status_id' => $status->status_id,
                'remarks' => 'Generated for testing',
            ]);
        }
    }
}
