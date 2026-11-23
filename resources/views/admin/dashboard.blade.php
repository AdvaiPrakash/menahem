@extends('admin.layouts.app')

@section('title', 'Admin Overview')
@section('header_title', 'Dashboard Overview')

@section('header_actions')
    @if(auth()->user()->canEditContent())
    <a href="{{ route('admin.products.create') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-none bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider transition-all border border-neutral-900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Add Product</span>
    </a>
    @endif
@endsection

@section('content')
<!-- Key Statistics Cards: Fully White Theme, Sharp Corners -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-none p-5 border border-neutral-200 flex items-center gap-4">
        <div class="w-11 h-11 rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Paid Revenue</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">₹{{ number_format($totalRevenue) }}</h3>
            <p class="text-[11px] text-emerald-700 font-semibold mt-0.5">{{ $paidOrdersCount }} Confirmed Orders</p>
        </div>
    </div>

    <div class="bg-white rounded-none p-5 border border-neutral-200 flex items-center gap-4">
        <div class="w-11 h-11 rounded-none bg-neutral-100 text-neutral-800 border border-neutral-200 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Inventory & Stock</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">{{ $totalProducts }} <span class="text-xs font-normal text-neutral-400">Items</span></h3>
            @if($lowStockCount > 0)
                <p class="text-[11px] text-amber-700 font-bold mt-0.5">⚠️ {{ $lowStockCount }} Low Stock Alert</p>
            @else
                <p class="text-[11px] text-neutral-600 font-medium mt-0.5">All products in stock</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-none p-5 border border-neutral-200 flex items-center gap-4">
        <div class="w-11 h-11 rounded-none bg-blue-50 text-blue-800 border border-blue-200 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Customer Leads</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">{{ $totalLeads }}</h3>
            <p class="text-[11px] text-blue-700 font-semibold mt-0.5">Unified CRM Profiles</p>
        </div>
    </div>

    <div class="bg-white rounded-none p-5 border border-neutral-200 flex items-center gap-4">
        <div class="w-11 h-11 rounded-none bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        </div>
        <div class="min-w-0">
            <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Custom Inquiries</p>
            <h3 class="text-xl font-black text-neutral-900 mt-0.5">{{ $totalInquiries }}</h3>
            <p class="text-[11px] text-amber-700 font-bold mt-0.5">{{ $newInquiriesCount }} Awaiting Reply</p>
        </div>
    </div>
</div>

