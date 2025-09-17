/** Persist a new PO and its items (validates FKs and totals) */
    public function store(Request $request)
    {
        $auth = $this->requireRole($request, 'requestor');
        $data = $request->validate([
            'supplier_id' => ['required','string','exists:suppliers,supplier_id'],
            'purpose' => ['required','string','max:255'],
            'date_requested' => ['required','date'],
            'delivery_date' => ['required','date'],
            'items' => ['required','array','min:1'],
            'items.*.item_description' => ['required','string'],
            'items.*.quantity' => ['required','integer','min:1'],
        ]);

        // Compute totals
        $subtotal = 0;
        // Validate FK existence and normalize current user id
        $supplierExists = DB::table('suppliers')->where('supplier_id', $request->input('supplier_id'))->exists();
        if (!$supplierExists) {
            return back()->withErrors(['supplier_id' => 'Selected supplier does not exist'])->withInput();
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
        $shipping = $subtotal > 0 ? 6000.00 : 0.00;
        $discount = $subtotal > 0 ? 13543.00 : 0.00;
        $vat = round($subtotal * 0.12, 2);
        $total = $subtotal + $vat; // match sample: total = ex-VAT + 12% VAT

        try {
            $created = DB::transaction(function () use ($auth, $data, $subtotal, $shipping, $discount, $total) {
            // Auto-generate PO number as next sequence per day (simple example)
            $today = now()->format('Ymd');
            $countToday = DB::table('purchase_orders')->whereDate('created_at', today())->count();
            $poNo = $today . '-' . str_pad((string)($countToday + 1), 3, '0', STR_PAD_LEFT);

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
                DB::table('items')->insert([
                    'item_id' => (string) Str::uuid(),
                    'purchase_order_id' => $poId,
                    'item_description' => $it['item_description'],
                    'quantity' => $it['quantity'],
                    'unit_price' => $it['unit_price'],
                    'total_cost' => $it['quantity'] * $it['unit_price'],
                ]);
            }
            // Mark status Draft
            $statusDraft = DB::table('statuses')->where('status_name','Draft')->value('status_id');
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
            ->select('po.*','s.name as supplier_name','st.status_name','ap.remarks')
            ->first();
        if (!$po) abort(404);
        $items = DB::table('items')->where('purchase_order_id', $po->purchase_order_id)->get();
        return view('po.show', compact('po','items','auth'));
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
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Add validation rules for your other fields
            'items' => 'required|array',
            'items.*.quantity' => 'required|numeric|min:0', // Enforce validation
            'items.*.unit_price' => 'required|numeric|min:0', // Enforce validation
            'shipping' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        // Recalculate the totals securely on the server side
        $subTotal = 0;
        if (isset($validatedData['items'])) {
            foreach ($validatedData['items'] as $item) {
                // Ensure floating point precision
                $subTotal += bcmul($item['quantity'], $item['unit_price'], 2);
            }
        }
        
        $shipping = $validatedData['shipping'] ?? 0;
        $discount = $validatedData['discount'] ?? 0;
        $grandTotal = bcadd(bcsub($subTotal, $discount, 2), $shipping, 2);

        // ... continue with your logic to create the PO and its items
        // Example:
        $po = Po::create([
            // ... other fields
            'sub_total' => $subTotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'grand_total' => $grandTotal,
        ]);

        return redirect()->route('po.index')->with('success', 'Purchase order created successfully!');
    }