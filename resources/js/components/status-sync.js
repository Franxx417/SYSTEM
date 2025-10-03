/**
 * Status Sync JavaScript
 * Handles real-time status synchronization for the application
 */

class StatusSync {
    static SYNC_FREQUENCY = 30000; // 30 seconds
    static MAX_RETRIES = 3;
    static CACHE_KEY = 'status_config';

    constructor() {
        this.isActive = false;
        this.syncInterval = null;
        this.lastSyncTime = null;
        this.retryCount = 0;
        
        this.init();
    }

    init() {
        // Start status sync if on dashboard or admin pages
        if (this.shouldStartSync()) {
            this.startSync();
        }

        // Listen for page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseSync();
            } else {
                this.resumeSync();
            }
        });

        // Listen for online/offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }

    shouldStartSync() {
        // Only start sync on superadmin pages
        const path = window.location.pathname;
        return path.includes('/superadmin');
    }

    startSync() {
        if (this.isActive) return;
        
        this.isActive = true;
        this.performSync(); // Initial sync
        
        this.syncInterval = setInterval(() => {
            this.performSync();
        }, StatusSync.SYNC_FREQUENCY);
        
        console.log('Status sync started');
    }

    pauseSync() {
        if (this.syncInterval) {
            clearInterval(this.syncInterval);
            this.syncInterval = null;
        }
        this.isActive = false;
        console.log('Status sync paused');
    }

    resumeSync() {
        if (!this.isActive && this.shouldStartSync()) {
            this.startSync();
        }
    }

    stopSync() {
        this.pauseSync();
        console.log('Status sync stopped');
    }

    async performSync() {
        try {
            // Check if we have a CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.warn('No CSRF token found for status sync');
                this.pauseSync();
                return;
            }

            // Make status request
            const response = await fetch('/superadmin?ajax=status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                this.handleSyncSuccess(data);
                this.retryCount = 0; // Reset retry count on success
            } else if (response.status === 403) {
                // Unauthorized - user is not superadmin, stop sync
                console.warn('Status sync stopped: Unauthorized access');
                this.pauseSync();
                return;
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            this.handleSyncError(error);
        }
    }

    handleSyncSuccess(data) {
        this.lastSyncTime = new Date();
        
        // Update system status indicators
        this.updateStatusIndicators(data);
        
        // Dispatch custom event for other components
        document.dispatchEvent(new CustomEvent('statusSync', {
            detail: { data, timestamp: this.lastSyncTime }
        }));
    }

    handleSyncError(error) {
        console.error('Status sync error:', error);
        this.retryCount++;
        
        if (this.retryCount >= StatusSync.MAX_RETRIES) {
            console.warn('Max retries reached, pausing status sync');
            this.pauseSync();
        } else {
            // Exponential backoff for retries
            const delay = Math.min(1000 * Math.pow(2, this.retryCount), 30000);
            setTimeout(() => {
                if (this.isActive) {
                    this.performSync();
                }
            }, delay);
        }

        // Dispatch error event
        document.dispatchEvent(new CustomEvent('statusSyncError', {
            detail: { error, retryCount: this.retryCount }
        }));
    }

    updateStatusIndicators(data) {
        // Update system status indicator
        const statusIndicator = document.querySelector('[data-status="system"]');
        if (statusIndicator && data.system_status) {
            statusIndicator.className = `status-indicator status-${data.system_status}`;
            statusIndicator.title = `System Status: ${data.system_status}`;
        }

        // Update PO status indicators if present
        if (data.po_statuses) {
            data.po_statuses.forEach(poStatus => {
                const poIndicators = document.querySelectorAll(`[data-po-status="${poStatus.status_name}"]`);
                poIndicators.forEach(indicator => {
                    if (poStatus.color) {
                        indicator.style.backgroundColor = poStatus.color;
                    }
                    if (poStatus.css_class) {
                        indicator.className = `status-indicator ${poStatus.css_class}`;
                    }
                });
            });
        }

        // Update last sync time
        const lastSyncElement = document.querySelector('[data-last-sync]');
        if (lastSyncElement) {
            lastSyncElement.textContent = this.formatTime(this.lastSyncTime);
            lastSyncElement.title = `Last sync: ${this.lastSyncTime.toLocaleString()}`;
        }

        // Update connection status
        const connectionStatus = document.querySelector('[data-connection-status]');
        if (connectionStatus) {
            connectionStatus.textContent = 'Connected';
            connectionStatus.className = 'connection-status connected';
        }
    }

    handleOnline() {
        console.log('Connection restored, resuming status sync');
        this.retryCount = 0;
        this.resumeSync();
        
        // Update UI
        const connectionStatus = document.querySelector('[data-connection-status]');
        if (connectionStatus) {
            connectionStatus.textContent = 'Connected';
            connectionStatus.className = 'connection-status connected';
        }
    }

    handleOffline() {
        console.log('Connection lost, pausing status sync');
        this.pauseSync();
        
        // Update UI
        const connectionStatus = document.querySelector('[data-connection-status]');
        if (connectionStatus) {
            connectionStatus.textContent = 'Offline';
            connectionStatus.className = 'connection-status offline';
        }
    }

    formatTime(date) {
        if (!date) return 'Never';
        
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) { // Less than 1 minute
            return 'Just now';
        } else if (diff < 3600000) { // Less than 1 hour
            const minutes = Math.floor(diff / 60000);
            return `${minutes}m ago`;
        } else {
            return date.toLocaleTimeString();
        }
    }

    // Public API methods
    getLastSyncTime() {
        return this.lastSyncTime;
    }

    isRunning() {
        return this.isActive;
    }

    forceSync() {
        if (this.isActive) {
            this.performSync();
        }
    }
}

// Initialize status sync when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    try {
        window.statusSync = new StatusSync();
        console.log('StatusSync initialized successfully');
    } catch (error) {
        console.error('Failed to initialize StatusSync:', error);
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = StatusSync;
}
