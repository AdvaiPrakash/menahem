@extends('admin.layouts.app')

@section('title', $isEdit ? 'Edit Product' : 'Add New Product')
@section('header_title', $isEdit ? 'Edit Product: ' . $product->name : 'Create New Product')

@section('header_actions')
    <a href="{{ route('admin.products.index') }}" 
       class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-semibold uppercase tracking-wider transition-colors">
        ← Back to Products
    </a>
@endsection

@section('content')
<div class="max-w-4xl">
    <form action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}" 
          method="POST" 
          enctype="multipart/form-data" 
          class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">Primary Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Product Title / Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="e.g. 400TC Mulmul Cotton Bedsheet & Dohar Set">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Category *</label>
                    <select name="category_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->path ?: $cat->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Material & Fabric Composition</label>
                    <input type="text" name="material" value="{{ old('material', $product->material) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="e.g. 100% Mulmul Cotton, Belgian Linen">
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">Pricing & Inventory</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Numeric Selling Price (₹) *</label>
                    <input type="number" name="numeric_price" value="{{ old('numeric_price', $product->numeric_price ?? 1499) }}" required min="0"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="1499">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Original / Strikethrough Price (₹)</label>
                    <input type="text" name="original_price" value="{{ old('original_price', $product->original_price) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="e.g. ₹1,999">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Badge / Highlight</label>
                    <input type="text" name="badge" value="{{ old('badge', $product->badge) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="e.g. Bestseller, 30% OFF, New">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="in_stock" value="1" {{ old('in_stock', $product->in_stock ?? true) ? 'checked' : '' }} class="rounded text-[#74826B] focus:ring-[#74826B]">
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Available in Stock</span>
                        <span class="text-xs text-slate-500">Visible for immediate purchase</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="is_bestseller" value="1" {{ old('is_bestseller', $product->is_bestseller) ? 'checked' : '' }} class="rounded text-[#74826B] focus:ring-[#74826B]">
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Mark as Best Seller</span>
                        <span class="text-xs text-slate-500">Showcases in top picks</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="rounded text-[#74826B] focus:ring-[#74826B]">
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Feature on Homepage</span>
                        <span class="text-xs text-slate-500">Hero & Trending displays</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100 pb-3">Media & Presentation</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Image Asset URL / Path</label>
                    <input type="text" name="image" value="{{ old('image', $product->image) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                           placeholder="/images/products/bedsheet-set.webp">
                    <p class="text-xs text-slate-400 mt-1">Direct public URL or local asset path</p>
                    
                    <div class="mt-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Or Upload New Image File</label>
                        <input type="file" name="image_file" accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#74826B] file:text-white hover:file:bg-[#636F5A]">
                    </div>
                </div>

                @if($product->image)
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                    <p class="text-xs font-semibold text-slate-500 mb-2">Current Image Preview</p>
                    <img src="{{ $product->image }}" alt="Current Image" class="w-32 h-32 object-cover rounded-lg mx-auto border border-slate-300">
                </div>
                @endif
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Detailed Description</label>
                <textarea name="desc" rows="4" 
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]"
                          placeholder="Silk and linen blend texture with breathable weave and matching pillow covers...">{{ old('desc', $product->desc) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.products.index') }}" 
               class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-sm font-semibold transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-xl bg-[#74826B] hover:bg-[#636F5A] text-white text-sm font-semibold transition-all shadow-md">
                {{ $isEdit ? 'Update Product' : 'Create Product' }}
            </button>
        </div>
    </form>
</div>
@endsection
