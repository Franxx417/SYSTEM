<!-- System Logs Management -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>System Logs</h6>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="log-level-filter" style="width: auto;">
                        <option value="">All Levels</option>
                        <option value="error">Error</option>
                        <option value="warning">Warning</option>
                        <option value="info">Info</option>
                        <option value="debug">Debug</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary" data-action="refresh-logs">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                    <a href="{{ route('superadmin.logs') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>View Full Logs
                    </a>
                    <button class="btn btn-sm btn-outline-danger" data-action="clear-logs">
                        <i class="fas fa-trash me-1"></i>Clear Logs
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="logs-container">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <div>Click "Refresh" to load recent log entries</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Log Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Total Entries</span>
                    <span class="badge bg-primary" data-log-stat="total">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Errors</span>
                    <span class="badge bg-danger" data-log-stat="error">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Warnings</span>
                    <span class="badge bg-warning" data-log-stat="warning">0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Info</span>
                    <span class="badge bg-info" data-log-stat="info">0</span>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Log Settings</h6>
            </div>
            <div class="card-body">
                <form id="log-settings-form">
                    <div class="mb-3">
                        <label class="form-label">Log Level</label>
                        <select class="form-select form-select-sm" name="log_level">
                            <option value="debug">Debug</option>
                            <option value="info" selected>Info</option>
                            <option value="warning">Warning</option>
                            <option value="error">Error</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Log Size (MB)</label>
                        <input type="number" class="form-control form-control-sm" name="max_log_size" value="10" min="1" max="100">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_rotate" checked>
                            <label class="form-check-label">Auto-rotate logs</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i>Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
