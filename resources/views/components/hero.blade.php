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

<section id="hero" class="relative pt-28 sm:pt-30 md:pt-32 pb-14 sm:pb-20 bg-gradient-to-b from-[#FAF7F0] via-[#F3EFE6] to-[#FAF7F0] overflow-hidden border-b border-[#D7C7B2]/60">
    <!-- Subtle Ambient Warm Glow Orbs -->
    <div class="absolute top-10 left-1/4 w-96 h-96 rounded-full bg-[#B5C1A5]/25 blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute bottom-10 right-1/4 w-96 h-96 rounded-full bg-[#D7C7B2]/30 blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- ===================================================================
             MOBILE-ONLY LAYOUT (< lg)
             1. Centered Hero Typography (Eyebrow, Title, Description, 2 Buttons)
             2. Single-Track Horizontal Scrolling Showcase (Edge-to-Edge Clean)
             3. 4 Categories Attached Directly Underneath
             =================================================================== -->
        <div class="lg:hidden flex flex-col items-center text-center">
            
            <!-- 1. Eyebrow Pill -->
            <x-reveal delay="50">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-md bg-[#E8E2D5] text-[#2B3326] border border-[#D7C7B2] text-xs font-bold uppercase tracking-wider mb-4 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#74826B] animate-pulse"></span>
                    <span>{{ $hero['label'] ?? 'NEW COLLECTION' }}</span>
                </div>
            </x-reveal>

            <!-- 2. Centered Editorial Headline in Playfair Display -->
            <x-reveal delay="100">
                <h1 class="text-3xl sm:text-4xl font-bold text-[#2B3326] leading-[1.12] tracking-tight font-serif max-w-sm sm:max-w-md mx-auto">
                    Transform Living <br />
                    Spaces with <span class="italic text-[#74826B]">Menahem</span>
                </h1>
            </x-reveal>

            <!-- 3. Centered Description -->
            <x-reveal delay="150">
                <p class="mt-3.5 text-xs sm:text-sm text-[#5C6654] leading-relaxed max-w-sm sm:max-w-md mx-auto font-normal">
                    {{ $hero['description'] ?? 'Discover beautifully curated home essentials, from elegant curtains to luxurious towels and table linens, designed to bring warmth, comfort, and sophistication to your living spaces.' }}
                </p>
            </x-reveal>

            <!-- 4. Dual Responsive CTA Buttons Side-by-Side (Linen & Olive Moss) -->
            <x-reveal delay="200" class="w-full">
                <div class="mt-6 flex items-center justify-center gap-2.5 sm:gap-3 w-full max-w-sm mx-auto">
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md shadow-[#74826B]/25 active:scale-98"
                    >
                        <span>Shop Collection</span>
                        <span class="text-[11px] font-mono">↗</span>
                    </a>

                    <a
                        href="{{ route('products.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg bg-white hover:bg-[#FAF7F0] text-[#2B3326] border border-[#D7C7B2] text-xs sm:text-sm font-bold tracking-wide hover:border-[#74826B] transition-all shadow-2xs active:scale-98"
                    >
                        <span>Explore Catalog</span>
                        <span class="text-[11px] font-mono text-[#74826B]">↗</span>
                    </a>
                </div>
            </x-reveal>

            <!-- 5. Single Horizontal Scrolling Gallery (Edge-to-Edge, No Side Vignettes) -->
            <x-reveal delay="250" class="w-full">
                <div class="mt-6 w-full max-w-xl mx-auto">
                    <div class="hero-marquee-container relative overflow-hidden rounded-lg bg-[#E8E2D5]/40 border border-[#D7C7B2] p-2 sm:p-2.5 shadow-xs">
                        
                        <!-- Single Horizontal Scrolling Track (One-Way Continuous Glide) -->
                        <div class="overflow-hidden">
                            <div class="animate-marquee-left flex flex-row gap-3 hover:[animation-play-state:paused]">
                                
                                <!-- Set 1 (Original) -->
                                @foreach($heroCards as $item)
                                    <a
                                        href="{{ route('products.show', $item['id']) }}"
                                        title="{{ $item['title'] }}"
                                        class="relative w-[230px] sm:w-[260px] h-[280px] sm:h-[310px] rounded-md overflow-hidden bg-white shadow-sm border border-[#D7C7B2] group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <!-- Background Image -->
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <!-- Soft Ambient Bottom Shading -->
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#2B3326]/75 via-[#2B3326]/20 to-transparent transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <!-- Top Row: Badge & Price -->
                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between pointer-events-none">
                                            <span class="px-2.5 py-1 rounded-md bg-white/95 backdrop-blur-md text-[#2B3326] text-[10px] font-bold tracking-wider uppercase border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-2.5 py-1 rounded-md bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] text-xs font-extrabold border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <!-- Middle Feature Chip -->
                                        <div class="relative z-10 px-3 sm:px-3.5 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white/90 backdrop-blur-md border border-[#D7C7B2] text-[#2B3326] text-[10px] sm:text-[11px] font-semibold shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#74826B]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <!-- Bottom Action Pill: Floating Light Capsule with title & arrow -->
                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-md bg-white/95 group-hover:bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] border border-[#D7C7B2] text-xs font-bold transition-all shadow-md">
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-xs text-[#74826B] font-mono group-hover:translate-x-0.5 transition-transform">↗</span>
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
                                        class="relative w-[230px] sm:w-[260px] h-[280px] sm:h-[310px] rounded-md overflow-hidden bg-white shadow-sm border border-[#D7C7B2] group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#2B3326]/75 via-[#2B3326]/20 to-transparent transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between pointer-events-none">
                                            <span class="px-2.5 py-1 rounded-md bg-white/95 backdrop-blur-md text-[#2B3326] text-[10px] font-bold tracking-wider uppercase border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-2.5 py-1 rounded-md bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] text-xs font-extrabold border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <div class="relative z-10 px-3 sm:px-3.5 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white/90 backdrop-blur-md border border-[#D7C7B2] text-[#2B3326] text-[10px] sm:text-[11px] font-semibold shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#74826B]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-md bg-white/95 group-hover:bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] border border-[#D7C7B2] text-xs font-bold transition-all shadow-md">
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-xs text-[#74826B] font-mono group-hover:translate-x-0.5 transition-transform">↗</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                            </div>
                        </div>

                    </div>
                </div>
            </x-reveal>

        </div>

        <!-- ===================================================================
             DESKTOP-ONLY LAYOUT (lg:grid)
             Left Column: Editorial Typography & Dual Action Buttons
             Center Column: Floating Quick-Action Pill
             Right Column: Single-Track Horizontal Carousel (Edge-to-Edge Clean)
             =================================================================== -->
        <div class="hidden lg:grid grid-cols-12 gap-8 items-center">
            
            <!-- Left Column: Editorial Typography, Action Pills & Feature Tags -->
            <div class="lg:col-span-6 xl:col-span-5 flex flex-col items-start text-left z-10">
                
                <!-- Eyebrow Pill -->
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-md bg-[#E8E2D5] text-[#2B3326] border border-[#D7C7B2] text-xs font-bold uppercase tracking-wider mb-5 shadow-2xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#74826B] animate-pulse"></span>
                        <span>{{ $hero['label'] ?? 'NEW COLLECTION' }}</span>
                    </div>
                </x-reveal>

                <!-- Main Editorial Headline in Playfair Display -->
                <x-reveal delay="150">
                    <h1 class="text-4xl sm:text-5xl lg:text-[54px] xl:text-[62px] font-bold text-[#2B3326] leading-[1.08] tracking-tight font-serif">
                        Transform Living <br />
                        Spaces with <br />
                        <span class="italic text-[#74826B]">Menahem</span>
                    </h1>
                </x-reveal>

                <!-- Subtitle Description -->
                <x-reveal delay="200">
                    <p class="mt-4 sm:mt-5 text-sm sm:text-base lg:text-[17px] text-[#5C6654] leading-relaxed max-w-lg font-normal">
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
                            class="inline-flex items-center justify-center gap-3 px-6 sm:px-7 py-3.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-sm font-bold tracking-wide transition-all shadow-md shadow-[#74826B]/25 hover:shadow-lg transform hover:-translate-y-0.5 group active:scale-98"
                        >
                            <span>Shop Collection</span>
                            <span class="w-6 h-6 rounded-md bg-white/30 flex items-center justify-center text-xs text-white group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform">
                                ↗
                            </span>
                        </a>

                        <a
                            href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center gap-2 px-6 sm:px-7 py-3.5 rounded-lg bg-white hover:bg-[#FAF7F0] text-[#2B3326] border border-[#D7C7B2] text-sm font-bold tracking-wide hover:border-[#74826B] transition-all shadow-2xs group active:scale-98"
                        >
                            <span>Explore Catalog</span>
                            <span class="text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform font-mono text-[#74826B]">
                                ↗
                            </span>
                        </a>
                    </div>
                </x-reveal>

                <!-- Bottom Tag Pills -->
                <x-reveal delay="300" class="w-full">
                    <div class="mt-10 sm:mt-12 pt-6 border-t border-[#D7C7B2]/70 flex flex-wrap items-center gap-2.5 w-full">
                        <span class="px-3 py-1 rounded-md bg-[#E8E2D5] text-[#2B3326] text-[11px] font-bold border border-[#D7C7B2] shadow-2xs">
                            100% Pure Cotton
                        </span>
                        <span class="px-3 py-1 rounded-md bg-[#F3EFE6] text-[#2B3326] text-[11px] font-bold border border-[#D7C7B2] shadow-2xs">
                            Handcrafted Weaves
                        </span>
                        <span class="px-3 py-1 rounded-md bg-white text-[#5C6654] text-[11px] font-semibold border border-[#D7C7B2] shadow-2xs">
                            Bespoke Tailoring
                        </span>
                    </div>
                </x-reveal>

            </div>

            <!-- Center Vertical Floating Quick-Action Pill -->
            <div class="hidden xl:flex xl:col-span-1 justify-center z-20">
                <div class="flex flex-col items-center gap-3 p-2 rounded-lg bg-white/95 backdrop-blur-md border border-[#D7C7B2] shadow-lg">
                    <a href="{{ route('products.index') }}" title="Wishlist Favorites" class="w-10 h-10 rounded-md bg-[#E8E2D5] hover:bg-[#D7C7B2] text-[#74826B] flex items-center justify-center transition-all duration-200 hover:scale-110 shadow-2xs">
                        <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" title="Concierge Assistance" class="w-10 h-10 rounded-md bg-[#F3EFE6] hover:bg-[#E8E2D5] text-[#2B3326] flex items-center justify-center transition-all duration-200 hover:scale-110 shadow-2xs">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>
                        </svg>
                    </a>
                    <a href="{{ route('home') }}#why-us" title="30-Day Easy Exchange" class="w-10 h-10 rounded-md bg-white hover:bg-[#E8E2D5] text-[#5C6654] hover:text-[#74826B] border border-[#D7C7B2] flex items-center justify-center transition-all duration-200 hover:scale-110 shadow-2xs">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" title="Bespoke Order Inquiry" class="w-10 h-10 rounded-md bg-[#74826B] hover:bg-[#636F5A] text-white flex items-center justify-center transition-all duration-200 hover:scale-110 shadow-xs">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Right Column: Single-Track Horizontal Scrolling Showcase (Edge-to-Edge, 100% Crisp) -->
            <div class="lg:col-span-6 xl:col-span-6 relative mt-6 lg:mt-0">
                <x-reveal delay="250">
                    <div class="hero-marquee-container relative overflow-hidden rounded-lg bg-[#E8E2D5]/40 border border-[#D7C7B2] p-3 sm:p-3.5 shadow-xs">
                        
                        <!-- Single Horizontal Scrolling Track (One Motion Only) -->
                        <div class="overflow-hidden">
                            <div class="animate-marquee-left flex flex-row gap-4 hover:[animation-play-state:paused]">
                                
                                <!-- Set 1 (Original) -->
                                @foreach($heroCards as $item)
                                    <a
                                        href="{{ route('products.show', $item['id']) }}"
                                        title="{{ $item['title'] }}"
                                        class="relative w-[280px] lg:w-[310px] xl:w-[330px] h-[360px] lg:h-[390px] xl:h-[420px] rounded-md overflow-hidden bg-white shadow-sm border border-[#D7C7B2] group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <!-- Soft Ambient Bottom Shading -->
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#2B3326]/75 via-[#2B3326]/20 to-transparent transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <!-- Top Row: Badge & Price -->
                                        <div class="relative z-10 p-3.5 sm:p-4 flex items-center justify-between pointer-events-none">
                                            <span class="px-3 py-1 rounded-md bg-white/95 backdrop-blur-md text-[#2B3326] text-[10px] font-bold tracking-wider uppercase border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-3 py-1 rounded-md bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] text-xs font-extrabold border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <!-- Middle Feature Chip -->
                                        <div class="relative z-10 px-3.5 sm:px-4 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md bg-white/90 backdrop-blur-md border border-[#D7C7B2] text-[#2B3326] text-[11px] font-semibold shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#74826B]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <!-- Bottom Action Pill with Title & Arrow -->
                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-4 py-2.5 rounded-md bg-white/95 group-hover:bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] shadow-md border border-[#D7C7B2] transition-all">
                                                <span class="text-xs sm:text-sm font-bold truncate">{{ $item['title'] }}</span>
                                                <span class="w-6 h-6 rounded-md bg-[#74826B]/20 flex items-center justify-center text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform flex-shrink-0 text-[#74826B] font-mono">
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
                                        class="relative w-[280px] lg:w-[310px] xl:w-[330px] h-[360px] lg:h-[390px] xl:h-[420px] rounded-md overflow-hidden bg-white shadow-sm border border-[#D7C7B2] group flex flex-col justify-between flex-shrink-0 cursor-pointer block transition-transform duration-300 hover:scale-[1.02]"
                                    >
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-108"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#2B3326]/75 via-[#2B3326]/20 to-transparent transition-opacity duration-300 group-hover:opacity-90"></div>

                                        <div class="relative z-10 p-3.5 sm:p-4 flex items-center justify-between pointer-events-none">
                                            <span class="px-3 py-1 rounded-md bg-white/95 backdrop-blur-md text-[#2B3326] text-[10px] font-bold tracking-wider uppercase border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="px-3 py-1 rounded-md bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] text-xs font-extrabold border border-[#D7C7B2] shadow-2xs">
                                                {{ $item['price'] }}
                                            </span>
                                        </div>

                                        <div class="relative z-10 px-3.5 sm:px-4 pointer-events-none">
                                            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md bg-white/90 backdrop-blur-md border border-[#D7C7B2] text-[#2B3326] text-[11px] font-semibold shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#74826B]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <div class="relative z-10 p-3 sm:p-3.5 pointer-events-none">
                                            <div class="flex items-center justify-between gap-2 px-4 py-2.5 rounded-md bg-white/95 group-hover:bg-[#E8E2D5] backdrop-blur-md text-[#2B3326] shadow-md border border-[#D7C7B2] transition-all">
                                                <span class="text-xs sm:text-sm font-bold truncate">{{ $item['title'] }}</span>
                                                <span class="w-6 h-6 rounded-md bg-[#74826B]/20 flex items-center justify-center text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform flex-shrink-0 text-[#74826B] font-mono">
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
