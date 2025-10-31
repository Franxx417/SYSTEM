class a{constructor(){this.printWindow=null,this.printData=null,this.fallbackUrl=null,this.init()}init(){this.bindEvents(),this.setupErrorHandling()}bindEvents(){document.addEventListener("click",e=>{if(e.target.closest(".btn-print-direct")){e.preventDefault(),e.stopPropagation();const t=e.target.closest(".btn-print-direct"),i=t.getAttribute("data-po-number"),d=t.getAttribute("data-fallback-url");this.directPrint(i,d)}}),document.addEventListener("keydown",e=>{if(e.key==="Enter"||e.key===" "){const t=e.target.closest(".btn-print-direct");t&&(e.preventDefault(),t.click())}})}setupErrorHandling(){window.addEventListener("error",e=>{(e.message.includes("print")||e.message.includes("Print"))&&(console.warn("Print error detected, falling back to preview method"),this.fallbackToPrintPreview())})}async directPrint(e,t){try{this.fallbackUrl=t,this.showLoadingIndicator();const i=await this.fetchPOData(e);if(!i)throw new Error("Failed to fetch purchase order data");const d=this.createPrintContent(i);if(!await this.executePrint(d))throw new Error("Direct print not supported")}catch(i){console.error("Direct print failed:",i),this.handlePrintError(i)}finally{this.hideLoadingIndicator()}}async fetchPOData(e){try{const t=await fetch(`/po/${e}/json`,{method:"GET",headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"}});if(!t.ok)throw new Error(`HTTP ${t.status}: ${t.statusText}`);return await t.json()}catch(t){return console.error("Failed to fetch PO data:",t),null}}createPrintContent(e){const{po:t,items:i,auth:d,companyLogo:r}=e,l=parseFloat(t.shipping_fee||0),s=parseFloat(t.discount||0),n=parseFloat(t.subtotal||0),p=t.vat_type==="VAT"?Math.round(n*.12*100)/100:0,g=parseFloat(t.total||n+p);return`
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO ${t.purchase_order_no}</title>
    <style>
        @page { 
            size: A4; 
            margin: 0.5in; 
        }
        html, body {
            width: 210mm;
            min-height: 297mm;
            margin: 0;
            padding: 0;
            font-family: Calibri, 'Calibri Body', Arial, Helvetica, sans-serif; 
            color: #111;
            font-size: 10pt;
            line-height: 1.4;
        }
        .container { 
            width: 100%; 
            max-width: 170mm;
            margin: 0 auto;
            padding: 8mm;
            box-sizing: border-box;
            position: relative;
        }
        .header { display:flex; justify-content:space-between; align-items:flex-start; }
        .brand { font-weight:800; letter-spacing:.5px; line-height:1.05; font-size:20px; text-align:center; }
        .title { text-align:center; font-weight:700; margin:15px 0 15px; font-size: 14pt; }
        table { width:100%; border-collapse:collapse; margin-bottom: 8px; }
        th, td { padding:5px 8px; font-size:10pt; }
        .grid { border: 1px solid #000; margin-bottom: 8px; }
        .grid td, .grid th { border-right: 1px solid #000; border-bottom: 1px solid #000; }
        .grid td:last-child, .grid th:last-child { border-right: none; }
        .grid tr:last-child td { border-bottom: none; }
        .items { border: 1px solid #000; margin-top: 8px; }
        .items td, .items th { border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 6px 8px; }
        .items td:last-child, .items th:last-child { border-right: none; }
        .items tr:last-child td { border-bottom: none; }
        .totals { border: 1px solid #000; width: 200px; margin-left: auto; margin-top: 8px; }
        .totals td { border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 5px 8px; }
        .totals td:last-child { border-right: none; }
        .totals tr:last-child td { border-bottom: none; }
        .no-border td, .no-border th { border:0 !important; }
        .right { text-align:right; }
        .center { text-align:center; }
        .signatures { 
            width: 70%; 
            margin: 40px auto 0 auto;
        }
        .sig-line { 
            border-top: 1px solid #000; 
            height: 24px; 
        }
        .footer-code { 
            position: absolute; 
            right: 0;
            bottom: 15px; 
            font-size: 9pt; 
            color:#333; 
        }
        .company-logo {
            max-height: 60px;
            max-width: 250px;
            height: auto;
            width: auto;
            display: block;
            margin: 0 auto 15px auto;
        }
        @media print { 
            .no-print { display:none !important; }
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: none;
                width: 100%;
                padding: 0.5in;
                position: relative;
                margin: 0 auto;
                min-height: calc(297mm - 1in);
                display: flex;
                flex-direction: column;
            }
            .signatures {
                position: fixed;
                bottom: 1.5in;
                left: 50%;
                transform: translateX(-50%);
                width: 70%;
                background: white;
                z-index: 10;
            }
            .footer-code {
                position: fixed;
                right: 0.5in;
                bottom: 0.5in;
                font-size: 9pt;
                color: #333;
                z-index: 11;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-area">
            ${r?`<img src="${r}" alt="Company Logo" class="company-logo">`:""}
            
            <div class="title">LOCAL PURCHASE ORDER</div>

            <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Requestor's Name:</strong></td>
                    <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${d?.name||""}</td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Date Requested:</strong></td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle;">${this.formatDate(t.date_requested)}</td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Position:</strong></td>
                    <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${d?.position||""}</td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Purchase Order No.</strong></td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle;">${t.purchase_order_no}</td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Department:</strong></td>
                    <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${d?.department||""}</td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Official Receipt No.</strong></td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle;">${t.official_receipt_no||""}</td>
                </tr>
            </table>
            
            <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:18%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Purpose/Use:</strong></td>
                    <td style="padding:4px 6px;">${t.purpose}</td>
                </tr>
            </table>

            <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Supplier/ Vendor Name:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${t.supplier_name||""}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;"><strong>TIN No.</strong> ${t.tin_no||""}</td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Supplier/ Vendor Address:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${t.supplier_address||""}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;">
                        <strong>VAT</strong> ${t.vat_type==="VAT"?"✓":"___"} &nbsp;&nbsp; 
                        <strong>Non-VAT</strong> ${t.vat_type==="Non_VAT"||t.vat_type==="Non-VAT"?"✓":"___"}
                    </td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Contact Person:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${t.contact_person||""}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;"></td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Contact Number:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${t.contact_number||""}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;"></td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Delivery Date:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${this.formatDate(t.delivery_date)}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;"></td>
                </tr>
            </table>

            <table class="items" style="margin-top:10px; margin-bottom:10px; width:100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width:8%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle;">Item No.</th>
                        <th style="width:15%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle;">Item</th>
                        <th style="width:37%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle;">Item Description</th>
                        <th style="width:10%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:center;">Quantity</th>
                        <th style="width:15%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:center;">Unit Price</th>
                        <th style="width:15%; padding:6px; border-bottom:1px solid #000; vertical-align:middle; text-align:center;">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    ${i.map((o,c)=>`
                    <tr>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:center;">${c+1}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle;">${o.item_name||""}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle;">${o.item_description}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:center;">${this.formatNumber(o.quantity)}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:right;">Php ${this.formatCurrency(o.unit_price)}</td>
                        <td style="padding:6px; vertical-align:middle; text-align:right;">Php ${this.formatCurrency(o.total_cost)}</td>
                    </tr>
                    `).join("")}
                </tbody>
            </table>

            <div style="margin-top:15px; display:flex; justify-content:flex-end;">
                <table class="totals" style="width:300px; border-collapse: collapse;">
                    <tr>
                        <td style="width:60%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:right;"><strong>Discount:</strong></td>
                        <td style="width:40%; padding:6px; border-bottom:1px solid #000; vertical-align:middle; text-align:right;">${this.formatCurrency(s)}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:right;"><strong>Delivery fee:</strong></td>
                        <td style="padding:6px; border-bottom:1px solid #000; vertical-align:middle; text-align:right;">${this.formatCurrency(l)}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:right;"><strong>TOTAL:</strong></td>
                        <td style="padding:6px; vertical-align:middle; text-align:right; font-weight:bold;">${this.formatCurrency(g)}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <table class="signatures no-border" style="margin-top:30px">
            <tr>
                <td style="width:50%; padding-right: 20px; vertical-align: top;">
                    <div style="margin-bottom: 25px;">Prepared by:</div>
                    <div class="sig-line"></div>
                    <div style="font-size: 8pt; color: #666; text-align: left;">(Sign over printed Name)<br/>Requestor's Name</div>
                </td>
                <td style="width:50%; padding-left: 20px; vertical-align: top;">
                    <div style="margin-bottom: 25px;">Approved by:</div>
                    <div class="sig-line"></div>
                    <div style="font-size: 8pt; color: #666; text-align: left;">(Sign over printed Name)<br/>Finance Controller</div>
                </td>
            </tr>
            <tr>
                <td style="padding-right: 20px; padding-top: 25px; vertical-align: top;">
                    <div style="margin-bottom: 25px;">Verified by:</div>
                    <div class="sig-line"></div>
                    <div style="font-size: 8pt; color: #666; text-align: left;">(Sign over printed Name)<br/>Department Head</div>
                </td>
                <td style="padding-left: 20px; padding-top: 25px; vertical-align: top;">
                    <div style="margin-bottom: 25px;">Received by:</div>
                    <div class="sig-line"></div>
                    <div style="font-size: 8pt; color: #666; text-align: left;">(Sign over printed Name)<br/>Requestor/ Authorized Personnel</div>
                </td>
            </tr>
        </table>

        <div class="footer-code">GAS-FIN-LPO-PH-003 Rev. 02</div>
    </div>
</body>
</html>
        `}async executePrint(e){return new Promise(t=>{try{if(!window.print){t(!1);return}const i=document.createElement("iframe");i.style.position="absolute",i.style.left="-9999px",i.style.top="-9999px",i.style.width="0",i.style.height="0",i.style.border="none",document.body.appendChild(i);const d=i.contentDocument||i.contentWindow.document;d.open(),d.write(e),d.close(),i.onload=()=>{try{i.contentWindow.focus(),i.contentWindow.print(),setTimeout(()=>{document.body.removeChild(i)},1e3),t(!0)}catch(r){console.error("Print execution failed:",r),document.body.removeChild(i),t(!1)}},setTimeout(()=>{if(i.parentNode)try{i.contentWindow.focus(),i.contentWindow.print(),setTimeout(()=>{i.parentNode&&document.body.removeChild(i)},1e3),t(!0)}catch{i.parentNode&&document.body.removeChild(i),t(!1)}},500)}catch(i){console.error("Print setup failed:",i),t(!1)}})}handlePrintError(e){console.error("Print error:",e),this.showNotification("Direct printing failed. Opening print preview...","warning"),setTimeout(()=>{this.fallbackToPrintPreview()},1e3)}fallbackToPrintPreview(){this.fallbackUrl?window.open(this.fallbackUrl,"_blank")||this.showNotification("Please allow popups to print this document","error"):this.showNotification("Print functionality is not available","error")}showLoadingIndicator(){const e=document.createElement("div");e.id="print-loading-overlay",e.innerHTML=`
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                        background: rgba(0,0,0,0.5); z-index: 9999; display: flex; 
                        align-items: center; justify-content: center;">
                <div style="background: white; padding: 20px; border-radius: 8px; 
                           text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="width: 40px; height: 40px; border: 4px solid #f3f3f3; 
                               border-top: 4px solid #007bff; border-radius: 50%; 
                               animation: spin 1s linear infinite; margin: 0 auto 10px;"></div>
                    <div>Preparing document for printing...</div>
                </div>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        `,document.body.appendChild(e)}hideLoadingIndicator(){const e=document.getElementById("print-loading-overlay");e&&e.remove()}showNotification(e,t="info"){const i=document.createElement("div");i.className=`alert alert-${t} alert-dismissible fade show`,i.style.position="fixed",i.style.top="20px",i.style.right="20px",i.style.zIndex="10000",i.style.minWidth="300px",i.innerHTML=`
            ${e}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `,document.body.appendChild(i),setTimeout(()=>{i.parentNode&&i.remove()},5e3)}formatDate(e){if(!e)return"";try{return new Date(e).toLocaleDateString("en-US",{month:"2-digit",day:"2-digit",year:"numeric"})}catch{return e}}formatNumber(e){return new Intl.NumberFormat().format(e||0)}formatCurrency(e){return new Intl.NumberFormat("en-US",{minimumFractionDigits:2,maximumFractionDigits:2}).format(e||0)}}document.addEventListener("DOMContentLoaded",()=>{new a});window.DirectPrintManager=a;
