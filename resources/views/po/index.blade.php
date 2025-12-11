@extends('layouts.app')
@section('title','My Purchase Orders')
@section('page_heading','My Purchase Orders')
@section('page_subheading','Create and track your purchase orders')

@push('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
@endpush

@section('content')
    
    <style>
        /* Status cards for modal */
        .status-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin: 4px 0;
            background: #f8f9fa;
            transition: all 0.2s ease;
            cursor: pointer;
            min-width: 140px;
        }
        
        .status-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .status-card.active {
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .status-icon {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        
        .status-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .status-description {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .status-action {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }
        
        /* Status option styling */
        .status-option {
            transition: all 0.2s ease;
        }
        
        .status-option:hover {
            background-color: #f8f9fa !important;
        }
        
        .status-option.active {
            background-color: #e7f1ff !important;
            border-color: #007bff !important;
        }
    </style>
    <!-- Filters and New PO button -->
    <div class="row mb-3">
        <div class="col-lg-8 mb-3 mb-lg-0">
            <form method="GET" action="{{ route('po.index') }}" class="row g-2">
                <div class="col-md-4 col-sm-6 mb-2 mb-sm-0">
                    <input type="text" name="search" class="form-control" placeholder="Search by PO number or purpose..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-sm-0">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->status_id }}" {{ request('status') == $status->status_id ? 'selected' : '' }}>
                                {{ $status->status_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2 mb-sm-0">
                    <select name="supplier" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_id }}" {{ request('supplier') == $supplier->supplier_id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <button type="submit" class="btn btn-outline-primary w-50"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
  <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z"/>
</svg></button>
                </div>
            </form>
        </div>
        <div class="col-lg-4 text-sm-end">
            <div class="d-flex gap-2 justify-content-end">
                 <a class="btn btn-outline-primary" href="{{ route('po.create') }}?new=1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
</svg></a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="po-index-table" data-po-show-template="{{ route('po.show_json','__po__') }}">
                <thead class="table-light">
                    <tr>
                        <th>No.</th>
                        <th>Purpose</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($pos as $po)
                    <tr>
                        <td>{{ $po->purchase_order_no }}</td>
                        <td>{{ Str::limit($po->purpose, 30) }}</td>
                        <td>{{ Str::limit($po->supplier_name ?? '—', 20) }}</td>
                        <td>
                            @php
                                $currentStatus = $statuses->firstWhere('status_id', $po->status_id);
                                $statusName = $currentStatus ? $currentStatus->status_name : 'Unknown';
                            @endphp
                            
                            <div style="cursor: pointer;" 
                                 data-po="{{ $po->purchase_order_no }}" 
                                 data-current-status="{{ $statusName }}"
                                 onclick="showStatusChangeModal('{{ $po->purchase_order_no }}', '{{ $statusName }}')">
                                @include('partials.status-display', ['status' => $statusName, 'type' => 'text'])
                            </div>
                        </td>
                        <td class="text-end">₱{{ number_format($po->total, 2) }}</td>
                        <td class="text-center">
                            <div class="btn-group btn-group-md" role="group">
                                <button class="btn btn-outline-primary btn-view-po" data-po="{{ $po->purchase_order_no }}" title="View Details">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
</svg>                                </button>
                                <button class="btn btn-outline-warning" onclick="showEditModal('{{ $po->purchase_order_no }}')" title="Edit">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
</svg>                                </button>
                                <button class="btn btn-outline-danger" onclick="deletePO('{{ $po->purchase_order_no }}', '{{ addslashes($po->purpose) }}')" title="Delete">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
  <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
</svg>                                </button>
                                <button class="btn btn-outline-info btn-print-direct" 
                                        data-po-number="{{ $po->purchase_order_no }}"
                                        data-fallback-url="{{ route('po.print', $po->purchase_order_no) }}"
                                        title="Print (Direct)" 
                                        aria-label="Print Purchase Order {{ $po->purchase_order_no }}">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
  <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
  <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1"/>
</svg>                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
         </table>
         </div>
     </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $pos->links() }}</div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Order Information Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Order Information</h6>
                            <div class="mb-2"><strong>Purchase Order:</strong> <span id="order_po_number" class="text-primary"></span></div>
                            <div class="mb-2"><strong>Ordered By:</strong> <span id="order_made_by"></span></div>
                            <div class="mb-2"><strong>Date Requested:</strong> <span id="order_date_requested"></span></div>
                            <div class="mb-2"><strong>Delivery Date:</strong> <span id="order_delivery_date"></span></div>
                            <div class="mb-2"><strong>Created:</strong> <span id="order_created_at"></span></div>
                            <div class="mb-2"><strong>Last Updated:</strong> <span id="order_updated_at"></span></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Order Details</h6>
                            <div class="mb-2"><strong>Purpose:</strong> <span id="order_purpose"></span></div>
                            <div class="mb-2"><strong>Supplier:</strong> <span id="order_supplier"></span></div>
                            <div class="mb-2"><strong>Status:</strong> <span id="order_status" class="badge"></span></div>
                            <div class="mb-2"><strong>Remarks:</strong> <span id="order_remarks" class="text-muted"></span></div>
                        </div>
                    </div>
                    
                    <!-- Order Totals Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="text-muted mb-3">Order Totals</h6>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Subtotal</small>
                                        <div class="fw-bold" id="order_subtotal">₱0.00</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Shipping</small>
                                        <div class="fw-bold" id="order_shipping">₱0.00</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-light rounded">
                                        <small class="text-muted">Discount</small>
                                        <div class="fw-bold" id="order_discount">₱0.00</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-2 bg-primary text-white rounded">
                                        <small>Total</small>
                                        <div class="fw-bold" id="order_total">₱0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Section -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="order_items">
                                    <!-- Items will be dynamically populated -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit PO Modal -->
    <div class="modal fade" id="editPOModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="poEditForm" method="POST" data-latest-price-url="{{ route('api.items.latest_price') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card"><div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">PO Number</label>
                                        <input class="form-control" type="text" id="edit-po-number" disabled />
                                    </div>
                                    <!-- Supplier select includes VAT flag; indicator shows below -->
                                    <div class="mb-1">
                                        <label class="form-label">Supplier</label>
                                        <select class="form-select" name="supplier_id" id="supplier-select" required>
                                            <option value="">Select supplier</option>
                                            @foreach($suppliers as $s)
                                                <option value="{{ $s->supplier_id }}" data-vat="{{ $s->vat_type }}">{{ $s->name }}</option>
                                            @endforeach
                                            <option value="__manual__">-- Add New Supplier --</option>
                                        </select>
                                        <div class="small text-muted mt-1">VAT Status: <span id="supplier-vat">—</span></div>
                                    </div>
                                    
                                    <!-- Manual Supplier Fields (initially hidden) -->
                                    <div id="manual-supplier-fields" class="d-none">
                                        <div class="card mb-3">
                                            <div class="card-header bg-light">New Supplier Details</div>
                                            <div class="card-body">
                                                <div class="row g-2">
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="supplier-name">
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">VAT Type</label>
                                                        <select class="form-select" id="supplier-vat-type">
                                                            <option value="">-- None --</option>
                                                            <option value="VAT">VAT</option>
                                                            <option value="Non-VAT">Non-VAT</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" class="form-control" id="supplier-address">
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Contact Person</label>
                                                        <input type="text" class="form-control" id="supplier-contact-person">
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label class="form-label">Contact Number</label>
                                                        <input type="text" class="form-control" id="supplier-contact-number">
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">TIN No.</label>
                                                    <input type="text" class="form-control" id="supplier-tin">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="purpose-input">Purpose</label>
                                        <textarea class="form-control" type="text" id="purpose-input" name="purpose" required maxlength="255"></textarea>
                                        <label for="purpose-input" id="text-count" class="text-muted"></label>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Date Requested</label>
                                            <input id="date-from" class="form-control" type="text" name="date_requested" required autocomplete="off" placeholder="YYYY-MM-DD" />
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Delivery Date</label>
                                            <input id="date-to" class="form-control" type="text" name="delivery_date" required autocomplete="off" placeholder="YYYY-MM-DD" />
                                        </div>
                                        <div id="result" class="text-muted small mt-2"></div>
                                    </div>
                                </div></div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card"><div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Shipping</span>
                                        <input class="form-control text-end w-50 number-only-input" id="calc-shipping-input" type="text" min="0" step="0.01" placeholder="0.00" />
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="text-muted">Discount</span>
                                        <input class="form-control text-end w-50 number-only-input" id="calc-discount-input" type="text" min="0" step="0.01" placeholder="0.00" />
                                    </div>
                                    <div class="d-flex justify-content-between mt-3">
                                        <span class="text-muted">Vatable Sales (Ex Vat)</span>
                                        <input class="form-control text-end w-50 number-only-input" id="calc-subtotal" type="text" placeholder="0" readonly />
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">12% Vat</span>
                                        <input class="form-control text-end w-50 number-only-input" id="calc-vat" type="text" placeholder="0" readonly />
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between fw-semibold">
                                        <span>TOTAL</span>
                                        <span id="calc-total">0.00</span>
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
                                <!-- Items will be populated dynamically -->
                            </div>
                        </div></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Item Row Template for Edit Modal -->
    <template id="itemRowTpl">
        <div class="row g-2 align-items-end item-row mb-2">
            <div class="col-md-12">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Item Name</label>
                        <select class="form-select item-name-select" name="items[IDX][item_name]">
                            <option value="">Select item</option>
                            <option value="__manual__">+ Add new item manually</option>
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
                            @foreach($existingNames as $row)
                                <option value="{{ $row->item_name }}" data-desc="{{ $row->sample_desc }}" data-price="{{ $row->unit_price }}">{{ $row->item_name }}</option>
                            @endforeach
                        </select>
                        <input class="form-control d-none item-name-manual" type="text" placeholder="Type item name" maxlength="255" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Description</label>
                        <input class="form-control item-desc-manual" name="items[IDX][item_description]" type="text" placeholder="Type item description (optional)" maxlength="255" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input class="form-control" name="items[IDX][quantity]" type="number" min="1" required />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit Price</label>
                        <input class="form-control unit-price" name="items[IDX][unit_price]" type="number" min="0" step="0.01" />
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-danger btn-sm removeItem" type="button">×</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePOModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-danger me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                            </svg>
                        </div>
                        <div>
                            <h6 class="mb-1">Are you sure you want to delete this Purchase Order?</h6>
                            <p class="mb-0 text-muted">This action cannot be undone.</p>
                        </div>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <strong>PO #:</strong> <span id="delete_po_number"></span><br>
                        <strong>Purpose:</strong> <span id="delete_po_purpose"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deletePOForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Purchase Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div class="modal fade" id="statusChangeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Change Purchase Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Purchase Order:</strong> <span id="status_po_number"></span><br>
                        <strong>Current Status:</strong> <span id="status_current"></span>
                    </div>
                    
                    <form id="statusChangeForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select New Status:</label>
                            <div class="list-group">
                                @foreach($statuses as $status)
                                    @php
                                        $description = $status->description ?? 'Status description';
                                    @endphp
                                    <div class="list-group-item list-group-item-action status-option" 
                                         data-status-id="{{ $status->status_id }}" 
                                         data-status-name="{{ $status->status_name }}"
                                         onclick="selectStatus(this)"
                                         style="cursor: pointer;">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ $status->status_name }}</h6>
                                        </div>
                                        <p class="mb-0 text-muted small">{{ $description }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" id="status_remarks" rows="2" placeholder="Add a note about this status change..."></textarea>
                        </div>
                        
                        <input type="hidden" name="status_id" id="selected_status_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmStatusChange" disabled>Update Status</button>
                </div>
            </div>
        </div>
    </div>


    @vite(['resources/js/pages/po-index.js', 'resources/js/pages/po-edit.js'])


@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
    <style>
        /* Enhanced datepicker styling - ensure it appears above edit modal */
        .ui-datepicker {
            z-index: 10000 !important;
            font-size: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
        
        /* Ensure datepicker appears above Bootstrap modal */
        .modal .ui-datepicker {
            z-index: 1060 !important;
        }
        
        .ui-datepicker-header {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .ui-datepicker td span, .ui-datepicker td a {
            padding: 4px;
            text-align: center;
        }
        
        .ui-datepicker .ui-state-active {
            background-color: #007bff;
            color: white;
        }
        
        .ui-datepicker .ui-state-hover {
            background-color: #e9ecef;
        }
        
        /* Date input styling */
        #date-from, #date-to {
            cursor: pointer;
        }
        
        #date-from:focus, #date-to:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        /* Result text styling */
        #result {
            font-weight: 500;
            margin-top: 5px;
        }
        
        /* Modal date section styling */
        .modal-body .row .col-md-6 {
            position: relative;
        }
        
        /* Loading state for date inputs */
        .date-loading {
            position: relative;
        }
        
        .date-loading::after {
            content: '📅';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }
    </style>
    <script>
        // Simple direct approach - initialize immediately (replicates create.blade.php behavior)
        jQuery(function($) {
            console.log('Initializing datepickers...');
            
            var $from = $('#date-from');
            var $to = $('#date-to');
            
            if ($from.length && $to.length) {
                $from.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function(d) {
                        $to.datepicker('option', 'minDate', d);
                        updateDateInfo();
                    }
                });
                
                $to.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function(d) {
                        $from.datepicker('option', 'maxDate', d);
                        updateDateInfo();
                    }
                });
                
                function updateDateInfo() {
                    var fromVal = $from.val();
                    var toVal = $to.val();
                    var $result = $('#result');
                    if (fromVal && toVal) {
                        var fromDate = new Date(fromVal);
                        var toDate = new Date(toVal);
                        var diffTime = Math.abs(toDate - fromDate);
                        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        $result.text('Delivery period: ' + diffDays + ' days');
                    } else {
                        $result.text('');
                    }
                }
                
                console.log('Datepickers initialized successfully!');
            }
            
            // Purpose counter
            var $purpose = $('#purpose-input');
            var $count = $('#text-count');
            var maxLen = $purpose.attr('maxlength');
            if ($purpose.length && $count.length && maxLen) {
                function updateCounter() {
                    var rem = maxLen - $purpose.val().length;
                    $count.text(rem + ' characters remaining');
                    $count.toggleClass('text-danger', rem <= 20).toggleClass('text-muted', rem > 20);
                }
                updateCounter();
                $purpose.on('input', updateCounter);
            }
        });
        
        // Modal-specific datepicker reinitialization
        $('#editPOModal').on('shown.bs.modal', function() {
            console.log('Edit modal shown - reinitializing datepickers...');
            
            var $from = $('#date-from');
            var $to = $('#date-to');
            
            // Destroy existing datepickers if any
            if ($from.hasClass('hasDatepicker')) {
                $from.datepicker('destroy');
            }
            if ($to.hasClass('hasDatepicker')) {
                $to.datepicker('destroy');
            }
            
            // Reinitialize with same settings
            if ($from.length && $to.length) {
                $from.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function(d) {
                        $to.datepicker('option', 'minDate', d);
                        updateDateInfo();
                    }
                });
                
                $to.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function(d) {
                        $from.datepicker('option', 'maxDate', d);
                        updateDateInfo();
                    }
                });
                
                function updateDateInfo() {
                    var fromVal = $from.val();
                    var toVal = $to.val();
                    var $result = $('#result');
                    if (fromVal && toVal) {
                        var fromDate = new Date(fromVal);
                        var toDate = new Date(toVal);
                        var diffTime = Math.abs(toDate - fromDate);
                        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        $result.text('Delivery period: ' + diffDays + ' days');
                    } else {
                        $result.text('');
                    }
                }
                
                console.log('Modal datepickers reinitialized successfully!');
            }
            
            // Reinitialize purpose counter
            var $purpose = $('#purpose-input');
            var $count = $('#text-count');
            var maxLen = $purpose.attr('maxlength');
            if ($purpose.length && $count.length && maxLen) {
                function updateCounter() {
                    var rem = maxLen - $purpose.val().length;
                    $count.text(rem + ' characters remaining');
                    $count.toggleClass('text-danger', rem <= 20).toggleClass('text-muted', rem > 20);
                }
                updateCounter();
                $purpose.off('input.modal').on('input.modal', updateCounter);
            }
        });
        
        // Clean up modal datepickers when hidden
        $('#editPOModal').on('hidden.bs.modal', function() {
            var $from = $('#date-from');
            var $to = $('#date-to');
            if ($from.hasClass('hasDatepicker')) {
                $from.datepicker('destroy');
            }
            if ($to.hasClass('hasDatepicker')) {
                $to.datepicker('destroy');
            }
        });

        // Number-only validation for financial inputs (same as create page)
        function enforcePositiveNumberOnly(input) {
          input.addEventListener('input', function() {
            let value = this.value;
            // Remove any non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            // Only allow one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
              value = parts[0] + '.' + parts.slice(1).join('');
            }
            // Ensure non-negative
            const num = parseFloat(value);
            if (num < 0 || isNaN(num)) {
              value = '';
            }
            this.value = value;
          });
          
          input.addEventListener('blur', function() {
            if (this.value === '' || this.value === '.') {
              this.value = '0.00';
            } else {
              const num = parseFloat(this.value);
              this.value = isNaN(num) ? '0.00' : num.toFixed(2);
            }
          });
          
          // Prevent negative numbers on paste
          input.addEventListener('paste', function() {
            setTimeout(() => {
              let value = this.value.replace(/[^0-9.]/g, '');
              const num = parseFloat(value);
              if (num < 0 || isNaN(num)) {
                this.value = '0.00';
              }
            }, 0);
          });
        }
        
        // Apply number-only validation to all financial inputs
        document.addEventListener('DOMContentLoaded', function() {
          document.querySelectorAll('.number-only-input:not([readonly])').forEach(function(input) {
            enforcePositiveNumberOnly(input);
          });
        });
    </script>
@endpush
