@props([
    'content' => [],
])

@php
    $about = $content['ABOUT'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $about['secondary_cta']['url'] ?? ($brand['whatsapp_url'] ?? '#');
    $pillars = $about['pillars'] ?? [];
    $stats = $about['stats'] ?? [];
@endphp

<section id="about" class="py-18 sm:py-24 bg-[#F3EFE6]/55 border-b border-[#D7C7B2]/70 relative overflow-hidden">
    <!-- Subtle Ambient Glow -->
    <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-[#B5C1A5]/15 blur-3xl pointer-events-none -z-10"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-[#D7C7B2]/20 blur-3xl pointer-events-none -z-10"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
            
            <!-- Left Column: Visual Story Frame & Stat Cards (5 cols) -->
            <div class="lg:col-span-5">
                <x-reveal delay="100">
                    <div class="relative">
                        <!-- Main Lifestyle Image Frame -->
                        <div class="relative rounded-lg overflow-hidden border border-[#D7C7B2] shadow-sm bg-white aspect-[4/4.6] group">
                            <img
                                src="{{ asset($about['image'] ?? '/images/hero/hero-lifestyle.webp') }}"
                                alt="{{ $about['image_alt'] ?? 'Menahem Artisanal Home Essentials' }}"
                                class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-102"
                            />
                            
                            <!-- Ambient Vignette Gradient -->
                            <div class="absolute inset-0 bg-linear-to-t from-[#2B3326]/75 via-transparent to-transparent pointer-events-none z-10"></div>

                            <!-- Bottom Floating Caption inside Image -->
                            <div class="absolute bottom-4 left-4 right-4 z-20 text-white">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white/95 backdrop-blur-md text-[#2B3326] border border-[#D7C7B2] text-[10px] font-bold uppercase tracking-wider mb-2 shadow-2xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#74826B]"></span>
                                    <span>{{ $about['tag'] ?? 'The one that comforts you' }}</span>
                                </div>
                                <p class="text-xs sm:text-sm text-[#FAF7F0] font-serif italic drop-shadow-xs">
                                    "{{ $about['quote'] ?? 'A home shouldn’t demand your attention. It should cradle your days and soften your evenings.' }}"
                                </p>
                            </div>
                        </div>

                        <!-- Quiet Craft Stats Strip Below Image -->
                        @if(!empty($stats))
                            <div class="grid grid-cols-3 gap-2.5 sm:gap-3 mt-3.5 p-3.5 rounded-lg bg-white border border-[#D7C7B2] shadow-2xs">
                                @foreach($stats as $stat)
                                    <div class="text-center">
                                        <div class="text-base sm:text-lg font-bold font-serif text-[#2B3326] tracking-tight">
                                            {{ $stat['value'] }}
                                        </div>
                                        <div class="text-[10px] sm:text-[11px] font-bold text-[#5C6654] leading-tight mt-0.5">
                                            {{ $stat['label'] }}
                                        </div>
                                        @if(!empty($stat['sub']))
                                            <div class="text-[9px] text-[#74826B] font-medium hidden sm:block mt-0.5">
                                                {{ $stat['sub'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-reveal>
            </div>

            <!-- Right Column: Editorial Narrative, Pillars & Actions (7 cols) -->
            <div class="lg:col-span-7 flex flex-col justify-center">
                <x-reveal delay="150">
                    <!-- Eyebrow Pill -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#E8E2D5] text-[#2B3326] border border-[#D7C7B2] text-xs font-bold uppercase tracking-wider mb-3 w-fit shadow-2xs">
                        <span>★</span>
                        <span>{{ $about['badge'] ?? 'Our Essence & Story' }}</span>
                    </div>

                    <!-- Heading in Playfair Display Font -->
                    <h2 class="text-3xl sm:text-4xl lg:text-[40px] font-bold tracking-tight text-[#2B3326] font-serif leading-[1.2]">
                        {{ $about['heading'] ?? 'Rooted in Simple Living, Crafted for Everyday Comfort' }}
                    </h2>

                    <!-- Lead Philosophy -->
                    <p class="text-sm sm:text-base text-[#2B3326] font-medium leading-relaxed mt-4">
                        {{ $about['lead'] ?? 'Menahem takes its name from an ancient word meaning comforter. We make home textiles that quiet the noise of the day.' }}
                    </p>

                    <!-- Body Story -->
                    <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed mt-3">
                        {{ $about['description'] ?? 'We began with a clear purpose: to craft home essentials that feel gentle from the very first wash, free from the stiff chemical finishes common in mass-produced furnishings. Hand-finished by generational artisans across India, our textiles pair breathable long-staple cottons with tailored stitches and calm, earth-drawn tones.' }}
                    </p>
                </x-reveal>

                <!-- 3 Minimalist Value Pillars -->
                @if(!empty($pillars))
                    <x-reveal delay="200">
                        <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-[#D7C7B2]">
                            @foreach($pillars as $index => $pillar)
                                <div class="p-3.5 rounded-lg bg-[#FAF7F0] border border-[#D7C7B2] shadow-2xs flex flex-col justify-between">
                                    <div>
                                        <div class="w-8 h-8 rounded-md bg-[#E8E2D5] border border-[#D7C7B2] flex items-center justify-center text-[#74826B] mb-2.5 shadow-2xs">
                                            @if($index === 0)
                                                <!-- Natural Weave Icon -->
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3m15.364 6.364L5.636 5.636m12.728 0L5.636 18.364"/>
                                                </svg>
                                            @elseif($index === 1)
                                                <!-- Tailored Needle / Craftsmanship Icon -->
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                            @else
                                                <!-- Indian Heritage / Atelier Icon -->
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <h3 class="text-xs sm:text-sm font-bold text-[#2B3326] font-serif">
                                            {{ $pillar['title'] }}
                                        </h3>
                                        <p class="text-[11px] text-[#5C6654] mt-1 leading-relaxed font-medium">
                                            {{ $pillar['description'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-reveal>
                @endif

                <!-- Call to Action Actions -->
                <x-reveal delay="250">
                    <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <a
                            href="{{ $about['cta']['url'] ?? '#trending' }}"
                            class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md shadow-[#74826B]/25 hover:shadow-lg transform hover:-translate-y-0.5 active:scale-98"
                        >
                            <span>{{ $about['cta']['label'] ?? 'Explore Curated Essentials' }}</span>
                            <span class="font-mono text-base leading-none">→</span>
                        </a>

                        <a
                            href="{{ $whatsappUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-lg bg-white hover:bg-[#FAF7F0] text-[#2B3326] border border-[#D7C7B2] text-xs sm:text-sm font-bold tracking-wide transition-colors shadow-2xs group"
                        >
                            <svg class="w-4 h-4 fill-current text-[#25d366]" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                            </svg>
                            <span>{{ $about['secondary_cta']['label'] ?? 'Inquire on WhatsApp' }}</span>
                            <span class="text-[#74826B] font-mono group-hover:translate-x-0.5 transition-transform">→</span>
                        </a>
                    </div>
                </x-reveal>

            </div>

        </div>

    </div>
</section>
