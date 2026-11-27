<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #222;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #384230;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .seller-title {
            font-size: 18px;
            font-weight: bold;
            color: #384230;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .invoice-title {
            font-size: 14px;
            font-weight: bold;
            color: #111;
            margin: 0;
            text-align: right;
        }
        .badge {
            background-color: #74826B;
            color: #fff;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 3px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-cell {
            width: 50%;
            vertical-align: top;
            padding: 8px;
            background-color: #fcfcfc;
            border: 1px solid #e5e5e5;
        }
        .section-header {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 4px;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f4f5f3;
            color: #384230;
            font-size: 9px;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .items-table td {
            padding: 6px 8px;
            border: 1px solid #eee;
            font-size: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-table {
            width: 100%;
            margin-top: 10px;
        }
        .summary-box {
            width: 45%;
            margin-left: auto;
            border: 1px solid #ddd;
            background-color: #fdfdfd;
        }
        .summary-box td {
            padding: 4px 10px;
            font-size: 10px;
        }
        .grand-total-row {
            background-color: #384230;
            color: #fff;
            font-size: 12px;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                @php
                    $sellerAddress = $invoice->seller_address ?: "Karunya,\nNo. 52/1307, St. James Road,\nThykoodam, Kochi,\nErnakulam, Kerala.\nKochi 682019, Kerala, India.";
                    $sellerPhone = $invoice->seller_phone ?: '+91 8129 4620 45';
                    $sellerEmail = $invoice->seller_email ?: 'info@menahem.co.in';
                @endphp
                <span class="badge">ORIGINAL TAX INVOICE (GST)</span>
                <h1 class="seller-title" style="margin-top: 6px;">{{ $invoice->seller_name ?: 'MENAHEM' }}</h1>
                <p style="margin: 3px 0; color: #444; max-width: 320px;">
                    {!! nl2br(e($sellerAddress)) !!}
                </p>
                <p style="margin: 2px 0;">
                    <strong>GSTIN:</strong> {{ $invoice->seller_gstin ?: '32DDLPP5032G1ZN' }} &nbsp;|&nbsp; <strong>State:</strong> {{ $invoice->seller_state ?: 'Kerala' }} ({{ $invoice->seller_state_code ?: '32' }})
                </p>
                <p style="margin: 2px 0; color: #555;">
                    Phone: {{ $sellerPhone }} &nbsp;|&nbsp; Email: {{ $sellerEmail }}
                </p>
            </td>
            <td style="width: 40%; vertical-align: top;" class="text-right">
                <h2 class="invoice-title">INVOICE: {{ $invoice->invoice_number }}</h2>
                <p style="margin: 3px 0;"><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
                <p style="margin: 3px 0;"><strong>Order ID:</strong> {{ $order ? $order->order_number : 'N/A' }}</p>
                <p style="margin: 3px 0;"><strong>Payment Ref:</strong> {{ $invoice->payment_reference }}</p>
                <p style="margin: 3px 0;"><strong>Payment Status:</strong> <span style="color: #2e7d32; font-weight: bold;">PAID</span></p>
                <p style="margin: 3px 0;">
                    <strong>Payment Mode:</strong> {{ $invoice->payment_method }}
                    @if($order && $order->isSimulatedPayment())
                        <br><span style="font-size: 8px; color: #b45309; font-weight: bold;">[SIMULATED TEST PAYMENT]</span>
                    @endif
                </p>
            </td>
        </tr>
    </table>

    <!-- Buyer & Consignee Details -->
    <table class="info-table">
        <tr>
            <td class="info-cell" style="border-right: none;">
                <div class="section-header">Billed To (Customer Details)</div>
                <strong style="font-size: 12px; color: #111;">{{ $invoice->customer_name }}</strong><br>
                Phone: {{ $invoice->customer_phone }}<br>
                Email: {{ $invoice->customer_email }}<br>
                @php $b = $invoice->billing_address ?? []; @endphp
                Address: {{ $b['address_line1'] ?? '' }} {{ $b['address_line2'] ?? '' }}<br>
                City/State: {{ $b['city'] ?? '' }}, {{ $b['state'] ?? '' }} - {{ $b['pin_code'] ?? '' }}<br>
                @if($invoice->customer_gstin)
                    <strong style="color: #384230;">Customer GSTIN: {{ $invoice->customer_gstin }}</strong>
                @endif
            </td>
            <td class="info-cell">
                <div class="section-header">Shipped To (Delivery Destination)</div>
                @php $s = $invoice->shipping_address ?? []; @endphp
                <strong style="font-size: 12px; color: #111;">{{ $s['full_name'] ?? $invoice->customer_name }}</strong><br>
                Phone: {{ $s['phone'] ?? $invoice->customer_phone }}<br>
                Address: {{ $s['address_line1'] ?? '' }} {{ $s['address_line2'] ?? '' }}<br>
                City/State: {{ $s['city'] ?? '' }}, {{ $s['state'] ?? '' }} - {{ $s['pin_code'] ?? '' }}<br>
                Country: India
            </td>
        </tr>
    </table>

    <!-- Itemized Breakdown Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 31%;">Product Description</th>
                <th style="width: 9%;" class="text-center">HSN</th>
                <th style="width: 7%;" class="text-center">Qty</th>
                <th style="width: 12%;" class="text-right">Rate (Incl. GST)</th>
                <th style="width: 11%;" class="text-right">Taxable</th>
                <th style="width: 13%;" class="text-center">GST Breakup</th>
                <th style="width: 12%;" class="text-right">Total (Incl. GST)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $idx => $item)
                @php
                    $itemTotalTax = (float) $item->cgst_amount + (float) $item->sgst_amount + (float) $item->igst_amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->custom_size)
                            <div style="font-size: 8px; color: #555;">Custom: {{ $item->custom_size }}</div>
                        @endif
                        @if($item->sku)
                            <div style="font-size: 8px; color: #888;">SKU: {{ $item->sku }}</div>
                        @endif
                        @if((float)$item->discount > 0)
                            <div style="font-size: 8px; color: #2e7d32;">Discount: -&#8377;{{ number_format($item->discount, 2) }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->hsn_code ?: '6304' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">&#8377;{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">&#8377;{{ number_format($item->taxable_value, 2) }}</td>
                    <td class="text-center">
                        {{ (float)$item->gst_rate }}%
                        <div style="font-size: 8px; color: #555;">
                            @if((float)$item->igst_amount > 0)
                                IGST: &#8377;{{ number_format($item->igst_amount, 2) }}
                            @else
                                C: &#8377;{{ number_format($item->cgst_amount, 2) }} | S: &#8377;{{ number_format($item->sgst_amount, 2) }}
                            @endif
                        </div>
                    </td>
                    <td class="text-right"><strong>&#8377;{{ number_format($item->total_amount, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Box -->
    <table class="summary-table">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                <p style="font-size: 9px; color: #555; margin-top: 0;">
                    <strong>Declaration:</strong><br>
                    All product prices shown are inclusive of applicable GST.
                    We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
                </p>
                <p style="font-size: 9px; color: #555;">
                    <strong>State Treatment:</strong> 
                    {{ $invoice->igst_amount > 0 ? 'Inter-state Transaction (IGST Applicable)' : 'Intra-state Transaction (CGST 2.5% + SGST 2.5% Included)' }}
                </p>
                @if($order && is_array($order->payment_details) && count($order->payment_details) > 1)
                    <p style="font-size: 9px; color: #333; margin-top: 6px;">
                        <strong>Split Payment Breakdown:</strong><br>
                        @foreach($order->payment_details as $pSplit)
                            {{ $pSplit['method'] ?? 'Payment' }}: &#8377;{{ number_format((float) ($pSplit['amount'] ?? 0), 2) }}@if(!$loop->last) &nbsp;|&nbsp; @endif
                        @endforeach
                    </p>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table class="summary-box" style="width: 100%;">
                    <tr>
                        <td>Product Subtotal (Incl. GST):</td>
                        <td class="text-right">&#8377;{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discount > 0)
                        <tr style="color: #2e7d32;">
                            <td>Discount:</td>
                            <td class="text-right">-&#8377;{{ number_format($invoice->discount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td><strong>Taxable Value (Excl. GST):</strong></td>
                        <td class="text-right"><strong>&#8377;{{ number_format($invoice->taxable_amount, 2) }}</strong></td>
                    </tr>
                    @if($invoice->cgst_amount > 0)
                        <tr>
                            <td>CGST (2.5%):</td>
                            <td class="text-right">&#8377;{{ number_format($invoice->cgst_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST (2.5%):</td>
                            <td class="text-right">&#8377;{{ number_format($invoice->sgst_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($invoice->igst_amount > 0)
                        <tr>
                            <td>IGST (Integrated Tax):</td>
                            <td class="text-right">&#8377;{{ number_format($invoice->igst_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Total GST (Included):</td>
                        <td class="text-right">&#8377;{{ number_format((float)$invoice->cgst_amount + (float)$invoice->sgst_amount + (float)$invoice->igst_amount, 2) }}</td>
                    </tr>
                    @if((float)$invoice->shipping_charges > 0)
                        <tr>
                            <td>Shipping &amp; Delivery:</td>
                            <td class="text-right">&#8377;{{ number_format($invoice->shipping_charges, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$invoice->gateway_charges > 0)
                        <tr>
                            <td>Convenience / Gateway Fee:</td>
                            <td class="text-right">&#8377;{{ number_format($invoice->gateway_charges, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total-row">
                        <td style="padding: 6px 10px;">Grand Total:</td>
                        <td style="padding: 6px 10px;" class="text-right">&#8377;{{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Amount Paid:</td>
                        <td class="text-right">&#8377;{{ number_format($invoice->amount_paid, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        This is a computer-generated tax invoice. No physical signature is required.<br>
        Thank you for choosing Menahem — The one that comforts you.
    </div>

</body>
</html>
