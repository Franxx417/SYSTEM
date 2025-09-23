<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO {{ $po->purchase_order_no }}</title>
    <style>
        @page { size: A4; margin: 48px; }
        body { font-family: 'Calibri Body', Calibri, Arial, Helvetica, sans-serif; color:#111; }
        .container { width: 100%; }
        .header { display:flex; justify-content:space-between; align-items:flex-start; }
        .brand { font-weight:800; letter-spacing:.5px; line-height:1.05; font-size:20px; text-align:center; }
        .title { text-align:center; font-weight:700; margin:16px 0 14px; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:6px 8px; font-size:12px; }
        .grid { border: 1px solid #555; }
        .grid td, .grid th { border-right: 1px solid #555; border-bottom: 1px solid #555; }
        .grid td:last-child, .grid th:last-child { border-right: none; }
        .grid tr:last-child td { border-bottom: none; }
        /* Force inner borders for nested tables */
        .grid td table td { border-bottom: 1px solid #555; border-right: 1px solid #555; }
        .grid td table td:last-child { border-right: none; }
        .items { border: 1px solid #555; }
        .items td, .items th { border-right: 1px solid #555; border-bottom: 1px solid #555; }
        .items td:last-child, .items th:last-child { border-right: none; }
        .items tr:last-child td { border-bottom: none; }
        .totals { border: 1px solid #555; }
        .totals td { border-right: 1px solid #555; border-bottom: 1px solid #555; }
        .totals td:last-child { border-right: none; }
        .totals tr:last-child td { border-bottom: none; }
        .no-border td, .no-border th { border:0 !important; }
        .grid td { height:26px; }
        .right { text-align:right; }
        .small { font-size:11px; color:#444; }
        .meta { width:260px; margin-top:8px; }
        .signatures { width:100%; margin-top:28px;}
        .sig-box { height:54px; }
        .sig-line { border-top:1px solid #555; height:22px; }
        .items thead th { background:#fafafa; font-weight:600; }
        .totals { width:300px; margin-left:auto; margin-top:10px; }
        .footer-code { position: fixed; left: 148mm; bottom: 0mm; font-size:11px; color:#333; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="margin:10px 0; text-align:right">
            <button onclick="window.print()">Print</button>
        </div>
        @php
            $companyLogo = \App\Models\Setting::getCompanyLogo();
        @endphp
        <div class="brand" style="text-align:center; margin-bottom: 10px;">
            @if($companyLogo)
                <img src="{{ $companyLogo }}" alt="Company Logo" style="max-height: 80px; max-width: 250px; object-fit: contain;">
            @endif
        </div>
        <div class="title">LOCAL PURCHASE ORDER</div>

        <!-- Header grid: 4 columns with proper borders -->
        <table class="small grid" style="margin-bottom:6px">
            <tr>
                <td style="width:26%; font-weight:bold;"><strong>Requestor's Name:</strong></td>
                <td style="width:39%;">{{ $auth['name'] ?? '' }}</td>
                <td style="width:20%; font-weight:bold;"><strong>Date Requested:</strong></td>
                <td style="width:15%; text-align:right;">{{ \Carbon\Carbon::parse($po->date_requested)->format('m-d-Y') }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;"><strong>Position:</strong></td>
                <td>{{ $auth['position'] }}</td>
                <td style="font-weight:bold;"><strong>Purchase Order No.</strong></td>
                <td style="text-align:right;">{{ $po->purchase_order_no }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold;"><strong>Department:</strong></td>
                <td>{{ $auth['department'] }}</td>
                <td style="font-weight:bold;"><strong>Official Receipt No.</strong></td>
                <td style="text-align:right;">{{ $po->official_receipt_no ?? '' }}</td>
            </tr>
        </table>
        <table class="small grid">
        <tr>
                <td colspan="2" style="padding:0">
                    <table class="small" style="width:100%">
                        <tr>
                            <td style="width:14%"><strong>Purpose/Use</strong>:</td>
                            <td>{{ $po->purpose }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="small grid" style="margin-top:8px">
            <tr>
                <td style="width:25%; font-weight:bold; background:#f5f5f5;">Supplier/ Vendor Name:</td>
                <td style="width:45%;">{{ $po->supplier_name }}</td>
                <td style="width:30%; font-weight:bold; background:#f5f5f5; vertical-align:top;">TIN No.: {{ $po->tin_no ?? '' }}</td>
            </tr>
            <tr>
                <td style="font-weight:bold; background:#f5f5f5;">Supplier/ Vendor Address:</td>
                <td>{{ $po->supplier_address ?? '' }}</td>
                <td style="font-weight:bold; background:#f5f5f5; vertical-align:top;">
                    @if($po->vat_type == 'VAT')
                        VAT <u>&#x2713;</u> &nbsp;&nbsp; Non-VAT ___
                    @elseif($po->vat_type == 'Non_VAT' || $po->vat_type == 'Non-VAT')
                        VAT ___ &nbsp;&nbsp; Non-VAT <u>&#x2713;</u>
                    @else
                        VAT ___ &nbsp;&nbsp; Non-VAT ___
                    @endif
                </td>
            </tr>
            @if($po->contact_person)
            <tr>
                <td style="font-weight:bold; background:#f5f5f5;">Contact Person:</td>
                <td>{{ $po->contact_person }}</td>
                <td></td>
            </tr>
            @endif
            @if($po->contact_number)
            <tr>
                <td style="font-weight:bold; background:#f5f5f5;">Contact Number:</td>
                <td>{{ $po->contact_number }}</td>
                <td></td>
            </tr>
            @endif
            @if($po->delivery_date)
            <tr>
                <td style="font-weight:bold; background:#f5f5f5;">Delivery Date:</td>
                <td>{{ $po->delivery_date }}</td>
                <td></td>
            </tr>
            @endif
        </table>

        <table class="items" style="margin-top:10px">
            <thead>
                <tr>
                    <th style="width:60px">Item No.</th>
                    <th style="width:120px">Item</th>
                    <th>Item Description</th>
                    <th style="width:80px" class="center">Quantity</th>
                    <th style="width:110px" class="center">Unit Price</th>
                    <th style="width:130px" class="center">Total Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $it)
                    <tr>
                        <td class="center">{{ $idx+1 }}</td>
                        <td>{{ $it->item_name ?? '' }}</td>
                        <td>{{ $it->item_description }}</td>
                        <td class="center">{{ number_format($it->quantity) }}</td>
                        <td class="center">Php {{ number_format($it->unit_price,2) }}</td>
                        <td class="center">Php {{ number_format($it->total_cost,2) }}</td>
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
        <table class="totals small">
            <tr><td>Shipping</td><td class="right">Php {{ number_format($shipping,2) }}</td></tr>
            <tr><td>Discount</td><td class="right">Php {{ number_format($discount,2) }}</td></tr>
            <tr><td><strong>VaTable Sales (Ex Vat)</strong></td><td class="right"><strong>Php {{ number_format($vatableAmount,2) }}</strong></td></tr>
            <tr><td>12% Vat</td><td class="right">Php {{ number_format($vat,2) }}</td></tr>
            <tr><td><strong>TOTAL:</strong></td><td class="right"><strong>Php {{ number_format($grand,2) }}</strong></td></tr>
        </table>
        <table class="signatures no-border small">
            <tr>
                <td style="width:50%">
                    <div>Prepared by:</div>
                    <div class="sig-box"></div>
                    <div class="sig-line"></div>
                    <div>(Sign over printed Name)<br/>{{ $auth['name'] ?? 'Requestor\'s Name' }}</div>
                </td>
                <td style="width:50%">
                    <div>Approved by:</div>
                    <div class="sig-box"></div>
                    <div class="sig-line"></div>
                    <div>(Sign over printed Name)<br/>Finance Controller</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div>Verified by:</div>
                    <div class="sig-box"></div>
                    <div class="sig-line"></div>
                    <div>(Sign over printed Name)<br/>Department Head</div>
                </td>
                <td>
                    <div>Received by:</div>
                    <div class="sig-box"></div>
                    <div class="sig-line"></div>
                    <div>(Sign over printed Name)<br/>{{ $auth['name'] ?? 'Requestor / Authorized Personnel' }}</div>
                </td>
            </tr>
        </table>

        <div class="footer-code">{{ config('app.document_code', 'GAS-FIN-LPO-PH-003 Rev. 02') }}</div>
    </div>
</body>
</html>


