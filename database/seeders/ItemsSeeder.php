<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing POs to associate items with
        $pos = DB::table('purchase_orders')->get();
        if ($pos->isEmpty()) {
            return;
        }

        $items = [
            // Computer Hardware
            ['name' => 'Dell Optiplex 7090', 'description' => 'Intel Core i7-11700, 16GB RAM, 512GB SSD, Windows 11 Pro', 'price_range' => [2000, 3000]],
            ['name' => 'HP EliteBook 850 G8', 'description' => 'Intel Core i5-1135G7, 8GB RAM, 256GB SSD, 15.6" Display', 'price_range' => [1500, 2500]],
            ['name' => 'Lenovo ThinkPad X1 Carbon', 'description' => 'Intel Core i7-1165G7, 16GB RAM, 1TB SSD, 14" 4K Display', 'price_range' => [2500, 3500]],
            ['name' => 'MacBook Pro 16" M1 Pro', 'description' => 'Apple M1 Pro chip, 16GB RAM, 512GB SSD, 16" Liquid Retina XDR', 'price_range' => [3000, 4000]],
            ['name' => 'Surface Laptop 4', 'description' => 'Intel Core i7-1185G7, 16GB RAM, 512GB SSD, 13.5" Touch Display', 'price_range' => [1800, 2800]],
            ['name' => 'ASUS ROG Strix G15', 'description' => 'AMD Ryzen 7 5800H, 16GB RAM, 1TB SSD, RTX 3060, 15.6" 144Hz', 'price_range' => [2200, 3200]],
            ['name' => 'Acer Aspire 5', 'description' => 'Intel Core i5-1135G7, 8GB RAM, 256GB SSD, 15.6" FHD Display', 'price_range' => [800, 1200]],
            ['name' => 'MSI Creator 15', 'description' => 'Intel Core i7-11800H, 32GB RAM, 1TB SSD, RTX 3070, 15.6" 4K', 'price_range' => [2800, 3800]],

            // Monitors & Displays
            ['name' => 'Dell UltraSharp U2720Q', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, USB-C, IPS Panel', 'price_range' => [400, 600]],
            ['name' => 'LG 27UN850-W', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, USB-C, HDR10, IPS Panel', 'price_range' => [350, 550]],
            ['name' => 'Samsung Odyssey G7', 'description' => '32" QHD Gaming Monitor, 2560x1440, 240Hz, 1ms, Curved VA Panel', 'price_range' => [500, 700]],
            ['name' => 'ASUS ProArt PA248QV', 'description' => '24" Professional Monitor, 1920x1200, 60Hz, 99% sRGB, IPS Panel', 'price_range' => [300, 500]],
            ['name' => 'BenQ PD2700U', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, 99% sRGB, IPS Panel', 'price_range' => [450, 650]],
            ['name' => 'HP EliteDisplay E243d', 'description' => '23.8" FHD Monitor, 1920x1080, 60Hz, USB-C, IPS Panel', 'price_range' => [200, 350]],
            ['name' => 'ViewSonic VP2768-4K', 'description' => '27" 4K UHD Monitor, 3840x2160, 60Hz, 99% sRGB, IPS Panel', 'price_range' => [400, 600]],
            ['name' => 'AOC 24G2U', 'description' => '24" FHD Gaming Monitor, 1920x1080, 144Hz, 1ms, IPS Panel', 'price_range' => [250, 400]],

            // Peripherals
            ['name' => 'Logitech MX Master 3', 'description' => 'Wireless Mouse, 4000 DPI, 70-day battery, USB-C charging', 'price_range' => [80, 120]],
            ['name' => 'Microsoft Surface Mouse', 'description' => 'Bluetooth Mouse, 1000 DPI, 12-month battery life', 'price_range' => [50, 80]],
            ['name' => 'Razer DeathAdder V2', 'description' => 'Gaming Mouse, 20000 DPI, 8 programmable buttons, RGB lighting', 'price_range' => [60, 100]],
            ['name' => 'Corsair K95 RGB Platinum', 'description' => 'Mechanical Gaming Keyboard, Cherry MX Speed switches, RGB backlighting', 'price_range' => [150, 250]],
            ['name' => 'Logitech MX Keys', 'description' => 'Wireless Keyboard, backlit keys, 5-month battery life, USB-C charging', 'price_range' => [80, 120]],
            ['name' => 'Keychron K2', 'description' => 'Wireless Mechanical Keyboard, Gateron switches, RGB backlighting', 'price_range' => [70, 110]],
            ['name' => 'SteelSeries Arctis 7', 'description' => 'Wireless Gaming Headset, 2.4GHz, 24-hour battery, 7.1 surround sound', 'price_range' => [100, 200]],
            ['name' => 'Sony WH-1000XM4', 'description' => 'Wireless Noise Canceling Headphones, 30-hour battery, LDAC codec', 'price_range' => [200, 350]],

            // Office Furniture
            ['name' => 'Herman Miller Aeron Chair', 'description' => 'Ergonomic Office Chair, Size B, PostureFit SL, Adjustable arms', 'price_range' => [800, 1200]],
            ['name' => 'Steelcase Leap V2', 'description' => 'Ergonomic Office Chair, LiveBack technology, Adjustable lumbar support', 'price_range' => [600, 900]],
            ['name' => 'IKEA Markus Chair', 'description' => 'Office Chair, Black, Adjustable height, 10-year warranty', 'price_range' => [100, 200]],
            ['name' => 'HON Ignition 2.0', 'description' => 'Ergonomic Office Chair, Adjustable lumbar support, Breathable mesh', 'price_range' => [200, 400]],
            ['name' => 'Knoll Generation Chair', 'description' => 'Ergonomic Office Chair, Dynamic suspension, Adjustable arms', 'price_range' => [500, 800]],
            ['name' => 'Office Depot ProDesk', 'description' => '60" x 30" Office Desk, White, Adjustable height, Cable management', 'price_range' => [300, 500]],
            ['name' => 'IKEA Bekant Desk', 'description' => 'Office Desk, 160x80cm, White, Adjustable feet, Cable management', 'price_range' => [150, 250]],
            ['name' => 'UPLIFT V2 Standing Desk', 'description' => 'Electric Standing Desk, 60" x 30", Dual motor, Programmable memory', 'price_range' => [600, 900]],

            // Networking Equipment
            ['name' => 'Cisco Catalyst 2960-X', 'description' => '24-Port Gigabit Ethernet Switch, Layer 3, PoE+ support', 'price_range' => [500, 800]],
            ['name' => 'Ubiquiti UniFi Dream Machine', 'description' => 'All-in-one Security Gateway, 4-port switch, WiFi 6, 1.3Gbps throughput', 'price_range' => [300, 500]],
            ['name' => 'Netgear ProSAFE GS728TP', 'description' => '28-Port Gigabit Smart Switch, 24 PoE+ ports, 4 SFP ports', 'price_range' => [400, 700]],
            ['name' => 'Aruba Instant On 1930', 'description' => '24-Port Gigabit Switch, Cloud-managed, PoE+ support', 'price_range' => [350, 600]],
            ['name' => 'TP-Link Omada EAP660 HD', 'description' => 'WiFi 6 Access Point, 2.4GHz + 5GHz, 1.8Gbps, Cloud management', 'price_range' => [200, 350]],
            ['name' => 'Ruckus R750 Access Point', 'description' => 'WiFi 6 Access Point, 2.4GHz + 5GHz, 4x4 MU-MIMO, Cloud management', 'price_range' => [300, 500]],
            ['name' => 'Fortinet FortiGate 60F', 'description' => 'Next-Generation Firewall, 1.5Gbps throughput, 8x GE RJ45 ports', 'price_range' => [400, 700]],
            ['name' => 'Palo Alto PA-220', 'description' => 'Next-Generation Firewall, 500Mbps throughput, 8x GE ports', 'price_range' => [500, 900]],

            // Printers & Scanners
            ['name' => 'HP LaserJet Pro M404dn', 'description' => 'Monochrome Laser Printer, 38ppm, Duplex printing, Network ready', 'price_range' => [200, 400]],
            ['name' => 'Canon imageCLASS MF445dw', 'description' => 'All-in-one Laser Printer, 28ppm, Duplex, WiFi, Mobile printing', 'price_range' => [150, 300]],
            ['name' => 'Brother MFC-L2750DW', 'description' => 'All-in-one Laser Printer, 32ppm, Duplex, WiFi, Mobile printing', 'price_range' => [180, 320]],
            ['name' => 'Epson WorkForce Pro WF-4830', 'description' => 'All-in-one Inkjet Printer, 20ppm, Duplex, WiFi, Mobile printing', 'price_range' => [120, 250]],
            ['name' => 'Xerox WorkCentre 6515', 'description' => 'All-in-one Laser Printer, 25ppm, Duplex, WiFi, Mobile printing', 'price_range' => [200, 400]],
            ['name' => 'HP ScanJet Pro 2500 f1', 'description' => 'Flatbed Scanner, 25ppm, Duplex, USB 3.0, TWAIN compatible', 'price_range' => [300, 500]],
            ['name' => 'Canon CanoScan LiDE 400', 'description' => 'Flatbed Scanner, 4800x4800 DPI, USB 2.0, TWAIN compatible', 'price_range' => [80, 150]],
            ['name' => 'Epson Perfection V600', 'description' => 'Flatbed Scanner, 6400x9600 DPI, USB 2.0, Film scanning', 'price_range' => [200, 350]],

            // Storage & Backup
            ['name' => 'Western Digital My Book 4TB', 'description' => 'External Hard Drive, USB 3.0, 4TB capacity, Automatic backup', 'price_range' => [80, 120]],
            ['name' => 'Seagate Backup Plus Hub 6TB', 'description' => 'External Hard Drive, USB 3.0, 6TB capacity, USB hub', 'price_range' => [100, 150]],
            ['name' => 'Samsung T7 Portable SSD 1TB', 'description' => 'Portable SSD, USB 3.2 Gen 2, 1TB capacity, 1050MB/s read', 'price_range' => [100, 150]],
            ['name' => 'SanDisk Extreme Pro 2TB', 'description' => 'Portable SSD, USB 3.2 Gen 2, 2TB capacity, 2000MB/s read', 'price_range' => [200, 300]],
            ['name' => 'Synology DS220+ NAS', 'description' => '2-Bay NAS, Intel Celeron J4025, 2GB RAM, 2x Gigabit Ethernet', 'price_range' => [300, 500]],
            ['name' => 'QNAP TS-251D NAS', 'description' => '2-Bay NAS, Intel Celeron J4005, 4GB RAM, 2x Gigabit Ethernet', 'price_range' => [350, 550]],
            ['name' => 'WD My Cloud EX2 Ultra', 'description' => '2-Bay NAS, Marvell ARMADA 385, 2x Gigabit Ethernet', 'price_range' => [200, 400]],
            ['name' => 'Buffalo LinkStation 220', 'description' => '2-Bay NAS, Marvell ARMADA 385, 2x Gigabit Ethernet', 'price_range' => [150, 300]],

            // Software & Licenses
            ['name' => 'Microsoft Office 365 Business', 'description' => 'Office 365 Business Premium, 1 user, 1TB OneDrive, Teams', 'price_range' => [150, 250]],
            ['name' => 'Adobe Creative Cloud', 'description' => 'Creative Cloud All Apps, 1 user, 100GB cloud storage', 'price_range' => [300, 500]],
            ['name' => 'Windows 11 Pro License', 'description' => 'Windows 11 Pro, Volume License, 1 PC', 'price_range' => [100, 200]],
            ['name' => 'VMware vSphere Standard', 'description' => 'vSphere 7.0 Standard, 1 CPU license, 1 year support', 'price_range' => [500, 800]],
            ['name' => 'Autodesk AutoCAD 2024', 'description' => 'AutoCAD 2024, 1 user, 1 year subscription', 'price_range' => [800, 1200]],
            ['name' => 'SolidWorks Professional', 'description' => 'SolidWorks 2024 Professional, 1 user, 1 year subscription', 'price_range' => [2000, 3000]],
            ['name' => 'Tableau Creator', 'description' => 'Tableau Creator, 1 user, 1 year subscription', 'price_range' => [600, 1000]],
            ['name' => 'Power BI Pro', 'description' => 'Power BI Pro, 1 user, 1 year subscription', 'price_range' => [100, 200]],

            // Office Supplies
            ['name' => 'Pilot G2 Retractable Pen', 'description' => 'Gel ink pen, 0.7mm, Blue ink, 12-pack', 'price_range' => [10, 20]],
            ['name' => 'Uni-ball Signo 207', 'description' => 'Gel ink pen, 0.7mm, Black ink, 12-pack', 'price_range' => [12, 22]],
            ['name' => 'Sharpie Permanent Marker', 'description' => 'Fine point permanent marker, Black ink, 12-pack', 'price_range' => [15, 25]],
            ['name' => 'Staedtler Triplus Fineliner', 'description' => 'Fine liner pen set, 20 colors, 0.3mm tip', 'price_range' => [20, 35]],
            ['name' => 'Moleskine Classic Notebook', 'description' => 'Hard cover notebook, Large, Ruled, 240 pages', 'price_range' => [15, 25]],
            ['name' => 'Leuchtturm1917 Notebook', 'description' => 'Hard cover notebook, A5, Dotted, 249 pages', 'price_range' => [18, 28]],
            ['name' => 'Post-it Notes 3x3', 'description' => 'Self-stick notes, 3x3 inches, Yellow, 12 pads', 'price_range' => [5, 15]],
            ['name' => 'Scotch Magic Tape', 'description' => 'Invisible tape, 3/4 inch x 450 inches, 6 rolls', 'price_range' => [8, 18]],

            // Cleaning & Maintenance
            ['name' => 'Lysol Disinfecting Wipes', 'description' => 'Disinfecting wipes, 80 count, Lemon scent', 'price_range' => [5, 12]],
            ['name' => 'Clorox Disinfecting Spray', 'description' => 'Disinfecting spray, 32 fl oz, Bleach-free', 'price_range' => [3, 8]],
            ['name' => 'Swiffer Duster Refills', 'description' => 'Dusting refills, 8 count, Electrostatic', 'price_range' => [8, 15]],
            ['name' => 'Pledge Multi-Surface Cleaner', 'description' => 'Multi-surface cleaner, 25 fl oz, Lemon scent', 'price_range' => [4, 10]],
            ['name' => 'Windex Glass Cleaner', 'description' => 'Glass cleaner, 32 fl oz, Ammonia-free', 'price_range' => [3, 8]],
            ['name' => 'Bona Hardwood Floor Cleaner', 'description' => 'Hardwood floor cleaner, 32 fl oz, Ready-to-use', 'price_range' => [6, 12]],
            ['name' => 'O-Cedar Microfiber Mop', 'description' => 'Microfiber mop, Washable pad, Adjustable handle', 'price_range' => [15, 30]],
            ['name' => 'Rubbermaid Commercial Trash Can', 'description' => 'Trash can, 32 gallon, Round, Black', 'price_range' => [25, 50]],

            // Security & Safety
            ['name' => 'Hikvision DS-2CD2143G0-I', 'description' => '4MP IP Camera, 2.8mm lens, Night vision, PoE', 'price_range' => [80, 150]],
            ['name' => 'Axis M3046-V', 'description' => '4MP IP Camera, 2.8mm lens, Night vision, PoE', 'price_range' => [100, 180]],
            ['name' => 'Dahua N45CG52', 'description' => '5MP IP Camera, 2.8mm lens, Night vision, PoE', 'price_range' => [90, 160]],
            ['name' => 'Bosch FLEXIDOME IP 4000', 'description' => '4MP IP Camera, 2.8mm lens, Night vision, PoE', 'price_range' => [120, 200]],
            ['name' => 'First Alert Smoke Detector', 'description' => 'Smoke detector, Battery operated, 10-year battery', 'price_range' => [15, 30]],
            ['name' => 'Kidde Fire Extinguisher', 'description' => 'Fire extinguisher, 2.5 lb, ABC dry chemical', 'price_range' => [25, 50]],
            ['name' => 'Master Lock Combination Lock', 'description' => 'Combination lock, 4-digit, Brass body', 'price_range' => [8, 18]],
            ['name' => 'Kensington Lock', 'description' => 'Laptop security lock, 6ft cable, T-bar lock', 'price_range' => [15, 30]],

            // Electrical & Power
            ['name' => 'APC Back-UPS Pro 1500VA', 'description' => 'UPS, 1500VA/900W, 8 outlets, USB charging', 'price_range' => [150, 250]],
            ['name' => 'CyberPower CP1500AVRLCD', 'description' => 'UPS, 1500VA/900W, 8 outlets, LCD display', 'price_range' => [120, 200]],
            ['name' => 'Tripp Lite SMART1500LCD', 'description' => 'UPS, 1500VA/900W, 8 outlets, LCD display', 'price_range' => [130, 220]],
            ['name' => 'Belkin Surge Protector', 'description' => 'Surge protector, 8 outlets, 2160 joules, 6ft cord', 'price_range' => [20, 40]],
            ['name' => 'Monster Power Strip', 'description' => 'Power strip, 8 outlets, 6ft cord, Surge protection', 'price_range' => [25, 45]],
            ['name' => 'Anker PowerPort 6', 'description' => 'USB charging station, 6 ports, 60W total output', 'price_range' => [30, 50]],
            ['name' => 'RAVPower 65W GaN Charger', 'description' => 'USB-C charger, 65W, GaN technology, Compact', 'price_range' => [40, 70]],
            ['name' => 'Apple 20W USB-C Charger', 'description' => 'USB-C charger, 20W, Fast charging, iPhone compatible', 'price_range' => [15, 30]],

            // Cables & Adapters
            ['name' => 'Amazon Basics HDMI Cable', 'description' => 'HDMI cable, 6ft, High speed, Gold plated', 'price_range' => [8, 18]],
            ['name' => 'Cable Matters USB-C Cable', 'description' => 'USB-C cable, 6ft, USB 3.1, 10Gbps', 'price_range' => [12, 25]],
            ['name' => 'Anker PowerLine III USB-C', 'description' => 'USB-C cable, 6ft, USB 3.1, 10Gbps', 'price_range' => [15, 30]],
            ['name' => 'Belkin Ethernet Cable', 'description' => 'Cat6 Ethernet cable, 25ft, Snagless connectors', 'price_range' => [10, 20]],
            ['name' => 'Monoprice Cat6 Cable', 'description' => 'Cat6 Ethernet cable, 50ft, Snagless connectors', 'price_range' => [15, 30]],
            ['name' => 'StarTech USB-C Hub', 'description' => 'USB-C hub, 7 ports, HDMI, USB 3.0, Ethernet', 'price_range' => [40, 80]],
            ['name' => 'Anker USB-C Hub', 'description' => 'USB-C hub, 7 ports, HDMI, USB 3.0, SD card reader', 'price_range' => [50, 90]],
            ['name' => 'Cable Matters DisplayPort Cable', 'description' => 'DisplayPort cable, 6ft, 4K@60Hz, Gold plated', 'price_range' => [10, 25]],

            // Miscellaneous
            ['name' => 'Blue Yeti USB Microphone', 'description' => 'USB microphone, Cardioid pattern, 48kHz/16-bit', 'price_range' => [80, 150]],
            ['name' => 'Audio-Technica ATR2100x-USB', 'description' => 'USB microphone, Cardioid pattern, 48kHz/16-bit', 'price_range' => [60, 120]],
            ['name' => 'Logitech C920 HD Pro Webcam', 'description' => 'HD webcam, 1080p@30fps, Autofocus, Stereo audio', 'price_range' => [50, 100]],
            ['name' => 'Microsoft LifeCam Studio', 'description' => 'HD webcam, 1080p@30fps, Autofocus, Stereo audio', 'price_range' => [40, 80]],
            ['name' => 'IKEA FROSTA Stool', 'description' => 'Wooden stool, Natural, 45cm height', 'price_range' => [20, 40]],
            ['name' => 'IKEA LACK Side Table', 'description' => 'Side table, White, 55x55cm', 'price_range' => [15, 30]],
            ['name' => 'IKEA KALLAX Shelf Unit', 'description' => 'Shelf unit, White, 77x147cm, 4 compartments', 'price_range' => [40, 80]],
            ['name' => 'IKEA BILLY Bookcase', 'description' => 'Bookcase, White, 80x202cm, 6 shelves', 'price_range' => [50, 100]],
        ];

        // Create items for each PO
        foreach ($pos as $po) {
            $itemCount = rand(2, 8); // 2-8 items per PO
            $selectedItems = array_rand($items, min($itemCount, count($items)));
            if (! is_array($selectedItems)) {
                $selectedItems = [$selectedItems];
            }

            foreach ($selectedItems as $itemIndex) {
                $item = $items[$itemIndex];
                $quantity = rand(1, 10);
                $unitPrice = rand($item['price_range'][0], $item['price_range'][1]) + (rand(0, 99) / 100);
                $totalCost = $quantity * $unitPrice;

                DB::table('items')->insert([
                    'item_id' => (string) \Illuminate\Support\Str::uuid(),
                    'purchase_order_id' => $po->purchase_order_id,
                    'item_name' => $item['name'],
                    'item_description' => $item['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_cost' => $totalCost,
                    'created_at' => $po->created_at,
                    'updated_at' => $po->updated_at,
                ]);
            }
        }
    }
}
