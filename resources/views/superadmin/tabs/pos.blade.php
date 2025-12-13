{{-- Purchase Orders Management Tab - Redirects to main PO page --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>All Purchase Orders</h6>
            <div class="text-muted small">System-wide purchase orders</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" data-action="po-refresh">
            <i class="fas fa-sync me-1"></i>Refresh
        </button>
    </div>
    <div class="card-body">
        <form id="po-system-filters" class="row g-2 align-items-end">
            <div class="col-lg-4">
                <label class="form-label small mb-1">Search</label>
                <input type="text" class="form-control" name="search" placeholder="PO number, purpose, vendor...">
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small mb-1">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label small mb-1">Vendor</label>
                <select class="form-select" name="supplier_id">
                    <option value="">All</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label small mb-1">Date Range</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" name="date_from">
                    <input type="date" class="form-control" name="date_to">
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label small mb-1">Amount</label>
                <div class="d-flex gap-2">
                    <input type="number" class="form-control" name="min_total" placeholder="Min" step="0.01" min="0">
                    <input type="number" class="form-control" name="max_total" placeholder="Max" step="0.01" min="0">
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label small mb-1">Per Page</label>
                <select class="form-select" name="per_page">
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i>Apply
                </button>
            </div>
        </form>

        <div class="mt-3" id="po-system-loading" style="display:none;">
            <div class="text-center py-4"><div class="spinner-border spinner-border-sm" role="status"></div> Loading purchase orders...</div>
        </div>

        <div class="mt-3" id="po-system-error" style="display:none;"></div>

        <div class="mt-3" id="po-system-empty" style="display:none;">
            <div class="text-center text-muted py-4">No purchase orders found.</div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-striped table-hover mb-0" id="po-system-table">
                <thead class="table-light">
                    <tr>
                        <th><button type="button" class="btn btn-link btn-sm p-0" data-po-sort="purchase_order_no">PO #</button></th>
                        <th><button type="button" class="btn btn-link btn-sm p-0" data-po-sort="date_requested">Date</button></th>
                        <th><button type="button" class="btn btn-link btn-sm p-0" data-po-sort="supplier_name">Vendor</button></th>
                        <th class="text-end"><button type="button" class="btn btn-link btn-sm p-0" data-po-sort="total">Amount</button></th>
                        <th><button type="button" class="btn btn-link btn-sm p-0" data-po-sort="status_name">Status</button></th>
                    </tr>
                </thead>
                <tbody id="po-system-tbody"></tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <div class="text-muted small" id="po-system-summary"></div>
            <nav aria-label="Purchase orders pagination" id="po-system-pagination"></nav>
        </div>
    </div>
</div>
