<!-- Enhanced Database Management -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-database me-2"></i>Database Overview</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-primary">{{ $dbStats['total_tables'] ?? 0 }}</div>
                            <div class="text-muted small">Total Tables</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-info">{{ $dbStats['total_records'] ?? 0 }}</div>
                            <div class="text-muted small">Total Records</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-warning">{{ $dbStats['db_size'] ?? 'N/A' }}</div>
                            <div class="text-muted small">Database Size</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="h4 text-success">{{ $dbStats['last_backup'] ?? 'Never' }}</div>
                            <div class="text-muted small">Last Backup</div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-outline-primary" data-action="load-table-info">
                        <i class="fas fa-sync-alt me-1"></i>Refresh Table Info
                    </button>
                    <a href="{{ route('superadmin.database') }}" class="btn btn-primary">
                        <i class="fas fa-cog me-1"></i>Database Settings
                    </a>
                </div>
                <div id="table-info" class="table-responsive"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Database Tools</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" data-action="optimize-database">
                        <i class="fas fa-tachometer-alt me-1"></i>Optimize Database
                    </button>
                    <button class="btn btn-outline-info" data-action="check-integrity">
                        <i class="fas fa-check-circle me-1"></i>Check Integrity
                    </button>
                    <button class="btn btn-outline-warning" data-action="repair-tables">
                        <i class="fas fa-wrench me-1"></i>Repair Tables
                    </button>
                    <button class="btn btn-outline-success" data-action="create-backup">
                        <i class="fas fa-download me-1"></i>Create Backup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
