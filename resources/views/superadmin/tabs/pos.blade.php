{{-- Purchase Orders Management Tab --}}
<div class="row g-3">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>All Purchase Orders</h6>
                <a href="{{ route('po.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i>Go to Purchase Orders
                </a>
            </div>
            <div class="card-body">
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center py-5">
                        <img src="{{ asset('images/redirect.svg') }}" alt="Redirect" class="img-fluid mb-4" style="max-width: 200px;">
                        <h4>Purchase Orders Management</h4>
                        <p class="text-muted">All purchase order management has been consolidated to the main Purchase Orders section.</p>
                        <p>Please use the button below to access the Purchase Orders management page:</p>
                        <a href="{{ route('po.index') }}" class="btn btn-primary">
                            <i class="fas fa-external-link-alt me-1"></i>Go to Purchase Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Automatically redirect to the PO index page
    document.addEventListener('DOMContentLoaded', function() {
        window.location.href = "{{ route('po.index') }}";
    });
</script>
                                    <div>
                                        <div class="small">Approved</div>
                                        <div class="h4">{{ DB::table('approvals')->join('statuses', 'approvals.status_id', '=', 'statuses.status_id')->where('statuses.status_name', 'Approved')->count() }}</div>
                                    </div>
                                    <i class="fas fa-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small">Received</div>
                                        <div class="h4">{{ DB::table('approvals')->whereNotNull('received_at')->count() }}</div>
                                    </div>
                                    <i class="fas fa-truck fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    // Fetch all purchase orders with complete details
                    $allPOs = DB::table('purchase_orders as po')
                        ->leftJoin('approvals as ap', function($join) {
                            $join->on('ap.purchase_order_id', '=', 'po.purchase_order_id')
                                 ->whereRaw('ap.prepared_at = (SELECT MAX(prepared_at) FROM approvals WHERE purchase_order_id = po.purchase_order_id)');
                        })
                        ->leftJoin('statuses as st', 'st.status_id', '=', 'ap.status_id')
                        ->leftJoin('suppliers as s', 's.supplier_id', '=', 'po.supplier_id')
                        ->leftJoin('users as u', 'u.user_id', '=', 'po.requestor_id')
                        ->select(
                            'po.purchase_order_no',
                            'po.purpose',
                            'po.total',
                            'po.created_at',
                            'st.status_name',
                            's.name as supplier_name',
                            's.contact_person as supplier_contact',
                            'u.name as requestor_name',
                            'u.department as requestor_dept',
                            'ap.prepared_at',
                            'ap.verified_at',
                            'ap.approved_at',
                            'ap.received_at'
                        )
                        ->orderByDesc('po.created_at')
                        ->get();
                @endphp

                <div class="table-responsive" id="poTableContainer">
                    <table class="table table-hover align-middle" id="poTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="10%">PO Number</th>
                                <th width="20%">Purpose</th>
                                <th width="12%">Supplier</th>
                                <th width="10%">Requestor</th>
                                <th width="8%">Status</th>
                                <th width="10%" class="text-end">Total Amount</th>
                                <th width="10%">Created</th>
                                <th width="10%">Last Updated</th>
                                <th width="10%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allPOs as $po)
                                <tr data-po-number="{{ $po->purchase_order_no }}" data-status="{{ $po->status_name ?? 'No Status' }}">
                                    <td>
                                        <div class="fw-bold text-primary">{{ $po->purchase_order_no }}</div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;" title="{{ $po->purpose }}">
                                            {{ $po->purpose ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $po->supplier_name ?? 'N/A' }}</div>
                                        @if($po->supplier_contact)
                                            <small class="text-muted d-block">{{ $po->supplier_contact }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $po->requestor_name ?? 'N/A' }}</div>
                                        @if($po->requestor_dept)
                                            <small class="text-muted d-block">{{ $po->requestor_dept }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="cursor: pointer;" 
                                             data-po="{{ $po->purchase_order_no }}" 
                                             data-current-status="{{ $po->status_name ?? 'Unknown' }}"
                                             onclick="showStatusChangeModal('{{ $po->purchase_order_no }}', '{{ $po->status_name ?? 'Unknown' }}')">
                                            @if(isset($po->status_name))
                                                @include('partials.status-display', ['status' => $po->status_name, 'type' => 'text'])
                                            @else
                                                <span class="text-muted">No Status</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold">₱{{ number_format($po->total ?? 0, 2) }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $po->created_at ? \Carbon\Carbon::parse($po->created_at)->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $po->created_at ? \Carbon\Carbon::parse($po->created_at)->format('h:i A') : '' }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $latestUpdate = $po->received_at ?? $po->approved_at ?? $po->verified_at ?? $po->prepared_at ?? $po->created_at;
                                        @endphp
                                        @if($latestUpdate)
                                            <div>{{ \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($latestUpdate)->format('h:i A') }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm action-buttons" role="group">
                                            <button type="button" 
                                                    class="btn btn-outline-primary action-btn btn-view-po" 
                                                    title="View Details"
                                                    data-po="{{ $po->purchase_order_no }}">
                                                <i class="fas fa-eye"></i>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-warning action-btn" 
                                                    title="Edit"
                                                    data-po="{{ $po->purchase_order_no }}"
                                                    onclick="showEditModal('{{ $po->purchase_order_no }}')">
                                                <i class="fas fa-edit"></i>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                            </button>                                            <a href="{{ route('po.print', $po->purchase_order_no) }}" 
                                               class="btn btn-outline-info action-btn" 
                                               title="Print"
                                               target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger action-btn" 
                                                    title="Delete"
                                                    data-po="{{ $po->purchase_order_no }}"
                                                    onclick="deletePO('{{ $po->purchase_order_no }}', '{{ addslashes($po->purpose) }}')">
                                                <i class="fas fa-trash"></i>
                                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-file-invoice fa-3x mb-3 d-block opacity-50"></i>
                                        <h5>No Purchase Orders Found</h5>
                                        <p class="mb-0">Use the "New PO" button in the sidebar to create your first purchase order.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Showing <span id="currentRange">1-<span id="currentPageEnd">10</span></span> of <span id="totalRows">{{ count($allPOs) }}</span> purchase orders
                        <span class="d-none d-md-inline ms-2">(Page <span id="currentPageNum">1</span> of <span id="totalPages">1</span>)</span>
                    </div>
                    
                    <!-- Pagination Navigation -->
                    <nav aria-label="Purchase orders pagination">
                        <ul class="pagination pagination-sm mb-0" id="paginationContainer">
                            <li class="page-item disabled" id="prevPage">
                                <a class="page-link" href="#" tabindex="-1">
                                    <i class="fas fa-chevron-left"></i>
                                    <span class="d-none d-sm-inline ms-1">Previous</span>
                                </a>
                            </li>
                            <!-- Page numbers will be inserted here by JavaScript -->
                            <li class="page-item disabled" id="nextPage">
                                <a class="page-link" href="#">
                                    <span class="d-none d-sm-inline me-1">Next</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <select class="form-select form-select-sm" id="itemsPerPage" style="width: auto;">
                            <option value="10" selected>10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportPOs()">
                            <i class="fas fa-download"></i>
                            <span class="d-none d-md-inline ms-1">Export CSV</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <div class="list-group" id="statusOptionsContainer">
                            <!-- Status options will be populated here -->
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

<script>
// Pagination state
let currentPage = 1;
let itemsPerPage = 10;
let filteredRows = [];

// Initialize pagination
document.addEventListener('DOMContentLoaded', function() {
    initializePagination();
    
    // Items per page change
    document.getElementById('itemsPerPage')?.addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        applyPagination();
    });
});

