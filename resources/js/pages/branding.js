/**
 * Branding (Core)
 * Application name/description + logo upload (drag/drop + preview) + save.
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('branding-form');
    if (!form) return;

    if (form.dataset.brandingJsBound === '1') return;
    form.dataset.brandingJsBound = '1';

    // Form elements
    const logoInput = document.getElementById('logo');
    const logoDropZone = document.getElementById('logo-drop-zone');
    const logoPreview = document.getElementById('logo-preview');
    const logoPreviewContainer = document.getElementById('logo-preview-container');
    const logoUploadPrompt = document.getElementById('logo-upload-prompt');

    // App info inputs
    const appName = document.getElementById('app_name');
    const appDescription = document.getElementById('app_description');
    
    // State management
    const unsavedBadge = document.getElementById('unsaved-changes');
    let hasUnsavedChanges = false;
    let originalFormData = new FormData(form);

    function getFormSignature() {
        const fd = new FormData(form);
        const pairs = [];

        for (const [k, v] of fd.entries()) {
            if (v instanceof File) {
                // Empty file inputs can appear as a File with size 0 and empty name
                const fileSig = v && v.name ? `${v.name}|${v.size}|${v.type}` : '';
                pairs.push([k, fileSig]);
            } else {
                pairs.push([k, String(v)]);
            }
        }

        pairs.sort((a, b) => (a[0] + '=' + a[1]).localeCompare(b[0] + '=' + b[1]));
        return pairs.map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`).join('&');
    }

    let originalSignature = getFormSignature();

    function syncDirtyState() {
        const currentSig = getFormSignature();
        hasUnsavedChanges = currentSig !== originalSignature;
        if (unsavedBadge) {
            unsavedBadge.style.display = hasUnsavedChanges ? 'inline-block' : 'none';
        }
    }

    // Track changes
    function markAsChanged() {
        syncDirtyState();
    }

    // Character counter for description
    if (appDescription) {
        const descCount = document.getElementById('desc_count');
        const updateDescCount = () => {
            if (descCount) {
                descCount.textContent = appDescription.value.length;
            }
        };
        appDescription.addEventListener('input', updateDescCount);
        appDescription.addEventListener('input', markAsChanged);
        updateDescCount();
    }

    if (appName) {
        appName.addEventListener('input', markAsChanged);
    }

    // Logo Upload Handling
    if (logoInput && logoDropZone) {
        // Click to upload
        logoDropZone.addEventListener('click', function(e) {
            if (!e.target.closest('#remove-logo-btn')) {
                logoInput.click();
            }
        });

        // Keyboard accessibility
        logoDropZone.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                logoInput.click();
            }
        });

        // File input change
        logoInput.addEventListener('change', function(e) {
            handleLogoFile(e.target.files[0]);
        });

        // Drag and drop
        logoDropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('drag-over');
        });

        logoDropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
        });

        logoDropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Ensure the dropped file is actually submitted with the form
                try {
                    const dt = new DataTransfer();
                    dt.items.add(files[0]);
                    logoInput.files = dt.files;
                } catch (_) {
                    // If DataTransfer isn't supported, preview still works but submit may require click-upload
                }
                handleLogoFile(files[0]);
            }
        });

    }

    function handleLogoFile(file) {
        if (!file) return;

        // Ensure file is attached to input even when handleLogoFile is called directly
        if (logoInput && (!logoInput.files || logoInput.files.length === 0)) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                logoInput.files = dt.files;
            } catch (_) {
                // Ignore if browser doesn't allow programmatic file assignment
            }
        }

        // Validate file type
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
        if (!validTypes.includes(file.type)) {
            showError('Please upload a PNG, JPG, or SVG file.');
            return;
        }

        // Validate file size (2MB)
        const maxSize = 2 * 1024 * 1024;
        if (file.size > maxSize) {
            showError('File size must be less than 2MB.');
            return;
        }

        // Read and preview
        const reader = new FileReader();
        reader.onload = function(e) {
            if (logoPreview && logoPreviewContainer && logoUploadPrompt) {
                logoPreview.src = e.target.result;
                logoPreviewContainer.style.display = 'block';
                logoUploadPrompt.style.display = 'none';
                markAsChanged();
            }
        };
        reader.readAsDataURL(file);
    }

    function clearLogoPreview() {
        if (logoPreview && logoPreviewContainer && logoUploadPrompt) {
            logoPreview.src = '';
            logoPreviewContainer.style.display = 'none';
            logoUploadPrompt.style.display = 'block';
        }
    }

    function showError(message) {
        const errorDiv = document.getElementById('logo-error');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
    }


    // Reset Button
    const resetBtn = document.getElementById('reset-btn');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (hasUnsavedChanges) {
                if (confirm('Are you sure you want to reset all changes? This will discard any unsaved modifications.')) {
                    form.reset();
                    location.reload();
                }
            } else {
                form.reset();
            }
        });
    }

    // Form Submission via API
    const saveBtn = document.getElementById('save-btn');
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate inputs
        if (!validateForm()) {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Branding';
            }
            return;
        }

        // Show loading state
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        }

        try {
            // Create FormData for file upload support
            const formData = new FormData(form);
            
            // Make API request
            const response = await makeApiRequest('/api/superadmin/branding/update', 'POST', formData);
            
            if (response.success) {
                // Show success message
                showSuccessNotification('Branding updated successfully!');
                
                // Clear unsaved changes badge
                originalFormData = new FormData(form);
                originalSignature = getFormSignature();
                hasUnsavedChanges = false;
                if (unsavedBadge) {
                    unsavedBadge.style.display = 'none';
                }
                
                // Reload page after 1 second to reflect changes
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showErrorNotification(response.error || 'Failed to update branding');
            }
        } catch (error) {
            console.error('Branding update failed:', error);
            const serverMsg = error && error.responseData && (error.responseData.error || error.responseData.message) ?
                error.responseData.error || error.responseData.message : null;
            showErrorNotification('Failed to update branding: ' + (serverMsg || error.message || 'Unknown error'));
            if (error && error.responseData && error.responseData.errors) {
                const firstKey = Object.keys(error.responseData.errors)[0];
                const firstErr = error.responseData.errors[firstKey][0];
                showErrorNotification('Validation error: ' + firstErr);
            }
        } finally {
            // Reset button state
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Branding';
            }
        }
    });

    /**
     * Make API request with proper error handling
     */
    async function makeApiRequest(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data) {
            if (data instanceof FormData) {
                options.body = data;
                // Don't set Content-Type for FormData - browser will set it with boundary
            } else {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(data);
            }
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            const err = new Error(errorData.error || `HTTP ${response.status}: ${response.statusText}`);
            err.responseData = errorData;
            err.status = response.status;
            throw err;
        }
        
        return await response.json();
    }

    /**
     * Show success notification
     */
    function showSuccessNotification(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
        alertDiv.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    /**
     * Show error notification
     */
    function showErrorNotification(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
        alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 7000);
    }

    function validateForm() {
        let isValid = true;

        // Validate app name
        if (appName && !appName.value.trim()) {
            appName.classList.add('is-invalid');
            isValid = false;
        } else if (appName) {
            appName.classList.remove('is-invalid');
        }

        return isValid;
    }

    // Warn about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        // Re-check current dirty state to avoid prompting on false positives
        syncDirtyState();
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    // Ensure we don't show the unsaved prompt/badge unless the user truly changed something
    syncDirtyState();

    console.log('Branding tab initialized');
});
