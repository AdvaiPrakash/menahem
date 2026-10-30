@props([
    'content' => [],
])

@php
    $hero = $content['HERO'] ?? $content;
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<section id="hero" class="relative pt-24 sm:pt-28 md:pt-36 pb-12 sm:pb-16 bg-[#faf9f5] overflow-hidden border-b border-[#353819]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Main Hero Grid (Desktop 2-columns, Mobile Stacked exactly like Reference) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Copy & Actions -->
            <div class="lg:col-span-6 flex flex-col items-start text-left">
                
                <!-- Eyebrow Pill (Camel #AC9661 with star) -->
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#AC9661]/15 text-[#AC9661] text-xs font-bold uppercase tracking-wider mb-4 sm:mb-6">
                        <span>★</span>
                        <span>{{ $hero['label'] ?? 'NEW COLLECTION' }}</span>
                    </div>
                </x-reveal>

                <!-- Main Heading (Matches Reference typography) -->
                <x-reveal delay="150">
                    <h1 class="text-[32px] sm:text-4xl lg:text-[54px] font-extrabold tracking-tight text-[#212411] leading-[1.12]">
                        Transform Your Home <br />
                        with <span class="text-[#AC9661]">Timeless Elegance</span>
                    </h1>
                </x-reveal>

                <!-- Description Paragraph -->
                <x-reveal delay="200">
                    <p class="mt-3.5 sm:mt-5 text-sm sm:text-base lg:text-lg text-[#595b45] leading-relaxed max-w-xl">
                        {{ $hero['description'] ?? 'Discover beautifully curated home essentials, from elegant curtains to luxurious towels and table linens, designed to bring warmth, comfort, and sophistication to your living spaces.' }}
                    </p>
                </x-reveal>

                <!-- Dual Buttons (Side-by-side on Mobile & Desktop) -->
                <x-reveal delay="250" class="w-full sm:w-auto">
                    <div class="mt-6 sm:mt-8 grid grid-cols-2 gap-3 w-full sm:flex sm:w-auto sm:items-center sm:gap-3.5">
                        <!-- Primary Button: Camel with Arrow -->
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 px-4 sm:px-7 py-3.5 rounded-xl bg-[#AC9661] hover:bg-[#937f4f] text-white text-xs sm:text-sm font-semibold tracking-wide transition-all shadow-xs hover:shadow-md transform hover:-translate-y-0.5">
                            <span>Shop Now</span>
                            <span class="text-base leading-none">→</span>
                        </a>

                        <!-- Secondary Button: Clean Outlined -->
                        <a href="#shop-categories" class="flex items-center justify-center px-3 sm:px-7 py-3.5 rounded-xl bg-white border border-[#353819]/25 text-[#212411] text-xs sm:text-sm font-semibold tracking-wide hover:bg-[#f3efe6] transition-all text-center">
                            <span>Explore Collections</span>
                        </a>
                    </div>
                </x-reveal>

                <!-- MOBILE ONLY SHOWCASE CARD (Directly below buttons as shown in reference) -->
                <div class="lg:hidden w-full mt-7">
                    <x-reveal delay="300">
                        <div class="relative w-full">
                            <!-- Circular "UP TO 30% OFF" Badge overlapping top-right -->
                            <div class="absolute -top-3 -right-2 z-20 w-20 h-20 rounded-full bg-[#353819] text-white flex flex-col items-center justify-center shadow-lg border-2 border-white">
                                <span class="text-[9px] uppercase tracking-wider text-white/80 font-semibold leading-tight">UP TO</span>
                                <span class="text-xl font-extrabold leading-none tracking-tight text-white my-0.5">30%</span>
                                <span class="text-[9px] uppercase tracking-wider text-[#AC9661] font-bold leading-tight">OFF</span>
                            </div>

                            <!-- Showcase Image Container -->
                            <div class="relative rounded-3xl overflow-hidden bg-[#f3efe6] aspect-[4/4.2] flex flex-col justify-end shadow-sm border border-[#353819]/15 group">
                                <img
                                    src="{{ asset($hero['image']['path'] ?? '/images/hero/hero-lifestyle.webp') }}"
                                    alt="{{ $hero['image']['alt'] ?? 'Menahem Luxury Home Essentials' }}"
                                    class="absolute inset-0 w-full h-full object-cover"
                                />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>

                                <!-- Bottom Floating Pill with Arrow (Matches Reference) -->
                                <a href="#shop-categories" class="relative z-10 mx-3 mb-3 flex items-center justify-between bg-white/95 backdrop-blur-md px-3.5 py-2.5 rounded-2xl border border-white/80 shadow-md group/pill">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-[#AC9661] flex-shrink-0"></span>
                                        <div>
                                            <span class="block text-xs font-bold text-[#212411] leading-tight">Curated Living Collection</span>
                                            <span class="block text-[10px] text-[#595b45] mt-0.5">Bedsheets • Curtains • Table & Sofa Decor</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-bold text-[#212411] group-hover/pill:translate-x-1 transition-transform">→</span>
                                </a>
                            </div>

                            <!-- Carousel Pagination Dots (Matches Reference) -->
                            <div class="flex items-center justify-center gap-1.5 mt-3.5">
                                <span class="w-2 h-2 rounded-full bg-[#353819]"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#353819]/25"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-[#353819]/25"></span>
                            </div>
                        </div>
                    </x-reveal>
                </div>

            </div>

            <!-- DESKTOP ONLY Right Column: Hero Showcase & Circular Discount Badge -->
            <div class="hidden lg:block lg:col-span-6 relative">
                <x-reveal delay="250">
                    <div class="relative w-full max-w-lg mx-auto">
                        
                        <!-- Circular "UP TO 30% OFF" Badge (Dark Khaki #353819 with Camel #AC9661 text) -->
                        <div class="absolute -top-4 -right-4 z-20 w-28 h-28 rounded-full bg-[#353819] text-white flex flex-col items-center justify-center shadow-lg border-2 border-white/80">
                            <span class="text-[11px] uppercase tracking-wider text-white/80 font-semibold">UP TO</span>
                            <span class="text-3xl font-extrabold leading-none tracking-tight text-white">30%</span>
                            <span class="text-[11px] uppercase tracking-wider text-[#AC9661] font-bold">OFF</span>
                        </div>

                        <!-- Hero Composition Graphic Container -->
                        <div class="relative rounded-3xl overflow-hidden bg-[#f3efe6] aspect-[4/3.8] flex flex-col justify-end shadow-md border border-[#353819]/15 group">
                            <!-- Photographic Hero Image -->
                            <img
                                src="{{ asset($hero['image']['path'] ?? '/images/hero/hero-lifestyle.webp') }}"
                                alt="{{ $hero['image']['alt'] ?? 'Menahem Luxury Home Essentials' }}"
                                class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                            />

                            <!-- Subtle Ambient Bottom Shadow Gradient -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>

                            <!-- Bottom Floating Label -->
                            <a href="#shop-categories" class="relative z-10 m-5 flex items-center justify-between bg-[#faf9f5]/95 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/70 shadow-lg group/pill">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#AC9661] animate-pulse"></span>
                                    <div>
                                        <span class="block text-xs font-bold text-[#212411]">Curated Living Collection</span>
                                        <span class="block text-[10px] text-[#595b45]">Bedsheets • Curtains • Table & Sofa Decor</span>
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-[#212411] group-hover/pill:translate-x-1 transition-transform">→</span>
                            </a>
                        </div>

                    </div>
                </x-reveal>
            </div>

        </div>

        <!-- 4 Trust Features (Matches Reference 2x2 on Mobile, 4-column on Desktop) -->
        <div class="mt-8 sm:mt-12 lg:mt-16 pt-6 border-t border-[#353819]/10 w-full">
            <x-reveal delay="350">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    
                    <!-- 1. Free Shipping -->
                    <div class="flex items-start gap-2.5">
                        <div class="mt-0.5 text-[#AC9661] flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1-4l-3 4h4z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs sm:text-sm font-bold text-[#212411] leading-snug">Free Shipping</span>
                            <span class="text-[10px] sm:text-xs text-[#595b45] mt-0.5">On orders over ₹999</span>
                        </div>
                    </div>

                    <!-- 2. Easy Returns (Cycle arrows matching reference) -->
                    <div class="flex items-start gap-2.5">
                        <div class="mt-0.5 text-[#AC9661] flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs sm:text-sm font-bold text-[#212411] leading-snug">Easy Returns</span>
                            <span class="text-[10px] sm:text-xs text-[#595b45] mt-0.5">30-day return policy</span>
                        </div>
                    </div>

                    <!-- 3. Secure Payment (Shield with checkmark) -->
                    <div class="flex items-start gap-2.5">
                        <div class="mt-0.5 text-[#AC9661] flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs sm:text-sm font-bold text-[#212411] leading-snug">Secure Payment</span>
                            <span class="text-[10px] sm:text-xs text-[#595b45] mt-0.5">100% secure</span>
                        </div>
                    </div>

                    <!-- 4. 24/7 Support (Customer support headset) -->
                    <div class="flex items-start gap-2.5">
                        <div class="mt-0.5 text-[#AC9661] flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs sm:text-sm font-bold text-[#212411] leading-snug">24/7 Support</span>
                            <span class="text-[10px] sm:text-xs text-[#595b45] mt-0.5">Dedicated support</span>
                        </div>
                    </div>

                </div>
            </x-reveal>
        </div>

    </div>
</section>
