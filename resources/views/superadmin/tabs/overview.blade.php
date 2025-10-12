<!-- System Overview Dashboard -->

<!-- Inventory Summary Section -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-warehouse me-2" viewBox="0 0 16 16">
                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 0a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM8 3a1 1 0 0 1 1 1v.5h.5a.5.5 0 0 1 0 1H9V6a1 1 0 1 1-2 0v-.5h-.5a.5.5 0 0 1 0-1H7V4a1 1 0 0 1 1-1"/>
                    </svg>
                    Inventory Summary
                </h6>
                <a href="{{ route('items.inventory') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>View Full Inventory
                </a>
            </div>
            <div class="card-body">
                @php
                    $totalItemsCount = DB::table('items')->count();
                    $totalInventoryValue = DB::table('items')->sum('total_cost');
                    $uniqueItemTypes = DB::table('items')
                        ->select(DB::raw('COUNT(DISTINCT CONCAT(COALESCE(item_name, \'\'), \'|\', COALESCE(item_description, \'\'))) as count'))
                        ->value('count');
                @endphp

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0 me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-box-seam text-primary" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-muted small">Total Items</div>
                                <div class="h5 mb-0">{{ number_format($totalItemsCount) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0 me-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-stack text-success" viewBox="0 0 16 16">
                                    <path d="m14.12 10.163 1.715.858c.22.11.22.424 0 .534L8.267 15.34a.6.6 0 0 1-.534 0L.165 11.555a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0l5.317-2.66zM7.733.063a.6.6 0 0 1 .534 0l7.568 3.784a.3.3 0 0 1 0 .535L8.267 8.165a.6.6 0 0 1-.534 0L.165 4.382a.299.299 0 0 1 0-.535z"/>
                                    <path d="m14.12 6.576 1.715.858c.22.11.22.424 0 .534l-7.568 3.784a.6.6 0 0 1-.534 0L.165 7.968a.299.299 0 0 1 0-.534l1.716-.858 5.317 2.659c.505.252 1.1.252 1.604 0z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-muted small">Unique Types</div>
                                <div class="h5 mb-0">{{ number_format($uniqueItemTypes) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0 me-3">
                                <span class="text-warning" style="font-size:28px;line-height:1;">₱</span>
                            </div>
                            <div>
                                <div class="text-muted small">Total Value</div>
                                <div class="h5 mb-0">₱{{ number_format($totalInventoryValue, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>System Activity</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="h4 text-primary">{{ $metrics['total_pos'] ?? 0 }}</div>
                            <div class="text-muted small">Total Purchase Orders</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="h4 text-warning">{{ $metrics['pending_pos'] ?? 0 }}</div>
                            <div class="text-muted small">Pending Approvals</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="h4 text-success">{{ $metrics['suppliers'] ?? 0 }}</div>
                            <div class="text-muted small">Active Suppliers</div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Recent Activity</th>
                                <th>User</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity ?? [] as $activity)
                                <tr>
                                    <td>{{ $activity->action ?? 'System activity' }}</td>
                                    <td>{{ $activity->user ?? 'System' }}</td>
                                    <td>{{ $activity->created_at ?? now()->format('H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">No recent activity</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-server me-2"></i>System Health</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Database Connection</span>
                    <span class="badge bg-success">Healthy</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Storage Space</span>
                    <span class="badge bg-warning">75% Used</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Cache Status</span>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Last Backup</span>
                    <span class="text-muted small">{{ $metrics['last_backup'] ?? 'Never' }}</span>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i>User Statistics</h6>
            </div>
            <div class="card-body">
                @foreach($userStats ?? [] as $role => $count)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $role) }}</span>
                        <span class="badge bg-primary">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
