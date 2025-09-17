<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnhancedItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Computer Hardware
            ['name' => 'Dell Optiplex 7090', 'description' => 'Intel Core i7-11700, 16GB RAM, 512GB SSD, Windows 11 Pro'],
            ['name' => 'HP EliteBook 850 G8', 'description' => 'Intel Core i5-1135G7, 8GB RAM, 256GB SSD, 15.6" Display'],
            ['name' => 'Lenovo ThinkPad X1 Carbon', 'description' => 'Intel Core i7-1165G7, 16GB RAM, 1TB SSD, 14" 4K Display'],
            ['name' => 'MacBook Pro 16" M1 Pro', 'description' => 'Apple M1 Pro chip, 16GB RAM, 512GB SSD, 16" Liquid Retina XDR'],
            ['name' => 'Surface Laptop 4', 'description' => 'Intel Core i7-1185G7, 16GB RAM, 512GB SSD, 13.5" Touch Display'],
            ['name' => 'ASUS ROG Strix G15', 'description' => 'AMD Ryzen 7 5800H, 16GB RAM, 1TB SSD, RTX 3060, 15.6" 144Hz'],
            ['name' => 'Acer Aspire 5', 'description' => 'Intel Core i5-1135G7, 8GB RAM, 256GB SSD, 15.6" FHD Display'],
            ['name' => 'MSI Creator 15', 'description' => 'Intel Core i7-11800H, 32GB RAM, 1TB SSD, RTX 3070, 15.6" 4K'],
            
            // Monitors & Displays
            ['name' => 'Dell UltraSharp U2720Q', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, USB-C, IPS Panel'],
            ['name' => 'LG 27UN850-W', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, USB-C, HDR10, IPS Panel'],
            ['name' => 'Samsung Odyssey G7', 'description' => '32" QHD Gaming Monitor, 2560x1440, 240Hz, 1ms, Curved VA Panel'],
            ['name' => 'ASUS ProArt PA248QV', 'description' => '24" Professional Monitor, 1920x1200, 60Hz, 99% sRGB, IPS Panel'],
            ['name' => 'BenQ PD2700U', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, 99% sRGB, IPS Panel'],
            ['name' => 'HP EliteDisplay E243d', 'description' => '23.8" FHD Monitor, 1920x1080, 60Hz, USB-C, IPS Panel'],
            ['name' => 'ViewSonic VP2768-4K', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, 99% sRGB, IPS Panel'],
            ['name' => 'AOC 24G2U', 'description' => '24" FHD Gaming Monitor, 1920x1080, 144Hz, 1ms, IPS Panel'],
            
            // Peripherals
            ['name' => 'Logitech MX Master 3', 'description' => 'Wireless Mouse, 4000 DPI, 70-day battery, USB-C charging'],
            ['name' => 'Microsoft Surface Mouse', 'description' => 'Bluetooth Mouse, 1000 DPI, 12-month battery life'],
            ['name' => 'Razer DeathAdder V2', 'description' => 'Gaming Mouse, 20000 DPI, 8 programmable buttons, RGB lighting'],
            ['name' => 'Corsair K95 RGB Platinum', 'description' => 'Mechanical Gaming Keyboard, Cherry MX Speed switches, RGB backlighting'],
            ['name' => 'Logitech MX Keys', 'description' => 'Wireless Keyboard, backlit keys, 5-month battery life, USB-C charging'],
            ['name' => 'Keychron K2', 'description' => 'Wireless Mechanical Keyboard, Gateron switches, RGB backlighting'],
            ['name' => 'SteelSeries Arctis 7', 'description' => 'Wireless Gaming Headset, 2.4GHz, 24-hour battery, 7.1 surround sound'],
            ['name' => 'Sony WH-1000XM4', 'description' => 'Wireless Noise Canceling Headphones, 30-hour battery, LDAC codec'],
            
            // Office Furniture
            ['name' => 'Herman Miller Aeron Chair', 'description' => 'Ergonomic Office Chair, Size B, PostureFit SL, Adjustable arms'],
            ['name' => 'Steelcase Leap V2', 'description' => 'Ergonomic Office Chair, LiveBack technology, Adjustable lumbar support'],
            ['name' => 'IKEA Markus Chair', 'description' => 'Office Chair, Black, Adjustable height, 10-year warranty'],
            ['name' => 'HON Ignition 2.0', 'description' => 'Ergonomic Office Chair, Adjustable lumbar support, Breathable mesh'],
            ['name' => 'Knoll Generation Chair', 'description' => 'Ergonomic Office Chair, Dynamic suspension, Adjustable arms'],
            ['name' => 'Office Depot ProDesk', 'description' => '60" x 30" Office Desk, White, Adjustable height, Cable management'],
            ['name' => 'IKEA Bekant Desk', 'description' => 'Office Desk, 160x80cm, White, Adjustable feet, Cable management'],
            ['name' => 'UPLIFT V2 Standing Desk', 'description' => 'Electric Standing Desk, 60" x 30", Dual motor, Programmable memory'],
            
            // Networking Equipment
            ['name' => 'Cisco Catalyst 2960-X', 'description' => '24-Port Gigabit Ethernet Switch, Layer 3, PoE+ support'],
            ['name' => 'Ubiquiti UniFi Dream Machine', 'description' => 'All-in-one Security Gateway, 4-port switch, WiFi 6, 1.3Gbps throughput'],
            ['name' => 'Netgear ProSAFE GS728TP', 'description' => '28-Port Gigabit Smart Switch, 24 PoE+ ports, 4 SFP ports'],
            ['name' => 'Aruba Instant On 1930', 'description' => '24-Port Gigabit Switch, Cloud-managed, PoE+ support'],
            ['name' => 'TP-Link Omada EAP660 HD', 'description' => 'WiFi 6 Access Point, 2.4GHz + 5GHz, 1.8Gbps, Cloud management'],
            ['name' => 'Ruckus R750 Access Point', 'description' => 'WiFi 6 Access Point, 2.4GHz + 5GHz, 4x4 MU-MIMO, Cloud management'],
            ['name' => 'Fortinet FortiGate 60F', 'description' => 'Next-Generation Firewall, 1.5Gbps throughput, 8x GE RJ45 ports'],
            ['name' => 'Palo Alto PA-220', 'description' => 'Next-Generation Firewall, 500Mbps throughput, 8x GE ports'],
            
            // Printers & Scanners
            ['name' => 'HP LaserJet Pro M404dn', 'description' => 'Monochrome Laser Printer, 38ppm, Duplex printing, Network ready'],
            ['name' => 'Canon imageCLASS MF445dw', 'description' => 'All-in-one Laser Printer, 28ppm, Duplex, WiFi, Mobile printing'],
            ['name' => 'Brother MFC-L2750DW', 'description' => 'All-in-one Laser Printer, 32ppm, Duplex, WiFi, Mobile printing'],
            ['name' => 'Epson WorkForce Pro WF-4830', 'description' => 'All-in-one Inkjet Printer, 20ppm, Duplex, WiFi, Mobile printing'],
            ['name' => 'Xerox WorkCentre 6515', 'description' => 'All-in-one Laser Printer, 25ppm, Duplex, WiFi, Mobile printing'],
            ['name' => 'HP ScanJet Pro 2500 f1', 'description' => 'Flatbed Scanner, 25ppm, Duplex, USB 3.0, TWAIN compatible'],
            ['name' => 'Canon CanoScan LiDE 400', 'description' => 'Flatbed Scanner, 4800x4800 DPI, USB 2.0, TWAIN compatible'],
            ['name' => 'Epson Perfection V600', 'description' => 'Flatbed Scanner, 6400x9600 DPI, USB 2.0, Film scanning'],
            
            // Storage & Backup
            ['name' => 'Western Digital My Book 4TB', 'description' => 'External Hard Drive, USB 3.0, 4TB capacity, Automatic backup'],
            ['name' => 'Seagate Backup Plus Hub 6TB', 'description' => 'External Hard Drive, USB 3.0, 6TB capacity, USB hub'],
            ['name' => 'Samsung T7 Portable SSD 1TB', 'description' => 'Portable SSD, USB 3.2 Gen 2, 1TB capacity, 1050MB/s read'],
            ['name' => 'SanDisk Extreme Pro 2TB', 'description' => 'Portable SSD, USB 3.2 Gen 2, 2TB capacity, 2000MB/s read'],
            ['name' => 'Synology DS220+ NAS', 'description' => '2-Bay NAS, Intel Celeron J4025, 2GB RAM, 2x Gigabit Ethernet'],
            ['name' => 'QNAP TS-251D NAS', 'description' => '2-Bay NAS, Intel Celeron J4005, 4GB RAM, 2x Gigabit Ethernet'],
            ['name' => 'WD My Cloud EX2 Ultra', 'description' => '2-Bay NAS, Marvell ARMADA 385, 2x Gigabit Ethernet'],
            ['name' => 'Buffalo LinkStation 220', 'description' => '2-Bay NAS, Marvell ARMADA 385, 2x Gigabit Ethernet'],
            
            // Software & Licenses
            ['name' => 'Microsoft Office 365 Business', 'description' => 'Office 365 Business Premium, 1 user, 1TB OneDrive, Teams'],
            ['name' => 'Adobe Creative Cloud', 'description' => 'Creative Cloud All Apps, 1 user, 100GB cloud storage'],
            ['name' => 'Windows 11 Pro License', 'description' => 'Windows 11 Pro, Volume License, 1 PC'],
            ['name' => 'VMware vSphere Standard', 'description' => 'vSphere 7.0 Standard, 1 CPU license, 1 year support'],
            ['name' => 'Autodesk AutoCAD 2024', 'description' => 'AutoCAD 2024, 1 user, 1 year subscription'],
            ['name' => 'SolidWorks Professional', 'description' => 'SolidWorks 2024 Professional, 1 user, 1 year subscription'],
            ['name' => 'Tableau Creator', 'description' => 'Tableau Creator, 1 user, 1 year subscription'],
            ['name' => 'Power BI Pro', 'description' => 'Power BI Pro, 1 user, 1 year subscription'],
            
            // Office Supplies
            ['name' => 'Pilot G2 Retractable Pen', 'description' => 'Gel ink pen, 0.7mm, Blue ink, 12-pack'],
            ['name' => 'Uni-ball Signo 207', 'description' => 'Gel ink pen, 0.7mm, Black ink, 12-pack'],
            ['name' => 'Sharpie Permanent Marker', 'description' => 'Fine point permanent marker, Black ink, 12-pack'],
            ['name' => 'Staedtler Triplus Fineliner', 'description' => 'Fine liner pen set, 20 colors, 0.3mm tip'],
            ['name' => 'Moleskine Classic Notebook', 'description' => 'Hard cover notebook, Large, Ruled, 240 pages'],
            ['name' => 'Leuchtturm1917 Notebook', 'description' => 'Hard cover notebook, A5, Dotted, 249 pages'],
            ['name' => 'Post-it Notes 3x3', 'description' => 'Self-stick notes, 3x3 inches, Yellow, 12 pads'],
            ['name' => 'Scotch Magic Tape', 'description' => 'Invisible tape, 3/4 inch x 450 inches, 6 rolls'],
            
            // Cleaning & Maintenance
            ['name' => 'Lysol Disinfecting Wipes', 'description' => 'Disinfecting wipes, 80 count, Lemon scent'],
            ['name' => 'Clorox Disinfecting Spray', 'description' => 'Disinfecting spray, 32 fl oz, Bleach-free'],
            ['name' => 'Swiffer Duster Refills', 'description' => 'Dusting refills, 8 count, Electrostatic'],
            ['name' => 'Pledge Multi-Surface Cleaner', 'description' => 'Multi-surface cleaner, 25 fl oz, Lemon scent'],
            ['name' => 'Windex Glass Cleaner', 'description' => 'Glass cleaner, 32 fl oz, Ammonia-free'],
            ['name' => 'Bona Hardwood Floor Cleaner', 'description' => 'Hardwood floor cleaner, 32 fl oz, Ready-to-use'],
            ['name' => 'O-Cedar Microfiber Mop', 'description' => 'Microfiber mop, Washable pad, Adjustable handle'],
            ['name' => 'Rubbermaid Commercial Trash Can', 'description' => 'Trash can, 32 gallon, Round, Black'],
            
            // Security & Safety
            ['name' => 'Hikvision DS-2CD2143G0-I', 'description' => '4MP IP Camera, 2.8mm lens, Night vision, PoE'],
            ['name' => 'Axis M3046-V', 'description' => '4MP IP Camera, 2.8mm lens, Night vision, PoE'],
            ['name' => 'Dahua N45CG52', 'description' => '5MP IP Camera, 2.8mm lens, Night vision, PoE'],
            ['name' => 'Bosch FLEXIDOME IP 4000', 'description' => '4MP IP Camera, 2.8mm lens, Night vision, PoE'],
            ['name' => 'First Alert Smoke Detector', 'description' => 'Smoke detector, Battery operated, 10-year battery'],
            ['name' => 'Kidde Fire Extinguisher', 'description' => 'Fire extinguisher, 2.5 lb, ABC dry chemical'],
            ['name' => 'Master Lock Combination Lock', 'description' => 'Combination lock, 4-digit, Brass body'],
            ['name' => 'Kensington Lock', 'description' => 'Laptop security lock, 6ft cable, T-bar lock'],
            
            // Electrical & Power
            ['name' => 'APC Back-UPS Pro 1500VA', 'description' => 'UPS, 1500VA/900W, 8 outlets, USB charging'],
            ['name' => 'CyberPower CP1500AVRLCD', 'description' => 'UPS, 1500VA/900W, 8 outlets, LCD display'],
            ['name' => 'Tripp Lite SMART1500LCD', 'description' => 'UPS, 1500VA/900W, 8 outlets, LCD display'],
            ['name' => 'Belkin Surge Protector', 'description' => 'Surge protector, 8 outlets, 2160 joules, 6ft cord'],
            ['name' => 'Monster Power Strip', 'description' => 'Power strip, 8 outlets, 6ft cord, Surge protection'],
            ['name' => 'Anker PowerPort 6', 'description' => 'USB charging station, 6 ports, 60W total output'],
            ['name' => 'RAVPower 65W GaN Charger', 'description' => 'USB-C charger, 65W, GaN technology, Compact'],
            ['name' => 'Apple 20W USB-C Charger', 'description' => 'USB-C charger, 20W, Fast charging, iPhone compatible'],
            
            // Cables & Adapters
            ['name' => 'Amazon Basics HDMI Cable', 'description' => 'HDMI cable, 6ft, High speed, Gold plated'],
            ['name' => 'Cable Matters USB-C Cable', 'description' => 'USB-C cable, 6ft, USB 3.1, 10Gbps'],
            ['name' => 'Anker PowerLine III USB-C', 'description' => 'USB-C cable, 6ft, USB 3.1, 10Gbps'],
            ['name' => 'Belkin Ethernet Cable', 'description' => 'Cat6 Ethernet cable, 25ft, Snagless connectors'],
            ['name' => 'Monoprice Cat6 Cable', 'description' => 'Cat6 Ethernet cable, 50ft, Snagless connectors'],
            ['name' => 'StarTech USB-C Hub', 'description' => 'USB-C hub, 7 ports, HDMI, USB 3.0, Ethernet'],
            ['name' => 'Anker USB-C Hub', 'description' => 'USB-C hub, 7 ports, HDMI, USB 3.0, SD card reader'],
            ['name' => 'Cable Matters DisplayPort Cable', 'description' => 'DisplayPort cable, 6ft, 4K@60Hz, Gold plated'],
            
            // Miscellaneous
            ['name' => 'Blue Yeti USB Microphone', 'description' => 'USB microphone, Cardioid pattern, 48kHz/16-bit'],
            ['name' => 'Audio-Technica ATR2100x-USB', 'description' => 'USB microphone, Cardioid pattern, 48kHz/16-bit'],
            ['name' => 'Logitech C920 HD Pro Webcam', 'description' => 'HD webcam, 1080p@30fps, Autofocus, Stereo audio'],
            ['name' => 'Microsoft LifeCam Studio', 'description' => 'HD webcam, 1080p@30fps, Autofocus, Stereo audio'],
            ['name' => 'IKEA FROSTA Stool', 'description' => 'Wooden stool, Natural, 45cm height'],
            ['name' => 'IKEA LACK Side Table', 'description' => 'Side table, White, 55x55cm'],
            ['name' => 'IKEA KALLAX Shelf Unit', 'description' => 'Shelf unit, White, 77x147cm, 4 compartments'],
            ['name' => 'IKEA BILLY Bookcase', 'description' => 'Bookcase, White, 80x202cm, 6 shelves'],
        ];

        // Get a supplier to associate items with
        $supplier = DB::table('suppliers')->first();
        if (!$supplier) {
            // Create a default supplier if none exists
            $supplierId = (string) Str::uuid();
            DB::table('suppliers')->insert([
                'supplier_id' => $supplierId,
                'name' => 'Default Supplier',
                'vat_type' => 'VAT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $supplier = (object) ['supplier_id' => $supplierId];
        }

        // Create a dummy PO for standalone items
        $poId = (string) Str::uuid();
        $max = (int) DB::table('purchase_orders')
            ->selectRaw('MAX(CASE WHEN ISNUMERIC(purchase_order_no)=1 THEN CAST(purchase_order_no AS INT) ELSE 0 END) as m')
            ->value('m');
        $poNo = (string) ($max + 1);

        // Get a user
        $user = DB::table('users')->first();
        if (!$user) {
            $userId = (string) Str::uuid();
            DB::table('users')->insert([
                'user_id' => $userId,
                'name' => 'Default User',
                'email' => 'user@example.com',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $user = (object) ['user_id' => $userId];
        }

        // Create PO
        DB::table('purchase_orders')->insert([
            'purchase_order_id' => $poId,
            'requestor_id' => $user->user_id,
            'supplier_id' => $supplier->supplier_id,
            'purpose' => 'Enhanced items catalog',
            'purchase_order_no' => $poNo,
            'official_receipt_no' => null,
            'date_requested' => now()->toDateString(),
            'delivery_date' => now()->addDays(7)->toDateString(),
            'shipping_fee' => 0.00,
            'discount' => 0.00,
            'subtotal' => 0.00,
            'total' => 0.00,
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
                'remarks' => 'Enhanced items catalog',
            ]);
        }

        // Create items with random prices and quantities
        $totalCost = 0;
        foreach ($items as $item) {
            $quantity = rand(1, 10);
            $unitPrice = rand(50, 5000) + (rand(0, 99) / 100); // Random price between 50.00 and 5000.99
            $totalCost += $quantity * $unitPrice;

            DB::table('items')->insert([
                'item_id' => (string) Str::uuid(),
                'purchase_order_id' => $poId,
                'item_name' => $item['name'],
                'item_description' => $item['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_cost' => $quantity * $unitPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Update PO totals
        DB::table('purchase_orders')
            ->where('purchase_order_id', $poId)
            ->update([
                'subtotal' => $totalCost,
                'total' => $totalCost,
                'updated_at' => now(),
            ]);
    }
}



