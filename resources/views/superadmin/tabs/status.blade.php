{{-- Status Management Tab --}}
@php
    // Get statuses directly from database
    $dbStatuses = DB::table('statuses')->orderBy('status_name')->get();
    
    // Get usage statistics from approvals table
    $statusUsage = [];
    if (Schema::hasTable('approvals')) {
        $usage = DB::table('approvals')
            ->join('statuses', 'statuses.status_id', '=', 'approvals.status_id')
            ->select('statuses.status_name', DB::raw('COUNT(*) as usage_count'))
            ->groupBy('statuses.status_name')
            ->get();
        
        foreach ($usage as $u) {
            $statusUsage[$u->status_name] = $u->usage_count;
        }
    }
    
    $stats = [
        'total_statuses' => count($dbStatuses),
        'total_usage' => array_sum($statusUsage)
    ];
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-traffic-light me-2"></i>Status Configuration</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm" onclick="addNewStatus()">
                        <i class="fas fa-plus"></i> Add Status
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Status Management:</strong> Configure status descriptions and manage all status settings from this interface.
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status Name</th>
                                <th>Description</th>
                                <th>Usage Count</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dbStatuses as $status)
                                <tr>
                                    <td>
                                        <strong>{{ $status->status_name }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $status->description ?? 'No description' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $statusUsage[$status->status_name] ?? 0 }} uses
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="editStatus('{{ $status->status_id }}', '{{ $status->status_name }}', '{{ addslashes($status->description ?? '') }}')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        @if(($statusUsage[$status->status_name] ?? 0) == 0)
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteStatus('{{ $status->status_id }}', '{{ $status->status_name }}')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Status Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="h4 text-primary">{{ $stats['total_statuses'] }}</div>
                        <div class="text-muted small">Total Statuses</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success">{{ $stats['total_usage'] }}</div>
                        <div class="text-muted small">Total Usage</div>
                    </div>
                </div>
                <hr>
                <div class="small">
                    <strong>Usage Breakdown:</strong>
                    <ul class="list-unstyled mt-2">
                        @foreach($dbStatuses as $dbStatus)
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span>{{ $dbStatus->status_name }}</span>
                                <span class="badge bg-secondary">
                                    {{ $statusUsage[$dbStatus->status_name] ?? 0 }} uses
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh Page
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="exportConfig()">
                        <i class="fas fa-download"></i> Export Configuration
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addNewStatus() {
    const statusName = prompt('Enter new status name:');
    if (!statusName || !statusName.trim()) {
        return;
    }
    
    const description = prompt('Enter description (optional):', '');
    
    fetch('/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            status_name: statusName.trim(),
            description: description || ''
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showQuickNotification('Status created successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Failed to create status');
        }
    })
    .catch(error => {
        console.error('[Status] Error creating status:', error);
        showQuickNotification('Error: ' + error.message, 'error');
    });
}

function editStatus(statusId, statusName, description) {
    const newName = prompt('Enter new status name:', statusName);
    if (!newName || !newName.trim()) {
        return;
    }
    
    const newDescription = prompt('Enter new description:', description);
    
    fetch(`/status/${statusId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            _method: 'PUT',
            status_name: newName.trim(),
            description: newDescription || ''
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showQuickNotification('Status updated successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('[Status] Error updating status:', error);
        showQuickNotification('Error: ' + error.message, 'error');
    });
}

function deleteStatus(statusId, statusName) {
    if (!confirm(`Are you sure you want to delete the status "${statusName}"?`)) {
        return;
    }
    
    fetch(`/status/${statusId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            _method: 'DELETE'
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showQuickNotification('Status deleted successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Failed to delete status');
        }
    })
    .catch(error => {
        console.error('[Status] Error deleting status:', error);
        showQuickNotification('Error: ' + error.message, 'error');
    });
}

function exportConfig() {
    fetch('{{ route("status.config") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(config => {
        const dataStr = JSON.stringify(config, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        const url = URL.createObjectURL(dataBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'status_configuration.json';
        link.click();
        URL.revokeObjectURL(url);
        showQuickNotification('Configuration exported', 'success');
    })
    .catch(error => {
        console.error('[Status] Error exporting config:', error);
        showQuickNotification('Error exporting configuration', 'error');
    });
}

function showQuickNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>
