@extends('layouts.app')
@section('title','Dashboard')
@section('page_heading','Dashboard')
@section('page_subheading','System administration and oversight')
@section('content')
<style>
    .metric-card { transition: transform 0.2s; }
    .metric-card:hover { transform: translateY(-2px); }
    .status-indicator { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
    .status-online { background-color: #28a745; }
    .status-warning { background-color: #ffc107; }
    .status-offline { background-color: #dc3545; }
    .nav-pills .nav-link { margin-right: 5px; }
</style>

<!-- System Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">System Status</div>
                        <div class="h5 mb-0 text-success">Online</div>
                    </div>
                    <div class="status-indicator status-online"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="h5 mb-0">{{ $metrics['users'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-users text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Active Sessions</div>
                        <div class="h5 mb-0">{{ $metrics['active_sessions'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-user-clock text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Database Size</div>
                        <div class="h5 mb-0">{{ $metrics['db_size'] ?? 'N/A' }}</div>
                    </div>
                    <i class="fas fa-database text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@php($active = request()->get('tab','overview'))
<ul class="nav nav-pills mb-4">
    <li class="nav-item"><a class="nav-link {{ $active==='overview' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'overview']) }}"><i class="fas fa-tachometer-alt me-1"></i>Overview</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='users' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'users']) }}"><i class="fas fa-users me-1"></i>User Management</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='security' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'security']) }}"><i class="fas fa-shield-alt me-1"></i>Security</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='system' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'system']) }}"><i class="fas fa-cogs me-1"></i>System</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='database' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'database']) }}"><i class="fas fa-database me-1"></i>Database</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='logs' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'logs']) }}"><i class="fas fa-file-alt me-1"></i>Logs</a></li>
    <li class="nav-item"><a class="nav-link {{ $active==='branding' ? 'active' : '' }}" href="{{ route('dashboard',['tab'=>'branding']) }}"><i class="fas fa-palette me-1"></i>Branding</a></li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    @include('dashboards.superadmin.tabs.' . $active, ['active' => $active])
</div>

<script>
// Enhanced Superadmin JavaScript Functions
function viewPO(poNo) {
    window.open('/po/' + poNo, '_blank');
}

// User Management Functions
function resetPassword(userId) {
    if (confirm('Reset password for this user?')) {
        fetch('{{ route("superadmin.system") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: 'action=reset_password&user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password reset successfully. New password: ' + data.new_password);
            } else {
                alert('Failed to reset password: ' + (data.error || 'Unknown error'));
            }
        });
    }
}

function toggleUser(userId) {
    if (confirm('Toggle user status?')) {
        fetch('{{ route("superadmin.system") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: 'action=toggle_user&user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to toggle user: ' + (data.error || 'Unknown error'));
            }
        });
    }
}

function loadTableInfo() {
    const tableInfoDiv = document.getElementById('table-info');
    if (!tableInfoDiv) return;
    
    tableInfoDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading...</div>';
    
    fetch('{{ route("superadmin.database.info") }}')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                tableInfoDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                return;
            }
            
            let html = '<table class="table table-sm table-striped"><thead class="table-dark"><tr><th>Table</th><th>Records</th><th>Columns</th></tr></thead><tbody>';
            
            Object.values(data).forEach(table => {
                html += `<tr>
                    <td><strong>${table.name}</strong></td>
                    <td><span class="badge bg-info">${table.count}</span></td>
                    <td><span class="badge bg-secondary">${table.columns.length}</span></td>
                </tr>`;
            });
            
            html += '</tbody></table>';
            tableInfoDiv.innerHTML = html;
        });
}
</script>
@endsection
