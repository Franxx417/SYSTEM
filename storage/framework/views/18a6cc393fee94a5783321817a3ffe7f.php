<?php $__env->startSection('title','Requestor Dashboard'); ?>
<?php $__env->startSection('page_heading','Procurement Dashboard'); ?>
<?php $__env->startSection('page_subheading','Quick overview of your operations'); ?>
<?php $__env->startSection('content'); ?>
    
    <link rel="stylesheet" href="<?php echo e(route('dynamic.status.css')); ?>">
        <div id="req-dashboard" data-summary-url="<?php echo e(route('api.dashboard.summary')); ?>" data-po-show-template="<?php echo e(route('po.show', '__po__')); ?>">
        <!-- Summary cards: quick metrics for the current requestor -->
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">My POs</div>
                    <div class="h3 mb-0" id="metric-my-total"><?php echo e($metrics['my_total'] ?? 0); ?></div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">Verified</div>
                    <div class="h3 mb-0" id="metric-my-verified"><?php echo e($metrics['my_verified'] ?? 0); ?></div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted">Approved</div>
                    <div class="h3 mb-0" id="metric-my-approved"><?php echo e($metrics['my_approved'] ?? 0); ?></div>
                </div></div>
            </div>
        </div>

        <!-- Recent POs -->
        <div class="row g-3">
            <div class="col-lg-12">
                <div class="card"><div class="card-header">Recent POs</div>
                    <ul class="list-group list-group-flush">
                        <?php $__currentLoopData = $recentPOs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div><strong>#<?php echo e($p->purchase_order_no); ?></strong></div>
                                    <small class="text-muted"><?php echo e(Str::limit($p->purpose, 30)); ?></small>
                                    <?php if($p->supplier_name): ?>
                                        <br><small class="text-muted"><?php echo e(Str::limit($p->supplier_name, 25)); ?></small>
                                    <?php endif; ?>
                                </div>
                                <span class="badge bg-primary">₱<?php echo e(number_format($p->total,2)); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
                <a class="btn btn-primary w-100 mt-3" href="<?php echo e(route('po.create')); ?>?new=1">Create Purchase Order</a>
            </div>
        </div>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/js/dashboards/requestor-dashboard.js']); ?>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/dashboards/requestor.blade.php ENDPATH**/ ?>