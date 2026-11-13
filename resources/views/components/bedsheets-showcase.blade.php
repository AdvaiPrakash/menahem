@props([
    'content' => [],
])

@php
    $bedsheets = $content['BEDSHEETS_SHOWCASE'] ?? [];
    $products = $bedsheets['products'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $viewAllUrl = $bedsheets['view_all_url'] ?? route('products.index', ['category' => 'bedsheets']);
    // Exactly 4 curated cards for 1 balanced row on desktop
    $bedsheetItems = array_slice($products, 0, 4);
@endphp

<section id="bedsheets-showcase" class="py-16 sm:py-24 bg-[#FAF7F0] border-b border-[#D7C7B2]/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header: Title, Subtitle & View All Bedsheets Link -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 sm:mb-12 gap-4">
            <div>
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#E8E2D5] text-[#2B3326] border border-[#D7C7B2] text-xs font-bold uppercase tracking-wider mb-2.5 shadow-2xs">
                        <span>★</span>
                        <span>{{ $bedsheets['badge'] ?? 'Bed Linen & Dohars' }}</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#2B3326] font-serif">
                        {{ $bedsheets['heading'] ?? 'Handcrafted Bedsheets & Mulmul Dohars' }}
                    </h2>
                    <p class="text-sm text-[#5C6654] mt-1.5 max-w-xl font-medium">
                        {{ $bedsheets['subheading'] ?? 'Breathable 400TC weaves and feather-soft reversible dohars tailored for pure slumber' }}
                    </p>
                </x-reveal>
            </div>

            <x-reveal delay="150">
                <a href="{{ $viewAllUrl }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#2B3326] hover:text-[#74826B] transition-colors group">
                    <span>{{ $bedsheets['view_all_label'] ?? 'View all bedsheets' }}</span>
                    <span class="transition-transform group-hover:translate-x-1 font-mono">→</span>
                </a>
            </x-reveal>
        </div>

        <!-- 4 Cards in 1 Row on Desktop -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
            @foreach($bedsheetItems as $index => $product)
                <x-reveal delay="{{ 100 * ($index % 4 + 1) }}">
                    <x-product-card :product="$product" :active="$loop->first" />
                </x-reveal>
            @endforeach
        </div>

        <!-- Bottom Explore Collection Action -->
        <div class="mt-12 text-center">
            <x-reveal delay="250">
                <a href="{{ $viewAllUrl }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-sm font-bold tracking-wide transition-all shadow-md shadow-[#74826B]/25 hover:shadow-lg transform hover:-translate-y-0.5 active:scale-98">
                    <span>{{ $bedsheets['cta_label'] ?? 'Explore Full Bedding Collection' }}</span>
                    <span class="text-base leading-none font-mono">→</span>
                </a>
            </x-reveal>
        </div>

    </div>
</section>
