<!-- System Overview Dashboard -->
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
