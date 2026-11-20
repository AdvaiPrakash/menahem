<x-layouts.app :content="$content" title="Shop by Category · Menahem Home Essentials">
<x-site-shell :content="$content">
<main class="bg-white min-h-screen pt-28 sm:pt-32 pb-20 sm:pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb Navigation -->
        <nav aria-label="Breadcrumb" class="mb-5">
            <ol class="flex items-center flex-wrap gap-2 text-xs text-[#5C6654] font-medium">
                <li>
                    <a href="{{ route('home') }}" class="hover:text-[#74826B] transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Home</span>
                    </a>
                </li>
                <li class="text-neutral-400" aria-hidden="true">›</li>
                <li class="text-[#2B3326] font-semibold" aria-current="page">
                    Shop by Category
                </li>
            </ol>
        </nav>

        <!-- Back Button & Header (Centered on mobile, Left-aligned on websites) -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-neutral-200 text-center sm:text-left items-center sm:items-start">
            <div class="w-full sm:w-auto">
                <div class="mb-3.5 flex justify-center sm:justify-start">
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-lg bg-[#2B3326] hover:bg-[#74826B] text-white text-xs sm:text-[13px] font-bold tracking-wide shadow-xs hover:shadow-md transition-all duration-200 cursor-pointer group active:scale-95"
                    >
                        <svg class="w-4 h-4 text-white group-hover:-translate-x-1 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back to Home</span>
                    </a>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold font-serif text-[#2B3326] tracking-tight text-center sm:text-left">
                    Shop by Category
                </h1>
                <p class="text-xs sm:text-sm text-[#5C6654] mt-1.5 max-w-2xl leading-relaxed text-center sm:text-left mx-auto sm:mx-0">
                    Select a category to explore our thoughtfully tailored pieces and find the perfect essentials for every room.
                </p>
            </div>

            <div class="flex items-center justify-center sm:justify-end gap-3 w-full sm:w-auto">
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-neutral-100 text-[#5C6654] border border-neutral-200">
                    {{ count($categories) }} Main Categories
                </span>
                <a
                    href="{{ route('products.index') }}"
                    class="text-xs font-bold text-[#74826B] hover:text-[#636F5A] transition-colors flex items-center gap-1"
                >
                    <span>View All Products</span>
                    <span class="font-mono">→</span>
                </a>
            </div>
        </div>

        <!-- Category Grid: 2 columns on mobile, 3 on tablet, 3 on desktop -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-3.5 sm:gap-6 lg:gap-8 mt-8">
            @foreach($categories as $key => $item)
                @php
                    $childCount = count($item['subcategories'] ?? []);
                    $targetUrl = url('/categories/' . ($item['slug'] ?? $key));
                @endphp
                <a
                    href="{{ $targetUrl }}"
                    class="group flex flex-col bg-white rounded-xl border border-neutral-200/90 hover:border-[#74826B] hover:shadow-lg transition-all duration-300 p-3 sm:p-4 text-left overflow-hidden relative"
                >
                    <!-- Photographic Card Media -->
                    <div class="w-full aspect-[4/3] sm:aspect-[4/3.2] rounded-lg bg-neutral-100 overflow-hidden relative">
                        <img
                            src="{{ asset($item['image']) }}"
                            alt="{{ $item['name'] }}"
                            loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <!-- Subcategory Count Pill -->
                        <span class="absolute top-2.5 right-2.5 text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/95 text-[#2B3326] shadow-xs backdrop-blur-xs border border-neutral-200/60">
                            {{ $childCount }} {{ $childCount === 1 ? 'Subcategory' : 'Subcategories' }}
                        </span>
                    </div>

                    <!-- Category Content -->
                    <div class="mt-3.5 flex flex-col flex-grow justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-1">
                                <h2 class="text-sm sm:text-base font-bold text-[#2B3326] group-hover:text-[#74826B] transition-colors font-serif leading-snug">
                                    {{ $item['name'] }}
                                </h2>
                                <svg class="w-4 h-4 text-neutral-400 group-hover:text-[#74826B] group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            @if(!empty($item['description']))
                                <p class="text-[11px] sm:text-xs text-[#5C6654] mt-1 line-clamp-2 leading-relaxed">
                                    {{ $item['description'] }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px] font-semibold text-[#74826B]">
                            <span>Explore options</span>
                            <span class="font-mono">→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

    </div>
</main>
</x-site-shell>
</x-layouts.app>
