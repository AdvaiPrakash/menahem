@props([
    'content' => [],
])

@php
    $sale = $content['CLEARANCE_SALE'] ?? [];
    $products = $sale['products'] ?? [];
@endphp

<section id="clearance-sale" class="py-14 sm:py-20 bg-white border-b border-neutral-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header: Centered on mobile, left-aligned on desktop -->
        <div class="mb-8 sm:mb-10 text-center sm:text-left">
            <x-reveal delay="50">
                <div class="flex flex-col items-center sm:items-start">
                    <!-- Eyebrow Badge (Square-shaped rounded-md) -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-neutral-100 text-[#2B3326] border border-neutral-200 text-xs font-bold uppercase tracking-wider mb-2.5 shadow-2xs">
                        <span class="text-[#74826B]">★</span>
                        <span>{{ $sale['badge'] ?? 'Limited Stock Clearance' }}</span>
                    </div>

                    <!-- Main Section Heading -->
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-[#2B3326] font-serif">
                        {{ $sale['heading'] ?? 'Clearance Sale' }}
                    </h2>

                    <!-- Subtitle -->
                    <p class="text-xs sm:text-sm text-[#5C6654] mt-2 font-medium max-w-2xl leading-relaxed">
                        {{ $sale['subheading'] ?? 'End-of-season archival markdowns on handcrafted home textiles. Enjoy guaranteed percent savings while limited quantities last.' }}
                    </p>
                </div>
            </x-reveal>
        </div>

        <!-- Clearance Products Grid: 2 products in 1 row on mobile, 4 in 1 row on desktop -->
        @if(!empty($products))
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                @foreach($products as $index => $product)
                    @php
                        $id = $product['id'] ?? ($index + 1);
                        $name = $product['name'] ?? 'Clearance Item';
                        $category = $product['category'] ?? 'Home Textiles';
                        $price = $product['price'] ?? '₹999';
                        $numericPrice = $product['numeric_price'] ?? 999;
                        $originalPrice = $product['original_price'] ?? null;
                        $discountPercent = $product['discount_percent'] ?? '40% OFF';
                        $savings = $product['savings'] ?? null;
                        $stockLabel = $product['stock_label'] ?? 'Limited stock';
                        $rating = $product['rating'] ?? 4.9;
                        $reviews = $product['reviews'] ?? 95;
                        $desc = $product['desc'] ?? '';
                        $image = $product['image'] ?? '/images/products/bedsheet-set.webp';
                        $productUrl = route('products.show', $id);
                    @endphp

                    <x-reveal delay="{{ 80 * ($index + 1) }}" class="h-full">
                        <div
                            x-data="{ liked: false }"
                            class="group bg-white rounded-xl p-2.5 sm:p-4 border border-neutral-200/90 hover:border-[#74826B] hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between h-full relative"
                        >
                            <!-- Top Image Container with Badges -->
                            <div>
                                <a href="{{ $productUrl }}" class="relative w-full aspect-[4/3] rounded-lg overflow-hidden bg-neutral-100 flex items-center justify-center block">
                                    <!-- Specified Percent Off Badge (Top Left) -->
                                    <div class="absolute top-2 left-2 sm:top-2.5 sm:left-2.5 z-10 flex flex-col gap-1 items-start pointer-events-none">
                                        <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md text-[10px] sm:text-[11px] font-black uppercase tracking-wider shadow-xs bg-[#2B3326] text-white border border-[#2B3326]">
                                            {{ $discountPercent }}
                                        </span>
                                    </div>

                                    <!-- Scarcity / Clearance Tag (Bottom Right - avoids collision with discount badge) -->
                                    <div class="absolute bottom-2 right-2 sm:bottom-2.5 sm:right-2.5 z-10 pointer-events-none">
                                        <span class="px-1.5 py-0.5 sm:px-2 sm:py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold uppercase tracking-wider bg-white/95 text-[#74826B] border border-neutral-200 backdrop-blur-xs shadow-2xs">
                                            Clearance
                                        </span>
                                    </div>

                                    <!-- Product Image -->
                                    <img
                                        src="{{ asset($image) }}"
                                        alt="{{ $name }}"
                                        loading="lazy"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                        onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                    />

                                    <!-- Fallback Icon -->
                                    <div style="display: none;" class="w-16 h-16 text-[#74826B] hidden items-center justify-center">
                                        <svg class="w-12 h-12" width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </div>
                                </a>

                                <!-- Stock Urgency Indicator -->
                                <div class="mt-2 sm:mt-3 flex items-center gap-1.5 text-[10px] sm:text-[11px] font-semibold text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                    <span class="truncate">{{ $stockLabel }}</span>
                                </div>

                                <!-- Product Title & Category -->
                                <div class="mt-1 text-left">
                                    <a href="{{ $productUrl }}">
                                        <h3 class="text-xs sm:text-base font-bold text-[#2B3326] group-hover:text-[#74826B] transition-colors leading-snug line-clamp-1 font-serif">
                                            {{ $name }}
                                        </h3>
                                    </a>
                                    <p class="text-[11px] sm:text-xs text-[#5C6654] mt-0.5 sm:mt-1 line-clamp-2 min-h-[28px] sm:min-h-[32px] leading-relaxed">
                                        {{ $desc }}
                                    </p>
                                </div>
                            </div>

                            <!-- Bottom Details & Pricing -->
                            <div class="mt-2.5 sm:mt-4 pt-2 sm:pt-3 border-t border-neutral-100 flex flex-col gap-2 sm:gap-3">
                                <!-- Price Row with Specified Percent Off & Savings Tag -->
                                <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-1 sm:gap-2">
                                    <div class="flex items-baseline gap-1.5 sm:gap-2 leading-none">
                                        <span class="text-sm sm:text-xl font-extrabold text-[#2B3326] tracking-tight leading-none">
                                            {{ $price }}
                                        </span>
                                        @if(!empty($originalPrice))
                                            <span class="text-[11px] sm:text-sm text-[#5C6654]/75 font-medium line-through leading-none">
                                                {{ $originalPrice }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!empty($savings))
                                        <span class="px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-[#F6F4EB] text-[#74826B] border border-[#74826B]/20 w-fit">
                                            {{ $savings }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons: Buy / Buy Now + Add to Cart (WhatsApp button removed per requirement) -->
                                <div class="flex items-center justify-between gap-1.5 sm:gap-2">
                                    <!-- Buy / Buy Now Button (Square-shaped rounded-md) -->
                                    <a
                                        href="{{ $productUrl }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-md bg-[#74826B] hover:bg-[#636F5A] text-white text-[11px] sm:text-xs font-bold tracking-wide transition-all duration-200 shadow-2xs hover:shadow-md whitespace-nowrap"
                                    >
                                        <span class="sm:hidden">Buy</span>
                                        <span class="hidden sm:inline">Buy Now</span>
                                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                    </a>

                                    <!-- Add to Cart (Dispatches Alpine Event) -->
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
                                        aria-label="Add {{ $name }} to shopping bag"
                                        title="Add to shopping bag"
                                        class="w-7 h-7 sm:w-9 sm:h-9 rounded-md border border-neutral-200 bg-white text-[#2B3326] hover:bg-neutral-100 hover:border-[#74826B] flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 cursor-pointer flex-shrink-0"
                                    >
                                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </x-reveal>
                @endforeach
            </div>
        @endif

    </div>
</section>
