
<?php $__env->startSection('title', 'Purchase Order '.$po->purchase_order_no); ?>
<?php $__env->startSection('page_heading', 'Purchase Order #'.$po->purchase_order_no); ?>
<?php $__env->startSection('page_subheading', 'Review details and print or edit'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('po.index')); ?>" class="btn btn-outline-secondary">Back to list</a>
            <a href="<?php echo e(route('po.edit', $po->purchase_order_no)); ?>" class="btn btn-outline-primary">Edit</a>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('po.print', $po->purchase_order_no)); ?>" class="btn btn-primary" target="_blank" rel="noopener">Print</a>
        </div>
    </div>

    <?php if(session('status')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('status')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">Request Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Requestor</dt>
                        <dd class="col-sm-7"><?php echo e($auth['name'] ?? ''); ?></dd>

                        <dt class="col-sm-5">Position</dt>
                        <dd class="col-sm-7"><?php echo e($auth['position'] ?? '—'); ?></dd>

                        <dt class="col-sm-5">Department</dt>
                        <dd class="col-sm-7"><?php echo e($auth['department'] ?? '—'); ?></dd>

                        <dt class="col-sm-5">Date Requested</dt>
                        <dd class="col-sm-7"><?php echo e($po->date_requested ? \Carbon\Carbon::parse($po->date_requested)->format('Y-m-d') : '—'); ?></dd>

                        <dt class="col-sm-5">Delivery Date</dt>
                        <dd class="col-sm-7"><?php echo e($po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('Y-m-d') : '—'); ?></dd>

                        <dt class="col-sm-5">Purpose</dt>
                        <dd class="col-sm-7"><?php echo e($po->purpose); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header">Supplier</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Name</dt>
                        <dd class="col-sm-7"><?php echo e($po->supplier_name); ?></dd>

                        <dt class="col-sm-5">TIN</dt>
                        <dd class="col-sm-7"><?php echo e($po->tin_no ?? '—'); ?></dd>

                        <dt class="col-sm-5">VAT Type</dt>
                        <dd class="col-sm-7"><?php echo e($po->vat_type ?? '—'); ?></dd>

                        <dt class="col-sm-5">Contact Person</dt>
                        <dd class="col-sm-7"><?php echo e($po->contact_person ?? '—'); ?></dd>

                        <dt class="col-sm-5">Contact Number</dt>
                        <dd class="col-sm-7"><?php echo e($po->contact_number ?? '—'); ?></dd>

                        <dt class="col-sm-5">Address</dt>
                        <dd class="col-sm-7"><?php echo e($po->supplier_address ?? '—'); ?></dd>

                        <dt class="col-sm-5">Status</dt>
                        <dd class="col-sm-7"><?php echo e($po->status_name ?? '—'); ?></dd>

                        <dt class="col-sm-5">Remarks</dt>
                        <dd class="col-sm-7"><?php echo e($po->remarks ?? '—'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Items</span>
            <span class="text-muted small"><?php echo e(count($items)); ?> item<?php echo e(count($items) === 1 ? '' : 's'); ?></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th>Item</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 120px;">Qty</th>
                            <th class="text-end" style="width: 140px;">Unit Price</th>
                            <th class="text-end" style="width: 140px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($idx + 1); ?></td>
                                <td><?php echo e($it->item_name ?? '—'); ?></td>
                                <td><?php echo e($it->item_description); ?></td>
                                <td class="text-center"><?php echo e(number_format($it->quantity)); ?></td>
                                <td class="text-end">₱<?php echo e(number_format($it->unit_price, 2)); ?></td>
                                <td class="text-end">₱<?php echo e(number_format($it->total_cost, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No items found for this purchase order.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php
        $shipping = (float)($po->shipping_fee ?? 0);
        $discount = (float)($po->discount ?? 0);
        $subtotal = (float)($po->subtotal ?? 0);
        $vat = 0;
        if (($po->vat_type ?? '') === 'VAT') {
            $vat = round($subtotal * 0.12, 2);
        }
        $grand = (float)($po->total ?? ($subtotal + $vat));
    ?>

    <div class="row justify-content-end mt-3">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">Totals</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Discount</span>
                        <span>₱<?php echo e(number_format($discount, 2)); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery fee</span>
                        <span>₱<?php echo e(number_format($shipping, 2)); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Vatable Sales (Ex VAT)</span>
                        <span>₱<?php echo e(number_format($subtotal, 2)); ?></span>
                    </div>
                    <?php if(($po->vat_type ?? '') === 'VAT'): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>VAT (12%)</span>
                            <span>₱<?php echo e(number_format($vat, 2)); ?></span>
                        </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between fw-semibold">
                        <span>Total</span>
                        <span>₱<?php echo e(number_format($grand, 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/po/show.blade.php ENDPATH**/ ?>