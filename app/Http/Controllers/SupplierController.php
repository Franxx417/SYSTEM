<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Manage suppliers (authorized_personnel only) */
class SupplierController extends Controller
{
    /** Ensure user is authorized_personnel */
    private function requireAuthorized(Request $request): array
    {
        $auth = $request->session()->get('auth_user');
        if (!$auth || !in_array($auth['role'], ['superadmin','requestor'], true)) {
            abort(403);
        }
        return $auth;
    }

    /** List suppliers */
    public function index(Request $request)
    {
        $auth = $this->requireAuthorized($request);
        $suppliers = DB::table('suppliers')->orderBy('name')->paginate(10);
        return view('suppliers.index', compact('suppliers','auth'));
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
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'vat_type' => ['nullable','string','in:VAT,Non-VAT,Non_VAT'],
            'contact_person' => ['nullable','string','max:100'],
            'contact_number' => ['nullable','string','max:20'],
            'tin_no' => ['nullable','string','max:20'],
        ]);
        // Normalize VAT type: treat empty string as null
        if (($data['vat_type'] ?? '') === '') {
            $data['vat_type'] = null;
        }
        DB::table('suppliers')->insert($data);
        return redirect()->route('suppliers.index')->with('status','Supplier created');
    }

    /** Show edit supplier form */
    public function edit(Request $request, string $id)
    {
        $this->requireAuthorized($request);
        $supplier = DB::table('suppliers')->where('supplier_id', $id)->first();
        if (!$supplier) abort(404);
        return view('suppliers.edit', compact('supplier'));
    }

    /** Update supplier */
    public function update(Request $request, string $id)
    {
        $this->requireAuthorized($request);
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'address' => ['nullable','string'],
            'vat_type' => ['nullable','string','in:VAT,Non-VAT,Non_VAT'],
            'contact_person' => ['nullable','string','max:100'],
            'contact_number' => ['nullable','string','max:20'],
            'tin_no' => ['nullable','string','max:20'],
        ]);
        if (($data['vat_type'] ?? '') === '') {
            $data['vat_type'] = null;
        }
        DB::table('suppliers')->where('supplier_id', $id)->update($data);
        return redirect()->route('suppliers.index')->with('status','Supplier updated');
    }
}



