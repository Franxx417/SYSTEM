// Suppliers Index Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value;

    // Handle Add Supplier Form
    const addSupplierForm = document.getElementById('addSupplierForm');
    if (addSupplierForm) {
        addSupplierForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Supplier created successfully!', 'success');
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('addSupplierModal')).hide();
                    // Reset form
                    addSupplierForm.reset();
                    // Reload page to show new supplier
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Failed to create supplier', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while creating the supplier', 'error');
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Handle Edit Supplier Form
    const editSupplierForm = document.getElementById('editSupplierForm');
    if (editSupplierForm) {
        editSupplierForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Supplier updated successfully!', 'success');
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('editSupplierModal')).hide();
                    // Reload page to show updated supplier
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Failed to update supplier', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while updating the supplier', 'error');
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }

    // Handle Delete Supplier Form
    const deleteSupplierForm = document.getElementById('deleteSupplierForm');
    if (deleteSupplierForm) {
        deleteSupplierForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Deleting...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Supplier deleted successfully!', 'success');
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('deleteSupplierModal')).hide();
                    // Reload page to remove deleted supplier
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Failed to delete supplier', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while deleting the supplier', 'error');
            })
            .finally(() => {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

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

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