// Search functionality
document.getElementById('searchPO')?.addEventListener('input', function(e) {
    currentPage = 1;
    filterTable();
});

// Status filter functionality
document.getElementById('filterStatus')?.addEventListener('change', function(e) {
    currentPage = 1;
    filterTable();
});

function initializePagination() {
    const table = document.getElementById('poTable');
    const rows = Array.from(table?.querySelectorAll('tbody tr') || []);
    filteredRows = rows.filter(row => !row.querySelector('td[colspan]'));
    applyPagination();
}

function filterTable() {
    const searchTerm = document.getElementById('searchPO')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('filterStatus')?.value || '';
    const table = document.getElementById('poTable');
    const rows = Array.from(table?.querySelectorAll('tbody tr') || []);
    
    filteredRows = rows.filter(row => {
        // Skip empty state row
        if (row.querySelector('td[colspan]')) {
            return false;
        }
        
        const poNumber = row.dataset.poNumber?.toLowerCase() || '';
        const status = row.dataset.status || '';
        const purpose = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
        const supplier = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
        
        const matchesSearch = poNumber.includes(searchTerm) || 
                            purpose.includes(searchTerm) || 
                            supplier.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    // Check if we need to show "no results"
    const tbody = table?.querySelector('tbody');
    const existingNoResults = tbody?.querySelector('.no-results-row');
    
    if (filteredRows.length === 0 && tbody && !existingNoResults) {
        rows.forEach(row => row.style.display = 'none');
        const noResultsRow = document.createElement('tr');
        noResultsRow.className = 'no-results-row';
        noResultsRow.innerHTML = `
            <td colspan="9" class="text-center text-muted py-4">
                <i class="fas fa-search fa-2x mb-2 d-block opacity-50"></i>
                <h6>No Purchase Orders Match Your Filters</h6>
                <p class="mb-0">Try adjusting your search or filter criteria</p>
            </td>
        `;
        tbody.appendChild(noResultsRow);
    } else if (filteredRows.length > 0 && existingNoResults) {
        existingNoResults.remove();
    }
    
    applyPagination();
}

function applyPagination() {
    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
    
    // Ensure current page is valid
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }
    
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
    
    // Hide all rows first
    const table = document.getElementById('poTable');
    const allRows = table?.querySelectorAll('tbody tr') || [];
    allRows.forEach(row => {
        if (!row.querySelector('td[colspan]')) {
            row.style.display = 'none';
        }
    });
    
    // Show only current page rows
    filteredRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        }
    });
    
    // Update pagination info
    document.getElementById('currentPageEnd').textContent = endIndex;
    document.getElementById('currentRange').innerHTML = totalItems > 0 ? `${startIndex + 1}-<span id="currentPageEnd">${endIndex}</span>` : '0-0';
    document.getElementById('totalRows').textContent = totalItems;
    document.getElementById('currentPageNum').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages;
    
    // Update pagination buttons
    updatePaginationButtons(totalPages);
}

