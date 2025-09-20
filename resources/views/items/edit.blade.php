@extends('layouts.app')
@section('title','Edit Item')
@section('page_heading','Edit Item')
@section('page_subheading','Update item details')
@section('content')
    <!-- Edit item form -->
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Item Details</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-1">Please fix the following errors:</div>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('items.update', $item->item_id) }}">
                        @csrf
                        @method('POST')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="item_name" name="item_name" 
                                       value="{{ old('item_name', $item->item_name) }}" required maxlength="255"
                                       placeholder="Enter item name">
                                <div class="form-text">A short, descriptive name for the item</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="{{ old('quantity', $item->quantity) }}" required min="1"
                                       placeholder="Enter quantity">
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-12">
                                <label for="item_description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="item_description" name="item_description" 
                                          rows="3" required maxlength="255" 
                                          placeholder="Enter detailed description of the item">{{ old('item_description', $item->item_description) }}</textarea>
                                <div class="form-text">Detailed description of the item (max 255 characters)</div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                           value="{{ old('unit_price', $item->unit_price) }}" required min="0" step="0.01"
                                           placeholder="0.00">
                                </div>
                                <div class="form-text">Price per unit in Philippine Peso</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Total Cost</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="total_cost" readonly 
                                           value="{{ number_format($item->total_cost, 2) }}">
                                </div>
                                <div class="form-text">Calculated automatically</div>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Note:</strong> Updating this item will also update the associated Purchase Order totals.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate total cost
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity');
            const unitPriceInput = document.getElementById('unit_price');
            const totalCostInput = document.getElementById('total_cost');
            
            function calculateTotal() {
                const quantity = parseFloat(quantityInput.value) || 0;
                const unitPrice = parseFloat(unitPriceInput.value) || 0;
                const total = quantity * unitPrice;
                totalCostInput.value = total.toFixed(2);
            }
            
            quantityInput.addEventListener('input', calculateTotal);
            unitPriceInput.addEventListener('input', calculateTotal);
            
            // Calculate on page load
            calculateTotal();
        });
    </script>
@endsection



