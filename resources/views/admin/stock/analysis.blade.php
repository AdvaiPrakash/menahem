@extends('admin.layouts.app')

@section('title', 'Stock Performance & Analysis')
@section('header_title', 'Stock Analysis')

@section('header_actions')
    <a href="{{ route('admin.stock.index') }}" 
       class="px-3.5 py-2 rounded-none border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        Back to Stock
    </a>
@endsection

@section('content')
<!-- Inventory KPI Summary -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-none p-6 border border-neutral-200 shadow-none flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total Units Sold</p>
            <h3 class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($totalUnitsSold) }}</h3>
            <p class="text-xs text-neutral-600 font-medium mt-1">Direct from store checkouts</p>
        </div>
        <div class="w-12 h-12 rounded-none bg-neutral-100 text-neutral-800 flex items-center justify-center font-bold text-xl border border-neutral-200">
            📦
        </div>
    </div>

    <div class="bg-white rounded-none p-6 border border-neutral-200 shadow-none flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Current Available Units</p>
            <h3 class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($totalCurrentStock) }}</h3>
            <p class="text-xs text-neutral-500 mt-1">Units ready to ship</p>
        </div>
        <div class="w-12 h-12 rounded-none bg-neutral-100 text-neutral-800 flex items-center justify-center font-bold text-xl border border-neutral-200">
            ✓
        </div>
    </div>

    <div class="bg-white rounded-none p-6 border border-neutral-200 shadow-none flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total Stock Inflow</p>
            <h3 class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($totalStockAdded) }}</h3>
            <p class="text-xs text-neutral-500 mt-1">Units provisioned into warehouse</p>
        </div>
        <div class="w-12 h-12 rounded-none bg-neutral-100 text-neutral-800 flex items-center justify-center font-bold text-xl border border-neutral-200">
            📥
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Best-Selling Products -->
    <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
        <div class="p-5 border-b border-neutral-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-neutral-900 flex items-center gap-2">
                    <span>🔥</span>
                    <span>Best-Selling Products</span>
                </h3>
                <p class="text-xs text-neutral-500">Highest sales volume products</p>
            </div>
            <span class="text-xs font-bold text-neutral-900 bg-neutral-100 px-2.5 py-1 rounded-none border border-neutral-200">Top 10</span>
        </div>

        <div class="divide-y divide-neutral-200">
            @forelse($bestSellers as $idx => $prod)
                <div class="p-4 hover:bg-neutral-50 transition-colors flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-6 text-center font-bold text-xs text-neutral-400">#{{ $idx + 1 }}</span>
                        <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-10 h-10 rounded-none object-cover bg-neutral-100 shrink-0 border border-neutral-200">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-neutral-900 truncate">{{ $prod->name }}</h4>
                            <p class="text-[11px] text-neutral-500">{{ $prod->category }} • {{ $prod->formatted_price }}</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-sm font-bold text-neutral-900">{{ $prod->sold_quantity }}</span>
                        <span class="text-[10px] text-neutral-400 block uppercase">Units Sold</span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-neutral-400 text-xs">
                    No sales recorded yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Slow-Moving Products / Restock Attention -->
    <div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
        <div class="p-5 border-b border-neutral-200 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-neutral-900 flex items-center gap-2">
                    <span>⏳</span>
                    <span>Slow-Moving Inventory</span>
                </h3>
                <p class="text-xs text-neutral-500">Products with lowest turnover</p>
            </div>
            <span class="text-xs font-bold text-neutral-900 bg-neutral-100 px-2.5 py-1 rounded-none border border-neutral-200">Attention</span>
        </div>

        <div class="divide-y divide-neutral-200">
            @forelse($slowMoving as $idx => $prod)
                <div class="p-4 hover:bg-neutral-50 transition-colors flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-10 h-10 rounded-none object-cover bg-neutral-100 shrink-0 border border-neutral-200">
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-neutral-900 truncate">{{ $prod->name }}</h4>
                            <p class="text-[11px] text-neutral-500">In Stock: <strong class="text-neutral-800">{{ $prod->stock_quantity }}</strong> units</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="text-sm font-bold text-neutral-500">{{ $prod->sold_quantity }}</span>
                        <span class="text-[10px] text-neutral-400 block uppercase">Sold</span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-neutral-400 text-xs">
                    No slow moving inventory.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