function updatePaginationButtons(totalPages) {
    const paginationContainer = document.getElementById('paginationContainer');
    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');
    
    // Clear existing page numbers
    const existingPages = paginationContainer.querySelectorAll('.page-number');
    existingPages.forEach(page => page.remove());
    
    // Update prev/next button states
    if (currentPage === 1) {
        prevPage.classList.add('disabled');
    } else {
        prevPage.classList.remove('disabled');
    }
    
    if (currentPage === totalPages) {
        nextPage.classList.add('disabled');
    } else {
        nextPage.classList.remove('disabled');
    }
    
    // Add click handlers for prev/next
    prevPage.querySelector('a').onclick = (e) => {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            applyPagination();
            scrollToTable();
        }
    };
    
    nextPage.querySelector('a').onclick = (e) => {
        e.preventDefault();
        if (currentPage < totalPages) {
            currentPage++;
            applyPagination();
            scrollToTable();
        }
    };
    
    // Generate page numbers (with smart ellipsis)
    const maxVisiblePages = window.innerWidth < 576 ? 3 : 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
    let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
    
    if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
    }
    
    // Always show first page
    if (startPage > 1) {
        addPageButton(1, paginationContainer, nextPage);
        if (startPage > 2) {
            addEllipsis(paginationContainer, nextPage);
        }
    }
    
    // Show page numbers
    for (let i = startPage; i <= endPage; i++) {
        addPageButton(i, paginationContainer, nextPage);
    }
    
    // Always show last page
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            addEllipsis(paginationContainer, nextPage);
        }
        addPageButton(totalPages, paginationContainer, nextPage);
    }
}

