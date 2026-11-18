@props([
    'content' => [],
    'product' => [],
    'relatedProducts' => [],
])

@php
    $id = $product['id'] ?? 1;
    $name = $product['name'] ?? 'Luxury Home Essential';
    $category = $product['category'] ?? 'Living';
    $categorySlug = $product['category_slug'] ?? 'living';
    $material = $product['material'] ?? 'Pure Breathable Cotton';
    $price = $product['price'] ?? '₹1,499';
    $numericPrice = $product['numeric_price'] ?? 1499;
    $originalPrice = $product['original_price'] ?? '₹1,999';
    $rating = $product['rating'] ?? 4.9;
    $reviews = $product['reviews'] ?? 148;
    $badge = $product['badge'] ?? 'Bestseller';
    $desc = $product['desc'] ?? 'Artisanal texture crafted with premium natural fibers, designed to bring understated luxury and everyday comfort to your home.';
    $image = $product['image'] ?? '/images/categories/bedsheets.webp';

    $isCurtain = str_contains(strtolower($name), 'curtain')
        || str_contains(strtolower($name), 'drape')
        || str_contains(strtolower($category), 'curtain')
        || str_contains(strtolower($categorySlug), 'curtain');

    $brand = $content['BRAND'] ?? [];
    $whatsappMsg = urlencode("Hello Menahem! I would like to order: {$name} ({$price}). Please assist me with delivery and payment.");
    $whatsappUrl = "https://wa.me/919876543210?text={$whatsappMsg}";

    // Curate 4 distinct high-resolution gallery images
    $gallery = array_values(array_unique([
        $image,
        '/images/hero/hero-lifestyle.webp',
        '/images/categories/curtains.webp',
        '/images/categories/bedsheets.webp',
        '/images/categories/dohars-quilts.webp',
        '/images/categories/towels.webp',
    ]));
    $gallery = array_slice($gallery, 0, 4);
@endphp

