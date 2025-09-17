<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Http\Requests\StorePurchaseOrderRequest;

/**
 * Handles Requestor purchase order CRUD (index, create, store, show).
 * Business rules:
 * - PO number auto generated (YYYYMMDD-XXX)
 * - Item unit price: from latest history by supplier+description, or user input if new
 * - Totals: shipping 6,000; discount 13,543; VAT 12% of ex-VAT; total = ex-VAT + VAT
 */
class PurchaseOrderController extends Controller
{
    /** Ensure the current session user has the required role */
    private function requireRole(Request $request, string $role): array
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || $auth['role'] !== $role) {
            abort(403);
        }
        return $auth;
    }

    /** List current user's POs with filters */
    public function index(Request $request)
    {
        $auth = $this->requireRole($request, 'requestor');
        
        $query = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
            ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
            ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
            ->where('po.requestor_id', $auth['user_id'])
            ->whereNotNull('st.status_name') // Only show POs with status
            ->select('po.*', 's.name as supplier_name', 'st.status_name', 'st.status_id');
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('po.purchase_order_no', 'like', "%{$search}%")
                  ->orWhere('po.purpose', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('st.status_id', $request->get('status'));
        }
        
        if ($request->filled('supplier')) {
            $query->where('s.supplier_id', $request->get('supplier'));
        }
        
        $pos = $query->orderByDesc('po.created_at')->paginate(15)->withQueryString();
        
        // Get filter options
        $statuses = DB::table('statuses')->orderBy('status_name')->get();
        $suppliers = DB::table('suppliers')->orderBy('name')->get();
        
        return view('po.index', compact('pos', 'auth', 'statuses', 'suppliers'));
    }

    /** Show PO create form */
    public function create(Request $request)
    {
        $auth = $this->requireRole($request, 'requestor');
        $suppliers = DB::table('suppliers')->orderBy('name')->get();
        // Cache existing item descriptions and a sample price to populate the dropdown
        $existingNames = Cache::remember('po_create_existing_items_v1', 300, function(){
            return DB::table('items')
                ->select(
                    DB::raw('item_description as item_name'),
                    DB::raw('MIN(unit_price) as unit_price')
                )
                ->groupBy('item_description')
                ->orderBy('item_description')
                ->limit(200)
                ->get();
        });
        return view('po.create', compact('suppliers', 'auth', 'existingNames'));
    }

    /** Get all PO details with proper joins (replaces non-existent view) */
    public function getAllPO()
    {
        $poDetails = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'po.requestor_id')
            ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
            ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
            ->whereNotNull('st.status_name') // Only show POs with status
            ->select(
                'po.*',
                's.name as supplier_name',
                'u.first_name',
                'u.last_name',
                'st.status_name',
                'ap.remarks'
            )
            ->orderByDesc('po.created_at')
            ->get();
        
        return response()->json($poDetails);
    }
    /** Return next PO number (for display) */
    public function nextNumber(): \Illuminate\Http\JsonResponse
    {
        // Simple cardinal increment: next = max(existing purchase_order_no cast to int) + 1
        $max = (int) DB::table('purchase_orders')
            ->selectRaw('MAX(CASE WHEN ISNUMERIC(purchase_order_no)=1 THEN CAST(purchase_order_no AS INT) ELSE 0 END) as m')
            ->value('m');
        $next = $max + 1;
        return response()->json(['po_no' => (string) $next]);
    }

    /** Persist a new PO and its items (validates FKs and totals) */
    public function store(StorePurchaseOrderRequest $request)
    {
        $auth = $this->requireRole($request, 'requestor');
        $data = $request->validated();

        // Compute totals
        $subtotal = 0;
        
        // Handle manual supplier creation if needed
        $supplierId = $request->input('supplier_id');
        if ($supplierId === '__manual__' && $request->has('new_supplier')) {
            $newSupplier = $request->input('new_supplier');
            
            // Validate required fields
            if (empty($newSupplier['name'])) {
                return back()->withErrors(['new_supplier.name' => 'Supplier name is required'])->withInput();
            }
            
            // Create new supplier
            $supplierId = (string) Str::uuid();
            DB::table('suppliers')->insert([
                'supplier_id' => $supplierId,
                'name' => $newSupplier['name'],
                'vat_type' => $newSupplier['vat_type'] ?? 'VAT',
                'address' => $newSupplier['address'] ?? null,
                'contact_person' => $newSupplier['contact_person'] ?? null,
                'contact_number' => $newSupplier['contact_number'] ?? null,
                'tin_no' => $newSupplier['tin_no'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Update the supplier_id in the data array
            $data['supplier_id'] = $supplierId;
        } else {
            // Validate FK existence for existing supplier
            $supplierExists = DB::table('suppliers')->where('supplier_id', $supplierId)->exists();
            if (!$supplierExists) {
                return back()->withErrors(['supplier_id' => 'Selected supplier does not exist'])->withInput();
            }
        }
        $userExists = DB::table('users')->where('user_id', $auth['user_id'])->exists();
        if (!$userExists) {
            // Try to recover by email (in case session has stale GUID)
            $recoveredId = $auth['email'] ? DB::table('users')->where('email', $auth['email'])->value('user_id') : null;
            if ($recoveredId) {
                $auth['user_id'] = $recoveredId;
                $request->session()->put('auth_user', $auth);
            } else {
                return back()->withErrors(['user' => 'Your user account was not found in users table. Please re-login or contact admin.'])->withInput();
            }
        }

        // Look up item prices from supplier price list or default costs
        foreach ($data['items'] as &$it) {
            $historicalPrice = DB::table('items')
                ->join('purchase_orders','purchase_orders.purchase_order_id','=','items.purchase_order_id')
                ->where('purchase_orders.supplier_id', $data['supplier_id'])
                ->where('items.item_description', $it['item_description'])
                ->orderByDesc('items.created_at')
                ->value('unit_price');
            // Use provided unit_price if present; else fallback to historical, else 0
            if (isset($it['unit_price']) && $it['unit_price'] !== null && $it['unit_price'] !== '') {
                $it['unit_price'] = (float)$it['unit_price'];
            } elseif ($historicalPrice !== null) {
                $it['unit_price'] = (float)$historicalPrice;
            } else {
                $it['unit_price'] = 0.0;
            }
            $subtotal += $it['quantity'] * $it['unit_price'];
        }
        unset($it);

        // Auto compute shipping and discount based on sample policy
        // Shipping: flat Php 6,000; Discount: Php 13,543; VAT handled in totals
        $shipping = 0.00;
        $discount = 0.00;
        $vat = 0.00;
        $total = $subtotal;

        try {
            $created = DB::transaction(function () use ($auth, $data, $subtotal, $shipping, $discount, $total) {
            // Auto-generate PO number as simple cardinal increment
            $max = (int) DB::table('purchase_orders')
                ->selectRaw('MAX(CASE WHEN ISNUMERIC(purchase_order_no)=1 THEN CAST(purchase_order_no AS INT) ELSE 0 END) as m')
                ->value('m');
            $poNo = (string) ($max + 1);

            $poId = (string) Str::uuid();
            DB::table('purchase_orders')->insert([
                'purchase_order_id' => $poId,
                'requestor_id' => $auth['user_id'],
                'supplier_id' => $data['supplier_id'],
                'purpose' => $data['purpose'],
                'purchase_order_no' => $poNo,
                'official_receipt_no' => null,
                'date_requested' => $data['date_requested'],
                'delivery_date' => $data['delivery_date'],
                'shipping_fee' => $shipping,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'total' => $total,
            ]);
            foreach ($data['items'] as $it) {
                $row = [
                    'item_id' => (string) Str::uuid(),
                    'purchase_order_id' => $poId,
                    'item_description' => $it['item_description'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'total_cost' => $it['quantity'] * $it['unit_price'],
                ];
                // Insert optional item_name only if the column exists in DB
                if (Schema::hasColumn('items', 'item_name')) {
                    $row['item_name'] = $it['item_name'] ?? null;
                }
                DB::table('items')->insert($row);
            }
            // Mark initial status as Pending
            $statusDraft = DB::table('statuses')->where('status_name','Pending')->value('status_id');
            DB::table('approvals')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $poId,
                'prepared_by_id' => $auth['user_id'],
                'prepared_at' => now(),
                'status_id' => $statusDraft,
                'remarks' => 'Created',
            ]);
                return ['po_id' => $poId, 'po_no' => $poNo];
            });
        } catch (\Throwable $e) {
            \Log::error('PO create failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['create' => 'Failed to create Purchase Order. '.$e->getMessage()])->withInput();
        }

        return redirect()->route('po.show', $created['po_no'])->with('status','Purchase Order created');
        
        
    }

    /** Show PO details by human-readable PO number */
    public function show(Request $request, string $poNo)
    {
        $auth = $this->requireRole($request, 'requestor');
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.supplier_id','=','po.supplier_id')
            ->leftJoin('approvals as ap','ap.purchase_order_id','=','po.purchase_order_id')
            ->leftJoin('statuses as st','st.status_id','=','ap.status_id')
            ->where('po.purchase_order_no', $poNo)
            ->where('po.requestor_id', $auth['user_id'])
            ->whereNotNull('st.status_name') // Only show POs with status
            ->select('po.*','s.name as supplier_name','st.status_name','ap.remarks')
            ->first();
        if (!$po) abort(404);
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();
        return view('po.show', compact('po','items','auth'));
    }

    /** Edit PO basic fields */
    public function edit(Request $request, string $poNo)
    {
        $auth = $this->requireRole($request, 'requestor');
        $po = DB::table('purchase_orders')->where('purchase_order_no', $poNo)->where('requestor_id',$auth['user_id'])->first();
        if (!$po) abort(404);
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();
        $suppliers = DB::table('suppliers')->orderBy('name')->get();
        return view('po.edit', compact('po','items','suppliers','auth'));
    }

    /** Update PO basic fields (supplier, purpose, dates). Items not edited here. */
    public function update(Request $request, string $poNo)
    {
        $auth = $this->requireRole($request, 'requestor');
        $po = DB::table('purchase_orders')->where('purchase_order_no', $poNo)->where('requestor_id',$auth['user_id'])->first();
        if (!$po) abort(404);
        $data = $request->validate([
            'supplier_id' => ['required'],
            'purpose' => ['required','string','max:255'],
            'date_requested' => ['required','date'],
            'delivery_date' => ['required','date'],
            'items' => ['nullable','array'],
            'items.*.item_name' => ['nullable','string','max:255'],
            'items.*.item_description' => ['required_with:items','string','max:255'],
            'items.*.quantity' => ['required_with:items','integer','min:1'],
            'items.*.unit_price' => ['nullable','numeric','min:0'],
            'new_supplier' => ['nullable','array'],
            'new_supplier.name' => ['required_if:supplier_id,__manual__','string','max:255'],
            'new_supplier.vat_type' => ['nullable','string','in:VAT,Non-VAT,VAT Exempt'],
            'new_supplier.address' => ['nullable','string','max:255'],
            'new_supplier.contact_person' => ['nullable','string','max:255'],
            'new_supplier.contact_number' => ['nullable','string','max:255'],
            'new_supplier.tin_no' => ['nullable','string','max:255'],
        ]);

        // Create supplier inline if requested
        if ($data['supplier_id'] === '__manual__' && isset($data['new_supplier'])) {
            $ns = $data['new_supplier'];
            $newId = (string) Str::uuid();
            DB::table('suppliers')->insert([
                'supplier_id' => $newId,
                'name' => $ns['name'],
                'vat_type' => $ns['vat_type'] ?? 'VAT',
                'address' => $ns['address'] ?? null,
                'contact_person' => $ns['contact_person'] ?? null,
                'contact_number' => $ns['contact_number'] ?? null,
                'tin_no' => $ns['tin_no'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $data['supplier_id'] = $newId;
        }
        // Recompute totals from provided items (or keep if none supplied)
        $subtotal = 0.0;
        $items = $data['items'] ?? [];
        foreach ($items as &$it) {
            $historicalPrice = DB::table('items')
                ->join('purchase_orders','purchase_orders.purchase_order_id','=','items.purchase_order_id')
                ->where('purchase_orders.supplier_id', $data['supplier_id'])
                ->where('items.item_description', $it['item_description'])
                ->orderByDesc('items.created_at')
                ->value('unit_price');
            if (isset($it['unit_price']) && $it['unit_price'] !== null && $it['unit_price'] !== '') {
                $it['unit_price'] = (float)$it['unit_price'];
            } elseif ($historicalPrice !== null) {
                $it['unit_price'] = (float)$historicalPrice;
            } else {
                $it['unit_price'] = 0.0;
            }
            $subtotal += $it['quantity'] * $it['unit_price'];
        }
        unset($it);
        $shipping = 0.00;
        $discount = 0.00;
        $vat = 0.00;
        $total = $subtotal;

        DB::transaction(function () use ($po, $data, $items, $subtotal, $shipping, $discount, $total) {
            DB::table('purchase_orders')->where('purchase_order_id', $po->purchase_order_id)->update([
                'supplier_id' => $data['supplier_id'],
                'purpose' => $data['purpose'],
                'date_requested' => $data['date_requested'],
                'delivery_date' => $data['delivery_date'],
                'subtotal' => $subtotal,
                'shipping_fee' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'updated_at' => now(),
            ]);
            // Replace items if provided
            if (!empty($items)) {
                DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->delete();
                foreach ($items as $it) {
                    $row = [
                        'item_id' => (string) Str::uuid(),
                        'purchase_order_id' => $po->purchase_order_id,
                        'item_description' => $it['item_description'],
                        'quantity' => $it['quantity'],
                        'unit_price' => $it['unit_price'],
                        'total_cost' => $it['quantity'] * $it['unit_price'],
                    ];
                    if (Schema::hasColumn('items', 'item_name')) {
                        $row['item_name'] = $it['item_name'] ?? null;
                    }
                    DB::table('items')->insert($row);
                }
            }
        });
        return redirect()->route('po.show', $poNo)->with('status','Purchase Order updated');
    }

    /** Print-friendly PO view matching sample document */
    public function print(Request $request, string $poNo)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth) abort(401);
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.supplier_id','=','po.supplier_id')
            ->leftJoin('approvals as ap','ap.purchase_order_id','=','po.purchase_order_id')
            ->leftJoin('statuses as st','st.status_id','=','ap.status_id')
            ->where('po.purchase_order_no', $poNo)
            ->select(
                'po.*',
                's.name as supplier_name',
                's.tin_no',
                's.vat_type',
                's.contact_person',
                's.contact_number',
                's.address as supplier_address',
                'st.status_name',
                'ap.remarks'
            )
            ->first();
        if (!$po) abort(404);
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();
        return view('po.print', compact('po','items','auth'));
    }

    /** Lightweight PO JSON for modals (any logged-in role) */
    public function showJson(Request $request, string $poNo)
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth) abort(401);
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s','s.supplier_id','=','po.supplier_id')
            ->leftJoin('approvals as ap','ap.purchase_order_id','=','po.purchase_order_id')
            ->leftJoin('statuses as st','st.status_id','=','ap.status_id')
            ->where('po.purchase_order_no', $poNo)
            ->select('po.*','s.name as supplier_name','st.status_name','ap.remarks')
            ->first();
        if (!$po) return response()->json(['error' => 'Not found'], 404);
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();
        return response()->json(['po' => $po, 'items' => $items]);
    }

    /**
     * Update PO status
     */
    public function updateStatus(Request $request, $poNo)
    {
        $auth = $this->requireRole($request, 'requestor');
        
        $request->validate([
            'status_id' => 'required|exists:statuses,status_id',
            'remarks' => 'nullable|string|max:255'
        ]);

        try {
            $po = DB::table('purchase_orders')->where('purchase_order_no', $poNo)->first();
            if (!$po) {
                return back()->withErrors(['error' => 'Purchase Order not found.']);
            }

            DB::table('approvals')
                ->where('purchase_order_id', $po->purchase_order_id)
                ->update([
                    'status_id' => $request->status_id,
                    'remarks' => $request->remarks ?: 'Status updated',
                    'updated_at' => now(),
                ]);

            return back()->with('success', 'PO status updated successfully.');
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }
}



