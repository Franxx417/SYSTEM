{{-- Enhanced Branding & UI Tab --}}
<div class="row g-3">
    {{-- Branding Settings --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-palette me-2"></i>Branding & UI Customization
                </h6>
            </div>
            <div class="card-body">
                {{-- Alerts --}}
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="fw-semibold mb-1"><i class="fas fa-times-circle me-1"></i>Please fix the following:</div>
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="branding-form" data-branding-js="1" action="{{ route('superadmin.branding') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Application Info Section --}}
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>Application Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="app_name" class="form-label">
                                    Application Name <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="app_name" 
                                    name="app_name" 
                                    value="{{ old('app_name', $settings['app.name'] ?? 'Procurement System') }}" 
                                    maxlength="100" 
                                    required
                                    aria-required="true"
                                    aria-describedby="app_name_help"
                                />
                                <div id="app_name_help" class="form-text">This appears in the sidebar and page titles</div>
                                <div class="invalid-feedback">Please provide an application name.</div>
                            </div>
                        </div>
                        <input type="hidden" name="app_tagline" value="{{ old('app_tagline', $settings['app.tagline'] ?? '') }}" />
                        <div class="mb-3">
                            <label for="app_description" class="form-label">Application Description</label>
                            <textarea 
                                class="form-control" 
                                id="app_description" 
                                name="app_description" 
                                rows="3" 
                                maxlength="255"
                                aria-describedby="app_description_help"
                            >{{ old('app_description', $settings['app.description'] ?? '') }}</textarea>
                            <div id="app_description_help" class="form-text">
                                Brief description of your application (<span id="desc_count">0</span>/255 characters)
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Logo Section --}}
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-image me-2"></i>Logo Configuration
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                @if(!empty($settings['branding.logo_path']))
                                    <div class="text-center" id="current-logo-section">
                                        <div class="mb-2"><strong>Current Logo:</strong></div>
                                        <img 
                                            src="{{ $settings['branding.logo_path'] }}" 
                                            alt="Current Logo" 
                                            class="img-thumbnail"
                                            style="max-height: 100px;"
                                        />
                                    </div>
                                @else
                                    <div class="text-muted">No logo uploaded yet.</div>
                                @endif

                                @if(session('auth_user') && session('auth_user.role') === 'superadmin')
                                    <div class="mt-3">
                                        <label class="form-label">Logo Upload</label>
                                        <div 
                                            class="logo-upload-area" 
                                            id="logo-drop-zone"
                                            role="button"
                                            tabindex="0"
                                            aria-label="Upload logo file"
                                        >
                                            <div id="logo-upload-prompt">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                                <p class="mb-1">Click to upload or drag and drop</p>
                                                <small class="text-muted">PNG, JPG, SVG (Max: 2MB, Recommended: 200x50px)</small>
                                            </div>
                                            <div id="logo-preview-container" style="display: none;">
                                                <img id="logo-preview" src="" alt="Logo preview" class="img-fluid mb-2" style="max-height: 150px;">
                                            </div>
                                        </div>
                                        <input 
                                            type="file" 
                                            class="d-none" 
                                            id="logo" 
                                            name="logo" 
                                            accept=".png,.jpg,.jpeg,.svg"
                                            aria-label="Logo file input"
                                        />
                                        <div class="invalid-feedback" id="logo-error"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2 justify-content-end border-top pt-3">
                        <button 
                            type="button" 
                            class="btn btn-outline-secondary" 
                            id="reset-btn"
                            aria-label="Reset form"
                        >
                            <i class="fas fa-undo me-1"></i>Reset Changes
                        </button>
                        <button 
                            type="submit" 
                            class="btn btn-primary" 
                            id="save-btn"
                            aria-label="Save branding settings"
                        >
                            <i class="fas fa-save me-1"></i>Save Branding
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('branding-form');
            if (!form) return;
            if (form.dataset.brandingInlineBound === '1') return;
            form.dataset.brandingInlineBound = '1';

            const logoInput = document.getElementById('logo');
            const logoDropZone = document.getElementById('logo-drop-zone');
            const logoPreview = document.getElementById('logo-preview');
            const logoPreviewContainer = document.getElementById('logo-preview-container');
            const logoUploadPrompt = document.getElementById('logo-upload-prompt');
            const appName = document.getElementById('app_name');
            const appDescription = document.getElementById('app_description');

            const errorDiv = document.getElementById('logo-error');
            function showError(message) {
                if (!errorDiv) return;
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
                setTimeout(() => { errorDiv.style.display = 'none'; }, 6000);
            }

            function showToast(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 520px;';
                alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                document.body.appendChild(alertDiv);
                setTimeout(() => { if (alertDiv.parentNode) alertDiv.remove(); }, 6000);
            }

            function handleLogoFile(file) {
                if (!file) return;

                const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/webp'];
                if (file.type && !validTypes.includes(file.type)) {
                    showError('Please upload a PNG, JPG, SVG, or WEBP file.');
                    return;
                }
                const maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    showError('File size must be less than 2MB.');
                    return;
                }

                if (logoInput) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        logoInput.files = dt.files;
                    } catch (_) {}
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    if (logoPreview && logoPreviewContainer && logoUploadPrompt) {
                        logoPreview.src = e.target.result;
                        logoPreviewContainer.style.display = 'block';
                        logoUploadPrompt.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }

            if (logoInput && logoDropZone) {
                logoDropZone.addEventListener('click', function (e) {
                    logoInput.click();
                });

                logoDropZone.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        logoInput.click();
                    }
                });

                logoInput.addEventListener('change', function (e) {
                    handleLogoFile(e.target.files[0]);
                });

                logoDropZone.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.add('drag-over');
                });

                logoDropZone.addEventListener('dragleave', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                });

                logoDropZone.addEventListener('drop', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.classList.remove('drag-over');
                    const files = e.dataTransfer.files;
                    if (files && files.length > 0) {
                        handleLogoFile(files[0]);
                    }
                });
            }

            const resetBtn = document.getElementById('reset-btn');
            if (resetBtn) {
                resetBtn.addEventListener('click', function () {
                    form.reset();
                    location.reload();
                });
            }

            const saveBtn = document.getElementById('save-btn');
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (appName && !appName.value.trim()) {
                    appName.classList.add('is-invalid');
                    return;
                }

                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                }

                try {
                    const formData = new FormData(form);
                    const resp = await fetch('/api/superadmin/branding/update', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await resp.json().catch(() => null);
                    if (!resp.ok) {
                        const msg = data && (data.error || data.message) ? (data.error || data.message) : `HTTP ${resp.status}`;
                        showToast('danger', `Failed to update branding: ${msg}`);
                        return;
                    }

                    if (!data || !data.success) {
                        showToast('danger', (data && data.error) ? data.error : 'Failed to update branding');
                        return;
                    }

                    showToast('success', data.message || 'Branding updated successfully');
                    setTimeout(() => location.reload(), 600);
                } catch (err) {
                    showToast('danger', 'Failed to update branding: ' + (err && err.message ? err.message : 'Unknown error'));
                } finally {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Branding';
                    }
                }
            });
        });
    </script>
@endpush

@push('styles')
    @vite(['resources/css/pages/branding.css'])
@endpush