<!-- Recent Orders Section -->
<div class="bg-white rounded-none border border-neutral-200 mb-6">
    <div class="p-4 border-b border-neutral-200 bg-neutral-50/50 flex items-center justify-between">
        <div>
            <h2 class="text-xs font-bold text-neutral-900 uppercase tracking-wider">Recent Customer Purchases</h2>
            <p class="text-[11px] text-neutral-500 mt-0.5">Live transactions and payment gateway statuses</p>
        </div>
        <a href="{{ route('admin.invoices.index') }}" class="text-xs font-bold text-neutral-900 hover:underline">
            View All Invoices &rarr;
        </a>
    </div>

    <div class="divide-y divide-neutral-200">
        @forelse($recentOrders as $ord)
            <div class="p-3.5 hover:bg-neutral-50 transition-colors flex items-center justify-between gap-4 text-xs">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-mono font-bold text-neutral-900">{{ $ord->order_number }}</span>
                        <span class="px-1.5 py-0.5 rounded-none font-bold text-[9px] uppercase tracking-wider {{ $ord->isPaid() ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-amber-100 text-amber-900 border border-amber-300' }}">
                            {{ $ord->payment_status }}
                        </span>
                        <span class="text-neutral-500 text-[11px]">({{ ucfirst($ord->customer_type) }})</span>
                    </div>
                    <p class="text-neutral-500 text-[11px] mt-0.5">{{ $ord->customer_name }} • {{ $ord->customer_email }}</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs font-black text-neutral-900 block">₹{{ number_format($ord->grand_total, 2) }}</span>
                    @if($ord->invoice)
                        <a href="{{ route('admin.invoices.download', $ord->invoice) }}" class="text-neutral-800 hover:underline font-bold text-[11px]">
                            Download PDF
                        </a>
                    @else
                        <span class="text-neutral-400 text-[10px]">{{ $ord->created_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-6 text-center text-neutral-400 text-xs">
                No customer orders placed yet.
            </div>
        @endforelse
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Inquiries Section -->
    <div class="bg-white rounded-none border border-neutral-200">
        <div class="p-4 border-b border-neutral-200 bg-neutral-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-neutral-900 uppercase tracking-wider">Recent Inquiries & Leads</h2>
                <p class="text-[11px] text-neutral-500 mt-0.5">Customer form submissions & custom requests</p>
            </div>
            <a href="{{ route('admin.inquiries.index') }}" class="text-xs font-bold text-neutral-900 hover:underline">
                View All &rarr;
            </a>
        </div>

        <div class="divide-y divide-neutral-200">
            @forelse($recentInquiries as $inq)
                <div class="p-3.5 hover:bg-neutral-50 transition-colors flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-xs text-neutral-900">{{ $inq->name }}</span>
                            @if($inq->isNew())
                                <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-amber-100 text-amber-900 border border-amber-300">New</span>
                            @elseif($inq->isContacted())
                                <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-blue-100 text-blue-900 border border-blue-300">Contacted</span>
                            @else
                                <span class="px-1.5 py-0.2 text-[9px] uppercase font-bold tracking-wider rounded-none bg-neutral-100 text-neutral-800 border border-neutral-300">Closed</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-neutral-500 truncate">
                            {{ $inq->phone ?: $inq->email ?: 'No contact' }} 
                            @if($inq->product_name) • <span class="font-medium text-neutral-800">{{ $inq->product_name }}</span>@endif
                        </p>
                        @if($inq->message)
                            <p class="text-xs text-neutral-600 mt-1 line-clamp-1 italic">"{{ $inq->message }}"</p>
                        @endif
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-[10px] text-neutral-400 block">{{ $inq->created_at->diffForHumans() }}</span>
                        <a href="{{ route('admin.inquiries.show', $inq) }}" class="inline-block mt-1 text-xs font-bold text-neutral-900 hover:underline">
                            Details
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-neutral-400">
                    No inquiries recorded yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Products Section -->
    <div class="bg-white rounded-none border border-neutral-200">
        <div class="p-4 border-b border-neutral-200 bg-neutral-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-xs font-bold text-neutral-900 uppercase tracking-wider">Catalog Preview</h2>
                <p class="text-[11px] text-neutral-500 mt-0.5">Recently added home essentials</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-neutral-900 hover:underline">
                Manage Catalog &rarr;
            </a>
        </div>

        <div class="divide-y divide-neutral-200">
            @forelse($recentProducts as $prod)
                <div class="p-3.5 hover:bg-neutral-50 transition-colors flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-10 h-10 rounded-none object-cover bg-neutral-100 shrink-0 border border-neutral-300">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-neutral-900 truncate">{{ $prod->name }}</h4>
                            <p class="text-[11px] text-neutral-500 truncate">{{ $prod->category }} • {{ $prod->material }}</p>
                            <span class="text-xs font-extrabold text-neutral-900">{{ $prod->formatted_price }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        @if($prod->in_stock)
                            <span class="px-1.5 py-0.5 text-[9px] uppercase font-bold tracking-wider rounded-none bg-emerald-100 text-emerald-900 border border-emerald-300">In Stock</span>
                        @else
                            <span class="px-1.5 py-0.5 text-[9px] uppercase font-bold tracking-wider rounded-none bg-rose-100 text-rose-900 border border-rose-300">Out of Stock</span>
                        @endif
                        @if(auth()->user()->canEditContent())
                            <a href="{{ route('admin.products.edit', $prod) }}" class="block mt-1 text-xs font-bold text-neutral-900 hover:underline">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-xs text-neutral-400">
                    No products found.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
