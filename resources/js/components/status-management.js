/**
 * Status Management JavaScript
 * Shared functions for status management across the application
 */

class StatusManager {
    static ENDPOINTS = {
        UPDATE: '/admin/status/update',
        REMOVE: '/admin/status/remove',
        REORDER: '/admin/status/reorder',
        CONFIG: '/admin/status/config',
        RESET: '/admin/status/reset'
    };

    /**
     * Show notification to user
     */
    static showNotification(message, type = 'info', duration = 5000) {
        const alertClass = {
            success: 'alert-success',
            error: 'alert-danger',
            warning: 'alert-warning',
            info: 'alert-info'
        }[type] || 'alert-info';

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
        }, duration);
    }

    /**
     * Get CSRF token from meta tag
     */
    static getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    /**
     * Make API request with proper headers
     */
    static async apiRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: { ...defaultOptions.headers, ...options.headers }
        };

        try {
            const response = await fetch(url, mergedOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    }

    /**
     * Update status configuration
     */
    static async updateStatus(statusName, config) {
        try {
            const data = await this.apiRequest(this.ENDPOINTS.UPDATE, {
                method: 'POST',
                body: JSON.stringify({
                    status_name: statusName,
                    ...config
                })
            });

            if (data.success) {
                this.showNotification(data.message, 'success');
                return true;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            this.showNotification(error.message || 'Failed to update status', 'error');
            return false;
        }
    }

    /**
     * Remove status
     */
    static async removeStatus(statusName) {
        if (!confirm(`Are you sure you want to remove the "${statusName}" status?`)) {
            return false;
        }

        try {
            const data = await this.apiRequest(this.ENDPOINTS.REMOVE, {
                method: 'POST',
                body: JSON.stringify({ status_name: statusName })
            });

            if (data.success) {
                this.showNotification(data.message, 'success');
                return true;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            this.showNotification(error.message || 'Failed to remove status', 'error');
            return false;
        }
    }

    /**
     * Reorder statuses
     */
    static async reorderStatuses(statusOrder) {
        try {
            const data = await this.apiRequest(this.ENDPOINTS.REORDER, {
                method: 'POST',
                body: JSON.stringify({ status_order: statusOrder })
            });

            if (data.success) {
                this.showNotification(data.message, 'success');
                return true;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            this.showNotification(error.message || 'Failed to reorder statuses', 'error');
            return false;
        }
    }

    /**
     * Get current configuration
     */
    static async getConfig() {
        try {
            return await this.apiRequest(this.ENDPOINTS.CONFIG);
        } catch (error) {
            this.showNotification('Failed to load configuration', 'error');
            throw error;
        }
    }

    /**
     * Reset to default configuration
     */
    static async resetToDefault() {
        if (!confirm('Are you sure you want to reset all status configurations to default? This cannot be undone.')) {
            return false;
        }

        try {
            const data = await this.apiRequest(this.ENDPOINTS.RESET, {
                method: 'POST'
            });

            if (data.success) {
                this.showNotification(data.message, 'success');
                return true;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            this.showNotification(error.message || 'Failed to reset configuration', 'error');
            return false;
        }
    }

    /**
     * Generate CSS class name from status name
     */
    static generateCssClass(statusName) {
        return 'status-' + statusName.toLowerCase().replace(/[^a-z0-9]/g, '-');
    }

    /**
     * Update status indicator color in DOM
     */
    static updateStatusIndicator(statusName, color) {
        const indicators = document.querySelectorAll(`[data-status="${statusName}"] .status-indicator`);
        indicators.forEach(indicator => {
            indicator.style.backgroundColor = color;
        });
    }

    /**
     * Export configuration as JSON file
     */
    static async exportConfig() {
        try {
            const config = await this.getConfig();
            const dataStr = JSON.stringify(config, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            const url = URL.createObjectURL(dataBlob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = `status_configuration_${new Date().toISOString().split('T')[0]}.json`;
            link.click();
            
            URL.revokeObjectURL(url);
            this.showNotification('Configuration exported successfully', 'success');
        } catch (error) {
            this.showNotification('Failed to export configuration', 'error');
        }
    }

    /**
     * Initialize sortable functionality
     */
    static initializeSortable(containerId, onUpdate) {
        const container = document.getElementById(containerId);
        if (!container || typeof Sortable === 'undefined') {
            return null;
        }

        return Sortable.create(container, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: onUpdate
        });
    }
}

// Make StatusManager available globally
window.StatusManager = StatusManager;
