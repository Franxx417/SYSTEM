<!-- System Maintenance -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>System Maintenance</h6>
            </div>
            <div class="card-body">
                <div class="maintenance-actions">
                    <div class="maintenance-card warning" data-action="clear-cache">
                        <div class="maintenance-icon text-warning">
                            <i class="fas fa-broom"></i>
                        </div>
                        <h6>Clear Cache</h6>
                        <p class="text-muted small mb-0">Clear application cache and temporary files</p>
                    </div>
                    <div class="maintenance-card success" data-action="backup-system">
                        <div class="maintenance-icon text-success">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h6>Full Backup</h6>
                        <p class="text-muted small mb-0">Create complete system backup</p>
                    </div>
                    <div class="maintenance-card info" data-action="update-system">
                        <div class="maintenance-icon text-info">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h6>Update System</h6>
                        <p class="text-muted small mb-0">Check and install system updates</p>
                    </div>
                    <div class="maintenance-card danger" data-action="restart-services">
                        <div class="maintenance-icon text-danger">
                            <i class="fas fa-power-off"></i>
                        </div>
                        <h6>Restart Services</h6>
                        <p class="text-muted small mb-0">Restart system services</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>PHP Version</span>
                    <span class="text-muted" data-system-info="php_version"><?php echo e(PHP_VERSION); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Laravel Version</span>
                    <span class="text-muted" data-system-info="laravel_version"><?php echo e(app()->version()); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Server Time</span>
                    <span class="text-muted" data-system-info="server_time"><?php echo e(now()->format('Y-m-d H:i:s')); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Uptime</span>
                    <span class="text-muted" data-system-info="uptime"><?php echo e($systemInfo['uptime'] ?? 'N/A'); ?></span>
                </div>
                <hr>
                <button class="btn btn-outline-primary btn-sm w-100" data-action="refresh-system-info">
                    <i class="fas fa-sync-alt me-1"></i>Refresh Information
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/superadmin/tabs/system.blade.php ENDPATH**/ ?>