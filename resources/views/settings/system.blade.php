@extends('layouts.app')
@section('title', 'System Settings')
@section('page_heading', 'System Settings')
@section('page_subheading', 'Configure application parameters and system preferences')

@section('content')
<!-- Settings Navigation -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <ul class="nav nav-tabs border-0" id="systemSettingsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="user-mgmt-tab" data-bs-toggle="tab" data-bs-target="#user-management" type="button" role="tab">
                    <i class="fas fa-users me-2"></i>User Management
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                    <i class="fas fa-shield-alt me-2"></i>Security
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                    <i class="fas fa-bell me-2"></i>Notifications
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button" role="tab">
                    <i class="fas fa-tachometer-alt me-2"></i>Performance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="application-tab" data-bs-toggle="tab" data-bs-target="#application" type="button" role="tab">
                    <i class="fas fa-cogs me-2"></i>Application
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content" id="systemSettingsTabContent">
    <!-- User Management Tab -->
    <div class="tab-pane fade show active" id="user-management" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>User Management Settings</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="resetCategory('user_management')">
                            <i class="fas fa-undo me-1"></i>Reset to Defaults
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="userManagementForm">
                            <div class="row g-3" id="user-management-settings">
                                <!-- Settings will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>User Management Info</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Configure default settings for new users, password requirements, and account security policies.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Tab -->
    <div class="tab-pane fade" id="security" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="resetCategory('security')">
                            <i class="fas fa-undo me-1"></i>Reset to Defaults
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="securityForm">
                            <div class="row g-3" id="security-settings">
                                <!-- Settings will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-shield-check me-2"></i>Security Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Secure</span>
                            <small>HTTPS Enabled</small>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Active</span>
                            <small>Session Management</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2">Optional</span>
                            <small>Two-Factor Auth</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Tab -->
    <div class="tab-pane fade" id="notifications" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Settings</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="resetCategory('notifications')">
                            <i class="fas fa-undo me-1"></i>Reset to Defaults
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="notificationsForm">
                            <div class="row g-3" id="notifications-settings">
                                <!-- Settings will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Settings
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="testNotifications()">
                                    <i class="fas fa-paper-plane me-1"></i>Test Notifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Status</h6>
                    </div>
                    <div class="card-body">
                        <div id="email-status">
                            <div class="text-center text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Checking...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tab -->
    <div class="tab-pane fade" id="performance" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Performance Settings</h6>
                        <button class="btn btn-sm btn-outline-primary" onclick="resetCategory('performance')">
                            <i class="fas fa-undo me-1"></i>Reset to Defaults
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="performanceForm">
                            <div class="row g-3" id="performance-settings">
                                <!-- Settings will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Settings
                                </button>
                                <button type="button" class="btn btn-outline-warning" onclick="clearCache()">
                                    <i class="fas fa-broom me-1"></i>Clear Cache
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>System Performance</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Cache Status</small>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 85%"></div>
                            </div>
                            <small>85% Efficiency</small>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Memory Usage</small>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: 60%"></div>
                            </div>
                            <small>60% Used</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Tab -->
    <div class="tab-pane fade" id="application" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Application Settings</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success" onclick="exportSettings()">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="importSettings()">
                                <i class="fas fa-upload me-1"></i>Import
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="resetCategory('application')">
                                <i class="fas fa-undo me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="applicationForm">
                            <div class="row g-3" id="application-settings">
                                <!-- Settings will be loaded here -->
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Save Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Application Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Version</small>
                            <div class="fw-semibold">1.0.0</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Environment</small>
                            <div class="fw-semibold">Production</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Last Updated</small>
                            <div class="fw-semibold">{{ now()->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// System Settings JavaScript
let currentSettings = {};

document.addEventListener('DOMContentLoaded', function() {
    loadAllSettings();
    setupEventListeners();
});

function setupEventListeners() {
    // Tab change events
    document.querySelectorAll('#systemSettingsTabs button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const category = event.target.getAttribute('data-bs-target').replace('#', '').replace('-', '_');
            loadCategorySettings(category);
        });
    });

    // Form submissions
    ['userManagementForm', 'securityForm', 'notificationsForm', 'performanceForm', 'applicationForm'].forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const category = getCategoryFromForm(formId);
                saveSettings(category, form);
            });
        }
    });
}

