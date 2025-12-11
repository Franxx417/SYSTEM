
<div class="row g-3">
    
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-palette me-2"></i>Branding & UI Customization
                </h6>
                <span class="badge bg-primary" id="unsaved-changes" style="display: none;">
                    <i class="fas fa-exclamation-circle me-1"></i>Unsaved Changes
                </span>
            </div>
            <div class="card-body">
                
                <?php if(session('status')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo e(session('status')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if(session('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo e(session('warning')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div class="fw-semibold mb-1"><i class="fas fa-times-circle me-1"></i>Please fix the following:</div>
                        <ul class="mb-0">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($e); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form id="branding-form" action="<?php echo e(route('superadmin.branding')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    
                    
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
                                    value="<?php echo e(old('app_name', $settings['app.name'] ?? 'Procurement System')); ?>" 
                                    maxlength="100" 
                                    required
                                    aria-required="true"
                                    aria-describedby="app_name_help"
                                />
                                <div id="app_name_help" class="form-text">This appears in the sidebar and page titles</div>
                                <div class="invalid-feedback">Please provide an application name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="app_tagline" class="form-label">Tagline</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="app_tagline" 
                                    name="app_tagline" 
                                    value="<?php echo e(old('app_tagline', $settings['app.tagline'] ?? '')); ?>" 
                                    maxlength="150"
                                    aria-describedby="app_tagline_help"
                                />
                                <div id="app_tagline_help" class="form-text">Short tagline or slogan</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="app_description" class="form-label">Application Description</label>
                            <textarea 
                                class="form-control" 
                                id="app_description" 
                                name="app_description" 
                                rows="3" 
                                maxlength="255"
                                aria-describedby="app_description_help"
                            ><?php echo e(old('app_description', $settings['app.description'] ?? '')); ?></textarea>
                            <div id="app_description_help" class="form-text">
                                Brief description of your application (<span id="desc_count">0</span>/255 characters)
                            </div>
                        </div>
                    </div>

                    <hr>

                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-image me-2"></i>Logo Configuration
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
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
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="remove-logo-btn">
                                                <i class="fas fa-trash me-1"></i>Remove
                                            </button>
                                        </div>
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
                                
                                <?php if(!empty($settings['branding.logo_path'])): ?>
                                    <div class="mt-3 text-center" id="current-logo-section">
                                        <div class="mb-2"><strong>Current Logo:</strong></div>
                                        <img 
                                            src="<?php echo e($settings['branding.logo_path']); ?>" 
                                            alt="Current Logo" 
                                            class="img-thumbnail"
                                            style="max-height: 100px;"
                                        />
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Logo Positioning</label>
                                <div class="mb-3">
                                    <label for="logo_position" class="form-label small">Position</label>
                                    <select class="form-select" id="logo_position" name="logo_position">
                                        <option value="left" <?php echo e(($settings['branding.logo_position'] ?? 'left') === 'left' ? 'selected' : ''); ?>>Left</option>
                                        <option value="center" <?php echo e(($settings['branding.logo_position'] ?? 'left') === 'center' ? 'selected' : ''); ?>>Center</option>
                                        <option value="right" <?php echo e(($settings['branding.logo_position'] ?? 'left') === 'right' ? 'selected' : ''); ?>>Right</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="logo_size" class="form-label small">Size (Height)</label>
                                    <div class="input-group">
                                        <input 
                                            type="range" 
                                            class="form-range" 
                                            id="logo_size" 
                                            name="logo_size" 
                                            min="30" 
                                            max="100" 
                                            value="<?php echo e($settings['branding.logo_size'] ?? 50); ?>"
                                            aria-label="Logo size"
                                        />
                                        <span class="input-group-text" id="logo_size_value"><?php echo e($settings['branding.logo_size'] ?? 50); ?>px</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-fill-drip me-2"></i>Color Scheme
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="primary_color" class="form-label">Primary Color</label>
                                <div class="input-group">
                                    <input 
                                        type="color" 
                                        class="form-control form-control-color" 
                                        id="primary_color" 
                                        name="primary_color" 
                                        value="<?php echo e(old('primary_color', $settings['branding.primary_color'] ?? '#0d6efd')); ?>"
                                        aria-label="Primary color picker"
                                    />
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="primary_color_hex" 
                                        value="<?php echo e(old('primary_color', $settings['branding.primary_color'] ?? '#0d6efd')); ?>"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        maxlength="7"
                                        aria-label="Primary color hex value"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="secondary_color" class="form-label">Secondary Color</label>
                                <div class="input-group">
                                    <input 
                                        type="color" 
                                        class="form-control form-control-color" 
                                        id="secondary_color" 
                                        name="secondary_color" 
                                        value="<?php echo e(old('secondary_color', $settings['branding.secondary_color'] ?? '#6c757d')); ?>"
                                        aria-label="Secondary color picker"
                                    />
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="secondary_color_hex" 
                                        value="<?php echo e(old('secondary_color', $settings['branding.secondary_color'] ?? '#6c757d')); ?>"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        maxlength="7"
                                        aria-label="Secondary color hex value"
                                    />
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="accent_color" class="form-label">Accent Color</label>
                                <div class="input-group">
                                    <input 
                                        type="color" 
                                        class="form-control form-control-color" 
                                        id="accent_color" 
                                        name="accent_color" 
                                        value="<?php echo e(old('accent_color', $settings['branding.accent_color'] ?? '#198754')); ?>"
                                        aria-label="Accent color picker"
                                    />
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="accent_color_hex" 
                                        value="<?php echo e(old('accent_color', $settings['branding.accent_color'] ?? '#198754')); ?>"
                                        pattern="^#[0-9A-Fa-f]{6}$"
                                        maxlength="7"
                                        aria-label="Accent color hex value"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <small>Colors will be applied to buttons, links, and UI elements throughout the application.</small>
                        </div>
                    </div>

                    <hr>

                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-font me-2"></i>Typography
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="font_family" class="form-label">Font Family</label>
                                <select class="form-select" id="font_family" name="font_family" aria-label="Font family selection">
                                    <?php
                                        $currentFont = $settings['branding.font_family'] ?? 'system-ui';
                                        $fonts = [
                                            'system-ui' => 'System UI (Default)',
                                            'Inter' => 'Inter',
                                            'Roboto' => 'Roboto',
                                            'Open Sans' => 'Open Sans',
                                            'Lato' => 'Lato',
                                            'Montserrat' => 'Montserrat',
                                            'Poppins' => 'Poppins',
                                            'Arial' => 'Arial',
                                            'Helvetica' => 'Helvetica',
                                            'Georgia' => 'Georgia',
                                            'Times New Roman' => 'Times New Roman'
                                        ];
                                    ?>
                                    <?php $__currentLoopData = $fonts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>" <?php echo e($currentFont === $value ? 'selected' : ''); ?>>
                                            <?php echo e($label); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="font_size" class="form-label">Base Font Size</label>
                                <div class="input-group">
                                    <input 
                                        type="range" 
                                        class="form-range" 
                                        id="font_size" 
                                        name="font_size" 
                                        min="12" 
                                        max="18" 
                                        step="0.5"
                                        value="<?php echo e($settings['branding.font_size'] ?? 14); ?>"
                                        aria-label="Font size"
                                    />
                                    <span class="input-group-text" id="font_size_value"><?php echo e($settings['branding.font_size'] ?? 14); ?>px</span>
                                </div>
                                <small class="text-muted">Recommended: 14-16px for optimal readability</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-paint-brush me-2"></i>Theme & Layout
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="theme_mode" class="form-label">Theme Mode</label>
                                <select class="form-select" id="theme_mode" name="theme_mode" aria-label="Theme mode selection">
                                    <option value="light" <?php echo e(($settings['branding.theme_mode'] ?? 'light') === 'light' ? 'selected' : ''); ?>>Light</option>
                                    <option value="dark" <?php echo e(($settings['branding.theme_mode'] ?? 'light') === 'dark' ? 'selected' : ''); ?>>Dark</option>
                                    <option value="auto" <?php echo e(($settings['branding.theme_mode'] ?? 'light') === 'auto' ? 'selected' : ''); ?>>Auto (System)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sidebar_position" class="form-label">Sidebar Position</label>
                                <select class="form-select" id="sidebar_position" name="sidebar_position" aria-label="Sidebar position">
                                    <option value="left" <?php echo e(($settings['branding.sidebar_position'] ?? 'left') === 'left' ? 'selected' : ''); ?>>Left</option>
                                    <option value="right" <?php echo e(($settings['branding.sidebar_position'] ?? 'left') === 'right' ? 'selected' : ''); ?>>Right</option>
                                    <option value="top" <?php echo e(($settings['branding.sidebar_position'] ?? 'left') === 'top' ? 'selected' : ''); ?>>Top (Horizontal)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-square me-2"></i>Button Styling
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="button_radius" class="form-label">Border Radius</label>
                                <div class="input-group">
                                    <input 
                                        type="range" 
                                        class="form-range" 
                                        id="button_radius" 
                                        name="button_radius" 
                                        min="0" 
                                        max="20" 
                                        step="1"
                                        value="<?php echo e($settings['branding.button_radius'] ?? 4); ?>"
                                        aria-label="Button border radius"
                                    />
                                    <span class="input-group-text" id="button_radius_value"><?php echo e($settings['branding.button_radius'] ?? 4); ?>px</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button_padding" class="form-label">Padding</label>
                                <div class="input-group">
                                    <input 
                                        type="range" 
                                        class="form-range" 
                                        id="button_padding" 
                                        name="button_padding" 
                                        min="4" 
                                        max="16" 
                                        step="1"
                                        value="<?php echo e($settings['branding.button_padding'] ?? 8); ?>"
                                        aria-label="Button padding"
                                    />
                                    <span class="input-group-text" id="button_padding_value"><?php echo e($settings['branding.button_padding'] ?? 8); ?>px</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="button_shadow" class="form-label">Shadow</label>
                                <select class="form-select" id="button_shadow" name="button_shadow" aria-label="Button shadow">
                                    <option value="none" <?php echo e(($settings['branding.button_shadow'] ?? 'sm') === 'none' ? 'selected' : ''); ?>>None</option>
                                    <option value="sm" <?php echo e(($settings['branding.button_shadow'] ?? 'sm') === 'sm' ? 'selected' : ''); ?>>Small</option>
                                    <option value="md" <?php echo e(($settings['branding.button_shadow'] ?? 'sm') === 'md' ? 'selected' : ''); ?>>Medium</option>
                                    <option value="lg" <?php echo e(($settings['branding.button_shadow'] ?? 'sm') === 'lg' ? 'selected' : ''); ?>>Large</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-code me-2"></i>Custom CSS
                        </h6>
                        <div class="mb-3">
                            <label for="custom_css" class="form-label">Custom CSS Rules</label>
                            <textarea 
                                class="form-control font-monospace" 
                                id="custom_css" 
                                name="custom_css" 
                                rows="8"
                                maxlength="5000"
                                placeholder="/* Add custom CSS rules here */&#10;.custom-class {&#10;  color: #333;&#10;}"
                                aria-describedby="custom_css_help"
                            ><?php echo e(old('custom_css', $settings['branding.custom_css'] ?? '')); ?></textarea>
                            <div id="custom_css_help" class="form-text">
                                Advanced CSS customization. Max 5000 characters. <span id="css_count">0</span>/5000
                            </div>
                        </div>
                    </div>

                    
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

    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-eye me-2"></i>Live Preview
                </h6>
            </div>
            <div class="card-body">
                <div class="preview-container" id="preview-container">
                    
                    <div class="preview-header mb-3 p-3 rounded" id="preview-header" style="background-color: #f8f9fa;">
                        <div class="d-flex align-items-center" id="preview-logo-container" style="justify-content: left;">
                            <img 
                                id="preview-logo" 
                                src="<?php echo e($settings['branding.logo_path'] ?? asset('images/default-logo.svg')); ?>" 
                                alt="Logo Preview" 
                                style="height: 50px; display: <?php echo e(!empty($settings['branding.logo_path']) ? 'block' : 'none'); ?>;"
                            />
                            <div class="ms-3">
                                <h5 class="mb-0" id="preview-app-name"><?php echo e($settings['app.name'] ?? 'Procurement System'); ?></h5>
                                <small class="text-muted" id="preview-app-tagline"><?php echo e($settings['app.tagline'] ?? ''); ?></small>
                            </div>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <div class="preview-text" id="preview-text">
                            <p class="mb-2">
                                This is sample body text using the selected font family and size. The quick brown fox jumps over the lazy dog.
                            </p>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <h6 class="mb-2 small text-muted">Button Styles</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm" id="preview-btn-primary">Primary</button>
                            <button class="btn btn-sm" id="preview-btn-secondary">Secondary</button>
                            <button class="btn btn-sm" id="preview-btn-accent">Accent</button>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <h6 class="mb-2 small text-muted">Link Styles</h6>
                        <a href="#" id="preview-link" class="d-block mb-1">Sample hyperlink</a>
                    </div>

                    
                    <div class="alert alert-sm mb-0" id="preview-alert" role="alert">
                        <i class="fas fa-info-circle me-1"></i>
                        Sample alert with accent color
                    </div>
                </div>

                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Preview updates in real-time as you make changes
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/branding.js']); ?>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/pages/branding.css']); ?>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/superadmin/tabs/branding.blade.php ENDPATH**/ ?>