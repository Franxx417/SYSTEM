// PO Create Page Script
// - Autogenerates PO number, toggles supplier VAT label
// - Manages dynamic item rows and latest price lookup
// - Recalculates totals in real-time (subtotal, VAT, total)
document.addEventListener('DOMContentLoaded', () => {
  console.log('PO Create script loaded');
  const formEl = document.getElementById('poForm');
  if (!formEl) {
    console.log('Form element not found');
    return; // only run on PO create page
  }
  console.log('Form element found:', formEl);

  const items = document.getElementById('items');
  const tplEl = document.getElementById('itemRowTpl');
  const tpl = tplEl ? tplEl.innerHTML : '';
  let idx = 0;

  fetch(formEl.dataset.nextNumberUrl || '/po/next/number')
    .then(r=>r.ok?r.json():{})
    .then(d=>{ const el=document.getElementById('po-number'); if(el && d.po_no) el.value=d.po_no; })
    .catch(()=>{});

  const supplierSelect = document.getElementById('supplier-select');
  const manualSupplierFields = document.getElementById('manual-supplier-fields');
  
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
  const addBtn = document.getElementById('addItem');
  if (addBtn) addBtn.addEventListener('click', addRow);
  if (items) {
    items.addEventListener('click', (e)=>{
      const target = e.target;
      if (target && target.classList && target.classList.contains('removeItem')){
        const row = target.closest('.item-row');
        if (row) row.remove();
        recalcTotals();
      }
    });
  }

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

  // This will be handled by initializeForm() function

  // --- Draft persistence (localStorage) ---
  const DRAFT_KEY = 'po_create_draft_v1';
  const SESSION_KEY = 'po_create_session_id';
  
  // Generate a unique session ID for this form creation session
  function generateSessionId() {
    return Date.now() + '_' + Math.random().toString(36).substr(2, 9);
  }
  
  // Check if this is a fresh form creation or a page refresh
  function isFreshFormCreation() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentSessionId = sessionStorage.getItem(SESSION_KEY);
    
    // If there's a 'new' parameter or no session ID exists, it's a fresh creation
    if (urlParams.has('new') || !currentSessionId) {
      return true;
    }
    
    // Check if we're coming from a different page (referrer check)
    const referrer = document.referrer;
    const currentPath = window.location.pathname;
    
    // If referrer doesn't contain the current path, it's likely a fresh creation
    if (referrer && !referrer.includes(currentPath)) {
      return true;
    }
    
    return false;
  }
  
  function serializeForm(){
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
  
  function restoreForm(data){
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
  
  function saveDraft(){ 
    try{ 
      localStorage.setItem(DRAFT_KEY, JSON.stringify(serializeForm())); 
      // Also save a timestamp to track when the draft was last updated
      localStorage.setItem(DRAFT_KEY + '_timestamp', Date.now().toString());
    }catch{} 
  }
  
  function loadDraft(){ 
    try{ 
      const raw = localStorage.getItem(DRAFT_KEY); 
      return raw ? JSON.parse(raw) : null; 
    }catch{ 
      return null; 
    } 
  }
  
  function clearDraft(){ 
    try{ 
      localStorage.removeItem(DRAFT_KEY); 
      localStorage.removeItem(DRAFT_KEY + '_timestamp');
      sessionStorage.removeItem(SESSION_KEY);
    }catch{} 
  }
  
  // Initialize form based on whether it's a fresh creation or refresh
  function initializeForm() {
    if (isFreshFormCreation()) {
      // Fresh form creation - clear any existing draft and start clean
      clearDraft();
      // Generate new session ID
      sessionStorage.setItem(SESSION_KEY, generateSessionId());
      console.log('Fresh form creation - starting with empty form');
      
      // Add initial empty item row for fresh forms
      if (tpl && items) {
        addRow();
        recalcTotals();
      }
    } else {
      // Page refresh - restore from draft if available
      const draftData = loadDraft();
      if (draftData) {
        console.log('Page refresh detected - restoring form data');
        restoreForm(draftData);
      } else {
        // No draft data but not a fresh creation - add default row
        if (tpl && items) {
          addRow();
          recalcTotals();
        }
      }
    }
  }
  
  // Save on changes
  formEl.addEventListener('input', saveDraft);
  formEl.addEventListener('change', saveDraft);
  
  // Initialize the form
  initializeForm();
  
  // Clear on successful submit (let server redirect first; best effort)
  formEl.addEventListener('submit', ()=>{ clearDraft(); });
});

// Number-only validation for financial inputs
function enforcePositiveNumberOnly(input) {
  input.addEventListener('input', function(e) {
    let value = this.value;
    // Remove any non-numeric characters except decimal point
    value = value.replace(/[^0-9.]/g, '');
    // Only allow one decimal point
    const parts = value.split('.');
    if (parts.length > 2) {
      value = parts[0] + '.' + parts.slice(1).join('');
    }
    // Ensure non-negative
    const num = parseFloat(value);
    if (num < 0 || isNaN(num)) {
      value = '';
    }
    this.value = value;
  });
  
  input.addEventListener('blur', function() {
    if (this.value === '' || this.value === '.') {
      this.value = '0.00';
    } else {
      const num = parseFloat(this.value);
      this.value = isNaN(num) ? '0.00' : num.toFixed(2);
    }
  });
  
  // Prevent negative numbers on paste
  input.addEventListener('paste', function(e) {
    setTimeout(() => {
      let value = this.value.replace(/[^0-9.]/g, '');
      const num = parseFloat(value);
      if (num < 0 || isNaN(num)) {
        this.value = '0.00';
      }
    }, 0);
  });
}

// Apply number-only validation to all financial inputs
document.querySelectorAll('.number-only-input:not([readonly])').forEach(input => {
  enforcePositiveNumberOnly(input);
});

// Note: Datepickers are now initialized directly in the view file using simple jQuery
// This keeps the main script focused on form functionality


