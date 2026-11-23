@extends('layouts.app')

@section('title', 'My Wishlist & Favorites · Menahem Luxury Home Essentials')

@section('content')
<div class="min-h-screen bg-[#FBFBF9] pt-24 pb-16" 
     x-data="{
        items: {{ json_encode($products->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => $p->numeric_price,
            'formatted_price' => $p->formatted_price,
            'original_price' => $p->original_price,
            'image' => $p->image,
            'category' => $p->category,
            'in_stock' => $p->in_stock,
            'material' => $p->material,
        ])->values()) }},
        removeFromWishlist(id) {
            fetch('{{ route('wishlist.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: id })
            }).then(r => r.json()).then(data => {
                this.items = this.items.filter(i => i.id !== id);
                window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: { count: this.items.length } }));
            }).catch(() => {});
        },
        addToCart(product) {
            window.dispatchEvent(new CustomEvent('add-to-cart', {
                detail: {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    category: product.category,
                    quantity: 1
                }
            }));
        }
     }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="border-b border-[#2B3326]/10 pb-6 mb-8 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4">
            <div>
                <span class="text-[11px] font-bold tracking-widest uppercase text-[#74826B] block mb-1">Saved Curations</span>
                <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#1C241B]">My Wishlist</h1>
                <p class="text-xs text-neutral-500 mt-1">Keep track of your favorite luxury linens, dohars and home accents</p>
            </div>
            <div>
                <span class="text-xs font-semibold px-3 py-1 bg-white border border-neutral-200 text-neutral-800">
                    <span x-text="items.length"></span> Saved Items
                </span>
            </div>
        </div>

        <!-- Wishlist Empty State -->
        <template x-if="items.length === 0">
            <div class="bg-white border border-neutral-200 p-12 text-center max-w-lg mx-auto shadow-xs">
                <div class="w-16 h-16 mx-auto mb-4 bg-neutral-100 flex items-center justify-center text-neutral-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <h3 class="text-lg font-serif font-bold text-[#1C241B] mb-2">Your Wishlist is Empty</h3>
                <p class="text-xs text-neutral-500 mb-6 leading-relaxed">
                    Explore our luxury home essentials, fine cotton bedsheets, and handcrafted curtains to save your favorite pieces.
                </p>
                <a href="{{ route('products.index') }}" 
                   class="inline-block px-6 py-3 bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-bold uppercase tracking-wider transition-all">
                    Browse Collection
                </a>
            </div>
        </template>

        <!-- Wishlist Grid -->
        <template x-if="items.length > 0">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <template x-for="item in items" :key="item.id">
                    <div class="bg-white border border-neutral-200 flex flex-col justify-between group hover:border-[#74826B]/50 transition-all duration-200">
                        <div class="relative">
                            <a :href="'/products/' + item.id" class="block aspect-square overflow-hidden bg-neutral-100">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </a>
                            <button type="button" 
                                    @click="removeFromWishlist(item.id)" 
                                    class="absolute top-2 right-2 w-8 h-8 bg-white/90 hover:bg-white text-neutral-600 hover:text-rose-600 flex items-center justify-center border border-neutral-200 shadow-xs"
                                    title="Remove from Wishlist">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 block" x-text="item.category"></span>
                                <h3 class="font-serif font-bold text-sm text-[#1C241B] mt-1 line-clamp-2">
                                    <a :href="'/products/' + item.id" x-text="item.name" class="hover:underline"></a>
                                </h3>
                                <p class="text-xs text-neutral-500 mt-1" x-text="item.material || 'Artisanal Cotton'"></p>
                            </div>

                            <div class="mt-4 pt-3 border-t border-neutral-100">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-base font-bold text-[#1C241B]" x-text="item.formatted_price || ('₹' + item.price)"></span>
                                    <template x-if="item.original_price">
                                        <span class="text-xs text-neutral-400 line-through" x-text="item.original_price"></span>
                                    </template>
                                </div>

                                <button type="button" 
                                        @click="addToCart(item)" 
                                        class="w-full py-2 bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-semibold uppercase tracking-wider transition-colors flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Add to Cart</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
@endsection
