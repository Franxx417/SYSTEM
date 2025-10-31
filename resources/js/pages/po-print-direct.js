/**
 * Direct Print Functionality for Purchase Orders
 * Bypasses print preview and directly triggers browser print dialog
 */

class DirectPrintManager {
    constructor() {
        this.printWindow = null;
        this.printData = null;
        this.fallbackUrl = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.setupErrorHandling();
    }

    bindEvents() {
        // Bind to existing print buttons in PO index
        document.addEventListener('click', (e) => {
            if (e.target.closest('.btn-print-direct')) {
                e.preventDefault();
                e.stopPropagation();
                
                const button = e.target.closest('.btn-print-direct');
                const poNumber = button.getAttribute('data-po-number');
                const fallbackUrl = button.getAttribute('data-fallback-url');
                
                this.directPrint(poNumber, fallbackUrl);
            }
        });

        // Handle keyboard accessibility
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                const target = e.target.closest('.btn-print-direct');
                if (target) {
                    e.preventDefault();
                    target.click();
                }
            }
        });
    }

    setupErrorHandling() {
        window.addEventListener('error', (e) => {
            if (e.message.includes('print') || e.message.includes('Print')) {
                console.warn('Print error detected, falling back to preview method');
                this.fallbackToPrintPreview();
            }
        });
    }

    async directPrint(poNumber, fallbackUrl) {
        try {
            this.fallbackUrl = fallbackUrl;
            
            // Show loading indicator
            this.showLoadingIndicator();
            
            // Fetch PO data for direct printing
            const poData = await this.fetchPOData(poNumber);
            
            if (!poData) {
                throw new Error('Failed to fetch purchase order data');
            }

            // Create print-optimized content
            const printContent = this.createPrintContent(poData);
            
            // Attempt direct print
            const success = await this.executePrint(printContent);
            
            if (!success) {
                throw new Error('Direct print not supported');
            }

        } catch (error) {
            console.error('Direct print failed:', error);
            this.handlePrintError(error);
        } finally {
            this.hideLoadingIndicator();
        }
    }

    async fetchPOData(poNumber) {
        try {
            const response = await fetch(`/po/${poNumber}/json`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Failed to fetch PO data:', error);
            return null;
        }
    }

    createPrintContent(poData) {
        const { po, items, auth, companyLogo } = poData;
        
        // Calculate totals
        const shipping = parseFloat(po.shipping_fee || 0);
        const discount = parseFloat(po.discount || 0);
        const subtotal = parseFloat(po.subtotal || 0);
        const vat = po.vat_type === 'VAT' ? Math.round(subtotal * 0.12 * 100) / 100 : 0;
        const total = parseFloat(po.total || (subtotal + vat));

        return `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO ${po.purchase_order_no}</title>
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
            ${companyLogo ? `<img src="${companyLogo}" alt="Company Logo" class="company-logo">` : ''}
            
            <div class="title">LOCAL PURCHASE ORDER</div>

            <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Requestor's Name:</strong></td>
                    <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${auth?.name || ''}</td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Date Requested:</strong></td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle;">${this.formatDate(po.date_requested)}</td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Position:</strong></td>
                    <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${auth?.position || ''}</td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Purchase Order No.</strong></td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle;">${po.purchase_order_no}</td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Department:</strong></td>
                    <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${auth?.department || ''}</td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Official Receipt No.</strong></td>
                    <td style="width:20%; padding:4px 6px; vertical-align:middle;">${po.official_receipt_no || ''}</td>
                </tr>
            </table>
            
            <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:18%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Purpose/Use:</strong></td>
                    <td style="padding:4px 6px;">${po.purpose}</td>
                </tr>
            </table>

            <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Supplier/ Vendor Name:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${po.supplier_name || ''}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;"><strong>TIN No.</strong> ${po.tin_no || ''}</td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Supplier/ Vendor Address:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${po.supplier_address || ''}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;">
                        <strong>VAT</strong> ${po.vat_type === 'VAT' ? '✓' : '___'} &nbsp;&nbsp; 
                        <strong>Non-VAT</strong> ${po.vat_type === 'Non_VAT' || po.vat_type === 'Non-VAT' ? '✓' : '___'}
                    </td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Contact Person:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${po.contact_person || ''}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;"></td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Contact Number:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${po.contact_number || ''}</td>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle;"></td>
                </tr>
                <tr>
                    <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Delivery Date:</strong></td>
                    <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">${this.formatDate(po.delivery_date)}</td>
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
                    ${items.map((item, idx) => `
                    <tr>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:center;">${idx + 1}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle;">${item.item_name || ''}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle;">${item.item_description}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:center;">${this.formatNumber(item.quantity)}</td>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:right;">Php ${this.formatCurrency(item.unit_price)}</td>
                        <td style="padding:6px; vertical-align:middle; text-align:right;">Php ${this.formatCurrency(item.total_cost)}</td>
                    </tr>
                    `).join('')}
                </tbody>
            </table>

            <div style="margin-top:15px; display:flex; justify-content:flex-end;">
                <table class="totals" style="width:300px; border-collapse: collapse;">
                    <tr>
                        <td style="width:60%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:right;"><strong>Discount:</strong></td>
                        <td style="width:40%; padding:6px; border-bottom:1px solid #000; vertical-align:middle; text-align:right;">${this.formatCurrency(discount)}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:right;"><strong>Delivery fee:</strong></td>
                        <td style="padding:6px; border-bottom:1px solid #000; vertical-align:middle; text-align:right;">${this.formatCurrency(shipping)}</td>
                    </tr>
                    <tr>
                        <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:right;"><strong>TOTAL:</strong></td>
                        <td style="padding:6px; vertical-align:middle; text-align:right; font-weight:bold;">${this.formatCurrency(total)}</td>
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
        `;
    }

    async executePrint(content) {
        return new Promise((resolve) => {
            try {
                // Check if browser supports direct printing
                if (!window.print) {
                    resolve(false);
                    return;
                }

                // Create hidden iframe for printing
                const iframe = document.createElement('iframe');
                iframe.style.position = 'absolute';
                iframe.style.left = '-9999px';
                iframe.style.top = '-9999px';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = 'none';
                
                document.body.appendChild(iframe);

                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                iframeDoc.open();
                iframeDoc.write(content);
                iframeDoc.close();

                // Wait for content to load
                iframe.onload = () => {
                    try {
                        // Focus the iframe and trigger print
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                        
                        // Clean up after a delay
                        setTimeout(() => {
                            document.body.removeChild(iframe);
                        }, 1000);
                        
                        resolve(true);
                    } catch (error) {
                        console.error('Print execution failed:', error);
                        document.body.removeChild(iframe);
                        resolve(false);
                    }
                };

                // Fallback if onload doesn't fire
                setTimeout(() => {
                    if (iframe.parentNode) {
                        try {
                            iframe.contentWindow.focus();
                            iframe.contentWindow.print();
                            setTimeout(() => {
                                if (iframe.parentNode) {
                                    document.body.removeChild(iframe);
                                }
                            }, 1000);
                            resolve(true);
                        } catch (error) {
                            if (iframe.parentNode) {
                                document.body.removeChild(iframe);
                            }
                            resolve(false);
                        }
                    }
                }, 500);

            } catch (error) {
                console.error('Print setup failed:', error);
                resolve(false);
            }
        });
    }

    handlePrintError(error) {
        console.error('Print error:', error);
        
        // Show user-friendly error message
        this.showNotification('Direct printing failed. Opening print preview...', 'warning');
        
        // Fallback to original print preview
        setTimeout(() => {
            this.fallbackToPrintPreview();
        }, 1000);
    }

    fallbackToPrintPreview() {
        if (this.fallbackUrl) {
            // Open print preview in new tab
            const printWindow = window.open(this.fallbackUrl, '_blank');
            if (!printWindow) {
                this.showNotification('Please allow popups to print this document', 'error');
            }
        } else {
            this.showNotification('Print functionality is not available', 'error');
        }
    }

    showLoadingIndicator() {
        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.id = 'print-loading-overlay';
        overlay.innerHTML = `
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
        `;
        document.body.appendChild(overlay);
    }

    hideLoadingIndicator() {
        const overlay = document.getElementById('print-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    showNotification(message, type = 'info') {
        // Create notification
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '10000';
        notification.style.minWidth = '300px';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Utility methods
    formatDate(dateString) {
        if (!dateString) return '';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                month: '2-digit',
                day: '2-digit', 
                year: 'numeric'
            });
        } catch {
            return dateString;
        }
    }

    formatNumber(num) {
        return new Intl.NumberFormat().format(num || 0);
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount || 0);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new DirectPrintManager();
});

// Export for manual initialization if needed
window.DirectPrintManager = DirectPrintManager;
