@extends('layouts.app')
@section('title','Edit Purchase Order')
@section('page_heading','Edit Purchase Order')
@section('page_subheading','Update supplier, items, and totals')
@section('content')
    <!-- Use the exact CDNs you added (kept as requested) -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PO edit form: supplier, dates, items, and realtime totals -->
    <form method="POST" action="{{ route('po.update', $po->purchase_order_no) }}" id="poEditForm" data-latest-price-url="{{ route('api.items.latest_price') }}" data-po-no="{{ $po->purchase_order_no }}">
        @csrf
        @method('PUT')
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">There were problems updating your PO:</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card"><div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">PO Number</label>
                        <input class="form-control" type="text" value="{{ $po->purchase_order_no }}" disabled />
                    </div>
                    <!-- Supplier select includes VAT flag; indicator shows below -->
                    <div class="mb-1">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id" id="supplier-select" required>
                            <option value="">Select supplier</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->supplier_id }}" data-vat="{{ $s->vat_type }}" @if($s->supplier_id===$po->supplier_id) selected @endif>{{ $s->name }}</option>
                            @endforeach
                            <option value="__manual__">-- Add New Supplier --</option>
                        </select>
                    </div>
                    
                    <!-- Manual Supplier Fields (initially hidden) -->
                    <div id="manual-supplier-fields" class="d-none">
                        <div class="card mb-3">
                            <div class="card-header bg-light">New Supplier Details</div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="supplier-name" name="new_supplier[name]">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">VAT Type</label>
                                        <select class="form-select" id="supplier-vat-type" name="new_supplier[vat_type]">
                                            <option value="">-- None --</option>
                                            <option value="VAT">VAT</option>
                                            <option value="Non-VAT">Non-VAT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" id="supplier-address" name="new_supplier[address]">
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Contact Person</label>
                                        <input type="text" class="form-control" id="supplier-contact-person" name="new_supplier[contact_person]">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="supplier-contact-number" name="new_supplier[contact_number]">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">TIN No.</label>
                                    <input type="text" class="form-control" id="supplier-tin" name="new_supplier[tin_no]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="purpose-input">Purpose</label>
                        <textarea class="form-control" type="text" id="purpose-input" name="purpose" required maxlength="255">{{ $po->purpose }}</textarea>
                        <label for="purpose-input" id="text-count" class="text-muted"></label>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Requested</label>
                            <input id="date-from" type="text" name="date_requested" value="{{ $po->date_requested }}" required autocomplete="off" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delivery Date</label>
                            <input id="date-to" class="datte" type="text" name="delivery_date" value="{{ $po->delivery_date }}" required autocomplete="off" />
                        </div>
                        <div id="result" class="text-muted small mt-2"></div>
                    </div>
                </div></div>
            </div>
            <div class="col-lg-6">
                <div class="card"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Shipping</span>
                        <input class="form-control text-end w-50" id="calc-shipping-input" type="number only" min="0" placeholder="0.00" value="{{ number_format($po->shipping_fee ?? 0, 2, '.', '') }}" />
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <span class="text-muted">Discount</span>
                        <input class="form-control text-end w-50" id="calc-discount-input" type="number only" min="0" placeholder="0.00" value="{{ number_format($po->discount ?? 0, 2, '.', '') }}" />
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <span class="text-muted">Vatable Sales (Ex Vat)</span>
                        <input class="form-control text-end w-50" id="calc-subtotal" type="text" placeholder="0" value="{{ number_format($po->subtotal ?? 0, 2, '.', '') }}" required autocomplete="off" />
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">12% Vat</span>
                        <input class="form-control text-end w-50" id="calc-vat" type="text" placeholder="0" value="{{ number_format(($po->total ?? 0) - ($po->subtotal ?? 0), 2, '.', '') }}" required autocomplete="off" />
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-semibold">
                        <span>TOTAL</span>
                        <span id="calc-total">{{ number_format($po->total ?? 0, 2) }}</span>
                    </div>
                </div></div>
            </div>
        </div>

        <!-- Items list; description can be picked from previous items or entered manually -->
        <div class="card mt-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">Items</div>
                <button class="btn btn-sm btn-outline-primary" id="addItem" type="button">Add Item</button>
            </div>
            <div id="items">
                @php
                    $existingNames = \Illuminate\Support\Facades\DB::table('items')
                        ->select('item_name', \Illuminate\Support\Facades\DB::raw('MIN(item_description) as sample_desc'), \Illuminate\Support\Facades\DB::raw('AVG(unit_price) as unit_price'))
                        ->whereNotNull('item_name')
                        ->groupBy('item_name')
                        ->orderBy('item_name')
                        ->limit(200)
                        ->get();
                    if ($existingNames->isEmpty()) {
                        $existingNames = \Illuminate\Support\Facades\DB::table('items')
                            ->select('item_description as item_name', 'item_description as sample_desc', \Illuminate\Support\Facades\DB::raw('AVG(unit_price) as unit_price'))
                            ->groupBy('item_description')
                            ->orderByRaw('COUNT(*) DESC')
                            ->limit(200)
                            ->get();
                    }
                @endphp
                @foreach($items as $i)
                    <div class="row g-2 align-items-end item-row mb-2" data-initial-desc="{{ $i->item_description }}">
                        <div class="col-md-12">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Item Name</label>
                                    <select class="form-select item-name-select" name="items[{{ $loop->index }}][item_name]">
                                        <option value="">Select item</option>
                                        <option value="__manual__">+ Add new item manually</option>
                                        @foreach($existingNames as $row)
                                            <option value="{{ $row->item_name }}" data-desc="{{ $row->sample_desc }}" data-price="{{ $row->unit_price }}" @if(($i->item_name ?? '')===$row->item_name) selected @endif>{{ $row->item_name }}</option>
                                        @endforeach
                                    </select>
                                    <input class="form-control d-none item-name-manual" type="text" placeholder="Type item name" maxlength="255" value="{{ $i->item_name ?? '' }}" />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Description</label>
                                    <input class="form-control item-desc-manual" name="items[{{ $loop->index }}][item_description]" type="text" placeholder="Type item description (optional)" maxlength="255" value="{{ $i->item_description }}" />
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty</label>
                                    <input class="form-control" name="items[{{ $loop->index }}][quantity]" type="number" min="1" value="{{ $i->quantity }}" required />
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Unit Price</label>
                                    <input class="form-control unit-price" name="items[{{ $loop->index }}][unit_price]" type="number" min="0" step="0.01" value="{{ number_format($i->unit_price,2,'.','') }}" />
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-outline-danger removeItem" type="button">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div></div>

        <div class="mt-3 d-flex justify-content-end">
            <a class="btn btn-outline-secondary me-2" href="{{ route('po.show', $po->purchase_order_no) }}">Cancel</a>
            <button class="btn btn-primary" type="submit">Save Changes</button>
        </div>
        <template id="itemRowTpl">
            <div class="row g-2 align-items-end item-row mb-2">
                <div class="col-md-6">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <select class="form-select item-name-select" name="items[IDX][item_name]">
                                <option value="">Select item</option>
                                @foreach($existingNames as $row)
                                    <option value="{{ $row->item_name }}" data-desc="{{ $row->sample_desc }}">{{ $row->item_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input class="form-control item-desc-manual" name="items[IDX][item_description]" type="text" placeholder="Type item description (optional)" maxlength="255" />
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input class="form-control" name="items[IDX][quantity]" type="number" min="1" value="1" required />
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Price</label>
                    <input class="form-control unit-price" name="items[IDX][unit_price]" type="number" min="0" step="0.01" placeholder="0.00" />
                </div>
                <div class="col-md-2 text-end">
                    <button class="btn btn-outline-danger removeItem" type="button">Remove</button>
                </div>
            </div>
        </template>
    <script src="/js/po-edit.js"></script>
        <script src="/js/po-edit-extras.js"></script>
    </form>
@endsection


