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
                get totalFormatted() {
                    return '₹' + ({{ $numericPrice }} * this.quantity).toLocaleString();
                },
                get dynamicWhatsappUrl() {
                    const msg = encodeURIComponent('Hello Menahem! I would like to order ' + this.quantity + 'x {{ addslashes($name) }} (Total: ' + this.totalFormatted + '). Please guide me with payment and delivery.');
                    return 'https://wa.me/919876543210?text=' + msg;
                }
            }"
            class="pt-38 sm:pt-42 lg:pt-46 pb-20 bg-[#FCF8FA] min-h-screen text-[#22121E]"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumbs Navigation: Generous, balanced distance below the fixed olive navbar -->
                <nav class="flex items-center flex-wrap gap-2 text-xs text-[#6E5666] mb-8 sm:mb-10 font-medium" aria-label="Breadcrumb">
                    <a href="{{ route('home') }}" class="hover:text-[#e68bbe] transition-colors flex items-center gap-1.5 group">
                        <svg class="w-3.5 h-3.5 opacity-70 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Home</span>
                    </a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('products.index') }}" class="hover:text-[#e68bbe] transition-colors">Shop</a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('products.index', ['category' => $categorySlug]) }}" class="hover:text-[#e68bbe] transition-colors">{{ $category }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-[#22121E] font-semibold truncate max-w-xs">{{ $name }}</span>
                </nav>

                <!-- Product Showcase Grid (Sticky Gallery on Left, Detailed Panel on Right) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start mb-20">

                    <!-- Left: Gallery Showcase (Sticky on scroll so thumbnails & preview stay in view) -->
                    <div class="lg:col-span-6 lg:sticky lg:top-36 self-start space-y-4">
                        <div class="flex flex-col-reverse sm:flex-row gap-3.5 sm:gap-4 items-start">
                            
                            <!-- Picture Changing Thumbnails Strip (Left column on sm+, horizontal on mobile) -->
                            <div class="flex sm:flex-col gap-2.5 sm:gap-3 flex-shrink-0 w-full sm:w-20 overflow-x-auto sm:overflow-visible pb-1 sm:pb-0" role="tablist" aria-label="Product thumbnails">
                                <template x-for="(thumb, idx) in gallery" :key="idx">
                                    <button
                                        type="button"
                                        @click="selectImage(idx)"
                                        :class="activeIndex === idx ? 'ring-2 ring-[#e68bbe] border-[#e68bbe] shadow-sm opacity-100 scale-102' : 'border-[#23121F]/10 opacity-70 hover:opacity-100 hover:border-[#e68bbe]/50'"
                                        class="relative w-16 sm:w-20 aspect-square rounded-2xl overflow-hidden bg-[#F8EAF3] border-2 transition-all duration-200 cursor-pointer flex-shrink-0"
                                        :aria-label="'View photo ' + (idx + 1)"
                                    >
                                        <img :src="thumb" :alt="'Thumbnail ' + (idx + 1)" class="w-full h-full object-cover" />
                                    </button>
                                </template>
                            </div>

                            <!-- Main Product Image (Tailored, Compact & Elegant: max-h ~460px) -->
                            <div class="relative flex-1 w-full aspect-[4/4.2] sm:aspect-square max-h-[440px] sm:max-h-[460px] rounded-3xl overflow-hidden bg-[#F8EAF3] border border-[#23121F]/10 shadow-sm group">
                                <!-- Floating Badge -->
                                @if($badge)
                                    <span class="absolute top-3.5 left-3.5 z-10 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-[#23121F] text-white shadow-md">
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
                                    class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-[#22121E] shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer hover:scale-105"
                                    aria-label="Previous image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button
                                    type="button"
                                    @click="nextImage()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-[#22121E] shadow-md flex items-center justify-center transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 cursor-pointer hover:scale-105"
                                    aria-label="Next image"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </button>

                                <!-- Slide Index Counter -->
                                <div class="absolute bottom-3 right-3 z-10 px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-md text-[10px] font-semibold text-white tracking-wider flex items-center gap-1 shadow-xs">
                                    <span x-text="activeIndex + 1">1</span>
                                    <span>/</span>
                                    <span x-text="gallery.length">{{ count($gallery) }}</span>
                                </div>

                                <!-- Subtle Zoom Hint -->
                                <div class="absolute bottom-3 left-3 z-10 px-2.5 py-1 rounded-full bg-white/85 backdrop-blur-md text-[10px] font-medium text-[#22121E] shadow-xs hidden sm:flex items-center gap-1">
                                    <span>Artisanal Weave View</span>
                                </div>
                            </div>

                        </div>

                        <!-- Quick Shipping / Return Assurance Strip below gallery -->
                        <div class="hidden sm:flex items-center justify-between text-[11px] text-[#6E5666] pt-1 px-1">
                            <span class="flex items-center gap-1">
                                <span class="text-[#e68bbe]">✓</span> In Stock & Ready to Ship
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="text-[#e68bbe]">✓</span> 100% Genuine Handcrafted
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="text-[#e68bbe]">✓</span> Free Delivery
                            </span>
                        </div>
                    </div>

                    <!-- Right: Product Details, Customization & Purchase Panel -->
                    <div class="lg:col-span-6 flex flex-col text-left">
                        
                        <!-- Eyebrow Pill -->
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#fde4f2] text-[#e68bbe] border border-[#f4b8da]/60 text-xs font-bold uppercase tracking-wider mb-3 w-fit">
                            <span>★</span>
                            <span>{{ $material }}</span>
                        </div>

                        <!-- Product Title in Playfair Display Font -->
                        <h1 class="text-3xl sm:text-4xl font-bold font-serif tracking-tight text-[#22121E] leading-tight">
                            {{ $name }}
                        </h1>

                        <!-- Rating & Review Count -->
                        <div class="flex items-center gap-3 mt-3.5 pb-4 border-b border-[#23121F]/10">
                            <div class="flex text-[#e68bbe]">
                                @for($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-xs font-bold text-[#22121E]">{{ number_format($rating, 1) }} / 5.0</span>
                            <span class="text-xs text-[#6E5666]">({{ $reviews }} Verified Reviews)</span>
                        </div>

                        <!-- Price Section -->
                        <div class="mt-5 flex items-baseline gap-3.5">
                            <span class="text-3xl sm:text-4xl font-extrabold text-[#22121E] tracking-tight">
                                {{ $price }}
                            </span>
                            @if(!empty($originalPrice))
                                <span class="text-lg text-[#6E5666]/70 line-through font-medium">
                                    {{ $originalPrice }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#fde4f2] text-[#e68bbe] text-xs font-bold">
                                    Save 25%
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-[#6E5666] mt-1 font-medium">Inclusive of all taxes • Free shipping across India</p>

                        <!-- Description Snippet -->
                        <p class="mt-4 text-sm text-[#6E5666] leading-relaxed">
                            {{ $desc }}
                        </p>

                        <!-- Highlights List -->
                        <div class="mt-5 grid grid-cols-2 gap-2.5 text-xs text-[#22121E] font-medium">
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#23121F]/10">
                                <span class="text-[#e68bbe] font-bold">✓</span>
                                <span>100% Breathable Weave</span>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#23121F]/10">
                                <span class="text-[#e68bbe] font-bold">✓</span>
                                <span>Zero Color Bleed</span>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#23121F]/10">
                                <span class="text-[#e68bbe] font-bold">✓</span>
                                <span>Pre-Washed & Soft</span>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-[#23121F]/10">
                                <span class="text-[#e68bbe] font-bold">✓</span>
                                <span>Bespoke Tailoring</span>
                            </div>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="mt-6 flex items-center gap-4">
                            <span class="text-xs font-bold text-[#22121E] uppercase tracking-wider">Quantity</span>
                            <div class="flex items-center rounded-xl bg-white border border-[#23121F]/20 p-1">
                                <button
                                    type="button"
                                    @click="if(quantity > 1) quantity--"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-[#22121E] hover:bg-[#F8EAF3] transition-colors cursor-pointer"
                                    aria-label="Decrease quantity"
                                >
                                    −
                                </button>
                                <span x-text="quantity" class="w-10 text-center text-sm font-bold text-[#22121E]">1</span>
                                <button
                                    type="button"
                                    @click="quantity++"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold text-[#22121E] hover:bg-[#F8EAF3] transition-colors cursor-pointer"
                                    aria-label="Increase quantity"
                                >
                                    +
                                </button>
                            </div>
                            <span class="text-xs text-[#6E5666]">
                                Total: <strong x-text="totalFormatted" class="text-[#22121E] font-bold"></strong>
                            </span>
                        </div>

                        <!-- Primary Action Buttons -->
                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <!-- WhatsApp Instant Buy Button -->
                            <a
                                :href="dynamicWhatsappUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex-1 flex items-center justify-center gap-2.5 px-7 py-4 rounded-xl bg-[#23121F] hover:bg-[#341B2E] text-white text-sm font-bold tracking-wide transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 group"
                            >
                                <svg class="w-5 h-5 fill-current text-[#25d366]" viewBox="0 0 24 24">
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
                                    price: {{ $numericPrice }},
                                    quantity: quantity,
                                    image: activeImage,
                                    category: '{{ addslashes($category) }}'
                                })"
                                class="flex-1 flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-white hover:bg-[#F8EAF3] text-[#22121E] border border-[#23121F]/20 text-sm font-bold tracking-wide transition-all shadow-xs hover:border-[#e68bbe] cursor-pointer"
                            >
                                <svg class="w-4.5 h-4.5 text-[#22121E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>Add to Bag</span>
                            </button>
                        </div>

                        <!-- Trust Guarantee Box -->
                        <div class="mt-6 p-4 rounded-2xl bg-white border border-[#23121F]/10 flex flex-col gap-2.5">
                            <div class="flex items-center gap-2.5 text-xs text-[#22121E] font-semibold">
                                <span class="text-[#e68bbe]">⚡</span>
                                <span>Free Pan-India Express Delivery on this product</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-xs text-[#6E5666]">
                                <span class="text-[#e68bbe]">🛡️</span>
                                <span>30-Day Hassle-Free Returns & 100% Quality Assurance</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-xs text-[#6E5666]">
                                <span class="text-[#e68bbe]">💬</span>
                                <span>Need custom sizes? Talk directly to our design master on WhatsApp</span>
                            </div>
                        </div>

                        <!-- Specifications & Craftsmanship Tabs inside details column (keeps sticky gallery visible while reading) -->
                        <div class="mt-8 bg-white rounded-3xl p-5 sm:p-6 border border-[#23121F]/10 shadow-xs">
                            <div class="flex items-center gap-5 border-b border-[#23121F]/10 pb-3">
                                <button
                                    type="button"
                                    @click="selectedTab = 'details'"
                                    :class="selectedTab === 'details' ? 'text-[#22121E] border-[#e68bbe] font-bold' : 'text-[#6E5666] border-transparent font-medium hover:text-[#22121E]'"
                                    class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                >
                                    Fabric & Details
                                </button>
                                <button
                                    type="button"
                                    @click="selectedTab = 'care'"
                                    :class="selectedTab === 'care' ? 'text-[#22121E] border-[#e68bbe] font-bold' : 'text-[#6E5666] border-transparent font-medium hover:text-[#22121E]'"
                                    class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                >
                                    Wash & Care
                                </button>
                                <button
                                    type="button"
                                    @click="selectedTab = 'shipping'"
                                    :class="selectedTab === 'shipping' ? 'text-[#22121E] border-[#e68bbe] font-bold' : 'text-[#6E5666] border-transparent font-medium hover:text-[#22121E]'"
                                    class="text-xs sm:text-sm pb-2 border-b-2 transition-colors cursor-pointer"
                                >
                                    Shipping & Returns
                                </button>
                            </div>

                            <!-- Tab 1: Fabric Details -->
                            <div x-show="selectedTab === 'details'" class="pt-5 space-y-4">
                                <h3 class="text-base font-bold font-serif text-[#22121E]">Pure Artisanal Excellence</h3>
                                <p class="text-xs sm:text-sm text-[#6E5666] leading-relaxed">
                                    Every Menahem essential is woven with meticulous care using hand-selected yarn. Designed to balance everyday durability with cloud-like gentleness, our fabrics age gracefully through repeated washes without pilling or color degradation.
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                                    <div class="p-3 rounded-xl bg-[#FCF8FA] border border-[#23121F]/10">
                                        <span class="text-[10px] uppercase font-bold text-[#6E5666]">Composition</span>
                                        <p class="text-xs font-bold text-[#22121E] mt-0.5">{{ $material }}</p>
                                    </div>
                                    <div class="p-3 rounded-xl bg-[#FCF8FA] border border-[#23121F]/10">
                                        <span class="text-[10px] uppercase font-bold text-[#6E5666]">Weave Density</span>
                                        <p class="text-xs font-bold text-[#22121E] mt-0.5">400+ Thread Count</p>
                                    </div>
                                    <div class="p-3 rounded-xl bg-[#FCF8FA] border border-[#23121F]/10">
                                        <span class="text-[10px] uppercase font-bold text-[#6E5666]">Craft Origin</span>
                                        <p class="text-xs font-bold text-[#22121E] mt-0.5">Handcrafted, India</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Wash & Care -->
                            <div x-show="selectedTab === 'care'" style="display: none;" class="pt-5 space-y-2.5">
                                <h3 class="text-base font-bold font-serif text-[#22121E]">Gentle Care for Long Life</h3>
                                <ul class="space-y-1.5 text-xs sm:text-sm text-[#6E5666] leading-relaxed">
                                    <li>• Machine wash gentle on cold or 30°C cycle with mild natural liquid detergent.</li>
                                    <li>• Do not use harsh chemical bleach or optical brighteners.</li>
                                    <li>• Line dry in soft shade to preserve the organic luster of natural cotton fibers.</li>
                                    <li>• Warm iron on reverse side if desired for a crisp luxury finish.</li>
                                </ul>
                            </div>

                            <!-- Tab 3: Shipping & Returns -->
                            <div x-show="selectedTab === 'shipping'" style="display: none;" class="pt-5 space-y-2.5">
                                <h3 class="text-base font-bold font-serif text-[#22121E]">Fast, Safe Delivery to Your Door</h3>
                                <p class="text-xs sm:text-sm text-[#6E5666] leading-relaxed">
                                    Orders are dispatched within 24 to 48 hours via top courier partners. Standard delivery takes 3 to 5 business days across metros.
                                </p>
                                <p class="text-xs sm:text-sm text-[#6E5666] leading-relaxed">
                                    If you are not completely enchanted with your purchase, initiate an exchange or return within 30 days of receiving your parcel.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Related Products Section (4 cards in 1 row on desktop) -->
                @if(!empty($relatedProducts))
                    <div class="mt-16 pt-12 border-t border-[#23121F]/10">
                        <div class="flex items-center justify-between mb-8">
                            <div>
                                <span class="text-xs font-bold text-[#e68bbe] uppercase tracking-wider">Complete The Look</span>
                                <h2 class="text-2xl sm:text-3xl font-bold font-serif text-[#22121E] tracking-tight mt-1">
                                    You May Also Love
                                </h2>
                            </div>
                            <a href="{{ route('products.index') }}" class="text-sm font-bold text-[#22121E] hover:text-[#e68bbe] transition-colors flex items-center gap-1 group">
                                <span>View All Catalog</span>
                                <span class="group-hover:translate-x-1 transition-transform">→</span>
                            </a>
                        </div>

                        <!-- 4 Cards Grid in 1 Row on Desktop -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
