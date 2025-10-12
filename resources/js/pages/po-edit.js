// PO Edit Page Script: dynamic items, price lookup, and totals calculation
document.addEventListener('DOMContentLoaded', function(){
  console.log('PO Edit script loaded');
  var formEl = document.getElementById('poEditForm');
  if (!formEl) {
    console.log('Form element not found');
    return;
  }
  console.log('Form element found:', formEl);
  
  var items = document.getElementById('items');
  var tplEl = document.getElementById('itemRowTpl');
  var tpl = tplEl ? tplEl.innerHTML : '';
  var idx = (items && items.children) ? items.children.length : 0;

  const supplierSelect = document.getElementById('supplier-select');
  const manualSupplierFields = document.getElementById('manual-supplier-fields');

  // Supplier VAT handling
  function updateVat(){
    const opt = supplierSelect?.options[supplierSelect.selectedIndex];
    const vatEl = document.getElementById('supplier-vat');
    const vatType = (opt && opt.dataset && opt.dataset.vat) ? opt.dataset.vat : '';
    if (vatEl) vatEl.textContent = vatType || '—';
    
    // Toggle manual supplier fields
    if (supplierSelect && supplierSelect.value === '__manual__') {
      if (manualSupplierFields) manualSupplierFields.classList.remove('d-none');
      const manualVatType = document.getElementById('supplier-vat-type')?.value || '';
      if (vatEl) vatEl.textContent = manualVatType || '—';
      // Ensure manual supplier inputs have names so they submit
      const map = [
        ['supplier-name','new_supplier[name]'],
        ['supplier-vat-type','new_supplier[vat_type]'],
        ['supplier-address','new_supplier[address]'],
        ['supplier-contact-person','new_supplier[contact_person]'],
        ['supplier-contact-number','new_supplier[contact_number]'],
        ['supplier-tin','new_supplier[tin_no]']
      ];
      map.forEach(([id, name])=>{ const el=document.getElementById(id); if (el) el.setAttribute('name', name); });
    } else {
      if (manualSupplierFields) manualSupplierFields.classList.add('d-none');
      // Remove names so they don't submit when not used
      ['supplier-name','supplier-vat-type','supplier-address','supplier-contact-person','supplier-contact-number','supplier-tin']
        .forEach(id=>{ const el=document.getElementById(id); if (el) el.removeAttribute('name'); });
    }
  }
  
  if (supplierSelect){ supplierSelect.addEventListener('change', ()=>{ updateVat(); recalcTotals(); }); updateVat(); }
  
  // Update VAT status when manual VAT type changes
  const supplierVatType = document.getElementById('supplier-vat-type');
  if (supplierVatType) {
    supplierVatType.addEventListener('change', function() {
      if (supplierSelect && supplierSelect.value === '__manual__') {
        const vatEl = document.getElementById('supplier-vat');
        if (vatEl) vatEl.textContent = this.value;
      }
      recalcTotals();
    });
  }

  function addRow(){
    const html = tpl.replaceAll('IDX', String(idx++));
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const row = wrapper.firstElementChild;
    if (items) items.appendChild(row);
    wireRow(row);
    recalcTotals();
  }
  var addBtn = document.getElementById('addItem');
  if (addBtn) addBtn.addEventListener('click', addRow);

  if (items) items.addEventListener('click', function(e){
    var t = e.target;
    if (t && t.classList && t.classList.contains('removeItem')){
      var row = t.closest('.item-row'); if (row) row.remove();
      recalcTotals();
    }
  });

  // Before submit: normalize item field names to contiguous indexes and ensure item_name posts
  if (formEl) {
    formEl.addEventListener('submit', (e) => {
      console.log('Form submit event triggered');
      const rows = Array.from(document.querySelectorAll('.item-row'));
      console.log('Found rows:', rows.length);
      if (!rows.length) return; // validation will handle min:1
      rows.forEach((row, i) => {
        const nameSelect = row.querySelector('.item-name-select');
        const nameManual = row.querySelector('.item-name-manual');
        const desc = row.querySelector('.item-desc-manual');
        const qty = row.querySelector('input[name$="[quantity]"]');
        const price = row.querySelector('.unit-price');

        // Ensure description has proper name
        if (desc) desc.name = `items[${i}][item_description]`;
        if (qty) qty.name = `items[${i}][quantity]`;
        if (price) price.name = `items[${i}][unit_price]`;

        // Ensure item_name uses a single source with correct name
        const manualVisible = nameManual && !nameManual.classList.contains('d-none');
        console.log(`Row ${i}: manualVisible=${manualVisible}, nameSelect.value=${nameSelect?.value}, nameManual.value=${nameManual?.value}`);
        if (manualVisible) {
          if (nameSelect) nameSelect.removeAttribute('name');
          if (nameManual) nameManual.name = `items[${i}][item_name]`;
          console.log(`Row ${i}: Set manual name to items[${i}][item_name]`);
        } else {
          if (nameManual) nameManual.removeAttribute('name');
          if (nameSelect) nameSelect.name = `items[${i}][item_name]`;
          console.log(`Row ${i}: Set select name to items[${i}][item_name]`);
        }
      });
    }, true);
  }

  function wireExisting(){
    if (!items) return;
    Array.prototype.forEach.call(items.querySelectorAll('.item-row'), wireRow);
  }

  function wireRow(row){
    const nameSelect = row.querySelector('.item-name-select');
    const manual = row.querySelector('.item-desc-manual');
    const unitPrice = row.querySelector('.unit-price');

    const nameManual = row.querySelector('.item-name-manual');
    function switchNameToManual(){ 
      if (nameManual){ 
        nameManual.classList.remove('d-none'); 
        nameManual.name = nameSelect.name; 
        nameManual.focus(); 
      } 
      if (nameSelect){ 
        nameSelect.removeAttribute('name'); 
      } 
    }
    function switchNameToSelect(){ 
      if (nameSelect && !nameSelect.name) {
        nameSelect.name = nameManual && nameManual.name ? nameManual.name : 'items[][item_name]'; 
      }
      if (nameManual){ 
        nameManual.name=''; 
        nameManual.classList.add('d-none'); 
      } 
    }

    if (nameSelect) nameSelect.addEventListener('change', async ()=>{
      if (!nameSelect.value){ 
        switchNameToSelect(); 
        if (manual) manual.value = '';
        if (unitPrice) unitPrice.value = '';
        recalcTotals();
        return; 
      }
      if (nameSelect.value === '__manual__'){ 
        switchNameToManual(); 
        if (manual) manual.value = '';
        if (unitPrice) unitPrice.value = '';
        recalcTotals();
        return; 
      }
      switchNameToSelect();
      const selected = nameSelect.options[nameSelect.selectedIndex];
      const suggestedDesc = selected ? (selected.dataset ? selected.dataset.desc : '') : '';
      const suggestedPrice = selected ? (selected.dataset ? selected.dataset.price : '') : '';
      
      // Auto-populate description if empty or if it matches a previous auto-populated value
      if (manual) {
        manual.value = suggestedDesc || '';
      }
      
      // Auto-populate unit price
      if (unitPrice && suggestedPrice) {
        unitPrice.value = parseFloat(suggestedPrice).toFixed(2);
      } else if (unitPrice) {
        unitPrice.value = '';
      }
      
      recalcTotals();
    });

    const qty = row.querySelector('input[name$="[quantity]"]');
    if (qty) qty.addEventListener('input', recalcTotals);
    if (unitPrice) unitPrice.addEventListener('input', recalcTotals);
  }

  function recalcTotals(){
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(r => {
      const qtyEl = r.querySelector('input[name$="[quantity]"]');
      const priceEl = r.querySelector('.unit-price');
      const q = parseFloat((qtyEl && qtyEl.value) || '0');
      const p = parseFloat((priceEl && priceEl.value) || '0');
      subtotal += q * p;
    });
    const shippingInput = document.getElementById('calc-shipping-input');
    const discountInput = document.getElementById('calc-discount-input');
    const shipping = shippingInput ? (parseFloat((shippingInput.value || '0').replace(/[^0-9.]/g,'')) || 0) : 0;
    const discount = discountInput ? (parseFloat((discountInput.value || '0').replace(/[^0-9.]/g,'')) || 0) : 0;
    const vatableSales = Math.max(0, Math.round((subtotal - discount + shipping) * 100) / 100);
    // Determine supplier VAT type
    let vatType = '';
    if (supplierSelect) {
      if (supplierSelect.value === '__manual__') {
        vatType = document.getElementById('supplier-vat-type')?.value || '';
      } else {
        const opt = supplierSelect.options[supplierSelect.selectedIndex];
        vatType = (opt && opt.dataset && opt.dataset.vat) ? opt.dataset.vat : '';
      }
    }
    // Compute VAT conditionally
    let vat = 0;
    if (vatType && vatType.toUpperCase() === 'VAT') {
      vat = Math.round(vatableSales * 0.12 * 100) / 100;
    } else {
      vat = 0; // Non-VAT or empty VAT type
    }
    // Total includes VAT only when vatType present (VAT type gets VAT, Non-VAT gets 0)
    const total = Math.round((vatableSales + (vatType ? vat : 0)) * 100) / 100;
    const subtotalInput = document.getElementById('calc-subtotal');
    const vatInput = document.getElementById('calc-vat');
    const totalDisplay = document.getElementById('calc-total');
    // Populate or keep zeros depending on VAT type presence
    if (vatType) {
      if (subtotalInput) subtotalInput.value = vatableSales.toFixed(2);
      if (vatInput) vatInput.value = vat.toFixed(2);
    } else {
      if (subtotalInput) subtotalInput.value = '0';
      if (vatInput) vatInput.value = '0';
    }
    if (totalDisplay) totalDisplay.textContent = total.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
  }

  const shippingInput = document.getElementById('calc-shipping-input');
  const discountInput = document.getElementById('calc-discount-input');
  if (shippingInput) shippingInput.addEventListener('input', recalcTotals);
  if (discountInput) discountInput.addEventListener('input', recalcTotals);

  wireExisting();
  
  // Initial calculation
  if (tpl && items) {
    recalcTotals();
  }

  // Expose helpers for other scripts (e.g., po-index.js)
  try {
    window.poEditWireRow = wireRow;
    window.poEditRecalcTotals = recalcTotals;
  } catch (_) {}

  // --- Draft persistence (localStorage) for edit modal ---
  const EDIT_DRAFT_KEY = 'po_edit_draft_v1';
  const EDIT_SESSION_KEY = 'po_edit_session_id';
  
  function generateSessionId() {
    return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }
  
  function serializeEditForm(){
    const data = {
      supplier_id: supplierSelect ? supplierSelect.value : '',
      new_supplier: {
        name: document.getElementById('supplier-name')?.value || '',
        vat_type: document.getElementById('supplier-vat-type')?.value || '',
        address: document.getElementById('supplier-address')?.value || '',
        contact_person: document.getElementById('supplier-contact-person')?.value || '',
        contact_number: document.getElementById('supplier-contact-number')?.value || '',
        tin_no: document.getElementById('supplier-tin')?.value || ''
      },
      purpose: document.getElementById('purpose-input')?.value || '',
      date_requested: document.getElementById('date-from')?.value || '',
      delivery_date: document.getElementById('date-to')?.value || '',
      shipping: document.getElementById('calc-shipping-input')?.value || '',
      discount: document.getElementById('calc-discount-input')?.value || '',
      items: []
    };
    document.querySelectorAll('#items .item-row').forEach(row => {
      const nameSelect = row.querySelector('.item-name-select');
      const nameManual = row.querySelector('.item-name-manual');
      const manualVisible = nameManual && !nameManual.classList.contains('d-none');
      const item_name = manualVisible ? (nameManual.value || '') : (nameSelect ? nameSelect.value : '');
      data.items.push({
        item_name,
        item_description: row.querySelector('.item-desc-manual')?.value || '',
        quantity: row.querySelector('input[name$="[quantity]"]')?.value || '',
        unit_price: row.querySelector('.unit-price')?.value || ''
      });
    });
    return data;
  }
  
  function restoreEditForm(data){
    try{
      if (!data) return;
      if (supplierSelect && data.supplier_id){ supplierSelect.value = data.supplier_id; supplierSelect.dispatchEvent(new Event('change')); }
      const ns = data.new_supplier || {};
      const set = (id,val)=>{ const el=document.getElementById(id); if (el){ el.value = val || ''; el.dispatchEvent(new Event('input')); } };
      set('supplier-name', ns.name);
      set('supplier-vat-type', ns.vat_type);
      set('supplier-address', ns.address);
      set('supplier-contact-person', ns.contact_person);
      set('supplier-contact-number', ns.contact_number);
      set('supplier-tin', ns.tin_no);
      set('purpose-input', data.purpose);
      set('date-from', data.date_requested);
      set('date-to', data.delivery_date);
      set('calc-shipping-input', data.shipping);
      set('calc-discount-input', data.discount);
      // rebuild items
      if (items){ items.innerHTML = ''; idx = 0; }
      (data.items || []).forEach(it => {
        addRow();
        const row = items.lastElementChild;
        const nameSelect = row.querySelector('.item-name-select');
        const nameManual = row.querySelector('.item-name-manual');
        if (nameSelect){
          if (it.item_name === '__manual__' || (nameManual && it.item_name && !Array.from(nameSelect.options).some(o=>o.value===it.item_name))) {
            // manual value
            nameSelect.value = '__manual__';
            nameSelect.dispatchEvent(new Event('change'));
            if (nameManual){ nameManual.value = it.item_name; }
          } else {
            nameSelect.value = it.item_name || '';
            nameSelect.dispatchEvent(new Event('change'));
          }
        }
        const desc = row.querySelector('.item-desc-manual'); if (desc){ desc.value = it.item_description || ''; desc.dispatchEvent(new Event('input')); }
        const qty = row.querySelector('input[name$="[quantity]"]'); if (qty){ qty.value = it.quantity || ''; qty.dispatchEvent(new Event('input')); }
        const price = row.querySelector('.unit-price'); if (price){ price.value = it.unit_price || ''; price.dispatchEvent(new Event('input')); }
      });
      recalcTotals();
    }catch{}
  }
  
  function saveEditDraft(){ 
    try{ 
      localStorage.setItem(EDIT_DRAFT_KEY, JSON.stringify(serializeEditForm())); 
      localStorage.setItem(EDIT_DRAFT_KEY + '_timestamp', Date.now().toString());
    }catch{} 
  }
  
  function loadEditDraft(){ 
    try{ 
      const raw = localStorage.getItem(EDIT_DRAFT_KEY); 
      return raw ? JSON.parse(raw) : null; 
    }catch{ 
      return null; 
    } 
  }
  
  function clearEditDraft(){ 
    try{ 
      localStorage.removeItem(EDIT_DRAFT_KEY); 
      localStorage.removeItem(EDIT_DRAFT_KEY + '_timestamp');
      sessionStorage.removeItem(EDIT_SESSION_KEY);
    }catch{} 
  }
  
  // Save on changes
  if (formEl) {
    formEl.addEventListener('input', saveEditDraft);
    formEl.addEventListener('change', saveEditDraft);
    
    // Clear on successful submit
    formEl.addEventListener('submit', ()=>{ clearEditDraft(); });
  }
  
  // Expose edit draft functions globally for use by modal handlers
  try {
    window.poEditRestoreForm = restoreEditForm;
    window.poEditLoadDraft = loadEditDraft;
    window.poEditClearDraft = clearEditDraft;
    window.poEditSaveDraft = saveEditDraft;
  } catch (_) {}

});

