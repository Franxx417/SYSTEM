@extends('layouts.app')
@section('title', 'Status Management')
@section('page_heading', 'Status Management')
@section('page_subheading', 'Manage purchase order statuses and color coding')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Status Configuration</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm" onclick="addNewStatus()">
                        <i class="fas fa-plus"></i> Add Status
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="resetToDefault()">
                        <i class="fas fa-undo"></i> Reset to Default
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Changes to status colors will be applied immediately across the system. 
                    You can drag and drop to reorder statuses.
                </div>

                <div id="status-list" class="sortable-list">
                    @foreach($config['status_order'] as $statusName)
                        @php($statusConfig = $config['status_colors'][$statusName] ?? [])
                        <div class="status-item card mb-3" data-status="{{ $statusName }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        <div class="drag-handle text-muted">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="d-flex align-items-center">
                                            <span class="status-indicator me-2" 
                                                  style="background-color: {{ $statusConfig['color'] ?? '#6c757d' }}; width: 16px; height: 16px; border-radius: 50%; display: inline-block;"></span>
                                            <strong>{{ $statusName }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="color" class="form-control form-control-color status-color" 
                                               value="{{ $statusConfig['color'] ?? '#6c757d' }}" 
                                               data-status="{{ $statusName }}"
                                               title="Status Color">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="color" class="form-control form-control-color text-color" 
                                               value="{{ $statusConfig['text_color'] ?? '#ffffff' }}" 
                                               data-status="{{ $statusName }}"
                                               title="Text Color">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" class="form-control status-description" 
                                               value="{{ $statusConfig['description'] ?? '' }}" 
                                               data-status="{{ $statusName }}"
                                               placeholder="Status description">
                                    </div>
                                    <div class="col-md-1">
                                        <div class="btn-group">
                                            <button class="btn btn-outline-success btn-sm" onclick="saveStatus('{{ $statusName }}')" title="Save">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            @if($statusName !== ($config['default_status'] ?? 'Pending'))
                                            <button class="btn btn-outline-danger btn-sm" onclick="removeStatus('{{ $statusName }}')" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-11 offset-md-1">
                                        <small class="text-muted">
                                            CSS Class: <code>{{ $statusConfig['css_class'] ?? 'status-secondary' }}</code>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Status Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary">{{ $stats['total_statuses'] }}</div>
                        <div class="text-muted small">Total Statuses</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success">{{ $stats['default_status'] }}</div>
                        <div class="text-muted small">Default Status</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="mb-0">Database Statuses</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    @foreach($dbStatuses as $dbStatus)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <strong>{{ $dbStatus->status_name }}</strong>
                                @if($dbStatus->description)
                                    <br><small class="text-muted">{{ $dbStatus->description }}</small>
                                @endif
                            </div>
                            @if(isset($config['status_colors'][$dbStatus->status_name]))
                                <span class="badge bg-success">Configured</span>
                            @else
                                <span class="badge bg-warning">Not Configured</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="mb-0">Preview</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($config['status_order'] as $statusName)
                                @php($statusConfig = $config['status_colors'][$statusName] ?? [])
                                <tr>
                                    <td>{{ $statusName }}</td>
                                    <td>
                                        <span class="badge" 
                                              style="background-color: {{ $statusConfig['color'] ?? '#6c757d' }}; color: {{ $statusConfig['text_color'] ?? '#ffffff' }};">
                                            {{ $statusName }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Status Modal -->
<div class="modal fade" id="addStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addStatusForm">
                    <div class="mb-3">
                        <label class="form-label">Status Name</label>
                        <input type="text" class="form-control" id="newStatusName" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Color</label>
                            <input type="color" class="form-control form-control-color" id="newStatusColor" value="#6c757d">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Text Color</label>
                            <input type="color" class="form-control form-control-color" id="newTextColor" value="#ffffff">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" id="newStatusDescription" placeholder="Optional description">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveNewStatus()">Add Status</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize sortable
const sortable = Sortable.create(document.getElementById('status-list'), {
    handle: '.drag-handle',
    animation: 150,
    onEnd: function(evt) {
        updateStatusOrder();
    }
});

// Update status order
function updateStatusOrder() {
    const statusItems = document.querySelectorAll('.status-item');
    const statusOrder = Array.from(statusItems).map(item => item.dataset.status);
    
    fetch('{{ route("admin.status.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status_order: statusOrder })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Status order updated successfully', 'success');
        } else {
            showNotification('Failed to update status order', 'error');
        }
    });
}

// Save individual status
function saveStatus(statusName) {
    const statusItem = document.querySelector(`[data-status="${statusName}"]`);
    const color = statusItem.querySelector('.status-color').value;
    const textColor = statusItem.querySelector('.text-color').value;
    const description = statusItem.querySelector('.status-description').value;
    
    // Generate CSS class name
    const cssClass = 'status-' + statusName.toLowerCase().replace(/[^a-z0-9]/g, '-');
    
    fetch('{{ route("admin.status.config.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            action: 'update_color',
            status_id: statusName,
            color: color
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Status updated successfully', 'success');
            updateStatusIndicator(statusName, color);
            location.reload(); // Reload to show changes
        } else {
            showNotification('Failed to update status', 'error');
        }
    });
}

// Remove status
function removeStatus(statusName) {
    if (confirm(`Are you sure you want to remove the "${statusName}" status?`)) {
        fetch('{{ route("admin.status.remove") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status_name: statusName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Status removed successfully', 'success');
                document.querySelector(`[data-status="${statusName}"]`).remove();
            } else {
                showNotification(data.message || 'Failed to remove status', 'error');
            }
        });
    }
}

// Add new status
function addNewStatus() {
    const modal = new bootstrap.Modal(document.getElementById('addStatusModal'));
    modal.show();
}

// Save new status
function saveNewStatus() {
    const name = document.getElementById('newStatusName').value;
    const color = document.getElementById('newStatusColor').value;
    const textColor = document.getElementById('newTextColor').value;
    const description = document.getElementById('newStatusDescription').value;
    
    if (!name.trim()) {
        showNotification('Status name is required', 'error');
        return;
    }
    
    const cssClass = 'status-' + name.toLowerCase().replace(/[^a-z0-9]/g, '-');
    
    fetch('{{ route("admin.status.config.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            action: 'update_color',
            status_id: name,
            color: color
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Status added successfully', 'success');
            bootstrap.Modal.getInstance(document.getElementById('addStatusModal')).hide();
            location.reload();
        } else {
            showNotification('Failed to add status', 'error');
        }
    });
}

// Reset to default
function resetToDefault() {
    if (confirm('Are you sure you want to reset all status configurations to default? This cannot be undone.')) {
        fetch('{{ route("admin.status.reset") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Configuration reset to defaults', 'success');
                location.reload();
            } else {
                showNotification('Failed to reset configuration', 'error');
            }
        });
    }
}

// Update status indicator color
function updateStatusIndicator(statusName, color) {
    const indicator = document.querySelector(`[data-status="${statusName}"] .status-indicator`);
    if (indicator) {
        indicator.style.backgroundColor = color;
    }
}

// Show notification
function showNotification(message, type) {
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
    }, 5000);
}

// Real-time color updates
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('status-color')) {
        const statusName = e.target.dataset.status;
        const color = e.target.value;
        updateStatusIndicator(statusName, color);
    }
});
</script>

@endsection
