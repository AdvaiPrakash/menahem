@extends('layouts.app')

@section('title', "Order #{$order->order_number} · Menahem")

@section('content')
<div class="min-h-screen bg-[#FBFBF9] pt-28 pb-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('account.orders') }}" class="text-xs font-bold text-[#74826B] hover:underline flex items-center gap-1">
                &larr; Back to all orders
            </a>
            @if($order->invoice)
                <a href="{{ route('account.orders.invoice', ['orderNumber' => $order->order_number]) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#384230] text-white text-xs font-bold hover:bg-[#2C3424] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download Tax Invoice (PDF)</span>
                </a>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6 sm:p-10 space-y-6">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-neutral-200">
                <div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $order->isPaid() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                    <h1 class="text-2xl font-serif font-bold text-[#2B3326] mt-2">Order #{{ $order->order_number }}</h1>
                    <p class="text-xs text-[#5C6654] mt-1">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-xs text-slate-500 block">Total Amount</span>
                    <span class="text-2xl font-bold text-[#384230]">₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>

            <!-- Items -->
            <div class="divide-y divide-neutral-100">
                @foreach($order->items as $item)
                    <div class="py-4 flex items-center justify-between gap-4 text-xs">
                        <div>
                            <h4 class="font-bold text-sm text-[#2B3326]">{{ $item->product_name }}</h4>
                            @if($item->custom_size)
                                <p class="text-[#74826B] font-medium mt-0.5">Custom Size: {{ $item->custom_size }}</p>
                            @endif
                            <p class="text-[#5C6654] mt-0.5">Quantity: {{ $item->quantity }} × ₹{{ number_format($item->unit_price, 2) }}</p>
                        </div>
                        <span class="font-bold text-sm text-[#2B3326]">₹{{ number_format($item->total_amount, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Summary Breakdown -->
            <div class="pt-6 border-t border-neutral-200 space-y-2 text-xs">
                <div class="flex justify-between text-[#5C6654]">
                    <span>Subtotal:</span>
                    <span class="font-bold text-[#2B3326]">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700 font-bold">
                        <span>Coupon Discount ({{ $order->coupon_code }}):</span>
                        <span>-₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-[#5C6654]">
                    <span>Total GST:</span>
                    <span>₹{{ number_format($order->total_tax, 2) }}</span>
                </div>
                <div class="flex justify-between text-[#5C6654]">
                    <span>Shipping:</span>
                    <span>{{ $order->shipping_fee == 0 ? 'FREE' : '₹'.number_format($order->shipping_fee, 2) }}</span>
                </div>
                <div class="pt-3 border-t border-neutral-200 flex justify-between text-base font-bold text-[#2B3326]">
                    <span>Grand Total:</span>
                    <span>₹{{ number_format($order->grand_total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
