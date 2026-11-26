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

    $rawGallery = $product['gallery'] ?? [];
    if (is_array($rawGallery) && ! empty($rawGallery)) {
        $gallery = array_values(array_unique(array_filter(array_merge([$image], $rawGallery))));
    } else {
        $gallery = [$image];
    }

    $highlights = is_array($product['highlights'] ?? null) ? array_values(array_filter($product['highlights'])) : [];
    $fabricDetails = is_array($product['fabric_details'] ?? null) ? $product['fabric_details'] : [];
    $washCare = trim((string) ($product['wash_care'] ?? ''));
    $shippingReturns = trim((string) ($product['shipping_returns'] ?? ''));

    $hasFabricTab = ! empty($fabricDetails['summary']) || ! empty($fabricDetails['weave_density']) || ! empty($fabricDetails['craft_origin']) || ! empty($product['material']);
    $hasCareTab = $washCare !== '';
    $hasShippingTab = $shippingReturns !== '';
    $defaultTab = $hasFabricTab ? 'details' : ($hasCareTab ? 'care' : ($hasShippingTab ? 'shipping' : 'none'));
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
                selectedTab: '{{ $defaultTab }}',
                isCurtain: {{ $isCurtain ? 'true' : 'false' }},
                sizeMode: 'standard', // 'standard' | 'custom'
                curtainUnit: 'inches',
                curtainBreadth: 48,
                curtainLength: 84,
                curtainHeader: 'Antique Brass Eyelets',
                curtainRoom: '',
                buyingNow: false,
                customizationError: '',

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
                    let summary = this.curtainBreadth + ' ' + this.curtainUnit + ' (B) × ' + this.curtainLength + ' ' + this.curtainUnit + ' (L) [' + this.curtainHeader + ']';
                    if (this.curtainRoom && this.curtainRoom.trim()) {
                        summary += ' - ' + this.curtainRoom.trim();
                    }
                    return summary;
                },
                validateCustomization() {
                    this.customizationError = '';
                    if (this.isCurtain && this.sizeMode === 'custom') {
                        const breadth = Number(this.curtainBreadth);
                        const length = Number(this.curtainLength);
                        if (!breadth || breadth < 1 || !length || length < 1) {
                            this.customizationError = 'Please enter valid Breadth and Length dimensions (minimum 1).';
                            return false;
                        }
                    }
                    return true;
                },
                addToBag() {
                    if (!this.validateCustomization()) return;
                    this.$dispatch('add-to-cart', {
                        id: {{ $id }},
                        name: '{{ addslashes($name) }}',
                        price: this.calculatedUnitPrice,
                        quantity: this.quantity,
                        image: this.activeImage,
                        category: '{{ addslashes($category) }}',
                        custom_size: this.customSizeString
                    });
                },
                async buyNow() {
                    if (!this.validateCustomization() || this.buyingNow) return;
                    this.buyingNow = true;

                    const itemPayload = {
                        id: this.customSizeString ? ({{ $id }} + '-' + Date.now()) : {{ $id }},
                        product_id: {{ $id }},
                        name: '{{ addslashes($name) }}',
                        price: this.calculatedUnitPrice,
                        quantity: this.quantity,
                        image: this.activeImage,
                        category: '{{ addslashes($category) }}',
                        custom_size: this.customSizeString
                    };

                    try {
                        sessionStorage.setItem('menahem_buy_now', JSON.stringify([itemPayload]));
                    } catch (e) {}

                    window.location.href = '{{ route('checkout.index', ['mode' => 'buy_now']) }}';
                }
            }"
            class="pt-24 sm:pt-28 lg:pt-30 pb-20 bg-white min-h-screen text-[#2B3326]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Back to Category Navigation Button -->
                <div class="mb-3.5 flex items-center justify-between flex-wrap gap-3">
                    <a
                        href="{{ route('products.index', ['category' => $categorySlug]) }}"
                        class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-lg bg-[#2B3326] hover:bg-[#74826B] text-white text-xs sm:text-[13px] font-bold tracking-wide shadow-xs hover:shadow-md transition-all duration-200 cursor-pointer group active:scale-95"
                    >
                        <svg class="w-4 h-4 text-white group-hover:-translate-x-1 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back to {{ $category }}</span>
                    </a>
                </div>

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
                            <div x-show="gallery.length > 1" class="flex sm:flex-col gap-2.5 sm:gap-3 flex-shrink-0 w-full sm:w-20 overflow-x-auto sm:overflow-visible pb-1 sm:pb-0" role="tablist" aria-label="Product thumbnails">
                                <template x-for="(thumb, idx) in gallery" :key="idx">
                                    <button
                                        type="button"
                                        @click="selectImage(idx)"
                                        :class="activeIndex === idx ? 'ring-2 ring-[#74826B] border-[#74826B] shadow-sm opacity-100 scale-102' : 'border-neutral-200 opacity-70 hover:opacity-100 hover:border-[#74826B]/50'"
                                        class="relative w-16 sm:w-20 aspect-square rounded-md overflow-hidden bg-white border-2 transition-all duration-200 cursor-pointer flex-shrink-0"
                                        :aria-label="'View photo ' + (idx + 1)"
                                    >
                                        <img :src="thumb" :alt="'Thumbnail ' + (idx + 1)" loading="lazy" class="w-full h-full object-cover" />
                                    </button>
                                </template>
                            </div>

                            <!-- Main Product Image (Preserves original proportions) -->
                            <div class="relative flex-1 w-full aspect-[4/4.2] sm:aspect-square max-h-[440px] sm:max-h-[460px] rounded-lg overflow-hidden bg-neutral-50/60 border border-neutral-200 shadow-sm group flex items-center justify-center">
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
                                    class="w-full h-full object-contain transition-all duration-500 group-hover:scale-105"
                                />

                                <!-- On-Image Arrow Switchers -->
                                <button
                                    x-show="gallery.length > 1"
                                    type="button"
                                    @click="prevImage()"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-md bg-white/90 hover:bg-white text-[#2B3326] shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer hover:scale-105"
                                    aria-label="Previous image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button
                                    x-show="gallery.length > 1"
                                    type="button"
                                    @click="nextImage()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-md bg-white/90 hover:bg-white text-[#2B3326] shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer hover:scale-105"
                                    aria-label="Next image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>

                                <!-- Slide Index Counter -->
                                <div x-show="gallery.length > 1" class="absolute bottom-3 right-3 z-10 px-2.5 py-1 rounded-md bg-black/60 backdrop-blur-md text-[10px] font-semibold text-white tracking-wider flex items-center gap-1 shadow-xs">
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

                        <!-- Product-Specific Highlights List -->
                        @if(!empty($highlights))
                            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-[#2B3326] font-medium">
                                @foreach($highlights as $highlight)
                                    <div class="flex items-center gap-2 p-2.5 rounded-md bg-white border border-neutral-200">
                                        <span class="text-[#74826B] font-bold">✓</span>
                                        <span>{{ $highlight }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Curtain Customization Option (Length & Breadth) if product is a curtain -->
                        @if($isCurtain)
                            <div class="mt-6 p-4 sm:p-5 rounded-xl border border-[#74826B]/30 bg-[#FAF8F5] space-y-4 shadow-2xs overflow-hidden">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-neutral-200">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm">✂️</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-[#2B3326]">Curtain Size &amp; Customization</span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:inline-flex rounded-md bg-white border border-neutral-200 p-0.5 text-xs font-bold w-full sm:w-auto">
                                        <button
                                            type="button"
                                            @click="sizeMode = 'standard'; customizationError = ''"
                                            :class="sizeMode === 'standard' ? 'bg-[#74826B] text-white shadow-xs' : 'text-[#5C6654] hover:text-[#2B3326]'"
                                            class="px-3 py-1.5 sm:py-1 rounded transition-colors cursor-pointer text-center"
                                        >
                                            Standard
                                        </button>
                                        <button
                                            type="button"
                                            @click="sizeMode = 'custom'"
                                            :class="sizeMode === 'custom' ? 'bg-[#74826B] text-white shadow-xs' : 'text-[#5C6654] hover:text-[#2B3326]'"
                                            class="px-3 py-1.5 sm:py-1 rounded transition-colors cursor-pointer text-center"
                                        >
                                            Custom Size
                                        </button>
                                    </div>
                                </div>

                                <!-- Standard Size Options -->
                                <div x-show="sizeMode === 'standard'" class="space-y-2.5">
                                    <span class="text-[11px] text-[#5C6654] block">Select standard Indian window or door pair drop:</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                        <button
                                            type="button"
                                            @click="curtainLength = 60; curtainBreadth = 48;"
                                            :class="curtainLength === 60 ? 'border-[#74826B] bg-[#74826B]/10 font-bold text-[#2B3326]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                            class="p-2.5 rounded-lg border flex sm:block items-center justify-between sm:text-center transition-all cursor-pointer"
                                        >
                                            <span class="block">Window (5ft)</span>
                                            <span class="text-[11px] sm:text-[10px] opacity-75">48&Prime; &times; 60&Prime;</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="curtainLength = 84; curtainBreadth = 48;"
                                            :class="curtainLength === 84 ? 'border-[#74826B] bg-[#74826B]/10 font-bold text-[#2B3326]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                            class="p-2.5 rounded-lg border flex sm:block items-center justify-between sm:text-center transition-all cursor-pointer"
                                        >
                                            <span class="block">Door (7ft)</span>
                                            <span class="text-[11px] sm:text-[10px] opacity-75">48&Prime; &times; 84&Prime;</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="curtainLength = 108; curtainBreadth = 48;"
                                            :class="curtainLength === 108 ? 'border-[#74826B] bg-[#74826B]/10 font-bold text-[#2B3326]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                            class="p-2.5 rounded-lg border flex sm:block items-center justify-between sm:text-center transition-all cursor-pointer"
                                        >
                                            <span class="block">Long Door (9ft)</span>
                                            <span class="text-[11px] sm:text-[10px] opacity-75">48&Prime; &times; 108&Prime;</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Custom Length & Breadth Inputs -->
                                <div x-show="sizeMode === 'custom'" class="space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-[#2B3326]">Enter Exact Custom Dimensions</span>
                                        
                                        <!-- Small writing button for displaying measurement manual -->
                                        <button
                                            type="button"
                                            @click="$dispatch('open-curtain-manual')"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#74826B] hover:text-[#2B3326] underline underline-offset-4 decoration-[#74826B]/60 transition-colors cursor-pointer group self-start sm:self-auto"
                                        >
                                            <svg class="w-3.5 h-3.5 text-[#74826B] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>How to measure? (View Manual)</span>
                                            <span class="text-[10px] font-mono">↗</span>
                                        </button>
                                    </div>

                                    <!-- Unit Selector -->
                                    <div class="flex items-center justify-between sm:justify-end gap-2 text-xs">
                                        <span class="text-[11px] font-medium text-[#5C6654]">Measurement Unit:</span>
                                        <div class="inline-flex rounded-md bg-white border border-neutral-200 p-0.5">
                                            <button
                                                type="button"
                                                @click="curtainUnit = 'inches'"
                                                :class="curtainUnit === 'inches' ? 'bg-[#74826B] text-white' : 'text-[#5C6654]'"
                                                class="px-3 py-1 sm:px-2 sm:py-0.5 rounded text-[11px] font-bold cursor-pointer"
                                            >
                                                Inches
                                            </button>
                                            <button
                                                type="button"
                                                @click="curtainUnit = 'feet'"
                                                :class="curtainUnit === 'feet' ? 'bg-[#74826B] text-white' : 'text-[#5C6654]'"
                                                class="px-3 py-1 sm:px-2 sm:py-0.5 rounded text-[11px] font-bold cursor-pointer"
                                            >
                                                Feet
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Inputs Grid -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-[#2B3326] mb-1.5">
                                                Breadth (Width) <span class="text-rose-600">*</span>
                                            </label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    required
                                                    x-model.number="curtainBreadth"
                                                    @input="customizationError = ''"
                                                    class="w-full pl-3 pr-14 py-2.5 sm:py-2 rounded-lg border border-neutral-300 text-xs font-bold text-[#2B3326] bg-white outline-none focus:border-[#74826B]"
                                                />
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-[#5C6654] pointer-events-none" x-text="curtainUnit"></span>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-[#2B3326] mb-1.5">
                                                Length (Height/Drop) <span class="text-rose-600">*</span>
                                            </label>
                                            <div class="relative">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    required
                                                    x-model.number="curtainLength"
                                                    @input="customizationError = ''"
                                                    class="w-full pl-3 pr-14 py-2.5 sm:py-2 rounded-lg border border-neutral-300 text-xs font-bold text-[#2B3326] bg-white outline-none focus:border-[#74826B]"
                                                />
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-[#5C6654] pointer-events-none" x-text="curtainUnit"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Header Style Choice -->
                                    <div>
                                        <label class="block text-xs font-semibold text-[#2B3326] mb-1.5">
                                            Header / Hanging Style <span class="text-rose-600">*</span>
                                        </label>
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs sm:text-[11px]">
                                            <button
                                                type="button"
                                                @click="curtainHeader = 'Antique Brass Eyelets'"
                                                :class="curtainHeader.includes('Eyelets') ? 'border-[#74826B] bg-white font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                                class="py-2.5 px-3 sm:p-2 rounded-md border text-center transition-all cursor-pointer"
                                            >
                                                Eyelet Rings
                                            </button>
                                            <button
                                                type="button"
                                                @click="curtainHeader = 'Classic Pinch Pleats'"
                                                :class="curtainHeader.includes('Pleats') ? 'border-[#74826B] bg-white font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                                class="py-2.5 px-3 sm:p-2 rounded-md border text-center transition-all cursor-pointer"
                                            >
                                                Pinch Pleats
                                            </button>
                                            <button
                                                type="button"
                                                @click="curtainHeader = 'Rod Pocket Casing'"
                                                :class="curtainHeader.includes('Pocket') ? 'border-[#74826B] bg-white font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 bg-white text-[#5C6654]'"
                                                class="py-2.5 px-3 sm:p-2 rounded-md border text-center transition-all cursor-pointer"
                                            >
                                                Rod Pocket
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Room/Window Note -->
                                    <div>
                                        <label class="block text-xs font-semibold text-[#2B3326] mb-1.5">
                                            Room / Window Note (Optional)
                                        </label>
                                        <input
                                            type="text"
                                            x-model="curtainRoom"
                                            placeholder="e.g. Master Bedroom, Balcony Door"
                                            class="w-full px-3 py-2.5 sm:py-2 rounded-lg border border-neutral-300 text-xs text-[#2B3326] bg-white outline-none focus:border-[#74826B]"
                                        />
                                    </div>

                                    <!-- Validation Error -->
                                    <p x-show="customizationError" x-text="customizationError" class="text-xs font-semibold text-rose-600"></p>

                                    <!-- Live Summary of custom dimensions -->
                                    <div class="p-3 rounded-lg bg-white border border-neutral-200 text-xs text-[#2B3326] flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                        <span class="text-[#5C6654]">Custom Fit:</span>
                                        <strong x-text="customSizeString" class="break-words"></strong>
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
                        <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 max-w-md">
                            <!-- Buy Now Button (Proceeds directly to Checkout) -->
                            <button
                                type="button"
                                @click="buyNow()"
                                :disabled="buyingNow"
                                class="flex-1 h-11 inline-flex items-center justify-center gap-2 px-5 rounded-lg bg-[#384230] hover:bg-[#2B3326] text-white text-[13px] font-semibold tracking-wide border border-transparent transition-colors shadow-xs cursor-pointer disabled:opacity-60"
                            >
                                <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span x-text="buyingNow ? 'Proceeding...' : 'Buy Now'">Buy Now</span>
                            </button>

                            <!-- Add to Bag Button (Triggers Cart Drawer) -->
                            <button
                                type="button"
                                @click="addToBag()"
                                class="flex-1 h-11 inline-flex items-center justify-center gap-2 px-5 rounded-lg bg-white hover:bg-[#F7F8F5] text-[#2B3326] border border-[#2B3326]/25 hover:border-[#2B3326] text-[13px] font-semibold tracking-wide transition-colors shadow-xs cursor-pointer"
                            >
                                <svg class="w-4 h-4 text-[#2B3326] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

                        <!-- Specifications & Craftsmanship Tabs inside details column (only shown when configured) -->
                        @if($hasFabricTab || $hasCareTab || $hasShippingTab)
                            <div class="mt-8 bg-white rounded-lg p-5 sm:p-6 border border-neutral-200 shadow-xs">
                                <div class="flex flex-wrap items-center gap-4 sm:gap-5 border-b border-neutral-200 pb-3">
                                    @if($hasFabricTab)
                                        <button
                                            type="button"
                                            @click="selectedTab = 'details'"
                                            :class="selectedTab === 'details' ? 'text-[#2B3326] border-[#74826B] font-bold' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                                            class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                        >
                                            Fabric &amp; Details
                                        </button>
                                    @endif
                                    @if($hasCareTab)
                                        <button
                                            type="button"
                                            @click="selectedTab = 'care'"
                                            :class="selectedTab === 'care' ? 'text-[#2B3326] border-[#74826B] font-bold' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                                            class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                        >
                                            Wash &amp; Care
                                        </button>
                                    @endif
                                    @if($hasShippingTab)
                                        <button
                                            type="button"
                                            @click="selectedTab = 'shipping'"
                                            :class="selectedTab === 'shipping' ? 'text-[#2B3326] border-[#74826B] font-bold' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                                            class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                        >
                                            Shipping &amp; Returns
                                        </button>
                                    @endif
                                </div>

                                <!-- Tab 1: Fabric Details -->
                                @if($hasFabricTab)
                                    <div x-show="selectedTab === 'details'" class="pt-5 space-y-4">
                                        @if(!empty($fabricDetails['summary']))
                                            <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                                {!! nl2br(e($fabricDetails['summary'])) !!}
                                            </p>
                                        @endif
                                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                            @if(!empty($product['material']))
                                                <div class="p-3 rounded-md bg-white border border-neutral-200">
                                                    <span class="text-[10px] uppercase font-bold text-[#5C6654]">Composition</span>
                                                    <p class="text-xs font-bold text-[#2B3326] mt-0.5">{{ $product['material'] }}</p>
                                                </div>
                                            @endif
                                            @if(!empty($fabricDetails['weave_density']))
                                                <div class="p-3 rounded-md bg-white border border-neutral-200">
                                                    <span class="text-[10px] uppercase font-bold text-[#5C6654]">Weave Density</span>
                                                    <p class="text-xs font-bold text-[#2B3326] mt-0.5">{{ $fabricDetails['weave_density'] }}</p>
                                                </div>
                                            @endif
                                            @if(!empty($fabricDetails['craft_origin']))
                                                <div class="p-3 rounded-md bg-white border border-neutral-200">
                                                    <span class="text-[10px] uppercase font-bold text-[#5C6654]">Craft Origin</span>
                                                    <p class="text-xs font-bold text-[#2B3326] mt-0.5">{{ $fabricDetails['craft_origin'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Tab 2: Wash & Care -->
                                @if($hasCareTab)
                                    <div x-show="selectedTab === 'care'" style="display: none;" class="pt-5 space-y-2.5">
                                        <ul class="space-y-1.5 text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                            @foreach(preg_split('/\r\n|\r|\n/', $washCare) as $careLine)
                                                @if(trim($careLine) !== '')
                                                    <li>• {{ trim($careLine) }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!-- Tab 3: Shipping & Returns -->
                                @if($hasShippingTab)
                                    <div x-show="selectedTab === 'shipping'" style="display: none;" class="pt-5 space-y-2.5">
                                        <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                            {!! nl2br(e($shippingReturns)) !!}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif

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
