@extends('admin.layouts.app')

@section('title', 'Admin Overview')
@section('header_title', 'Dashboard Overview')

@section('header_actions')
    @if(auth()->user()->canEditContent())
    <a href="{{ route('admin.products.create') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-xs">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Add Product</span>
    </a>
    @endif
@endsection

@section('content')
<!-- Key Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Products</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-0.5">{{ $totalProducts }}</h3>
            <p class="text-xs text-emerald-600 font-medium mt-0.5">{{ $inStockProducts }} In Stock</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Categories</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-0.5">{{ $totalCategories }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">Hierarchical taxonomy</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Customer Inquiries</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-0.5">{{ $totalInquiries }}</h3>
            <p class="text-xs text-amber-600 font-semibold mt-0.5">{{ $newInquiriesCount }} Awaiting Response</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Team Accounts</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-0.5">{{ $totalUsers }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">Role-controlled staff</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Inquiries Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Recent Customer Leads</h2>
                <p class="text-xs text-slate-500">Latest form submissions & custom requests</p>
            </div>
            <a href="{{ route('admin.inquiries.index') }}" class="text-xs font-semibold text-[#74826B] hover:underline">
                View All Leads →
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($recentInquiries as $inq)
                <div class="p-4 hover:bg-slate-50/80 transition-colors flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-sm text-slate-900">{{ $inq->name }}</span>
                            @if($inq->isNew())
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded-full bg-amber-100 text-amber-800">New</span>
                            @elseif($inq->isContacted())
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded-full bg-blue-100 text-blue-800">Contacted</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded-full bg-slate-100 text-slate-700">Closed</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 truncate">
                            {{ $inq->phone ?: $inq->email ?: 'No direct contact' }} 
                            @if($inq->product_name) • <span class="font-medium text-slate-700">{{ $inq->product_name }}</span>@endif
                        </p>
                        @if($inq->message)
                            <p class="text-xs text-slate-600 mt-1 line-clamp-1 italic">"{{ $inq->message }}"</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[11px] text-slate-400 block">{{ $inq->created_at->diffForHumans() }}</span>
                        <a href="{{ route('admin.inquiries.show', $inq) }}" class="inline-block mt-1 text-xs font-semibold text-[#74826B] hover:underline">
                            Details
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-400">
                    No inquiries recorded yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Products Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Catalog Preview</h2>
                <p class="text-xs text-slate-500">Recently added home essentials</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-[#74826B] hover:underline">
                Manage Catalog →
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($recentProducts as $prod)
                <div class="p-4 hover:bg-slate-50/80 transition-colors flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-12 h-12 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-200/60">
                        <div class="min-w-0">
                            <h4 class="text-sm font-semibold text-slate-900 truncate">{{ $prod->name }}</h4>
                            <p class="text-xs text-slate-500 truncate">{{ $prod->category }} • {{ $prod->material }}</p>
                            <span class="text-xs font-bold text-slate-900">{{ $prod->formatted_price }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        @if($prod->in_stock)
                            <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded-full bg-emerald-100 text-emerald-800">In Stock</span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] uppercase font-bold tracking-wider rounded-full bg-rose-100 text-rose-800">Out of Stock</span>
                        @endif
                        @if(auth()->user()->canEditContent())
                            <a href="{{ route('admin.products.edit', $prod) }}" class="block mt-1 text-xs font-semibold text-[#74826B] hover:underline">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-400">
                    No products found.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
