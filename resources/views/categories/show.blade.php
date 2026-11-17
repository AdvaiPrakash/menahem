<x-layouts.app :content="$content" :title="($category['name'] ?? 'Category') . ' · Menahem Collection'">
<x-site-shell :content="$content">
<main class="bg-white min-h-screen pt-28 sm:pt-32 pb-20 sm:pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb Navigation -->
        <nav aria-label="Breadcrumb" class="mb-5">
            <ol class="flex items-center flex-wrap gap-1.5 sm:gap-2 text-xs text-[#5C6654] font-medium">
                @foreach($breadcrumbs as $index => $crumb)
                    @if(!empty($crumb['url']) && !$crumb['is_current'])
                        <li>
                            <a href="{{ $crumb['url'] }}" class="hover:text-[#74826B] transition-colors flex items-center gap-1">
                                @if($index === 0)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                    </svg>
                                @endif
                                <span>{{ $crumb['name'] }}</span>
                            </a>
                        </li>
                        <li class="text-neutral-400" aria-hidden="true">›</li>
                    @else
                        <li class="text-[#2B3326] font-semibold truncate max-w-[200px] sm:max-w-none" aria-current="page">
                            {{ $crumb['name'] }}
                        </li>
                    @endif
                @endforeach
            </ol>
        </nav>

        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 border-b border-neutral-200">
            <div>
                <!-- Easy Back Navigation Option -->
                <div class="mb-2.5">
                    <a
                        href="{{ $parentUrl }}"
                        onclick="if (window.history.length > 1 && document.referrer.includes(window.location.host)) { window.history.back(); return false; }"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#5C6654] hover:text-[#2B3326] transition-colors py-1 px-2.5 rounded-md hover:bg-neutral-100 -ml-2.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back to {{ $parentName }}</span>
                    </a>
                </div>

                <!-- Current Category Heading -->
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold font-serif text-[#2B3326] tracking-tight">
                    {{ $category['name'] }}
                </h1>
                <p class="text-xs sm:text-sm text-[#5C6654] mt-1.5 max-w-2xl leading-relaxed">
                    {{ $category['description'] ?? 'Choose a category below to browse our handcrafted products.' }}
                </p>
            </div>

            <!-- Subcategory counter & all products link -->
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-neutral-100 text-[#5C6654] border border-neutral-200">
                    {{ count($subcategories) }} {{ count($subcategories) === 1 ? 'Option' : 'Options' }} Available
                </span>
                <a
                    href="{{ route('products.index', ['category' => $category['slug']]) }}"
                    class="text-xs font-bold text-[#74826B] hover:text-[#636F5A] transition-colors flex items-center gap-1"
                >
                    <span>View all products in this category</span>
                    <span class="font-mono">→</span>
                </a>
            </div>
        </div>

        <!-- Subcategories Grid: 2 columns on mobile, 3 on tablet, 4 on desktop -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6 mt-8">
            @foreach($subcategories as $childKey => $child)
                @php
                    $hasChildren = !empty($child['subcategories']);
                    $childPath = $path . '/' . ($child['slug'] ?? $childKey);
                    $targetUrl = $hasChildren
                        ? url('/categories/' . $childPath)
                        : route('products.index', ['category' => $child['slug'] ?? $childKey]);
                    $subCount = count($child['subcategories'] ?? []);
                @endphp
                <a
                    href="{{ $targetUrl }}"
                    class="group flex flex-col bg-white rounded-xl border border-neutral-200/90 hover:border-[#74826B] hover:shadow-lg transition-all duration-300 p-3 sm:p-4 text-left overflow-hidden relative"
                >
                    <!-- Card Media Box with Consistent Aspect Ratio -->
                    <div class="w-full aspect-[4/3] sm:aspect-[4/3.2] rounded-lg bg-neutral-100 overflow-hidden relative">
                        <img
                            src="{{ asset($child['image'] ?? $category['image']) }}"
                            alt="{{ $child['name'] }}"
                            loading="lazy"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <!-- Status Badge: Subcategory count or direct Products pill -->
                        <span class="absolute top-2.5 right-2.5 text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-full bg-white/95 text-[#2B3326] shadow-xs backdrop-blur-xs border border-neutral-200/60">
                            @if($hasChildren)
                                {{ $subCount }} {{ $subCount === 1 ? 'size/type' : 'sizes/types' }}
                            @else
                                View Products
                            @endif
                        </span>
                    </div>

                    <!-- Card Body -->
                    <div class="mt-3.5 flex flex-col flex-grow justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-1">
                                <h2 class="text-sm sm:text-base font-bold text-[#2B3326] group-hover:text-[#74826B] transition-colors font-serif leading-snug">
                                    {{ $child['name'] }}
                                </h2>
                                <svg class="w-4 h-4 text-neutral-400 group-hover:text-[#74826B] group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            @if(!empty($child['description']))
                                <p class="text-[11px] sm:text-xs text-[#5C6654] mt-1 line-clamp-2 leading-relaxed">
                                    {{ $child['description'] }}
                                </p>
                            @endif
                        </div>

                        <!-- Action Indicator Footer -->
                        <div class="mt-3 pt-2.5 border-t border-neutral-100 flex items-center justify-between text-[11px] font-semibold text-[#74826B]">
                            <span>{{ $hasChildren ? 'Select option' : 'Shop collection' }}</span>
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