function addPageButton(pageNum, container, beforeElement) {
    const li = document.createElement('li');
    li.className = `page-item page-number ${pageNum === currentPage ? 'active' : ''}`;
    li.innerHTML = `<a class="page-link" href="#">${pageNum}</a>`;
    li.onclick = (e) => {
        e.preventDefault();
        currentPage = pageNum;
        applyPagination();
        scrollToTable();
    };
    container.insertBefore(li, beforeElement);
}

function addEllipsis(container, beforeElement) {
    const li = document.createElement('li');
    li.className = 'page-item disabled page-number';
    li.innerHTML = '<a class="page-link" href="#">...</a>';
    container.insertBefore(li, beforeElement);
}

function scrollToTable() {
    const table = document.getElementById('poTableContainer');
    if (table) {
        table.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Export to CSV functionality
function exportPOs() {
    const table = document.getElementById('poTable');
    const rows = table?.querySelectorAll('tbody tr');
    
    if (!rows || rows.length === 0) {
        alert('No purchase orders to export');
        return;
    }
    
    let csv = 'PO Number,Purpose,Supplier,Contact,Requestor,Department,Status,Total Amount,Created Date,Last Updated\n';
    
    rows.forEach(row => {
        // Skip hidden rows and empty state
        if (row.style.display === 'none' || row.querySelector('td[colspan]')) {
            return;
        }
        
        const cells = row.querySelectorAll('td');
        if (cells.length < 9) return;
        
        const poNumber = cells[0].textContent.trim();
        const purpose = cells[1].querySelector('div')?.textContent.trim() || '';
        const supplier = cells[2].querySelector('.fw-medium')?.textContent.trim() || '';
        const contact = cells[2].querySelector('small')?.textContent.trim() || '';
        const requestor = cells[3].querySelector('div')?.textContent.trim() || '';
        const department = cells[3].querySelector('small')?.textContent.trim() || '';
        const status = cells[4].textContent.trim();
        const amount = cells[5].textContent.trim();
        const created = cells[6].querySelector('div')?.textContent.trim() || '';
        const updated = cells[7].querySelector('div')?.textContent.trim() || '';
        
        // Escape commas in text
        const escapeCsv = (text) => {
            text = text.replace(/"/g, '""');
            return text.includes(',') ? `"${text}"` : text;
        };
        
        csv += `${escapeCsv(poNumber)},${escapeCsv(purpose)},${escapeCsv(supplier)},${escapeCsv(contact)},${escapeCsv(requestor)},${escapeCsv(department)},${escapeCsv(status)},${escapeCsv(amount)},${escapeCsv(created)},${escapeCsv(updated)}\n`;
    });
    
    // Create download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    const timestamp = new Date().toISOString().slice(0, 10);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `purchase_orders_${timestamp}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Add keyboard shortcut for search (Ctrl+F or Cmd+F)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'f' && document.getElementById('searchPO')) {
        e.preventDefault();
        document.getElementById('searchPO').focus();
    }
});

// Delete PO Modal Handler (matching po-index.js behavior)
window.deletePO = function deletePO(poNo, purpose){
    var form = document.getElementById('deletePOForm');
    if (form) form.action = '/po/' + poNo;
    var el;
    if(el = document.getElementById('delete_po_number')) el.textContent = poNo;
    if(el = document.getElementById('delete_po_purpose')) el.textContent = purpose;
    var modal = new bootstrap.Modal(document.getElementById('deletePOModal'));
    modal.show();
};

// Handle delete form submission with animation
document.getElementById('deletePOForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
    submitBtn.disabled = true;
    
    fetch(form.action, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('deletePOModal'));
        modal.hide();
        
        // Show success toast
        showToast('Purchase order deleted successfully', 'success');
        
        // Remove row with animation
        const poNo = form.action.split('/').pop();
        const row = document.querySelector(`tr[data-po-number="${poNo}"]`);
        if (row) {
            row.style.transition = 'all 0.3s ease-out';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                row.remove();
                initializePagination();
            }, 300);
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('Failed to delete purchase order. Please try again.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// View PO button handler (uses the global poModal from layouts/app.blade.php)
document.addEventListener('click', function(e) {
    // Check if clicked element or its parent is the view button
    const viewBtn = e.target.classList.contains('btn-view-po') ? e.target : e.target.closest('.btn-view-po');
    if (!viewBtn) return;
    
    // Prevent default action and stop propagation
    e.preventDefault();
    e.stopPropagation();
    
    const poNo = viewBtn.getAttribute('data-po');
    if (!poNo) {
        console.error('No PO number found on button');
        return;
    }
    
    console.log('Opening PO modal for:', poNo);
    
    // Show loading state
    const icon = viewBtn.querySelector('i.fa-eye');
    const spinner = viewBtn.querySelector('.spinner-border');
    const originalIcon = icon ? icon.className : '';
    
    if (icon) {
        icon.classList.add('d-none');
    }
    if (spinner) {
        spinner.classList.remove('d-none');
    }
    viewBtn.disabled = true;
    
    // Fetch PO data using the same endpoint as po/index.blade.php
    const url = `/po/${poNo}/json`;
    console.log('Fetching from:', url);
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            
            // Populate the global poModal (same as po-index.js)
            const modal = document.getElementById('poModal');
            if (!modal) {
                console.error('poModal not found in layout');
                showToast('Modal not found. Opening in new page...', 'warning');
                setTimeout(() => {
                    window.location.href = `/po/${poNo}`;
                }, 1000);
                return;
            }
            
            // Fill modal with data (matches po-index.js behavior exactly)
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent && data.html) {
                modalContent.innerHTML = data.html;
                
                // Show modal
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            } else {
                console.warn('No HTML data in response');
                showToast('Invalid response. Opening in new page...', 'warning');
                setTimeout(() => {
                    window.location.href = `/po/${poNo}`;
                }, 1000);
                return;
            }
            
            // Reset button state
            if (icon) {
                icon.classList.remove('d-none');
            }
            if (spinner) {
                spinner.classList.add('d-none');
            }
            viewBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching PO:', error);
            
            // Reset button state
            if (icon) {
                icon.classList.remove('d-none');
            }
            if (spinner) {
                spinner.classList.add('d-none');
            }
            viewBtn.disabled = false;
            
            showToast('Failed to load purchase order. Opening in new page...', 'warning');
            // Fallback to regular page navigation
            setTimeout(() => {
                window.location.href = `/po/${poNo}`;
            }, 1000);
        });
});

