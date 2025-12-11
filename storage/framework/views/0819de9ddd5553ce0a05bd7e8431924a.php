
<?php
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
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-traffic-light me-2"></i>Status Configuration</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStatusModal">
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
                            <?php $__currentLoopData = $dbStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($status->status_name); ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo e($status->description ?? 'No description'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo e($statusUsage[$status->status_name] ?? 0); ?> uses
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="openEditModal('<?php echo e($status->status_id); ?>', '<?php echo e($status->status_name); ?>', '<?php echo e(addslashes($status->description ?? '')); ?>')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php if(($statusUsage[$status->status_name] ?? 0) == 0): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="openDeleteModal('<?php echo e($status->status_id); ?>', '<?php echo e($status->status_name); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <div class="h4 text-primary"><?php echo e($stats['total_statuses']); ?></div>
                        <div class="text-muted small">Total Statuses</div>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success"><?php echo e($stats['total_usage']); ?></div>
                        <div class="text-muted small">Total Usage</div>
                    </div>
                </div>
                <hr>
                <div class="small">
                    <strong>Usage Breakdown:</strong>
                    <ul class="list-unstyled mt-2">
                        <?php $__currentLoopData = $dbStatuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dbStatus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="d-flex justify-content-between align-items-center py-1">
                                <span><?php echo e($dbStatus->status_name); ?></span>
                                <span class="badge bg-secondary">
                                    <?php echo e($statusUsage[$dbStatus->status_name] ?? 0); ?> uses
                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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


<div class="modal fade" id="addStatusModal" tabindex="-1" aria-labelledby="addStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addStatusModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addStatusForm" onsubmit="handleAddStatus(event)">
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-start">
                        <i class="fas fa-info-circle me-2 mt-1"></i>
                        <div>
                            <strong>Create a new status</strong><br>
                            <small>Define a status name and optional description for purchase order tracking.</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_status_name" class="form-label">
                            Status Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="add_status_name" 
                               name="status_name" 
                               required 
                               maxlength="100"
                               placeholder="e.g., Pending, Approved, Verified"
                               autocomplete="off">
                        <div class="form-text">Enter a clear, descriptive status name</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_description" class="form-label">
                            Description <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea class="form-control" 
                                  id="add_description" 
                                  name="description" 
                                  rows="3" 
                                  maxlength="500"
                                  placeholder="Provide additional details about this status..."
                                  style="min-height: 80px; max-height: 200px;"></textarea>
                        <div class="form-text">Explain when this status should be used</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editStatusModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Status
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStatusForm" onsubmit="handleEditStatus(event)">
                <input type="hidden" id="edit_status_id" name="status_id">
                <div class="modal-body">
                    <div class="alert alert-warning d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle me-2 mt-1"></i>
                        <div>
                            <strong>Editing existing status</strong><br>
                            <small>Changes will affect all purchase orders using this status.</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status_name" class="form-label">
                            Status Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="edit_status_name" 
                               name="status_name" 
                               required 
                               maxlength="100"
                               autocomplete="off">
                        <div class="form-text">Update the status name</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">
                            Description <span class="text-muted">(Optional)</span>
                        </label>
                        <textarea class="form-control" 
                                  id="edit_description" 
                                  name="description" 
                                  rows="3" 
                                  maxlength="500"
                                  style="min-height: 80px; max-height: 200px;"></textarea>
                        <div class="form-text">Update the status description</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteStatusModal" tabindex="-1" aria-labelledby="deleteStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteStatusModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Delete Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteStatusForm" onsubmit="handleDeleteStatus(event)">
                <input type="hidden" id="delete_status_id" name="status_id">
                <div class="modal-body">
                    <div class="alert alert-danger d-flex align-items-start mb-3">
                        <i class="fas fa-exclamation-circle me-2 mt-1 fs-4"></i>
                        <div>
                            <strong>Warning: This action cannot be undone!</strong><br>
                            <small>Deleting this status will permanently remove it from the system.</small>
                        </div>
                    </div>
                    
                    <div class="bg-light p-3 rounded border border-danger">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-traffic-light text-danger me-2 fs-5"></i>
                            <div>
                                <div class="text-muted small">Status to be deleted:</div>
                                <div class="fw-bold fs-5" id="delete_status_name"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <p class="mb-2"><strong>Are you sure you want to delete this status?</strong></p>
                        <ul class="small text-muted mb-0">
                            <li>This status will be removed from the system</li>
                            <li>This action is permanent and cannot be reversed</li>
                            <li>Only unused statuses can be deleted</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open Edit Modal
function openEditModal(statusId, statusName, description) {
    document.getElementById('edit_status_id').value = statusId;
    document.getElementById('edit_status_name').value = statusName;
    document.getElementById('edit_description').value = description || '';
    
    const modal = new bootstrap.Modal(document.getElementById('editStatusModal'));
    modal.show();
}

// Open Delete Modal
function openDeleteModal(statusId, statusName) {
    document.getElementById('delete_status_id').value = statusId;
    document.getElementById('delete_status_name').textContent = statusName;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteStatusModal'));
    modal.show();
}

// Real-time validation for status name
function validateStatusName(input) {
    const value = input.value.trim();
    const feedback = input.parentElement.querySelector('.invalid-feedback') || createFeedbackElement(input);
    
    if (!value) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        feedback.textContent = 'Status name is required';
        return false;
    } else if (value.length < 2) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        feedback.textContent = 'Status name must be at least 2 characters';
        return false;
    } else if (value.length > 100) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        feedback.textContent = 'Status name must not exceed 100 characters';
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

function createFeedbackElement(input) {
    const feedback = document.createElement('div');
    feedback.className = 'invalid-feedback';
    input.parentElement.appendChild(feedback);
    return feedback;
}

// Add real-time validation listeners
document.addEventListener('DOMContentLoaded', function() {
    const statusNameInputs = ['add_status_name', 'edit_status_name'];
    statusNameInputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('blur', () => validateStatusName(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    validateStatusName(input);
                }
            });
        }
    });
    
    // Reset form validation when modals are closed
    ['addStatusModal', 'editStatusModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                    form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
                        el.classList.remove('is-invalid', 'is-valid');
                    });
                }
            });
        }
    });
});

