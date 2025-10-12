@extends('layouts.app')
@section('title','Inventory Summary')
@section('page_heading','Inventory Summary')
@section('page_subheading','Overview of all inventory items grouped by category')
@section('content')
    <!-- Inventory Summary Dashboard -->
    <div class="row g-3 mb-4">
        <!-- Total Items Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="mb-0">{{ number_format($totalItemsCount) }}</h3>
                            <small class="text-muted">All inventory entries</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unique Item Types Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-stack" viewBox="0 0 16 16">
                                    <path d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.6.6 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.6.6 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.6.6 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535z"/>
                                    <path d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.6.6 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Unique Items</h6>
                            <h3 class="mb-0">{{ number_format($uniqueItemTypes) }}</h3>
                            <small class="text-muted">Different item types</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Value Card -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                <span aria-hidden="true" title="Philippine Peso" style="font-size:28px;line-height:1;">₱</span>
                            </div>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Value</h6>
                            <h3 class="mb-0">₱{{ number_format($totalInventoryValue, 2) }}</h3>
                            <small class="text-muted">Inventory worth</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Groups Table -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Inventory Items by Category</h5>
            <a href="{{ route('items.index') }}" class="btn btn-outline-primary btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                </svg> View All Items
            </a>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('items.inventory') }}" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" 
                               placeholder="Search inventory items..." 
                               value="{{ $search }}">
                        <button type="submit" class="btn btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                        </button>
                        @if($search)
                            <a href="{{ route('items.inventory') }}" class="btn btn-outline-danger ms-2">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Inventory Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th class="text-end">Total Quantity</th>
                            <th class="text-end">Avg. Unit Price</th>
                            <th class="text-end">Total Value</th>
                            <th class="text-center">Entries</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryGroups as $group)
                            <tr>
                                <td>
                                    <strong>{{ $group->item_name ?: 'Unnamed Item' }}</strong>
                                </td>
                                <td>
                                    <div class="text-muted small" style="max-width: 300px;">
                                        {{ Str::limit($group->item_description, 80) }}
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-primary rounded-pill">
                                        {{ number_format($group->total_quantity) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    ₱{{ number_format($group->avg_unit_price, 2) }}
                                </td>
                                <td class="text-end">
                                    <strong>₱{{ number_format($group->total_value, 2) }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $group->entry_count }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($group->last_updated)->format('M d, Y') }}
                                    </small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-inbox mb-2" viewBox="0 0 16 16">
                                        <path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438zM3.809 3.563A1.5 1.5 0 0 1 4.981 3h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a.5.5 0 0 1 .105.374l-.39 3.124A1.5 1.5 0 0 1 14.117 13H1.883a1.5 1.5 0 0 1-1.489-1.314l-.39-3.124a.5.5 0 0 1 .106-.374z"/>
                                    </svg>
                                    <br>
                                    No inventory items found.
                                    @if($search)
                                        Try adjusting your search terms.
                                    @else
                                        <a href="{{ route('items.index') }}">Add items to get started</a>.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($inventoryGroups->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Grand Total:</th>
                                <th class="text-end">
                                    <span class="badge bg-primary rounded-pill">
                                        {{ number_format($inventoryGroups->sum('total_quantity')) }}
                                    </span>
                                </th>
                                <th colspan="2" class="text-end">
                                    <strong>₱{{ number_format($inventoryGroups->sum('total_value'), 2) }}</strong>
                                </th>
                                <th class="text-center">
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $inventoryGroups->sum('entry_count') }}
                                    </span>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Info Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                </svg>
                <strong>About Inventory Summary:</strong> This view groups all items by their name and description, showing aggregated quantities and values. The "Entries" column indicates how many individual records exist for each item type across all purchase orders.
            </div>
        </div>
    </div>

    <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert){
                setTimeout(function(){
                    if (alert && alert.parentNode) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
    </script>
@endsection
