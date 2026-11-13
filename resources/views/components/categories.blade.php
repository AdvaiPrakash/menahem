@props([
    'content' => [],
])

@php
    $categories = $content['CATEGORIES'] ?? [];
    $allItems = $categories['items'] ?? [];
    // Show 5 categories in a row on desktop, 2 in a row on mobile
    $items = array_slice($allItems, 0, 5);
@endphp

<section id="shop-categories" class="py-12 sm:py-16 lg:py-20 bg-[#FAF7F0] border-b border-[#D7C7B2]/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header: Title & View All Link -->
        <div class="flex items-center justify-between mb-6 sm:mb-10">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#2B3326] font-serif">
                    {{ $categories['heading'] ?? 'Shop by Category' }}
                </h2>
                <p class="text-xs sm:text-sm text-[#5C6654] mt-1 font-medium">
                    {{ $categories['subheading'] ?? 'Bedsheets, Curtains, Table Decor, Sofa Living & Luxury Towels' }}
                </p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs sm:text-sm font-bold text-[#2B3326] hover:text-[#74826B] transition-colors flex items-center gap-1.5 group">
                <span>{{ $categories['view_all_label'] ?? 'View all collections' }}</span>
                <span class="transition-transform group-hover:translate-x-1 font-mono">→</span>
            </a>
        </div>

        <!-- Categories Grid: 5 in a row on desktop, 2 in a row on mobile with real images -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-5 lg:gap-6">
            @foreach($items as $index => $item)
                <x-reveal delay="{{ 50 * ($index + 1) }}" class="{{ $index === 4 ? 'col-span-2 sm:col-span-1 max-w-[calc(50%-0.4375rem)] mx-auto sm:max-w-none w-full' : 'w-full' }}">
                    <a
                        href="{{ route('products.index', ['category' => $item['slug']]) }}"
                        class="group flex flex-col items-center text-center w-full"
                    >
                        <!-- Rounded Photographic Card Box -->
                        <div class="w-full aspect-[4/4.2] sm:aspect-square rounded-lg bg-[#F3EFE6] border border-[#D7C7B2] group-hover:border-[#74826B] group-hover:shadow-md transition-all duration-300 relative overflow-hidden">
                            <img
                                src="{{ asset($item['image']) }}"
                                alt="{{ $item['name'] }}"
                                loading="lazy"
                                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            />
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
                    </a>
                </x-reveal>
            @endforeach
        </div>

    </div>
</section>
