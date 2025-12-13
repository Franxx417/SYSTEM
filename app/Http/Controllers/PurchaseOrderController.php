<?php

namespace App\Http\Controllers;

use App\Domain\PurchaseOrders\CreatePurchaseOrderAction;
use App\Http\Requests\StorePurchaseOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Handles Requestor purchase order CRUD (index, create, store, show).
 * Business rules:
 * - PO number auto generated (YYYYMMDD-XXX)
 * - Item unit price: from latest history by supplier+description, or user input if new
 * - Totals: shipping 6,000; discount 13,543; VAT 12% of ex-VAT; total = ex-VAT + VAT
 */
class PurchaseOrderController extends Controller
{
    /** Ensure the current session user has the required role - SUPERADMIN HAS UNRESTRICTED ACCESS */
    private function requireRole(Request $request, string $role): array
    {
        $auth = $request->session()->get('auth_user');
        if (! $auth) {
            abort(403);
        }

        // SUPERADMIN HAS UNRESTRICTED ACCESS TO EVERYTHING
        if ($auth['role'] === 'superadmin') {
            return $auth;
        }

        // For non-superadmin users, check specific role
        if ($auth['role'] !== $role) {
            abort(403);
        }

        return $auth;
    }

    private function canWritePo(array $auth, object $po): bool
    {
        if (($auth['role'] ?? null) === 'superadmin') {
            return true;
        }

        return isset($auth['user_id']) && isset($po->requestor_id) && (string) $po->requestor_id === (string) $auth['user_id'];
    }

