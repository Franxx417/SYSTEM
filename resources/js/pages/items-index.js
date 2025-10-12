// Items Index Page JavaScript
// Handles delete confirmation modal and edit item modal interactions
// Replicates the same functionality as create.blade.php for modal behaviors

// Expose as globals so existing inline onclick attributes (if any) keep working
window.confirmDelete = function confirmDelete(itemId, itemName) {
    var nameEl = document.getElementById('itemName');
    if (nameEl) nameEl.textContent = itemName || '';
    var form = document.getElementById('deleteForm');
    if (form) {
        var baseUrl = window.location.origin;
        form.action = baseUrl + '/items/' + itemId;
    }
    if (window.bootstrap && document.getElementById('deleteModal')) {
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
};

window.editItem = function editItem(id, itemName, description, quantity, unitPrice) {
    var form = document.getElementById('editItemForm');
    if (form) {
        form.action = '/items/' + id;
    }
    var nameInput = document.getElementById('edit_item_name');
    if (nameInput) nameInput.value = itemName || '';
    var descInput = document.getElementById('edit_item_description');
    if (descInput) descInput.value = description || '';
    var qtyInput = document.getElementById('edit_quantity');
    if (qtyInput) qtyInput.value = quantity || '1';
    var priceInput = document.getElementById('edit_unit_price');
    if (priceInput) priceInput.value = unitPrice || '';
    
    // Calculate initial total (matches create.blade.php behavior)
    calculateEditTotal();
    
    if (window.bootstrap && document.getElementById('editItemModal')) {
        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    }
};

// Calculate total cost for create modal
// Matches the exact calculation logic from create.blade.php
function calculateCreateTotal() {
    var quantityInput = document.getElementById('create_quantity');
    var unitPriceInput = document.getElementById('create_unit_price');
    var totalCostInput = document.getElementById('create_total_cost');
    
    if (quantityInput && unitPriceInput && totalCostInput) {
        var quantity = parseFloat(quantityInput.value) || 0;
        var unitPrice = parseFloat(unitPriceInput.value) || 0;
        var total = quantity * unitPrice;
        totalCostInput.value = total.toFixed(2);
    }
}

// Calculate total cost for edit modal
// Replicates the same calculation behavior as create.blade.php for consistency
function calculateEditTotal() {
    var quantityInput = document.getElementById('edit_quantity');
    var unitPriceInput = document.getElementById('edit_unit_price');
    var totalCostInput = document.getElementById('edit_total_cost');
    
    if (quantityInput && unitPriceInput && totalCostInput) {
        var quantity = parseFloat(quantityInput.value) || 0;
        var unitPrice = parseFloat(unitPriceInput.value) || 0;
        var total = quantity * unitPrice;
        totalCostInput.value = total.toFixed(2);
    }
}

// Initialize page functionality - matches create.blade.php behavior
document.addEventListener('DOMContentLoaded', function() {
    // Alert auto-dismiss
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert){
        setTimeout(function(){
            if (alert && alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
    
    // Attach event listeners for create modal - matches create.blade.php functionality
    var createQty = document.getElementById('create_quantity');
    var createPrice = document.getElementById('create_unit_price');
    if (createQty) {
        createQty.addEventListener('input', calculateCreateTotal);
    }
    if (createPrice) {
        createPrice.addEventListener('input', calculateCreateTotal);
    }
    
    // Attach event listeners for edit modal - replicates create.blade.php behavior
    var editQty = document.getElementById('edit_quantity');
    var editPrice = document.getElementById('edit_unit_price');
    if (editQty) {
        editQty.addEventListener('input', calculateEditTotal);
    }
    if (editPrice) {
        editPrice.addEventListener('input', calculateEditTotal);
    }
    
    // Reset create form when modal is opened - matches create.blade.php initialization
    var createModal = document.getElementById('createItemModal');
    if (createModal) {
        createModal.addEventListener('show.bs.modal', function() {
            var form = document.getElementById('createItemForm');
            if (form) {
                // Don't reset if there are validation errors (errors will be in the modal)
                var hasErrors = form.querySelector('.alert-danger');
                if (!hasErrors) {
                    form.reset();
                    // Set default quantity to 1 (matches create.blade.php default)
                    var qtyInput = document.getElementById('create_quantity');
                    if (qtyInput) qtyInput.value = '1';
                }
                // Calculate on modal open (matches create.blade.php behavior)
                calculateCreateTotal();
            }
        });
    }
    
    // Reset edit form calculations when modal is opened - replicates create.blade.php behavior
    var editModal = document.getElementById('editItemModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function() {
            // Calculate total on modal open (matches create.blade.php behavior)
            setTimeout(function() {
                calculateEditTotal();
            }, 100); // Small delay to ensure values are populated
        });
    }
    
    // Auto-open create modal if there are validation errors
    var createModalEl = document.getElementById('createItemModal');
    if (createModalEl) {
        var createForm = document.getElementById('createItemForm');
        if (createForm && createForm.querySelector('.alert-danger')) {
            var modal = new bootstrap.Modal(createModalEl);
            modal.show();
        }
    }
    
    // Auto-open edit modal if there are validation errors  
    var editModalEl = document.getElementById('editItemModal');
    if (editModalEl) {
        var editForm = document.getElementById('editItemForm');
        if (editForm && editForm.querySelector('.alert-danger')) {
            var modal = new bootstrap.Modal(editModalEl);
            modal.show();
        }
    }
    
    // Initialize calculations on page load (matches create.blade.php behavior)
    calculateCreateTotal();
    calculateEditTotal();
    
    // Additional form validation and behavior matching create.blade.php
    var createForm = document.getElementById('createItemForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            // Ensure calculations are up to date before submission
            calculateCreateTotal();
        });
    }
    
    var editForm = document.getElementById('editItemForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            // Ensure calculations are up to date before submission
            calculateEditTotal();
        });
    }
});
