/**
 * Enhanced Superadmin Dashboard JavaScript
 * Handles all superadmin dashboard functionality including tabs, user management, 
 * database operations, security settings, and system maintenance
 */

class SuperadminDashboard {
    constructor() {
        this.currentTab = this.getCurrentTab();
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.poSystemState = {
            page: 1,
            sort_by: 'created_at',
            sort_dir: 'desc'
        };
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
        this.bindPurchaseOrdersEvents();
        this.bindUserManagementEvents();
        this.bindSecurityEvents();
        this.bindSystemEvents();
        this.bindDatabaseEvents();
        this.bindLogsEvents();
        this.bindBrandingEvents();
    }

    // =========================
    // PURCHASE ORDERS TAB (SYSTEM-WIDE)
    // =========================
    bindPurchaseOrdersEvents() {
        const table = document.getElementById('po-system-table');
        if (!table) return;

        const refreshBtn = document.querySelector('[data-action="po-refresh"]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.loadSystemPurchaseOrders({ noCache: true }));
        }

        const form = document.getElementById('po-system-filters');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.poSystemState.page = 1;
                this.loadSystemPurchaseOrders();
            });
        }

        // Sorting
        table.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-po-sort]');
            if (!btn) return;
            const sortBy = btn.getAttribute('data-po-sort');
            if (!sortBy) return;

            if (this.poSystemState.sort_by === sortBy) {
                this.poSystemState.sort_dir = this.poSystemState.sort_dir === 'asc' ? 'desc' : 'asc';
            } else {
                this.poSystemState.sort_by = sortBy;
                this.poSystemState.sort_dir = 'asc';
            }
            this.poSystemState.page = 1;
            this.loadSystemPurchaseOrders();
        });

        // Pagination
        const pagination = document.getElementById('po-system-pagination');
        if (pagination) {
            pagination.addEventListener('click', (e) => {
                const link = e.target.closest('[data-po-page]');
                if (!link) return;
                e.preventDefault();
                const page = Number(link.getAttribute('data-po-page'));
                if (!page || page < 1) return;
                this.poSystemState.page = page;
                this.loadSystemPurchaseOrders();
            });
        }
    }

    async loadSystemPurchaseOrders(options = {}) {
        const table = document.getElementById('po-system-table');
        const tbody = document.getElementById('po-system-tbody');
        const loading = document.getElementById('po-system-loading');
        const errorBox = document.getElementById('po-system-error');
        const emptyBox = document.getElementById('po-system-empty');
        const summary = document.getElementById('po-system-summary');
        const pagination = document.getElementById('po-system-pagination');
        const form = document.getElementById('po-system-filters');

        if (!table || !tbody) return;

        const noCache = !!options.noCache;

        if (loading) loading.style.display = '';
        if (errorBox) {
            errorBox.style.display = 'none';
            errorBox.innerHTML = '';
        }
        if (emptyBox) emptyBox.style.display = 'none';
        tbody.innerHTML = '';
        if (summary) summary.textContent = '';
        if (pagination) pagination.innerHTML = '';

        try {
            const params = new URLSearchParams();
            params.set('page', String(this.poSystemState.page));
            params.set('sort_by', this.poSystemState.sort_by);
            params.set('sort_dir', this.poSystemState.sort_dir);
            if (noCache) params.set('no_cache', '1');

            if (form) {
                const fd = new FormData(form);
                for (const [k, v] of fd.entries()) {
                    if (v !== null && String(v).trim() !== '') {
                        params.set(k, String(v));
                    }
                }
            }

            const response = await this.makeRequest(`/api/superadmin/purchase-orders?${params.toString()}`, 'GET');

            if (!response.success) {
                throw new Error(response.error || 'Failed to load purchase orders');
            }

            const rows = Array.isArray(response.data) ? response.data : [];
            const meta = response.meta || {};

            this.populatePoSystemFilters(meta.filters);
            this.renderPoSystemRows(rows);
            this.renderPoSystemSummary(meta);
            this.renderPoSystemPagination(meta);

            if (rows.length === 0 && emptyBox) {
                emptyBox.style.display = '';
            }
        } catch (error) {
            const message = (error && error.responseData && (error.responseData.error || error.responseData.message))
                ? (error.responseData.error || error.responseData.message)
                : (error.message || 'Failed to load purchase orders');

            if (errorBox) {
                errorBox.style.display = '';
                errorBox.innerHTML = `<div class="alert alert-danger mb-0">${this.sanitizeHTML(message)}</div>`;
            }
        } finally {
            if (loading) loading.style.display = 'none';
        }
    }

    populatePoSystemFilters(filters) {
        if (!filters) return;

        const form = document.getElementById('po-system-filters');
        if (!form) return;

        const statusSelect = form.querySelector('select[name="status"]');
        const supplierSelect = form.querySelector('select[name="supplier_id"]');

        if (statusSelect && Array.isArray(filters.statuses) && statusSelect.options.length <= 1) {
            filters.statuses.forEach(st => {
                const opt = document.createElement('option');
                opt.value = st.status_id;
                opt.textContent = st.status_name;
                statusSelect.appendChild(opt);
            });
        }

        if (supplierSelect && Array.isArray(filters.suppliers) && supplierSelect.options.length <= 1) {
            filters.suppliers.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.supplier_id;
                opt.textContent = s.name;
                supplierSelect.appendChild(opt);
            });
        }
    }

    renderPoSystemRows(rows) {
        const tbody = document.getElementById('po-system-tbody');
        if (!tbody) return;

        const formatMoney = (v) => {
            const num = Number(v);
            if (isNaN(num)) return '₱0.00';
            return `₱${num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        };

        tbody.innerHTML = rows.map(r => {
            const poNo = this.sanitizeHTML(String(r.purchase_order_no || ''));
            const date = this.sanitizeHTML(String(r.date_requested || ''));
            const vendor = this.sanitizeHTML(String(r.supplier_name || '—'));
            const status = this.sanitizeHTML(String(r.status_name || '—'));
            const total = formatMoney(r.total);
            return `
                <tr>
                    <td>${poNo}</td>
                    <td>${date}</td>
                    <td>${vendor}</td>
                    <td class="text-end">${total}</td>
                    <td>${status}</td>
                </tr>
            `;
        }).join('');
    }

    renderPoSystemSummary(meta) {
        const summary = document.getElementById('po-system-summary');
        if (!summary) return;
        const from = meta.from || 0;
        const to = meta.to || 0;
        const total = meta.total || 0;
        summary.textContent = total ? `Showing ${from}-${to} of ${total}` : '';
    }

    renderPoSystemPagination(meta) {
        const pagination = document.getElementById('po-system-pagination');
        if (!pagination) return;
        const current = Number(meta.current_page || 1);
        const last = Number(meta.last_page || 1);
        if (last <= 1) {
            pagination.innerHTML = '';
            return;
        }

        const mkItem = (page, label, disabled = false, active = false) => {
            const cls = ['page-item'];
            if (disabled) cls.push('disabled');
            if (active) cls.push('active');
            const attrs = disabled ? '' : `href="#" data-po-page="${page}"`;
            return `
                <li class="${cls.join(' ')}">
                    <a class="page-link" ${attrs}>${label}</a>
                </li>
            `;
        };

        const windowSize = 3;
        const start = Math.max(1, current - windowSize);
        const end = Math.min(last, current + windowSize);

        let html = '<ul class="pagination pagination-sm mb-0">';
        html += mkItem(current - 1, '&laquo;', current <= 1);

        if (start > 1) {
            html += mkItem(1, '1', false, current === 1);
            if (start > 2) {
                html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
        }

        for (let p = start; p <= end; p++) {
            html += mkItem(p, String(p), false, p === current);
        }

        if (end < last) {
            if (end < last - 1) {
                html += '<li class="page-item disabled"><span class="page-link">…</span></li>';
            }
            html += mkItem(last, String(last), false, current === last);
        }

        html += mkItem(current + 1, '&raquo;', current >= last);
        html += '</ul>';
        pagination.innerHTML = html;
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

        // Auto-refresh system metrics every 30 seconds
        this.startAutoRefresh();
    }

    startAutoRefresh() {
        // Only auto-refresh if we're on the overview tab
        if (window.location.search.includes('tab=overview') || !window.location.search.includes('tab=')) {
            setInterval(() => {
                this.refreshSystemMetrics(true); // Silent refresh
            }, 30000); // 30 seconds
        }
    }

    async refreshSystemMetrics(silent = false) {
        try {
            const response = await this.makeRequest('/api/superadmin/metrics', 'GET');
            if (response.success) {
                this.updateMetricsDisplay(response.data);
                if (!silent) {
                    this.showNotification('System metrics refreshed successfully', 'success');
                }
            }
        } catch (error) {
            if (!silent) {
                this.showNotification('Failed to refresh metrics', 'error');
            }
            console.error('System metrics refresh failed:', error);
        }
    }

    updateMetricsDisplay(metrics) {
        // Update basic metric cards
        Object.keys(metrics).forEach(key => {
            const element = document.querySelector(`[data-metric="${key}"]`);
            if (element && key !== 'system_performance') {
                element.textContent = metrics[key];
            }
        });

        // Update system performance metrics if available
        if (metrics.system_performance) {
            this.updateSystemPerformanceMetrics(metrics.system_performance);
        }
    }

    updateSystemPerformanceMetrics(systemMetrics) {
        // Update CPU usage
        const cpuElement = document.querySelector('[data-metric="cpu_usage"]');
        if (cpuElement && systemMetrics.cpu) {
            cpuElement.textContent = `${systemMetrics.cpu.usage_percent}%`;
        }

        // Update Memory usage
        const memoryElement = document.querySelector('[data-metric="memory_usage"]');
        if (memoryElement && systemMetrics.memory && systemMetrics.memory.system) {
            memoryElement.textContent = `${systemMetrics.memory.system.usage_percent}%`;
        }

        // Update Disk usage
        const diskElement = document.querySelector('[data-metric="disk_usage"]');
        if (diskElement && systemMetrics.disk) {
            diskElement.textContent = `${systemMetrics.disk.usage_percent}%`;
        }

        // Update Network connections
        const networkElement = document.querySelector('[data-metric="network_connections"]');
        if (networkElement && systemMetrics.network) {
            networkElement.textContent = systemMetrics.network.active_connections;
        }

        // Update database connectivity badge
        const dbBadge = document.querySelector('.badge.bg-success, .badge.bg-danger');
        if (dbBadge && systemMetrics.network) {
            dbBadge.className = `badge bg-${systemMetrics.network.database_connectivity ? 'success' : 'danger'}`;
            dbBadge.textContent = systemMetrics.network.database_connectivity ? 'OK' : 'Error';
        }
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

        // Refresh security overview
        const refreshBtn = document.querySelector('[data-action="security-refresh"]');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.refreshSecurityOverview());
        }

        // Refresh security alerts
        const refreshAlertsBtn = document.querySelector('[data-action="security-refresh-alerts"]');
        if (refreshAlertsBtn) {
            refreshAlertsBtn.addEventListener('click', () => this.refreshSecurityAlerts());
        }

        const severityFilter = document.getElementById('alert-severity-filter');
        if (severityFilter) {
            severityFilter.addEventListener('change', () => this.refreshSecurityAlerts());
        }

        // Force logout all sessions
        const forceLogoutBtn = document.querySelector('[data-action="force-logout-all"]');
        if (forceLogoutBtn) {
            forceLogoutBtn.addEventListener('click', () => this.forceLogoutAllSessions());
        }
        // Active Sessions UI removed; no terminate-session bindings
    }

    async refreshSecurityOverview(silent = false) {
        const statusBadge = document.getElementById('security-status');
        if (!silent && statusBadge) {
            statusBadge.className = 'badge bg-secondary';
            statusBadge.textContent = 'Loading';
        }

        try {
            const response = await this.makeRequest('/api/superadmin/security/stats', 'GET');
            if (response.success && response.data) {
                this.updateSecurityOverviewCards(response.data);
                this.updateSecurityStatusBadge(response.data);
                if (!silent) {
                    this.showNotification('Security data refreshed', 'success');
                }
            } else if (!silent) {
                this.showNotification(response.error || 'Failed to refresh security data', 'error');
            }
        } catch (error) {
            if (!silent) {
                const serverMsg = error && error.responseData && (error.responseData.error || error.responseData.message) ?
                    (error.responseData.error || error.responseData.message) : null;
                this.showNotification(serverMsg ? `Failed to refresh security data: ${serverMsg}` : 'Failed to refresh security data', 'error');
            }
            if (statusBadge) {
                statusBadge.className = 'badge bg-warning';
                statusBadge.textContent = 'Error';
            }
        }
    }

    updateSecurityOverviewCards(stats) {
        const activeSessionsEl = document.getElementById('active-sessions-count');
        const loginRateEl = document.getElementById('login-success-rate');
        const alertsCountEl = document.getElementById('security-alerts-count');
        const activitiesEl = document.getElementById('activities-24h');

        if (activeSessionsEl) activeSessionsEl.textContent = this.formatNumber(stats.active_sessions_count, '0');
        if (alertsCountEl) alertsCountEl.textContent = this.formatNumber(stats.unresolved_alerts, '0');
        if (activitiesEl) activitiesEl.textContent = this.formatNumber(stats.activities_last_24h, '0');
        if (loginRateEl) {
            const rate = (stats.login_success_rate === null || stats.login_success_rate === undefined) ? 0 : Number(stats.login_success_rate);
            loginRateEl.textContent = `${isNaN(rate) ? 0 : rate.toFixed(1)}%`;
        }
    }

    updateSecurityStatusBadge(stats) {
        const statusBadge = document.getElementById('security-status');
        if (!statusBadge) return;

        const critical = Number(stats.critical_alerts || 0);
        const unresolved = Number(stats.unresolved_alerts || 0);

        if (critical > 0) {
            statusBadge.className = 'badge bg-danger';
            statusBadge.textContent = 'Critical';
        } else if (unresolved > 0) {
            statusBadge.className = 'badge bg-warning';
            statusBadge.textContent = 'Alerts';
        } else {
            statusBadge.className = 'badge bg-success';
            statusBadge.textContent = 'Secure';
        }
    }

    async refreshSecurityAlerts(silent = false) {
        const container = document.getElementById('alerts-container');
        if (!container) return;

        if (!silent) {
            container.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm" role="status"></div> Loading alerts...</div>';
        }

        const severity = document.getElementById('alert-severity-filter')?.value;
        const qs = severity ? `?severity=${encodeURIComponent(severity)}` : '';

        try {
            const response = await this.makeRequest(`/api/superadmin/security/alerts${qs}`, 'GET');
            if (response.success && response.data) {
                const alerts = response.data.alerts || [];
                this.renderSecurityAlerts(alerts, container);
                if (!silent) {
                    this.showNotification('Security alerts refreshed', 'success');
                }
            } else {
                container.innerHTML = '<div class="alert alert-danger">Failed to load alerts</div>';
                if (!silent) {
                    this.showNotification(response.error || 'Failed to load alerts', 'error');
                }
            }
        } catch (error) {
            container.innerHTML = '<div class="alert alert-danger">Failed to load alerts</div>';
            if (!silent) {
                const serverMsg = error && error.responseData && (error.responseData.error || error.responseData.message) ?
                    (error.responseData.error || error.responseData.message) : null;
                this.showNotification(serverMsg ? `Failed to load alerts: ${serverMsg}` : 'Failed to load alerts', 'error');
            }
        }
    }

    renderSecurityAlerts(alerts, container) {
        if (!alerts || alerts.length === 0) {
            container.innerHTML = `
                <div class="text-center text-success py-4">
                    <i class="fas fa-shield-check fa-3x mb-3"></i>
                    <h5>No Security Alerts</h5>
                    <p class="text-muted">Your system is secure with no pending alerts.</p>
                </div>
            `;
            return;
        }

        const severityClass = (sev) => {
            const s = (sev || '').toLowerCase();
            if (s === 'critical') return 'danger';
            if (s === 'high') return 'warning';
            if (s === 'medium') return 'info';
            if (s === 'low') return 'secondary';
            return 'secondary';
        };

        let html = '<div class="list-group">';
        alerts.forEach(a => {
            const sev = a.severity || a.type || 'info';
            const title = a.title || 'Security Alert';
            const description = a.description || a.message || '';
            const createdAt = a.created_at || '';
            html += `
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="me-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-${severityClass(sev)}">${this.sanitizeHTML(String(sev)).toUpperCase()}</span>
                                <strong>${this.sanitizeHTML(String(title))}</strong>
                            </div>
                            <div class="text-muted small">${this.sanitizeHTML(String(description))}</div>
                        </div>
                        <div class="text-muted small">${this.sanitizeHTML(String(createdAt))}</div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
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

    // terminateSession removed with Active Sessions UI

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
        if (!tableInfoDiv) {
            console.error('[Database] Table info container not found');
            return;
        }

        // Show loading state
        tableInfoDiv.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-muted">Loading table information...</div>
            </div>
        `;

        try {
            const response = await this.makeRequest('/api/superadmin/database/table-info', 'GET');

            // Debug logging in development
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.log('[Database] API Response:', response);
                console.log('[Database] Data received:', response.data);
            }

            if (response && response.success && response.data) {
                this.displayTableInfo(response.data, tableInfoDiv);
            } else {
                const errorMessage = response?.error || 'Failed to load table information';
                console.error('[Database] Error:', errorMessage);
                tableInfoDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${errorMessage}
                    </div>
                `;
            }
        } catch (error) {
            console.error('[Database] Load table info failed:', error);
            tableInfoDiv.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> Failed to load table information
                    <div class="mt-2"><small>${error.message || 'Unknown error occurred'}</small></div>
                </div>
            `;
        }
    }

    displayTableInfo(tables, container) {
        // Validate input
        if (!tables || !Array.isArray(tables)) {
            console.error('[Database] Invalid table data:', tables);
            container.innerHTML = '<div class="alert alert-warning">No table data available</div>';
            return;
        }

        if (tables.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No tables found in database</div>';
            return;
        }

        // Debug logging
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('[Database] Displaying', tables.length, 'tables');
        }

        let html = `
            <table class="table table-sm table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Table Name</th>
                        <th class="text-center">Records</th>
                        <th class="text-center">Columns</th>
                        <th class="text-center">Size</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
        `;

        tables.forEach((table, index) => {
            // Validate and sanitize table data
            const tableName = this.sanitizeHTML(table.name || 'Unknown');
            const tableCount = table.count !== null && table.count !== undefined ? 
                              (typeof table.count === 'number' ? table.count : 'Error') : 0;
            const tableColumns = table.columns !== null && table.columns !== undefined ? 
                                (typeof table.columns === 'number' ? table.columns : 
                                 typeof table.columns === 'object' && Array.isArray(table.columns) ? table.columns.length : 0) : 0;
            const tableSize = table.size || 'N/A';
            const tableStatus = table.status || 'Unknown';
            const tableError = table.error || null;

            // Determine status badge
            let statusBadge = '';
            switch(tableStatus.toLowerCase()) {
                case 'ok':
                    statusBadge = '<span class="badge bg-success">OK</span>';
                    break;
                case 'missing':
                    statusBadge = '<span class="badge bg-warning">Missing</span>';
                    break;
                case 'error':
                    statusBadge = `<span class="badge bg-danger" title="${this.sanitizeHTML(tableError || 'Unknown error')}">Error</span>`;
                    break;
                default:
                    statusBadge = `<span class="badge bg-secondary">${tableStatus}</span>`;
            }

            // Count display
            const countDisplay = tableError || typeof tableCount !== 'number' ? 
                `<span class="badge bg-danger" title="${this.sanitizeHTML(tableError || 'Error retrieving count')}">Error</span>` : 
                `<span class="badge bg-info">${tableCount.toLocaleString()}</span>`;
            
            // Columns display
            const columnsDisplay = typeof tableColumns === 'number' ? 
                `<span class="badge bg-secondary">${tableColumns}</span>` : 
                `<span class="badge bg-warning">N/A</span>`;

            html += `
                <tr>
                    <td><strong>${tableName}</strong></td>
                    <td class="text-center">${countDisplay}</td>
                    <td class="text-center">${columnsDisplay}</td>
                    <td class="text-center"><span class="text-muted">${this.sanitizeHTML(tableSize)}</span></td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">
                        <button 
                            class="btn btn-sm btn-outline-primary" 
                            onclick="superadminDashboard.viewTableDetails('${tableName}')" 
                            ${tableError || tableStatus.toLowerCase() === 'error' || tableStatus.toLowerCase() === 'missing' ? 'disabled' : ''}
                            title="${tableError || tableStatus.toLowerCase() === 'error' ? 'Cannot view details due to error' : 'View table details'}">
                            <i class="fas fa-info-circle me-1"></i>View Details
                        </button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        
        // Add summary footer
        const totalTables = tables.length;
        const okTables = tables.filter(t => t.status && t.status.toLowerCase() === 'ok').length;
        const errorTables = tables.filter(t => t.status && (t.status.toLowerCase() === 'error' || t.status.toLowerCase() === 'missing')).length;
        
        html += `
            <div class="mt-3 p-3 bg-light rounded">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="h5 mb-0">${totalTables}</div>
                        <div class="text-muted small">Total Tables</div>
                    </div>
                    <div class="col-md-4">
                        <div class="h5 mb-0 text-success">${okTables}</div>
                        <div class="text-muted small">Healthy</div>
                    </div>
                    <div class="col-md-4">
                        <div class="h5 mb-0 ${errorTables > 0 ? 'text-danger' : 'text-muted'}">${errorTables}</div>
                        <div class="text-muted small">Issues</div>
                    </div>
                </div>
            </div>
        `;
        
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
            if (brandingForm.dataset.brandingJs === '1') return;
            if (brandingForm.dataset.brandingSaBound === '1') return;
            brandingForm.dataset.brandingSaBound = '1';
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
                const msg = response.error || 'Failed to update branding';
                this.showNotification(msg, 'error');
            }
        } catch (error) {
            const serverMsg = error && error.responseData && (error.responseData.error || (error.responseData.message)) ?
                error.responseData.error || error.responseData.message : null;
            const finalMsg = serverMsg ? `Failed to update branding: ${serverMsg}` : `Failed to update branding: ${error.message || 'Unknown error'}`;
            this.showNotification(finalMsg, 'error');
            if (error && error.responseData && error.responseData.errors) {
                const firstKey = Object.keys(error.responseData.errors)[0];
                const firstErr = error.responseData.errors[firstKey][0];
                this.showNotification(`Validation error: ${firstErr}`, 'error');
            }
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
                let errorData = null;
                try {
                    errorData = await response.json();
                } catch (_) { /* ignore non-JSON */ }
                const err = new Error(`HTTP ${response.status}: ${response.statusText}`);
                if (errorData) err.responseData = errorData;
                err.status = response.status;
                throw err;
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('Request failed:', error);
            throw error;
        }
    }

    showNotification(message, type = 'info') {
        // Sanitize message
        const safeMessage = this.sanitizeHTML(message);
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
        notification.innerHTML = `
            ${safeMessage}
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

    /**
     * Sanitize HTML to prevent XSS
     */
    sanitizeHTML(str) {
        if (str === null || str === undefined) {
            return '';
        }
        
        const temp = document.createElement('div');
        temp.textContent = String(str);
        return temp.innerHTML;
    }

    /**
     * Safely get nested object property
     */
    safeGet(obj, path, defaultValue = null) {
        try {
            return path.split('.').reduce((acc, part) => acc && acc[part], obj) ?? defaultValue;
        } catch (e) {
            return defaultValue;
        }
    }

    /**
     * Format number with fallback
     */
    formatNumber(value, fallback = 'N/A') {
        if (value === null || value === undefined || value === '') {
            return fallback;
        }
        
        const num = Number(value);
        if (isNaN(num)) {
            return fallback;
        }
        
        return num.toLocaleString();
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
            case 'purchase-orders':
                setTimeout(() => {
                    this.poSystemState.page = 1;
                    this.loadSystemPurchaseOrders({ noCache: false });
                }, 300);
                break;
            case 'security':
                setTimeout(() => {
                    this.refreshSecurityOverview(true);
                    this.refreshSecurityAlerts(true);
                }, 500);
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
