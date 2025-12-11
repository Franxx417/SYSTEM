<!-- Enhanced Database Management -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-database me-2"></i>Database Overview</h6>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h4 text-primary mb-1" data-db-stat="total_tables">
                                <?php echo e(isset($dbStats['total_tables']) && is_numeric($dbStats['total_tables']) ? number_format($dbStats['total_tables']) : '0'); ?>

                            </div>
                            <div class="text-muted small">Total Tables</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h4 text-info mb-1" data-db-stat="total_records">
                                <?php echo e(isset($dbStats['total_records']) && is_numeric($dbStats['total_records']) ? number_format($dbStats['total_records']) : '0'); ?>

                            </div>
                            <div class="text-muted small">Total Records</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h4 text-warning mb-1" data-db-stat="db_size">
                                <?php echo e($dbStats['db_size'] ?? 'N/A'); ?>

                            </div>
                            <div class="text-muted small">Database Size</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <div class="h4 text-success mb-1" data-db-stat="last_backup">
                                <?php echo e($dbStats['last_backup'] ?? 'Never'); ?>

                            </div>
                            <div class="text-muted small">Last Backup</div>
                        </div>
                    </div>
                </div>
                
                <?php if(!isset($dbStats) || empty($dbStats)): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Database statistics are not available at this time.
                    </div>
                <?php endif; ?>
                
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-outline-primary" data-action="load-table-info" title="Load current table information">
                        <i class="fas fa-sync-alt me-1"></i>Refresh Table Info
                    </button>
                    <a href="<?php echo e(route('superadmin.database')); ?>" class="btn btn-primary" title="Configure database settings">
                        <i class="fas fa-cog me-1"></i>Database Settings
                    </a>
                </div>
                
                <!-- Table information will be loaded here -->
                <div id="table-info" class="table-responsive">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-2"></i>
                        <p class="mb-0">Click "Refresh Table Info" to load database tables</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Database Tools</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" data-action="optimize-database" title="Optimize database performance">
                        <i class="fas fa-tachometer-alt me-1"></i>Optimize Database
                    </button>
                    <button class="btn btn-outline-info" data-action="check-integrity" title="Check database integrity">
                        <i class="fas fa-check-circle me-1"></i>Check Integrity
                    </button>
                    <button class="btn btn-outline-warning" data-action="repair-tables" title="Repair corrupted tables">
                        <i class="fas fa-wrench me-1"></i>Repair Tables
                    </button>
                    <button class="btn btn-outline-success" data-action="create-backup" title="Create database backup">
                        <i class="fas fa-download me-1"></i>Create Backup
                    </button>
                </div>
                
                <hr class="my-3">
                
                <div class="small text-muted">
                    <p class="mb-2"><strong>Quick Tips:</strong></p>
                    <ul class="ps-3 mb-0">
                        <li>Optimize regularly for best performance</li>
                        <li>Check integrity after system errors</li>
                        <li>Create backups before major changes</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Debug Info (Development Only) -->
        <?php if(config('app.debug')): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fas fa-bug me-2"></i>Debug Info</h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p class="mb-1"><strong>DB Connection:</strong> <?php echo e(config('database.default')); ?></p>
                    <p class="mb-1"><strong>DB Name:</strong> <?php echo e(config('database.connections.sqlsrv.database')); ?></p>
                    <p class="mb-1"><strong>API Endpoint:</strong> /api/superadmin/database/table-info</p>
                    <p class="mb-0"><strong>Debug Mode:</strong> <span class="badge bg-warning">ON</span></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/superadmin/tabs/database.blade.php ENDPATH**/ ?>