// Show toast notification
function showToast(message, type = 'success') {
    const existingToasts = document.querySelectorAll('.action-toast');
    existingToasts.forEach(toast => toast.remove());
    
    const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const bgMap = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
    };
    
    const toastHtml = `
        <div class="toast action-toast show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
            <div class="toast-header ${bgMap[type]} text-white">
                <i class="fas ${iconMap[type]} me-2"></i>
                <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.querySelector('.action-toast');
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
    
    toast.show();
}

// Edit PO Modal Handler (matching po-index.js behavior)
window.showEditModal = function(poId) {
    try {
        // For simplicity in the superadmin view, navigate to the edit page
        // This maintains consistency while keeping the implementation clean
        window.location.href = '/po/' + poId + '/edit';
    } catch (e) {
        console.error('Error opening edit modal:', e);
        showToast('Could not open edit modal', 'error');
    }
};

// Status change modal handler
window.showStatusChangeModal = function(poNumber, currentStatus) {
    document.getElementById('status_po_number').textContent = poNumber;
    document.getElementById('status_current').textContent = currentStatus;
    document.getElementById('selected_status_id').value = '';
    document.getElementById('status_remarks').value = '';
    document.getElementById('confirmStatusChange').disabled = true;
    
    // Fetch available statuses
    fetch('/api/statuses')
        .then(response => response.json())
        .then(statuses => {
            const container = document.getElementById('statusOptionsContainer');
            container.innerHTML = '';
            
            statuses.forEach(status => {
                const isActive = status.status_name === currentStatus;
                const div = document.createElement('div');
                div.className = `list-group-item list-group-item-action status-option ${isActive ? 'active' : ''}`;
                div.setAttribute('data-status-id', status.status_id);
                div.setAttribute('data-status-name', status.status_name);
                div.style.cursor = 'pointer';
                div.onclick = function() { selectStatus(this); };
                
                div.innerHTML = `
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${status.status_name}</h6>
                    </div>
                    <p class="mb-0 text-muted small">${status.description || 'Status description'}</p>
                `;
                
                container.appendChild(div);
            });
        })
        .catch(error => {
            console.error('Error fetching statuses:', error);
            showToast('Failed to load statuses', 'error');
        });
    
    const modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
    modal.show();
};

// Select status function
window.selectStatus = function(element) {
    // Remove active class from all options
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.remove('active');
    });
    
    // Add active class to selected
    element.classList.add('active');
    
    // Update hidden input
    const statusId = element.getAttribute('data-status-id');
    const statusName = element.getAttribute('data-status-name');
    document.getElementById('selected_status_id').value = statusId;
    
    // Enable confirm button
    document.getElementById('confirmStatusChange').disabled = false;
};

// Confirm status change
document.getElementById('confirmStatusChange')?.addEventListener('click', function() {
    const poNumber = document.getElementById('status_po_number').textContent;
    const statusId = document.getElementById('selected_status_id').value;
    const remarks = document.getElementById('status_remarks').value;
    
    if (!statusId) {
        showToast('Please select a status', 'warning');
        return;
    }
    
    const btn = this;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
    btn.disabled = true;
    
    // Submit status change
    fetch(`/po/${poNumber}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status_id: statusId,
            remarks: remarks
        })
    })
    .then(response => response.json())
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('statusChangeModal'));
        modal.hide();
        
        showToast('Status updated successfully', 'success');
        
        // Reload the page to show updated status
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showToast('Failed to update status. Please try again.', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

</script>

<style>
/* Table styling */
#poTable tbody tr {
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
}

