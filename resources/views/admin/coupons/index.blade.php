@extends('admin.layouts.app')

@section('title', 'Offer & Coupon Management')
@section('header_title', 'Offer & Coupon Management')

@section('header_actions')
    @if(auth()->user()->canEditContent())
    <a href="{{ route('admin.coupons.create') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-none bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider transition-all border border-neutral-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Create Offer Code</span>
    </a>
    @endif
@endsection

@section('content')
<!-- Metric Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-none p-4 border border-neutral-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Coupons</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">{{ $stats['total'] }}</h3>
        </div>
        <div class="w-9 h-9 rounded-none bg-neutral-100 text-neutral-800 border border-neutral-200 flex items-center justify-center font-bold text-sm">
            🏷️
        </div>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Active Codes</p>
            <h3 class="text-xl font-black text-emerald-700 mt-0.5">{{ $stats['active'] }}</h3>
        </div>
        <div class="w-9 h-9 rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center font-bold text-sm">
            ✓
        </div>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Percentage (%)</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">{{ $stats['percentage'] }}</h3>
        </div>
        <div class="w-9 h-9 rounded-none bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center font-bold text-sm">
            %
        </div>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Fixed (₹)</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">{{ $stats['fixed'] }}</h3>
        </div>
        <div class="w-9 h-9 rounded-none bg-blue-50 text-blue-800 border border-blue-200 flex items-center justify-center font-bold text-sm">
            ₹
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="bg-white rounded-none border border-neutral-200 p-4 mb-5">
    <form action="{{ route('admin.coupons.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2.5">
        <div class="flex-1 relative">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Search coupon code or description..." 
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 bg-white">
            <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="sm:w-44">
            <select name="status" class="w-full px-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                <option value="">All Statuses</option>
                <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>
        </div>

        <div class="sm:w-44">
            <select name="discount_type" class="w-full px-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                <option value="">All Types</option>
                <option value="percentage" {{ $typeFilter === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                <option value="fixed" {{ $typeFilter === 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded-none uppercase tracking-wider transition-colors border border-neutral-900 cursor-pointer">
                Filter
            </button>
            @if($search || $statusFilter || $typeFilter)
                <a href="{{ route('admin.coupons.index') }}" class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold rounded-none flex items-center justify-center border border-neutral-300">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Coupons Table -->
<div class="bg-white rounded-none border border-neutral-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-600 font-bold border-b border-neutral-200">
                <tr>
                    <th class="py-3 px-4 sm:px-6">Offer Code</th>
                    <th class="py-3 px-4">Discount</th>
                    <th class="py-3 px-4">Min Order</th>
                    <th class="py-3 px-4">Usage</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4 sm:px-6 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse($coupons as $coupon)
                    <tr class="hover:bg-neutral-50 transition-colors">
                        <td class="py-3 px-4 sm:px-6">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-neutral-900 bg-neutral-100 px-2 py-0.5 rounded-none border border-neutral-300 text-xs tracking-wider">
                                    {{ $coupon->code }}
                                </span>
                            </div>
                            @if($coupon->description)
                                <p class="text-[11px] text-neutral-500 mt-1 line-clamp-1">{{ $coupon->description }}</p>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($coupon->discount_type === 'percentage')
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase bg-amber-100 text-amber-900 border border-amber-300">
                                    {{ $coupon->discount_value }}% OFF
                                </span>
                                @if($coupon->max_discount_amount)
                                    <span class="block text-[10px] text-neutral-500 mt-0.5 font-mono">Max ₹{{ number_format($coupon->max_discount_amount) }}</span>
                                @endif
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase bg-blue-100 text-blue-900 border border-blue-300">
                                    ₹{{ number_format($coupon->discount_value) }} FLAT
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs font-medium text-neutral-800">
                            {{ $coupon->min_order_amount ? '₹'.number_format($coupon->min_order_amount) : 'None' }}
                        </td>
                        <td class="py-3 px-4 text-xs text-neutral-700 font-mono">
                            <span class="font-bold text-neutral-900">{{ $coupon->used_count }}</span>
                            <span class="text-neutral-400">/ {{ $coupon->usage_limit ?: '∞' }}</span>
                        </td>
                        <td class="py-3 px-4">
                            @if($coupon->is_active)
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase tracking-wider bg-neutral-100 text-neutral-700 border border-neutral-300">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 sm:px-6 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(auth()->user()->canEditContent())
                                    <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                title="Toggle Active/Inactive"
                                                class="px-2 py-0.5 text-[11px] font-bold rounded-none border {{ $coupon->is_active ? 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }} transition-colors cursor-pointer">
                                            {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" 
                                       class="px-2 py-0.5 text-[11px] font-bold rounded-none bg-neutral-100 text-neutral-800 hover:bg-neutral-200 border border-neutral-300 transition-colors"
                                       title="Edit">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete offer code {{ $coupon->code }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-0.5 text-[11px] font-bold rounded-none bg-white text-rose-700 hover:bg-rose-50 border border-rose-300 transition-colors cursor-pointer" title="Delete">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-neutral-400">
                            No offer codes found. Create your first coupon to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($coupons->hasPages())
        <div class="p-3 border-t border-neutral-200 bg-neutral-50">
            {{ $coupons->links() }}
        </div>
    @endif
</div>
@endsection
