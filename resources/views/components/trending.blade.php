@props([
    'content' => [],
])

@php
    $trending = $content['TRENDING'] ?? [];
    $products = $trending['products'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $viewAllUrl = $trending['view_all_url'] ?? '/products';
    // Exactly 4 cards for 1 complete row on desktop
    $trendingItems = array_slice($products, 0, 4);
@endphp

<section id="trending" class="py-16 sm:py-24 bg-[#F6FAF7] border-b border-[#162B1F]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header: Title, Subtitle & View All Products Link -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 sm:mb-12 gap-4">
            <div>
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-md bg-[#D5F0C0] text-[#2E7D5B] border border-[#B5DFB2]/60 text-xs font-bold uppercase tracking-wider mb-2.5">
                        <span>★</span>
                        <span>Curated Essentials</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#14281E] font-serif">
                        {{ $trending['heading'] ?? 'Trending Home Essentials' }}
                    </h2>
                    <p class="text-sm text-[#4E6858] mt-1.5 max-w-xl">
                        {{ $trending['subheading'] ?? 'Handpicked favorites loved by hundreds of homes across India' }}
                    </p>
                </x-reveal>
            </div>

            <x-reveal delay="150">
                <a href="{{ $viewAllUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#14281E] hover:text-[#2E7D5B] transition-colors group">
                    <span>{{ $trending['view_all_label'] ?? 'View all products' }}</span>
                    <span class="transition-transform group-hover:translate-x-1 font-mono">→</span>
                </a>
            </x-reveal>
        </div>

        <!-- 4 Cards in 1 Row on Desktop Screen -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
            @foreach($trendingItems as $index => $product)
                <x-reveal delay="{{ 100 * ($index % 4 + 1) }}">
                    <x-product-card :product="$product" :active="$loop->first" />
                </x-reveal>
            @endforeach
        </div>

        <!-- Bottom Explore Collection Bar -->
        <div class="mt-12 text-center">
            <x-reveal delay="250">
                <a href="{{ $viewAllUrl }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-[#162B1F] hover:bg-[#180914] text-white text-sm font-bold tracking-wide transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <span>Explore Full Menahem Collection</span>
                    <span class="text-base leading-none font-mono">→</span>
                </a>
            </x-reveal>
        </div>

    </div>
</section>