// Handle Add Status Form Submission
function handleAddStatus(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    const statusNameInput = document.getElementById('add_status_name');
    
    // Validate before submission
    if (!validateStatusName(statusNameInput)) {
        statusNameInput.focus();
        return;
    }
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
    
    const formData = new FormData(form);
    const data = {
        status_name: formData.get('status_name').trim(),
        description: formData.get('description').trim() || ''
    };
    
function addNewStatus() {
    fetch('/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify(data)
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
            bootstrap.Modal.getInstance(document.getElementById('addStatusModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Failed to create status');
        }
    })
    .catch(error => {
        console.error('[Status] Error creating status:', error);
        showQuickNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
}

// Handle Edit Status Form Submission
function handleEditStatus(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    const statusNameInput = document.getElementById('edit_status_name');
    
    // Validate before submission
    if (!validateStatusName(statusNameInput)) {
        statusNameInput.focus();
        return;
    }
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    
    const formData = new FormData(form);
    const statusId = formData.get('status_id');
    const data = {
        _method: 'PUT',
        status_name: formData.get('status_name').trim(),
        description: formData.get('description').trim() || ''
    };
    
    editStatus(statusId, data, submitBtn, originalBtnText);
}

function editStatus(statusId, data, submitBtn, originalBtnText) {

    fetch(`/status/${statusId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify(data)
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
            bootstrap.Modal.getInstance(document.getElementById('editStatusModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('[Status] Error updating status:', error);
        showQuickNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
}

// Handle Delete Status Form Submission
function handleDeleteStatus(event) {
    event.preventDefault();
    
    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
    
    const statusId = document.getElementById('delete_status_id').value;
    
    deleteStatus(statusId, submitBtn, originalBtnText);
}

function deleteStatus(statusId, submitBtn, originalBtnText) {

    fetch(`/status/${statusId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
            bootstrap.Modal.getInstance(document.getElementById('deleteStatusModal')).hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Failed to delete status');
        }
    })
    .catch(error => {
        console.error('[Status] Error deleting status:', error);
        showQuickNotification('Error: ' + error.message, 'error');
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
}

function exportConfig() {
    fetch('<?php echo e(route("status.config")); ?>', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
<?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/superadmin/tabs/status.blade.php ENDPATH**/ ?>