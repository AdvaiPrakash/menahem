@props([
    'content' => [],
])

@php
    $hero = $content['HERO'] ?? $content;
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';

    // Column 1 (Scrolls UPWARD): Curated cards duplicated for seamless infinite loop
    $col1Cards = [
        [
            'id' => 2,
            'title' => 'Custom Linen Drapes',
            'category' => 'Living Room',
            'chip' => '100% Belgian Flax Weave',
            'action_label' => 'Explore Drapes',
            'image' => '/images/categories/curtains.webp',
            'alt' => 'Luxury Curtains & Drapes',
            'price' => '₹1,899',
            'badge' => '★ 4.9',
            'height' => 'h-[290px] sm:h-[310px]',
        ],
        [
            'id' => 1,
            'title' => 'Embroidered Bed Linen',
            'category' => 'Bedroom Edit',
            'chip' => '400TC Sateen Weave',
            'action_label' => 'View Linens',
            'image' => '/images/categories/bedsheets.webp',
            'alt' => 'Embroidered Pure Cotton Bed Linen',
            'price' => '₹1,499',
            'badge' => '★ 4.9',
            'height' => 'h-[310px] sm:h-[330px]',
        ],
        [
            'id' => 1,
            'title' => 'Artisanal Mulmul Dohars',
            'category' => 'Bedroom Edit',
            'chip' => 'Reversible 3-Layer Mulmul',
            'action_label' => 'View Dohars',
            'image' => '/images/categories/dohars-quilts.webp',
            'alt' => 'Mulmul Cotton Dohars',
            'price' => '₹1,699',
            'badge' => 'Bestseller',
            'height' => 'h-[330px] sm:h-[350px]',
        ],
        [
            'id' => 4,
            'title' => 'Designer Sofa Throws',
            'category' => 'Comfort Living',
            'chip' => 'Textured Jacquard Blend',
            'action_label' => 'Shop Throws',
            'image' => '/images/categories/sofa-decor.webp',
            'alt' => 'Designer Sofa Decor',
            'price' => '₹899',
            'badge' => 'New In',
            'height' => 'h-[280px] sm:h-[300px]',
        ],
    ];

    // Column 2 (Scrolls DOWNWARD): Curated cards duplicated for seamless infinite loop
    $col2Cards = [
        [
            'id' => 5,
            'title' => '600 GSM Combed Cotton',
            'category' => 'Bath & Wellness',
            'chip' => '600 GSM Combed Cotton',
            'action_label' => 'Try Out',
            'image' => '/images/categories/towels.webp',
            'alt' => 'Luxury Bath Towels',
            'price' => '₹699',
            'badge' => 'BESTSELLER',
            'height' => 'h-[310px] sm:h-[330px]',
        ],
        [
            'id' => 6,
            'title' => 'Woven Cotton Rugs',
            'category' => 'Floor Accents',
            'chip' => 'Hand-Knotted Geometric',
            'action_label' => 'Discover Rugs',
            'image' => '/images/categories/rugs.webp',
            'alt' => 'Handcrafted Rugs',
            'price' => '₹2,199',
            'badge' => 'TRY OUT ↗',
            'height' => 'h-[320px] sm:h-[340px]',
        ],
        [
            'id' => 3,
            'title' => 'Handcrafted Table Linens',
            'category' => 'Dining Suite',
            'chip' => 'Stain-Resistant Pure Linen',
            'action_label' => 'Shop Dining',
            'image' => '/images/categories/table-decor.webp',
            'alt' => 'Table Runners & Mats',
            'price' => '₹799',
            'badge' => 'ARTISANAL',
            'height' => 'h-[280px] sm:h-[300px]',
        ],
        [
            'id' => 1,
            'title' => 'Curated Living Showcase',
            'category' => 'Signature Collection',
            'chip' => 'Bespoke Interior Harmony',
            'action_label' => 'Explore All',
            'image' => '/images/hero/hero-lifestyle.webp',
            'alt' => 'Menahem Curated Living Showcase',
            'price' => '₹2,499',
            'badge' => '★ 5.0',
            'height' => 'h-[340px] sm:h-[360px]',
        ],
    ];
