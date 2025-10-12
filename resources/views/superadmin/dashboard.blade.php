@extends('layouts.app')
@section('title','Dashboard')
@section('page_heading','Dashboard')
@section('page_subheading','System administration and oversight')

@push('styles')
    <link rel="stylesheet" href="/css/superadmin-dashboard.css">
@endpush

@section('content')
<div class="superadmin-dashboard">

<!-- System Overview Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">System Status</div>
                        <div class="h3 mb-0 text-success">Online</div>
                    </div>
                    <i class="fas fa-server text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Users</div>
                        <div class="h3 mb-0">{{ $metrics['users'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-users text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Active Sessions</div>
                        <div class="h3 mb-0">{{ $metrics['active_sessions'] ?? 0 }}</div>
                    </div>
                    <i class="fas fa-user-clock text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Database Size</div>
                        <div class="h3 mb-0">{{ $metrics['db_size'] ?? 'N/A' }}</div>
                    </div>
                    <i class="fas fa-database text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@php($active = request()->get('tab','overview'))
@php($tabMap = [
    'purchase-orders' => 'pos',
    'user-management' => 'users',
    'security' => 'security',
    'system' => 'system',
    'database' => 'database',
    'logs' => 'logs',
    'branding' => 'branding',
    'status' => 'status',
    'overview' => 'overview'
])
@php($tabFile = $tabMap[$active] ?? 'overview')

<!-- Tab Content -->
<div class="tab-content">
    @include('superadmin.tabs.' . $tabFile, ['active' => $active])
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
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('add_password')">
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
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('add_password_confirmation')">
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

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/dashboards/superadmin-dashboard-enhanced.js', 'resources/css/pages/superadmin-dashboard.css'])
    <script>
    // Show Add User Modal
    function showAddUserModal() {
        // Reset form
        const form = document.getElementById('addUserForm');
        if (form) {
            form.reset();
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
        }
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
        modal.show();
    }

    // Toggle password visibility
    function togglePasswordVisibility(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '_icon');
        
        if (passwordField && icon) {
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
    }

    // Form validation and submission for Add User
    document.addEventListener('DOMContentLoaded', function() {
        const addUserForm = document.getElementById('addUserForm');
        
        if (addUserForm) {
            addUserForm.addEventListener('submit', function(e) {
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
                    const confirmField = document.getElementById('add_password_confirmation');
                    confirmField.classList.add('is-invalid');
                    const feedback = confirmField.parentElement.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = 'Passwords do not match';
                    }
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
                        const modalElement = document.getElementById('addUserModal');
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        }
                        
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
            const usernameInput = document.getElementById('add_username');
            if (usernameInput) {
                usernameInput.addEventListener('input', function() {
                    const value = this.value;
                    const feedback = this.parentElement.querySelector('.invalid-feedback');
                    
                    if (value.length > 0 && value.length < 3) {
                        this.classList.add('is-invalid');
                        if (feedback) feedback.textContent = 'Username must be at least 3 characters';
                    } else if (!/^[a-zA-Z0-9_]+$/.test(value) && value.length > 0) {
                        this.classList.add('is-invalid');
                        if (feedback) feedback.textContent = 'Username can only contain letters, numbers, and underscores';
                    } else {
                        this.classList.remove('is-invalid');
                        if (feedback) feedback.textContent = '';
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
                        if (feedback) feedback.textContent = 'Please enter a valid email address';
                    } else {
                        this.classList.remove('is-invalid');
                        if (feedback) feedback.textContent = '';
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
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = 'Passwords do not match';
                        }
                    } else {
                        this.classList.remove('is-invalid');
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = '';
                        }
                    }
                });
            }
        }
    });

    // Notification function
    function showNotification(message, type = 'success') {
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
