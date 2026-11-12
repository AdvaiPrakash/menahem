@props([
    'product' => [],
    'active' => false,
])

@php
    $id = $product['id'] ?? 1;
    $name = $product['name'] ?? 'Home Essential';
    $category = $product['category'] ?? 'Living';
    $price = $product['price'] ?? '₹999';
    $numericPrice = $product['numeric_price'] ?? 999;
    $originalPrice = $product['original_price'] ?? null;
    $rating = $product['rating'] ?? 4.9;
    $reviews = $product['reviews'] ?? 112;
    $desc = $product['desc'] ?? 'Silk and linen blend texture with breathable weave and matching finishes.';
    $image = $product['image'] ?? '/images/categories/bedsheets.webp';
    $badge = $product['badge'] ?? null;
    $material = $product['material'] ?? null;
    $isActive = $active || ($badge === 'Bestseller');
    $productUrl = route('products.show', $id);
@endphp

<div
    x-data="{ liked: false }"
    class="group bg-white rounded-3xl p-3.5 sm:p-4 border border-[#162B1F]/10 hover:border-[#2E7D5B]/50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-full relative"
    data-category="{{ strtolower($product['category_slug'] ?? $category) }}"
    data-material="{{ strtolower($material ?? '') }}"
    data-price="{{ $numericPrice }}"
    data-name="{{ strtolower($name) }}"
>
    <!-- Top Image Container with Soft Rounded Corners -->
    <a href="{{ $productUrl }}" class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden bg-[#EAF4EE] flex items-center justify-center block">
        <!-- Optional Floating Badge -->
        @if($badge)
            <div class="absolute top-3 left-3 z-10">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-xs {{ $badge === 'Sale' ? 'bg-[#162B1F] text-white' : ($badge === 'New' ? 'bg-[#2E7D5B] text-white' : 'bg-[#102016] text-[#B5DFB2]') }}">
                    {{ $badge }}
                </span>
            </div>
        @endif

        <!-- Product Image -->
        <img
            src="{{ asset($image) }}"
            alt="{{ $name }}"
            loading="lazy"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
        />

        <!-- Fallback vector icon -->
        <div style="display: none;" class="w-16 h-16 text-[#2E7D5B] hidden items-center justify-center">
            <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
    </a>

    <!-- Product Text Details -->
    <div class="mt-3.5 flex flex-col flex-grow text-left">
        <a href="{{ $productUrl }}">
            <h3 class="text-base sm:text-[17px] font-bold text-[#14281E] group-hover:text-[#2E7D5B] transition-colors leading-snug line-clamp-1">
                {{ $name }}
            </h3>
        </a>
        <p class="text-xs sm:text-[13px] text-[#4E6858] mt-1.5 leading-relaxed line-clamp-2 min-h-[36px]">
            {{ $desc }}
        </p>

        <!-- Formal & Elegant Price Row -->
        <div class="mt-3.5 flex items-baseline justify-between gap-2">
            <!-- Left: Parallel Prices -->
            <div class="flex items-baseline gap-2.5 leading-none">
                <span class="text-lg sm:text-[19px] font-extrabold text-[#14281E] tracking-tight leading-none">
                    {{ $price }}
                </span>
                @if(!empty($originalPrice))
                    <span class="text-[13px] sm:text-sm text-[#4E6858]/75 font-medium leading-none line-through" style="text-decoration: line-through; text-decoration-thickness: 1.5px; text-decoration-color: #4E6858;">
                        {{ $originalPrice }}
                    </span>
                @endif
            </div>

            <!-- Right: Rating Stars & Score (in Rose #2E7D5B) -->
            <div class="flex items-center gap-1 flex-shrink-0 leading-none">
                <div class="flex text-[#2E7D5B] leading-none">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-3.5 h-3.5 fill-current" width="14" height="14" viewBox="0 0 20 20">
                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                        </svg>
                    @endfor
                </div>
                <span class="text-xs font-bold text-[#14281E] leading-none ml-0.5">
                    {{ number_format($rating, 1) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Buy Now Button (Links to Detail Page) & Action Buttons -->
    <div class="mt-4 pt-3 border-t border-[#162B1F]/10 flex items-center justify-between gap-2">
        <!-- Left: Buy Now Button -> navigates to Detail Page -->
        <a
            href="{{ $productUrl }}"
            class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-4.5 py-2 rounded-xl bg-[#162B1F] hover:bg-[#223F2E] text-white text-xs sm:text-sm font-bold tracking-wide transition-all duration-200 shadow-xs hover:shadow-md transform hover:scale-[1.02] active:scale-98"
        >
            <span>Buy Now</span>
            <svg class="w-3.5 h-3.5 text-white/90" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>

        <!-- Right: Action Icons (Wishlist Heart + Add to Cart Shopping Bag) -->
        <div class="flex items-center gap-2">
            <!-- Wishlist Heart Button -->
            <button
                type="button"
                @click="liked = !liked"
                :class="liked ? 'bg-pink-50 text-[#2E7D5B] border-[#B5DFB2]' : 'bg-white text-gray-400 border-gray-200 hover:text-[#2E7D5B] hover:border-[#B5DFB2]'"
                aria-label="Add to wishlist"
                title="Add to wishlist"
                class="w-9 h-9 rounded-full border flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 cursor-pointer"
            >
                <svg
                    class="w-4 h-4 transition-transform duration-200"
                    width="16"
                    height="16"
                    :class="liked ? 'scale-110 fill-[#2E7D5B]' : 'fill-none'"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                    />
                </svg>
            </button>

            <!-- Add to Cart / Shopping Bag Button (Triggers Cart Drawer) -->
            <button
                type="button"
                @click="$dispatch('add-to-cart', {
                    id: {{ $id }},
                    name: '{{ addslashes($name) }}',
                    price: {{ $numericPrice }},
                    quantity: 1,
                    image: '{{ asset($image) }}',
                    category: '{{ addslashes($category) }}'
                })"
                aria-label="Add to cart"
                title="Add to cart"
                class="w-9 h-9 rounded-full border border-[#162B1F]/20 bg-white text-[#14281E] hover:bg-[#162B1F] hover:text-white hover:border-[#162B1F] flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 cursor-pointer"
            >
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </button>
        </div>

    </div>
</div>
