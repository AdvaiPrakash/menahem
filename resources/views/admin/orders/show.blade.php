@extends('admin.layouts.app')

@section('title', "Order #{$order->order_number}")
@section('header_title', "Order Details: #{$order->order_number}")

@section('header_actions')
    <a href="{{ route('admin.orders.invoice', $order) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span>Download Invoice</span>
    </a>
    <a href="{{ route('admin.orders.index') }}" 
       class="px-3.5 py-2 border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        Back to Orders
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Top Header Card -->
    <div class="bg-white p-6 border border-neutral-200 shadow-none">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-neutral-200">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-bold font-mono text-neutral-900">#{{ $order->order_number }}</h2>
                    <span class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider border
                        {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider border
                        {{ $order->order_status === 'delivered' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-neutral-100 text-neutral-800 border-neutral-300' }}">
                        {{ ucfirst($order->order_status) }}
                    </span>
                </div>
                <p class="text-xs text-neutral-500 mt-1">
                    Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }} • Payment Ref: <span class="font-mono">{{ $order->razorpay_payment_id ?: 'Pending' }}</span>
                </p>
            </div>

            <div class="text-right">
                <span class="text-xs text-neutral-400 block uppercase tracking-wider">Total Payable Amount</span>
                <span class="text-2xl font-bold text-neutral-900">₹{{ number_format($order->grand_total, 2) }}</span>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-6 text-xs">
            <div>
                <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-1.5">Customer Information</h4>
                <p class="text-sm font-bold text-neutral-900">{{ $order->customer_name }}</p>
                <p class="text-neutral-600 mt-0.5">{{ $order->customer_phone ?: 'No phone provided' }}</p>
                <p class="text-neutral-600">{{ $order->customer_email }}</p>
                @if($order->customer_gstin)
                    <p class="mt-1 font-semibold text-neutral-900">Customer GSTIN: {{ $order->customer_gstin }}</p>
                @endif
                <span class="inline-block mt-2 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-neutral-100 text-neutral-800 border border-neutral-300">
                    {{ ucfirst($order->customer_type) }} Customer
                </span>
            </div>

            <div>
                <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-1.5">Shipping / Delivery Address</h4>
                @php $s = $order->shipping_address ?? []; @endphp
                <p class="text-sm font-bold text-neutral-900">{{ $s['full_name'] ?? $order->customer_name }}</p>
                <p class="text-neutral-600 mt-0.5">Phone: {{ $s['phone'] ?? $order->customer_phone }}</p>
                <p class="text-neutral-600 mt-1">
                    {{ $s['address_line1'] ?? '' }} {{ $s['address_line2'] ?? '' }}<br>
                    {{ $s['city'] ?? '' }}, {{ $s['state'] ?? '' }} - {{ $s['pin_code'] ?? '' }}
                </p>
            </div>

            <div>
                <h4 class="font-bold uppercase tracking-wider text-neutral-400 mb-1.5">Billing Address</h4>
                @php $b = $order->billing_address ?? []; @endphp
                <p class="text-sm font-bold text-neutral-900">{{ $b['full_name'] ?? $order->customer_name }}</p>
                <p class="text-neutral-600 mt-1">
                    {{ $b['address_line1'] ?? '' }} {{ $b['address_line2'] ?? '' }}<br>
                    {{ $b['city'] ?? '' }}, {{ $b['state'] ?? '' }} - {{ $b['pin_code'] ?? '' }}
                </p>
                <div class="mt-3 pt-3 border-t border-neutral-200">
                    <p class="text-neutral-500">Method: <strong class="text-neutral-800">{{ $order->payment_method ?: 'Razorpay' }}</strong></p>
                    @if($order->paid_at)
                        <p class="text-neutral-500">Paid At: <strong class="text-neutral-800">{{ $order->paid_at->format('d M Y, h:i A') }}</strong></p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Status Management Card -->
    <div class="bg-white p-6 border border-neutral-200 shadow-none">
        <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-700 pb-3 border-b border-neutral-200">
            Order Status & Fulfillment
        </h3>

        <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Order Fulfillment Status
                    </label>
                    <select name="order_status" class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                        <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="confirmed" {{ $order->order_status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->order_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                        Payment Status
                    </label>
                    <select name="payment_status" class="w-full px-3.5 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                        <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">
                    Internal Admin Notes / Tracking Information
                </label>
                <textarea name="notes" rows="2" 
                          placeholder="Courier partner, tracking number, customer update notes..." 
                          class="w-full px-3.5 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">{{ old('notes', $order->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" 
                        class="px-5 py-2 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors">
                    Update Order Status
                </button>
            </div>
        </form>
    </div>

    <!-- Ordered Items Table -->
    <div class="bg-white border border-neutral-200 shadow-none overflow-hidden">
        <div class="p-4 border-b border-neutral-200 flex items-center justify-between">
            <h3 class="text-sm font-bold uppercase tracking-wider text-neutral-700">
                Ordered Products ({{ $order->items->count() }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-neutral-50 text-neutral-700 uppercase font-semibold border-b border-neutral-200">
                    <tr>
                        <th class="py-3 px-4">Item Details</th>
                        <th class="py-3 px-3">HSN</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-3 text-right">Unit Price</th>
                        <th class="py-3 px-3 text-right">Taxable</th>
                        <th class="py-3 px-3 text-center">GST %</th>
                        <th class="py-3 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ $item->product->image }}" alt="{{ $item->product_name }}" class="w-10 h-10 object-cover bg-neutral-100 border border-neutral-200 shrink-0">
                                    @endif
                                    <div>
                                        <span class="font-bold text-neutral-900 text-sm block">{{ $item->product_name }}</span>
                                        @if($item->custom_size)
                                            <span class="text-[11px] text-neutral-500">Size: {{ $item->custom_size }}</span>
                                        @endif
                                        @if($item->sku)
                                            <span class="text-[10px] text-neutral-400 font-mono block">SKU: {{ $item->sku }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 font-mono text-neutral-600">{{ $item->hsn_code }}</td>
                            <td class="py-3 px-3 text-center font-bold text-neutral-900">{{ $item->quantity }}</td>
                            <td class="py-3 px-3 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 px-3 text-right font-medium">₹{{ number_format($item->taxable_value, 2) }}</td>
                            <td class="py-3 px-3 text-center font-semibold text-neutral-700">{{ (float)$item->gst_rate }}%</td>
                            <td class="py-3 px-4 text-right font-bold text-neutral-900">₹{{ number_format($item->total_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary Breakdown -->
        <div class="p-6 bg-neutral-50 border-t border-neutral-200 flex flex-col sm:flex-row justify-between gap-6 text-xs">
            <div class="max-w-md text-neutral-500">
                @if($order->coupon_code)
                    <div class="p-2.5 bg-neutral-100 border border-neutral-300 font-mono text-xs text-neutral-900 mb-2">
                        Applied Coupon: <strong>{{ $order->coupon_code }}</strong> (Discount: ₹{{ number_format($order->discount_amount, 2) }})
                    </div>
                @endif
                <p>Tax invoice compliant with Central Goods and Services Tax Act. Associated with permanent customer lead record.</p>
            </div>

            <div class="w-full sm:w-80 space-y-1.5">
                <div class="flex justify-between text-neutral-600">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-neutral-900">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-neutral-900 font-medium">
                        <span>Coupon Discount:</span>
                        <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-neutral-600">
                    <span>Taxable Amount:</span>
                    <span class="font-semibold text-neutral-900">₹{{ number_format($order->taxable_amount, 2) }}</span>
                </div>
                @if($order->cgst_amount > 0)
                    <div class="flex justify-between text-neutral-600">
                        <span>CGST:</span>
                        <span>₹{{ number_format($order->cgst_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-neutral-600">
                        <span>SGST:</span>
                        <span>₹{{ number_format($order->sgst_amount, 2) }}</span>
                    </div>
                @endif
                @if($order->igst_amount > 0)
                    <div class="flex justify-between text-neutral-600">
                        <span>IGST (Integrated Tax):</span>
                        <span>₹{{ number_format($order->igst_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-neutral-600">
                    <span>Shipping Charges:</span>
                    <span>{{ $order->shipping_fee == 0 ? 'FREE' : '₹'.number_format($order->shipping_fee, 2) }}</span>
                </div>
                @if($order->gateway_fee > 0)
                    <div class="flex justify-between text-neutral-600">
                        <span>Gateway Charges:</span>
                        <span>₹{{ number_format($order->gateway_fee, 2) }}</span>
                    </div>
                @endif
                <div class="pt-2 border-t border-neutral-300 flex justify-between text-base font-bold text-neutral-900">
                    <span>Grand Total:</span>
                    <span>₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
