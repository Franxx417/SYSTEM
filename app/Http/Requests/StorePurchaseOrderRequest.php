<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization is handled in the controller via role check
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'supplier_id' => ['required', 'string'],
            'purpose' => ['required', 'string', 'max:255'],
            'date_requested' => ['required', 'date'],
            'delivery_date' => ['required', 'date', 'after_or_equal:date_requested'],
            'items' => ['required', 'array', 'min:1'],
            // Align with DB column size (e.g., NVARCHAR(255))
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.item_description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            // Optional checkboxes from UI; do not block saving if absent
            'vatable_sales' => ['nullable', 'boolean'],
            'apply_vat_12' => ['nullable', 'boolean'],
            // New supplier fields
            'new_supplier' => ['nullable', 'array'],
            'new_supplier.name' => ['required_if:supplier_id,__manual__', 'string', 'max:255'],
            'new_supplier.vat_type' => ['nullable', 'string', 'in:VAT,Non-VAT,VAT Exempt'],
            'new_supplier.address' => ['nullable', 'string', 'max:255'],
            'new_supplier.contact_person' => ['nullable', 'string', 'max:255'],
            'new_supplier.contact_number' => ['nullable', 'string', 'max:255'],
            'new_supplier.tin_no' => ['nullable', 'string', 'max:255'],
        ];

        // Only require supplier_id to exist if it's not a manual entry
        if ($this->input('supplier_id') !== '__manual__') {
            $rules['supplier_id'][] = 'exists:suppliers,supplier_id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Please select a supplier.',
            'supplier_id.exists' => 'Selected supplier does not exist.',
            'purpose.required' => 'Please enter the purpose.',
            'date_requested.required' => 'Please select the request date.',
            'delivery_date.after_or_equal' => 'Delivery date cannot be before the request date.',
            'items.required' => 'Add at least one item to your order.',
            'items.*.item_description.required' => 'Each item needs a description.',
            'items.*.quantity.min' => 'Item quantity must be at least 1.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',
            'new_supplier.name.required_if' => 'Supplier name is required when adding a new supplier.',
            'new_supplier.vat_type.in' => 'Please select a valid VAT type.',
        ];
    }
}
