// Items Index Page JavaScript
// Handles delete confirmation modal and edit item modal interactions

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
    if (qtyInput) qtyInput.value = quantity || '';
    var priceInput = document.getElementById('edit_unit_price');
    if (priceInput) priceInput.value = unitPrice || '';
    if (window.bootstrap && document.getElementById('editItemModal')) {
        new bootstrap.Modal(document.getElementById('editItemModal')).show();
    }
};

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert){
        setTimeout(function(){
            if (alert && alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    });
});
