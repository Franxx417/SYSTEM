// Mirror of create page UX for edit page: VAT label, totals calc, counters, datepickers
document.addEventListener('DOMContentLoaded', function(){
  var vatLabel = document.getElementById('supplier-vat');
  var supplierSelect = document.getElementById('supplier-select');
  var manualSupplierFields = document.getElementById('manual-supplier-fields');
  function updateVat(){
    if (!supplierSelect || !vatLabel) return;
    var opt = supplierSelect.options[supplierSelect.selectedIndex];
    vatLabel.textContent = (opt && opt.dataset && opt.dataset.vat) ? opt.dataset.vat : '—';
    if (supplierSelect && supplierSelect.value === '__manual__'){
      if (manualSupplierFields) manualSupplierFields.classList.remove('d-none');
      var vt = document.getElementById('supplier-vat-type');
      if (vt) vatLabel.textContent = vt.value || 'VAT';
      // Ensure manual supplier inputs have names
      var map = [
        ['supplier-name','new_supplier[name]'],
        ['supplier-vat-type','new_supplier[vat_type]'],
        ['supplier-address','new_supplier[address]'],
        ['supplier-contact-person','new_supplier[contact_person]'],
        ['supplier-contact-number','new_supplier[contact_number]'],
        ['supplier-tin','new_supplier[tin_no]']
      ];
      map.forEach(function(p){ var el=document.getElementById(p[0]); if (el) el.setAttribute('name', p[1]); });
    } else {
      if (manualSupplierFields) manualSupplierFields.classList.add('d-none');
      // Remove names when not used
      ['supplier-name','supplier-vat-type','supplier-address','supplier-contact-person','supplier-contact-number','supplier-tin']
        .forEach(function(id){ var el=document.getElementById(id); if (el) el.removeAttribute('name'); });
    }
  }
  if (supplierSelect){ supplierSelect.addEventListener('change', updateVat); updateVat(); }
  var supplierVatType = document.getElementById('supplier-vat-type');
  if (supplierVatType){ supplierVatType.addEventListener('change', function(){ if (supplierSelect && supplierSelect.value==='__manual__' && vatLabel){ vatLabel.textContent = this.value; } }); }

  var items = document.getElementById('items');
  function recalc(){
    var subtotal = 0;
    if (items){
      Array.prototype.forEach.call(items.querySelectorAll('.item-row'), function(r){
        var q = parseFloat((r.querySelector('input[name$="[quantity]"]')||{}).value || '0');
        var p = parseFloat((r.querySelector('.unit-price')||{}).value || '0');
        subtotal += (isNaN(q)?0:q) * (isNaN(p)?0:p);
      });
    }
    var shipping = 0, discount = 0;
    var subEl = document.getElementById('calc-subtotal');
    var vatEl = document.getElementById('calc-vat');
    var totalEl = document.getElementById('calc-total');
    if (subEl) subEl.value = '';
    if (vatEl) vatEl.value = '';
    if (totalEl) totalEl.textContent = (Math.round((subtotal - discount + shipping) * 100) / 100).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
  }
  if (items){
    items.addEventListener('input', function(e){ if (e.target.matches('.unit-price') || e.target.name && e.target.name.endsWith('[quantity]')) recalc(); });
  }
  recalc();

  if (typeof window !== 'undefined' && window.jQuery){
    jQuery(function($){
      var $from = $('#date-from'); var $to = $('#date-to');
      if ($from.length && $to.length){
        $from.datepicker({ dateFormat: 'yy-mm-dd', onSelect: function(d){ $to.datepicker('option','minDate', d); }});
        $to.datepicker({ dateFormat: 'yy-mm-dd', onSelect: function(d){ $from.datepicker('option','maxDate', d); }});
      }
      var $purpose = $('#purpose-input'); var $count = $('#text-count'); var maxLen = $purpose.attr('maxlength');
      if ($purpose.length && $count.length && maxLen){
        function updateCounter(){ var rem = maxLen - $purpose.val().length; $count.text(rem + ' characters remaining').toggleClass('text-danger', rem<=20).toggleClass('text-muted', rem>20); }
        updateCounter(); $purpose.on('input', updateCounter);
      }
      function handleNum(){ var $i=$(this); var v=$i.val().replace(/[^0-9.]/g,''); var parts=v.split('.'); if(parts.length>2) v=parts[0]+'.'+parts.slice(1).join(''); var n=parseFloat(v); if (n<0 || isNaN(n)) v='0.00'; $i.val(v); }
      function blurNum(){ var $i=$(this); var n=parseFloat($i.val()); $i.val(isNaN(n)?'0.00':n.toFixed(2)); }
      $('#calc-shipping-input, #calc-discount-input').on('input', handleNum).on('blur', blurNum).on('input', recalc);
    });
  }
});


