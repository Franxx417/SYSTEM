// Page script for resources/views/po/create.blade.php
document.addEventListener('DOMContentLoaded', () => {
  const formEl = document.getElementById('poForm');
  if (!formEl) return; // only run on PO create page

  const items = document.getElementById('items');
  const tplEl = document.getElementById('itemRowTpl');
  const tpl = tplEl ? tplEl.innerHTML : '';
  let idx = 0;

  fetch(formEl.dataset.nextNumberUrl || '/po/next/number')
    .then(r=>r.ok?r.json():{})
    .then(d=>{ const el=document.getElementById('po-number'); if(el && d.po_no) el.value=d.po_no; })
    .catch(()=>{});

  const supplierSelect = document.getElementById('supplier-select');
  function updateVat(){
    const opt = supplierSelect?.options[supplierSelect.selectedIndex];
    const vatEl = document.getElementById('supplier-vat');
    if (vatEl) vatEl.textContent = (opt && opt.dataset && opt.dataset.vat) ? opt.dataset.vat : '—';
  }
  if (supplierSelect){ supplierSelect.addEventListener('change', updateVat); updateVat(); }

  function addRow(){
    const html = tpl.replaceAll('IDX', String(idx++));
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    const row = wrapper.firstElementChild;
    items.appendChild(row);
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

  function wireRow(row){
    const select = row.querySelector('.item-desc-select');
    const manual = row.querySelector('.item-desc-manual');
    const unitPrice = row.querySelector('.unit-price');

    function switchToManual(){ manual.classList.remove('d-none'); manual.name = select.name; select.removeAttribute('name'); manual.focus(); }
    function switchToSelect(){ if (!select.name) select.name = manual.name || 'items[][item_description]'; manual.name=''; manual.classList.add('d-none'); }

    select.addEventListener('change', async ()=>{
      if (!select.value) { switchToSelect(); if (unitPrice) unitPrice.value=''; return; }
      if (select.value === '__manual__') { switchToManual(); if (unitPrice) unitPrice.value=''; return; }
      switchToSelect();
      try{
        const url = new URL(formEl.dataset.latestPriceUrl || '/api/items/latest-price', window.location.origin);
        url.searchParams.set('description', select.value);
        const data = await fetch(url.toString()).then(r=>r.json());
        if (data.price !== null && data.price !== undefined && unitPrice) { unitPrice.value = parseFloat(data.price).toFixed(2); recalcTotals(); }
        else if (unitPrice) { unitPrice.value=''; }
      }catch{ if (unitPrice) unitPrice.value=''; }
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
    const vat = 0; // keep VAT excluded from total
    const total = Math.round(vatableSales * 100) / 100;
    const subtotalInput = document.getElementById('calc-subtotal');
    const vatInput = document.getElementById('calc-vat');
    const totalDisplay = document.getElementById('calc-total');
    // Keep these fields always empty as requested
    if (subtotalInput) subtotalInput.value = '';
    if (vatInput) vatInput.value = '';
    if (totalDisplay) totalDisplay.textContent = total.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
  }

  const shippingInput = document.getElementById('calc-shipping-input');
  const discountInput = document.getElementById('calc-discount-input');
  if (shippingInput) shippingInput.addEventListener('input', recalcTotals);
  if (discountInput) discountInput.addEventListener('input', recalcTotals);

  if (tpl && items) {
    addRow();
    recalcTotals();
  }

  
});
// jQuery-only helpers used on the page
if (typeof window !== 'undefined' && window.jQuery) {
  jQuery(function($){
    const $from = $('#date-from'); const $to = $('#date-to');
    if ($from.length && $to.length) {
      $from.datepicker({ dateFormat: 'yy-mm-dd', onSelect: d => $to.datepicker('option','minDate', d) });
      $to.datepicker({ dateFormat: 'yy-mm-dd', onSelect: d => $from.datepicker('option','maxDate', d) });
    }
    const $purpose = $('#purpose-input'); const $count = $('#text-count'); const maxLen = $purpose.attr('maxlength');
    if ($purpose.length && $count.length && maxLen){
      function updateCounter(){ const rem = maxLen - $purpose.val().length; $count.text(rem + ' characters remaining'); $count.toggleClass('text-danger', rem<=20).toggleClass('text-muted', rem>20); }
      updateCounter(); $purpose.on('input', updateCounter);
    }
    function handleNum(){ const $i=$(this); let v=$i.val().replace(/[^0-9.]/g,''); const parts=v.split('.'); if(parts.length>2) v=parts[0]+'.'+parts.slice(1).join(''); const n=parseFloat(v); if (n<0 || isNaN(n)) v='0.00'; $i.val(v); }
    function blurNum(){ const $i=$(this); const n=parseFloat($i.val()); $i.val(isNaN(n)?'0.00':n.toFixed(2)); }
    $('#calc-shipping-input, #calc-discount-input, #calc-vat-input, #calc-subtotal-input').on('input', handleNum).on('blur', blurNum);
  });
}


