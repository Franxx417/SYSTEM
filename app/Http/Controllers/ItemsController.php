<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemsController extends Controller
{
    /**
     * Require user to have specific role(s) or be superadmin
     */
    private function requireRole(Request $request, $roles): array
    {
        $auth = $request->session()->get('auth_user');
        if (! $auth) {
            abort(403);
        }

        // Superadmin has access to everything
        if ($auth['role'] === 'superadmin') {
            return $auth;
        }

        // Handle both single role and array of roles
        $allowedRoles = is_array($roles) ? $roles : [$roles];
        if (! in_array($auth['role'], $allowedRoles)) {
            abort(403);
        }

        return $auth;
    }

    /**
     * Display a listing of items with search and pagination
     */
    public function index(Request $request)
    {
        $auth = $this->requireRole($request, ['requestor', 'superadmin']);

        $search = $request->get('search', '');
        $perPage = 15;

        // First get the most recent item for each unique name/description
        $latestItems = DB::table('items')
            ->select('item_name', 'item_description', DB::raw('MAX(created_at) as latest_created_at'))
            ->where(function ($q) use ($search) {
                if ($search) {
                    $q->where('item_name', 'like', "%{$search}%")
                        ->orWhere('item_description', 'like', "%{$search}%");
                }
            })
            ->groupBy('item_name', 'item_description');

        $query = DB::table('items as i')
            ->joinSub($latestItems, 'latest', function ($join) {
                $join->on('i.item_name', '=', 'latest.item_name')
                    ->on('i.item_description', '=', 'latest.item_description')
                    ->on('i.created_at', '=', 'latest.latest_created_at');
            })
            ->select([
                'i.item_id',
                'i.item_name',
                'i.item_description',
                'i.quantity',
                'i.unit_price',
                'i.total_cost',
                'i.created_at',
                'purchase_orders.purchase_order_no',
                'suppliers.name as supplier_name',
            ])
            ->leftJoin('purchase_orders', 'purchase_orders.purchase_order_id', '=', 'i.purchase_order_id')
            ->leftJoin('suppliers', 'suppliers.supplier_id', '=', 'purchase_orders.supplier_id')
            ->orderBy('i.item_name', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('i.item_name', 'like', "%{$search}%")
                    ->orWhere('i.item_description', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate($perPage)->withQueryString();

        return view('items.index', compact('items', 'search'));
    }

    /**
     * Show the form for creating a new item
     */
    public function create(Request $request)
    {
        $auth = $this->requireRole($request, ['requestor', 'superadmin']);

        return view('items.create');
    }

    /**
     * Display inventory summary with grouped items
     */
    public function inventory(Request $request)
    {
        $auth = $this->requireRole($request, ['requestor', 'superadmin']);

        $search = $request->get('search', '');

        // Get total count of all items
        $totalItemsCount = DB::table('items')->count();

        // Get total inventory value
        $totalInventoryValue = DB::table('items')->sum('total_cost');

        // Group items by name and aggregate quantities and costs
        $query = DB::table('items')
            ->select([
                DB::raw('COALESCE(item_name, LEFT(item_description, 50)) as item_group'),
                'item_name',
                'item_description',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('AVG(unit_price) as avg_unit_price'),
                DB::raw('SUM(total_cost) as total_value'),
                DB::raw('COUNT(*) as entry_count'),
                DB::raw('MIN(created_at) as first_added'),
                DB::raw('MAX(updated_at) as last_updated'),
            ])
            ->groupBy('item_name', 'item_description');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('item_description', 'like', "%{$search}%");
            });
        }

        $inventoryGroups = $query->orderBy('total_quantity', 'desc')->get();

        // Calculate unique item types
        $uniqueItemTypes = $inventoryGroups->count();

        return view('items.inventory', compact(
            'inventoryGroups',
            'totalItemsCount',
            'totalInventoryValue',
            'uniqueItemTypes',
            'search'
        ));
    }

    /**
     * Store a newly created item
     */
    public function store(Request $request)
    {
        $auth = $this->requireRole($request, ['requestor', 'superadmin']);

        $request->validate([
            'item_name' => 'required|string|max:255',
            'item_description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $auth) {
                // Create a dummy PO for standalone items
                $poId = (string) Str::uuid();
                $max = (int) DB::table('purchase_orders')
                    ->selectRaw('MAX(CASE WHEN ISNUMERIC(purchase_order_no)=1 THEN CAST(purchase_order_no AS INT) ELSE 0 END) as m')
                    ->value('m');
                $poNo = (string) ($max + 1);

                // Get a default supplier or create one
                $supplier = DB::table('suppliers')->first();
                if (! $supplier) {
                    $supplierId = (string) Str::uuid();
                    DB::table('suppliers')->insert([
                        'supplier_id' => $supplierId,
                        'name' => 'Default Supplier',
                        'vat_type' => 'VAT',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $supplierId = $supplier->supplier_id;
                }

                // Create PO
                DB::table('purchase_orders')->insert([
                    'purchase_order_id' => $poId,
                    'requestor_id' => $auth['user_id'],
                    'supplier_id' => $supplierId,
                    'purpose' => 'Standalone item entry',
                    'purchase_order_no' => $poNo,
                    'official_receipt_no' => null,
                    'date_requested' => now()->toDateString(),
                    'delivery_date' => now()->addDays(7)->toDateString(),
                    'shipping_fee' => 0.00,
                    'discount' => 0.00,
                    'subtotal' => $request->quantity * $request->unit_price,
                    'total' => $request->quantity * $request->unit_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create item
                DB::table('items')->insert([
                    'item_id' => (string) Str::uuid(),
                    'purchase_order_id' => $poId,
                    'item_name' => $request->item_name,
                    'item_description' => $request->item_description,
                    'quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'total_cost' => $request->quantity * $request->unit_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create approval record
                $status = DB::table('statuses')->where('status_name', 'Pending')->first();
                if ($status) {
                    DB::table('approvals')->insert([
                        'approval_id' => (string) Str::uuid(),
                        'purchase_order_id' => $poId,
                        'prepared_by_id' => $auth['user_id'],
                        'prepared_at' => now(),
                        'status_id' => $status->status_id,
                        'remarks' => 'Standalone item entry',
                    ]);
                }
            });

            return redirect()->route('items.index')->with('success', 'Item created successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create item: '.$e->getMessage()])->withInput();
        }
    }

    /**
     * Show the form for editing an item
     */
    public function edit(Request $request, $id)
    {
        $auth = $this->requireRole($request, ['requestor', 'superadmin']);

        $item = DB::table('items')
            ->where('item_id', $id)
            ->first();

        if (! $item) {
            return redirect()->route('items.index')->withErrors(['error' => 'Item not found.']);
        }

        return view('items.edit', compact('item'));
    }

    /**
     * Update the specified item
     */
    public function update(Request $request, $id)
    {
        $auth = $this->requireRole($request, ['requestor', 'superadmin']);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated, $id) {
                DB::table('items')
                    ->where('item_id', $id)
                    ->update([
                        'item_name' => $validated['item_name'],
                        'item_description' => $validated['item_description'],
                        'quantity' => $validated['quantity'],
                        'unit_price' => $validated['unit_price'],
                        'total_cost' => $validated['quantity'] * $validated['unit_price'],
                        'updated_at' => now(),
                    ]);

                // Update PO totals
                $item = DB::table('items')->where('item_id', $id)->first();
                if ($item) {
                    $poItems = DB::table('items')
                        ->where('purchase_order_id', $item->purchase_order_id)
                        ->get();

                    $subtotal = $poItems->sum('total_cost');

                    DB::table('purchase_orders')
                        ->where('purchase_order_id', $item->purchase_order_id)
                        ->update([
                            'subtotal' => $subtotal,
                            'total' => $subtotal,
                            'updated_at' => now(),
                        ]);
                }
            });

            return redirect()->route('items.index')->with('success', 'Item updated successfully.');

        } catch (\Exception $e) {
            return redirect()->route('items.index')->withErrors(['error' => 'Failed to update item: '.$e->getMessage()]);
        }
    }

    /**
     * Remove the specified item
     */
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $item = DB::table('items')->where('item_id', $id)->first();
                if ($item) {
                    // Delete the item
                    DB::table('items')->where('item_id', $id)->delete();

                    // Check if PO has other items
                    $remainingItems = DB::table('items')
                        ->where('purchase_order_id', $item->purchase_order_id)
                        ->count();

                    // If no items left, delete the PO and its approval
                    if ($remainingItems == 0) {
                        DB::table('approvals')->where('purchase_order_id', $item->purchase_order_id)->delete();
                        DB::table('purchase_orders')->where('purchase_order_id', $item->purchase_order_id)->delete();
                    } else {
                        // Recalculate PO totals
                        $poItems = DB::table('items')
                            ->where('purchase_order_id', $item->purchase_order_id)
                            ->get();

                        $subtotal = $poItems->sum('total_cost');

                        DB::table('purchase_orders')
                            ->where('purchase_order_id', $item->purchase_order_id)
                            ->update([
                                'subtotal' => $subtotal,
                                'total' => $subtotal,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

            return redirect()->route('items.index')->with('success', 'Item deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete item: '.$e->getMessage()]);
        }
    }
}
