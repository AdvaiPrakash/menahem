@extends('layouts.app')

@section('title', "Order Confirmed #{$order->order_number} · Menahem")

@section('content')
<div class="min-h-screen bg-[#FBFBF9] pt-24 pb-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <!-- Success Banner -->
        <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6 sm:p-10 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center text-3xl mx-auto mb-4">
                ✓
            </div>
            <span class="text-xs font-bold uppercase tracking-wider text-[#74826B]">Payment Verified & Confirmed</span>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#2B3326] mt-1">Thank You for Your Order!</h1>
            <p class="text-xs sm:text-sm text-[#5C6654] mt-2 max-w-md mx-auto">
                Your order <strong class="text-[#2B3326]">#{{ $order->order_number }}</strong> has been placed successfully. A confirmation email with your official tax invoice has been sent to <strong>{{ $order->customer_email }}</strong>.
            </p>

            <!-- Actions: Download Invoice PDF & Back Home -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                @if($invoice && $invoice->pdf_token)
                    <a href="{{ route('invoices.download.token', ['token' => $invoice->pdf_token]) }}" 
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-[#384230] hover:bg-[#2C3424] text-white text-xs font-bold uppercase tracking-wider transition-all shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Download GST Tax Invoice (PDF)</span>
                    </a>
                @endif
                <a href="{{ route('home') }}" 
                   class="w-full sm:w-auto px-6 py-3 rounded-lg border border-neutral-300 text-[#2B3326] text-xs font-bold uppercase tracking-wider hover:bg-neutral-50 transition-colors">
                    Continue Shopping
                </a>
            </div>
        </div>

        <!-- Order Summary Card -->
        <div class="mt-8 bg-white rounded-2xl border border-neutral-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-4 border-b border-neutral-100 text-xs">
                <div>
                    <p class="text-slate-400 uppercase font-semibold">Order Information</p>
                    <p class="font-bold text-[#2B3326] mt-0.5 font-mono text-sm">#{{ $order->order_number }}</p>
                    <p class="text-slate-500 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div>
                    <p class="text-slate-400 uppercase font-semibold">Payment Status</p>
                    <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded-full font-bold text-emerald-800 bg-emerald-100">
                        Paid via Razorpay ({{ $order->razorpay_payment_id ?: 'Verified' }})
                    </span>
                </div>
            </div>

            <!-- Delivery Address Details -->
            <div class="text-xs">
                <p class="text-slate-400 uppercase font-semibold mb-1">Delivering To</p>
                @php $s = $order->shipping_address ?? []; @endphp
                <p class="font-bold text-[#2B3326] text-sm">{{ $s['full_name'] ?? $order->customer_name }}</p>
                <p class="text-[#5C6654] mt-0.5">{{ $s['phone'] ?? $order->customer_phone }} • {{ $order->customer_email }}</p>
                <p class="text-[#5C6654] mt-1">
                    {{ $s['address_line1'] ?? '' }} {{ $s['address_line2'] ?? '' }}<br>
                    {{ $s['city'] ?? '' }}, {{ $s['state'] ?? '' }} - {{ $s['pin_code'] ?? '' }}
                </p>
            </div>

            <!-- Purchased Items Table -->
            <div class="pt-4 border-t border-neutral-100">
                <p class="text-xs text-slate-400 uppercase font-semibold mb-3">Order Items</p>
                <div class="divide-y divide-neutral-100 text-xs">
                    @foreach($order->items as $item)
                        <div class="py-3 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-bold text-[#2B3326]">{{ $item->product_name }}</p>
                                @if($item->custom_size)
                                    <span class="text-[11px] text-[#74826B]">Custom: {{ $item->custom_size }}</span>
                                @endif
                                <p class="text-[11px] text-[#5C6654]">Qty: {{ $item->quantity }} × ₹{{ number_format($item->unit_price, 2) }}</p>
                            </div>
                            <span class="font-bold text-[#2B3326]">₹{{ number_format($item->total_amount, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Final Financial Summary -->
            <div class="pt-4 border-t border-neutral-200 space-y-1.5 text-xs">
                <div class="flex justify-between text-[#5C6654]">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-[#2B3326]">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700 font-bold">
                        <span>Coupon Discount ({{ $order->coupon_code }}):</span>
                        <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-[#5C6654]">
                    <span>Total Applicable GST:</span>
                    <span class="font-semibold text-[#2B3326]">₹{{ number_format($order->total_tax, 2) }}</span>
                </div>
                <div class="flex justify-between text-[#5C6654]">
                    <span>Shipping Charges:</span>
                    <span>{{ $order->shipping_fee == 0 ? 'FREE' : '₹'.number_format($order->shipping_fee, 2) }}</span>
                </div>
                <div class="pt-2 border-t border-neutral-200 flex justify-between text-sm font-extrabold text-[#2B3326]">
                    <span>Total Paid:</span>
                    <span>₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
