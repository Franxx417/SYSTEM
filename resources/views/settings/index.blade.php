@extends('layouts.app')
@section('title', 'Account Settings')
@section('page_heading', 'Account Settings')
@section('page_subheading', 'Manage your account preferences and security')

@section('content')
<!-- Settings Navigation Tabs -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <ul class="nav nav-tabs border-0" id="settingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                    <i class="fas fa-user me-2"></i>Profile
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                    <i class="fas fa-lock me-2"></i>Security
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                    <i class="fas fa-sliders-h me-2"></i>Preferences
                </button>
            </li>
            @if($auth && $auth['role'] !== 'superadmin')
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button" role="tab">
                    <i class="fas fa-palette me-2"></i>Appearance
                </button>
            </li>
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                    <i class="fas fa-bell me-2"></i>Notifications
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="settingsTabContent">
    <!-- Profile Tab -->
    <div class="tab-pane fade show active" id="profile" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.profile.update') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $user->name ?? $auth['name'] }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ $user->email ?? $auth['email'] }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="position" value="{{ $user->position ?? $auth['position'] }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" name="department" value="{{ $user->department ?? $auth['department'] }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-image me-2"></i>Company Logo</h6>
            </div>
            <div class="card-body">
                @if($currentLogo)
                    <div class="mb-3">
                        <label class="form-label">Current Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $currentLogo }}" alt="Company Logo" class="img-thumbnail" style="max-height: 80px; max-width: 200px;">
                            <form method="POST" action="{{ route('settings.logo.remove') }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to remove the logo?')">
                                    <i class="fas fa-trash me-1"></i>Remove Logo
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('settings.logo.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ $currentLogo ? 'Replace Logo' : 'Upload Logo' }}</label>
                        <input type="file" class="form-control" name="logo" accept="image/*" required>
                        <div class="form-text">
                            Supported formats: JPEG, PNG, GIF, SVG, WebP. Maximum size: 2MB.<br>
                            Recommended dimensions: 300x100px for optimal display on documents.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i>{{ $currentLogo ? 'Replace Logo' : 'Upload Logo' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lock me-2"></i>Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.password.update') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="new_password_confirmation" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">Role</div>
                    <div class="fw-medium">{{ ucwords(str_replace('_', ' ', $auth['role'])) }}</div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">User ID</div>
                    <div class="fw-medium">{{ $auth['user_id'] }}</div>
                </div>
                <div class="mb-3">
                    <div class="small text-muted">Last Updated</div>
                    <div class="fw-medium">{{ $user->updated_at ?? 'Never' }}</div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            </div>
        </div>
    </div>
    <!-- End Profile Tab -->

    <!-- Security Tab -->
    <div class="tab-pane fade" id="security" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('settings.password.update') }}" id="passwordForm">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="current_password" id="current_password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye" id="current_password_icon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="new_password" id="new_password" minlength="8" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye" id="new_password_icon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="new_password_confirmation" id="new_password_confirmation" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                            <i class="fas fa-eye" id="new_password_confirmation_icon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Password Requirements:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>At least 8 characters long</li>
                                            <li>Use a mix of letters, numbers, and symbols</li>
                                            <li>Avoid using common words or personal information</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-1"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Account Security</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Two-Factor Authentication</div>
                                    <small class="text-muted">Add an extra layer of security to your account</small>
                                </div>
                                <span class="badge bg-secondary">Coming Soon</span>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">Active Sessions</div>
                                    <small class="text-muted">Current: Web Browser • Last login: {{ now()->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Security Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Use a strong, unique password</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Change password regularly</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Never share your credentials</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Log out from shared devices</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Security Tab -->

    <!-- Preferences Tab -->
    <div class="tab-pane fade" id="preferences" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>General Preferences</h6>
                    </div>
                    <div class="card-body">
                        <form id="preferencesForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Language</label>
                                    <select class="form-select">
                                        <option selected>English (US)</option>
                                        <option>Filipino</option>
                                        <option>中文 (Chinese)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date Format</label>
                                    <select class="form-select">
                                        <option>MM/DD/YYYY</option>
                                        <option selected>DD/MM/YYYY</option>
                                        <option>YYYY-MM-DD</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Time Format</label>
                                    <select class="form-select">
                                        <option selected>12-hour (AM/PM)</option>
                                        <option>24-hour</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Timezone</label>
                                    <select class="form-select">
                                        <option selected>Asia/Manila (GMT+8)</option>
                                        <option>Asia/Tokyo (GMT+9)</option>
                                        <option>UTC (GMT+0)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <hr>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="autoSave" checked>
                                        <label class="form-check-label" for="autoSave">
                                            Auto-save drafts
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="compactView">
                                        <label class="form-check-label" for="compactView">
                                            Compact view for tables
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Preferences
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>About Preferences</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 small text-muted">
                            Customize how you interact with the system. Your preferences will be applied across all your devices.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Preferences Tab -->

    <!-- Appearance Tab -->
    @if($auth && $auth['role'] !== 'superadmin')
    <div class="tab-pane fade" id="appearance" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-image me-2"></i>Company Logo</h6>
                    </div>
                    <div class="card-body">
                        @if($currentLogo)
                            <div class="mb-3">
                                <label class="form-label">Current Logo</label>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $currentLogo }}" alt="Company Logo" class="img-thumbnail" style="max-height: 80px; max-width: 200px;">
                                    <form method="POST" action="{{ route('settings.logo.remove') }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to remove the logo?')">
                                            <i class="fas fa-trash me-1"></i>Remove Logo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('settings.logo.upload') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ $currentLogo ? 'Replace Logo' : 'Upload Logo' }}</label>
                                <input type="file" class="form-control" name="logo" accept="image/*" required>
                                <div class="form-text">
                                    Supported formats: JPEG, PNG, GIF, SVG, WebP. Maximum size: 2MB.<br>
                                    Recommended dimensions: 300x100px for optimal display on documents.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>{{ $currentLogo ? 'Replace Logo' : 'Upload Logo' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Logo Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use PNG for transparency</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Keep it simple and clear</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Test on different backgrounds</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <!-- End Appearance Tab -->

    <!-- Notifications Tab -->
    <div class="tab-pane fade" id="notifications" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Preferences</h6>
                    </div>
                    <div class="card-body">
                        <form id="notificationsForm">
                            <div class="mb-3">
                                <h6>Purchase Orders</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="notif_po_created" checked>
                                    <label class="form-check-label" for="notif_po_created">
                                        New purchase order created
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="notif_po_approved" checked>
                                    <label class="form-check-label" for="notif_po_approved">
                                        Purchase order approved
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="notif_po_rejected" checked>
                                    <label class="form-check-label" for="notif_po_rejected">
                                        Purchase order rejected
                                    </label>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <h6>System Notifications</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="notif_system_updates" checked>
                                    <label class="form-check-label" for="notif_system_updates">
                                        System updates and maintenance
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="notif_security">
                                    <label class="form-check-label" for="notif_security">
                                        Security alerts
                                    </label>
                                </div>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <h6>Email Notifications</h6>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="email_daily_summary">
                                    <label class="form-check-label" for="email_daily_summary">
                                        Daily activity summary
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="email_weekly_report">
                                    <label class="form-check-label" for="email_weekly_report">
                                        Weekly reports
                                    </label>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Notification Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Stay Informed</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-0">
                            Configure which notifications you'd like to receive. You can always update these settings later.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Notifications Tab -->
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-success text-white">
                <i class="fas fa-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert">
            <div class="toast-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<script>
// Toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Auto-hide toasts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(function(toast) {
        setTimeout(function() {
            const bsToast = new bootstrap.Toast(toast);
            bsToast.hide();
        }, 5000);
    });
    
    // Prevent forms from being submitted multiple times
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                setTimeout(function() {
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });
});

// Preferences form handling (client-side for now)
document.getElementById('preferencesForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Preferences saved! (This is a demo - backend integration pending)');
});

// Notifications form handling (client-side for now)
document.getElementById('notificationsForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Notification settings saved! (This is a demo - backend integration pending)');
});
</script>

<style>
.nav-tabs {
    border-bottom: 2px solid #dee2e6;
}

.nav-tabs .nav-link {
    color: #6c757d;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.nav-tabs .nav-link:hover {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
}

.nav-tabs .nav-link.active {
    color: #0d6efd;
    background-color: transparent;
    border-bottom-color: #0d6efd;
    font-weight: 600;
}

.toast {
    min-width: 300px;
}
</style>
@endsection
