@extends('admin.layouts.app')

@section('title', 'Manage Products')
@section('header_title', 'Products Catalog')

@section('header_actions')
    @if(auth()->user()->canEditContent())
    <a href="{{ route('admin.products.create') }}" 
       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Add Product</span>
    </a>
    @endif
@endsection

@section('content')
<!-- Filter & Search Bar -->
<div class="bg-white p-4 rounded-none border border-neutral-200 shadow-none mb-6">
    <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Search Products</label>
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Name, material..." 
                       class="w-full pl-9 pr-3 py-2 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                <svg class="w-4 h-4 text-neutral-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Category</label>
            <select name="category" class="w-full px-3 py-2 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ $selectedCategory === $cat->slug ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Stock Availability</label>
            <select name="stock" class="w-full px-3 py-2 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                <option value="">All Statuses</option>
                <option value="in_stock" {{ $stockFilter === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                <option value="out_of_stock" {{ $stockFilter === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2 px-4 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-colors">
                Filter
            </button>
            <a href="{{ route('admin.products.index') }}" class="py-2 px-3 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors text-center">
                Reset
            </a>
        </div>
    </form>
</div>

<!-- Responsive Content: Stacked Cards on Mobile, Real Table on Desktop -->
<div class="bg-white rounded-none border border-neutral-200 shadow-none overflow-hidden">
    <!-- Desktop Table View (Hidden on mobile) -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left text-sm text-neutral-600">
            <thead class="bg-neutral-50 border-b border-neutral-200 text-[11px] uppercase font-bold tracking-wider text-neutral-500">
                <tr>
                    <th class="px-5 py-3.5">Product</th>
                    <th class="px-5 py-3.5">Category</th>
                    <th class="px-5 py-3.5">Price</th>
                    <th class="px-5 py-3.5">Rating</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200">
                @forelse($products as $product)
                <tr class="hover:bg-neutral-50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-none object-cover bg-neutral-100 shrink-0 border border-neutral-200">
                            <div>
                                <span class="font-semibold text-neutral-900 block">{{ $product->name }}</span>
                                <span class="text-xs text-neutral-500">{{ $product->material ?: 'Artisanal Blend' }}</span>
                                @if($product->badge)
                                    <span class="inline-block ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded-none bg-neutral-100 text-neutral-800 border border-neutral-300">{{ $product->badge }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-xs font-medium text-neutral-700">
                        {{ $product->category }}
                    </td>
                    <td class="px-5 py-4">
                        <span class="font-bold text-neutral-900">{{ $product->formatted_price }}</span>
                        @if($product->original_price)
                            <span class="text-xs text-neutral-400 line-through block">{{ $product->original_price }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-xs">
                        <div class="flex items-center gap-1 text-neutral-800 font-semibold">
                            <span>★</span>
                            <span class="text-neutral-900">{{ $product->rating }}</span>
                            <span class="text-neutral-400">({{ $product->reviews }})</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @if($product->in_stock)
                            <span class="px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200">
                                In Stock
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider rounded-none bg-rose-50 text-rose-800 border border-rose-200">
                                Sold Out
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('products.show', $product->id) }}" target="_blank" 
                               class="p-1.5 text-neutral-400 hover:text-neutral-900 rounded-none hover:bg-neutral-100 transition-colors" title="View on site">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            @if(auth()->user()->canEditContent())
                            <a href="{{ route('admin.products.edit', $product) }}" 
                               class="px-2.5 py-1 rounded-none bg-neutral-100 hover:bg-neutral-900 hover:text-white text-neutral-800 text-xs font-semibold transition-colors border border-neutral-300">
                                Edit
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1 rounded-none bg-rose-50 hover:bg-rose-700 hover:text-white text-rose-700 text-xs font-semibold transition-colors border border-rose-200">
                                    Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-neutral-400">
                        No products match your criteria.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Stacked Card View (Shown on screens < md) -->
    <div class="block md:hidden divide-y divide-neutral-200">
        @forelse($products as $product)
        <div class="p-4 space-y-3">
            <div class="flex items-start gap-3">
                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="w-16 h-16 rounded-none object-cover bg-neutral-100 shrink-0 border border-neutral-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-bold text-neutral-500 uppercase">{{ $product->category }}</span>
                        @if($product->in_stock)
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-none bg-emerald-50 text-emerald-800 border border-emerald-200">In Stock</span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-none bg-rose-50 text-rose-800 border border-rose-200">Out of Stock</span>
                        @endif
                    </div>
                    <h3 class="font-bold text-neutral-900 text-sm mt-0.5">{{ $product->name }}</h3>
                    <p class="text-xs text-neutral-500">{{ $product->material ?: '100% Mulmul Cotton' }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-bold text-neutral-900">{{ $product->formatted_price }}</span>
                        @if($product->original_price)
                            <span class="text-xs text-neutral-400 line-through">{{ $product->original_price }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Mobile Action Bar -->
            <div class="flex items-center justify-between pt-2 border-t border-neutral-200">
                <a href="{{ route('products.show', $product->id) }}" target="_blank" class="text-xs font-semibold text-neutral-600 hover:text-neutral-900 flex items-center gap-1">
                    <span>Preview</span> ↗
                </a>
                @if(auth()->user()->canEditContent())
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}" class="px-3 py-1.5 rounded-none bg-neutral-100 text-neutral-800 text-xs font-semibold border border-neutral-300">
                        Edit
                    </a>
                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-none bg-rose-50 text-rose-700 text-xs font-semibold border border-rose-200">
                            Delete
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-sm text-neutral-400">
            No products match your criteria.
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="p-4 border-t border-neutral-200 bg-neutral-50">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
