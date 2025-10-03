@extends('layouts.app')
@section('title', 'User Management')
@section('page_heading', 'User Management')
@section('page_subheading', 'Manage system users and permissions')

@push('styles')
    <style>
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 0.875rem;
        }
        
        /* Add User Modal Styles */
        #addUserModal .modal-header {
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        #addUserModal .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        #addUserModal .form-control:focus,
        #addUserModal .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        #addUserModal .input-group .btn {
            border-left: 0;
        }
        
        #addUserModal .alert-info {
            background-color: #e7f3ff;
            border-color: #b8daff;
            color: #0c5460;
        }
        
        .modal-lg {
            max-width: 800px;
        }
        
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .invalid-feedback {
            display: block;
            font-size: 0.875rem;
        }
        
        .is-invalid {
            border-color: #dc3545;
        }
        
        .is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        /* Loading state for submit button */
        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        /* Password toggle button styling */
        .input-group .btn-outline-secondary {
            border-color: #ced4da;
        }
        
        .input-group .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }
    </style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>All Users</h6>
                </div>
                <button class="btn btn-primary btn-sm" onclick="showAddUserModal()">
                    <i class="fas fa-plus me-1"></i>Add User
                </button>
            </div>
            <div class="card-body p-0">
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th class="d-none d-md-table-cell">Position</th>
                                <th class="d-none d-lg-table-cell">Department</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($users ?? [] as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                            {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $u->name }}</div>
                                            <div class="text-muted small">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell"><span class="text-muted">{{ $u->position ?? 'N/A' }}</span></td>
                                <td class="d-none d-lg-table-cell"><span class="text-muted">{{ $u->department ?? 'N/A' }}</span></td>
                                <td>
                                    @if($u->role === 'superadmin')
                                        <span class="badge bg-danger">{{ ucfirst($u->role) }}</span>
                                    @elseif($u->role === 'authorized_personnel')
                                        <span class="badge bg-warning">Authorized Personnel</span>
                                    @elseif($u->role === 'requestor')
                                        <span class="badge bg-info">{{ ucfirst($u->role) }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $u->role ?? 'Unknown' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @php($active = isset($u->is_active) ? (bool)$u->is_active : true)
                                    <span class="badge status-badge {{ $active ? 'bg-success' : 'bg-danger' }}">{{ $active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm d-none d-md-flex">
                                        <button class="btn btn-outline-primary" onclick="editUser('{{ $u->user_id }}', '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ addslashes($u->position) }}', '{{ addslashes($u->department) }}', '{{ $u->role }}')">
                                            <i class="fas fa-edit"></i><span class="ms-1 d-none d-xl-inline">Edit</span>
                                        </button>
                                        <button class="btn btn-outline-warning" 
                                                data-action="reset-password" 
                                                data-user-id="{{ $u->user_id }}"
                                                data-bs-toggle="tooltip" 
                                                title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        @php($active = isset($u->is_active) ? (bool)$u->is_active : true)
                                        <button class="btn toggle-user-btn {{ $active ? 'btn-outline-danger' : 'btn-outline-success' }}" 
                                                data-action="toggle-user" 
                                                data-user-id="{{ $u->user_id }}" 
                                                data-active="{{ $active ? 1 : 0 }}"
                                                data-bs-toggle="tooltip" 
                                                title="{{ $active ? 'Deactivate User' : 'Activate User' }}">
                                            <i class="fas fa-{{ $active ? 'ban' : 'check' }}"></i><span class="ms-1 d-none d-xl-inline">{{ $active ? 'Deactivate' : 'Activate' }}</span>
                                        </button>
                                    </div>
                                    
                                    <!-- Mobile dropdown menu -->
                                    <div class="dropdown d-md-none">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button class="dropdown-item" onclick="editUser('{{ $u->user_id }}', '{{ addslashes($u->name) }}', '{{ $u->email }}', '{{ addslashes($u->position) }}', '{{ addslashes($u->department) }}', '{{ $u->role }}')">
                                                    <i class="fas fa-edit me-2"></i>Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item" 
                                                        data-action="reset-password" 
                                                        data-user-id="{{ $u->user_id }}">
                                                    <i class="fas fa-key me-2"></i>Reset Password
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                @php($active = isset($u->is_active) ? (bool)$u->is_active : true)
                                                <button class="dropdown-item toggle-user-btn {{ $active ? 'text-danger' : 'text-success' }}" 
                                                        data-action="toggle-user" 
                                                        data-user-id="{{ $u->user_id }}" 
                                                        data-active="{{ $active ? 1 : 0 }}">
                                                    <i class="fas fa-{{ $active ? 'ban' : 'check' }} me-2"></i>{{ $active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    No users found
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addUserModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New User
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="add_name" name="name" required 
                                       placeholder="Enter full name" maxlength="100">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_username" class="form-label">
                                    <i class="fas fa-at me-1"></i>Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="add_username" name="username" required 
                                       placeholder="Enter username" maxlength="50">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control" id="add_email" name="email" required 
                                       placeholder="Enter email address" maxlength="100">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_role" class="form-label">
                                    <i class="fas fa-user-tag me-1"></i>Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="add_role" name="role" required>
                                    <option value="">Select a role</option>
                                    <option value="requestor">Requestor</option>
                                    <option value="authorized_personnel">Authorized Personnel</option>
                                    <option value="superadmin">Super Admin</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_position" class="form-label">
                                    <i class="fas fa-briefcase me-1"></i>Position
                                </label>
                                <input type="text" class="form-control" id="add_position" name="position" 
                                       placeholder="Enter job position" maxlength="100">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_department" class="form-label">
                                    <i class="fas fa-building me-1"></i>Department
                                </label>
                                <input type="text" class="form-control" id="add_department" name="department" 
                                       placeholder="Enter department" maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="add_password" name="password" required 
                                           placeholder="Enter password" minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('add_password')">
                                        <i class="fas fa-eye" id="add_password_icon"></i>
                                    </button>
                                </div>
                                <div class="form-text">Minimum 6 characters</div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="add_password_confirmation" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="add_password_confirmation" name="password_confirmation" required 
                                           placeholder="Confirm password" minlength="6">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('add_password_confirmation')">
                                        <i class="fas fa-eye" id="add_password_confirmation_icon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> The user will be created with an active status and can log in immediately with the provided credentials.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="addUserSubmitBtn">
                        <i class="fas fa-user-plus me-1"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" class="form-control" id="edit_position" name="position">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" id="edit_department" name="department">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="requestor">Requestor</option>
                            <option value="authorized_personnel">Authorized Personnel</option>
                            <option value="superadmin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/pages/admin-users-index.js'])
<script>
// Show Add User Modal
function showAddUserModal() {
    // Reset form
    document.getElementById('addUserForm').reset();
    
    // Clear any previous validation states
    const form = document.getElementById('addUserForm');
    form.classList.remove('was-validated');
    
    // Clear invalid feedback
    const invalidFeedbacks = form.querySelectorAll('.invalid-feedback');
    invalidFeedbacks.forEach(feedback => {
        feedback.textContent = '';
    });
    
    // Remove invalid classes
    const inputs = form.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.classList.remove('is-invalid', 'is-valid');
    });
    
    // Show modal
    new bootstrap.Modal(document.getElementById('addUserModal')).show();
}

// Toggle password visibility
function togglePassword(fieldId) {
    const passwordField = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form validation for Add User
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = document.getElementById('addUserSubmitBtn');
    
    // Basic validation
    if (!form.checkValidity()) {
        e.stopPropagation();
        form.classList.add('was-validated');
        return;
    }
    
    // Password confirmation validation
    const password = document.getElementById('add_password').value;
    const passwordConfirmation = document.getElementById('add_password_confirmation').value;
    
    if (password !== passwordConfirmation) {
        document.getElementById('add_password_confirmation').classList.add('is-invalid');
        document.getElementById('add_password_confirmation').nextElementSibling.nextElementSibling.textContent = 'Passwords do not match';
        return;
    }
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
    submitBtn.disabled = true;
    
    // Submit form via AJAX
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
            
            // Show success message
            showNotification('User created successfully!', 'success');
            
            // Reload page to show new user
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Handle validation errors
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.getElementById('add_' + field);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.parentElement.querySelector('.invalid-feedback') || 
                                       input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = data.errors[field][0];
                        }
                    }
                });
            } else {
                showNotification(data.message || 'Failed to create user', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while creating the user', 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Real-time validation
document.addEventListener('DOMContentLoaded', function() {
    // Username validation
    const usernameInput = document.getElementById('add_username');
    if (usernameInput) {
        usernameInput.addEventListener('input', function() {
            const value = this.value;
            const feedback = this.parentElement.querySelector('.invalid-feedback');
            
            if (value.length > 0 && value.length < 3) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Username must be at least 3 characters';
            } else if (!/^[a-zA-Z0-9_]+$/.test(value) && value.length > 0) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Username can only contain letters, numbers, and underscores';
            } else {
                this.classList.remove('is-invalid');
                feedback.textContent = '';
            }
        });
    }
    
    // Email validation
    const emailInput = document.getElementById('add_email');
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const value = this.value;
            const feedback = this.parentElement.querySelector('.invalid-feedback');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (value.length > 0 && !emailRegex.test(value)) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Please enter a valid email address';
            } else {
                this.classList.remove('is-invalid');
                feedback.textContent = '';
            }
        });
    }
    
    // Password confirmation validation
    const passwordConfirmInput = document.getElementById('add_password_confirmation');
    const passwordInput = document.getElementById('add_password');
    
    if (passwordConfirmInput && passwordInput) {
        passwordConfirmInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmation = this.value;
            const feedback = this.parentElement.nextElementSibling;
            
            if (confirmation.length > 0 && password !== confirmation) {
                this.classList.add('is-invalid');
                feedback.textContent = 'Passwords do not match';
            } else {
                this.classList.remove('is-invalid');
                feedback.textContent = '';
            }
        });
    }
});

function editUser(userId, name, email, position, department, role) {
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_position').value = position || '';
    document.getElementById('edit_department').value = department || '';
    document.getElementById('edit_role').value = role;
    
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}

function showNotification(message, type = 'success') {
    // Simple notification - can be enhanced
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        <i class="fas ${icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endpush
@endsection
