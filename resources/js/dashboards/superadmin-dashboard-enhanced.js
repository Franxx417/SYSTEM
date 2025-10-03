/**
 * Enhanced Superadmin Dashboard JavaScript
 * Handles all superadmin dashboard functionality including tabs, user management, 
 * database operations, security settings, and system maintenance
 */

class SuperadminDashboard {
    constructor() {
        this.currentTab = this.getCurrentTab();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeCurrentTab();
        this.setupAutoRefresh();
        this.setupNotifications();
    }

    getCurrentTab() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('tab') || 'overview';
    }

    bindEvents() {
        // Global event listeners
        document.addEventListener('DOMContentLoaded', () => {
            this.setupAlertAutoDismiss();
            this.setupFormValidation();
        });

        // Tab-specific event listeners
        this.bindOverviewEvents();
        this.bindUserManagementEvents();
        this.bindSecurityEvents();
        this.bindSystemEvents();
        this.bindDatabaseEvents();
        this.bindLogsEvents();
        this.bindBrandingEvents();
    }

    // =========================
    // OVERVIEW TAB FUNCTIONALITY
    // =========================
    bindOverviewEvents() {
        // Refresh system metrics
        const refreshBtn = document.querySelector('[data-action="refresh-metrics"]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshSystemMetrics());
        }
    }

    async refreshSystemMetrics() {
        try {
            const response = await this.makeRequest('/api/superadmin/metrics', 'GET');
            if (response.success) {
                this.updateMetricsDisplay(response.data);
                this.showNotification('System metrics refreshed successfully', 'success');
            }
        } catch (error) {
            this.showNotification('Failed to refresh metrics', 'error');
        }
    }

    updateMetricsDisplay(metrics) {
        // Update metric cards
        Object.keys(metrics).forEach(key => {
            const element = document.querySelector(`[data-metric="${key}"]`);
            if (element) {
                element.textContent = metrics[key];
            }
        });
    }

    // =========================
    // USER MANAGEMENT FUNCTIONALITY
    // =========================
    bindUserManagementEvents() {
        // User action buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="reset-password"]')) {
                const userId = e.target.getAttribute('data-user-id');
                this.resetUserPassword(userId);
            }
            
            if (e.target.matches('[data-action="toggle-user"]')) {
                const userId = e.target.getAttribute('data-user-id');
                this.toggleUserStatus(userId);
            }

            if (e.target.matches('[data-action="delete-user"]')) {
                const userId = e.target.getAttribute('data-user-id');
                this.deleteUser(userId);
            }
        });

        // Quick user creation form
        const quickCreateForm = document.querySelector('#quick-user-form');
        if (quickCreateForm) {
            quickCreateForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createUserQuick(new FormData(quickCreateForm));
            });
        }

        // User search and filter
        const userSearch = document.querySelector('#user-search');
        if (userSearch) {
            userSearch.addEventListener('input', (e) => {
                this.filterUsers(e.target.value);
            });
        }
    }

    async resetUserPassword(userId) {
        if (!confirm('Are you sure you want to reset this user\'s password?')) {
            return;
        }

        try {
            const response = await this.makeRequest('/api/superadmin/users/reset-password', 'POST', {
                user_id: userId
            });

            if (response.success) {
                this.showNotification(`Password reset successfully. New password: ${response.new_password}`, 'success');
                // Show password in a modal for better UX
                this.showPasswordModal(response.new_password);
            } else {
                this.showNotification(response.error || 'Failed to reset password', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to reset password', 'error');
        }
    }

    async toggleUserStatus(userId) {
        if (!confirm('Are you sure you want to toggle this user\'s status?')) {
            return;
        }

        try {
            const response = await this.makeRequest('/api/superadmin/users/toggle', 'POST', {
                user_id: userId
            });

            if (response.success) {
                this.showNotification('User status updated successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(response.error || 'Failed to update user status', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update user status', 'error');
        }
    }

    async deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await this.makeRequest('/api/superadmin/users/delete', 'DELETE', {
                user_id: userId
            });

            if (response.success) {
                this.showNotification('User deleted successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(response.error || 'Failed to delete user', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to delete user', 'error');
        }
    }

    async createUserQuick(formData) {
        try {
            const response = await this.makeRequest('/api/superadmin/users/create', 'POST', formData);

            if (response.success) {
                this.showNotification('User created successfully', 'success');
                document.querySelector('#quick-user-form').reset();
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(response.error || 'Failed to create user', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to create user', 'error');
        }
    }

    filterUsers(searchTerm) {
        const userRows = document.querySelectorAll('.user-row');
        const term = searchTerm.toLowerCase();

        userRows.forEach(row => {
            const userName = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
            const userEmail = row.querySelector('.user-email')?.textContent.toLowerCase() || '';
            const userRole = row.querySelector('.user-role')?.textContent.toLowerCase() || '';

            const matches = userName.includes(term) || userEmail.includes(term) || userRole.includes(term);
            row.style.display = matches ? '' : 'none';
        });
    }

    // =========================
    // SECURITY TAB FUNCTIONALITY
    // =========================
    bindSecurityEvents() {
        // Security settings form
        const securityForm = document.querySelector('#security-settings-form');
        if (securityForm) {
            securityForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateSecuritySettings(new FormData(securityForm));
            });
        }

        // Force logout all sessions
        const forceLogoutBtn = document.querySelector('[data-action="force-logout-all"]');
        if (forceLogoutBtn) {
            forceLogoutBtn.addEventListener('click', () => this.forceLogoutAllSessions());
        }

        // Session management
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="terminate-session"]')) {
                const sessionId = e.target.getAttribute('data-session-id');
                this.terminateSession(sessionId);
            }
        });
    }

    async updateSecuritySettings(formData) {
        try {
            const response = await this.makeRequest('/api/superadmin/security/update', 'POST', formData);

            if (response.success) {
                this.showNotification('Security settings updated successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to update security settings', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update security settings', 'error');
        }
    }

    async forceLogoutAllSessions() {
        if (!confirm('Are you sure you want to force logout all active sessions? This will log out all users.')) {
            return;
        }

        try {
            const response = await this.makeRequest('/api/superadmin/security/force-logout-all', 'POST');

            if (response.success) {
                this.showNotification('All sessions terminated successfully', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                this.showNotification(response.error || 'Failed to terminate sessions', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to terminate sessions', 'error');
        }
    }

    async terminateSession(sessionId) {
        if (!confirm('Are you sure you want to terminate this session?')) {
            return;
        }

        try {
            const response = await this.makeRequest('/api/superadmin/security/terminate-session', 'POST', {
                session_id: sessionId
            });

            if (response.success) {
                this.showNotification('Session terminated successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(response.error || 'Failed to terminate session', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to terminate session', 'error');
        }
    }

    // =========================
    // SYSTEM TAB FUNCTIONALITY
    // =========================
    bindSystemEvents() {
        // System maintenance actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="clear-cache"]')) {
                this.clearCache();
            }
            
            if (e.target.matches('[data-action="backup-system"]')) {
                this.backupSystem();
            }

            if (e.target.matches('[data-action="update-system"]')) {
                this.updateSystem();
            }

            if (e.target.matches('[data-action="restart-services"]')) {
                this.restartServices();
            }
        });

        // System info refresh
        const refreshSystemInfoBtn = document.querySelector('[data-action="refresh-system-info"]');
        if (refreshSystemInfoBtn) {
            refreshSystemInfoBtn.addEventListener('click', () => this.refreshSystemInfo());
        }
    }

    async clearCache() {
        if (!confirm('Are you sure you want to clear the system cache?')) {
            return;
        }

        try {
            const response = await this.makeRequest('/api/superadmin/system/clear-cache', 'POST');

            if (response.success) {
                this.showNotification('Cache cleared successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to clear cache', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to clear cache', 'error');
        }
    }

    async backupSystem() {
        if (!confirm('Are you sure you want to create a full system backup? This may take several minutes.')) {
            return;
        }

        try {
            this.showNotification('Backup started... This may take a few minutes.', 'info');
            const response = await this.makeRequest('/api/superadmin/system/backup', 'POST');

            if (response.success) {
                this.showNotification('System backup completed successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to create backup', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to create backup', 'error');
        }
    }

    async updateSystem() {
        if (!confirm('Are you sure you want to update the system? This will restart the application.')) {
            return;
        }

        try {
            this.showNotification('System update started...', 'info');
            const response = await this.makeRequest('/api/superadmin/system/update', 'POST');

            if (response.success) {
                this.showNotification('System updated successfully. Restarting...', 'success');
                setTimeout(() => location.reload(), 3000);
            } else {
                this.showNotification(response.error || 'Failed to update system', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update system', 'error');
        }
    }

    async restartServices() {
        if (!confirm('Are you sure you want to restart system services? This may cause temporary downtime.')) {
            return;
        }

        try {
            this.showNotification('Restarting services...', 'info');
            const response = await this.makeRequest('/api/superadmin/system/restart-services', 'POST');

            if (response.success) {
                this.showNotification('Services restarted successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to restart services', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to restart services', 'error');
        }
    }

    async refreshSystemInfo() {
        try {
            const response = await this.makeRequest('/api/superadmin/system/info', 'GET');
            if (response.success) {
                this.updateSystemInfoDisplay(response.data);
                this.showNotification('System information refreshed', 'success');
            }
        } catch (error) {
            this.showNotification('Failed to refresh system information', 'error');
        }
    }

    updateSystemInfoDisplay(info) {
        // Update system info display elements
        Object.keys(info).forEach(key => {
            const element = document.querySelector(`[data-system-info="${key}"]`);
            if (element) {
                element.textContent = info[key];
            }
        });
    }

    // =========================
    // DATABASE TAB FUNCTIONALITY
    // =========================
    bindDatabaseEvents() {
        // Database tools
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="optimize-database"]')) {
                this.optimizeDatabase();
            }
            
            if (e.target.matches('[data-action="check-integrity"]')) {
                this.checkDatabaseIntegrity();
            }

            if (e.target.matches('[data-action="repair-tables"]')) {
                this.repairTables();
            }

            if (e.target.matches('[data-action="create-backup"]')) {
                this.createDatabaseBackup();
            }

            if (e.target.matches('[data-action="load-table-info"]')) {
                this.loadTableInfo();
            }
        });

        // Auto-load table info if on database tab
        if (this.currentTab === 'database') {
            setTimeout(() => this.loadTableInfo(), 500);
        }
    }

    async optimizeDatabase() {
        if (!confirm('Are you sure you want to optimize the database? This may take several minutes.')) {
            return;
        }

        try {
            this.showNotification('Optimizing database...', 'info');
            const response = await this.makeRequest('/api/superadmin/database/optimize', 'POST');

            if (response.success) {
                this.showNotification('Database optimized successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to optimize database', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to optimize database', 'error');
        }
    }

    async checkDatabaseIntegrity() {
        try {
            this.showNotification('Checking database integrity...', 'info');
            const response = await this.makeRequest('/api/superadmin/database/check-integrity', 'POST');

            if (response.success) {
                this.showNotification('Database integrity check completed', 'success');
                this.showDatabaseIntegrityResults(response.data);
            } else {
                this.showNotification(response.error || 'Failed to check database integrity', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to check database integrity', 'error');
        }
    }

    async repairTables() {
        if (!confirm('Are you sure you want to repair database tables? This may take several minutes.')) {
            return;
        }

        try {
            this.showNotification('Repairing database tables...', 'info');
            const response = await this.makeRequest('/api/superadmin/database/repair', 'POST');

            if (response.success) {
                this.showNotification('Database tables repaired successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to repair tables', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to repair tables', 'error');
        }
    }

    async createDatabaseBackup() {
        if (!confirm('Are you sure you want to create a database backup?')) {
            return;
        }

        try {
            this.showNotification('Creating database backup...', 'info');
            const response = await this.makeRequest('/api/superadmin/database/backup', 'POST');

            if (response.success) {
                this.showNotification('Database backup created successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to create backup', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to create backup', 'error');
        }
    }

    async loadTableInfo() {
        const tableInfoDiv = document.getElementById('table-info');
        if (!tableInfoDiv) return;

        tableInfoDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading table information...</div>';

        try {
            const response = await this.makeRequest('/api/superadmin/database/table-info', 'GET');

            if (response.success) {
                this.displayTableInfo(response.data, tableInfoDiv);
            } else {
                tableInfoDiv.innerHTML = `<div class="alert alert-danger">${response.error || 'Failed to load table information'}</div>`;
            }
        } catch (error) {
            tableInfoDiv.innerHTML = '<div class="alert alert-danger">Failed to load table information</div>';
        }
    }

    displayTableInfo(tables, container) {
        let html = `
            <table class="table table-sm table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Table Name</th>
                        <th>Records</th>
                        <th>Columns</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;

        tables.forEach(table => {
            const countDisplay = table.error ? 
                `<span class="badge bg-danger" title="${table.error}">Error</span>` : 
                `<span class="badge bg-info">${table.count || 0}</span>`;
                
            html += `
                <tr>
                    <td><strong>${table.name}</strong></td>
                    <td>${countDisplay}</td>
                    <td><span class="badge bg-secondary">${table.columns?.length || 0}</span></td>
                    <td><span class="text-muted">${table.size || 'N/A'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="superadminDashboard.viewTableDetails('${table.name}')" ${table.error ? 'disabled' : ''}>
                            View Details
                        </button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    async viewTableDetails(tableName) {
        try {
            // Show loading notification
            this.showNotification('Loading table details...', 'info');
            
            const response = await this.makeRequest(`/api/superadmin/database/table-details/${tableName}`, 'GET');
            
            if (response.success && response.data) {
                this.showTableDetailsModal(tableName, response.data);
            } else {
                this.showNotification(response.error || 'Failed to load table details', 'error');
            }
        } catch (error) {
            console.error('Failed to load table details:', error);
            this.showNotification('Failed to load table details: ' + error.message, 'error');
        }
    }

    showDatabaseIntegrityResults(results) {
        // Create and show modal with integrity check results
        const modal = this.createModal('Database Integrity Check Results', this.formatIntegrityResults(results));
        modal.show();
    }

    formatIntegrityResults(results) {
        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Table</th><th>Status</th><th>Issues</th></tr></thead><tbody>';
        
        results.forEach(result => {
            const statusClass = result.status === 'OK' ? 'success' : 'danger';
            html += `
                <tr>
                    <td>${result.table}</td>
                    <td><span class="badge bg-${statusClass}">${result.status}</span></td>
                    <td>${result.issues || 'None'}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        return html;
    }

    // =========================
    // LOGS TAB FUNCTIONALITY
    // =========================
    bindLogsEvents() {
        // Log management actions
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="refresh-logs"]')) {
                this.refreshLogs();
            }
            
            if (e.target.matches('[data-action="clear-logs"]')) {
                this.clearLogs();
            }
        });

        // Log level filter
        const logLevelFilter = document.getElementById('log-level-filter');
        if (logLevelFilter) {
            logLevelFilter.addEventListener('change', (e) => {
                this.filterLogs(e.target.value);
            });
        }

        // Log settings form
        const logSettingsForm = document.getElementById('log-settings-form');
        if (logSettingsForm) {
            logSettingsForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateLogSettings(new FormData(logSettingsForm));
            });
        }

        // Auto-refresh logs if on logs tab
        if (this.currentTab === 'logs') {
            setTimeout(() => this.refreshLogs(), 500);
        }
    }

    async refreshLogs() {
        const container = document.getElementById('logs-container');
        if (!container) return;

        container.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading logs...</div>';

        try {
            const response = await this.makeRequest('/api/superadmin/logs/recent', 'GET');

            if (response.success) {
                this.displayLogs(response.data.logs, container);
                this.updateLogStats(response.data.stats);
            } else {
                container.innerHTML = `<div class="alert alert-danger">${response.error || 'Failed to load logs'}</div>`;
            }
        } catch (error) {
            container.innerHTML = '<div class="alert alert-danger">Failed to load logs</div>';
        }
    }

    displayLogs(logs, container) {
        if (!logs || logs.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4">No log entries found</div>';
            return;
        }

        let html = '<div class="log-entries">';
        logs.forEach(log => {
            const levelClass = this.getLogLevelClass(log.level);
            html += `
                <div class="log-entry border-start border-3 border-${levelClass} p-3 mb-2 bg-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-1">
                                <span class="badge bg-${levelClass} me-2">${log.level.toUpperCase()}</span>
                                <small class="text-muted">${log.timestamp}</small>
                            </div>
                            <div class="log-message">${log.message}</div>
                            ${log.context ? `<div class="log-context mt-2"><small class="text-muted">Context: ${JSON.stringify(log.context)}</small></div>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    getLogLevelClass(level) {
        const levelMap = {
            'error': 'danger',
            'warning': 'warning',
            'info': 'info',
            'debug': 'secondary'
        };
        return levelMap[level.toLowerCase()] || 'secondary';
    }

    updateLogStats(stats) {
        Object.keys(stats).forEach(key => {
            const element = document.querySelector(`[data-log-stat="${key}"]`);
            if (element) {
                element.textContent = stats[key];
            }
        });
    }

    filterLogs(level) {
        const logEntries = document.querySelectorAll('.log-entry');
        logEntries.forEach(entry => {
            if (!level) {
                entry.style.display = '';
            } else {
                const badge = entry.querySelector('.badge');
                const entryLevel = badge ? badge.textContent.toLowerCase() : '';
                entry.style.display = entryLevel === level ? '' : 'none';
            }
        });
    }

    async clearLogs() {
        if (!confirm('Are you sure you want to clear all logs? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await this.makeRequest('/api/superadmin/logs/clear', 'POST');

            if (response.success) {
                this.showNotification('Logs cleared successfully', 'success');
                this.refreshLogs();
            } else {
                this.showNotification(response.error || 'Failed to clear logs', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to clear logs', 'error');
        }
    }

    async updateLogSettings(formData) {
        try {
            const response = await this.makeRequest('/api/superadmin/logs/settings', 'POST', formData);

            if (response.success) {
                this.showNotification('Log settings updated successfully', 'success');
            } else {
                this.showNotification(response.error || 'Failed to update log settings', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update log settings', 'error');
        }
    }

    // =========================
    // BRANDING TAB FUNCTIONALITY
    // =========================
    bindBrandingEvents() {
        // Logo preview
        const logoInput = document.querySelector('input[name="logo"]');
        if (logoInput) {
            logoInput.addEventListener('change', (e) => {
                this.previewLogo(e.target.files[0]);
            });
        }

        // Branding form
        const brandingForm = document.querySelector('#branding-form');
        if (brandingForm) {
            brandingForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateBranding(new FormData(brandingForm));
            });
        }
    }

    previewLogo(file) {
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            let preview = document.querySelector('#logo-preview');
            if (!preview) {
                preview = document.createElement('div');
                preview.id = 'logo-preview';
                preview.className = 'mt-2';
                document.querySelector('input[name="logo"]').parentNode.appendChild(preview);
            }
            preview.innerHTML = `<img src="${e.target.result}" alt="Logo Preview" style="height:48px;width:auto" class="border rounded">`;
        };
        reader.readAsDataURL(file);
    }

    async updateBranding(formData) {
        try {
            const response = await this.makeRequest('/api/superadmin/branding/update', 'POST', formData);

            if (response.success) {
                this.showNotification('Branding updated successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showNotification(response.error || 'Failed to update branding', 'error');
            }
        } catch (error) {
            this.showNotification('Failed to update branding', 'error');
        }
    }

    // =========================
    // UTILITY FUNCTIONS
    // =========================
    async makeRequest(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data) {
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        try {
            const response = await fetch(url, options);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Request failed:', error);
            throw error;
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    showPasswordModal(password) {
        const modal = this.createModal('Password Reset', `
            <div class="alert alert-warning">
                <strong>New Password:</strong> 
                <code class="fs-5">${password}</code>
            </div>
            <p>Please save this password securely. The user should change it upon first login.</p>
        `);
        modal.show();
    }

    showTableDetailsModal(tableName, details) {
        const modalId = 'table-details-modal-' + Date.now();
        
        // Build columns table
        const columnsTable = details.columns && details.columns.length > 0 ? `
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-striped table-hover">
                    <thead class="sticky-top bg-light">
                        <tr>
                            <th>Column Name</th>
                            <th>Data Type</th>
                            <th>Nullable</th>
                            <th>Identity</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${details.columns.map(col => `
                            <tr>
                                <td><strong>${col.name}</strong></td>
                                <td><code class="text-primary">${col.type}</code></td>
                                <td><span class="badge bg-${col.nullable === 'YES' ? 'warning' : 'success'}">${col.nullable}</span></td>
                                <td><span class="badge bg-${col.identity === 'YES' ? 'info' : 'secondary'}">${col.identity}</span></td>
                                <td><small class="text-muted">${col.default}</small></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-muted text-center py-3">No column information available</p>';
        
        // Build indexes table
        const indexesTable = details.indexes && details.indexes.length > 0 ? `
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Index Name</th>
                            <th>Type</th>
                            <th>Unique</th>
                            <th>Primary</th>
                            <th>Columns</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${details.indexes.map(idx => `
                            <tr>
                                <td><strong>${idx.name}</strong></td>
                                <td><span class="badge bg-secondary">${idx.type}</span></td>
                                <td><span class="badge bg-${idx.unique === 'YES' ? 'success' : 'secondary'}">${idx.unique}</span></td>
                                <td><span class="badge bg-${idx.primary === 'YES' ? 'primary' : 'secondary'}">${idx.primary}</span></td>
                                <td><small>${idx.columns.join(', ')}</small></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-muted text-center py-3">No indexes found</p>';
        
        // Build foreign keys table
        const foreignKeysTable = details.foreign_keys && details.foreign_keys.length > 0 ? `
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Constraint Name</th>
                            <th>Column</th>
                            <th>References</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${details.foreign_keys.map(fk => `
                            <tr>
                                <td><strong>${fk.name}</strong></td>
                                <td><code>${fk.column}</code></td>
                                <td><code class="text-success">${fk.references}</code></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-muted text-center py-3">No foreign key constraints found</p>';
        
        // Build sample data table
        const sampleDataTable = details.sample_data && details.sample_data.length > 0 ? `
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-striped table-hover">
                    <thead class="sticky-top bg-light">
                        <tr>
                            ${Object.keys(details.sample_data[0]).map(key => `<th>${key}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${details.sample_data.map(row => `
                            <tr>
                                ${Object.values(row).map(val => {
                                    const displayVal = val === null ? '<em class="text-muted">NULL</em>' : 
                                                      typeof val === 'object' ? JSON.stringify(val) : 
                                                      String(val).length > 100 ? String(val).substring(0, 100) + '...' : val;
                                    return `<td><small>${displayVal}</small></td>`;
                                }).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        ` : '<p class="text-muted text-center py-3">No sample data available</p>';
        
        // Build the complete modal content
        const modalContent = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-table me-2"></i>Table Details: <strong>${tableName}</strong>
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Table Summary -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Records</h6>
                                            <h3 class="mb-0 text-primary">${details.count || 0}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Columns</h6>
                                            <h3 class="mb-0 text-success">${details.columns?.length || 0}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Size</h6>
                                            <h3 class="mb-0 text-info">${details.size || 'N/A'}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning">
                                        <div class="card-body text-center">
                                            <h6 class="text-muted mb-1">Engine</h6>
                                            <h3 class="mb-0 text-warning" style="font-size: 1.2rem;">${details.engine || 'N/A'}</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tabbed Content -->
                            <ul class="nav nav-tabs" id="tableDetailsTabs-${modalId}" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="columns-tab-${modalId}" data-bs-toggle="tab" 
                                            data-bs-target="#columns-${modalId}" type="button" role="tab">
                                        <i class="bi bi-list-columns-reverse me-1"></i>Columns (${details.columns?.length || 0})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="indexes-tab-${modalId}" data-bs-toggle="tab" 
                                            data-bs-target="#indexes-${modalId}" type="button" role="tab">
                                        <i class="bi bi-key me-1"></i>Indexes (${details.indexes?.length || 0})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="fk-tab-${modalId}" data-bs-toggle="tab" 
                                            data-bs-target="#fk-${modalId}" type="button" role="tab">
                                        <i class="bi bi-link-45deg me-1"></i>Foreign Keys (${details.foreign_keys?.length || 0})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="data-tab-${modalId}" data-bs-toggle="tab" 
                                            data-bs-target="#data-${modalId}" type="button" role="tab">
                                        <i class="bi bi-table me-1"></i>Sample Data (${details.sample_data?.length || 0})
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content border border-top-0 p-3" id="tableDetailsTabContent-${modalId}">
                                <div class="tab-pane fade show active" id="columns-${modalId}" role="tabpanel">
                                    ${columnsTable}
                                </div>
                                <div class="tab-pane fade" id="indexes-${modalId}" role="tabpanel">
                                    ${indexesTable}
                                </div>
                                <div class="tab-pane fade" id="fk-${modalId}" role="tabpanel">
                                    ${foreignKeysTable}
                                </div>
                                <div class="tab-pane fade" id="data-${modalId}" role="tabpanel">
                                    ${sampleDataTable}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalContent);
        const modalElement = document.getElementById(modalId);
        
        // Clean up modal when hidden
        modalElement.addEventListener('hidden.bs.modal', () => {
            modalElement.remove();
        });
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }

    createModal(title, content) {
        const modalId = 'dynamic-modal-' + Date.now();
        const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">${content}</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalElement = document.getElementById(modalId);
        
        // Clean up modal when hidden
        modalElement.addEventListener('hidden.bs.modal', () => {
            modalElement.remove();
        });

        return new bootstrap.Modal(modalElement);
    }

    setupAlertAutoDismiss() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        });
    }

    setupFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    }

    validateForm(form) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    initializeCurrentTab() {
        // Tab-specific initialization
        switch (this.currentTab) {
            case 'database':
                setTimeout(() => this.loadTableInfo(), 500);
                break;
            case 'overview':
                setTimeout(() => this.refreshSystemMetrics(), 500);
                break;
            case 'logs':
                setTimeout(() => this.loadRecentLogs(), 500);
                break;
        }
    }

    setupAutoRefresh() {
        // Auto-refresh certain data every 30 seconds
        if (this.currentTab === 'overview') {
            setInterval(() => {
                this.refreshSystemMetrics();
            }, 30000);
        }
    }

    setupNotifications() {
        // Setup real-time notifications if WebSocket is available
        if (typeof io !== 'undefined') {
            const socket = io();
            socket.on('admin-notification', (data) => {
                this.showNotification(data.message, data.type);
            });
        }
    }


    async loadRecentLogs() {
        const logsContainer = document.getElementById('logs-container');
        if (!logsContainer) return;

        logsContainer.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm" role="status"></div> Loading logs...</div>';

        try {
            const response = await this.makeRequest('/api/superadmin/logs/recent', 'GET');
            if (response.logs) {
                this.displayLogs(response.logs, logsContainer);
            } else {
                logsContainer.innerHTML = '<div class="alert alert-danger">Failed to load logs</div>';
            }
        } catch (error) {
            logsContainer.innerHTML = '<div class="alert alert-danger">Failed to load logs</div>';
        }
    }

    displayLogs(logs, container) {
        if (!logs || logs.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4">No log entries found</div>';
            return;
        }

        let html = '<div class="log-entries">';
        logs.forEach(log => {
            const level = this.extractLogLevel(log);
            const levelClass = this.getLogLevelClass(level);
            
            html += `
                <div class="log-entry mb-2 p-2 border-start border-3 border-${levelClass} bg-light">
                    <div class="small text-muted">${this.formatLogTimestamp(log)}</div>
                    <div class="log-content">${this.escapeHtml(log)}</div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }

    extractLogLevel(logLine) {
        const match = logLine.match(/\.(ERROR|WARNING|INFO|DEBUG)\]/);
        return match ? match[1].toLowerCase() : 'info';
    }

    getLogLevelClass(level) {
        const classes = {
            'error': 'danger',
            'warning': 'warning',
            'info': 'info',
            'debug': 'secondary'
        };
        return classes[level] || 'info';
    }

    formatLogTimestamp(logLine) {
        const match = logLine.match(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/);
        return match ? match[1] : 'Unknown time';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global functions for backward compatibility
function viewPO(poNo) {
    window.open('/po/' + poNo, '_blank');
}

function resetPassword(userId) {
    if (window.superadminDashboard) {
        window.superadminDashboard.resetUserPassword(userId);
    }
}

function toggleUser(userId) {
    if (window.superadminDashboard) {
        window.superadminDashboard.toggleUserStatus(userId);
    }
}

function loadTableInfo() {
    if (window.superadminDashboard) {
        window.superadminDashboard.loadTableInfo();
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    try {
        window.superadminDashboard = new SuperadminDashboard();
        console.log('SuperadminDashboard initialized successfully');
    } catch (error) {
        console.error('Failed to initialize SuperadminDashboard:', error);
    }
});
