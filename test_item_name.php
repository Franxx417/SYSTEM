<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking items table for item_name column:\n";
$items = DB::table('items')->select('item_name', 'item_description')->limit(3)->get();

foreach($items as $item) {
    echo "Name: " . ($item->item_name ?? 'NULL') . " | Desc: " . $item->item_description . "\n";
}

echo "\nTotal items: " . DB::table('items')->count() . "\n";