// jQuery-only helpers used on the page
if (typeof window !== 'undefined' && window.jQuery) {
  jQuery(function($){
    console.log('Initializing PO edit modal datepickers...');
    
    // Function to safely initialize datepickers
    function initDatepickers() {
      const $from = $('#date-from');
      const $to = $('#date-to');
      
      console.log('Found date inputs:', $from.length, $to.length);
      
      if ($from.length && $to.length) {
        // Destroy existing datepickers if any
        if ($from.hasClass('hasDatepicker')) {
          console.log('Destroying existing "from" datepicker');
          $from.datepicker('destroy');
        }
        if ($to.hasClass('hasDatepicker')) {
          console.log('Destroying existing "to" datepicker');
          $to.datepicker('destroy');
        }
        
        try {
          // Initialize datepickers with enhanced options
          $from.datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            yearRange: 'c-5:c+5',
            onSelect: function(d) {
              console.log('From date selected:', d);
              $to.datepicker('option', 'minDate', d);
              updateDateInfo();
            }
          });
          
          $to.datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            yearRange: 'c-5:c+5',
            onSelect: function(d) {
              console.log('To date selected:', d);
              $from.datepicker('option', 'maxDate', d);
              updateDateInfo();
            }
          });
          
          console.log('Datepickers initialized successfully!');
        } catch (error) {
          console.error('Error initializing datepickers:', error);
          // Fallback to manual date validation
          addManualDateValidation($from, $to);
        }
      } else {
        console.warn('Date input elements not found');
      }
    }
    
    function updateDateInfo() {
      const $from = $('#date-from');
      const $to = $('#date-to');
      const fromVal = $from.val();
      const toVal = $to.val();
      const $result = $('#result');
      if (fromVal && toVal) {
        const fromDate = new Date(fromVal);
        const toDate = new Date(toVal);
        const diffTime = Math.abs(toDate - fromDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        $result.text(`Delivery period: ${diffDays} days`);
      } else {
        $result.text('');
      }
    }
    
    // Manual date validation fallback
    function addManualDateValidation($from, $to) {
      console.log('Adding manual date validation as fallback...');
      
      $from.on('blur', function() {
        validateDateFormat($(this));
      });
      
      $to.on('blur', function() {
        validateDateFormat($(this));
        validateDateRange($from, $to);
      });
      
      $from.on('input', function() {
        validateDateRange($from, $to);
      });
      
      function validateDateFormat($input) {
        const dateStr = $input.val();
        if (dateStr) {
          const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
          if (!dateRegex.test(dateStr)) {
            $input.addClass('is-invalid');
            if (!$input.next('.invalid-feedback').length) {
              $input.after('<div class="invalid-feedback">Please use YYYY-MM-DD format</div>');
            }
          } else {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) {
              $input.addClass('is-invalid');
              if (!$input.next('.invalid-feedback').length) {
                $input.after('<div class="invalid-feedback">Please enter a valid date</div>');
              }
            } else {
              $input.removeClass('is-invalid');
              $input.next('.invalid-feedback').remove();
            }
          }
        }
      }
      
      function validateDateRange($from, $to) {
        const fromDate = new Date($from.val());
        const toDate = new Date($to.val());
        
        if (!isNaN(fromDate.getTime()) && !isNaN(toDate.getTime())) {
          if (fromDate > toDate) {
            $to.addClass('is-invalid');
            if (!$to.next('.invalid-feedback').length) {
              $to.after('<div class="invalid-feedback">Delivery date must be after request date</div>');
            }
          } else {
            $to.removeClass('is-invalid');
            $to.next('.invalid-feedback').remove();
            updateDateInfo();
          }
        }
      }
    }
    
    // Initialize datepickers when modal is shown
    $('#editPOModal').on('shown.bs.modal', function() {
      console.log('Edit modal shown, initializing datepickers...');
      
      // Add a small delay to ensure DOM is fully ready
      setTimeout(function() {
        initDatepickers();
      }, 100);
    });
    
    // Clean up datepickers when modal is hidden
    $('#editPOModal').on('hidden.bs.modal', function() {
      console.log('Edit modal hidden, cleaning up datepickers...');
      const $from = $('#date-from');
      const $to = $('#date-to');
      
      try {
        if ($from.hasClass('hasDatepicker')) {
          console.log('Destroying "from" datepicker');
          $from.datepicker('destroy');
        }
        if ($to.hasClass('hasDatepicker')) {
          console.log('Destroying "to" datepicker');
          $to.datepicker('destroy');
        }
      } catch (error) {
        console.error('Error destroying datepickers:', error);
      }
    });
    
    // Initialize on document ready for non-modal usage
    $(document).ready(function() {
      console.log('Document ready, checking for jQuery UI availability...');
      if (typeof $.datepicker !== 'undefined') {
        console.log('jQuery UI datepicker is available');
        // Initialize immediately if not in a modal context
        if (!$('#editPOModal').length) {
          initDatepickers();
        }
      } else {
        console.error('jQuery UI datepicker is not available');
      }
    });
    
    // Character counter for purpose input
    const $purpose = $('#purpose-input'); 
    const $count = $('#text-count'); 
    const maxLen = $purpose.attr('maxlength');
    if ($purpose.length && $count.length && maxLen){
      function updateCounter(){
        const rem = maxLen - $purpose.val().length; 
        $count.text(rem + ' characters remaining'); 
        $count.toggleClass('text-danger', rem<=20).toggleClass('text-muted', rem>20);
      }
      updateCounter(); 
      $purpose.on('input', updateCounter);
    }
    
    // Number input handlers for shipping and discount
    function handleNum(){ 
      const $i=$(this); 
      let v=$i.val().replace(/[^0-9.]/g,''); 
      const parts=v.split('.'); 
      if(parts.length>2) v=parts[0]+'.'+parts.slice(1).join(''); 
      const n=parseFloat(v); 
      if (n<0 || isNaN(n)) v='0.00'; 
      $i.val(v);
    }
    function blurNum(){ 
      const $i=$(this); 
      const n=parseFloat($i.val()); 
      $i.val(isNaN(n)?'0.00':n.toFixed(2));
    }
    $('#calc-shipping-input, #calc-discount-input').on('input', handleNum).on('blur', blurNum);
    
    // Expose updateDateInfo for external use
    window.updateDateInfo = updateDateInfo;
  });
}


