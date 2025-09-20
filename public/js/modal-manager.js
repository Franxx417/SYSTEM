/**
 * Modal Manager - Global modal utilities for the procurement system
 * Provides common modal functionality across all views
 */

// Global modal utilities
window.ModalManager = {
    
    // Show a confirmation modal with custom content
    showConfirmation: function(title, message, confirmText, cancelText, onConfirm) {
        var modalId = 'globalConfirmModal';
        var existingModal = document.getElementById(modalId);
        
        if (existingModal) {
            existingModal.remove();
        }
        
        var modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${message}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${cancelText || 'Cancel'}</button>
                            <button type="button" class="btn btn-primary" id="confirmAction">${confirmText || 'Confirm'}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        var modal = new bootstrap.Modal(document.getElementById(modalId));
        
        document.getElementById('confirmAction').addEventListener('click', function() {
            if (onConfirm) onConfirm();
            modal.hide();
        });
        
        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
            document.getElementById(modalId).remove();
        });
        
        modal.show();
    },
    
    // Show a success message modal
    showSuccess: function(title, message) {
        this.showAlert('success', title, message);
    },
    
    // Show an error message modal
    showError: function(title, message) {
        this.showAlert('danger', title, message);
    },
    
    // Show an info message modal
    showInfo: function(title, message) {
        this.showAlert('info', title, message);
    },
    
    // Generic alert modal
    showAlert: function(type, title, message) {
        var modalId = 'globalAlertModal';
        var existingModal = document.getElementById(modalId);
        
        if (existingModal) {
            existingModal.remove();
        }
        
        var iconMap = {
            'success': '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle text-success" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.061L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>',
            'danger': '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-triangle text-danger" viewBox="0 0 16 16"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/><path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/></svg>',
            'info': '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-info-circle text-info" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>'
        };
        
        var modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    ${iconMap[type] || iconMap['info']}
                                </div>
                                <div>
                                    ${message}
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        var modal = new bootstrap.Modal(document.getElementById(modalId));
        
        document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
            document.getElementById(modalId).remove();
        });
        
        modal.show();
    },
    
    // Form validation helper
    validateForm: function(formId, rules) {
        var form = document.getElementById(formId);
        if (!form) return false;
        
        var isValid = true;
        var firstErrorField = null;
        
        // Clear previous validation states
        form.querySelectorAll('.is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(function(el) {
            el.remove();
        });
        
        // Validate each rule
        Object.keys(rules).forEach(function(fieldName) {
            var field = form.querySelector('[name="' + fieldName + '"]');
            if (!field) return;
            
            var rule = rules[fieldName];
            var value = field.value.trim();
            var fieldValid = true;
            var errorMessage = '';
            
            // Required validation
            if (rule.required && !value) {
                fieldValid = false;
                errorMessage = rule.requiredMessage || 'This field is required.';
            }
            
            // Min length validation
            if (fieldValid && rule.minLength && value.length < rule.minLength) {
                fieldValid = false;
                errorMessage = rule.minLengthMessage || `Minimum ${rule.minLength} characters required.`;
            }
            
            // Max length validation
            if (fieldValid && rule.maxLength && value.length > rule.maxLength) {
                fieldValid = false;
                errorMessage = rule.maxLengthMessage || `Maximum ${rule.maxLength} characters allowed.`;
            }
            
            // Email validation
            if (fieldValid && rule.email && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                fieldValid = false;
                errorMessage = rule.emailMessage || 'Please enter a valid email address.';
            }
            
            // Number validation
            if (fieldValid && rule.number && value && isNaN(value)) {
                fieldValid = false;
                errorMessage = rule.numberMessage || 'Please enter a valid number.';
            }
            
            // Min value validation
            if (fieldValid && rule.min !== undefined && parseFloat(value) < rule.min) {
                fieldValid = false;
                errorMessage = rule.minMessage || `Value must be at least ${rule.min}.`;
            }
            
            // Custom validation
            if (fieldValid && rule.custom && !rule.custom(value, form)) {
                fieldValid = false;
                errorMessage = rule.customMessage || 'Invalid value.';
            }
            
            if (!fieldValid) {
                isValid = false;
                field.classList.add('is-invalid');
                
                var feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = errorMessage;
                field.parentNode.appendChild(feedback);
                
                if (!firstErrorField) {
                    firstErrorField = field;
                }
            }
        });
        
        // Focus first error field
        if (firstErrorField) {
            firstErrorField.focus();
        }
        
        return isValid;
    },
    
    // Auto-dismiss alerts
    autoDismissAlerts: function(selector, delay) {
        selector = selector || '.alert';
        delay = delay || 5000;
        
        document.querySelectorAll(selector).forEach(function(alert) {
            setTimeout(function() {
                if (alert && alert.parentNode) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, delay);
        });
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    ModalManager.autoDismissAlerts();
    
    // Add global form validation
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (form.hasAttribute('data-validate')) {
            var rules = {};
            try {
                rules = JSON.parse(form.getAttribute('data-validate'));
            } catch (ex) {
                console.warn('Invalid validation rules:', ex);
                return;
            }
            
            if (!ModalManager.validateForm(form.id, rules)) {
                e.preventDefault();
                return false;
            }
        }
    });
});
