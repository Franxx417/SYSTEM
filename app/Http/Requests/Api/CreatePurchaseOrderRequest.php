<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Create Purchase Order API Request
 * 
 * Validates incoming requests for creating purchase orders via API
 */
class CreatePurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Authorization handled by middleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'supplier_id' => 'required|string|exists:suppliers,supplier_id',
            'purpose' => 'required|string|max:500',
            'date_requested' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:date_requested',
            'shipping_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'official_receipt_no' => 'nullable|string|max:100',
            
            // Items array
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'supplier_id.required' => 'Supplier is required',
            'supplier_id.exists' => 'Selected supplier does not exist',
            'purpose.required' => 'Purpose is required',
            'date_requested.required' => 'Request date is required',
            'delivery_date.required' => 'Delivery date is required',
            'delivery_date.after_or_equal' => 'Delivery date must be on or after request date',
            'items.required' => 'At least one item is required',
            'items.min' => 'At least one item is required',
            'items.*.item_name.required' => 'Item name is required',
            'items.*.quantity.required' => 'Item quantity is required',
            'items.*.quantity.min' => 'Item quantity must be at least 1',
            'items.*.unit_price.required' => 'Item unit price is required',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed',
                    'status' => 422,
                    'details' => $validator->errors()
                ],
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'version' => '1.0.0'
                ]
            ], 422)
        );
    }
}
