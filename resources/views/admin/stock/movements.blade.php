@extends('admin.layouts.app')

@section('title', 'Stock Movement History')
@section('header_title', 'Stock Movement History')

@section('header_actions')
    <a href="{{ route('admin.stock.index') }}" 
       class="px-3.5 py-2 rounded-none border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        Back to Stock
    </a>
@endsection

@section('content')
<!-- Filter bar -->
<div class="bg-white rounded-none border border-neutral-200 p-4 mb-6 shadow-none">
    <form action="{{ route('admin.stock.movements') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Search by product name or SKU..." 
                   class="w-full pl-9 pr-4 py-2 text-sm rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
            <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="sm:w-52">
            <select name="movement_type" class="w-full px-3 py-2 text-sm rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                <option value="">All Movement Types</option>
                <option value="initial" {{ $typeFilter === 'initial' ? 'selected' : '' }}>Initial Setup</option>
                <option value="restock" {{ $typeFilter === 'restock' ? 'selected' : '' }}>Stock Added (Restock)</option>
                <option value="sold" {{ $typeFilter === 'sold' ? 'selected' : '' }}>Product Sold</option>
                <option value="order_cancelled" {{ $typeFilter === 'order_cancelled' ? 'selected' : '' }}>Order Cancelled Restock</option>
                <option value="order_returned" {{ $typeFilter === 'order_returned' ? 'selected' : '' }}>Returned Product</option>
                <option value="damaged" {{ $typeFilter === 'damaged' ? 'selected' : '' }}>Damaged / Discarded</option>
                <option value="manual_adjustment" {{ $typeFilter === 'manual_adjustment' ? 'selected' : '' }}>Manual Adjustment</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-neutral-900 hover:bg-black text-white text-xs font-semibold rounded-none uppercase tracking-wider transition-colors">
                Filter
            </button>
            @if($search || $typeFilter)
                <a href="{{ route('admin.stock.movements') }}" class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-semibold rounded-none flex items-center justify-center">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Movements Table -->
<div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-neutral-50 text-xs uppercase tracking-wider text-neutral-600 font-semibold border-b border-neutral-200">
                <tr>
                    <th class="py-3.5 px-4 sm:px-6">Date & Time</th>
                    <th class="py-3.5 px-4">Product Name</th>
                    <th class="py-3.5 px-4">Movement Type</th>
                    <th class="py-3.5 px-4 text-center">Previous</th>
                    <th class="py-3.5 px-4 text-center">Change</th>
                    <th class="py-3.5 px-4 text-center">Updated</th>
                    <th class="py-3.5 px-4">Attributed To</th>
                    <th class="py-3.5 px-4 sm:px-6">Reason / Ref</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 text-xs">
                @forelse($movements as $m)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="py-3.5 px-4 sm:px-6 text-neutral-500 whitespace-nowrap">
                            {{ $m->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-neutral-900 block">{{ $m->product ? $m->product->name : 'Deleted Product' }}</span>
                            <span class="font-mono text-[10px] text-neutral-400">{{ $m->product?->sku }}</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-none font-bold text-[10px] uppercase tracking-wider border 
                                {{ $m->movement_type === 'sold' ? 'bg-blue-50 text-blue-800 border-blue-200' : 
                                   ($m->movement_type === 'restock' || $m->movement_type === 'initial' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 
                                   ($m->movement_type === 'damaged' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-neutral-100 text-neutral-700 border-neutral-200')) }}">
                                {{ $m->movementTypeLabel() }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center text-neutral-600 font-medium">{{ $m->previous_quantity }}</td>
                        <td class="py-3.5 px-4 text-center font-bold {{ $m->quantity_changed > 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $m->quantity_changed > 0 ? '+'.$m->quantity_changed : $m->quantity_changed }}
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-neutral-900">{{ $m->updated_quantity }}</td>
                        <td class="py-3.5 px-4 text-neutral-600">
                            {{ $m->user ? $m->user->name : ($m->movement_type === 'sold' ? 'Customer Order' : 'System') }}
                        </td>
                        <td class="py-3.5 px-4 sm:px-6 text-neutral-500 italic max-w-xs truncate">
                            {{ $m->reason ?: ($m->order ? 'Order #'.$m->order->order_number : '—') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-neutral-400">
                            No stock movements recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($movements->hasPages())
        <div class="p-4 border-t border-neutral-200 bg-neutral-50">
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection
