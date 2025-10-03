{{-- Purchase Orders Management Tab --}}
<div class="row g-3">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Purchase Orders Management</h6>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-sm btn-primary" onclick="window.location.href='{{ route('po.create') }}'">
                        <i class="fas fa-plus me-1"></i>New PO
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small">Total POs</div>
                                        <div class="h4">{{ $metrics['total_pos'] ?? 0 }}</div>
                                    </div>
                                    <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="small">Pending</div>
                                        <div class="h4">{{ $metrics['pending_pos'] ?? 0 }}</div>
                                    </div>
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
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

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>PO Number</th>
                                <th class="d-none d-md-table-cell">Purpose</th>
                                <th>Status</th>
                                <th class="d-none d-lg-table-cell">Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPOs ?? [] as $po)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $po->purchase_order_no }}</div>
                                        <div class="text-muted small d-md-none">{{ Str::limit($po->purpose ?? '', 30) }}</div>
                                    </td>
                                    <td class="d-none d-md-table-cell">{{ Str::limit($po->purpose ?? '', 50) }}</td>
                                    <td>
                                        @if(isset($po->status_name))
                                            @include('partials.status-display', ['status' => $po->status_name, 'type' => 'badge'])
                                        @else
                                            @include('partials.status-display', ['status' => 'No Status', 'type' => 'badge'])
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">₱{{ number_format($po->total ?? 0, 2) }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('po.show', $po->purchase_order_no) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i><span class="ms-1 d-none d-xl-inline">View</span>
                                            </a>
                                            <a href="{{ route('po.print', $po->purchase_order_no) }}" class="btn btn-outline-secondary" target="_blank">
                                                <i class="fas fa-print"></i><span class="ms-1 d-none d-xl-inline">Print</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-file-invoice fa-2x mb-2 d-block"></i>
                                        No purchase orders found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('po.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list me-1"></i>View All Purchase Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
