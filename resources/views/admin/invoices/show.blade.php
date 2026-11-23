@extends('admin.layouts.app')

@section('title', "Invoice: {$invoice->invoice_number}")
@section('header_title', "Invoice Details: {$invoice->invoice_number}")

@section('header_actions')
    <a href="{{ route('admin.invoices.download', $invoice) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span>Download PDF</span>
    </a>
    <a href="{{ route('admin.invoices.index') }}" 
       class="px-3.5 py-2 rounded-none border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        Back to Invoices
    </a>
@endsection

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-none border border-neutral-200 shadow-none p-6 sm:p-10 text-neutral-800">
    <!-- Invoice Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start pb-6 border-b border-neutral-200">
        <div>
            <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-none bg-neutral-100 text-neutral-900 border border-neutral-300">
                TAX INVOICE (GST)
            </span>
            <h2 class="text-2xl font-bold text-neutral-900 mt-2">{{ $invoice->seller_name }}</h2>
            <p class="text-xs text-neutral-600 mt-1 max-w-sm whitespace-pre-line">{{ $invoice->seller_address }}</p>
            <p class="text-xs text-neutral-700 font-semibold mt-1">
                GSTIN: <span class="font-mono text-neutral-900">{{ $invoice->seller_gstin }}</span> • State: {{ $invoice->seller_state }} (Code: {{ $invoice->seller_state_code }})
            </p>
        </div>
        <div class="mt-4 sm:mt-0 text-left sm:text-right">
            <h3 class="text-lg font-mono font-bold text-neutral-900">{{ $invoice->invoice_number }}</h3>
            <p class="text-xs text-neutral-500 mt-0.5">Date: {{ $invoice->invoice_date->format('d M Y') }}</p>
            <p class="text-xs text-neutral-500 mt-0.5">Order ID: <span class="font-mono font-semibold text-neutral-800">{{ $order->order_number }}</span></p>
            <p class="text-xs text-neutral-500 mt-0.5">Payment Ref: <span class="font-mono">{{ $invoice->payment_reference }}</span></p>
            <span class="inline-block mt-2 px-2.5 py-0.5 text-xs font-bold rounded-none bg-neutral-100 text-neutral-900 border border-neutral-300">
                Payment Status: PAID
            </span>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-neutral-200 text-xs">
        <div>
            <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-2">Billed To</h4>
            <p class="text-sm font-bold text-neutral-900">{{ $invoice->customer_name }}</p>
            <p class="text-neutral-600 mt-0.5">{{ $invoice->customer_phone }} • {{ $invoice->customer_email }}</p>
            @php $b = $invoice->billing_address ?? []; @endphp
            <p class="text-neutral-600 mt-1">
                {{ $b['address_line1'] ?? '' }} {{ $b['address_line2'] ?? '' }}<br>
                {{ $b['city'] ?? '' }}, {{ $b['state'] ?? '' }} - {{ $b['pin_code'] ?? '' }}
            </p>
            @if($invoice->customer_gstin)
                <p class="mt-1 font-semibold text-neutral-900">Customer GSTIN: {{ $invoice->customer_gstin }}</p>
            @endif
        </div>
        <div>
            <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-2">Shipped To</h4>
            @php $s = $invoice->shipping_address ?? []; @endphp
            <p class="text-sm font-bold text-neutral-900">{{ $s['full_name'] ?? $invoice->customer_name }}</p>
            <p class="text-neutral-600 mt-0.5">Phone: {{ $s['phone'] ?? $invoice->customer_phone }}</p>
            <p class="text-neutral-600 mt-1">
                {{ $s['address_line1'] ?? '' }} {{ $s['address_line2'] ?? '' }}<br>
                {{ $s['city'] ?? '' }}, {{ $s['state'] ?? '' }} - {{ $s['pin_code'] ?? '' }}
            </p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="py-6 overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-neutral-100 text-neutral-700 uppercase font-semibold border-b border-neutral-200">
                <tr>
                    <th class="py-2.5 px-3">Item Description</th>
                    <th class="py-2.5 px-2">HSN</th>
                    <th class="py-2.5 px-2 text-center">Qty</th>
                    <th class="py-2.5 px-2 text-right">Unit Price</th>
                    <th class="py-2.5 px-2 text-right">Taxable</th>
                    <th class="py-2.5 px-2 text-center">GST %</th>
                    <th class="py-2.5 px-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @foreach($order->items as $item)
                    <tr>
                        <td class="py-3 px-3">
                            <span class="font-bold text-neutral-900 block">{{ $item->product_name }}</span>
                            @if($item->custom_size)
                                <span class="text-[11px] text-neutral-500">Size: {{ $item->custom_size }}</span>
                            @endif
                            @if($item->sku)
                                <span class="text-[10px] text-neutral-400 font-mono block">SKU: {{ $item->sku }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-2 font-mono text-neutral-600">{{ $item->hsn_code }}</td>
                        <td class="py-3 px-2 text-center font-bold text-neutral-800">{{ $item->quantity }}</td>
                        <td class="py-3 px-2 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3 px-2 text-right font-medium">₹{{ number_format($item->taxable_value, 2) }}</td>
                        <td class="py-3 px-2 text-center font-semibold text-neutral-700">{{ (float)$item->gst_rate }}%</td>
                        <td class="py-3 px-3 text-right font-bold text-neutral-900">₹{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary & Totals -->
    <div class="pt-4 border-t border-neutral-200 flex flex-col sm:flex-row justify-between gap-6 text-xs">
        <div class="max-w-xs text-neutral-500">
            <p class="font-semibold text-neutral-700 mb-1">Payment Method: {{ $invoice->payment_method }}</p>
            <p>This is a computer-generated tax invoice issued in compliance with the Central Goods and Services Tax Act.</p>
        </div>

        <div class="w-full sm:w-72 space-y-1.5">
            <div class="flex justify-between text-neutral-600">
                <span>Subtotal:</span>
                <span class="font-semibold text-neutral-900">₹{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount > 0)
                <div class="flex justify-between text-neutral-900 font-medium">
                    <span>Coupon Discount:</span>
                    <span>-₹{{ number_format($invoice->discount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-neutral-600">
                <span>Taxable Amount:</span>
                <span class="font-semibold text-neutral-900">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
            </div>
            @if($invoice->cgst_amount > 0)
                <div class="flex justify-between text-neutral-600">
                    <span>CGST:</span>
                    <span>₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-neutral-600">
                    <span>SGST:</span>
                    <span>₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                </div>
            @endif
            @if($invoice->igst_amount > 0)
                <div class="flex justify-between text-neutral-600">
                    <span>IGST (Integrated Tax):</span>
                    <span>₹{{ number_format($invoice->igst_amount, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-neutral-600">
                <span>Shipping Charges:</span>
                <span>{{ $invoice->shipping_charges == 0 ? 'FREE' : '₹'.number_format($invoice->shipping_charges, 2) }}</span>
            </div>
            @if($invoice->gateway_charges > 0)
                <div class="flex justify-between text-neutral-600">
                    <span>Gateway Charges:</span>
                    <span>₹{{ number_format($invoice->gateway_charges, 2) }}</span>
                </div>
            @endif
            <div class="pt-2 border-t border-neutral-300 flex justify-between text-base font-bold text-neutral-900">
                <span>Grand Total:</span>
                <span>₹{{ number_format($invoice->grand_total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
