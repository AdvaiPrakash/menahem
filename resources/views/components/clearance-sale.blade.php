@props([
    'content' => [],
])

@php
    $sale = $content['CLEARANCE_SALE'] ?? [];
    $products = $sale['products'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $phone = preg_replace('/[^0-9]/', '', $brand['whatsapp_number'] ?? '919876543210');
@endphp

<section id="clearance-sale" class="py-16 sm:py-20 bg-neutral-50/70 border-b border-neutral-200 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header: Centered on mobile, left-aligned on desktop -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-10 sm:mb-12">
            <x-reveal delay="50" class="w-full sm:w-auto">
                <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                    <!-- Eyebrow Badge (Square-shaped rounded-md) -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white text-[#2B3326] border border-neutral-200 text-xs font-bold uppercase tracking-wider mb-2.5 shadow-2xs mx-auto sm:mx-0">
                        <span class="text-[#74826B]">★</span>
                        <span>{{ $sale['badge'] ?? 'Limited Stock Clearance' }}</span>
                    </div>

                    <!-- Main Section Heading -->
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-[#2B3326] font-serif text-center sm:text-left">
                        {{ $sale['heading'] ?? 'Clearance Sale' }}
                    </h2>

                    <!-- Subtitle -->
                    <p class="text-xs sm:text-sm text-[#5C6654] mt-2 font-medium max-w-2xl text-center sm:text-left mx-auto sm:mx-0 leading-relaxed">
                        {{ $sale['subheading'] ?? 'End-of-season archival markdowns on handcrafted home textiles. Enjoy guaranteed percent savings while limited quantities last.' }}
                    </p>
                </div>
            </x-reveal>

            <!-- Desktop View All Button (Square-shaped rounded-md) -->
            @if(!empty($sale['view_all_url']))
                <x-reveal delay="100" class="flex-shrink-0 flex justify-center sm:justify-end">
                    <a
                        href="{{ url($sale['view_all_url']) }}"
                        class="group inline-flex items-center gap-2 px-4 py-2 rounded-md bg-white hover:bg-[#2B3326] text-[#2B3326] hover:text-white border border-neutral-200 hover:border-[#2B3326] text-xs font-bold uppercase tracking-wider transition-all duration-300 shadow-2xs"
                    >
                        <span>{{ $sale['view_all_label'] ?? 'Explore All Clearance Deals' }}</span>
                        <span class="transition-transform duration-300 group-hover:translate-x-1 font-mono">→</span>
                    </a>
                </x-reveal>
            @endif
        </div>

        <!-- Clearance Products Grid: 1 col on mobile, 2 col on tablet, 4 col on desktop -->
        @if(!empty($products))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
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
                        $whatsappMsg = urlencode("Hello Menahem! I would like to order the Clearance Sale item: {$name} ({$discountPercent} at {$price}). Is it still available?");
                        $productWhatsappUrl = "https://wa.me/{$phone}?text={$whatsappMsg}";
                    @endphp

                    <x-reveal delay="{{ 80 * ($index + 1) }}" class="h-full">
                        <div
                            x-data="{ liked: false }"
                            class="group bg-white rounded-xl p-4 border border-neutral-200/90 hover:border-[#74826B] hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between h-full relative"
                        >
                            <!-- Top Image Container with Badges -->
                            <div>
                                <a href="{{ $productUrl }}" class="relative w-full aspect-[4/3] rounded-lg overflow-hidden bg-neutral-100 flex items-center justify-center block">
                                    <!-- Specified Percent Off Badge (Square-shaped rounded-md with prominent contrast) -->
                                    <div class="absolute top-2.5 left-2.5 z-10 flex flex-col gap-1 items-start">
                                        <span class="px-2.5 py-1 rounded-md text-[11px] font-black uppercase tracking-wider shadow-xs bg-[#2B3326] text-white border border-[#2B3326]">
                                            {{ $discountPercent }}
                                        </span>
                                    </div>

                                    <!-- Scarcity / Clearance Tag on Top Right -->
                                    <div class="absolute top-2.5 right-2.5 z-10">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-white/95 text-[#74826B] border border-neutral-200 backdrop-blur-xs shadow-2xs">
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
                                <div class="mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                    <span>{{ $stockLabel }}</span>
                                </div>

                                <!-- Product Title & Category -->
                                <div class="mt-1 text-left">
                                    <a href="{{ $productUrl }}">
                                        <h3 class="text-base font-bold text-[#2B3326] group-hover:text-[#74826B] transition-colors leading-snug line-clamp-1 font-serif">
                                            {{ $name }}
                                        </h3>
                                    </a>
                                    <p class="text-xs text-[#5C6654] mt-1 line-clamp-2 min-h-[32px] leading-relaxed">
                                        {{ $desc }}
                                    </p>
                                </div>
                            </div>

                            <!-- Bottom Details & Pricing -->
                            <div class="mt-4 pt-3 border-t border-neutral-100 flex flex-col gap-3">
                                <!-- Price Row with Specified Percent Off & Savings Tag -->
                                <div class="flex items-baseline justify-between gap-2">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-lg sm:text-xl font-extrabold text-[#2B3326] tracking-tight leading-none">
                                            {{ $price }}
                                        </span>
                                        @if(!empty($originalPrice))
                                            <span class="text-xs sm:text-sm text-[#5C6654]/75 font-medium line-through leading-none">
                                                {{ $originalPrice }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!empty($savings))
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[#F6F4EB] text-[#74826B] border border-[#74826B]/20">
                                            {{ $savings }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Action Buttons: Buy Now + Quick Add + Wishlist -->
                                <div class="flex items-center justify-between gap-2">
                                    <!-- Buy Now Button (Square-shaped rounded-md) -->
                                    <a
                                        href="{{ $productUrl }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-md bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-bold tracking-wide transition-all duration-200 shadow-2xs hover:shadow-md"
                                    >
                                        <span>Buy Now</span>
                                        <svg class="w-3.5 h-3.5 text-white" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        class="w-9 h-9 rounded-md border border-neutral-200 bg-white text-[#2B3326] hover:bg-neutral-100 hover:border-[#74826B] flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 cursor-pointer flex-shrink-0"
                                    >
                                        <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </button>

                                    <!-- WhatsApp Order Button -->
                                    <a
                                        href="{{ $productWhatsappUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="Order on WhatsApp"
                                        title="Order via WhatsApp"
                                        class="w-9 h-9 rounded-md border border-[#25D366]/40 bg-white text-[#25D366] hover:bg-[#25D366] hover:text-white flex items-center justify-center transition-all duration-200 shadow-2xs hover:scale-105 active:scale-95 flex-shrink-0"
                                    >
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.746.953 3.71 1.456 5.711 1.457h.004c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </x-reveal>
                @endforeach
            </div>

            <!-- Mobile View All Button (Centered on mobile, hidden on sm+) -->
            @if(!empty($sale['view_all_url']))
                <div class="mt-8 flex justify-center sm:hidden">
                    <a
                        href="{{ url($sale['view_all_url']) }}"
                        class="group inline-flex items-center gap-2 px-6 py-2.5 rounded-md bg-[#2B3326] text-white text-xs font-bold uppercase tracking-wider transition-all duration-300 shadow-sm"
                    >
                        <span>{{ $sale['view_all_label'] ?? 'Explore All Clearance Deals' }}</span>
                        <span class="transition-transform duration-300 group-hover:translate-x-1 font-mono">→</span>
                    </a>
                </div>
            @endif
        @endif

    </div>
</section>