<x-layouts.app :content="$content" :title="$name . ' · Menahem Luxury Home Essentials'">
    <x-site-shell :content="$content">
        <main
            x-data="{
                activeImage: '{{ asset($gallery[0]) }}',
                activeIndex: 0,
                gallery: [
                    @foreach($gallery as $thumb)
                        '{{ asset($thumb) }}',
                    @endforeach
                ],
                quantity: 1,
                selectedTab: 'details',
                isCurtain: {{ $isCurtain ? 'true' : 'false' }},
                sizeMode: 'standard', // 'standard' | 'custom'
                curtainUnit: 'inches',
                curtainBreadth: 48,
                curtainLength: 84,
                curtainHeader: 'Antique Brass Eyelets',
                curtainRoom: '',

                nextImage() {
                    this.activeIndex = (this.activeIndex + 1) % this.gallery.length;
                    this.activeImage = this.gallery[this.activeIndex];
                },
                prevImage() {
                    this.activeIndex = (this.activeIndex - 1 + this.gallery.length) % this.gallery.length;
                    this.activeImage = this.gallery[this.activeIndex];
                },
                selectImage(idx) {
                    this.activeIndex = idx;
                    this.activeImage = this.gallery[idx];
                },
                get lengthInInches() {
                    return this.curtainUnit === 'feet' ? (this.curtainLength * 12) : this.curtainLength;
                },
                get calculatedUnitPrice() {
                    let base = {{ $numericPrice }};
                    if (this.isCurtain && this.sizeMode === 'custom') {
                        if (this.lengthInInches > 96) base += 250;
                    }
                    return base;
                },
                get totalFormatted() {
                    return '₹' + (this.calculatedUnitPrice * this.quantity).toLocaleString();
                },
                get customSizeString() {
                    if (!this.isCurtain || this.sizeMode !== 'custom') return null;
                    return this.curtainBreadth + ' ' + this.curtainUnit + ' (B) × ' + this.curtainLength + ' ' + this.curtainUnit + ' (L) [' + this.curtainHeader + ']';
                },
                get dynamicWhatsappUrl() {
                    let lines = [
                        'Hello Menahem! I would like to order ' + this.quantity + 'x {{ addslashes($name) }}'
                    ];
                    if (this.isCurtain && this.sizeMode === 'custom') {
                        lines.push('• Custom Dimensions: Length = ' + this.curtainLength + ' ' + this.curtainUnit + ', Breadth = ' + this.curtainBreadth + ' ' + this.curtainUnit);
                        lines.push('• Header Style: ' + this.curtainHeader);
                        if (this.curtainRoom) lines.push('• Window/Room: ' + this.curtainRoom);
                    }
                    lines.push('• Total: ' + this.totalFormatted);
                    lines.push('Please guide me with custom tailoring and delivery.');
                    return 'https://wa.me/{{ $brand['whatsapp_number'] ?? '919876543210' }}?text=' + encodeURIComponent(lines.join('\n'));
                }
            }"
            class="pt-24 sm:pt-28 lg:pt-30 pb-20 bg-white min-h-screen text-[#2B3326]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumbs Navigation: Clean, aligned distance below the fixed navbar -->
                <nav class="flex items-center flex-wrap gap-2 text-xs text-[#5C6654] mb-5 sm:mb-6 font-medium" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-[#74826B] transition-colors flex items-center gap-1.5 group">
                        <svg class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Home</span>
                    </a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('products.index') }}" class="hover:text-[#74826B] transition-colors">Shop</a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('products.index', ['category' => $categorySlug]) }}" class="hover:text-[#74826B] transition-colors">{{ $category }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-[#2B3326] font-semibold truncate max-w-xs">{{ $name }}</span>
                </nav>

                <!-- Product Showcase Grid (Sticky Gallery on Left, Detailed Panel on Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start mb-20">

                    <!-- Left: Gallery Showcase (Sticky on scroll so thumbnails & preview stay in view) -->
                    <div class="lg:col-span-6 lg:sticky lg:top-30 self-start space-y-4">
                        <div class="flex flex-col-reverse sm:flex-row gap-3.5 sm:gap-4 items-start">
                            
                            <!-- Picture Changing Thumbnails Strip (Left column on sm+, horizontal on mobile) -->
                            <div class="flex sm:flex-col gap-2.5 sm:gap-3 flex-shrink-0 w-full sm:w-20 overflow-x-auto sm:overflow-visible pb-1 sm:pb-0" role="tablist" aria-label="Product thumbnails">
                                <template x-for="(thumb, idx) in gallery" :key="idx">
                                    <button
                                        type="button"
                                        @click="selectImage(idx)"
                                        :class="activeIndex === idx ? 'ring-2 ring-[#74826B] border-[#74826B] shadow-sm opacity-100 scale-102' : 'border-neutral-200 opacity-70 hover:opacity-100 hover:border-[#74826B]/50'"
                                        class="relative w-16 sm:w-20 aspect-square rounded-md overflow-hidden bg-white border-2 transition-all duration-200 cursor-pointer flex-shrink-0"
                                        :aria-label="'View photo ' + (idx + 1)"
                                    >
                                        <img :src="thumb" :alt="'Thumbnail ' + (idx + 1)" class="w-full h-full object-cover" />
                                    </button>
                                </template>
                            </div>

                            <!-- Main Product Image (Tailored, Compact & Elegant: max-h ~460px) -->
                            <div class="relative flex-1 w-full aspect-[4/4.2] sm:aspect-square max-h-[440px] sm:max-h-[460px] rounded-lg overflow-hidden bg-white border border-neutral-200 shadow-sm group">
                                <!-- Floating Badge -->
                                @if($badge)
                                    <span class="absolute top-3.5 left-3.5 z-10 px-3 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider bg-neutral-100 text-[#2B3326] border border-neutral-200 shadow-sm">
                                        {{ $badge }}
                                    </span>
                                @endif

                                <!-- Main Active Image -->
                                <img
                                    :src="activeImage"
                                    alt="{{ $name }}"
                                    class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105"
                                />

                                <!-- On-Image Arrow Switchers -->
                                <button
                                    type="button"
                                    @click="prevImage()"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-md bg-white/90 hover:bg-white text-[#2B3326] shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer hover:scale-105"
                                    aria-label="Previous image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button
                                    type="button"
                                    @click="nextImage()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-md bg-white/90 hover:bg-white text-[#2B3326] shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer hover:scale-105"
                                    aria-label="Next image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>

                                <!-- Slide Index Counter -->
                                <div class="absolute bottom-3 right-3 z-10 px-2.5 py-1 rounded-md bg-black/60 backdrop-blur-md text-[10px] font-semibold text-white tracking-wider flex items-center gap-1 shadow-xs">
                                    <span x-text="activeIndex + 1">1</span>
                                    <span>/</span>
                                    <span x-text="gallery.length">{{ count($gallery) }}</span>
                                </div>

                                <!-- Subtle Zoom Hint -->
                                <div class="absolute bottom-3 left-3 z-10 px-2.5 py-1 rounded-md bg-white/85 backdrop-blur-md text-[10px] font-medium text-[#2B3326] shadow-xs hidden sm:flex items-center gap-1">
                                    <span>Artisanal Weave View</span>
                                </div>
                            </div>

                        </div>

                        <!-- Quick Shipping / Return Assurance Strip below gallery -->
                        <div class="hidden sm:flex items-center justify-between text-[11px] text-[#5C6654] pt-1 px-1">
                            <span class="flex items-center gap-1">
                                <span class="text-[#74826B]">✓</span> In Stock & Ready to Ship
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="text-[#74826B]">✓</span> 100% Genuine Handcrafted
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="text-[#74826B]">✓</span> Free Delivery
                            </span>
                        </div>
                    </div>

                    <!-- Right: Product Details, Customization & Purchase Panel (Centered on mobile, Left-aligned on websites) -->
                    <div class="lg:col-span-6 flex flex-col text-center lg:text-left items-center lg:items-start">
                        
                        <!-- Eyebrow Pill -->
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-neutral-100 text-[#2B3326] border border-neutral-200 text-xs font-bold uppercase tracking-wider mb-3 w-fit mx-auto lg:mx-0">
                            <span class="text-[#74826B]">★</span>
                            <span>{{ $material }}</span>
                        </div>

                        <!-- Product Title in Playfair Display Font -->
                        <h1 class="text-3xl sm:text-4xl font-bold font-serif tracking-tight text-[#2B3326] leading-tight text-center lg:text-left">
                            {{ $name }}
                        </h1>

                        <!-- Rating & Review Count -->
                        <div class="flex items-center justify-center lg:justify-start gap-3 mt-3.5 pb-4 border-b border-neutral-200 w-full">
                            <div class="flex text-[#74826B]">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-xs font-bold text-[#2B3326]">{{ number_format($rating, 1) }} / 5.0</span>
                            <span class="text-xs text-[#5C6654]">({{ $reviews }} Verified Reviews)</span>
                        </div>

                        <!-- Price Section -->
                        <div class="mt-5 flex items-baseline justify-center lg:justify-start gap-3.5">
                            <span class="text-3xl sm:text-4xl font-extrabold text-[#2B3326] tracking-tight">
                                {{ $price }}
                            </span>
                            @if(!empty($originalPrice))
                                <span class="text-lg text-[#5C6654]/70 line-through font-medium">
                                    {{ $originalPrice }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-md bg-neutral-100 text-[#74826B] border border-neutral-200 text-xs font-bold">
                                    Save 25%
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-[#5C6654] mt-1 font-medium text-center lg:text-left">Inclusive of all taxes • Free shipping across India</p>

                        <!-- Description Snippet -->
                        <p class="mt-4 text-sm text-[#5C6654] leading-relaxed text-center lg:text-left">
                            {{ $desc }}
                        </p>

                        <!-- Highlights List -->
                        <div class="mt-5 grid grid-cols-2 gap-2.5 text-xs text-[#2B3326] font-medium">
                            <div class="flex items-center gap-2 p-2.5 rounded-md bg-white border border-neutral-200">
                                <span class="text-[#74826B] font-bold">✓</span>
                                <span>100% Breathable Weave</span>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-md bg-white border border-neutral-200">
                                <span class="text-[#74826B] font-bold">✓</span>
                                <span>Zero Color Bleed</span>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-md bg-white border border-neutral-200">
                                <span class="text-[#74826B] font-bold">✓</span>
                                <span>Pre-Washed & Soft</span>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-md bg-white border border-neutral-200">
                                <span class="text-[#74826B] font-bold">✓</span>
                                <span>Bespoke Tailoring</span>
                            </div>
                        </div>

                        <!-- Curtain Customization Option (Length & Breadth) if product is a curtain -->
                        @if($isCurtain)
                            <div class="mt-6 p-4 sm:p-5 rounded-xl border border-[#74826B]/30 bg-[#FAF8F5] space-y-4 shadow-2xs">
                                <div class="flex items-center justify-between pb-3 border-b border-neutral-200">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">✂️</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-[#2B3326]">Curtain Size & Customization</span>
                                    </div>
                                    <div class="inline-flex rounded-md bg-white border border-neutral-200 p-0.5 text-xs font-bold">
                                        <button
                                            type="button"
                                            @click="sizeMode = 'standard'"
                                            :class="sizeMode === 'standard' ? 'bg-[#74826B] text-white shadow-xs' : 'text-[#5C6654] hover:text-[#2B3326]'"
                                            class="px-2.5 py-1 rounded transition-colors cursor-pointer"
                                        >
                                            Standard
                                        </button>
                                        <button
                                            type="button"
                                            @click="sizeMode = 'custom'"
                                            :class="sizeMode === 'custom' ? 'bg-[#74826B] text-white shadow-xs' : 'text-[#5C6654] hover:text-[#2B3326]'"
                                            class="px-2.5 py-1 rounded transition-colors cursor-pointer"
                                        >
                                            Custom Size
                                        </button>
                                    </div>
                                </div>

                                <!-- Standard Size Options -->
                                <div x-show="sizeMode === 'standard'" class="space-y-2">
                                    <span class="text-[11px] text-[#5C6654] block">Select standard Indian window or door pair drop:</span>
                                    <div class="grid grid-cols-3 gap-2 text-xs">
                                        <button
                                            type="button"
                                            @click="curtainLength = 60; curtainBreadth = 48;"
                                            :class="curtainLength === 60 ? 'border-[#74826B] bg-[#74826B]/10 font-bold text-[#2B3326]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                            class="p-2 rounded-lg border text-center transition-all cursor-pointer"
                                        >
                                            <span class="block">Window (5ft)</span>
                                            <span class="text-[10px] opacity-75">48" × 60"</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="curtainLength = 84; curtainBreadth = 48;"
                                            :class="curtainLength === 84 ? 'border-[#74826B] bg-[#74826B]/10 font-bold text-[#2B3326]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                            class="p-2 rounded-lg border text-center transition-all cursor-pointer"
                                        >
                                            <span class="block">Door (7ft)</span>
                                            <span class="text-[10px] opacity-75">48" × 84"</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="curtainLength = 108; curtainBreadth = 48;"
                                            :class="curtainLength === 108 ? 'border-[#74826B] bg-[#74826B]/10 font-bold text-[#2B3326]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                            class="p-2 rounded-lg border text-center transition-all cursor-pointer"
                                        >
                                            <span class="block">Long Door (9ft)</span>
                                            <span class="text-[10px] opacity-75">48" × 108"</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Custom Length & Breadth Inputs -->
                                <div x-show="sizeMode === 'custom'" class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-[#2B3326]">Enter Exact Custom Dimensions</span>
                                        
                                        <!-- Small writing button for displaying measurement manual -->
                                        <button
                                            type="button"
                                            @click="$dispatch('open-curtain-manual')"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#74826B] hover:text-[#2B3326] underline underline-offset-4 decoration-[#74826B]/60 transition-colors cursor-pointer group"
                                        >
                                            <svg class="w-3.5 h-3.5 text-[#74826B] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>How to measure? (View Manual)</span>
                                            <span class="text-[10px] font-mono">↗</span>
                                        </button>
                                    </div>

                                    <!-- Unit Selector -->
                                    <div class="flex items-center justify-end gap-2 text-xs">
                                        <span class="text-[11px] text-[#5C6654]">Unit:</span>
                                        <div class="inline-flex rounded-md bg-white border border-neutral-200 p-0.5">
                                            <button
                                                type="button"
                                                @click="curtainUnit = 'inches'"
                                                :class="curtainUnit === 'inches' ? 'bg-[#74826B] text-white' : 'text-[#5C6654]'"
                                                class="px-2 py-0.5 rounded text-[11px] font-bold cursor-pointer"
                                            >
                                                Inches
                                            </button>
                                            <button
                                                type="button"
                                                @click="curtainUnit = 'feet'"
                                                :class="curtainUnit === 'feet' ? 'bg-[#74826B] text-white' : 'text-[#5C6654]'"
                                                class="px-2 py-0.5 rounded text-[11px] font-bold cursor-pointer"
                                            >
                                                Feet
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Inputs Grid -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#2B3326] mb-1">
                                                Breadth (Width)
                                            </label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    x-model.number="curtainBreadth"
                                                    class="w-full px-3 py-2 rounded-lg border border-neutral-300 text-xs font-bold text-[#2B3326] bg-white outline-none focus:border-[#74826B]"
                                                />
                                                <span class="absolute right-2.5 top-2 text-[11px] text-[#5C6654]" x-text="curtainUnit"></span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-[#2B3326] mb-1">
                                                Length (Height/Drop)
                                            </label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    x-model.number="curtainLength"
                                                    class="w-full px-3 py-2 rounded-lg border border-neutral-300 text-xs font-bold text-[#2B3326] bg-white outline-none focus:border-[#74826B]"
                                                />
                                                <span class="absolute right-2.5 top-2 text-[11px] text-[#5C6654]" x-text="curtainUnit"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Header Style Choice -->
                                    <div>
                                        <label class="block text-xs font-semibold text-[#2B3326] mb-1.5">
                                            Header / Hanging Style
                                        </label>
                                        <div class="grid grid-cols-3 gap-2 text-[11px]">
                                            <button
                                                type="button"
                                                @click="curtainHeader = 'Antique Brass Eyelets'"
                                                :class="curtainHeader.includes('Eyelets') ? 'border-[#74826B] bg-white font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                                class="p-2 rounded-md border text-center transition-all cursor-pointer"
                                            >
                                                Eyelet Rings
                                            </button>
                                            <button
                                                type="button"
                                                @click="curtainHeader = 'Classic Pinch Pleats'"
                                                :class="curtainHeader.includes('Pleats') ? 'border-[#74826B] bg-white font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                                class="p-2 rounded-md border text-center transition-all cursor-pointer"
                                            >
                                                Pinch Pleats
                                            </button>
                                            <button
                                                type="button"
                                                @click="curtainHeader = 'Rod Pocket Casing'"
                                                :class="curtainHeader.includes('Pocket') ? 'border-[#74826B] bg-white font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                                class="p-2 rounded-md border text-center transition-all cursor-pointer"
                                            >
                                                Rod Pocket
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Room/Window Note -->
                                    <div>
                                        <input
                                            type="text"
                                            x-model="curtainRoom"
                                            placeholder="Room / Window note (e.g. Master Bedroom, Balcony Door)"
                                            class="w-full px-3 py-1.5 rounded-lg border border-neutral-300 text-xs text-[#2B3326] bg-white outline-none focus:border-[#74826B]"
                                        />
                                    </div>

                                    <!-- Live Summary of custom dimensions -->
                                    <div class="p-2.5 rounded-lg bg-white border border-neutral-200 text-xs text-[#2B3326] flex items-center justify-between">
                                        <span class="text-[#5C6654]">Custom Fit:</span>
                                        <strong x-text="customSizeString"></strong>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Quantity Selector -->
                        <div class="mt-6 flex items-center gap-4">
                            <span class="text-xs font-bold text-[#2B3326] uppercase tracking-wider">Quantity</span>
                            <div class="flex items-center rounded-md bg-white border border-neutral-200 p-1">
                                <button
                                    type="button"
                                    @click="if(quantity > 1) quantity--"
                                    class="w-8 h-8 rounded-sm flex items-center justify-center text-sm font-bold text-[#2B3326] hover:bg-neutral-100 transition-colors cursor-pointer"
                                    aria-label="Decrease quantity"
                                >
                                    −
                                </button>
                                <span x-text="quantity" class="w-10 text-center text-sm font-bold text-[#2B3326]">1</span>
                                <button
                                    type="button"
                                    @click="quantity++"
                                    class="w-8 h-8 rounded-sm flex items-center justify-center text-sm font-bold text-[#2B3326] hover:bg-neutral-100 transition-colors cursor-pointer"
                                    aria-label="Increase quantity"
                                >
                                    +
                                </button>
                            </div>
                            <span class="text-xs text-[#5C6654]">
                                Total: <strong x-text="totalFormatted" class="text-[#2B3326] font-bold"></strong>
                            </span>
                        </div>

                        <!-- Primary Action Buttons -->
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <!-- WhatsApp Instant Buy Button -->
                            <a
                                :href="dynamicWhatsappUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex-1 flex items-center justify-center gap-2.5 px-7 py-4 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-sm font-bold tracking-wide transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 group"
                            >
                                <svg class="w-5 h-5 fill-current text-white" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>Buy via WhatsApp</span>
                                <span class="text-xs group-hover:translate-x-0.5 transition-transform">→</span>
                            </a>

                            <!-- Add to Bag Button (Triggers Cart Drawer) -->
                            <button
                                type="button"
                                @click="$dispatch('add-to-cart', {
                                    id: {{ $id }},
                                    name: '{{ addslashes($name) }}',
                                    price: calculatedUnitPrice,
                                    quantity: quantity,
                                    image: activeImage,
                                    category: '{{ addslashes($category) }}',
                                    custom_size: customSizeString
                                })"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-4 rounded-lg bg-white hover:bg-neutral-100 text-[#2B3326] border border-neutral-200 text-sm font-bold tracking-wide transition-all shadow-xs hover:border-[#74826B] cursor-pointer"
                            >
                                <svg class="w-4.5 h-4.5 text-[#2B3326]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>Add to Bag</span>
                            </button>
                        </div>

                        <!-- Trust Guarantee Box -->
                        <div class="mt-6 p-4 rounded-lg bg-white border border-neutral-200 flex flex-col gap-2.5">
                            <div class="flex items-center gap-2.5 text-xs text-[#2B3326] font-semibold">
                                <span class="text-[#74826B]">⚡</span>
                                <span>Free Pan-India Express Delivery on this product</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-xs text-[#5C6654]">
                                <span class="text-[#74826B]">🛡️</span>
                                <span>30-Day Hassle-Free Returns & 100% Quality Assurance</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-xs text-[#5C6654]">
                                <span class="text-[#74826B]">💬</span>
                                <span>Need custom sizes? Talk directly to our design master on WhatsApp</span>
                            </div>
                        </div>

                        <!-- Specifications & Craftsmanship Tabs inside details column (keeps sticky gallery visible while reading) -->
                        <div class="mt-8 bg-white rounded-lg p-5 sm:p-6 border border-neutral-200 shadow-xs">
                            <div class="flex items-center gap-5 border-b border-neutral-200 pb-3">
                                <button
                                    type="button"
                                    @click="selectedTab = 'details'"
                                    :class="selectedTab === 'details' ? 'text-[#2B3326] border-[#74826B] font-bold' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                                    class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                >
                                    Fabric & Details
                                </button>
                                <button
                                    type="button"
                                    @click="selectedTab = 'care'"
                                    :class="selectedTab === 'care' ? 'text-[#2B3326] border-[#74826B] font-bold' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                                    class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                >
                                    Wash & Care
                                </button>
                                <button
                                    type="button"
                                    @click="selectedTab = 'shipping'"
                                    :class="selectedTab === 'shipping' ? 'text-[#2B3326] border-[#74826B] font-bold' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                                    class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                >
                                    Shipping & Returns
                                </button>
                            </div>

                            <!-- Tab 1: Fabric Details -->
                            <div x-show="selectedTab === 'details'" class="pt-5 space-y-4">
                                <h3 class="text-base font-bold font-serif text-[#2B3326]">Pure Artisanal Excellence</h3>
                                <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                    Every Menahem essential is woven with meticulous care using hand-selected yarn. Designed to balance everyday durability with cloud-like gentleness, our fabrics age gracefully through repeated washes without pilling or color degradation.
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                    <div class="p-3 rounded-md bg-white border border-neutral-200">
                                        <span class="text-[10px] uppercase font-bold text-[#5C6654]">Composition</span>
                                        <p class="text-xs font-bold text-[#2B3326] mt-0.5">{{ $material }}</p>
                                    </div>
                                    <div class="p-3 rounded-md bg-white border border-neutral-200">
                                        <span class="text-[10px] uppercase font-bold text-[#5C6654]">Weave Density</span>
                                        <p class="text-xs font-bold text-[#2B3326] mt-0.5">400+ Thread Count</p>
                                    </div>
                                    <div class="p-3 rounded-md bg-white border border-neutral-200">
                                        <span class="text-[10px] uppercase font-bold text-[#5C6654]">Craft Origin</span>
                                        <p class="text-xs font-bold text-[#2B3326] mt-0.5">Handcrafted, India</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Wash & Care -->
                            <div x-show="selectedTab === 'care'" style="display: none;" class="pt-5 space-y-2.5">
                                <h3 class="text-base font-bold font-serif text-[#2B3326]">Gentle Care for Long Life</h3>
                                <ul class="space-y-1.5 text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                    <li>• Machine wash gentle on cold or 30°C cycle with mild natural liquid detergent.</li>
                                    <li>• Do not use harsh chemical bleach or optical brighteners.</li>
                                    <li>• Line dry in soft shade to preserve the organic luster of natural cotton fibers.</li>
                                    <li>• Warm iron on reverse side if desired for a crisp luxury finish.</li>
                                </ul>
                            </div>

                            <!-- Tab 3: Shipping & Returns -->
                            <div x-show="selectedTab === 'shipping'" style="display: none;" class="pt-5 space-y-2.5">
                                <h3 class="text-base font-bold font-serif text-[#2B3326]">Fast, Safe Delivery to Your Door</h3>
                                <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                    Orders are dispatched within 24 to 48 hours via top courier partners. Standard delivery takes 3 to 5 business days across metros.
                                </p>
                                <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                    If you are not completely enchanted with your purchase, initiate an exchange or return within 30 days of receiving your parcel.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Related Products Section (4 cards in 1 row on desktop) -->
                @if(!empty($relatedProducts))
                    <div class="mt-16 pt-12 border-t border-neutral-200">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <span class="text-xs font-bold text-[#74826B] uppercase tracking-wider">Complete The Look</span>
                                <h2 class="text-2xl sm:text-3xl font-bold font-serif text-[#2B3326] tracking-tight mt-1">
                                    You May Also Love
                                </h2>
                            </div>
                            <a href="{{ route('products.index') }}" class="text-sm font-bold text-[#2B3326] hover:text-[#74826B] transition-colors flex items-center gap-1 group">
                                <span>View All Catalog</span>
                                <span class="group-hover:translate-x-1 transition-transform">→</span>
                            </a>
                        </div>

                        <!-- 2 in 1 Row on Mobile, 4 Cards in 1 Row on Desktop -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
                            @foreach($relatedProducts as $relProduct)
                                <x-product-card :product="$relProduct" />
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </main>
    </x-site-shell>
</x-layouts.app>
