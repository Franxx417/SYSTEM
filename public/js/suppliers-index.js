// Suppliers Index Page JavaScript
// Expose editSupplier as a global function
window.editSupplier = function editSupplier(id, name, vatType, address, contactPerson, contactNumber, tinNo) {
    var form = document.getElementById('editSupplierForm');
    if (form) form.action = '/suppliers/' + id;
    
    var el;
    el = document.getElementById('edit_name'); if (el) el.value = name || '';
    el = document.getElementById('edit_vat_type'); if (el) el.value = vatType || '';
    el = document.getElementById('edit_address'); if (el) el.value = address || '';
    el = document.getElementById('edit_contact_person'); if (el) el.value = contactPerson || '';
    el = document.getElementById('edit_contact_number'); if (el) el.value = contactNumber || '';
    el = document.getElementById('edit_tin_no'); if (el) el.value = tinNo || '';
    
    if (window.bootstrap && document.getElementById('editSupplierModal')) {
        new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
    }
};

// Delete supplier function
window.deleteSupplier = function deleteSupplier(id, name) {
    var form = document.getElementById('deleteSupplierForm');
    if (form) form.action = '/suppliers/' + id;
    
    var nameEl = document.getElementById('delete_supplier_name');
    if (nameEl) nameEl.textContent = name || '';
    
    if (window.bootstrap && document.getElementById('deleteSupplierModal')) {
        new bootstrap.Modal(document.getElementById('deleteSupplierModal')).show();
    }
};