@endphp

<section id="hero" class="relative pt-28 sm:pt-30 md:pt-32 pb-14 sm:pb-20 bg-[#FCF8FA] overflow-hidden border-b border-[#23121F]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- ===================================================================
             MOBILE-ONLY LAYOUT (< lg): Matches User's Reference Screenshot
             1. Centered Hero Typography (Eyebrow, Title, Description, 2 Buttons)
             2. 2-Column Vertical Scrolling Gallery (The Image Sliding Portion)
             3. 4 Categories Attached Just Behind (Below) The Image Sliding Portion
             (Wired with scroll-motion reveal curves and smooth stagger)
             =================================================================== -->
        <div class="lg:hidden flex flex-col items-center text-center">
            
            <!-- 1. Eyebrow Pill -->
            <x-reveal delay="50">
                <div class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-[#fde4f2] text-[#e68bbe] border border-[#f4b8da]/60 text-xs font-bold uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#e68bbe] animate-pulse"></span>
                    <span>{{ $hero['label'] ?? 'NEW COLLECTION' }}</span>
                </div>
            </x-reveal>

            <!-- 2. Centered Editorial Headline in Playfair Display -->
            <x-reveal delay="100">
                <h1 class="text-3xl sm:text-4xl font-bold text-[#22121E] leading-[1.12] tracking-tight font-serif max-w-sm sm:max-w-md mx-auto">
                    Transform Living <br />
                    Spaces with <span class="italic text-[#e68bbe]">Menahem</span>
                </h1>
            </x-reveal>

            <!-- 3. Centered Description -->
            <x-reveal delay="150">
                <p class="mt-3.5 text-xs sm:text-sm text-[#6E5666] leading-relaxed max-w-sm sm:max-w-md mx-auto font-normal">
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
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-full bg-[#23121F] hover:bg-[#341B2E] text-white text-xs sm:text-sm font-semibold tracking-wide transition-all shadow-sm"
                    >
                        <span>Shop Collection</span>
                        <span class="text-[11px] font-mono">↗</span>
                    </a>

                    <a
                        href="{{ route('products.index') }}"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-full bg-white hover:bg-[#FCF8FA] text-[#22121E] border border-[#23121F]/15 text-xs sm:text-sm font-semibold tracking-wide hover:border-[#e68bbe] hover:text-[#e68bbe] transition-all shadow-2xs"
                    >
                        <span>Explore Catalog</span>
                        <span class="text-[11px] font-mono">↗</span>
                    </a>
                </div>
            </x-reveal>

            <!-- 5. Two-Column Vertical Masonry-Style Gallery (The Image Sliding Portion) -->
            <!-- Left column scrolls UPWARD, Right column scrolls DOWNWARD, Portrait 3:4 cards -->
            <x-reveal delay="250" class="w-full">
                <div class="mt-6 w-full max-w-sm sm:max-w-md mx-auto">
                    <div class="relative h-[530px] sm:h-[580px] overflow-hidden rounded-[26px] sm:rounded-[30px] bg-[#F8EAF3]/35 border border-[#23121F]/10 p-2 sm:p-2.5 shadow-sm">
                        
                        <!-- Top Soft Vignette Mask -->
                        <div class="absolute top-0 left-0 right-0 h-16 bg-gradient-to-b from-[#FCF8FA] via-[#FCF8FA]/80 to-transparent z-20 pointer-events-none"></div>
                        
                        <!-- Bottom Soft Vignette Mask -->
                        <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-[#FCF8FA] via-[#FCF8FA]/80 to-transparent z-20 pointer-events-none"></div>

                        <!-- Dual Opposite-Direction Vertical Columns Container -->
                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3 h-full overflow-hidden">
                            
                            <!-- LEFT COLUMN: Scrolls UPWARD from bottom to top -->
                            <div class="flex flex-col gap-2.5 sm:gap-3 animate-marquee-up hover:[animation-play-state:paused]">
                                
                                <!-- Set 1 (Original) -->
                                @foreach($col1Cards as $idx => $item)
                                    <div class="relative w-full aspect-[3/4] rounded-[22px] sm:rounded-[24px] overflow-hidden bg-white shadow-md border border-white/70 group flex flex-col justify-between flex-shrink-0">
                                        <!-- Portrait Background Image -->
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/5 pointer-events-none"></div>

                                        <!-- Top Spacer -->
                                        <div></div>

                                        <!-- Bottom Action Pill: Floating dark pill with title & arrow -->
                                        <div class="relative z-10 p-2 sm:p-2.5">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-1.5 px-2.5 py-1.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white border border-white/20 text-[10px] sm:text-[11px] font-semibold transition-all group/btn"
                                            >
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-[9px] text-white/80 group-hover/btn:translate-x-0.5 transition-transform">›</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Upward Scroll Indicator Pill between Card 1 and Card 2 -->
                                    @if($idx === 0)
                                        <div class="flex justify-center -my-2 relative z-20 pointer-events-none">
                                            <div class="w-7 h-7 rounded-full bg-white/95 backdrop-blur-md shadow-md border border-black/5 flex items-center justify-center text-[#22121E]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <!-- Set 2 (Duplicated for 100% seamless infinite loop) -->
                                @foreach($col1Cards as $idx => $item)
                                    <div class="relative w-full aspect-[3/4] rounded-[22px] sm:rounded-[24px] overflow-hidden bg-white shadow-md border border-white/70 group flex flex-col justify-between flex-shrink-0" aria-hidden="true">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/5 pointer-events-none"></div>

                                        <div></div>

                                        <div class="relative z-10 p-2 sm:p-2.5">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-1.5 px-2.5 py-1.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white border border-white/20 text-[10px] sm:text-[11px] font-semibold transition-all group/btn"
                                            >
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-[9px] text-white/80 group-hover/btn:translate-x-0.5 transition-transform">›</span>
                                            </a>
                                        </div>
                                    </div>

                                    @if($idx === 0)
                                        <div class="flex justify-center -my-2 relative z-20 pointer-events-none" aria-hidden="true">
                                            <div class="w-7 h-7 rounded-full bg-white/95 backdrop-blur-md shadow-md border border-black/5 flex items-center justify-center text-[#22121E]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                            </div>

                            <!-- RIGHT COLUMN: Scrolls DOWNWARD from top to bottom -->
                            <div class="flex flex-col gap-2.5 sm:gap-3 animate-marquee-down hover:[animation-play-state:paused]">
                                
                                <!-- Set 1 (Original) -->
                                @foreach($col2Cards as $idx => $item)
                                    <div class="relative w-full aspect-[3/4] rounded-[22px] sm:rounded-[24px] overflow-hidden bg-white shadow-md border border-white/70 group flex flex-col justify-between flex-shrink-0">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/5 pointer-events-none"></div>

                                        <!-- Top Badges Row -->
                                        <div class="relative z-10 p-2 sm:p-2.5 flex items-center justify-between">
                                            <span class="px-2 py-0.5 rounded-full {{ $item['badge'] === 'BESTSELLER' ? 'bg-[#23121F]/80 text-white' : 'bg-[#e68bbe] text-white' }} backdrop-blur-md text-[9px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            
                                            <!-- Wishlist Heart Icon Button -->
                                            <span class="w-6 h-6 rounded-full bg-black/30 backdrop-blur-md text-white flex items-center justify-center text-[10px] hover:bg-white hover:text-[#22121E] transition-colors">
                                                ♡
                                            </span>
                                        </div>

                                        <!-- Bottom Action Pill -->
                                        <div class="relative z-10 p-2 sm:p-2.5">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-1.5 px-2.5 py-1.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white border border-white/20 text-[10px] sm:text-[11px] font-semibold transition-all group/btn"
                                            >
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-[9px] text-white/80 group-hover/btn:translate-x-0.5 transition-transform">›</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Downward Scroll Indicator Pill between Card 1 and Card 2 -->
                                    @if($idx === 0)
                                        <div class="flex justify-center -my-2 relative z-20 pointer-events-none">
                                            <div class="w-7 h-7 rounded-full bg-white/95 backdrop-blur-md shadow-md border border-black/5 flex items-center justify-center text-[#22121E]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <!-- Set 2 (Duplicated for 100% seamless infinite loop) -->
                                @foreach($col2Cards as $idx => $item)
                                    <div class="relative w-full aspect-[3/4] rounded-[22px] sm:rounded-[24px] overflow-hidden bg-white shadow-md border border-white/70 group flex flex-col justify-between flex-shrink-0" aria-hidden="true">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/5 pointer-events-none"></div>

                                        <div class="relative z-10 p-2 sm:p-2.5 flex items-center justify-between">
                                            <span class="px-2 py-0.5 rounded-full {{ $item['badge'] === 'BESTSELLER' ? 'bg-[#23121F]/80 text-white' : 'bg-[#e68bbe] text-white' }} backdrop-blur-md text-[9px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="w-6 h-6 rounded-full bg-black/30 backdrop-blur-md text-white flex items-center justify-center text-[10px]">
                                                ♡
                                            </span>
                                        </div>

                                        <div class="relative z-10 p-2 sm:p-2.5">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-1.5 px-2.5 py-1.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white border border-white/20 text-[10px] sm:text-[11px] font-semibold transition-all group/btn"
                                            >
                                                <span class="truncate">{{ $item['title'] }}</span>
                                                <span class="text-[9px] text-white/80 group-hover/btn:translate-x-0.5 transition-transform">›</span>
                                            </a>
                                        </div>
                                    </div>

                                    @if($idx === 0)
                                        <div class="flex justify-center -my-2 relative z-20 pointer-events-none" aria-hidden="true">
                                            <div class="w-7 h-7 rounded-full bg-white/95 backdrop-blur-md shadow-md border border-black/5 flex items-center justify-center text-[#22121E]">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                            </div>

                        </div>

                    </div>
                </div>
            </x-reveal>

            <!-- 6. Product Categories: Attached directly behind (below) the image sliding portion -->
            <x-reveal delay="300" class="w-full">
                <div class="mt-4 sm:mt-5 w-full max-w-sm sm:max-w-md mx-auto p-3 sm:p-4 rounded-[24px] sm:rounded-3xl bg-white/80 backdrop-blur-md border border-[#23121F]/10 shadow-2xs">
                    <div class="grid grid-cols-4 gap-2 sm:gap-3 text-center">
                        
                        <!-- Category 1: Curtains -->
                        <a href="{{ route('products.index', ['category' => 'curtains']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#F8EAF3] group-hover:bg-[#fde4f2] transition-colors flex items-center justify-center text-[#22121E] border border-black/5 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16M5 4v16c0-4 3-6 3-6s3 2 3 6V4M19 4v16c0-4-3-6-3-6s-3 2-3 6V4"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#22121E] group-hover:text-[#e68bbe] transition-colors">
                                Curtains
                            </span>
                        </a>

                        <!-- Category 2: Towels -->
                        <a href="{{ route('products.index', ['category' => 'towels']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#F8EAF3] group-hover:bg-[#fde4f2] transition-colors flex items-center justify-center text-[#22121E] border border-black/5 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7H5a2 2 0 00-2 2v1a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2zM19 12H5a2 2 0 00-2 2v1a2 2 0 002 2h14a2 2 0 002-2v-1a2 2 0 00-2-2zM19 17H5a2 2 0 00-2 2v1a2 2 0 002 2h14a2 2 0 002-2v-1a2 2 0 00-2-2z"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#22121E] group-hover:text-[#e68bbe] transition-colors">
                                Towels
                            </span>
                        </a>

                        <!-- Category 3: Table Linen -->
                        <a href="{{ route('products.index', ['category' => 'table-decor']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#F8EAF3] group-hover:bg-[#fde4f2] transition-colors flex items-center justify-center text-[#22121E] border border-black/5 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="3" y="6" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 11h18M8 6v12M16 6v12"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#22121E] group-hover:text-[#e68bbe] transition-colors whitespace-nowrap">
                                Table Linen
                            </span>
                        </a>

                        <!-- Category 4: Rugs -->
                        <a href="{{ route('products.index', ['category' => 'rugs']) }}" class="flex flex-col items-center group">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-[#F8EAF3] group-hover:bg-[#fde4f2] transition-colors flex items-center justify-center text-[#22121E] border border-black/5 shadow-2xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="4" y="5" width="16" height="14" rx="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16M7 5v14M17 5v14M2 7h2M2 12h2M2 17h2M20 7h2M20 12h2M20 17h2"/>
                                </svg>
                            </div>
                            <span class="mt-1.5 text-[11px] sm:text-xs font-semibold text-[#22121E] group-hover:text-[#e68bbe] transition-colors">
                                Rugs
                            </span>
                        </a>

                    </div>

                    <!-- 3 Pagination Indicator Dots Underneath Categories -->
                    <div class="mt-3.5 flex items-center justify-center gap-1.5">
                        <span class="w-3.5 h-1.5 rounded-full bg-[#22121E]"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#e68bbe]/70"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#f4b8da]"></span>
                    </div>
                </div>
            </x-reveal>

        </div>

        <!-- ===================================================================
             DESKTOP-ONLY LAYOUT (lg:grid): Exactly Preserved Without Any Modification
             Left Editorial Column + Floating Quick-Action Pill + Right Dual Marquee
             =================================================================== -->
        <div class="hidden lg:grid grid-cols-12 gap-8 items-center">
            
            <!-- Left Column: Editorial Typography, Action Pills & Feature Tags -->
            <div class="lg:col-span-6 flex flex-col items-start text-left z-10">
                
                <!-- Eyebrow Pill -->
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#fde4f2] text-[#e68bbe] border border-[#f4b8da]/60 text-xs font-bold uppercase tracking-wider mb-5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#e68bbe] animate-pulse"></span>
                        <span>{{ $hero['label'] ?? 'NEW COLLECTION' }}</span>
                    </div>
                </x-reveal>

                <!-- Main Editorial Headline in Playfair Display -->
                <x-reveal delay="150">
                    <h1 class="text-4xl sm:text-5xl lg:text-[58px] xl:text-[66px] font-bold text-[#22121E] leading-[1.08] tracking-tight font-serif">
                        Transform Living <br />
                        Spaces with <br />
                        <span class="italic text-[#e68bbe]">Menahem</span>
                    </h1>
                </x-reveal>

                <!-- Subtitle Description -->
                <x-reveal delay="200">
                    <p class="mt-4 sm:mt-5 text-sm sm:text-base lg:text-[17px] text-[#6E5666] leading-relaxed max-w-lg font-normal">
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
                            class="inline-flex items-center justify-center gap-3 px-6 sm:px-7 py-3.5 rounded-full bg-[#23121F] hover:bg-[#341B2E] text-white text-sm font-semibold tracking-wide transition-all shadow-sm hover:shadow-lg transform hover:-translate-y-0.5 group"
                        >
                            <span>Shop Collection</span>
                            <span class="w-6 h-6 rounded-full bg-white/15 flex items-center justify-center text-xs group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform">
                                ↗
                            </span>
                        </a>

                        <a
                            href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center gap-2 px-6 sm:px-7 py-3.5 rounded-full bg-white/80 hover:bg-white text-[#22121E] border border-[#23121F]/15 text-sm font-semibold tracking-wide hover:border-[#e68bbe] hover:text-[#e68bbe] transition-all shadow-2xs group"
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
                    <div class="mt-10 sm:mt-12 pt-6 border-t border-[#23121F]/10 flex flex-wrap items-center gap-2.5 w-full">
                        <span class="px-3 py-1 rounded-lg bg-[#fde4f2] text-[#22121E] text-[11px] font-semibold border border-[#f4b8da]/50">
                            100% Pure Cotton
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-[#F8EAF3] text-[#e68bbe] text-[11px] font-bold border border-[#eea1cd]/40">
                            Handcrafted Weaves
                        </span>
                        <span class="px-3 py-1 rounded-lg bg-white text-[#6E5666] text-[11px] font-medium border border-[#23121F]/10">
                            Bespoke Tailoring
                        </span>
                    </div>
                </x-reveal>

            </div>

            <!-- Center Vertical Floating Quick-Action Pill -->
            <div class="hidden xl:flex lg:col-span-1 justify-center z-20">
                <div class="flex flex-col items-center gap-3 p-2 rounded-full bg-white/90 backdrop-blur-md border border-[#23121F]/10 shadow-lg">
                    <a href="{{ route('products.index') }}" title="Wishlist Favorites" class="w-10 h-10 rounded-full bg-[#fde4f2] hover:bg-[#f9cee7] text-[#e68bbe] flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" title="Concierge Assistance" class="w-10 h-10 rounded-full bg-[#F8EAF3] hover:bg-[#f4b8da] text-[#22121E] flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 18v-6a9 9 0 0118 0v6M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>
                        </svg>
                    </a>
                    <a href="{{ route('home') }}#why-us" title="30-Day Easy Exchange" class="w-10 h-10 rounded-full bg-white hover:bg-[#fde4f2] text-[#6E5666] hover:text-[#e68bbe] border border-[#23121F]/10 flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" title="Bespoke Order Inquiry" class="w-10 h-10 rounded-full bg-[#23121F] hover:bg-[#e68bbe] text-white flex items-center justify-center transition-all duration-200 hover:scale-110">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Right Column: Dual Counter-Marquee Vertical Scrolling Showcase (Desktop) -->
            <div class="lg:col-span-6 xl:col-span-5 relative mt-6 lg:mt-0">
                <x-reveal delay="250">
                    <div class="hero-marquee-container relative h-[520px] sm:h-[580px] lg:h-[640px] overflow-hidden rounded-[32px] bg-[#F8EAF3]/40 border border-[#23121F]/10 p-2 sm:p-3 shadow-sm">
                        
                        <div class="absolute top-0 left-0 right-0 h-20 bg-gradient-to-b from-[#FCF8FA] via-[#FCF8FA]/85 to-transparent z-20 pointer-events-none"></div>
                        <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-[#FCF8FA] via-[#FCF8FA]/85 to-transparent z-20 pointer-events-none"></div>

                        <div class="grid grid-cols-2 gap-3 sm:gap-4 h-full overflow-hidden">
                            
                            <!-- 1ST SECTION: SCROLLS UPWARD -->
                            <div class="flex flex-col gap-3.5 sm:gap-4 animate-marquee-up hover:[animation-play-state:paused]">
                                @foreach($col1Cards as $item)
                                    <div class="relative w-full {{ $item['height'] }} rounded-[24px] sm:rounded-[28px] overflow-hidden bg-white shadow-md border border-white/80 group flex flex-col justify-between flex-shrink-0">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/10"></div>

                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between">
                                            <span class="px-2.5 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-[10px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="w-6 h-6 rounded-full bg-white/30 backdrop-blur-md text-white flex items-center justify-center text-xs font-bold hover:bg-white hover:text-[#22121E] transition-colors">
                                                ×
                                            </span>
                                        </div>

                                        <div class="relative z-10 px-3 sm:px-3.5">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#f4b8da]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <div class="relative z-10 p-2.5 sm:p-3">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white shadow-lg border border-white/20 transition-all group/btn"
                                            >
                                                <span class="text-[11px] sm:text-xs font-semibold truncate">{{ $item['title'] }}</span>
                                                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5 transition-transform flex-shrink-0">
                                                    ↗
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach

                                @foreach($col1Cards as $item)
                                    <div class="relative w-full {{ $item['height'] }} rounded-[24px] sm:rounded-[28px] overflow-hidden bg-white shadow-md border border-white/80 group flex flex-col justify-between flex-shrink-0" aria-hidden="true">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/10"></div>
                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between">
                                            <span class="px-2.5 py-1 rounded-full bg-black/40 backdrop-blur-md text-white text-[10px] font-bold tracking-wider uppercase border border-white/20">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="w-6 h-6 rounded-full bg-white/30 backdrop-blur-md text-white flex items-center justify-center text-xs font-bold hover:bg-white hover:text-[#22121E] transition-colors">
                                                ×
                                            </span>
                                        </div>
                                        <div class="relative z-10 px-3 sm:px-3.5">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#f4b8da]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>
                                        <div class="relative z-10 p-2.5 sm:p-3">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white shadow-lg border border-white/20 transition-all group/btn"
                                            >
                                                <span class="text-[11px] sm:text-xs font-semibold truncate">{{ $item['title'] }}</span>
                                                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5 transition-transform flex-shrink-0">
                                                    ↗
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- 2ND SECTION: SCROLLS DOWNWARD -->
                            <div class="flex flex-col gap-3.5 sm:gap-4 animate-marquee-down hover:[animation-play-state:paused]">
                                @foreach($col2Cards as $item)
                                    <div class="relative w-full {{ $item['height'] }} rounded-[24px] sm:rounded-[28px] overflow-hidden bg-white shadow-md border border-white/80 group flex flex-col justify-between flex-shrink-0">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/10"></div>

                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between">
                                            <span class="px-2.5 py-1 rounded-full bg-[#e68bbe] text-white text-[10px] font-bold tracking-wider uppercase shadow-xs">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="w-6 h-6 rounded-full bg-white/30 backdrop-blur-md text-white flex items-center justify-center text-xs font-bold hover:bg-white hover:text-[#22121E] transition-colors">
                                                ×
                                            </span>
                                        </div>

                                        <div class="relative z-10 px-3 sm:px-3.5">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#e68bbe]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>

                                        <div class="relative z-10 p-2.5 sm:p-3">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white shadow-lg border border-white/20 transition-all group/btn"
                                            >
                                                <span class="text-[11px] sm:text-xs font-semibold truncate">{{ $item['title'] }}</span>
                                                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5 transition-transform flex-shrink-0">
                                                    ↗
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach

                                @foreach($col2Cards as $item)
                                    <div class="relative w-full {{ $item['height'] }} rounded-[24px] sm:rounded-[28px] overflow-hidden bg-white shadow-md border border-white/80 group flex flex-col justify-between flex-shrink-0" aria-hidden="true">
                                        <img
                                            src="{{ asset($item['image']) }}"
                                            alt="{{ $item['alt'] }}"
                                            loading="lazy"
                                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        />
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-black/10"></div>
                                        <div class="relative z-10 p-3 sm:p-3.5 flex items-center justify-between">
                                            <span class="px-2.5 py-1 rounded-full bg-[#e68bbe] text-white text-[10px] font-bold tracking-wider uppercase shadow-xs">
                                                {{ $item['badge'] }}
                                            </span>
                                            <span class="w-6 h-6 rounded-full bg-white/30 backdrop-blur-md text-white flex items-center justify-center text-xs font-bold hover:bg-white hover:text-[#22121E] transition-colors">
                                                ×
                                            </span>
                                        </div>
                                        <div class="relative z-10 px-3 sm:px-3.5">
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-md border border-white/30 text-white text-[10px] sm:text-[11px] font-medium shadow-xs">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#e68bbe]"></span>
                                                <span class="line-clamp-1">{{ $item['chip'] }}</span>
                                            </div>
                                        </div>
                                        <div class="relative z-10 p-2.5 sm:p-3">
                                            <a
                                                href="{{ route('products.show', $item['id']) }}"
                                                class="flex items-center justify-between gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-full bg-[#23121F]/90 hover:bg-[#23121F] backdrop-blur-md text-white shadow-lg border border-white/20 transition-all group/btn"
                                            >
                                                <span class="text-[11px] sm:text-xs font-semibold truncate">{{ $item['title'] }}</span>
                                                <span class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center text-[10px] group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5 transition-transform flex-shrink-0">
                                                    ↗
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>

                    </div>
                </x-reveal>
            </div>

        </div>

    </div>
</section>
