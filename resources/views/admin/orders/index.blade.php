@extends('admin.layouts.app')

@section('title', 'Order Management')
@section('header_title', 'Order Management')

@section('content')
<div class="space-y-6" data-live-context="orders">
    <!-- Orders Statistics KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Total Orders</span>
            <span id="live-orders-total" class="text-2xl font-bold text-neutral-900 mt-1 block">{{ number_format($stats['total'] ?? 0) }}</span>
            <span class="text-[11px] text-neutral-500 mt-0.5 block">Store transactions</span>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Paid Revenue</span>
            <span id="live-orders-revenue" class="text-2xl font-bold text-neutral-900 mt-1 block">₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}</span>
            <span id="live-orders-paid-label" class="text-[11px] text-emerald-700 font-semibold mt-0.5 block">{{ $stats['paid'] ?? 0 }} paid orders</span>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Processing</span>
            <span id="live-orders-processing" class="text-2xl font-bold text-neutral-900 mt-1 block">{{ $stats['processing'] ?? 0 }}</span>
            <span class="text-[11px] text-amber-700 font-semibold mt-0.5 block">Awaiting shipment</span>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Delivered</span>
            <span id="live-orders-delivered" class="text-2xl font-bold text-neutral-900 mt-1 block">{{ $stats['delivered'] ?? 0 }}</span>
            <span class="text-[11px] text-neutral-500 mt-0.5 block">Successfully fulfilled</span>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-none border border-neutral-200 shadow-none">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Search Orders</label>
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? '' }}" 
                           placeholder="Order #, name, phone, email..." 
                           class="w-full pl-8 pr-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                    <svg class="w-3.5 h-3.5 text-neutral-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Payment Status</label>
                <select name="payment_status" class="w-full px-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                    <option value="">All Payment Statuses</option>
                    <option value="paid" {{ ($paymentStatus ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ ($paymentStatus ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ ($paymentStatus ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ ($paymentStatus ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Order Status</label>
                <select name="order_status" class="w-full px-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                    <option value="">All Order Statuses</option>
                    <option value="processing" {{ ($orderStatus ?? '') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="confirmed" {{ ($orderStatus ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="shipped" {{ ($orderStatus ?? '') === 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ ($orderStatus ?? '') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ ($orderStatus ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-colors">
                    Filter
                </button>
                @if(!empty($search) || !empty($paymentStatus) || !empty($orderStatus))
                    <a href="{{ route('admin.orders.index') }}" class="py-2 px-3 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-neutral-600">
                <thead class="bg-neutral-50 border-b border-neutral-200 text-[11px] uppercase font-bold tracking-wider text-neutral-500">
                    <tr>
                        <th class="px-5 py-3.5">Order ID</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Order Date</th>
                        <th class="px-5 py-3.5">Total Amount</th>
                        <th class="px-5 py-3.5">Payment</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="live-orders-tbody" class="divide-y divide-neutral-200">
                    @include('admin.orders.partials.table-rows', ['orders' => $orders])
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="p-4 border-t border-neutral-200 bg-neutral-50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
