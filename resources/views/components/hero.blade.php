@props([
    'content' => [],
])

@php
    $hero = $content['HERO'] ?? $content;
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';

    // Single unified collection of curated cards for the one horizontal scrolling track
    $heroCards = [
        [
            'id' => 2,
            'title' => 'Custom Linen Drapes',
            'category' => 'Living Room',
            'chip' => '100% Belgian Flax Weave',
            'image' => '/images/products/curtains-blackout.webp',
            'alt' => 'Luxury Curtains & Drapes',
            'price' => '₹1,299',
            'badge' => '★ 4.9',
        ],
        [
            'id' => 1,
            'title' => 'Embroidered Bed Linen',
            'category' => 'Bedroom Edit',
            'chip' => '400TC Sateen Weave',
            'image' => '/images/products/bedsheet-set.webp',
            'alt' => 'Embroidered Pure Cotton Bed Linen',
            'price' => '₹1,499',
            'badge' => 'Bestseller',
        ],
        [
            'id' => 5,
            'title' => '600 GSM Combed Cotton',
            'category' => 'Bath & Wellness',
            'chip' => 'Turkish Terry Weave',
            'image' => '/images/products/towels-luxury.webp',
            'alt' => 'Luxury Bath Towels',
            'price' => '₹699',
            'badge' => 'BESTSELLER',
        ],
        [
            'id' => 7,
            'title' => 'Artisanal Mulmul Dohars',
            'category' => 'Bedroom Edit',
            'chip' => 'Reversible 3-Layer Mulmul',
            'image' => '/images/products/dohar-quilt.webp',
            'alt' => 'Mulmul Cotton Dohars',
            'price' => '₹1,799',
            'badge' => '★ 4.9',
        ],
        [
            'id' => 3,
            'title' => 'Handcrafted Table Runner',
            'category' => 'Dining Suite',
            'chip' => 'Stain-Resistant Pure Linen',
            'image' => '/images/products/table-runner.webp',
            'alt' => 'Table Runners & Mats',
            'price' => '₹799',
            'badge' => 'ARTISANAL',
        ],
        [
            'id' => 6,
            'title' => 'Woven Wool & Jute Rugs',
            'category' => 'Floor Accents',
            'chip' => 'Hand-Knotted Geometric',
            'image' => '/images/products/rugs-artisan.webp',
            'alt' => 'Handcrafted Rugs',
            'price' => '₹2,499',
            'badge' => '★ 5.0',
        ],
        [
            'id' => 4,
            'title' => 'Designer Sofa Throws',
            'category' => 'Comfort Living',
            'chip' => 'Textured Jacquard Blend',
            'image' => '/images/products/sofa-throw.webp',
            'alt' => 'Designer Sofa Decor',
            'price' => '₹899',
            'badge' => 'New In',
        ],
        [
            'id' => 9,
            'title' => 'Translucent Sheer Voiles',
            'category' => 'Living Room',
            'chip' => 'Sunlight Diffusing Linen',
            'image' => '/images/products/curtains-sheer.webp',
            'alt' => 'Sheer Linen Living Room Drapes',
            'price' => '₹1,149',
            'badge' => 'Trending',
        ],
    ];
@endphp