#poTable tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transform: scale(1.005);
}

#poTable thead th {
    position: sticky;
    top: 0;
    background-color: #212529;
    z-index: 10;
    white-space: nowrap;
}

.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Focus states */
#searchPO:focus,
#filterStatus:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Pagination styling */
.pagination {
    flex-wrap: wrap;
    gap: 0.25rem;
}

.pagination .page-link {
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}

.pagination .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    font-weight: 600;
}

.pagination .page-link:hover:not(.disabled) {
    background-color: #e9ecef;
    border-color: #0d6efd;
    transform: translateY(-1px);
}

/* Responsive table container */
#poTableContainer {
    position: relative;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Summary cards responsive */
@media (max-width: 768px) {
    .card.bg-primary, 
    .card.bg-warning, 
    .card.bg-success, 
    .card.bg-info {
        margin-bottom: 0.75rem;
    }
    
    #poTable thead th {
        font-size: 0.875rem;
        padding: 0.5rem 0.25rem;
    }
    
    #poTable tbody td {
        font-size: 0.875rem;
        padding: 0.5rem 0.25rem;
    }
    
    .pagination .page-link {
        padding: 0.375rem 0.625rem;
        font-size: 0.875rem;
    }
}

/* Mobile optimizations */
@media (max-width: 576px) {
    #searchPO {
        width: 100% !important;
        margin-bottom: 0.5rem;
    }
    
    #filterStatus {
        width: 100% !important;
    }
    
    .card-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.75rem;
    }
    
    #poTable {
        font-size: 0.8125rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.375rem;
        font-size: 0.75rem;
    }
    
    /* Hide less critical columns on very small screens */
    #poTable th:nth-child(7),
    #poTable td:nth-child(7),
    #poTable th:nth-child(8),
    #poTable td:nth-child(8) {
        display: none;
    }
}

