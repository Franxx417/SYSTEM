<?php
/**
 * ItemController
 *
 * Lightweight APIs to support UI interactions such as item
 * description suggestions and latest unit price lookup.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function latestPrice(Request $request)
    {
        $desc = (string) $request->query('description', '');
        if ($desc === '') {
            return response()->json(['price' => null]);
        }
        
        // Get the latest price for this item from ANY source (seeded or user-created)
        // Trim and compare case-insensitively to catch small input variations
        $price = DB::table('items')
            ->whereRaw('LOWER(item_description) = LOWER(?)', [trim($desc)])
            ->orderByDesc('created_at')
            ->value('unit_price');
            
        return response()->json(['price' => $price]);
    }
    public function suggestions(Request $request)
    {
        $supplierId = $request->query('supplier_id');
        $q = (string) $request->query('q', '');

        $query = DB::table('items as i')
            ->join('purchase_orders as po', 'po.purchase_order_id', '=', 'i.purchase_order_id')
            ->when($supplierId, function ($qb) use ($supplierId) {
                $qb->where('po.supplier_id', $supplierId);
            })
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where('i.item_description', 'like', "%$q%");
            })
            ->select('i.item_description')
            ->selectRaw('MAX(i.unit_price) as latest_price')
            ->groupBy('i.item_description')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(20);

        $items = $query->get();

        return response()->json($items);
    }
}


