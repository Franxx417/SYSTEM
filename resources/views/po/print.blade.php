<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO {{ $po->purchase_order_no }} - {{ $companyName ?? 'Procurement System' }}</title>
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
            font-family: {{ isset($branding['font_family']) ? $branding['font_family'] : 'Calibri' }}, 'Calibri Body', Arial, Helvetica, sans-serif; 
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
        /* Force inner borders for nested tables */
        .grid td table td { border-bottom: 1px solid #000; border-right: 1px solid #000; }
        .grid td table td:last-child { border-right: none; }
        .items { border: 1px solid #000; margin-top: 8px; }
        .items td, .items th { border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 6px 8px; }
        .items td:last-child, .items th:last-child { border-right: none; }
        .items tr:last-child td { border-bottom: none; }
        .totals { border: 1px solid #000; width: 200px; margin-left: auto; margin-top: 8px; }
        .totals td { border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 5px 8px; }
        .totals td:last-child { border-right: none; }
        .totals tr:last-child td { border-bottom: none; }
        .no-border td, .no-border th { border:0 !important; }
        .grid td { height:24px; }
        .right { text-align:right; }
        .center { text-align:center; }
        .small { font-size:9pt; color:#000; }
        .meta { width:260px; margin-top:8px; }
        .signatures { 
            width: 75%; 
            margin: 40px auto 0 auto;
        }
        .sig-line { 
            border-top: 1px solid #000; 
            height: 24px; 
        }
        .items thead th { font-weight:600; }
        .footer-code { 
            position: absolute; 
            right: 0;
            bottom: 15px; 
            font-size: 9pt; 
            color:#333; 
        }
        .company-logo {
            max-height: 80px;
            max-width: 300px;
            height: auto;
            width: auto;
            display: block;
            margin: 0 auto 20px auto;
        }
        @media print { 
            .no-print { display:none; }
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
                min-height: calc(297mm - 1in); /* Full page minus margins */
                display: flex;
                flex-direction: column;
            }
            .content-area {
                flex: 1;
            }
            .signatures {
                position: fixed;
                bottom: 1.5in; /* Above footer code */
                left: 50%;
                transform: translateX(-50%);
                width: 70%;
                background: white;
                z-index: 10;
            }
            table { margin-bottom: 12px; }
            .grid { margin-bottom: 12px; }
            .items { margin-top: 12px; }
            .footer-code {
                position: fixed;
                right: 0.5in;
                bottom: 0.5in;
                font-size: 9pt;
                color: #333;
                z-index: 11;
             }
             .company-logo {
                max-height: 60px;
                max-width: 250px;
                height: auto;
                width: auto;
                display: block;
                margin: 0 auto 15px auto;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
             }
        }
        @media screen {
            body {
                background: #f0f0f0;
                padding: 20px;
            }
            .container {
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                min-height: 277mm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content-area">
            <div class="no-print" style="margin:10px 0; text-align:right">
                <button onclick="window.print()">Print</button>
            </div>
            
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="Company Logo" class="company-logo">
            @endif
            
            <div class="title">LOCAL PURCHASE ORDER</div>

        <!-- Header grid: 4 columns with proper borders -->
        <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Requestor's Name:</strong></td>
                <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $auth['name'] ?? '' }}</td>
                <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Date Requested:</strong></td>
                <td style="width:20%; padding:4px 6px; vertical-align:middle;">{{ \Carbon\Carbon::parse($po->date_requested)->format('m-d-Y') }}</td>
            </tr>
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Position:</strong></td>
                <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $auth['position'] }}</td>
                <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Purchase Order No.</strong></td>
                <td style="width:20%; padding:4px 6px; vertical-align:middle;">{{ $po->purchase_order_no }}</td>
            </tr>
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Department:</strong></td>
                <td style="width:35%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $auth['department'] }}</td>
                <td style="width:20%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Official Receipt No.</strong></td>
                <td style="width:20%; padding:4px 6px; vertical-align:middle;">{{ $po->official_receipt_no ?? '' }}</td>
            </tr>
        </table>
        
        <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
            <tr>
                <td style="width:18%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Purpose/Use:</strong></td>
                <td style="padding:4px 6px;">{{ $po->purpose }}</td>
            </tr>
        </table>

        <table class="grid" style="margin-bottom:10px; width:100%; border-collapse: collapse;">
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Supplier/ Vendor Name:</strong></td>
                <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $po->supplier_name }}</td>
                <td style="width:25%; padding:4px 6px; vertical-align:middle;"><strong>TIN No.</strong> {{ $po->tin_no ?? '' }}</td>
            </tr>
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Supplier/ Vendor Address:</strong></td>
                <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $po->supplier_address ?? '' }}</td>
                <td style="width:25%; padding:4px 6px; vertical-align:middle;">
                    <strong>VAT</strong> @if($po->vat_type == 'VAT') ✓ @else ___ @endif &nbsp;&nbsp; 
                    <strong>Non-VAT</strong> @if($po->vat_type == 'Non_VAT' || $po->vat_type == 'Non-VAT') ✓ @else ___ @endif
                </td>
            </tr>
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Contact Person:</strong></td>
                <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $po->contact_person }}</td>
                <td style="width:25%; padding:4px 6px; vertical-align:middle;"></td>
            </tr>
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Contact Number:</strong></td>
                <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $po->contact_number }}</td>
                <td style="width:25%; padding:4px 6px; vertical-align:middle;"></td>
            </tr>
            <tr>
                <td style="width:25%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;"><strong>Delivery Date:</strong></td>
                <td style="width:50%; padding:4px 6px; vertical-align:middle; border-right:1px solid #000;">{{ $po->delivery_date ? date('Y-m-d', strtotime($po->delivery_date)) : '' }}</td>
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
                @foreach($items as $idx => $it)
                <tr>
                    <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:center;">{{ $idx+1 }}</td>
                    <td style="padding:6px; border-right:1px solid #000; vertical-align:middle;">{{ $it->item_name ?? '' }}</td>
                    <td style="padding:6px; border-right:1px solid #000; vertical-align:middle;">{{ $it->item_description }}</td>
                    <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:center;">{{ number_format($it->quantity) }}</td>
                    <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:right;">Php {{ number_format($it->unit_price,2) }}</td>
                    <td style="padding:6px; vertical-align:middle; text-align:right;">Php {{ number_format($it->total_cost,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @php
            // Align with saved DB values: totals are stored on purchase_orders
            $shipping = (float)($po->shipping_fee ?? 0);
            $discount = (float)($po->discount ?? 0);
            $sub = (float)($po->subtotal ?? 0);
            
            // Calculate VAT based on supplier's VAT type
            $vatableAmount = $sub;
            $vat = 0;
            
            if ($po->vat_type == 'VAT') {
                // For VAT suppliers, calculate 12% VAT
                $vat = round($vatableAmount * 0.12, 2);
            }
            
            $grand = (float)($po->total ?? ($sub + $vat));
        @endphp
        
        <!-- Totals section -->
        <div style="margin-top:15px; display:flex; justify-content:flex-end;">
            <table class="totals" style="width:300px; border-collapse: collapse;">
                <tr>
                    <td style="width:60%; padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:right;"><strong>Discount:</strong></td>
                    <td style="width:40%; padding:6px; border-bottom:1px solid #000; vertical-align:middle; text-align:right;">{{ number_format($discount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px; border-right:1px solid #000; border-bottom:1px solid #000; vertical-align:middle; text-align:right;"><strong>Delivery fee:</strong></td>
                    <td style="padding:6px; border-bottom:1px solid #000; vertical-align:middle; text-align:right;">{{ number_format($shipping, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px; border-right:1px solid #000; vertical-align:middle; text-align:right;"><strong>TOTAL:</strong></td>
                    <td style="padding:6px; vertical-align:middle; text-align:right; font-weight:bold;">{{ number_format($grand, 2) }}</td>
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


