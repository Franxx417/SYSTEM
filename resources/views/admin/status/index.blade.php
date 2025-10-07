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
                    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <i class="fas fa-plus me-1"></i>Add Status
                    </button>
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
                            <li class="sortable-item" data-id="{{ $status->status_id }}" data-status-name="{{ $status->status_name }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-grip-vertical drag-handle me-3"></i>
                                        <span class="status-indicator" 
                                              style="background-color: {{ $status->color ?? '#6c757d' }};"
                                              data-status-id="{{ $status->status_id }}"
                                              data-status-name="{{ $status->status_name }}"
                                              data-status-color="{{ $status->color ?? '#6c757d' }}"></span>
                                        <div>
                                            <strong>{{ $status->status_name }}</strong>
                                            @if($status->description)
                                                <br><small class="text-muted">{{ $status->description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        @if(isset($statusUsage[$status->status_id]))
                                            <span class="badge" 
                                                  style="background-color: {{ $status->color ?? '#6c757d' }}; color: #ffffff;"
                                                  data-status-badge="{{ $status->status_name }}">
                                                {{ $statusUsage[$status->status_id] }} uses
                                            </span>
                                        @endif
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editStatus('{{ $status->status_id }}', '{{ $status->status_name }}', '{{ $status->description }}', '{{ $status->color ?? '#007bff' }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
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
                        <button class="btn btn-primary" onclick="openCreateModal()">
                            <i class="fas fa-plus me-1"></i>Create First Status
                        </button>
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
                                <span class="status-indicator" 
                                      style="background-color: {{ $status->color ?? '#6c757d' }};"
                                      data-status-id="{{ $status->status_id }}"
                                      data-status-name="{{ $status->status_name }}"
                                      data-status-color="{{ $status->color ?? '#6c757d' }}"></span>
                                <span>{{ $status->status_name }}</span>
                            </div>
                            <span class="badge" 
                                  style="background-color: {{ $status->color ?? '#6c757d' }}; color: #ffffff;"
                                  data-status-badge="{{ $status->status_name }}">
                                {{ $statusUsage[$status->status_id] ?? 0 }}
                            </span>
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

<!-- Create Status Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createForm" method="POST" action="{{ route('admin.status.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="createStatusName" class="form-label">Status Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="createStatusName" name="status_name" required maxlength="50">
                        <div class="invalid-feedback">Please provide a status name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="createDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="createDescription" name="description" rows="3" maxlength="255"></textarea>
                        <small class="text-muted">Optional description for this status</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="createColor" class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="createColor" name="color" value="#007bff">
                            <input type="text" class="form-control" id="createColorHex" value="#007BFF" readonly>
                        </div>
                        <small class="text-muted">Choose a color to represent this status</small>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Create a new status to manage your workflow more effectively.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editStatusName" class="form-label">Status Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editStatusName" name="status_name" required maxlength="50">
                        <div class="invalid-feedback">Please provide a status name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3" maxlength="255"></textarea>
                        <small class="text-muted">Optional description for this status</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editColor" class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="editColor" name="color" value="#007bff">
                            <input type="text" class="form-control" id="editColorHex" readonly>
                        </div>
                        <small class="text-muted">Choose a color to represent this status</small>
                    </div>
                    
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Changes will be applied immediately to all items using this status.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Status
                    </button>
                </div>
            </form>
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
@vite(['resources/js/status-color-sync.js'])
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
    
    // Listen for color changes from other interfaces
    if (window.statusColorSync) {
        window.statusColorSync.onColorChange(function(data) {
            console.log('Received color change:', data);
            
            // Update all status indicators on this page
            window.statusColorSync.updatePageIndicators(data.statusName, data.color);
            
            // Show notification
            showAlert(`Status "${data.statusName}" color updated from another window`, 'info');
        });
    }
    
    // Color picker sync for Edit modal
    const editColorPicker = document.getElementById('editColor');
    const editColorHex = document.getElementById('editColorHex');
    
    if (editColorPicker && editColorHex) {
        editColorPicker.addEventListener('input', function() {
            editColorHex.value = this.value.toUpperCase();
        });
        
        editColorHex.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                editColorPicker.value = this.value;
            }
        });
    }
    
    // Color picker sync for Create modal
    const createColorPicker = document.getElementById('createColor');
    const createColorHex = document.getElementById('createColorHex');
    
    if (createColorPicker && createColorHex) {
        createColorPicker.addEventListener('input', function() {
            createColorHex.value = this.value.toUpperCase();
        });
        
        createColorHex.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                createColorPicker.value = this.value;
            }
        });
    }
    
    // Handle create form submission
    const createForm = document.getElementById('createForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const actionUrl = this.action;
            
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Status created successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('createModal')).hide();
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert(data.error || 'Failed to create status', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while creating status', 'danger');
            });
        });
    }
    
    // Handle edit form submission
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const actionUrl = this.action;
            
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Status updated successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                    
                    // Extract status info for sync
                    const statusId = actionUrl.split('/').pop();
                    const statusName = formData.get('status_name');
                    const color = formData.get('color');
                    
                    // Broadcast color change to other interfaces
                    if (window.statusColorSync && color) {
                        window.statusColorSync.notifyColorChange(statusId, statusName, color);
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert(data.error || 'Failed to update status', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating status', 'danger');
            });
        });
    }
});

function openCreateModal() {
    // Reset form
    document.getElementById('createForm').reset();
    document.getElementById('createColor').value = '#007bff';
    document.getElementById('createColorHex').value = '#007BFF';
    
    // Show modal
    new bootstrap.Modal(document.getElementById('createModal')).show();
}

function editStatus(id, name, description, color) {
    // Set form values
    document.getElementById('editStatusName').value = name;
    document.getElementById('editDescription').value = description || '';
    document.getElementById('editColor').value = color;
    document.getElementById('editColorHex').value = color.toUpperCase();
    
    // Set form action
    document.getElementById('editForm').action = `/admin/status/${id}`;
    
    // Show modal
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

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
