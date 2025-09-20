// document.addEventListener('DOMContentLoaded', () => {
//             const items = document.getElementById('items');
//             const tpl = document.getElementById('itemRowTpl').innerHTML;
//             let idx = 0;
//             // Fetch next PO number for display
//             fetch('{{ route('po.next_number') }}').then(r=>r.json()).then(d=>{
//                 const el = document.getElementById('po-number');
//                 if (el && d.po_no) el.value = d.po_no;
//             });
//             // VAT indicator
//             const supplierSelect = document.getElementById('supplier-select');
//             function updateVat(){
//                 const opt = supplierSelect.options[supplierSelect.selectedIndex];
//                 document.getElementById('supplier-vat').textContent = opt && opt.dataset && opt.dataset.vat ? opt.dataset.vat : '—';
//             }
//             if (supplierSelect){ supplierSelect.addEventListener('change', updateVat); updateVat(); }
//             function addRow(){
//                 const html = tpl.replaceAll('IDX', String(idx++));
//                 const wrapper = document.createElement('div');
//                 wrapper.innerHTML = html;
//                 const row = wrapper.firstElementChild;
//                 items.appendChild(row);
//                 wireRow(row);
//                 recalcTotals();
//             }
//             document.getElementById('addItem').addEventListener('click', addRow);
//             items.addEventListener('click', (e)=>{
//                 if (e.target.classList.contains('removeItem')){
//                     e.target.closest('.item-row')?.remove();
//                     recalcTotals();
//                 }
//             });
//             function wireRow(row){
//                 const select = row.querySelector('.item-desc-select');
//                 const manual = row.querySelector('.item-desc-manual');
//                 const unitPrice = row.querySelector('.unit-price');

//                 function switchToManual(){
//                     manual.classList.remove('d-none');
//                     // move name attribute to manual input so it posts the value
//                     manual.name = select.name;
//                     select.removeAttribute('name');
//                     manual.focus();
//                 }
//                 function switchToSelect(){
//                     if (!select.name) select.name = manual.name || 'items[][item_description]';
//                     manual.name = '';
//                     manual.classList.add('d-none');
//                 }

//                 select.addEventListener('change', async ()=>{
//                     if (!select.value) { switchToSelect(); if (unitPrice) unitPrice.value=''; return; }
//                     if (select.value === '__manual__') { switchToManual(); if (unitPrice) unitPrice.value=''; return; }
//                     switchToSelect();
//                     const supplierSelect = document.querySelector('select[name="supplier_id"]');
//                     const supplierId = supplierSelect ? supplierSelect.value : '';
//                     if (!supplierId) { return; }
//                     const url = new URL('{{ route('api.items.latest_price') }}', window.location.origin);
//                     url.searchParams.set('supplier_id', supplierId);
//                     url.searchParams.set('description', select.value);
//                     const res = await fetch(url.toString());
//                     const data = await res.json();
//                     if (data.price && unitPrice) {
//                         unitPrice.value = parseFloat(data.price).toFixed(2);
//                         recalcTotals();
//                     }
//                 });

//                 // If user types manually and then clears, keep manual input name
//                 manual.addEventListener('input', ()=>{ /* placeholder for extra validation if needed */ });

//                 // Recalculate when qty or unit price changes
//                 const qty = row.querySelector('input[name$="[quantity]"]');
//                 qty.addEventListener('input', recalcTotals);
//                 if (unitPrice) unitPrice.addEventListener('input', recalcTotals);
//             }
//             // Realtime totals per sample policy
//             function recalcTotals(){
//                 let subtotal = 0;
//                 document.querySelectorAll('.item-row').forEach(r => {
//                     const q = parseFloat(r.querySelector('input[name$="[quantity]"]').value || '0');
//                     const p = parseFloat((r.querySelector('.unit-price')?.value) || '0');
//                     subtotal += q * p;
//                 });
//                 const shipping = subtotal > 0 ? 6000.00 : 0.00;
//                 const discount = subtotal > 0 ? 13543.00 : 0.00;
//                 const vat = Math.round(subtotal * 0.12 * 100) / 100;
//                 const total = Math.round((subtotal + vat) * 100) / 100;
//                 document.getElementById('calc-shipping').textContent = shipping.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
//                 document.getElementById('calc-discount').textContent = discount.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
//                 document.getElementById('calc-subtotal').textContent = subtotal.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
//                 document.getElementById('calc-vat').textContent = vat.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
//                 document.getElementById('calc-total').textContent = total.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
//             }
//             addRow();
//             recalcTotals();
//         });