<section id="hero" class="relative pt-28 sm:pt-30 md:pt-32 pb-14 sm:pb-20 bg-[#F6FAF7] overflow-hidden border-b border-[#162B1F]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- ===================================================================
             MOBILE-ONLY LAYOUT (< lg)
             1. Centered Hero Typography (Eyebrow, Title, Description, 2 Buttons)
             2. Single-Track Horizontal Scrolling Showcase (One Motion Only)
             3. 4 Categories Attached Directly Underneath
             =================================================================== -->
        <div class="lg:hidden flex flex-col items-center text-center">
            
            <!-- 1. Eyebrow Pill -->
            <x-reveal delay="50">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-[#D5F0C0] text-[#2E7D5B] border border-[#B5DFB2]/60 text-xs font-bold uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D5B] animate-pulse"></span>
                    <span>{{ $hero['label'] ?? 'NEW COLLECTION' }}</span>
                </div>
            </x-reveal>

            <!-- 2. Centered Editorial Headline in Playfair Display -->
            <x-reveal delay="100">
                <h1 class="text-3xl sm:text-4xl font-bold text-[#14281E] leading-[1.12] tracking-tight font-serif max-w-sm sm:max-w-md mx-auto">
                    Transform Living <br />
                    Spaces with <span class="italic text-[#2E7D5B]">Menahem</span>
                </h1>
            </x-reveal>

            <!-- 3. Centered Description -->
            <x-reveal delay="150">
                <p class="mt-3.5 text-xs sm:text-sm text-[#4E6858] leading-relaxed max-w-sm sm:max-w-md mx-auto font-normal">
                    {{ $hero['description'] ?? 'Discover beautifully curated home essentials, from elegant curtains to luxurious towels and table linens, designed to bring warmth, comfort, and sophistication to your living spaces.' }}
                </p>
            </x-reveal>

            <!-- 4. Dual Responsive CTA Buttons Side-by-Side -->
            <x-reveal delay="200" class="w-full">
                <div class="mt-6 flex items-center justify-center gap-2.5 sm:gap-3 w-full max-w-sm mx-auto">
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-full bg-[#162B1F] hover:bg-[#223F2E] text-white text-xs sm:text-sm font-semibold tracking-wide transition-all shadow-sm"
                    >
                        <span>Shop Collection</span>
                        <span class="text-[11px] font-mono">↗</span>
                    </a>

                    <a
                        href="{{ route('products.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-full bg-white hover:bg-[#F6FAF7] text-[#14281E] border border-[#162B1F]/15 text-xs sm:text-sm font-semibold tracking-wide hover:border-[#2E7D5B] hover:text-[#2E7D5B] transition-all shadow-2xs"
                    >
                        <span>Explore Catalog</span>
                        <span class="text-[11px] font-mono">↗</span>
                    </a>
                </div>
            </x-reveal>

            <!-- 5. Single Horizontal Scrolling Gallery (One Track, One Motion Only) -->
            <x-reveal delay="250" class="w-full">
                <div class="mt-6 w-full max-w-xl mx-auto">
                    <div class="hero-marquee-container relative overflow-hidden rounded-[26px] sm:rounded-[30px] bg-[#EAF4EE]/35 border border-[#162B1F]/10 p-2 sm:p-2.5 shadow-sm">
                        
                        <!-- Left Soft Vignette Mask -->
                        <div class="absolute top-0 bottom-0 left-0 w-12 sm:w-16 bg-gradient-to-r from-[#F6FAF7] via-[#F6FAF7]/85 to-transparent z-20 pointer-events-none"></div>
                        
                        <!-- Right Soft Vignette Mask -->
                        <div class="absolute top-0 bottom-0 right-0 w-12 sm:w-16 bg-gradient-to-l from-[#F6FAF7] via-[#F6FAF7]/85 to-transparent z-20 pointer-events-none"></div>

                        <!-- Single Horizontal Scrolling Track (One-Way Continuous Glide) -->
                        <div class="overflow-hidden">
                            <div class="animate-marquee-left flex flex-row gap-3 hover:[animation-play-state:paused]">
                                
                                <!-- Set 1 (Original) -->
                                @foreach($heroCards as $item)
                                    <a
                                        href="{{ route('products.show', $item['id']) }}"
                                        title="{{ $item['title'] }}"
                                        class="relative w-[230px] sm:w-[260px] h-[280px] sm:h-[310px] rounded-[22px] sm:rounded-[24px] overflow-hidden bg-white shadow-md border border-white/70 group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <!-- Background Image -->
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-black/10 transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <!-- Top Row: Badge & Price -->
                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between pointer-events-none">
                                            <span class="px-2.5 py-1 rounded-full bg-[#162B1F]/80 backdrop-blur-md text-white text-[10px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-md text-[#14281E] text-xs font-bold shadow-xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <!-- Middle Feature Chip -->
                                        <div class="relative z-10 px-3 sm:px-3.5 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#B5DFB2]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <!-- Bottom Action Pill: Floating dark pill with title & arrow -->
                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-full bg-[#162B1F]/90 group-hover:bg-[#162B1F] backdrop-blur-md text-white border border-white/20 text-xs font-semibold transition-all shadow-md">
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-xs text-[#2E7D5B] font-mono group-hover:translate-x-0.5 transition-transform">↗</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                                <!-- Set 2 (Duplicated for 100% seamless infinite loop) -->
                                @foreach($heroCards as $item)
                                    <a
                                        href="{{ route('products.show', $item['id']) }}"
                                        title="{{ $item['title'] }}"
                                        aria-hidden="true"
                                        tabindex="-1"
                                        class="relative w-[230px] sm:w-[260px] h-[280px] sm:h-[310px] rounded-[22px] sm:rounded-[24px] overflow-hidden bg-white shadow-md border border-white/70 group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-black/10 transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between pointer-events-none">
                                            <span class="px-2.5 py-1 rounded-full bg-[#162B1F]/80 backdrop-blur-md text-white text-[10px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-md text-[#14281E] text-xs font-bold shadow-xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <div class="relative z-10 px-3 sm:px-3.5 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#B5DFB2]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-full bg-[#162B1F]/90 group-hover:bg-[#162B1F] backdrop-blur-md text-white border border-white/20 text-xs font-semibold transition-all shadow-md">
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-xs text-[#2E7D5B] font-mono group-hover:translate-x-0.5 transition-transform">↗</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                            </div>
                        </div>

                    </div>
                </div>
            </x-reveal>

            <!-- 6. Product Categories: Attached directly underneath the gallery -->
            <x-reveal delay="300" class="w-full">
                <div class="mt-4 sm:mt-5 w-full max-w-sm sm:max-w-md mx-auto p-3 sm:p-4 rounded-[24px] sm:rounded-3xl bg-white/80 backdrop-blur-md border border-[#162B1F]/10 shadow-2xs">
                    <div class="grid grid-cols-4 gap-2 sm:gap-3 text-center">
                        
                        <!-- Category 1: Curtains -->
                        <a href="{{ route('products.index', ['category' => 'curtains']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#EAF4EE] group-hover:bg-[#D5F0C0] transition-colors flex items-center justify-center text-[#14281E] border border-black/5 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16M5 4v16c0-4 3-6 3-6s3 2 3 6V4M19 4v16c0-4-3-6-3-6s-3 2-3 6V4"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#14281E] group-hover:text-[#2E7D5B] transition-colors">
                                Curtains
                            </span>
                        </a>

                        <!-- Category 2: Towels -->
                        <a href="{{ route('products.index', ['category' => 'towels']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#EAF4EE] group-hover:bg-[#D5F0C0] transition-colors flex items-center justify-center text-[#14281E] border border-black/5 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7H5a2 2 0 00-2 2v1a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2zM19 12H5a2 2 0 00-2 2v1a2 2 0 002 2h14a2 2 0 002-2v-1a2 2 0 00-2-2zM19 17H5a2 2 0 00-2 2v1a2 2 0 002 2h14a2 2 0 002-2v-1a2 2 0 00-2-2z"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#14281E] group-hover:text-[#2E7D5B] transition-colors">
                                Towels
                            </span>
                        </a>

                        <!-- Category 3: Table Linen -->
                        <a href="{{ route('products.index', ['category' => 'table-decor']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#EAF4EE] group-hover:bg-[#D5F0C0] transition-colors flex items-center justify-center text-[#14281E] border border-black/5 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="3" y="6" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 11h18M8 6v12M16 6v12"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#14281E] group-hover:text-[#2E7D5B] transition-colors whitespace-nowrap">
                                Table Linen
                            </span>
                        </a>

                        <!-- Category 4: Rugs -->
                        <a href="{{ route('products.index', ['category' => 'rugs']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#EAF4EE] group-hover:bg-[#D5F0C0] transition-colors flex items-center justify-center text-[#14281E] border border-black/5 shadow-2xs">
                                <rect x="4" y="5" width="16" height="14" rx="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16M7 5v14M17 5v14M2 7h2M2 12h2M2 17h2M20 7h2M20 12h2M20 17h2"/>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#14281E] group-hover:text-[#2E7D5B] transition-colors">
                                Rugs
                            </span>
                        </a>

                    </div>

                    <!-- 3 Pagination Indicator Dots Underneath Categories -->
                    <div class="mt-3.5 flex items-center justify-center gap-1.5">
                        <span class="w-3.5 h-1.5 rounded-full bg-[#14281E]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D5B]/70"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#B5DFB2]"></span>
                    </div>
                </div>
            </x-reveal>

        </div>

        <!-- ===================================================================
             DESKTOP-ONLY LAYOUT (lg:grid)
             Left Column: Editorial Typography & Dual Action Buttons
             Center Column: Floating Quick-Action Pill
             Right Column: Single-Track Horizontal Carousel (One Motion Only)
             =================================================================== -->
        <div class="hidden lg:grid grid-cols-12 gap-8 items-center">
            
            <!-- Left Column: Editorial Typography, Action Pills & Feature Tags -->
            <div class="lg:col-span-6 xl:col-span-5 flex flex-col items-start text-left z-10">
                
                <!-- Eyebrow Pill -->
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#D5F0C0] text-[#2E7D5B] border border-[#B5DFB2]/60 text-xs font-bold uppercase tracking-wider mb-5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#2E7D5B] animate-pulse"></span>
                        <span>{{ $hero['label'] ?? 'NEW COLLECTION' }}</span>
                    </div>
                </x-reveal>

                <!-- Main Editorial Headline in Playfair Display -->
                <x-reveal delay="150">
                    <h1 class="text-4xl sm:text-5xl lg:text-[54px] xl:text-[62px] font-bold text-[#14281E] leading-[1.08] tracking-tight font-serif">
                        Transform Living <br />
                        Spaces with <br />
                        <span class="italic text-[#2E7D5B]">Menahem</span>
                    </h1>
                </x-reveal>

                <!-- Subtitle Description -->
                <x-reveal delay="200">
                    <p class="mt-4 sm:mt-5 text-sm sm:text-base lg:text-[17px] text-[#4E6858] leading-relaxed max-w-lg font-normal">
                        {{ $hero['description'] ?? 'Discover thoughtfully curated home essentials, from bespoke linen drapery to ultra-soft cotton towels and bedsheets, designed for everyday warmth, comfort, and sophistication.' }}
                    </p>
                </x-reveal>

                <!-- Dual Action Button Pills -->
                <x-reveal delay="250" class="w-full sm:w-auto">
                    <div class="mt-7 sm:mt-8 flex flex-wrap items-center gap-3 sm:gap-4 w-full sm:w-auto">
                        <a
                            href="{{ $whatsappUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-3 px-6 sm:px-7 py-3.5 rounded-full bg-[#162B1F] hover:bg-[#223F2E] text-white text-sm font-semibold tracking-wide transition-all shadow-sm hover:shadow-lg transform hover:-translate-y-0.5 group"
                        >
                            <span>Shop Collection</span>
                            <span class="w-6 h-6 rounded-full bg-white/15 flex items-center justify-center text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform">
                                ↗
                            </span>
                        </a>

                        <a
                            href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center gap-2 px-6 sm:px-7 py-3.5 rounded-full bg-white/80 hover:bg-white text-[#14281E] border border-[#162B1F]/15 text-sm font-semibold tracking-wide hover:border-[#2E7D5B] hover:text-[#2E7D5B] transition-all shadow-2xs group"
                        >
                            <span>Explore Catalog</span>
                            <span class="text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform font-mono">
                                ↗
                            </span>
                        </a>
                    </div>
                </x-reveal>

                <!-- Bottom Tag Pills -->
                <x-reveal delay="300" class="w-full">
                    <div class="mt-10 sm:mt-12 pt-6 border-t border-[#162B1F]/10 flex flex-wrap items-center gap-2.5 w-full">
                        <span class="px-3 py-1 rounded-lg bg-[#D5F0C0] text-[#14281E] text-[11px] font-semibold border border-[#B5DFB2]/50">
                            100% Pure Cotton
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-[#EAF4EE] text-[#2E7D5B] text-[11px] font-bold border border-[#9EDBC5]/40">
                            Handcrafted Weaves
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-white text-[#4E6858] text-[11px] font-medium border border-[#162B1F]/10">
                            Bespoke Tailoring
                        </span>
                    </div>
                </x-reveal>

            </div>

            <!-- Center Vertical Floating Quick-Action Pill -->
            <div class="hidden xl:flex xl:col-span-1 justify-center z-20">
                <div class="flex flex-col items-center gap-3 p-2 rounded-full bg-white/90 backdrop-blur-md border border-[#162B1F]/10 shadow-lg">
                    <a href="{{ route('products.index') }}" title="Wishlist Favorites" class="w-10 h-10 rounded-full bg-[#D5F0C0] hover:bg-[#BCEBD7] text-[#2E7D5B] flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" title="Concierge Assistance" class="w-10 h-10 rounded-full bg-[#EAF4EE] hover:bg-[#B5DFB2] text-[#14281E] flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>
                        </svg>
                    </a>
                    <a href="{{ route('home') }}#why-us" title="30-Day Easy Exchange" class="w-10 h-10 rounded-full bg-white hover:bg-[#D5F0C0] text-[#4E6858] hover:text-[#2E7D5B] border border-[#162B1F]/10 flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" title="Bespoke Order Inquiry" class="w-10 h-10 rounded-full bg-[#162B1F] hover:bg-[#2E7D5B] text-white flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Right Column: Single-Track Horizontal Scrolling Showcase (One Motion Only) -->
            <div class="lg:col-span-6 xl:col-span-6 relative mt-6 lg:mt-0">
                <x-reveal delay="250">
                    <div class="hero-marquee-container relative overflow-hidden rounded-[32px] bg-[#EAF4EE]/45 border border-[#162B1F]/10 p-3 sm:p-3.5 shadow-sm">
                        
                        <!-- Left Soft Vignette Mask -->
                        <div class="absolute top-0 bottom-0 left-0 w-16 sm:w-24 bg-gradient-to-r from-[#F6FAF7] via-[#F6FAF7]/90 to-transparent z-20 pointer-events-none"></div>
                        
                        <!-- Right Soft Vignette Mask -->
                        <div class="absolute top-0 bottom-0 right-0 w-16 sm:w-24 bg-gradient-to-l from-[#F6FAF7] via-[#F6FAF7]/90 to-transparent z-20 pointer-events-none"></div>

                        <!-- Single Horizontal Scrolling Track (One Motion Only) -->
                        <div class="overflow-hidden">
                            <div class="animate-marquee-left flex flex-row gap-4 hover:[animation-play-state:paused]">
                                
                                <!-- Set 1 (Original) -->
                                @foreach($heroCards as $item)
                                    <a
                                        href="{{ route('products.show', $item['id']) }}"
                                        title="{{ $item['title'] }}"
                                        class="relative w-[280px] lg:w-[310px] xl:w-[330px] h-[360px] lg:h-[390px] xl:h-[420px] rounded-[26px] sm:rounded-[28px] overflow-hidden bg-white shadow-md border border-white/80 group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-black/10 transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <!-- Top Row: Badge & Price -->
                                        <div class="relative z-10 p-3.5 sm:p-4 flex items-center justify-between pointer-events-none">
                                            <span class="px-3 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-[10px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-3 py-1 rounded-full bg-white/95 backdrop-blur-md text-[#14281E] text-xs font-bold shadow-xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <!-- Middle Feature Chip -->
                                        <div class="relative z-10 px-3.5 sm:px-4 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#B5DFB2]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <!-- Bottom Action Pill with Title & Arrow -->
                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-4 py-2.5 rounded-full bg-[#162B1F]/90 group-hover:bg-[#162B1F] backdrop-blur-md text-white shadow-lg border border-white/20 transition-all">
                                                <span class="text-xs sm:text-sm font-semibold truncate">{{ $item['title'] }}</span>
                                                <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform flex-shrink-0 text-[#2E7D5B]">
                                                    ↗
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                                <!-- Set 2 (Duplicated for 100% seamless infinite loop) -->
                                @foreach($heroCards as $item)
                                    <a
                                        href="{{ route('products.show', $item['id']) }}"
                                        title="{{ $item['title'] }}"
                                        aria-hidden="true"
                                        tabindex="-1"
                                        class="relative w-[280px] lg:w-[310px] xl:w-[330px] h-[360px] lg:h-[390px] xl:h-[420px] rounded-[26px] sm:rounded-[28px] overflow-hidden bg-white shadow-md border border-white/80 group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/25 to-black/10 transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <div class="relative z-10 p-3.5 sm:p-4 flex items-center justify-between pointer-events-none">
                                            <span class="px-3 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-[10px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-3 py-1 rounded-full bg-white/95 backdrop-blur-md text-[#14281E] text-xs font-bold shadow-xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <div class="relative z-10 px-3.5 sm:px-4 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#B5DFB2]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-4 py-2.5 rounded-full bg-[#162B1F]/90 group-hover:bg-[#162B1F] backdrop-blur-md text-white shadow-lg border border-white/20 transition-all">
                                                <span class="text-xs sm:text-sm font-semibold truncate">{{ $item['title'] }}</span>
                                                <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform flex-shrink-0 text-[#2E7D5B]">
                                                    ↗
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                            </div>
                        </div>

                    </div>
                </x-reveal>
            </div>

        </div>

    </div>
</section>
