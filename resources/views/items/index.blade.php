@extends('layouts.app')
@section('title','Items Management')
@section('page_heading','Items Management')
@section('page_subheading','Manage your item catalog')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <!-- Items listing with search and actions -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Items Catalog</h5>
            <a href="{{ route('items.create') }}" class="btn btn-primary">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
</svg>            </a>
        </div>
        <div class="card-body">
            <!-- Search and filters -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="{{ route('items.index') }}" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" 
                               placeholder="Search items by name or description..." 
                               value="{{ $search }}">
                        <button type="submit" class="btn btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
  <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
</svg>
                        </button>
                        @if($search)
                            <a href="{{ route('items.index') }}" class="btn btn-outline-danger ms-2">
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

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Items table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Item Name</th>
                            {{-- <th>Description</th> --}}
                            <th>Unit Price</th>
                            <th>Total Cost</th>
                            <th>Supplier</th>
                            <th>PO Number</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->item_name ?: Str::limit($item->item_description, 50) ?: 'N/A' }}</strong>
                                    @if($item->item_description)
                                        <div class="small text-muted">{{ Str::limit($item->item_description, 50) }}</div>
                                    @endif
                                </td>
                                {{-- <td>{{ Str::limit($item->item_description, 50) }}</td> --}}
                                <td class="text-center">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-center">₱{{ number_format($item->total_cost, 2) }}</td>
                                <td>{{ $item->supplier_name ?: 'N/A' }}</td>
                                <td>
                                    @if($item->purchase_order_no)
                                        <a href="{{ route('po.show', $item->purchase_order_no) }}" 
                                           class="text-decoration-none">
                                            {{ $item->purchase_order_no }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary" title="Edit" 
                                                onclick="editItem('{{ $item->item_id }}', '{{ addslashes($item->item_name ?: $item->item_description) }}', '{{ addslashes($item->item_description) }}', '{{ $item->unit_price }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
  <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
  <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
</svg>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="confirmDelete('{{ $item->item_id }}', '{{ $item->item_name ?: $item->item_description }}')" 
                                                title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16">
  <path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5"/>
</svg></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-box-open fa-2x mb-2"></i><br>
                                    No items found. 
                                    @if($search)
                                        Try adjusting your search terms.
                                    @else
                                        <a href="{{ route('items.create') }}">Add your first item</a>.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Delete confirmation modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this item?</p>
                    <p><strong id="itemName"></strong></p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Item</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editItemForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Item Name</label>
                                <input class="form-control" name="item_name" id="edit_item_name" required maxlength="255" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="item_description" id="edit_item_description" rows="3" maxlength="255"></textarea>
                            </div>
                            <input type="hidden" name="quantity" id="edit_quantity" value="1" />
                            <div class="col-md-6">
                                <label class="form-label">Unit Price</label>
                                <input class="form-control" name="unit_price" id="edit_unit_price" type="number" min="0" step="0.01" required />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="/js/items-index.js"></script>
@endsection



