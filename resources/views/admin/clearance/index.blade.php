@extends('admin.layouts.app')

@section('title', 'Clearance Sale Management')
@section('header_title', 'Clearance Sale Management')

@section('content')
<div class="space-y-6">
    <!-- Selected Clearance Products -->
    <div class="bg-white border border-neutral-200 overflow-hidden">
        <div class="p-5 border-b border-neutral-200 bg-neutral-50/70 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">
                    Active Clearance Sale Products ({{ $clearanceItems->count() }})
                </h2>
                <p class="text-[11px] text-neutral-500 mt-0.5">
                    Configure percentage or fixed discounts, display order, and independent Homepage / All Products visibility.
                </p>
            </div>
        </div>

        <div class="divide-y divide-neutral-200">
            @forelse($clearanceItems as $item)
                @php
                    $product = $item->product;
                @endphp
                @if($product)
                <div class="p-4 sm:p-5 hover:bg-neutral-50/60 transition-colors">
                    <form action="{{ route('admin.clearance.update', $item) }}" method="POST"
                          class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                        @csrf
                        @method('PUT')

                        <!-- Product Info -->
                        <div class="lg:col-span-3 flex items-center gap-3 min-w-0">
                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                 class="w-12 h-12 object-cover border border-neutral-200 shrink-0 bg-neutral-100">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-neutral-900 truncate">{{ $product->name }}</p>
                                <p class="text-[11px] text-neutral-500">
                                    Original: <strong class="text-neutral-800">₹{{ number_format($product->numeric_price) }}</strong>
                                    &rarr;
                                    Sale: <strong class="text-emerald-700">₹{{ number_format($item->calculateSalePrice($product->numeric_price), 2) }}</strong>
                                </p>
                                <span class="inline-block mt-0.5 px-1.5 py-0.2 text-[9px] font-bold uppercase bg-neutral-900 text-white">
                                    {{ $item->discountBadgeLabel($product->numeric_price) }}
                                </span>
                            </div>
                        </div>

                        <!-- Discount Method & Value -->
                        <div class="lg:col-span-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Discount Type</label>
                            <select name="discount_type"
                                    class="w-full px-2.5 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                <option value="percentage" {{ $item->discount_type === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ $item->discount_type === 'fixed' ? 'selected' : '' }}>Fixed (₹)</option>
                            </select>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Discount Value</label>
                            <input type="number" step="0.01" min="0" name="discount_value" value="{{ $item->discount_value }}" required
                                   class="w-full px-2.5 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                        </div>

                        <div class="lg:col-span-1">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Order</label>
                            <input type="number" min="0" name="display_order" value="{{ $item->display_order }}" required
                                   class="w-full px-2.5 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                        </div>

                        <!-- Visibility Controls -->
                        <div class="lg:col-span-2 space-y-1.5 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="show_on_homepage" value="1" {{ $item->show_on_homepage ? 'checked' : '' }}
                                       class="border-neutral-300 text-neutral-900 focus:ring-neutral-900">
                                <span class="text-[11px] font-semibold text-neutral-800">Show in Homepage Clearance Sale</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="show_in_all_products" value="1" {{ $item->show_in_all_products ? 'checked' : '' }}
                                       class="border-neutral-300 text-neutral-900 focus:ring-neutral-900">
                                <span class="text-[11px] font-semibold text-neutral-800">Show in All Products Page</span>
                            </label>
                            <input type="hidden" name="is_active" value="1">
                        </div>

                        <!-- Actions -->
                        <div class="lg:col-span-2 flex items-center justify-end gap-2">
                            @if(auth()->user()->canEditContent())
                                <button type="submit"
                                        class="px-3 py-2 bg-neutral-900 hover:bg-black text-white text-[11px] font-bold uppercase tracking-wider cursor-pointer">
                                    Save
                                </button>
                            @endif
                    </form>
                            @if(auth()->user()->canEditContent())
                                <form action="{{ route('admin.clearance.destroy', $item) }}" method="POST"
                                      onsubmit="return confirm('Remove this product from Clearance Sale?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-2 bg-rose-50 hover:bg-rose-700 hover:text-white text-rose-700 border border-rose-200 text-[11px] font-bold uppercase tracking-wider cursor-pointer">
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </div>
                </div>
                @endif
            @empty
                <div class="p-8 text-center text-xs text-neutral-400">
                    No products selected for Clearance Sale yet. Use the catalog selector below to add products.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Select Products from Catalog to Add -->
    <div class="bg-white border border-neutral-200 overflow-hidden">
        <div class="p-5 border-b border-neutral-200 bg-neutral-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Add Products to Clearance Sale</h2>
                <p class="text-[11px] text-neutral-500 mt-0.5">Search existing catalog products, select one or multiple, and apply a discount.</p>
            </div>

            <form method="GET" action="{{ route('admin.clearance.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search products by name, SKU..."
                       class="px-3 py-1.5 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                <button type="submit" class="px-3 py-1.5 bg-neutral-900 text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.clearance.index') }}" class="px-3 py-1.5 border border-neutral-300 text-xs font-semibold text-neutral-700">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <form action="{{ route('admin.clearance.store') }}" method="POST" class="p-5 space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 bg-neutral-50 border border-neutral-200">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Discount Method *</label>
                    <select name="discount_type" required class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white">
                        <option value="percentage">Percentage Discount (%)</option>
                        <option value="fixed">Fixed Amount Discount (₹)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Discount Value *</label>
                    <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', 20) }}" required
                           class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white">
                </div>

                <div class="flex flex-col justify-center space-y-1.5">
                    <label class="flex items-center gap-2 cursor-pointer text-xs">
                        <input type="checkbox" name="show_on_homepage" value="1" checked class="border-neutral-300">
                        <span class="font-semibold text-neutral-800">Show in Homepage Clearance Sale</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs">
                        <input type="checkbox" name="show_in_all_products" value="1" checked class="border-neutral-300">
                        <span class="font-semibold text-neutral-800">Show in All Products Page</span>
                    </label>
                </div>

                <div class="flex items-end">
                    @if(auth()->user()->canEditContent())
                        <button type="submit"
                                class="w-full py-2.5 px-4 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                            Add Selected to Clearance
                        </button>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto border border-neutral-200">
                <table class="w-full text-left text-xs text-neutral-600">
                    <thead class="bg-neutral-50 border-b border-neutral-200 text-[10px] uppercase font-bold tracking-wider text-neutral-500">
                        <tr>
                            <th class="px-4 py-3 w-10">Select</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Original Price</th>
                            <th class="px-4 py-3">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200">
                        @forelse($availableProducts as $prod)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="product_ids[]" value="{{ $prod->id }}"
                                           class="border-neutral-300 text-neutral-900 focus:ring-neutral-900">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $prod->image }}" alt="{{ $prod->name }}" class="w-9 h-9 object-cover border border-neutral-200">
                                        <div>
                                            <span class="font-bold text-neutral-900 block">{{ $prod->name }}</span>
                                            <span class="text-[10px] text-neutral-400 font-mono">{{ $prod->sku }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $prod->category }}</td>
                                <td class="px-4 py-3 font-bold text-neutral-900">₹{{ number_format($prod->numeric_price) }}</td>
                                <td class="px-4 py-3">{{ $prod->stock_quantity }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-neutral-400">
                                    No additional products available to add.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($availableProducts->hasPages())
                <div>{{ $availableProducts->links() }}</div>
            @endif
        </form>
    </div>
</div>
@endsection
