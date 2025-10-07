/**
 * Status Color Synchronization System
 * 
 * This module handles real-time color synchronization between:
 * - Quick Status Management (Dashboard Tab)
 * - Advanced Status Management (Admin Interface)
 * 
 * Features:
 * - Real-time color updates across both interfaces
 * - LocalStorage for cross-tab communication
 * - Event-based updates
 * - Automatic UI refresh
 */

class StatusColorSync {
    constructor() {
        this.syncKey = 'status_color_sync_event';
        this.lastUpdateKey = 'status_color_last_update';
        this.checkInterval = 1000; // Check every second
        this.listeners = [];
        
        this.init();
    }
    
    init() {
        // Listen for localStorage changes (cross-tab sync)
        window.addEventListener('storage', (e) => {
            if (e.key === this.syncKey && e.newValue) {
                const data = JSON.parse(e.newValue);
                this.handleSyncEvent(data);
            }
        });
        
        // Poll for updates (same-tab sync)
        setInterval(() => {
            this.checkForUpdates();
        }, this.checkInterval);
        
        console.log('Status Color Sync initialized');
    }
    
    /**
     * Notify all interfaces of a color change
     * @param {string} statusId - The status ID
     * @param {string} statusName - The status name
     * @param {string} color - The new color (hex)
     */
    notifyColorChange(statusId, statusName, color) {
        const event = {
            statusId: statusId,
            statusName: statusName,
            color: color.toUpperCase(),
            timestamp: Date.now(),
            source: window.location.pathname
        };
        
        // Store in localStorage for cross-tab sync
        localStorage.setItem(this.syncKey, JSON.stringify(event));
        localStorage.setItem(this.lastUpdateKey, event.timestamp.toString());
        
        // Trigger local handlers immediately
        this.triggerListeners(event);
        
        console.log('Color change notified:', event);
    }
    
    /**
     * Handle sync event from another tab or poll
     * @param {object} data - The sync event data
     */
    handleSyncEvent(data) {
        const lastUpdate = parseInt(localStorage.getItem(this.lastUpdateKey) || '0');
        
        // Only process if this is a new event
        if (data.timestamp > lastUpdate && data.source !== window.location.pathname) {
            console.log('Processing sync event:', data);
            this.triggerListeners(data);
            localStorage.setItem(this.lastUpdateKey, data.timestamp.toString());
        }
    }
    
    /**
     * Check for updates (polling method)
     */
    checkForUpdates() {
        const syncData = localStorage.getItem(this.syncKey);
        if (syncData) {
            const data = JSON.parse(syncData);
            this.handleSyncEvent(data);
        }
    }
    
    /**
     * Register a callback for color changes
     * @param {function} callback - Function to call when color changes
     */
    onColorChange(callback) {
        this.listeners.push(callback);
    }
    
    /**
     * Trigger all registered listeners
     * @param {object} data - The sync event data
     */
    triggerListeners(data) {
        this.listeners.forEach(callback => {
            try {
                callback(data);
            } catch (error) {
                console.error('Error in sync listener:', error);
            }
        });
    }
    
    /**
     * Update color in the database (unified endpoint)
     * @param {string} statusId - The status ID
     * @param {string} color - The new color (hex)
     * @returns {Promise}
     */
    async updateColorInDatabase(statusId, color) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }
        
        const response = await fetch(`/admin/status/${statusId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                _method: 'PUT',
                color: color,
                sync_only_color: true // Flag to only update color
            })
        });
        
        if (!response.ok) {
            throw new Error('Failed to update color in database');
        }
        
        return await response.json();
    }
    
    /**
     * Get current status colors from database
     * @returns {Promise<object>}
     */
    async getStatusColors() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const response = await fetch('/admin/status/config', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch status colors');
        }
        
        const data = await response.json();
        return data.config || {};
    }
    
    /**
     * Update all status indicators on the current page
     * @param {string} statusName - The status name
     * @param {string} color - The new color (hex)
     */
    updatePageIndicators(statusName, color) {
        console.log(`Updating indicators for "${statusName}" to ${color}`);
        
        // Update color pickers with data-status attribute
        document.querySelectorAll(`[data-status="${statusName}"]`).forEach(element => {
            if (element.type === 'color') {
                element.value = color;
                console.log(`Updated color picker for ${statusName}`);
            }
        });
        
        // Update status indicators by data-status-name
        document.querySelectorAll('.status-indicator[data-status-name]').forEach(indicator => {
            if (indicator.getAttribute('data-status-name') === statusName) {
                indicator.style.backgroundColor = color;
                indicator.setAttribute('data-status-color', color);
                console.log(`Updated status indicator for ${statusName}`);
            }
        });
        
        // Update badges with data-status-badge attribute
        document.querySelectorAll('[data-status-badge]').forEach(badge => {
            if (badge.getAttribute('data-status-badge') === statusName) {
                badge.style.backgroundColor = color;
                console.log(`Updated badge for ${statusName}`);
            }
        });
        
        // Update sortable items
        document.querySelectorAll(`.sortable-item[data-status-name="${statusName}"]`).forEach(item => {
            const indicator = item.querySelector('.status-indicator');
            if (indicator) {
                indicator.style.backgroundColor = color;
            }
            const badge = item.querySelector('.badge');
            if (badge) {
                badge.style.backgroundColor = color;
            }
        });
        
        // Update any generic status-colored elements
        document.querySelectorAll('[data-status-color]').forEach(element => {
            const elStatusName = element.getAttribute('data-status-name');
            if (elStatusName === statusName) {
                element.style.backgroundColor = color;
                element.setAttribute('data-status-color', color);
            }
        });
    }
    
    /**
     * Broadcast update to server (for server-side event broadcasting)
     * @param {string} statusId - The status ID
     * @param {string} statusName - The status name
     * @param {string} color - The new color (hex)
     */
    async broadcastUpdate(statusId, statusName, color) {
        try {
            await this.updateColorInDatabase(statusId, color);
            this.notifyColorChange(statusId, statusName, color);
            return { success: true };
        } catch (error) {
            console.error('Error broadcasting update:', error);
            return { success: false, error: error.message };
        }
    }
    
    /**
     * Clear sync data
     */
    clearSync() {
        localStorage.removeItem(this.syncKey);
        localStorage.removeItem(this.lastUpdateKey);
    }
    
    /**
     * Get sync statistics
     * @returns {object}
     */
    getSyncStats() {
        const syncData = localStorage.getItem(this.syncKey);
        const lastUpdate = localStorage.getItem(this.lastUpdateKey);
        
        return {
            hasData: !!syncData,
            lastUpdate: lastUpdate ? new Date(parseInt(lastUpdate)) : null,
            listeners: this.listeners.length,
            currentPage: window.location.pathname
        };
    }
}

// Create global instance
window.statusColorSync = new StatusColorSync();

// Export for ES6 modules
export default window.statusColorSync;
