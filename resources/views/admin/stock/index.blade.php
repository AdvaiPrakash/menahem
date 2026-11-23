@extends('admin.layouts.app')

@section('title', 'Stock Management')
@section('header_title', 'Stock Management')

@section('header_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.stock.movements') }}" 
           class="px-3 py-1.5 rounded-none border border-neutral-300 text-neutral-800 text-xs font-bold uppercase tracking-wider hover:bg-neutral-100 transition-colors bg-white">
            Movement History
        </a>
        <a href="{{ route('admin.stock.analysis') }}" 
           class="px-3 py-1.5 rounded-none bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider transition-all border border-neutral-900">
            Stock Analysis
        </a>
    </div>
@endsection

@section('content')
<!-- Stock Dashboard KPI Statistics -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-5">
    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Total Products</p>
        <h3 class="text-xl font-black text-neutral-900 mt-1">{{ $totalProducts }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Available Units</p>
        <h3 class="text-xl font-black text-emerald-700 mt-1">{{ number_format($totalAvailableStock) }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Units Sold</p>
        <h3 class="text-xl font-black text-neutral-900 mt-1">{{ number_format($totalSoldQuantity) }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Low Stock (&le; 10)</p>
        <h3 class="text-xl font-black text-amber-700 mt-1">{{ $lowStockProducts }}</h3>
    </div>

    <div class="bg-white rounded-none p-4 border border-neutral-200">
        <p class="text-[10px] font-bold text-neutral-500 uppercase tracking-widest">Out of Stock</p>
        <h3 class="text-xl font-black text-rose-700 mt-1">{{ $outOfStockProducts }}</h3>
    </div>
</div>

<!-- Search & Status Filter -->
<div class="bg-white rounded-none border border-neutral-200 p-4 mb-5">
    <form action="{{ route('admin.stock.index') }}" method="GET" class="flex flex-col sm:flex-row gap-2.5">
        <div class="flex-1 relative">
            <input type="text" 
                   name="search" 
                   value="{{ $search }}" 
                   placeholder="Search product by name, SKU, HSN, or category..." 
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 bg-white">
            <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>

        <div class="sm:w-52">
            <select name="stock_status" class="w-full px-3 py-2 text-xs rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 bg-white">
                <option value="">All Stock Statuses</option>
                <option value="in_stock" {{ $statusFilter === 'in_stock' ? 'selected' : '' }}>In Stock (&gt; 10 units)</option>
                <option value="low_stock" {{ $statusFilter === 'low_stock' ? 'selected' : '' }}>Low Stock (&le; 10 units)</option>
                <option value="out_of_stock" {{ $statusFilter === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0 units)</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold rounded-none uppercase tracking-wider transition-colors border border-neutral-900 cursor-pointer">
                Filter
            </button>
            @if($search || $statusFilter)
                <a href="{{ route('admin.stock.index') }}" class="px-3 py-2 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 text-xs font-bold rounded-none flex items-center justify-center border border-neutral-300">
                    Reset
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Product Stock Table with Quick Adjustment Modal via Alpine.js -->
<div x-data="{
    adjustModal: false,
    selectedProduct: null,
    actionType: 'restock',
    openAdjust(prod, action) {
        this.selectedProduct = prod;
        this.actionType = action;
        this.adjustModal = true;
    }
}">
    <div class="bg-white rounded-none border border-neutral-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-neutral-50 text-[10px] uppercase tracking-wider text-neutral-600 font-bold border-b border-neutral-200">
                    <tr>
                        <th class="py-3 px-4 sm:px-6">Product & SKU</th>
                        <th class="py-3 px-4">HSN Code</th>
                        <th class="py-3 px-4">Opening</th>
                        <th class="py-3 px-4">Available</th>
                        <th class="py-3 px-4">Sold</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 sm:px-6 text-right">Stock Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200">
                    @forelse($products as $prod)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="py-3 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-9 h-9 rounded-none object-cover bg-neutral-100 border border-neutral-300 shrink-0">
                                    <div class="min-w-0">
                                        <p class="font-bold text-neutral-900 text-xs truncate max-w-xs">{{ $prod->name }}</p>
                                        <p class="text-[10px] font-mono text-neutral-500">{{ $prod->sku ?: 'SKU-'.$prod->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 font-mono text-xs text-neutral-700">
                                {{ $prod->hsn_code ?: '6304' }}
                            </td>
                            <td class="py-3 px-4 text-xs font-medium text-neutral-700">
                                {{ $prod->opening_stock }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-black {{ $prod->stock_quantity <= 0 ? 'text-rose-600' : ($prod->stock_quantity <= 10 ? 'text-amber-600' : 'text-neutral-900') }}">
                                    {{ $prod->stock_quantity }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs font-semibold text-neutral-800">
                                {{ $prod->sold_quantity }}
                            </td>
                            <td class="py-3 px-4">
                                @if($prod->stock_quantity <= 0)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase tracking-wider bg-rose-100 text-rose-900 border border-rose-300">
                                        Out of Stock
                                    </span>
                                @elseif($prod->stock_quantity <= ($prod->low_stock_threshold ?: 10))
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase tracking-wider bg-amber-100 text-amber-900 border border-amber-300">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded-none text-[9px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-900 border border-emerald-300">
                                        In Stock
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" 
                                            @click="openAdjust({{ json_encode(['id' => $prod->id, 'name' => $prod->name, 'stock' => $prod->stock_quantity]) }}, 'restock')"
                                            class="px-2 py-1 text-[11px] font-bold rounded-none bg-neutral-900 text-white hover:bg-neutral-800 transition-colors border border-neutral-900 cursor-pointer">
                                        + Stock
                                    </button>
                                    <button type="button" 
                                            @click="openAdjust({{ json_encode(['id' => $prod->id, 'name' => $prod->name, 'stock' => $prod->stock_quantity]) }}, 'damaged')"
                                            class="px-2 py-1 text-[11px] font-bold rounded-none bg-white text-rose-700 hover:bg-rose-50 border border-rose-300 transition-colors cursor-pointer"
                                            title="Record Damaged">
                                        Damaged
                                    </button>
                                    <button type="button" 
                                            @click="openAdjust({{ json_encode(['id' => $prod->id, 'name' => $prod->name, 'stock' => $prod->stock_quantity]) }}, 'manual_adjustment')"
                                            class="px-2 py-1 text-[11px] font-bold rounded-none bg-neutral-100 text-neutral-800 hover:bg-neutral-200 border border-neutral-300 transition-colors cursor-pointer"
                                            title="Manual Adjustment">
                                        Adjust
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-neutral-400">
                                No products found matching criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-3 border-t border-neutral-200 bg-neutral-50">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- Quick Stock Adjustment Modal: Sharp Corners & Clean White -->
    <div x-show="adjustModal" 
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-neutral-900/50 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;">
        <div @click.away="adjustModal = false" class="bg-white rounded-none max-w-md w-full p-6 shadow-2xl border border-neutral-300">
            <div class="flex items-center justify-between pb-3 border-b border-neutral-200 mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-900">
                    <span x-text="actionType === 'restock' ? 'Add New Stock' : (actionType === 'damaged' ? 'Record Damaged Stock' : 'Manual Stock Adjustment')"></span>
                </h3>
                <button type="button" @click="adjustModal = false" class="text-neutral-400 hover:text-neutral-900 font-bold">✕</button>
            </div>

            <template x-if="selectedProduct">
                <form :action="'/admin/stock/' + selectedProduct.id + '/adjust'" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="movement_type" :value="actionType">

                    <div class="p-3 bg-neutral-50 rounded-none border border-neutral-200">
                        <p class="text-[10px] font-bold text-neutral-500 uppercase">Selected Product:</p>
                        <h4 class="text-xs font-black text-neutral-900 truncate mt-0.5" x-text="selectedProduct.name"></h4>
                        <p class="text-[11px] text-neutral-600 mt-1">Available Stock: <strong class="text-neutral-900" x-text="selectedProduct.stock"></strong> units</p>
                    </div>

                    <template x-if="actionType === 'manual_adjustment'">
                        <div>
                            <label class="block text-[10px] font-bold text-neutral-700 uppercase tracking-wider mb-1">Adjustment Direction</label>
                            <select name="adjustment_direction" class="w-full px-3 py-2 rounded-none border border-neutral-300 bg-white">
                                <option value="add">Add Units to Inventory (+)</option>
                                <option value="reduce">Reduce Units from Inventory (-)</option>
                            </select>
                        </div>
                    </template>

                    <div>
                        <label class="block text-[10px] font-bold text-neutral-700 uppercase tracking-wider mb-1">
                            <span x-text="actionType === 'damaged' ? 'Damaged Units Quantity' : 'Quantity Units'"></span>
                            <span class="text-rose-600">*</span>
                        </label>
                        <input type="number" 
                               name="quantity" 
                               min="1" 
                               required 
                               placeholder="e.g. 10" 
                               class="w-full px-3 py-2 rounded-none border border-neutral-300 font-bold focus:outline-none focus:border-neutral-900 bg-white">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-neutral-700 uppercase tracking-wider mb-1">Reason / Note</label>
                        <input type="text" 
                               name="reason" 
                               placeholder="e.g. New shipment arrived, warehouse damage, shelf recount" 
                               class="w-full px-3 py-2 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 bg-white">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-neutral-200">
                        <button type="button" @click="adjustModal = false" class="px-4 py-2 text-xs font-bold rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-none bg-neutral-900 hover:bg-neutral-800 text-white uppercase tracking-wider border border-neutral-900 cursor-pointer">
                            Submit Adjustment
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>
</div>
@endsection
