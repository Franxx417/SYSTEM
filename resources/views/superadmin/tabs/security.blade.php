<!-- Enhanced Security Center -->
<div class="row g-3 mb-4">
    <!-- Security Overview Cards -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="h4 mb-0" id="active-sessions-count">0</div>
                        <div class="small">Active Sessions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="h4 mb-0" id="login-success-rate">0%</div>
                        <div class="small">Login Success Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="h4 mb-0" id="security-alerts-count">0</div>
                        <div class="small">Security Alerts</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="h4 mb-0" id="activities-24h">0</div>
                        <div class="small">Activities (24h)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h6>
                <div class="badge bg-success" id="security-status">Secure</div>
            </div>
            <div class="card-body">
                <form id="security-settings-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Session Timeout (minutes)</label>
                        <input type="number" class="form-control" name="session_timeout" 
                               value="{{ $securitySettings['session_timeout'] ?? 120 }}" 
                               min="5" max="1440" required />
                        <div class="form-text">Time before inactive sessions expire (5-1440 minutes)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Login Attempts</label>
                        <input type="number" class="form-control" name="max_login_attempts" 
                               value="{{ $securitySettings['max_login_attempts'] ?? 5 }}" 
                               min="3" max="10" required />
                        <div class="form-text">Maximum failed login attempts before lockout</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="force_https" 
                                   {{ ($securitySettings['force_https'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label">Force HTTPS</label>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Settings
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-action="force-logout-all">
                            <i class="fas fa-sign-out-alt me-1"></i>Force Logout All
                        </button>
                        <button type="button" class="btn btn-outline-info" data-action="security-refresh">
                            <i class="fas fa-sync me-1"></i>Refresh
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</div>

<!-- Security Alerts Section -->
<div class="row g-3 mt-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Security Alerts</h6>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="alert-severity-filter" style="width: auto;">
                        <option value="">All Severities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary" data-action="security-refresh-alerts">
                        <i class="fas fa-sync me-1"></i>Refresh
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="alerts-container">
                    <div class="text-center text-success py-4">
                        <i class="fas fa-shield-check fa-3x mb-3"></i>
                        <h5>No Security Alerts</h5>
                        <p class="text-muted">Your system is secure with no pending alerts.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Activity Monitor removed -->

<!-- Chart.js removed -->
