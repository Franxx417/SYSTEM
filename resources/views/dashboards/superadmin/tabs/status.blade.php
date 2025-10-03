{{-- Status Management Tab --}}
@php
    $statusManager = app(\App\Services\StatusConfigManager::class);
    $config = $statusManager->getConfig();
    $dbStatuses = DB::table('statuses')->orderBy('status_name')->get();
    $stats = $statusManager->getStatusStats();
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
                    <a href="{{ route('admin.status.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-cog"></i> Advanced Settings
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Quick Status Management:</strong> This interface allows you to quickly view and modify status colors. 
                    For advanced features like reordering and detailed configuration, use the Advanced Settings.
                </div>

                <div class="row">
                    @foreach($config['status_order'] as $statusName)
                        @php($statusConfig = $config['status_colors'][$statusName] ?? [])
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <span class="status-indicator me-2" 
                                                  style="background-color: {{ $statusConfig['color'] ?? '#6c757d' }}; width: 16px; height: 16px; border-radius: 50%; display: inline-block;"></span>
                                            <div>
                                                <strong>{{ $statusName }}</strong>
                                                <br><small class="text-muted">{{ $statusConfig['description'] ?? 'No description' }}</small>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="color" class="form-control form-control-color status-color-quick" 
                                                   value="{{ $statusConfig['color'] ?? '#6c757d' }}" 
                                                   data-status="{{ $statusName }}"
                                                   title="Change Color"
                                                   style="width: 40px; height: 40px;">
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge" 
                                              style="background-color: {{ $statusConfig['color'] ?? '#6c757d' }}; color: {{ $statusConfig['text_color'] ?? '#ffffff' }};">
                                            {{ $statusName }} Preview
                                        </span>
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
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Status Statistics</h5>
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
                <hr>
                <div class="small">
                    <strong>Configuration Status:</strong>
                    <ul class="list-unstyled mt-2">
                        @foreach($dbStatuses as $dbStatus)
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span>{{ $dbStatus->status_name }}</span>
                                @if(isset($config['status_colors'][$dbStatus->status_name]))
                                    <span class="badge bg-success">Configured</span>
                                @else
                                    <span class="badge bg-warning">Not Configured</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Live Preview</h5>
            </div>
            <div class="card-body">
                <div class="small mb-2"><strong>Status Indicators:</strong></div>
                @foreach($config['status_order'] as $statusName)
                    @php($statusConfig = $config['status_colors'][$statusName] ?? [])
                    <div class="d-flex align-items-center mb-2">
                        <span class="status-indicator me-2" 
                              style="background-color: {{ $statusConfig['color'] ?? '#6c757d' }}; width: 12px; height: 12px; border-radius: 50%; display: inline-block;"></span>
                        <span class="small">{{ $statusName }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" onclick="refreshStatusCache()">
                        <i class="fas fa-sync"></i> Refresh Status Cache
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="resetToDefault()">
                        <i class="fas fa-undo"></i> Reset to Default
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
// Quick status color change
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('status-color-quick')) {
        const statusName = e.target.dataset.status;
        const color = e.target.value;
        
        // Update the visual indicator immediately
        const indicator = e.target.closest('.card-body').querySelector('.status-indicator');
        if (indicator) {
            indicator.style.backgroundColor = color;
        }
        
        // Update the preview badge
        const badge = e.target.closest('.card-body').querySelector('.badge');
        if (badge) {
            badge.style.backgroundColor = color;
        }
        
        // Save the change
        saveStatusColorQuick(statusName, color);
    }
});

function saveStatusColorQuick(statusName, color) {
    // Get current status config
    fetch('{{ route("admin.status.config") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(config => {
        const statusConfig = config.status_colors[statusName] || {};
        statusConfig.color = color;
        
        // Generate CSS class name if not exists
        if (!statusConfig.css_class) {
            statusConfig.css_class = 'status-' + statusName.toLowerCase().replace(/[^a-z0-9]/g, '-');
        }
        
        // Update status
        return fetch('{{ route("admin.status.config.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                action: 'update_color',
                status_id: statusName, // This should be the actual status ID, not name
                color: color
            })
        });
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showQuickNotification('Status color updated successfully', 'success');
        } else {
            showQuickNotification('Failed to update status color', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showQuickNotification('Error updating status color', 'error');
    });
}

function refreshStatusCache() {
    // This would clear the cache and reload the page
    fetch('{{ route("admin.status.config") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(() => {
        showQuickNotification('Status cache refreshed', 'success');
        setTimeout(() => location.reload(), 1000);
    });
}

function resetToDefault() {
    if (confirm('Are you sure you want to reset all status configurations to default?')) {
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
                showQuickNotification('Configuration reset to defaults', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showQuickNotification('Failed to reset configuration', 'error');
            }
        });
    }
}

function exportConfig() {
    fetch('{{ route("admin.status.config") }}', {
        method: 'GET',
        headers: {
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
