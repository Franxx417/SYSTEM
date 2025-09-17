// PO Index: modal view handler for My Purchase Orders
document.addEventListener('DOMContentLoaded', function(){
  var table = document.getElementById('po-index-table');
  if (!table) return;
  var jsonTemplate = table.getAttribute('data-po-show-template');
  if (!jsonTemplate) return;

  // View PO Modal Handler
  table.addEventListener('click', async function(e){
    var btn = e.target && e.target.closest('.btn-view-po');
    if (!btn) return;
    var po = btn.getAttribute('data-po');
    var url = jsonTemplate.replace('__po__', po);
    try {
      var r = await fetch(url);
      if (!r.ok) return;
      var d = await r.json();
      fillPoModal(d);
      if (window.bootstrap && document.getElementById('poModal')) {
        new bootstrap.Modal(document.getElementById('poModal')).show();
      }
    } catch (_) {}
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

  // Confirm Status Change
  var confirmBtn = document.getElementById('confirmStatusChange');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function() {
      if (!(window.pendingStatusForm && window.pendingStatusValue)) return;
      var form = window.pendingStatusForm;
      var select = window.pendingStatusSelect;
      var newVal = window.pendingStatusValue;
      var remarksEl = document.getElementById('status_remarks');
      var remarks = remarksEl ? remarksEl.value : '';

      // Prepare AJAX request
      var tokenInput = form.querySelector('input[name="_token"]');
      var csrf = tokenInput ? tokenInput.value : '';
      var fd = new FormData(form);
      fd.set('status_id', newVal);
      fd.set('remarks', remarks || 'Status changed via dropdown');

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf
        },
        body: fd,
        credentials: 'same-origin'
      }).then(function(res){
        if (!res.ok) throw new Error('Failed to update status');
        return res.json().catch(function(){ return {}; });
      }).then(function(){
        // Update UI selection and original value
        if (select) {
          select.value = newVal;
          select.dataset.originalValue = newVal;
          // update data-current-status label
          var opt = select.options[select.selectedIndex];
          if (opt) select.setAttribute('data-current-status', opt.textContent);
        }
      }).catch(function(err){
        console.error(err);
        alert('Could not update status. Please try again.');
      }).finally(function(){
        var modal = bootstrap.Modal.getInstance(document.getElementById('statusChangeModal'));
        if (modal) modal.hide();
        // cleanup pending refs
        window.pendingStatusForm = null;
        window.pendingStatusValue = null;
        window.pendingStatusSelect = null;
        window.pendingStatusOldValue = null;
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
});


