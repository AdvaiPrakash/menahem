@props([
    'content' => [],
])

@php
    $promo = $content['PROMO_BANNER'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<section id="promo-banner" class="py-16 sm:py-20 bg-[#FAF7F0]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <x-reveal delay="150">
            <!-- Split Promo Banner Container: Warm Linen, Sand & Sage Gradient -->
            <div class="rounded-lg overflow-hidden shadow-sm border border-[#D7C7B2] grid grid-cols-1 lg:grid-cols-12 bg-gradient-to-br from-[#E8E2D5] via-[#D7C7B2]/50 to-[#B5C1A5]/40 relative">
                
                <!-- Left Column: Light Botanical Panel with Offer & Live Timer -->
                <div class="lg:col-span-6 p-8 sm:p-12 lg:p-14 flex flex-col justify-between relative z-10">
                    
                    <div>
                        <!-- Pill: LIMITED TIME OFFER -->
                        <div class="inline-block px-3.5 py-1 rounded-md bg-white/90 backdrop-blur-md text-[#2B3326] border border-[#D7C7B2] text-[11px] font-bold uppercase tracking-wider mb-5 shadow-2xs">
                            {{ $promo['label'] ?? 'LIMITED TIME OFFER' }}
                        </div>

                        <!-- Main Heading in Playfair Display -->
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-[#2B3326] tracking-tight leading-tight font-serif">
                            {{ $promo['heading'] ?? 'Give Your Home a Fresh New Look!' }}
                        </h2>

                        <!-- Description -->
                        <p class="mt-4 text-sm sm:text-base text-[#5C6654] max-w-md leading-relaxed font-medium">
                            {{ $promo['offer'] ?? 'Up to 30% OFF on Selected Collections.' }} Discover beautiful handcrafted essentials at exclusive prices.
                        </p>

                        <!-- CTA Button (Earthy Olive Moss) -->
                        <div class="mt-8">
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-sm font-bold tracking-wide transition-all shadow-md shadow-[#74826B]/25 hover:shadow-lg transform hover:-translate-y-0.5">
                                <span>{{ $promo['button_label'] ?? 'Shop the Sale' }}</span>
                                <span class="text-base leading-none font-mono">→</span>
                            </a>
                        </div>
                    </div>

                    <!-- Circular Countdown Timer Badge -->
                    <div class="mt-10 sm:mt-12 inline-flex items-center">
                        <div class="bg-white/95 rounded-lg px-6 py-4 shadow-md border border-[#D7C7B2] flex flex-col items-center justify-center text-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#5C6654] mb-1">
                                Hurry Up! Offer ends in
                            </span>
                            <div class="flex items-center gap-2 font-mono font-bold text-sm sm:text-base text-[#2B3326] leading-none">
                                <div class="flex flex-col items-center">
                                    <span id="timer-days">02</span>
                                    <span class="text-[8px] font-sans text-[#5C6654] font-normal uppercase">Days</span>
                                </div>
                                <span class="text-[#74826B]">:</span>
                                <div class="flex flex-col items-center">
                                    <span id="timer-hrs">14</span>
                                    <span class="text-[8px] font-sans text-[#5C6654] font-normal uppercase">Hrs</span>
                                </div>
                                <span class="text-[#74826B]">:</span>
                                <div class="flex flex-col items-center">
                                    <span id="timer-mins">36</span>
                                    <span class="text-[8px] font-sans text-[#5C6654] font-normal uppercase">Mins</span>
                                </div>
                                <span class="text-[#74826B]">:</span>
                                <div class="flex flex-col items-center">
                                    <span id="timer-secs" class="text-[#74826B]">45</span>
                                    <span class="text-[8px] font-sans text-[#5C6654] font-normal uppercase">Secs</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Visual Imagery Grid in Warm Frame -->
                <div class="lg:col-span-6 grid grid-cols-2 gap-3 p-3 sm:p-4 bg-[#F3EFE6]/70">
                    
                    <!-- Image Card 1: Folded Linens & Bedding -->
                    <div class="relative rounded-md overflow-hidden bg-white border border-[#D7C7B2] flex flex-col justify-end p-6 min-h-[260px] lg:min-h-full group shadow-sm">
                        <img src="{{ asset('/images/categories/dohars-quilts.webp') }}" alt="Artisanal Dohars & Quilts" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2B3326]/80 via-[#2B3326]/25 to-transparent z-10"></div>
                        <div class="relative z-20 text-white">
                            <span class="text-[10px] uppercase tracking-wider text-[#E8E2D5] font-bold">Mulmul Cotton</span>
                            <h4 class="text-base font-bold text-white">Dohars & Quilts</h4>
                            <p class="text-xs text-[#E8E2D5]">Ultra-soft finish</p>
                        </div>
                    </div>

                    <!-- Image Card 2: Modern Elegant Drapery & Living Space -->
                    <div class="relative rounded-md overflow-hidden bg-white border border-[#D7C7B2] flex flex-col justify-end p-6 min-h-[260px] lg:min-h-full group shadow-sm">
                        <img src="{{ asset('/images/categories/curtains.webp') }}" alt="Luxury Curtains & Drapes" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-gradient-to-t from-[#2B3326]/80 via-[#2B3326]/25 to-transparent z-10"></div>
                        <div class="relative z-20 text-white">
                            <span class="text-[10px] uppercase tracking-wider text-[#E8E2D5] font-bold">Linen Weaves</span>
                            <h4 class="text-base font-bold text-white">Luxury Drapes</h4>
                            <p class="text-xs text-[#E8E2D5]">Custom tailored</p>
                        </div>
                    </div>

                </div>

            </div>
        </x-reveal>

    </div>
</section>