function loadAllSettings() {
    loadCategorySettings('user_management');
}

function loadCategorySettings(category) {
    fetch(`/api/system-settings/category/${category}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(settings => {
        currentSettings[category] = settings;
        renderSettings(category, settings);
    })
    .catch(error => {
        console.error('Error loading settings:', error);
        showNotification('Failed to load settings', 'error');
    });
}

function renderSettings(category, settings) {
    const container = document.getElementById(`${category.replace('_', '-')}-settings`);
    if (!container) return;

    container.innerHTML = settings.map(setting => {
        return renderSettingField(setting);
    }).join('');
}

function renderSettingField(setting) {
    const fieldId = `${setting.key}_field`;
    
    switch (setting.type) {
        case 'boolean':
            return `
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="${fieldId}" name="${setting.key}" ${setting.value ? 'checked' : ''}>
                        <label class="form-check-label" for="${fieldId}">
                            ${setting.description || setting.key}
                        </label>
                    </div>
                </div>
            `;
        case 'integer':
            return `
                <div class="col-md-6">
                    <label class="form-label" for="${fieldId}">${setting.description || setting.key}</label>
                    <input type="number" class="form-control" id="${fieldId}" name="${setting.key}" value="${setting.value || ''}" min="0">
                </div>
            `;
        case 'string':
        default:
            return `
                <div class="col-md-6">
                    <label class="form-label" for="${fieldId}">${setting.description || setting.key}</label>
                    <input type="text" class="form-control" id="${fieldId}" name="${setting.key}" value="${setting.value || ''}">
                </div>
            `;
    }
}

function saveSettings(category, form) {
    const formData = new FormData(form);
    const settings = {};
    
    // Convert FormData to object, handling checkboxes
    for (const [key, value] of formData.entries()) {
        settings[key] = value;
    }
    
    // Handle unchecked checkboxes
    form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        if (!checkbox.checked) {
            settings[checkbox.name] = false;
        } else {
            settings[checkbox.name] = true;
        }
    });

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

    fetch('/api/system-settings/batch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            category: category,
            settings: settings
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Settings saved successfully', 'success');
        } else {
            showNotification(data.message || 'Failed to save settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        showNotification('Failed to save settings', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function resetCategory(category) {
    if (!confirm(`Are you sure you want to reset all ${category.replace('_', ' ')} settings to defaults?`)) {
        return;
    }

    fetch('/api/system-settings/reset', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ category: category })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Settings reset successfully', 'success');
            loadCategorySettings(category);
        } else {
            showNotification(data.message || 'Failed to reset settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error resetting settings:', error);
        showNotification('Failed to reset settings', 'error');
    });
}

function exportSettings() {
    fetch('/api/system-settings/export', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `system-settings-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        showNotification('Settings exported successfully', 'success');
    })
    .catch(error => {
        console.error('Error exporting settings:', error);
        showNotification('Failed to export settings', 'error');
    });
}

function importSettings() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = JSON.parse(e.target.result);
                
                fetch('/api/system-settings/import', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showNotification(result.message, 'success');
                        loadAllSettings();
                    } else {
                        showNotification(result.message || 'Failed to import settings', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error importing settings:', error);
                    showNotification('Failed to import settings', 'error');
                });
            } catch (error) {
                showNotification('Invalid JSON file', 'error');
            }
        };
        reader.readAsText(file);
    };
    input.click();
}

function clearCache() {
    if (!confirm('Are you sure you want to clear the application cache?')) return;

    fetch('/api/system-settings/clear-cache', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        showNotification('Cache cleared successfully', 'success');
    })
    .catch(error => {
        console.error('Error clearing cache:', error);
        showNotification('Failed to clear cache', 'error');
    });
}

function testNotifications() {
    showNotification('Test notification sent!', 'info');
}

function getCategoryFromForm(formId) {
    const mapping = {
        'userManagementForm': 'user_management',
        'securityForm': 'security',
        'notificationsForm': 'notifications',
        'performanceForm': 'performance',
        'applicationForm': 'application'
    };
    return mapping[formId] || 'general';
}

function showNotification(message, type) {
    if (typeof showQuickNotification === 'function') {
        showQuickNotification(message, type);
    } else {
        alert(message);
    }
}
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

.progress {
    background-color: #e9ecef;
}
</style>
@endsection
