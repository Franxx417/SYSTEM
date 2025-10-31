/**
 * Branding & UI Tab - Real-time Preview and Interactions
 * Handles logo upload, color changes, typography, and live preview updates
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('branding-form');
    if (!form) return;

    // Form elements
    const logoInput = document.getElementById('logo');
    const logoDropZone = document.getElementById('logo-drop-zone');
    const logoPreview = document.getElementById('logo-preview');
    const logoPreviewContainer = document.getElementById('logo-preview-container');
    const logoUploadPrompt = document.getElementById('logo-upload-prompt');
    const removeLogo = document.getElementById('remove-logo-btn');
    
    // App info inputs
    const appName = document.getElementById('app_name');
    const appTagline = document.getElementById('app_tagline');
    const appDescription = document.getElementById('app_description');
    
    // Logo positioning
    const logoPosition = document.getElementById('logo_position');
    const logoSize = document.getElementById('logo_size');
    const logoSizeValue = document.getElementById('logo_size_value');
    
    // Color inputs
    const primaryColor = document.getElementById('primary_color');
    const primaryColorHex = document.getElementById('primary_color_hex');
    const secondaryColor = document.getElementById('secondary_color');
    const secondaryColorHex = document.getElementById('secondary_color_hex');
    const accentColor = document.getElementById('accent_color');
    const accentColorHex = document.getElementById('accent_color_hex');
    
    // Typography
    const fontFamily = document.getElementById('font_family');
    const fontSize = document.getElementById('font_size');
    const fontSizeValue = document.getElementById('font_size_value');
    
    // Preview elements
    const previewAppName = document.getElementById('preview-app-name');
    const previewAppTagline = document.getElementById('preview-app-tagline');
    const previewLogo = document.getElementById('preview-logo');
    const previewLogoContainer = document.getElementById('preview-logo-container');
    const previewText = document.getElementById('preview-text');
    const previewBtnPrimary = document.getElementById('preview-btn-primary');
    const previewBtnSecondary = document.getElementById('preview-btn-secondary');
    const previewBtnAccent = document.getElementById('preview-btn-accent');
    const previewLink = document.getElementById('preview-link');
    const previewAlert = document.getElementById('preview-alert');
    
    // State management
    const unsavedBadge = document.getElementById('unsaved-changes');
    let hasUnsavedChanges = false;
    let originalFormData = new FormData(form);

    // Track changes
    function markAsChanged() {
        hasUnsavedChanges = true;
        if (unsavedBadge) {
            unsavedBadge.style.display = 'inline-block';
        }
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
        updateDescCount();
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
                handleLogoFile(files[0]);
            }
        });

        // Remove logo button
        if (removeLogo) {
            removeLogo.addEventListener('click', function(e) {
                e.stopPropagation();
                clearLogoPreview();
                logoInput.value = '';
                markAsChanged();
            });
        }
    }

    function handleLogoFile(file) {
        if (!file) return;

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
                
                // Update live preview
                if (previewLogo) {
                    previewLogo.src = e.target.result;
                    previewLogo.style.display = 'block';
                }
                
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

    // Real-time Preview Updates
    
    // App Name
    if (appName && previewAppName) {
        appName.addEventListener('input', function() {
            previewAppName.textContent = this.value || 'Procurement System';
            markAsChanged();
        });
    }

    // App Tagline
    if (appTagline && previewAppTagline) {
        appTagline.addEventListener('input', function() {
            previewAppTagline.textContent = this.value;
            previewAppTagline.style.display = this.value ? 'block' : 'none';
            markAsChanged();
        });
    }

    // Logo Position
    if (logoPosition && previewLogoContainer) {
        logoPosition.addEventListener('change', function() {
            previewLogoContainer.style.justifyContent = this.value === 'center' ? 'center' : 
                                                         this.value === 'right' ? 'flex-end' : 'flex-start';
            markAsChanged();
        });
    }

    // Logo Size
    if (logoSize && logoSizeValue && previewLogo) {
        logoSize.addEventListener('input', function() {
            const size = this.value + 'px';
            logoSizeValue.textContent = size;
            previewLogo.style.height = size;
            markAsChanged();
        });
    }

    // Color Pickers - Primary
    if (primaryColor && primaryColorHex) {
        primaryColor.addEventListener('input', function() {
            primaryColorHex.value = this.value;
            updateColorPreview('primary', this.value);
            markAsChanged();
        });
        primaryColorHex.addEventListener('input', function() {
            if (isValidHex(this.value)) {
                primaryColor.value = this.value;
                updateColorPreview('primary', this.value);
                markAsChanged();
            }
        });
    }

    // Color Pickers - Secondary
    if (secondaryColor && secondaryColorHex) {
        secondaryColor.addEventListener('input', function() {
            secondaryColorHex.value = this.value;
            updateColorPreview('secondary', this.value);
            markAsChanged();
        });
        secondaryColorHex.addEventListener('input', function() {
            if (isValidHex(this.value)) {
                secondaryColor.value = this.value;
                updateColorPreview('secondary', this.value);
                markAsChanged();
            }
        });
    }

    // Color Pickers - Accent
    if (accentColor && accentColorHex) {
        accentColor.addEventListener('input', function() {
            accentColorHex.value = this.value;
            updateColorPreview('accent', this.value);
            markAsChanged();
        });
        accentColorHex.addEventListener('input', function() {
            if (isValidHex(this.value)) {
                accentColor.value = this.value;
                updateColorPreview('accent', this.value);
                markAsChanged();
            }
        });
    }

    function isValidHex(hex) {
        return /^#[0-9A-F]{6}$/i.test(hex);
    }

    function updateColorPreview(type, color) {
        if (type === 'primary') {
            if (previewBtnPrimary) {
                previewBtnPrimary.style.backgroundColor = color;
                previewBtnPrimary.style.borderColor = color;
                previewBtnPrimary.style.color = getContrastColor(color);
            }
            if (previewLink) {
                previewLink.style.color = color;
            }
        } else if (type === 'secondary') {
            if (previewBtnSecondary) {
                previewBtnSecondary.style.backgroundColor = color;
                previewBtnSecondary.style.borderColor = color;
                previewBtnSecondary.style.color = getContrastColor(color);
            }
        } else if (type === 'accent') {
            if (previewBtnAccent) {
                previewBtnAccent.style.backgroundColor = color;
                previewBtnAccent.style.borderColor = color;
                previewBtnAccent.style.color = getContrastColor(color);
            }
            if (previewAlert) {
                previewAlert.style.backgroundColor = color + '20'; // 20% opacity
                previewAlert.style.borderColor = color;
                previewAlert.style.color = color;
            }
        }
    }

    // Calculate contrast color for text
    function getContrastColor(hexColor) {
        const r = parseInt(hexColor.substr(1, 2), 16);
        const g = parseInt(hexColor.substr(3, 2), 16);
        const b = parseInt(hexColor.substr(5, 2), 16);
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return (yiq >= 128) ? '#000000' : '#ffffff';
    }

    // Font Family
    if (fontFamily && previewText) {
        fontFamily.addEventListener('change', function() {
            const selectedFont = this.value;
            previewText.style.fontFamily = selectedFont === 'system-ui' ? 
                'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif' : 
                `"${selectedFont}", sans-serif`;
            
            // Load Google Font if needed
            if (selectedFont !== 'system-ui' && !['Arial', 'Helvetica', 'Georgia', 'Times New Roman'].includes(selectedFont)) {
                loadGoogleFont(selectedFont);
            }
            markAsChanged();
        });
    }

    // Font Size
    if (fontSize && fontSizeValue && previewText) {
        fontSize.addEventListener('input', function() {
            const size = this.value + 'px';
            fontSizeValue.textContent = size;
            previewText.style.fontSize = size;
            markAsChanged();
        });
    }

    function loadGoogleFont(fontName) {
        const linkId = 'google-font-' + fontName.replace(/\s+/g, '-');
        if (!document.getElementById(linkId)) {
            const link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            link.href = `https://fonts.googleapis.com/css2?family=${fontName.replace(/\s+/g, '+')}:wght@400;500;600;700&display=swap`;
            document.head.appendChild(link);
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

    // Form Submission
    const saveBtn = document.getElementById('save-btn');
    form.addEventListener('submit', function(e) {
        // Show loading state
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
        }

        // Validate inputs
        if (!validateForm()) {
            e.preventDefault();
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Branding';
            }
        }
    });

    function validateForm() {
        let isValid = true;

        // Validate app name
        if (appName && !appName.value.trim()) {
            appName.classList.add('is-invalid');
            isValid = false;
        } else if (appName) {
            appName.classList.remove('is-invalid');
        }

        // Validate color hex inputs
        [primaryColorHex, secondaryColorHex, accentColorHex].forEach(input => {
            if (input && input.value && !isValidHex(input.value)) {
                input.classList.add('is-invalid');
                isValid = false;
            } else if (input) {
                input.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    // Warn about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    // Initialize preview with current values
    function initializePreview() {
        if (primaryColor && previewBtnPrimary) {
            updateColorPreview('primary', primaryColor.value);
        }
        if (secondaryColor && previewBtnSecondary) {
            updateColorPreview('secondary', secondaryColor.value);
        }
        if (accentColor && previewBtnAccent) {
            updateColorPreview('accent', accentColor.value);
        }
        if (fontFamily && previewText) {
            const selectedFont = fontFamily.value;
            previewText.style.fontFamily = selectedFont === 'system-ui' ? 
                'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif' : 
                `"${selectedFont}", sans-serif`;
        }
        if (fontSize && previewText) {
            previewText.style.fontSize = fontSize.value + 'px';
        }
        if (logoSize && previewLogo) {
            previewLogo.style.height = logoSize.value + 'px';
        }
        if (logoPosition && previewLogoContainer) {
            previewLogoContainer.style.justifyContent = logoPosition.value === 'center' ? 'center' : 
                                                         logoPosition.value === 'right' ? 'flex-end' : 'flex-start';
        }
    }

    initializePreview();

    console.log('Branding tab initialized with real-time preview');
});
