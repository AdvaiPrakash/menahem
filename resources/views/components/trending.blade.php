@props([
    'content' => [],
])

@php
    $trending = $content['TRENDING'] ?? [];
    $products = $trending['products'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $viewAllUrl = $trending['view_all_url'] ?? '/products';
@endphp

<section id="trending" class="py-16 sm:py-24 bg-[#faf9f5] border-b border-[#353819]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header: Title, Subtitle & View All Products Link -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 sm:mb-12 gap-4">
            <div>
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-md bg-[#AC9661]/15 text-[#AC9661] text-xs font-bold uppercase tracking-wider mb-2.5">
                        <span>★</span>
                        <span>Curated Essentials</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#212411]">
                        {{ $trending['heading'] ?? 'Trending Home Essentials' }}
                    </h2>
                    <p class="text-sm text-[#595b45] mt-1.5 max-w-xl">
                        {{ $trending['subheading'] ?? 'Handpicked favorites loved by hundreds of homes across India' }}
                    </p>
                </x-reveal>
            </div>

            <x-reveal delay="150">
                <a href="{{ $viewAllUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#212411] hover:text-[#AC9661] transition-colors group">
                    <span>{{ $trending['view_all_label'] ?? 'View all products' }}</span>
                    <span class="transition-transform group-hover:translate-x-1 font-mono">→</span>
                </a>
            </x-reveal>
        </div>

        <!-- 3-Column Product Grid (Matches Reference Product Card UI) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @foreach($products as $index => $product)
                <x-reveal delay="{{ 100 * ($index % 3 + 1) }}">
                    <x-product-card :product="$product" :active="$loop->first" />
                </x-reveal>
            @endforeach
        </div>

        <!-- Bottom Explore Collection Bar -->
        <div class="mt-12 text-center">
            <x-reveal delay="250">
                <a href="{{ $viewAllUrl }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-[#353819] hover:bg-[#282b13] text-white text-sm font-bold tracking-wide transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                    <span>Explore Full Menahem Collection</span>
                    <span class="text-base leading-none font-mono">→</span>
                </a>
            </x-reveal>
        </div>

    </div>
</section>
