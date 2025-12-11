<?php $__env->startSection('title','Create Purchase Order'); ?>
<?php $__env->startSection('page_heading','Create Purchase Order'); ?>
<?php $__env->startSection('page_subheading','Fill in supplier, items, and totals'); ?>

<?php $__env->startPush('styles'); ?>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
    <!-- PO create form: supplier, dates, items, and realtime totals -->
    <form method="POST" action="<?php echo e(route('po.store')); ?>" id="poForm" data-next-number-url="<?php echo e(route('po.next_number')); ?>" data-latest-price-url="<?php echo e(route('api.items.latest_price')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">There were problems saving your PO:</div>
                <ul class="mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card"><div class="card-body">
                    <!-- Generated PO number preview (server will generate the same pattern) -->
                    <div class="mb-3">
                        <label class="form-label">PO Number</label>
                        <input class="form-control" id="po-number" type="text" value="Generating..." disabled />
                    </div>
                    <!-- Supplier select includes VAT flag; indicator shows below -->
                    <div class="mb-1">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="supplier_id" id="supplier-select" required>
                            <option value="">Select supplier</option>
                            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($s->supplier_id); ?>" data-vat="<?php echo e($s->vat_type); ?>"><?php echo e($s->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <option value="__manual__">-- Add New Supplier --</option>
                        </select>
                    </div>
                    
                    <!-- Manual Supplier Fields (initially hidden) -->
                    <div id="manual-supplier-fields" class="d-none">
                        <div class="card mb-3">
                            <div class="card-header bg-light">New Supplier Details</div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="supplier-name" name="new_supplier[name]">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">VAT Type</label>
                                        <select class="form-select" id="supplier-vat-type" name="new_supplier[vat_type]">
                                            <option value="">-- None --</option>
                                            <option value="VAT">VAT</option>
                                            <option value="Non-VAT">Non-VAT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Address</label>
                                    <input type="text" class="form-control" id="supplier-address" name="new_supplier[address]">
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Contact Person</label>
                                        <input type="text" class="form-control" id="supplier-contact-person" name="new_supplier[contact_person]">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="supplier-contact-number" name="new_supplier[contact_number]">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">TIN No.</label>
                                    <input type="text" class="form-control" id="supplier-tin" name="new_supplier[tin_no]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="purpose-input">Purpose</label>
                        <textarea class="form-control" type="text"  id="purpose-input" name="purpose" required maxlength="255" ></textarea>
                        <label for="purpose-input" id="text-count" class="text-muted"></label>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date Requested</label>
                            <input id="date-from" class="form-control" type="text" name="date_requested" required autocomplete="off" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Delivery Date</label>
                            <input id="date-to" class="form-control" type="text" name="delivery_date" required autocomplete="off" />
                        </div>
                        <div id="result" class="text-muted small mt-2"></div>
                    </div>
                </div></div>
            </div>
            <div class="col-lg-6">
    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between">
            <span class="text-muted">Shipping</span>
            <input class="form-control text-end w-50 number-only-input" id="calc-shipping-input" type="text" min="0" step="0.01" placeholder="0.00" />
        </div>
        <div class="d-flex justify-content-between mt-2">
            <span class="text-muted">Discount</span>
            <input class="form-control text-end w-50 number-only-input" id="calc-discount-input" type="text" min="0" step="0.01" placeholder="0.00" />
        </div>
        <div class="d-flex justify-content-between mt-3">
    <span class="text-muted">Vatable Sales (Ex Vat)</span>
    <input class="form-control text-end w-50 number-only-input" id="calc-subtotal" type="text" placeholder="0" readonly />
</div>
<div class="d-flex justify-content-between">
    <span class="text-muted">12% Vat</span>
    <input class="form-control text-end w-50 number-only-input" id="calc-vat" type="text" placeholder="0" readonly >
        </div>
        <hr>
        <div class="d-flex justify-content-between fw-semibold">
            <span>TOTAL</span>
            <span id="calc-total">0.00</span>
        </div>
    </div></div>
</div>
        </div>

        <!-- Items list; description can be picked from previous items or entered manually -->
        <div class="card mt-3"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="fw-semibold">Items</div>
                <button class="btn btn-sm btn-outline-primary" id="addItem" type="button">Add Item</button>
            </div>
            <div id="items"></div>
        </div></div>

        <div class="mt-3 d-flex justify-content-end gap-2">
            <button class="btn btn-outline-primary" type="submit" name="action" value="save">Save</button>
            <button class="btn btn-primary" type="submit" name="action" value="save_and_print">Save & Print</button>
        </div>
    </form>

    <!-- Template for each item row (cloned dynamically) -->
    <template id="itemRowTpl">
        <div class="row g-2 align-items-end item-row mb-2">
            <div class="col-md-12">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Item Name</label>
                        <select class="form-select item-name-select" name="items[IDX][item_name]">
                            <option value="">Select item</option>
                            <option value="__manual__">+ Add new item manually</option>
                            <?php $__currentLoopData = $existingNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($row->item_name); ?>" data-desc="<?php echo e($row->item_name); ?>" data-price="<?php echo e($row->unit_price); ?>"><?php echo e($row->item_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <input class="form-control d-none item-name-manual" type="text" placeholder="Type item name" maxlength="255" />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Description</label>
                        <input class="form-control item-desc-manual" name="items[IDX][item_description]" type="text" placeholder="Type item description (optional)" maxlength="255" />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input class="form-control" name="items[IDX][quantity]" type="number" min="1" value="1" required />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit Price</label>
                        <input class="form-control unit-price" name="items[IDX][unit_price]" type="number" min="0" step="0.01" placeholder="0.00" />
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-danger removeItem" type="button">Remove</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        // Simple direct approach - initialize immediately
        jQuery(function($) {
            console.log('Initializing datepickers...');
            
            var $from = $('#date-from');
            var $to = $('#date-to');
            
            if ($from.length && $to.length) {
                $from.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function(d) {
                        $to.datepicker('option', 'minDate', d);
                        updateDateInfo();
                    }
                });
                
                $to.datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    onSelect: function(d) {
                        $from.datepicker('option', 'maxDate', d);
                        updateDateInfo();
                    }
                });
                
                function updateDateInfo() {
                    var fromVal = $from.val();
                    var toVal = $to.val();
                    var $result = $('#result');
                    if (fromVal && toVal) {
                        var fromDate = new Date(fromVal);
                        var toDate = new Date(toVal);
                        var diffTime = Math.abs(toDate - fromDate);
                        var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        $result.text('Delivery period: ' + diffDays + ' days');
                    } else {
                        $result.text('');
                    }
                }
                
                console.log('Datepickers initialized successfully!');
            }
            
            // Purpose counter
            var $purpose = $('#purpose-input');
            var $count = $('#text-count');
            var maxLen = $purpose.attr('maxlength');
            if ($purpose.length && $count.length && maxLen) {
                function updateCounter() {
                    var rem = maxLen - $purpose.val().length;
                    $count.text(rem + ' characters remaining');
                    $count.toggleClass('text-danger', rem <= 20).toggleClass('text-muted', rem > 20);
                }
                updateCounter();
                $purpose.on('input', updateCounter);
            }
        });
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pages/po-create.js']); ?>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\KAIZER\Desktop\cdn\resources\views/po/create.blade.php ENDPATH**/ ?>