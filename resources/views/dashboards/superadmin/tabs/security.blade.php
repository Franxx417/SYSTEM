<!-- Security Center -->
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h6>
            </div>
            <div class="card-body">
                <form id="security-settings-form" data-validate>
                    @csrf
                    <input type="hidden" name="action" value="update_security" />
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
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-users-slash me-2"></i>Active Sessions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Last Activity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeSessions ?? [] as $session)
                                <tr class="session-item">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                {{ strtoupper(substr($session->username ?? 'U', 0, 1)) }}
                                            </div>
                                            {{ $session->username ?? 'Unknown' }}
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $session->ip_address ?? 'N/A' }}</code>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $session->last_activity ?? 'Now' }}</span>
                                            <button class="btn btn-sm btn-outline-danger session-terminate" 
                                                    data-action="terminate-session" 
                                                    data-session-id="{{ $session->id ?? '' }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No active sessions</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
