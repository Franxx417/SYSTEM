@extends('layouts.app')
@section('title', 'Advanced Status Management')
@section('page_heading', 'Advanced Status Management')
@section('page_subheading', 'Comprehensive status configuration and management')

@push('styles')
<style>
    .status-indicator {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    .sortable-list {
        list-style: none;
        padding: 0;
    }
    .sortable-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 0.5rem;
        cursor: move;
        transition: all 0.2s ease;
    }
    .sortable-item:hover {
        background: #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .drag-handle {
        color: #6c757d;
        cursor: grab;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-traffic-light me-2"></i>Status Management</h6>
                <div>
                    <a href="{{ route('admin.status.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Status
                    </a>
                    <button class="btn btn-outline-info btn-sm" onclick="saveOrder()">
                        <i class="fas fa-save me-1"></i>Save Order
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error') || isset($error))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') ?? $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Advanced Status Management:</strong> Drag and drop to reorder statuses, edit individual status properties, and manage the complete workflow.
                </div>

                @if($statuses->count() > 0)
                    <ul class="sortable-list" id="statusList">
                        @foreach($statuses as $status)
                            <li class="sortable-item" data-id="{{ $status->status_id }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-grip-vertical drag-handle me-3"></i>
                                        <span class="status-indicator" style="background-color: {{ $status->color ?? '#6c757d' }};"></span>
                                        <div>
                                            <strong>{{ $status->status_name }}</strong>
                                            @if($status->description)
                                                <br><small class="text-muted">{{ $status->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(isset($statusUsage[$status->status_id]))
                                            <span class="badge bg-info">{{ $statusUsage[$status->status_id] }} uses</span>
                                        @endif
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.status.edit', $status->status_id) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if(!isset($statusUsage[$status->status_id]) || $statusUsage[$status->status_id] == 0)
                                                <button class="btn btn-outline-danger" onclick="deleteStatus('{{ $status->status_id }}', '{{ $status->status_name }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-outline-secondary" disabled title="Cannot delete: Status is in use">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-traffic-light text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">No Statuses Found</h5>
                        <p class="text-muted">Create your first status to get started with workflow management.</p>
                        <a href="{{ route('admin.status.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create First Status
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Status Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 mb-0 text-primary">{{ $statuses->count() }}</div>
                        <small class="text-muted">Total Statuses</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 mb-0 text-success">{{ collect($statusUsage)->sum() }}</div>
                        <small class="text-muted">Total Usage</small>
                    </div>
                </div>

                @if($statuses->count() > 0)
                    <hr>
                    <h6 class="mb-3">Usage Breakdown</h6>
                    @foreach($statuses as $status)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="status-indicator" style="background-color: {{ $status->color ?? '#6c757d' }};"></span>
                                <span>{{ $status->status_name }}</span>
                            </div>
                            <span class="badge bg-secondary">{{ $statusUsage[$status->status_id] ?? 0 }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Quick Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Drag statuses to reorder workflow</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Color-code for visual clarity</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Statuses in use cannot be deleted</li>
                    <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Use descriptions for clarity</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the status "<span id="statusName"></span>"?</p>
                <p class="text-danger small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable
    const statusList = document.getElementById('statusList');
    if (statusList) {
        new Sortable(statusList, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost'
        });
    }
});

function saveOrder() {
    const statusList = document.getElementById('statusList');
    if (!statusList) return;
    
    const order = Array.from(statusList.children).map(item => item.dataset.id);
    
    fetch('{{ route("admin.status.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ order: order })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Status order updated successfully!', 'success');
        } else {
            showAlert('Failed to update status order', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while updating status order', 'danger');
    });
}

function deleteStatus(id, name) {
    document.getElementById('statusName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/status/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    const container = document.querySelector('.card-body');
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
}
</script>
@endpush