/* Tablet optimizations */
@media (min-width: 577px) and (max-width: 992px) {
    #poTable {
        font-size: 0.9rem;
    }
}

/* Smooth scrolling for table navigation */
html {
    scroll-behavior: smooth;
}

/* Loading state for pagination */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#poTable tbody tr {
    animation: fadeIn 0.3s ease-in-out;
}

/* Improved button group on mobile */
.btn-group-sm {
    gap: 0.25rem;
}

/* Enhanced action buttons */
.btn-group .btn {
    transition: all 0.2s;
}

.btn-group .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Status badge enhancements */
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

/* Table scroll indicator */
#poTableContainer::-webkit-scrollbar {
    height: 8px;
}

#poTableContainer::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#poTableContainer::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#poTableContainer::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Ellipsis styling */
.page-item.disabled .page-link {
    opacity: 0.6;
}

/* Action button enhancements */
.action-btn {
    position: relative;
    overflow: hidden;
    min-width: 36px;
    min-height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.action-btn:disabled {
    cursor: not-allowed;
    pointer-events: none;
}

.action-btn:active:not(:disabled) {
    transform: scale(0.95);
}

/* Ripple effect */
.ripple {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.6);
    pointer-events: none;
    animation: ripple-animation 0.6s ease-out;
}

@keyframes ripple-animation {
    from {
        transform: scale(0);
        opacity: 1;
    }
    to {
        transform: scale(2);
        opacity: 0;
    }
}

/* Loading spinner */
.action-btn .spinner-border-sm {
    width: 0.875rem;
    height: 0.875rem;
    border-width: 0.15em;
}

/* Button group enhancements for touch devices */
@media (max-width: 768px) {
    .action-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .action-btn {
        min-width: 40px;
        min-height: 40px;
        flex: 0 0 auto;
    }
}

/* Hover effects for desktop */
@media (hover: hover) {
    .action-btn:hover:not(:disabled) {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
}

/* Focus states for accessibility */
.action-btn:focus {
    outline: 2px solid #0d6efd;
    outline-offset: 2px;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.action-btn:focus:not(:focus-visible) {
    outline: none;
    box-shadow: none;
}

/* Toast notifications */
.action-toast {
    min-width: 300px;
    max-width: 400px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-header i {
    font-size: 1.1rem;
}

/* Confirmation modal enhancements */
#confirmationModal .modal-content {
    border: none;
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
}

#confirmationModal .modal-header {
    border-bottom: none;
}

#confirmationModal .modal-footer {
    border-top: 1px solid #dee2e6;
}

/* Button press effect */
.btn:active:not(:disabled) {
    transform: scale(0.98);
}

/* Disabled state clarity */
.action-btn:disabled .fas {
    opacity: 0.5;
}

/* Success state */
.action-btn.btn-success {
    animation: pulse 0.5s ease-out;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

/* Error shake animation */
.action-btn.error-shake {
    animation: shake 0.5s ease-out;
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    10%, 30%, 50%, 70%, 90% {
        transform: translateX(-5px);
    }
    20%, 40%, 60%, 80% {
        transform: translateX(5px);
    }
}

/* Row removal animation */
@keyframes fadeOutLeft {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(-20px);
    }
}

/* Smooth transitions */
.action-btn,
.action-btn i,
.action-btn .spinner-border {
    transition: all 0.2s ease-in-out;
}

/* Status option styling (matching po/index.blade.php) */
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

/* Status change modal */
#statusChangeModal .list-group-item {
    border: 1px solid #e9ecef;
    margin-bottom: 0.5rem;
    border-radius: 0.375rem;
}

#statusChangeModal .list-group-item:last-child {
    margin-bottom: 0;
}
</style>

<!-- Toast Container for Notifications -->
<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 5;"></div>

@push('scripts')
<script src="{{ asset('js/dashboards/superadmin-po-actions.js') }}"></script>
@endpush
