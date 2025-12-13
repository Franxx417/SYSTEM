<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Manage suppliers (requestor and superadmin) */
class SupplierController extends Controller
{
    /** Ensure user is authorized - SUPERADMIN HAS UNRESTRICTED ACCESS */
    private function requireAuthorized(Request $request): array
    {
        $auth = $request->session()->get('auth_user');
        if (! $auth) {
            abort(403);
        }

        // SUPERADMIN HAS UNRESTRICTED ACCESS TO EVERYTHING
        if ($auth['role'] === 'superadmin') {
            return $auth;
        }

        // Allow requestor for suppliers
        if ($auth['role'] !== 'requestor') {
            abort(403);
        }

        return $auth;
    }

    /** List suppliers */
    public function index(Request $request)
    {
        $auth = $this->requireAuthorized($request);
        $suppliers = DB::table('suppliers')->orderBy('name')->paginate(10);

        return view('suppliers.index', compact('suppliers', 'auth'));
    }

    /** Show supplier create form */
    public function create(Request $request)
    {
        $auth = $this->requireAuthorized($request);

        return view('suppliers.create', compact('auth'));
    }

    /** Persist a new supplier */
    public function store(Request $request)
    {
        $this->requireAuthorized($request);

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
                'vat_type' => ['nullable', 'string', 'in:VAT,Non-VAT,Non_VAT'],
                'contact_person' => ['nullable', 'string', 'max:100'],
                'contact_number' => ['nullable', 'string', 'max:20'],
                'tin_no' => ['nullable', 'string', 'max:20'],
            ]);

            // Normalize VAT type: treat empty string as null
            if (($data['vat_type'] ?? '') === '') {
                $data['vat_type'] = null;
            }

            // Add supplier_id
            $data['supplier_id'] = (string) \Illuminate\Support\Str::uuid();
            $data['created_at'] = now();
            $data['updated_at'] = now();

            DB::table('suppliers')->insert($data);

            // Return JSON response for AJAX/modal requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier created successfully',
                    'supplier' => $data,
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('suppliers.index')->with('status', 'Supplier created');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create supplier: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('suppliers.index')->with('error', 'Failed to create supplier');
        }
    }

    /** Show edit supplier form */
    public function edit(Request $request, string $id)
    {
        $this->requireAuthorized($request);
        $supplier = DB::table('suppliers')->where('supplier_id', $id)->first();

        if (! $supplier) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Supplier not found'], 404);
            }
            abort(404);
        }

        // Return JSON response for AJAX/modal requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'supplier' => $supplier,
            ]);
        }

        // Return view for regular requests
        return view('suppliers.edit', compact('supplier'));
    }

    /** Update supplier */
    public function update(Request $request, string $id)
    {
        $this->requireAuthorized($request);

        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
                'vat_type' => ['nullable', 'string', 'in:VAT,Non-VAT,Non_VAT'],
                'contact_person' => ['nullable', 'string', 'max:100'],
                'contact_number' => ['nullable', 'string', 'max:20'],
                'tin_no' => ['nullable', 'string', 'max:20'],
            ]);

            if (($data['vat_type'] ?? '') === '') {
                $data['vat_type'] = null;
            }

            $updated = DB::table('suppliers')->where('supplier_id', $id)->update($data);

            if (! $updated) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Supplier not found'], 404);
                }

                return redirect()->route('suppliers.index')->with('error', 'Supplier not found');
            }

            // Return JSON response for AJAX/modal requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier updated successfully',
                    'supplier' => DB::table('suppliers')->where('supplier_id', $id)->first(),
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('suppliers.index')->with('status', 'Supplier updated');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update supplier: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('suppliers.index')->with('error', 'Failed to update supplier');
        }
    }

    /** Delete supplier */
    public function destroy(Request $request, string $id)
    {
        $this->requireAuthorized($request);

        try {
            // Check if supplier is used in any purchase orders
            $usedInPO = DB::table('purchase_orders')->where('supplier_id', $id)->exists();

            if ($usedInPO) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete supplier as it is referenced in purchase orders.',
                    ], 400);
                }

                return redirect()->route('suppliers.index')->with('error', 'Cannot delete supplier as it is referenced in purchase orders.');
            }

            $deleted = DB::table('suppliers')->where('supplier_id', $id)->delete();

            if (! $deleted) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Supplier not found',
                    ], 404);
                }

                return redirect()->route('suppliers.index')->with('error', 'Supplier not found');
            }

            // Return JSON response for AJAX/modal requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier deleted successfully',
                ]);
            }

            // Return redirect for regular form submissions
            return redirect()->route('suppliers.index')->with('status', 'Supplier deleted successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete supplier: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->route('suppliers.index')->with('error', 'Failed to delete supplier');
        }
    }
}
