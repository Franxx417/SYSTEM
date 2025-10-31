/**
 * Settings Page JavaScript
 * Handles preferences and notifications form submissions with proper state management
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize forms
    initializePreferencesForm();
    initializeNotificationsForm();
    loadUserPreferences();
    loadNotificationSettings();
});

/**
 * Initialize Preferences Form
 */
function initializePreferencesForm() {
    const preferencesForm = document.getElementById('preferencesForm');
    
    if (!preferencesForm) return;
    
    preferencesForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        try {
            const formData = new FormData(this);
            const data = {
                language: formData.get('language') || document.querySelector('select[name="language"]').value,
                date_format: formData.get('date_format') || document.querySelector('select[name="date_format"]').value,
                time_format: formData.get('time_format') || document.querySelector('select[name="time_format"]').value,
                timezone: formData.get('timezone') || document.querySelector('select[name="timezone"]').value,
                auto_save: document.getElementById('autoSave').checked,
                compact_view: document.getElementById('compactView').checked
            };
            
            const response = await fetch('/settings/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Success', result.message, 'success');
            } else {
                throw new Error(result.message || 'Failed to save preferences');
            }
            
        } catch (error) {
            console.error('Error saving preferences:', error);
            showToast('Error', error.message || 'Failed to save preferences', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/**
 * Initialize Notifications Form
 */
function initializeNotificationsForm() {
    const notificationsForm = document.getElementById('notificationsForm');
    
    if (!notificationsForm) return;
    
    notificationsForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        
        try {
            const data = {
                notif_po_created: document.getElementById('notif_po_created').checked,
                notif_po_approved: document.getElementById('notif_po_approved').checked,
                notif_po_rejected: document.getElementById('notif_po_rejected').checked,
                notif_system_updates: document.getElementById('notif_system_updates').checked,
                notif_security: document.getElementById('notif_security').checked,
                email_daily_summary: document.getElementById('email_daily_summary').checked,
                email_weekly_report: document.getElementById('email_weekly_report').checked
            };
            
            const response = await fetch('/settings/notifications', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showToast('Success', result.message, 'success');
            } else {
                throw new Error(result.message || 'Failed to save notification settings');
            }
            
        } catch (error) {
            console.error('Error saving notifications:', error);
            showToast('Error', error.message || 'Failed to save notification settings', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

/**
 * Load User Preferences
 */
async function loadUserPreferences() {
    try {
        const response = await fetch('/settings/preferences', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.preferences) {
            const prefs = result.preferences;
            
            // Set form values
            const languageSelect = document.querySelector('select[name="language"]');
            const dateFormatSelect = document.querySelector('select[name="date_format"]');
            const timeFormatSelect = document.querySelector('select[name="time_format"]');
            const timezoneSelect = document.querySelector('select[name="timezone"]');
            
            if (languageSelect) {
                const option = Array.from(languageSelect.options).find(opt => 
                    opt.value === prefs.language || opt.textContent.includes(getLanguageName(prefs.language))
                );
                if (option) option.selected = true;
            }
            
            if (dateFormatSelect) {
                const option = Array.from(dateFormatSelect.options).find(opt => opt.value === prefs.date_format);
                if (option) option.selected = true;
            }
            
            if (timeFormatSelect) {
                const option = Array.from(timeFormatSelect.options).find(opt => 
                    opt.value === prefs.time_format || opt.textContent.includes(prefs.time_format + '-hour')
                );
                if (option) option.selected = true;
            }
            
            if (timezoneSelect) {
                const option = Array.from(timezoneSelect.options).find(opt => 
                    opt.value === prefs.timezone || opt.textContent.includes(prefs.timezone)
                );
                if (option) option.selected = true;
            }
            
            // Set checkbox values
            const autoSaveCheckbox = document.getElementById('autoSave');
            const compactViewCheckbox = document.getElementById('compactView');
            
            if (autoSaveCheckbox) autoSaveCheckbox.checked = prefs.auto_save;
            if (compactViewCheckbox) compactViewCheckbox.checked = prefs.compact_view;
        }
    } catch (error) {
        console.error('Error loading preferences:', error);
    }
}

/**
 * Load Notification Settings
 */
async function loadNotificationSettings() {
    try {
        const response = await fetch('/settings/notifications', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success && result.notifications) {
            const notifs = result.notifications;
            
            // Set checkbox values
            Object.keys(notifs).forEach(key => {
                const checkbox = document.getElementById(key);
                if (checkbox) checkbox.checked = notifs[key];
            });
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

/**
 * Show toast notification
 */
function showToast(title, message, type = 'info') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toastHTML = `
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = toastContainer.lastElementChild;
    const bsToast = new bootstrap.Toast(toastElement);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        bsToast.hide();
        setTimeout(() => toastElement.remove(), 500);
    }, 5000);
}

/**
 * Create toast container if it doesn't exist
 */
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '11';
    document.body.appendChild(container);
    return container;
}

/**
 * Get language display name
 */
function getLanguageName(code) {
    const languages = {
        'en': 'English',
        'fil': 'Filipino',
        'zh': '中文'
    };
    return languages[code] || code;
}

/**
 * Add name attributes to select elements if missing
 */
document.addEventListener('DOMContentLoaded', function() {
    const preferencesForm = document.getElementById('preferencesForm');
    if (preferencesForm) {
        // Ensure all select elements have name attributes
        const languageSelect = preferencesForm.querySelector('select:nth-of-type(1)');
        const dateFormatSelect = preferencesForm.querySelector('select:nth-of-type(2)');
        const timeFormatSelect = preferencesForm.querySelector('select:nth-of-type(3)');
        const timezoneSelect = preferencesForm.querySelector('select:nth-of-type(4)');
        
        if (languageSelect && !languageSelect.name) languageSelect.name = 'language';
        if (dateFormatSelect && !dateFormatSelect.name) dateFormatSelect.name = 'date_format';
        if (timeFormatSelect && !timeFormatSelect.name) timeFormatSelect.name = 'time_format';
        if (timezoneSelect && !timezoneSelect.name) timezoneSelect.name = 'timezone';
    }
});
