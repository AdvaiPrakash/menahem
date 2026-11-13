@props([
    'content' => [],
])

@php
    $roomsShowcase = $content['ROOMS_SHOWCASE'] ?? [];
    $rooms = $roomsShowcase['rooms'] ?? [];
@endphp

<section id="shop-by-rooms" class="py-16 sm:py-24 bg-[#FAF7F0] border-b border-[#D7C7B2]/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header: Title & Subtitle -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 sm:mb-14 gap-4">
            <div>
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#E8E2D5] text-[#2B3326] border border-[#D7C7B2] text-xs font-bold uppercase tracking-wider mb-2.5 shadow-2xs">
                        <span>★</span>
                        <span>{{ $roomsShowcase['badge'] ?? 'Curated Living Spaces' }}</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#2B3326] font-serif">
                        {{ $roomsShowcase['heading'] ?? 'Shop by Rooms' }}
                    </h2>
                    <p class="text-sm text-[#5C6654] mt-1.5 max-w-xl font-medium">
                        {{ $roomsShowcase['subheading'] ?? 'Thoughtfully tailored textiles and handcrafted essentials designed for every corner of your home' }}
                    </p>
                </x-reveal>
            </div>

            <x-reveal delay="150">
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#2B3326] hover:text-[#74826B] transition-colors group">
                    <span>Explore All Spaces</span>
                    <span class="transition-transform group-hover:translate-x-1 font-mono">→</span>
                </a>
            </x-reveal>
        </div>

        <!-- 3 Rooms Grid (Shop for Bedroom, Shop for Livingroom, Shop for Kitchen) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            @foreach($rooms as $index => $room)
                <x-reveal delay="{{ 100 * ($index + 1) }}" class="h-full">
                    <div class="group relative flex flex-col h-full rounded-lg overflow-hidden bg-white border border-[#D7C7B2] hover:border-[#74826B] hover:shadow-xl transition-all duration-500">
                        
                        <!-- Room Photographic Preview Frame -->
                        <div class="relative w-full aspect-[4/3] overflow-hidden bg-[#F3EFE6]">
                            <img
                                src="{{ asset($room['image']) }}"
                                alt="{{ $room['alt'] ?? $room['title'] }}"
                                loading="lazy"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                            />
                            
                            <!-- Floating Badge -->
                            <span class="absolute top-4 left-4 px-3 py-1 rounded-md bg-[#FAF7F0]/95 backdrop-blur-md text-[#2B3326] text-[11px] font-bold uppercase tracking-wider border border-[#D7C7B2] shadow-xs">
                                {{ $room['badge'] }}
                            </span>
                        </div>

                        <!-- Card Body & Action -->
                        <div class="p-6 sm:p-7 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl sm:text-2xl font-bold font-serif text-[#2B3326] group-hover:text-[#74826B] transition-colors">
                                    {{ $room['title'] }}
                                </h3>
                                <p class="text-xs sm:text-sm text-[#5C6654] mt-2.5 leading-relaxed font-normal">
                                    {{ $room['description'] }}
                                </p>

                                <!-- Room Highlight Tags -->
                                <div class="flex flex-wrap gap-1.5 mt-4">
                                    @foreach($room['tags'] ?? [] as $tag)
                                        <span class="px-2.5 py-1 rounded-sm bg-[#F3EFE6] text-[#5C6654] text-[11px] font-semibold border border-[#D7C7B2]/70">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- CTA Button linking to filtered products -->
                            <a
                                href="{{ $room['url'] }}"
                                class="mt-6 inline-flex items-center justify-between w-full py-3.5 px-5 rounded-md bg-[#FAF7F0] group-hover:bg-[#74826B] text-[#2B3326] group-hover:text-white border border-[#D7C7B2] group-hover:border-[#74826B] text-xs font-bold transition-all duration-300 shadow-2xs"
                            >
                                <span>{{ $room['cta'] }}</span>
                                <span class="text-sm font-mono group-hover:translate-x-1 transition-transform">→</span>
                            </a>
                        </div>

                    </div>
                </x-reveal>
            @endforeach
        </div>

    </div>
</section>
