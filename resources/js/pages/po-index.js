// PO Index: modal view handler for My Purchase Orders
// Import direct print functionality
import './po-print-direct.js';

document.addEventListener('DOMContentLoaded', function(){
  var table = document.getElementById('po-index-table');
  if (!table) return;
  var jsonTemplate = table.getAttribute('data-po-show-template');
  if (!jsonTemplate) return;

  // View PO Modal Handler (use new Order Details modal)
  table.addEventListener('click', function(e){
    var btn = e.target && e.target.closest('.btn-view-po');
    if (!btn) return;
    var po = btn.getAttribute('data-po');
    // Use the new API-backed modal
    fetchOrderDetails(po);
    // Prevent any other handlers from running for this click
    e.stopPropagation();
    e.preventDefault();
  });

  // Edit PO Modal Handler
  window.editPO = function editPO(poNo, purpose, supplierId, dateRequested, deliveryDate){
    var form = document.getElementById('editPOForm');
    if (form) form.action = '/po/' + poNo;
    var el;
    el = document.getElementById('edit_purpose'); if (el) el.value = purpose || '';
    el = document.getElementById('edit_supplier_id'); if (el) el.value = supplierId || '';
    el = document.getElementById('edit_date_requested'); if (el) el.value = dateRequested || '';
    el = document.getElementById('edit_delivery_date'); if (el) el.value = deliveryDate || '';
    if (window.bootstrap && document.getElementById('editPOModal')) {
      new bootstrap.Modal(document.getElementById('editPOModal')).show();
    }
  };

  // Delete PO Modal Handler
  window.deletePO = function deletePO(poNo, purpose){
    var form = document.getElementById('deletePOForm');
    if (form) form.action = '/po/' + poNo;
    var el;
    el = document.getElementById('delete_po_number'); if (el) el.textContent = poNo || '';
    el = document.getElementById('delete_po_purpose'); if (el) el.textContent = purpose || '';
    if (window.bootstrap && document.getElementById('deletePOModal')) {
      new bootstrap.Modal(document.getElementById('deletePOModal')).show();
    }
  };

  // Status Change Modal Handler (prevent auto-submit/refresh)
  var statusDropdowns = document.querySelectorAll('.status-dropdown');
  statusDropdowns.forEach(function(dropdown) {
    // keep track of original value
    dropdown.dataset.originalValue = dropdown.value;
    
    // Set initial status indicator color
    updateStatusIndicator(dropdown);
    
    dropdown.addEventListener('change', function(e) {
      e.preventDefault();
      e.stopPropagation();
      var select = this;
      var form = select.closest('form');
      if (!form) return;
      // Prevent native submit of this form unless explicitly allowed
      form.addEventListener('submit', function(ev){
        if (!window.__allowStatusSubmit) {
          ev.preventDefault();
          ev.stopPropagation();
        }
      }, { once: true, capture: true });

      var poNo = select.dataset.po || form.action.split('/').pop();
      var currentStatus = select.getAttribute('data-current-status') || (function(){
        var sel = select.options[select.selectedIndex];
        return sel ? sel.textContent : '';
      })();
      var newStatus = select.options[select.selectedIndex].textContent;
      var newStatusId = select.value;

      // Store refs for confirm handler
      window.pendingStatusForm = form;
      window.pendingStatusValue = newStatusId;
      window.pendingStatusSelect = select;
      window.pendingStatusOldValue = select.dataset.originalValue || select.value;

      // Populate modal
      var el;
      el = document.getElementById('status_po_number'); if (el) el.textContent = poNo;
      el = document.getElementById('status_from'); if (el) el.textContent = currentStatus;
      el = document.getElementById('status_to'); if (el) el.textContent = newStatus;
      el = document.getElementById('status_remarks'); if (el) el.value = '';

      if (window.bootstrap && document.getElementById('statusChangeModal')) {
        var modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
        modal.show();
      }

      // Revert UI immediately until confirmed
      select.value = select.dataset.originalValue;
    });
  });
  
  // Function to update status indicator based on selected status
  function updateStatusIndicator(selectElement) {
    var statusName = selectElement.options[selectElement.selectedIndex].textContent;
    var statusIndicator = selectElement.closest('.d-flex')?.querySelector('.status-indicator');
    
    if (!statusIndicator) return;
    
    // Remove all existing status classes
    statusIndicator.classList.remove('status-warning', 'status-info', 'status-online', 'status-success', 'status-offline');
    
    // Add appropriate class based on status
    switch(statusName) {
      case 'Pending':
        statusIndicator.classList.add('status-warning');
        break;
      case 'Verified':
        statusIndicator.classList.add('status-info');
        break;
      case 'Approved':
        statusIndicator.classList.add('status-online');
        break;
      case 'Received':
        statusIndicator.classList.add('status-success');
        break;
      case 'Rejected':
        statusIndicator.classList.add('status-offline');
        break;
    }
  }

  // Confirm Status Change - Works with modal-based status changes
  var confirmBtn = document.getElementById('confirmStatusChange');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function() {
      var form = document.getElementById('statusChangeForm');
      var statusIdInput = document.getElementById('selected_status_id');
      var remarksEl = document.getElementById('status_remarks');
      
      if (!form || !statusIdInput || !statusIdInput.value) {
        console.error('[PO Status Update] Missing form or status ID');
        return;
      }
      
      var newVal = statusIdInput.value;
      var remarks = remarksEl ? remarksEl.value : '';

      // Prepare AJAX request
      var tokenInput = form.querySelector('input[name="_token"]');
      var csrf = tokenInput ? tokenInput.value : '';
      var fd = new FormData(form);
      fd.set('status_id', newVal);
      fd.set('remarks', remarks || 'Status changed via status modal');

      console.log('[PO Status Update] Sending request...', {
        action: form.action,
        status_id: newVal,
        remarks: remarks
      });

      // Disable button to prevent double-submission
      if (confirmBtn) confirmBtn.disabled = true;

      fetch(form.action, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf
        },
        body: fd,
        credentials: 'same-origin'
      }).then(async function(res){
        const contentType = res.headers.get('content-type');
        
        // Check if response is JSON
        if (!contentType || !contentType.includes('application/json')) {
          const text = await res.text();
          console.error('[PO Status Update] Received non-JSON response:', text.substring(0, 200));
          throw new Error('Server returned HTML instead of JSON. Please check authentication.');
        }
        
        const data = await res.json();
        
        if (!res.ok) {
          throw new Error(data.error || 'Failed to update status');
        }
        
        return data;
      }).then(function(data){
        console.log('[PO Status Update] Success:', data);
        
        // Show success message
        if (data.success && data.message) {
          showNotification(data.message, 'success');
        }
        
        // Reload to sync with latest data
        setTimeout(function(){
          location.reload();
        }, 1500);
        
      }).catch(function(err){
        console.error('[PO Status Update] Error:', err);
        showNotification('Error: ' + err.message, 'error');
      }).finally(function(){
        // Re-enable button
        if (confirmBtn) confirmBtn.disabled = false;
        
        var modal = bootstrap.Modal.getInstance(document.getElementById('statusChangeModal'));
        if (modal) modal.hide();
      });
    });
  }

  // Fill PO Modal with data
  function fillPoModal(d){
    var po = (d && d.po) || {};
    var items = (d && d.items) || [];
    var el;
    el = document.getElementById('poModalLabel'); if (el) el.textContent = 'PO #' + (po.purchase_order_no || '');
    el = document.getElementById('poModalSupplier'); if (el) el.textContent = po.supplier_name || '';
    el = document.getElementById('poModalStatus'); if (el) el.textContent = po.status_name || '';
    el = document.getElementById('poModalPurpose'); if (el) el.textContent = po.purpose || '';
    el = document.getElementById('poModalTotals'); if (el) el.textContent = '₱' + Number(po.total || 0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
    var tbody = document.getElementById('poModalItems'); if (tbody){
      tbody.innerHTML = '';
      items.forEach(function(it){
        var tr = document.createElement('tr');
        tr.innerHTML = '<td>' + (it.item_name || it.item_description || '') + '</td>' +
                       '<td class="text-end">' + (it.quantity || 0) + '</td>' +
                       '<td class="text-end">₱' + Number(it.unit_price||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>' +
                       '<td class="text-end">₱' + Number(it.total_cost||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2}) + '</td>';
        tbody.appendChild(tr);
      });
    }
  }

  // Enhanced Edit Modal Population
  function populateEditModal(d) {
    var po = (d && d.po) || {};
    var items = (d && d.items) || [];
    
    // Populate basic fields
    var el;
    el = document.getElementById('edit-po-number'); if (el) el.value = po.purchase_order_no || '';
    el = document.getElementById('purpose-input'); if (el) el.value = po.purpose || '';
    el = document.getElementById('date-from'); if (el) el.value = po.date_requested || '';
    el = document.getElementById('date-to'); if (el) el.value = po.delivery_date || '';
    
    // Populate supplier
    var supplierSelect = document.getElementById('supplier-select');
    if (supplierSelect && po.supplier_id) {
      supplierSelect.value = po.supplier_id;
      // Trigger change event to update VAT status
      supplierSelect.dispatchEvent(new Event('change'));
    }
    
    // Trigger datepicker date info update after data population
    if (window.jQuery && typeof window.updatePODateInfo === 'function') {
      console.log('Triggering datepicker date info update after data population');
      setTimeout(function() {
        window.updatePODateInfo();
      }, 100);
    }
    
    // Debug log for modal data population
    console.log('PO edit modal populated with data:', {
      po_number: po.purchase_order_no,
      date_requested: po.date_requested,
      delivery_date: po.delivery_date,
      supplier_id: po.supplier_id
    });
    
    // Populate totals
    el = document.getElementById('calc-shipping-input'); if (el) el.value = Number(po.shipping_fee || 0).toFixed(2);
    el = document.getElementById('calc-discount-input'); if (el) el.value = Number(po.discount || 0).toFixed(2);
    el = document.getElementById('calc-subtotal'); if (el) el.value = Number(po.subtotal || 0).toFixed(2);
    el = document.getElementById('calc-vat'); if (el) el.value = Number((po.total || 0) - (po.subtotal || 0)).toFixed(2);
    el = document.getElementById('calc-total'); if (el) el.textContent = Number(po.total || 0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
    
    // Clear and populate items
    var itemsContainer = document.getElementById('items');
    if (itemsContainer) {
      itemsContainer.innerHTML = '';
      
      items.forEach(function(item, index) {
        // Add item row using the template
        var template = document.getElementById('itemRowTpl');
        if (template) {
          var html = template.innerHTML.replaceAll('IDX', String(index));
          var wrapper = document.createElement('div');
          wrapper.innerHTML = html;
          var row = wrapper.firstElementChild;
          itemsContainer.appendChild(row);
          
          // Populate item data
          var nameSelect = row.querySelector('.item-name-select');
          var nameManual = row.querySelector('.item-name-manual');
          var descInput = row.querySelector('.item-desc-manual');
          var qtyInput = row.querySelector('input[name$="[quantity]"]');
          var priceInput = row.querySelector('.unit-price');
          
          // Set item name
          if (nameSelect && item.item_name) {
            var found = false;
            Array.from(nameSelect.options).forEach(function(option) {
              if (option.value === item.item_name) {
                option.selected = true;
                found = true;
              }
            });
            if (!found) {
              nameSelect.value = '__manual__';
              nameSelect.dispatchEvent(new Event('change'));
              if (nameManual) nameManual.value = item.item_name;
            }
          }
          
          // Set other fields
          if (descInput) descInput.value = item.item_description || '';
          if (qtyInput) qtyInput.value = item.quantity || '';
          if (priceInput) priceInput.value = Number(item.unit_price || 0).toFixed(2);
          
          // Wire the row for dynamic behavior (from po-edit.js)
          if (window.poEditWireRow) window.poEditWireRow(row);
        }
      });
      if (window.poEditRecalcTotals) window.poEditRecalcTotals();
    }
  }

  // Update the existing edit modal functions to use the new structure
  window.showEditModal = function(poId) {
    try {
      // Clear any existing draft data when opening a specific PO for edit
      if (window.poEditClearDraft) {
        window.poEditClearDraft();
      }
      
      // Reuse the same JSON endpoint used for the View modal
      var table = document.getElementById('po-index-table');
      var tmpl = table ? table.getAttribute('data-po-show-template') : null;
      var url = tmpl ? tmpl.replace('__po__', poId) : ('/po/' + poId + '/data');
      fetch(url)
        .then(response => response.json())
        .then(data => {
          populateEditModal(data);
          var form = document.getElementById('poEditForm');
          if (form) {
            form.action = '/po/' + poId;
          }
          var modal = new bootstrap.Modal(document.getElementById('editPOModal'));
          
          // Add event listener to ensure datepickers initialize after modal is fully shown
          document.getElementById('editPOModal').addEventListener('shown.bs.modal', function onModalShown() {
            console.log('Edit modal shown via showEditModal - datepickers should initialize');
            // Remove this listener to prevent memory leaks
            document.getElementById('editPOModal').removeEventListener('shown.bs.modal', onModalShown);
          }, { once: true });
          
          modal.show();
        })
        .catch(error => {
          console.error('Error loading PO data:', error);
          alert('Error loading purchase order data');
        });
    } catch (e) {
      console.error(e);
      alert('Could not open edit modal.');
    }
  };

  // Auto-dismiss alerts after 5 seconds
  var alerts = document.querySelectorAll('.alert');
  alerts.forEach(function(alert) {
    setTimeout(function() {
      var bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  // Helper function to show notifications
  function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
    notification.innerHTML = `
      <i class="fas ${icon} me-2"></i>${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss after 3 seconds
    setTimeout(function() {
      if (notification.parentNode) {
        const bsAlert = new bootstrap.Alert(notification);
        bsAlert.close();
      }
    }, 3000);
  }

  // Note: View button event handler is already handled by table event delegation above (line 12-21)

  /**
   * Fetch order details and populate the modal
   */
  async function fetchOrderDetails(poNumber) {
    try {
      const response = await fetch(`/api/purchase-orders/${poNumber}`);
      
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }
      
      const data = await response.json();

      if (data.success) {
        // Populate order information fields
        document.getElementById('order_po_number').textContent = data.order.purchase_order_no || '';
        document.getElementById('order_made_by').textContent = data.order.made_by || 'N/A';
        document.getElementById('order_date_requested').textContent = data.order.date_requested || 'N/A';
        document.getElementById('order_delivery_date').textContent = data.order.delivery_date || 'N/A';
        
        // Format and display created/updated dates
        const formatDate = (dateStr) => {
          if (!dateStr) return 'N/A';
          try {
            return new Date(dateStr).toLocaleString();
          } catch {
            return dateStr;
          }
        };
        document.getElementById('order_created_at').textContent = formatDate(data.order.created_at);
        document.getElementById('order_updated_at').textContent = formatDate(data.order.updated_at);
        
        // Populate order details
        document.getElementById('order_purpose').textContent = data.order.purpose || 'N/A';
        document.getElementById('order_supplier').textContent = data.order.supplier_name || 'N/A';
        
        // Set status with appropriate styling
        const statusElement = document.getElementById('order_status');
        statusElement.textContent = data.order.status_name || 'N/A';
        statusElement.className = 'badge bg-secondary'; // Default styling
        
        // Add status-specific styling
        const statusName = data.order.status_name;
        if (statusName) {
          statusElement.classList.remove('bg-secondary');
          switch(statusName.toLowerCase()) {
            case 'pending':
              statusElement.classList.add('bg-warning');
              break;
            case 'verified':
              statusElement.classList.add('bg-info');
              break;
            case 'approved':
              statusElement.classList.add('bg-success');
              break;
            case 'received':
              statusElement.classList.add('bg-primary');
              break;
            case 'rejected':
              statusElement.classList.add('bg-danger');
              break;
            default:
              statusElement.classList.add('bg-secondary');
          }
        }
        
        document.getElementById('order_remarks').textContent = data.order.remarks || 'No remarks';
        
        // Populate order totals
        const formatCurrency = (amount) => {
          const num = Number(amount || 0);
          return `₱${num.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        };
        
        document.getElementById('order_subtotal').textContent = formatCurrency(data.order.subtotal);
        document.getElementById('order_shipping').textContent = formatCurrency(data.order.shipping_fee);
        document.getElementById('order_discount').textContent = formatCurrency(data.order.discount);
        document.getElementById('order_total').textContent = formatCurrency(data.order.total);

        // Populate items
        const itemsTable = document.getElementById('order_items');
        itemsTable.innerHTML = '';
        (data.items || []).forEach(item => {
          const qty = Number(item.quantity || 0);
          const price = Number(item.unit_price || 0);
          const totalCost = Number(item.total_cost || (qty * price));
          const row = `<tr>
              <td>${item.item_name || 'N/A'}</td>
              <td>${item.description || 'N/A'}</td>
              <td class="text-end">${qty}</td>
              <td class="text-end">${formatCurrency(price)}</td>
              <td class="text-end">${formatCurrency(totalCost)}</td>
          </tr>`;
          itemsTable.insertAdjacentHTML('beforeend', row);
        });

        // Show modal
        const orderDetailsModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        orderDetailsModal.show();
      } else {
        alert('Failed to fetch order details. Please try again.');
      }
    } catch (error) {
      console.error('Error fetching order details:', error);
      console.error('Error details:', {
        message: error.message,
        stack: error.stack,
        poNumber: poNumber
      });
      alert(`Error fetching order details: ${error.message}. Please check the console for more details.`);
    }
  }

  // If redirected after creation/update with an open_po flag, auto-open the modal
  const openPo = table.dataset.openPo;
  if (openPo) {
    // slight delay to ensure bootstrap is ready
    setTimeout(() => fetchOrderDetails(openPo), 150);
  }
});

// Global functions for status change modal (must be outside DOMContentLoaded)
let currentPO = '';
let currentStatusName = '';

window.showStatusChangeModal = function(poNumber, statusName) {
  currentPO = poNumber;
  currentStatusName = statusName;
  
  document.getElementById('status_po_number').textContent = poNumber;
  document.getElementById('status_current').textContent = statusName;
  
  // Reset form
  document.getElementById('statusChangeForm').action = `/po/${poNumber}/status`;
  document.getElementById('selected_status_id').value = '';
  document.getElementById('status_remarks').value = '';
  document.getElementById('confirmStatusChange').disabled = true;
  
  // Reset all status options
  document.querySelectorAll('.status-option').forEach(option => {
    option.classList.remove('active');
  });
  
  // Show modal
  new bootstrap.Modal(document.getElementById('statusChangeModal')).show();
};

window.selectStatus = function(element) {
  // Remove active class from all options
  document.querySelectorAll('.status-option').forEach(option => {
    option.classList.remove('active');
  });
  
  // Add active class to selected option
  element.classList.add('active');
  
  // Set hidden input value
  const statusId = element.getAttribute('data-status-id');
  const statusName = element.getAttribute('data-status-name');
  
  document.getElementById('selected_status_id').value = statusId;
  
  // Enable confirm button if different status selected
  const confirmBtn = document.getElementById('confirmStatusChange');
  if (statusName !== currentStatusName) {
    confirmBtn.disabled = false;
  } else {
    confirmBtn.disabled = true;
  }
};
