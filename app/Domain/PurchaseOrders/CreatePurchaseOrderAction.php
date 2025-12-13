<?php

namespace App\Domain\PurchaseOrders;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreatePurchaseOrderAction
{
    /**
     * Create a purchase order, its items, and initial approval status.
     *
     * @param  array  $auth  Authenticated user payload from session
     * @param  array  $data  Validated request data
     * @param  Session  $session  Session store to update recovered auth_user if needed
     * @return array{po_id:string, po_no:string}
     *
     * @throws ValidationException
     */
    public function handle(array &$auth, array $data, Session $session): array
    {
        $data['supplier_id'] = $this->ensureSupplier($data);
        $this->ensureUserExists($auth, $session);

        [$items, $subtotal, $shipping, $discount, $total] = $this->prepareItemsAndTotals($data);

        try {
            return DB::transaction(function () use ($auth, $data, $items, $subtotal, $shipping, $discount, $total) {
                $poNo = $this->nextPurchaseOrderNumber();
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

                foreach ($items as $it) {
                    $row = [
                        'item_id' => (string) Str::uuid(),
                        'purchase_order_id' => $poId,
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

                $statusDraft = DB::table('statuses')->where('status_name', 'Pending')->value('status_id');
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
            Log::error('PO create failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    private function ensureSupplier(array $data): string
    {
        $supplierId = $data['supplier_id'];

        if ($supplierId === '__manual__') {
            $newSupplier = $data['new_supplier'] ?? [];
            if (empty($newSupplier['name'])) {
                throw ValidationException::withMessages([
                    'new_supplier.name' => 'Supplier name is required',
                ]);
            }

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
        } else {
            $exists = DB::table('suppliers')->where('supplier_id', $supplierId)->exists();
            if (! $exists) {
                throw ValidationException::withMessages([
                    'supplier_id' => 'Selected supplier does not exist',
                ]);
            }
        }

        return $supplierId;
    }

    private function ensureUserExists(array &$auth, Session $session): void
    {
        $userExists = DB::table('users')->where('user_id', $auth['user_id'])->exists();
        if ($userExists) {
            return;
        }

        $recoveredId = $auth['email']
            ? DB::table('users')->where('email', $auth['email'])->value('user_id')
            : null;

        if ($recoveredId) {
            $auth['user_id'] = $recoveredId;
            $session->put('auth_user', $auth);

            return;
        }

        throw ValidationException::withMessages([
            'user' => 'Your user account was not found in users table. Please re-login or contact admin.',
        ]);
    }

    private function prepareItemsAndTotals(array $data): array
    {
        $items = $data['items'];
        $subtotal = 0.0;

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
        $total = $subtotal;

        return [$items, $subtotal, $shipping, $discount, $total];
    }

    private function nextPurchaseOrderNumber(): string
    {
        $max = (int) DB::table('purchase_orders')
            ->selectRaw('MAX(CASE WHEN ISNUMERIC(purchase_order_no)=1 THEN CAST(purchase_order_no AS INT) ELSE 0 END) as m')
            ->value('m');

        return (string) ($max + 1);
    }
}
