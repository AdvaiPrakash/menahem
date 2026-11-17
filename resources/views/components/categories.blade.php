@props([
    'content' => [],
])

@php
    $categories = $content['CATEGORIES'] ?? [];
    $items = $categories['items'] ?? [];
@endphp

<section id="shop-categories" class="py-12 sm:py-16 lg:py-20 bg-white border-b border-neutral-200/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header: Title & View All Link -->
        <div class="flex items-center justify-between mb-6 sm:mb-10 pb-4 border-b border-neutral-100">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#2B3326] font-serif">
                    {{ $categories['heading'] ?? 'Shop by Category' }}
                </h2>
                <p class="text-xs sm:text-sm text-[#5C6654] mt-1 font-medium">
                    {{ $categories['subheading'] ?? 'Curtains, Table Decor, Sofa Decor, Towels, Bedsheets & Rugs' }}
                </p>
            </div>
            <a href="{{ route('categories.index') }}" class="text-xs sm:text-sm font-bold text-[#2B3326] hover:text-[#74826B] transition-colors flex items-center gap-1.5 group">
                <span>{{ $categories['view_all_label'] ?? 'View all collections' }}</span>
                <span class="transition-transform group-hover:translate-x-1 font-mono">→</span>
            </a>
        </div>

        <!-- Categories Grid: 2-column grid on mobile, 3 on tablet, 6 in a row on desktop -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 sm:gap-5 lg:gap-6">
            @foreach($items as $index => $item)
                <x-reveal delay="{{ 40 * ($index + 1) }}" class="w-full">
                    <a
                        href="{{ url('/categories/' . $item['slug']) }}"
                        class="group flex flex-col bg-white rounded-xl border border-neutral-200/80 hover:border-[#74826B] hover:shadow-md transition-all duration-300 p-2.5 sm:p-3 text-center w-full"
                    >
                        <!-- Photographic Media Container -->
                        <div class="w-full aspect-[4/4] rounded-lg bg-neutral-100 border border-neutral-200/60 group-hover:border-[#74826B]/50 transition-all duration-300 relative overflow-hidden">
                            <img
                                src="{{ asset($item['image']) }}"
                                alt="{{ $item['name'] }}"
                                loading="lazy"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>

                        <!-- Category Name Underneath -->
                        <span class="mt-2.5 sm:mt-3 text-xs sm:text-sm font-bold text-[#2B3326] group-hover:text-[#74826B] transition-colors line-clamp-1 font-serif">
                            {{ $item['name'] }}
                        </span>
                        @if(!empty($item['desc']))
                            <span class="text-[10px] sm:text-xs text-[#5C6654] line-clamp-1 font-medium mt-0.5">
                                {{ $item['desc'] }}
                            </span>
                        @endif

                        <span class="mt-2 text-[10px] font-semibold text-[#74826B] group-hover:underline flex items-center justify-center gap-0.5">
                            <span>Explore</span>
                            <span class="font-mono">→</span>
                        </span>
                    </a>
                </x-reveal>
            @endforeach
        </div>

    </div>
</section>
