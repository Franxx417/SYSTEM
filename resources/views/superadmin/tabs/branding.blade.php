<div class="card border-0 shadow-sm">
    <div class="card-header">Branding</div>
    <div class="card-body">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Please fix the following:</div>
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form id="branding-form" enctype="multipart/form-data" data-validate>
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Application Name</label>
                        <input class="form-control" type="text" name="app_name" 
                               value="{{ $settings['app.name'] ?? '' }}" 
                               maxlength="100" required />
                        <div class="form-text">This will appear in the sidebar and page titles</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Application Description</label>
                        <textarea class="form-control" name="app_description" rows="3" 
                                  maxlength="255">{{ $settings['app.description'] ?? '' }}</textarea>
                        <div class="form-text">Brief description of your application</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Logo Upload</label>
                        <div class="logo-upload-area" onclick="document.querySelector('input[name=logo]').click()">
                            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                            <div>Click to upload or drag and drop</div>
                            <small class="text-muted">PNG, JPG, SVG (Max: 2MB)</small>
                        </div>
                        <input class="form-control d-none" type="file" name="logo" 
                               accept=".png,.jpg,.jpeg,.svg" />
                    </div>
                    @if(!empty($settings['branding.logo_path']))
                        <div class="text-center">
                            <div class="mb-2"><strong>Current Logo:</strong></div>
                            <img src="{{ $settings['branding.logo_path'] }}" 
                                 alt="Current Logo" 
                                 class="current-logo"/>
                        </div>
                    @endif
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Save Branding
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('branding-form').reset()">
                    <i class="fas fa-undo me-1"></i>Reset
                </button>
            </div>
        </form>
    </div>
</div>
