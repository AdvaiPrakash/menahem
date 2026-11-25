@extends('admin.layouts.app')

@section('title', $isEdit ? 'Edit Product' : 'Add New Product')
@section('header_title', $isEdit ? 'Edit Product: ' . $product->name : 'Create New Product')

@section('header_actions')
    <a href="{{ route('admin.products.index') }}" 
       class="inline-flex items-center gap-2 px-3 py-2 rounded-none border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-semibold uppercase tracking-wider transition-colors">
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

        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-500 border-b border-neutral-200 pb-3">Primary Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Product Title / Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="e.g. 400TC Mulmul Cotton Bedsheet & Dohar Set">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Category *</label>
                    <select name="category_id" class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->path ?: $cat->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Material & Fabric Composition</label>
                    <input type="text" name="material" value="{{ old('material', $product->material) }}"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="e.g. 100% Mulmul Cotton, Belgian Linen">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">SKU (Stock Keeping Unit)</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 font-mono text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="e.g. MNH-BED-001 (Auto-generated if empty)">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">HSN Code (GST Invoice)</label>
                    <input type="text" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code ?? '6304') }}"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 font-mono text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="e.g. 6304 or 6302">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">GST Rate (%)</label>
                    <input type="number" step="0.01" name="gst_rate" value="{{ old('gst_rate', $product->gst_rate ?? 12.0) }}"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="12.00">
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-500 border-b border-neutral-200 pb-3">Pricing & Inventory</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Selling Price (₹) *</label>
                    <input type="number" name="numeric_price" value="{{ old('numeric_price', $product->numeric_price ?? 1499) }}" required min="0"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="1499">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Original Price (₹)</label>
                    <input type="text" name="original_price" value="{{ old('original_price', $product->original_price) }}"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="e.g. ₹1,999">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Current Stock Qty *</label>
                    <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 50) }}" required min="0"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm font-semibold focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="50">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Low Stock Alert (&le;)</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}" min="1"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="10">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Badge / Highlight</label>
                <input type="text" name="badge" value="{{ old('badge', $product->badge) }}"
                       class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                       placeholder="e.g. Bestseller, 30% OFF, New">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-neutral-200">
                <label class="flex items-center gap-3 p-3 rounded-none border border-neutral-200 hover:bg-neutral-50 cursor-pointer">
                    <input type="checkbox" name="in_stock" value="1" {{ old('in_stock', $product->in_stock ?? true) ? 'checked' : '' }} class="rounded-none text-neutral-900 focus:ring-neutral-900 border-neutral-300">
                    <div>
                        <span class="text-sm font-semibold text-neutral-800 block">Available in Stock</span>
                        <span class="text-xs text-neutral-500">Visible for immediate purchase</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-none border border-neutral-200 hover:bg-neutral-50 cursor-pointer">
                    <input type="checkbox" name="is_bestseller" value="1" {{ old('is_bestseller', $product->is_bestseller) ? 'checked' : '' }} class="rounded-none text-neutral-900 focus:ring-neutral-900 border-neutral-300">
                    <div>
                        <span class="text-sm font-semibold text-neutral-800 block">Mark as Best Seller</span>
                        <span class="text-xs text-neutral-500">Showcases in top picks</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-3 rounded-none border border-neutral-200 hover:bg-neutral-50 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="rounded-none text-neutral-900 focus:ring-neutral-900 border-neutral-300">
                    <div>
                        <span class="text-sm font-semibold text-neutral-800 block">Feature on Homepage</span>
                        <span class="text-xs text-neutral-500">Hero & Trending displays</span>
                    </div>
                </label>
            </div>
        </div>

        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-6">
            <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-500 border-b border-neutral-200 pb-3">Media &amp; Product Gallery</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Primary Image Asset URL / Path</label>
                    <input type="text" name="image" value="{{ old('image', $product->image) }}"
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                           placeholder="/images/products/bedsheet-set.webp">
                    <p class="text-xs text-neutral-400 mt-1">Direct public URL or local asset path</p>
                    
                    <div class="mt-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Or Upload New Primary Image</label>
                        <input type="file" name="image_file" accept="image/*"
                               class="w-full text-xs text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-none file:border file:border-neutral-300 file:text-xs file:font-semibold file:bg-neutral-900 file:text-white hover:file:bg-black">
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Upload Additional Gallery Images (Multiple)</label>
                        <input type="file" name="gallery_files[]" accept="image/*" multiple
                               class="w-full text-xs text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-none file:border file:border-neutral-300 file:text-xs file:font-semibold file:bg-neutral-900 file:text-white hover:file:bg-black">
                        <p class="text-[11px] text-neutral-400 mt-1">Select one or more photos (JPG, PNG, WebP, GIF, max 5MB each)</p>
                    </div>
                </div>

                @if($product->image)
                <div class="p-3 bg-neutral-50 rounded-none border border-neutral-200 text-center">
                    <p class="text-xs font-semibold text-neutral-500 mb-2">Current Primary Image</p>
                    <img src="{{ $product->image }}" alt="Current Image" class="w-32 h-32 object-cover rounded-none mx-auto border border-neutral-300">
                </div>
                @endif
            </div>

            @php
                $existingImages = $isEdit ? $product->allImages() : [];
            @endphp
            @if(!empty($existingImages))
                <div class="pt-4 border-t border-neutral-200 space-y-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-neutral-700">
                        Manage Uploaded Gallery Images ({{ count($existingImages) }})
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($existingImages as $idx => $imgPath)
                            <div class="p-3 border border-neutral-200 bg-neutral-50 space-y-2.5">
                                <div class="aspect-[4/3] bg-white border border-neutral-200 overflow-hidden flex items-center justify-center">
                                    <img src="{{ $imgPath }}" alt="Product image {{ $idx + 1 }}" class="w-full h-full object-contain">
                                </div>

                                <div class="flex items-center justify-between text-xs">
                                    <label class="inline-flex items-center gap-1.5 font-semibold text-neutral-800 cursor-pointer">
                                        <input type="radio" name="primary_image" value="{{ $imgPath }}"
                                               {{ ($product->image === $imgPath) ? 'checked' : '' }}
                                               class="text-neutral-900 focus:ring-neutral-900">
                                        <span>Primary</span>
                                    </label>

                                    <label class="inline-flex items-center gap-1.5 text-rose-700 font-semibold cursor-pointer">
                                        <input type="checkbox" name="remove_gallery[]" value="{{ $imgPath }}" class="border-rose-300 text-rose-700">
                                        <span>Remove</span>
                                    </label>
                                </div>

                                <div class="grid grid-cols-2 gap-2 items-center">
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-neutral-500">Order</label>
                                        <input type="number" min="0" name="gallery_order[{{ $imgPath }}]" value="{{ $idx }}"
                                               class="w-full px-2 py-1 text-xs border border-neutral-300 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase text-neutral-500">Replace</label>
                                        <input type="file" name="replace_gallery_files[{{ $idx }}]" accept="image/*"
                                               class="w-full text-[10px] text-neutral-500">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Detailed Description</label>
                <textarea name="desc" rows="4" 
                          class="w-full px-4 py-2.5 rounded-none border border-neutral-300 text-sm focus:outline-none focus:border-neutral-900 bg-white"
                          placeholder="Silk and linen blend texture with breathable weave and matching pillow covers...">{{ old('desc', $product->desc) }}</textarea>
            </div>
        </div>

        <!-- Product Highlights, Fabric Details, Wash Care & Shipping -->
        @php
            $initialHighlights = old('highlights', is_array($product->highlights) ? $product->highlights : []);
            $fabricDetails = is_array($product->fabric_details) ? $product->fabric_details : [];
        @endphp
        <div class="bg-white p-6 rounded-none border border-neutral-200 shadow-none space-y-6"
             x-data="{
                highlights: @js(array_values($initialHighlights)),
                addHighlight() { this.highlights.push(''); },
                removeHighlight(i) { this.highlights.splice(i, 1); },
                moveHighlight(i, dir) {
                    const target = i + dir;
                    if (target < 0 || target >= this.highlights.length) return;
                    const copy = [...this.highlights];
                    [copy[i], copy[target]] = [copy[target], copy[i]];
                    this.highlights = copy;
                }
             }">
            <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-500 border-b border-neutral-200 pb-3">
                Product Information &amp; Care (Per Product)
            </h2>

            <!-- Highlights -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700">Product Highlights</label>
                        <p class="text-[11px] text-neutral-500">Only highlights configured here will display on this product's page.</p>
                    </div>
                    <button type="button" @click="addHighlight()"
                            class="px-3 py-1.5 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                        + Add Highlight
                    </button>
                </div>

                <div class="space-y-2">
                    <template x-for="(hl, index) in highlights" :key="index">
                        <div class="flex items-center gap-2">
                            <input type="text" name="highlights[]" x-model="highlights[index]"
                                   placeholder="e.g. 100% Breathable Weave"
                                   class="flex-1 px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                            <button type="button" @click="moveHighlight(index, -1)" :disabled="index === 0"
                                    class="px-2 py-2 border border-neutral-300 text-xs disabled:opacity-30 cursor-pointer" title="Move up">&uarr;</button>
                            <button type="button" @click="moveHighlight(index, 1)" :disabled="index === highlights.length - 1"
                                    class="px-2 py-2 border border-neutral-300 text-xs disabled:opacity-30 cursor-pointer" title="Move down">&darr;</button>
                            <button type="button" @click="removeHighlight(index)"
                                    class="px-2.5 py-2 border border-rose-200 bg-rose-50 text-rose-700 text-xs font-bold cursor-pointer">&times;</button>
                        </div>
                    </template>
                    <p x-show="highlights.length === 0" class="text-xs text-neutral-400 italic">No highlights configured for this product.</p>
                </div>
            </div>

            <!-- Fabric Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-neutral-200">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Fabric Details Summary</label>
                    <textarea name="fabric_summary" rows="2"
                              class="w-full px-4 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900"
                              placeholder="Describe fabric texture, weave, and finish...">{{ old('fabric_summary', $fabricDetails['summary'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Weave Density / Weight</label>
                    <input type="text" name="weave_density" value="{{ old('weave_density', $fabricDetails['weave_density'] ?? '') }}"
                           class="w-full px-4 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900"
                           placeholder="e.g. 400+ Thread Count / 220 GSM">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Craft Origin</label>
                    <input type="text" name="craft_origin" value="{{ old('craft_origin', $fabricDetails['craft_origin'] ?? '') }}"
                           class="w-full px-4 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900"
                           placeholder="e.g. Handcrafted in Karur, India">
                </div>
            </div>

            <!-- Wash & Care -->
            <div class="pt-4 border-t border-neutral-200">
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Wash &amp; Care Instructions</label>
                <textarea name="wash_care" rows="3"
                          class="w-full px-4 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900"
                          placeholder="One instruction per line (e.g. Machine wash cold gentle cycle)...">{{ old('wash_care', $product->wash_care) }}</textarea>
            </div>

            <!-- Shipping & Returns -->
            <div class="pt-4 border-t border-neutral-200">
                <label class="block text-xs font-bold uppercase tracking-wider text-neutral-700 mb-1.5">Shipping &amp; Returns</label>
                <textarea name="shipping_returns" rows="3"
                          class="w-full px-4 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900"
                          placeholder="Product-specific dispatch timeline and return policy...">{{ old('shipping_returns', $product->shipping_returns) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.products.index') }}" 
               class="px-5 py-2.5 rounded-none border border-neutral-300 text-neutral-600 hover:bg-neutral-100 text-sm font-semibold transition-colors">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 rounded-none bg-neutral-900 hover:bg-black text-white text-sm font-semibold transition-all shadow-none">
                {{ $isEdit ? 'Update Product' : 'Create Product' }}
            </button>
        </div>
    </form>
</div>
@endsection