    /** List current user's POs with filters */
    public function index(Request $request)
    {
        $auth = $this->requireRole($request, 'requestor');

        $pendingStatusId = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');

        $query = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
            ->leftJoin('approvals as ap', function ($join) {
                $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
            })
            ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
            ->select('po.*', 's.name as supplier_name')
            ->selectRaw('COALESCE(st.status_name, ?) as status_name', ['Pending'])
            ->selectRaw('COALESCE(st.status_id, ?) as status_id', [$pendingStatusId]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('po.purchase_order_no', 'like', "%{$search}%")
                    ->orWhere('po.purpose', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($pendingStatusId) {
                $query->whereRaw('COALESCE(st.status_id, ?) = ?', [$pendingStatusId, $status]);
            } else {
                $query->where('st.status_id', $status);
            }
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
        $existingNames = Cache::remember('po_create_existing_items_v1', 300, function () {
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
        $pendingStatusId = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');

        $poDetails = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
            ->leftJoin('users as u', 'u.user_id', '=', 'po.requestor_id')
            ->leftJoin('approvals as ap', function ($join) {
                $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                    ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
            })
            ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
            ->select('po.*', 's.name as supplier_name', 'u.first_name', 'u.last_name', 'ap.remarks')
            ->selectRaw('COALESCE(st.status_name, ?) as status_name', ['Pending'])
            ->selectRaw('COALESCE(st.status_id, ?) as status_id', [$pendingStatusId])
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
    public function store(StorePurchaseOrderRequest $request, CreatePurchaseOrderAction $createPurchaseOrder)
    {
        $auth = $this->requireRole($request, 'requestor');
        $data = $request->validated();
        try {
            $created = $createPurchaseOrder->handle($auth, $data, $request->session());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            \Log::error('PO create failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->withErrors(['create' => 'Failed to create Purchase Order. '.$e->getMessage()])->withInput();
        }

        return redirect()
            ->route('po.index')
            ->with('status', 'Purchase Order created')
            ->with('open_po', $created['po_no']);

    }

    /** Show PO details by human-readable PO number */
    public function show(Request $request, string $poNo)
    {
        $auth = $this->requireRole($request, 'requestor');
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
            ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
            ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
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

        if (! $po) {
            abort(404);
        }

        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();

        // Reuse existing print view (no new blade needed)
        $brandingService = app(\App\Services\BrandingService::class);
        $branding = $brandingService->getPrintData();
        $companyLogo = $branding['company_logo'];
        $companyName = $branding['company_name'];

        return view('po.print', compact('po', 'items', 'auth', 'companyLogo', 'companyName', 'branding'));
    }

    /** Edit PO basic fields */
    public function edit(Request $request, string $poNo)
    {
        $auth = $this->requireRole($request, 'requestor');
        $po = DB::table('purchase_orders')->where('purchase_order_no', $poNo)->first();
        if (! $po) {
            abort(404);
        }
        if (! $this->canWritePo($auth, $po)) {
            abort(403);
        }
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();
        $suppliers = DB::table('suppliers')->orderBy('name')->get();

        return view('po.edit', compact('po', 'items', 'suppliers', 'auth'));
    }

    /** Update PO basic fields (supplier, purpose, dates). Items not edited here. */
    public function update(Request $request, string $poNo)
    {
        $auth = $this->requireRole($request, 'requestor');
        $po = DB::table('purchase_orders')->where('purchase_order_no', $poNo)->first();
        if (! $po) {
            abort(404);
        }
        if (! $this->canWritePo($auth, $po)) {
            abort(403);
        }
        $data = $request->validate([
            'supplier_id' => ['required'],
            'purpose' => ['required', 'string', 'max:255'],
            'date_requested' => ['required', 'date'],
            'delivery_date' => ['required', 'date'],
            'items' => ['nullable', 'array'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.item_description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'new_supplier' => ['nullable', 'array'],
            'new_supplier.name' => ['required_if:supplier_id,__manual__', 'string', 'max:255'],
            'new_supplier.vat_type' => ['nullable', 'string', 'in:VAT,Non-VAT,VAT Exempt'],
            'new_supplier.address' => ['nullable', 'string', 'max:255'],
            'new_supplier.contact_person' => ['nullable', 'string', 'max:255'],
            'new_supplier.contact_number' => ['nullable', 'string', 'max:255'],
            'new_supplier.tin_no' => ['nullable', 'string', 'max:255'],
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
                ->join('purchase_orders', 'purchase_orders.purchase_order_id', '=', 'items.purchase_order_id')
                ->where('purchase_orders.supplier_id', $data['supplier_id'])
                ->where('items.item_description', $it['item_description'])
                ->orderByDesc('items.created_at')
                ->value('unit_price');
            if (isset($it['unit_price']) && $it['unit_price'] !== null && $it['unit_price'] !== '') {
                $it['unit_price'] = (float) $it['unit_price'];
            } elseif ($historicalPrice !== null) {
                $it['unit_price'] = (float) $historicalPrice;
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
            if (! empty($items)) {
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

        return redirect()->route('po.show', $poNo)->with('status', 'Purchase Order updated');
    }

    public function destroy(Request $request, string $poNo)
    {
        $wantsJson = $request->wantsJson() || $request->ajax() || $request->expectsJson();
        $auth = $this->requireRole($request, 'requestor');

        $po = DB::table('purchase_orders')->where('purchase_order_no', $poNo)->first();
        if (! $po) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'error' => 'Not found'], 404);
            }
            abort(404);
        }

        if (! $this->canWritePo($auth, $po)) {
            if ($wantsJson) {
                return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
            }
            abort(403);
        }

        DB::transaction(function () use ($po) {
            DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->delete();
            DB::table('approvals')->where('purchase_order_id', $po->purchase_order_id)->delete();
            DB::table('purchase_orders')->where('purchase_order_id', $po->purchase_order_id)->delete();
        });

        if ($wantsJson) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('po.index')->with('status', 'Purchase Order deleted');
    }

    /** Print-friendly PO view matching sample document */
    public function print(Request $request, string $poNo)
    {
        $auth = $request->session()->get('auth_user');
        if (! $auth) {
            abort(401);
        }
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
            ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
            ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
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
        if (! $po) {
            abort(404);
        }
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();

        // Get branding data for print view
        $brandingService = app(\App\Services\BrandingService::class);
        $branding = $brandingService->getPrintData();
        $companyLogo = $branding['company_logo'];
        $companyName = $branding['company_name'];

        return view('po.print', compact('po', 'items', 'auth', 'companyLogo', 'companyName', 'branding'));
    }

    /** Lightweight PO JSON for modals (any logged-in role) */
    public function showJson(Request $request, string $poNo)
    {
        $auth = $request->session()->get('auth_user');
        if (! $auth) {
            abort(401);
        }
        $po = DB::table('purchase_orders as po')
            ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
            ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
            ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
            ->where('po.purchase_order_no', $poNo)
            ->select('po.*', 's.name as supplier_name', 'st.status_name', 'ap.remarks')
            ->first();
        if (! $po) {
            return response()->json(['error' => 'Not found'], 404);
        }
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();

        return response()->json(['po' => $po, 'items' => $items]);
    }

    /**
     * Update PO status with proper synchronization and error handling
     */
    public function updateStatus(Request $request, $poNo)
    {
        // Detect if AJAX request
        $wantsJson = $request->wantsJson() || $request->ajax() || $request->expectsJson();

        try {
            // Check authentication
            $auth = $this->requireRole($request, 'requestor');

            // Validate input
            $validator = \Validator::make($request->all(), [
                'status_id' => 'required|uuid|exists:statuses,status_id',
                'remarks' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                if ($wantsJson) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                return back()->withErrors($validator)->withInput();
            }

            // Start transaction for data consistency
            DB::beginTransaction();

            try {
                // Find Purchase Order
                $po = DB::table('purchase_orders')
                    ->where('purchase_order_no', $poNo)
                    ->first();

                if (! $po) {
                    DB::rollBack();
                    $error = 'Purchase Order not found: '.$poNo;
                    \Log::warning('[PO Status Update] '.$error);

                    if ($wantsJson) {
                        return response()->json([
                            'success' => false,
                            'error' => $error,
                        ], 404);
                    }

                    return back()->withErrors(['error' => $error]);
                }

                if (! $this->canWritePo($auth, $po)) {
                    DB::rollBack();
                    $error = 'Forbidden';
                    if ($wantsJson) {
                        return response()->json([
                            'success' => false,
                            'error' => $error,
                        ], 403);
                    }

                    return back()->withErrors(['error' => $error]);
                }

                // Get status information
                $status = DB::table('statuses')
                    ->where('status_id', $request->status_id)
                    ->first();

                if (! $status) {
                    DB::rollBack();
                    $error = 'Status not found';
                    \Log::warning('[PO Status Update] '.$error, ['status_id' => $request->status_id]);

                    if ($wantsJson) {
                        return response()->json([
                            'success' => false,
                            'error' => $error,
                        ], 404);
                    }

                    return back()->withErrors(['error' => $error]);
                }

                // Update approvals table
                $affected = DB::table('approvals')
                    ->where('purchase_order_id', $po->purchase_order_id)
                    ->update([
                        'status_id' => $request->status_id,
                        'remarks' => trim($request->remarks ?: 'Status updated via status change modal'),
                    ]);

                // Verify update succeeded
                if ($affected === 0) {
                    // Check if approval record exists
                    $approvalExists = DB::table('approvals')
                        ->where('purchase_order_id', $po->purchase_order_id)
                        ->exists();

                    if (! $approvalExists) {
                        // Create approval record if it doesn't exist
                        DB::table('approvals')->insert([
                            'approval_id' => (string) \Str::uuid(),
                            'purchase_order_id' => $po->purchase_order_id,
                            'status_id' => $request->status_id,
                            'remarks' => trim($request->remarks ?: 'Status updated via status change modal'),
                            'prepared_at' => now(),
                        ]);

                        \Log::info('[PO Status Update] Created approval record', [
                            'po_no' => $poNo,
                            'status' => $status->status_name,
                        ]);
                    }
                }

                // Commit transaction
                DB::commit();

                // Log successful update
                \Log::info('[PO Status Update] Success', [
                    'po_no' => $poNo,
                    'old_status' => $po->status ?? 'N/A',
                    'new_status' => $status->status_name,
                    'updated_by' => $auth['user_id'],
                    'remarks' => $request->remarks,
                ]);

                if ($wantsJson) {
                    return response()->json([
                        'success' => true,
                        'message' => 'PO status updated successfully',
                        'data' => [
                            'po_no' => $poNo,
                            'status_name' => $status->status_name,
                            'updated_at' => now()->toIso8601String(),
                        ],
                    ], 200);
                }

                return back()->with('success', 'PO status updated successfully to '.$status->status_name);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('[PO Status Update] Failed', [
                'po_no' => $poNo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($wantsJson) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update status: '.$e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update status: '.$e->getMessage()]);
        }
    }

    /**
     * Get detailed information for a specific purchase order
     */
    public function getOrderDetails(Request $request, string $poNo)
    {
        try {
            \Log::info('Fetching order details', ['po_no' => $poNo]);

            $auth = $request->session()->get('auth_user');
            if (! $auth) {
                \Log::warning('Unauthorized access attempt for order details', ['po_no' => $poNo]);

                return response()->json(['success' => false, 'message' => 'Unauthorized - Please login'], 401);
            }

            \Log::info('User authenticated', ['user_id' => $auth['user_id'], 'role' => $auth['role']]);

            $po = DB::table('purchase_orders as po')
                ->leftJoin('users as u', 'u.user_id', '=', 'po.requestor_id')
                ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
                ->leftJoin('approvals as ap', 'ap.purchase_order_id', '=', 'po.purchase_order_id')
                ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                ->where('po.purchase_order_no', $poNo)
                ->select(
                    'po.*',
                    DB::raw("ISNULL(u.name, '') as made_by"),
                    's.name as supplier_name',
                    'st.status_name',
                    'ap.remarks'
                )
                ->first();

            \Log::info('PO query result', ['po_found' => ! is_null($po), 'po_data' => $po]);

            if (! $po) {
                \Log::warning('Purchase order not found', ['po_no' => $poNo]);

                return response()->json(['success' => false, 'message' => 'Purchase order not found'], 404);
            }

            $items = DB::table('items')
                ->where('purchase_order_id', $po->purchase_order_id)
                ->select('item_name', 'item_description as description', 'quantity', 'unit_price', 'total_cost')
                ->get();

            return response()->json([
                'success' => true,
                'order' => $po,
                'items' => $items,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching order details', [
                'po_no' => $poNo,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching order details: '.$e->getMessage(),
            ], 500);
        }
    }
}
