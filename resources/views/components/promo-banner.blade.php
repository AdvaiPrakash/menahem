@props([
    'content' => [],
])

@php
    $promo = $content['PROMO_BANNER'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<section id="promo-banner" class="py-16 sm:py-20 bg-[#FCF8FA]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <x-reveal delay="150">
            <!-- Split Promo Banner Container (Rich Velvet #341B2E and Rose #e68bbe) -->
            <div class="rounded-3xl overflow-hidden shadow-sm grid grid-cols-1 lg:grid-cols-12 bg-[#341B2E] relative">
                
                <!-- Left Column: Dark Velvet Panel with Offer & Live Timer -->
                <div class="lg:col-span-6 p-8 sm:p-12 lg:p-14 flex flex-col justify-between relative z-10">
                    
                    <div>
                        <!-- Pill: LIMITED TIME OFFER -->
                        <div class="inline-block px-3 py-1 rounded-md bg-white/15 text-[#f4b8da] text-[11px] font-bold uppercase tracking-wider mb-5">
                            {{ $promo['label'] ?? 'LIMITED TIME OFFER' }}
                        </div>

                        <!-- Main Heading in Playfair Display -->
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight leading-tight font-serif">
                            {{ $promo['heading'] ?? 'Give Your Home a Fresh New Look!' }}
                        </h2>

                        <!-- Description -->
                        <p class="mt-4 text-sm sm:text-base text-white/80 max-w-md leading-relaxed">
                            {{ $promo['offer'] ?? 'Up to 30% OFF on Selected Collections.' }} Discover beautiful handcrafted essentials at exclusive prices.
                        </p>

                        <!-- CTA Button (Rose #e68bbe) -->
                        <div class="mt-8">
                            <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-7 py-3.5 rounded-lg bg-[#e68bbe] hover:bg-[#d272a8] text-white text-sm font-semibold tracking-wide transition-all shadow-md">
                                <span>{{ $promo['button_label'] ?? 'Shop the Sale' }}</span>
                                <span class="text-base leading-none">→</span>
                            </a>
                        </div>
                    </div>

                    <!-- Circular Countdown Timer Badge -->
                    <div class="mt-10 sm:mt-12 inline-flex items-center">
                        <div class="bg-white rounded-full px-6 py-4 shadow-xl border border-gray-100 flex flex-col items-center justify-center text-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">
                                Hurry Up! Offer ends in
                            </span>
                            <div class="flex items-center gap-2 font-mono font-bold text-sm sm:text-base text-gray-900 leading-none">
                                <div class="flex flex-col items-center">
                                    <span id="timer-days">02</span>
                                    <span class="text-[8px] font-sans text-gray-400 font-normal uppercase">Days</span>
                                </div>
                                <span class="text-gray-300">:</span>
                                <div class="flex flex-col items-center">
                                    <span id="timer-hrs">14</span>
                                    <span class="text-[8px] font-sans text-gray-400 font-normal uppercase">Hrs</span>
                                </div>
                                <span class="text-gray-300">:</span>
                                <div class="flex flex-col items-center">
                                    <span id="timer-mins">36</span>
                                    <span class="text-[8px] font-sans text-gray-400 font-normal uppercase">Mins</span>
                                </div>
                                <span class="text-gray-300">:</span>
                                <div class="flex flex-col items-center">
                                    <span id="timer-secs" class="text-[#e68bbe]">45</span>
                                    <span class="text-[8px] font-sans text-gray-400 font-normal uppercase">Secs</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Visual Imagery Grid in Deep Velvet #1D0B19 -->
                <div class="lg:col-span-6 grid grid-cols-2 gap-2 p-2 bg-[#1D0B19]">
                    
                    <!-- Image Card 1: Folded Linens & Bedding -->
                    <div class="relative rounded-2xl overflow-hidden bg-[#2C1628] flex flex-col justify-end p-6 min-h-[260px] lg:min-h-full group">
                        <img src="{{ asset('/images/categories/dohars-quilts.webp') }}" alt="Artisanal Dohars & Quilts" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent z-10"></div>
                        <div class="relative z-20 text-white">
                            <span class="text-[10px] uppercase tracking-wider text-[#f4b8da] font-bold">Mulmul Cotton</span>
                            <h4 class="text-base font-bold">Dohars & Quilts</h4>
                            <p class="text-xs text-white/70">Ultra-soft finish</p>
                        </div>
                    </div>

                    <!-- Image Card 2: Modern Elegant Drapery & Living Space -->
                    <div class="relative rounded-2xl overflow-hidden bg-[#23121F] flex flex-col justify-end p-6 min-h-[260px] lg:min-h-full group">
                        <img src="{{ asset('/images/categories/curtains.webp') }}" alt="Luxury Curtains & Drapes" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent z-10"></div>
                        <div class="relative z-20 text-white">
                            <span class="text-[10px] uppercase tracking-wider text-[#f4b8da] font-bold">Linen Weaves</span>
                            <h4 class="text-base font-bold">Luxury Drapes</h4>
                            <p class="text-xs text-white/70">Custom tailored</p>
                        </div>
                    </div>

                </div>

            </div>
        </x-reveal>

    </div>
</section>
