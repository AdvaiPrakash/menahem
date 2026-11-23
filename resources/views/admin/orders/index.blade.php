@extends('admin.layouts.app')

@section('title', 'Order Management')
@section('header_title', 'Order Management')

@section('content')
<div class="space-y-6">
    <!-- Orders Statistics KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Total Orders</span>
            <span class="text-2xl font-bold text-neutral-900 mt-1 block">{{ number_format($stats['total'] ?? 0) }}</span>
            <span class="text-[11px] text-neutral-500 mt-0.5 block">Store transactions</span>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Paid Revenue</span>
            <span class="text-2xl font-bold text-neutral-900 mt-1 block">₹{{ number_format($stats['total_revenue'] ?? 0, 2) }}</span>
            <span class="text-[11px] text-emerald-700 font-semibold mt-0.5 block">{{ $stats['paid'] ?? 0 }} paid orders</span>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Processing</span>
            <span class="text-2xl font-bold text-neutral-900 mt-1 block">{{ $stats['processing'] ?? 0 }}</span>
            <span class="text-[11px] text-amber-700 font-semibold mt-0.5 block">Awaiting shipment</span>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-none border border-neutral-200 shadow-none">
            <span class="text-xs uppercase font-bold text-neutral-400 block tracking-wider">Delivered</span>
            <span class="text-2xl font-bold text-neutral-900 mt-1 block">{{ $stats['delivered'] ?? 0 }}</span>
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
                <tbody class="divide-y divide-neutral-200">
                    @forelse($orders as $order)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-neutral-900 hover:underline">
                                    {{ $order->order_number }}
                                </a>
                                <span class="text-[10px] text-neutral-400 block font-mono">{{ $order->items->count() }} items</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-bold text-neutral-900 block">{{ $order->customer_name }}</span>
                                <span class="text-xs text-neutral-500">{{ $order->customer_phone ?: $order->customer_email }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-neutral-500 whitespace-nowrap">
                                {{ $order->created_at->format('d M Y, h:i A') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-bold text-neutral-900">₹{{ number_format($order->grand_total, 2) }}</span>
                                @if($order->discount_amount > 0)
                                    <span class="text-[10px] text-neutral-500 block">Saved ₹{{ number_format($order->discount_amount, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-none border
                                    {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 
                                       ($order->payment_status === 'pending' ? 'bg-amber-50 text-amber-800 border-amber-300' : 
                                       ($order->payment_status === 'refunded' ? 'bg-blue-50 text-blue-800 border-blue-300' : 'bg-rose-50 text-rose-800 border-rose-300')) }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-none border
                                    {{ $order->order_status === 'delivered' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 
                                       ($order->order_status === 'shipped' ? 'bg-blue-50 text-blue-800 border-blue-300' : 
                                       ($order->order_status === 'confirmed' ? 'bg-indigo-50 text-indigo-800 border-indigo-300' : 
                                       ($order->order_status === 'cancelled' ? 'bg-rose-50 text-rose-800 border-rose-300' : 'bg-amber-50 text-amber-800 border-amber-300'))) }}">
                                    {{ ucfirst($order->order_status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.orders.show', $order) }}" 
                                       class="px-2.5 py-1 text-xs font-semibold bg-neutral-900 hover:bg-black text-white">
                                        View
                                    </a>
                                    <a href="{{ route('admin.orders.invoice', $order) }}" 
                                       class="px-2.5 py-1 text-xs font-semibold bg-white hover:bg-neutral-100 text-neutral-800 border border-neutral-300"
                                       title="Download Invoice PDF">
                                        Invoice
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-neutral-400 text-sm">
                                No orders matching your criteria.
                            </td>
                        </tr>
                    @endforelse
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
