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
    class="group bg-white rounded-lg p-3.5 sm:p-4 border border-[#D7C7B2] hover:border-[#74826B] hover:shadow-xl hover:shadow-[#74826B]/10 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between h-full relative"
    data-category="{{ strtolower($product['category_slug'] ?? $category) }}"
    data-material="{{ strtolower($material ?? '') }}"
    data-price="{{ $numericPrice }}"
    data-name="{{ strtolower($name) }}"
>
    <!-- Top Image Container with Soft Rounded Corners -->
    <a href="{{ $productUrl }}" class="relative w-full aspect-[4/3] rounded-md overflow-hidden bg-[#F3EFE6] flex items-center justify-center block">
        <!-- Optional Floating Badge (Linen & Sage Palette) -->
        @if($badge)
            <div class="absolute top-3 left-3 z-10">
                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider shadow-2xs {{ $badge === 'Sale' ? 'bg-[#E8E2D5] text-[#2B3326] border border-[#D7C7B2]' : ($badge === 'New' ? 'bg-[#B5C1A5] text-[#2B3326] border border-[#74826B]/60' : 'bg-[#D7C7B2] text-[#2B3326] border border-[#74826B]/40') }}">
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
        <div style="display: none;" class="w-16 h-16 text-[#74826B] hidden items-center justify-center">
            <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
    </a>

    <!-- Product Text Details -->
    <div class="mt-3.5 flex flex-col flex-grow text-left">
        <a href="{{ $productUrl }}">
            <h3 class="text-base sm:text-[17px] font-bold text-[#2B3326] group-hover:text-[#74826B] transition-colors leading-snug line-clamp-1">
                {{ $name }}
            </h3>
        </a>
        <p class="text-xs sm:text-[13px] text-[#5C6654] mt-1.5 leading-relaxed line-clamp-2 min-h-[36px] font-medium">
            {{ $desc }}
        </p>

        <!-- Formal & Elegant Price Row -->
        <div class="mt-3.5 flex items-baseline justify-between gap-2">
            <!-- Left: Parallel Prices -->
            <div class="flex items-baseline gap-2.5 leading-none">
                <span class="text-lg sm:text-[19px] font-extrabold text-[#2B3326] tracking-tight leading-none">
                    {{ $price }}
                </span>
                @if(!empty($originalPrice))
                    <span class="text-[13px] sm:text-sm text-[#5C6654]/70 font-medium leading-none line-through" style="text-decoration: line-through; text-decoration-thickness: 1.5px; text-decoration-color: #5C6654;">
                        {{ $originalPrice }}
                    </span>
                @endif
            </div>

            <!-- Right: Rating Stars & Score (in Earthy Olive #74826B) -->
            <div class="flex items-center gap-1 flex-shrink-0 leading-none">
                <div class="flex text-[#74826B] leading-none">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-3.5 h-3.5 fill-current" width="14" height="14" viewBox="0 0 20 20">
                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                        </svg>
                    @endfor
                </div>
                <span class="text-xs font-bold text-[#2B3326] leading-none ml-0.5">
                    {{ number_format($rating, 1) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Buy Now Button & Action Buttons -->
    <div class="mt-4 pt-3 border-t border-[#D7C7B2]/70 flex items-center justify-between gap-2">
        <!-- Left: Buy Now Button -> navigates to Detail Page -->
        <a
            href="{{ $productUrl }}"
            class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-4.5 py-2 rounded-md bg-[#74826B] hover:bg-[#636F5A] text-white text-xs sm:text-sm font-bold tracking-wide transition-all duration-200 shadow-2xs hover:shadow-md transform hover:scale-[1.02] active:scale-98"
        >
            <span>Buy Now</span>
            <svg class="w-3.5 h-3.5 text-white" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>

        <!-- Right: Action Icons (Wishlist Heart + Add to Cart Shopping Bag) -->
        <div class="flex items-center gap-2">
            <!-- Wishlist Heart Button -->
            <button
                type="button"
                @click="liked = !liked"
                :class="liked ? 'bg-[#E8E2D5] text-[#74826B] border-[#74826B]' : 'bg-white text-gray-400 border-[#D7C7B2] hover:text-[#74826B] hover:border-[#74826B]'"
                aria-label="Add to wishlist"
                title="Add to wishlist"
                class="w-9 h-9 rounded-md border flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 cursor-pointer"
            >
                <svg
                    class="w-4 h-4 transition-transform duration-200"
                    width="16"
                    height="16"
                    :class="liked ? 'scale-110 fill-[#74826B]' : 'fill-none'"
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
                class="w-9 h-9 rounded-md border border-[#D7C7B2] bg-white text-[#2B3326] hover:bg-[#E8E2D5] hover:border-[#74826B] hover:text-[#2B3326] flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 cursor-pointer"
            >
                <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </button>
        </div>

    </div>
</div>
