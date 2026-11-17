@php
    $pageTitle = !empty($categoryInfo['name'])
        ? ($categoryInfo['name'] . ' · Menahem Collection')
        : 'Menahem Collection · Home Essentials';
    $catalog = $content['CATALOG'] ?? [];
    $allProducts = $catalog['products'] ?? [];
    $categories = $catalog['filters']['categories'] ?? [];
    $materials = $catalog['filters']['materials'] ?? [];
    $priceRange = $catalog['filters']['price_range'] ?? ['min' => 349, 'max' => 2999];
    $initialCategory = $selectedCategory ?? 'all';
    $targetSlugs = $targetCategorySlugs ?? [$initialCategory];
@endphp

<x-layouts.app :content="$content" :title="$pageTitle">
<x-site-shell :content="$content">
<div
    x-data="{
        selectedCategory: '{{ $initialCategory }}',
        targetCategorySlugs: {{ json_encode($targetSlugs) }},
        selectedMaterials: [],
        maxPrice: {{ $priceRange['max'] ?? 2999 }},
        searchQuery: '',
        quickFilter: 'all',
        showMoreCategories: false,

        resetCategories() {
            this.selectedCategory = 'all';
            this.targetCategorySlugs = ['all'];
        },
        resetMaterials() {
            this.selectedMaterials = [];
        },
        resetPrice() {
            this.maxPrice = {{ $priceRange['max'] ?? 2999 }};
        },
        resetAll() {
            this.selectedCategory = 'all';
            this.targetCategorySlugs = ['all'];
            this.selectedMaterials = [];
            this.maxPrice = {{ $priceRange['max'] ?? 2999 }};
            this.searchQuery = '';
            this.quickFilter = 'all';
        },

        matches(product) {
            // Category check matching target category slugs or selected category
            if (this.selectedCategory !== 'all') {
                const productSlugs = product.category_slugs || [product.category_slug];
                const matchesAnyTarget = this.targetCategorySlugs.some(target => productSlugs.includes(target));
                const matchesSelected = productSlugs.includes(this.selectedCategory);
                if (!matchesAnyTarget && !matchesSelected) {
                    return false;
                }
            }

            // Quick filter check (New, Sale, Bestseller)
            if (this.quickFilter === 'new' && product.badge !== 'New') return false;
            if (this.quickFilter === 'sale' && product.badge !== 'Sale') return false;
            if (this.quickFilter === 'bestseller' && product.badge !== 'Bestseller') return false;

            // Material check
            if (this.selectedMaterials.length > 0) {
                const matchMat = this.selectedMaterials.some(m => product.material.toLowerCase().includes(m.toLowerCase()));
                if (!matchMat) return false;
            }

            // Price check
            if (product.numeric_price > this.maxPrice) {
                return false;
            }

            // Search query check
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                const inName = product.name.toLowerCase().includes(q);
                const inDesc = product.desc.toLowerCase().includes(q);
                const inCat = product.category.toLowerCase().includes(q);
                const inMat = product.material.toLowerCase().includes(q);
                if (!inName && !inDesc && !inCat && !inMat) return false;
            }

            return true;
        }
    }"
    class="bg-white min-h-screen pt-28 pb-20"
>
    <!-- Secondary Search & Filter Bar (Pure White Minimal Styling) -->
    <div class="border-b border-neutral-200 bg-white/95 backdrop-blur-md sticky top-[80px] sm:top-[96px] z-30 py-3 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                <!-- Left: Category Back to Navigation & Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                    <!-- Browse Categories Button -->
                    <a
                        href="{{ route('categories.index') }}"
                        class="px-3 py-2 rounded-md bg-neutral-100 hover:bg-neutral-200 text-[#2B3326] border border-neutral-200 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1.5 flex-shrink-0"
                    >
                        <svg class="w-3.5 h-3.5 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                        </svg>
                        <span>Categories</span>
                    </a>

                    @if($selectedCategory !== 'all')
                        <a
                            href="{{ route('products.index') }}"
                            class="px-3 py-2 rounded-md bg-[#74826B]/10 text-[#74826B] hover:bg-[#74826B] hover:text-white border border-[#74826B]/30 text-xs font-bold whitespace-nowrap transition-colors flex items-center gap-1 flex-shrink-0"
                        >
                            <span>{{ $categoryInfo['name'] ?? $selectedCategory }}</span>
                            <span class="text-xs">✕</span>
                        </a>
                    @endif

                    <!-- Filter Pill: New Arrivals -->
                    <button
                        type="button"
                        @click="quickFilter = (quickFilter === 'new' ? 'all' : 'new')"
                        :class="quickFilter === 'new' ? 'bg-[#74826B] text-white border-[#74826B]' : 'bg-white text-[#2B3326] border-neutral-300 hover:border-[#74826B]'"
                        class="px-3 py-2 rounded-md border text-xs font-semibold whitespace-nowrap transition-colors flex-shrink-0 cursor-pointer"
                    >
                        New Arrivals
                    </button>

                    <!-- Filter Pill: Sale -->
                    <button
                        type="button"
                        @click="quickFilter = (quickFilter === 'sale' ? 'all' : 'sale')"
                        :class="quickFilter === 'sale' ? 'bg-[#74826B] text-white border-[#74826B]' : 'bg-white text-[#2B3326] border-neutral-300 hover:border-[#74826B]'"
                        class="px-3 py-2 rounded-md border text-xs font-semibold whitespace-nowrap transition-colors flex-shrink-0 cursor-pointer"
                    >
                        Sale
                    </button>

                    <!-- Filter Pill: Bestsellers -->
                    <button
                        type="button"
                        @click="quickFilter = (quickFilter === 'bestseller' ? 'all' : 'bestseller')"
                        :class="quickFilter === 'bestseller' ? 'bg-[#74826B] text-white border-[#74826B]' : 'bg-white text-[#2B3326] border-neutral-300 hover:border-[#74826B]'"
                        class="px-3 py-2 rounded-md border text-xs font-semibold whitespace-nowrap transition-colors flex-shrink-0 cursor-pointer"
                    >
                        Bestsellers
                    </button>
                </div>

                <!-- Right: Search Bar -->
                <div class="flex items-center gap-3 w-full lg:w-auto">
                    <div class="relative w-full lg:w-72">
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Search products..."
                            class="w-full pl-9 pr-4 py-2 rounded-md bg-white border border-neutral-300 text-xs text-[#2B3326] placeholder-neutral-400 focus:outline-hidden focus:border-[#74826B] focus:ring-1 focus:ring-[#74826B] transition-colors"
                        />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-neutral-400">
                            <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8">
        
        <!-- Breadcrumbs Navigation -->
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="flex items-center flex-wrap gap-1.5 sm:gap-2 text-xs text-[#5C6654] font-medium">
                @if(!empty($breadcrumbs))
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
                @else
                    <li>
                        <a href="{{ route('home') }}" class="hover:text-[#74826B] transition-colors">Home</a>
                    </li>
                    <li class="text-neutral-400">›</li>
                    <li>
                        <a href="{{ route('categories.index') }}" class="hover:text-[#74826B] transition-colors">Shop by Category</a>
                    </li>
                    <li class="text-neutral-400">›</li>
                    <li class="text-[#2B3326] font-semibold">All Products</li>
                @endif
            </ol>
        </nav>

        <!-- Back Button & Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-6 mb-8 border-b border-neutral-200">
            <div>
                <div class="mb-2">
                    <a
                        href="{{ $parentBackUrl }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#5C6654] hover:text-[#2B3326] transition-colors py-1 px-2.5 rounded-md hover:bg-neutral-100 -ml-2.5 cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Back to {{ $parentBackName }}</span>
                    </a>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold font-serif tracking-tight text-[#2B3326]">
                    {{ $categoryInfo['name'] ?? 'Menahem Collection' }}
                </h1>
                <p class="text-xs sm:text-sm text-[#5C6654] mt-1 max-w-xl">
                    {{ $categoryInfo['description'] ?? 'Thoughtfully crafted home essentials designed for comfort and everyday luxury.' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-neutral-100 text-[#5C6654] border border-neutral-200">
                    {{ count($allProducts) }} products in catalog
                </span>
            </div>
        </div>

        <!-- Two-Column Layout: Filter Sidebar + Product Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Filter Sidebar (Pure White, Subtle Border) -->
            <aside class="lg:col-span-3 bg-white rounded-xl p-5 sm:p-6 border border-neutral-200 shadow-xs space-y-6 sticky top-36">
                
                <!-- Category Section -->
                <div>
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-neutral-200">
                        <h2 class="text-xs font-bold text-[#2B3326] uppercase tracking-wider">
                            Active Category
                        </h2>
                        @if($selectedCategory !== 'all')
                            <a
                                href="{{ route('products.index') }}"
                                class="text-xs text-[#74826B] hover:underline font-semibold"
                            >
                                Clear
                            </a>
                        @endif
                    </div>

                    @if(!empty($categoryInfo))
                        <div class="p-3 rounded-lg bg-neutral-50 border border-neutral-200/80 mb-3">
                            <span class="text-[10px] uppercase font-bold text-[#74826B] tracking-wider block mb-1">Current Filter</span>
                            <div class="text-xs font-bold text-[#2B3326] font-serif">{{ $categoryInfo['name'] }}</div>
                            @if(!empty($categoryInfo['description']))
                                <p class="text-[11px] text-[#5C6654] mt-0.5 leading-snug">{{ $categoryInfo['description'] }}</p>
                            @endif
                        </div>
                    @endif

                    <a
                        href="{{ route('categories.index') }}"
                        class="w-full py-2 px-3 rounded-md border border-neutral-200 hover:border-[#74826B] bg-white text-xs font-semibold text-[#2B3326] hover:text-[#74826B] transition-colors flex items-center justify-between"
                    >
                        <span>Explore All Categories</span>
                        <span class="font-mono text-neutral-400">→</span>
                    </a>
                </div>

                <!-- Material Filter -->
                <div>
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-neutral-200">
                        <h2 class="text-xs font-bold text-[#2B3326] uppercase tracking-wider">
                            Material
                        </h2>
                        <button
                            type="button"
                            @click="resetMaterials()"
                            class="text-xs text-[#5C6654] hover:text-[#74826B] transition-colors cursor-pointer"
                        >
                            Reset
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach($materials as $mat)
                            <label class="flex items-center justify-between text-xs text-[#2B3326] cursor-pointer hover:text-[#74826B] transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        value="{{ $mat['slug'] }}"
                                        x-model="selectedMaterials"
                                        class="w-4 h-4 rounded-sm text-[#74826B] focus:ring-[#74826B] border-neutral-300"
                                    />
                                    <span class="font-medium">{{ $mat['name'] }}</span>
                                </div>
                                <span class="text-[11px] text-[#5C6654]">{{ $mat['count'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Price Slider -->
                <div>
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-neutral-200">
                        <h2 class="text-xs font-bold text-[#2B3326] uppercase tracking-wider">
                            Max Price
                        </h2>
                        <button
                            type="button"
                            @click="resetPrice()"
                            class="text-xs text-[#5C6654] hover:text-[#74826B] transition-colors cursor-pointer"
                        >
                            Reset
                        </button>
                    </div>

                    <div class="mt-2">
                        <input
                            type="range"
                            min="{{ $priceRange['min'] ?? 349 }}"
                            max="{{ $priceRange['max'] ?? 2999 }}"
                            step="50"
                            x-model="maxPrice"
                            class="w-full accent-[#74826B] cursor-pointer"
                        />
                        <div class="flex items-center justify-between text-[11px] font-bold text-[#5C6654] mt-2">
                            <span>₹{{ $priceRange['min'] ?? 349 }}</span>
                            <span class="text-xs font-bold text-[#2B3326] bg-neutral-100 px-2 py-0.5 rounded-md border border-neutral-200">
                                Up to ₹<span x-text="maxPrice"></span>
                            </span>
                            <span>₹{{ $priceRange['max'] ?? 2999 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Clear All Filters Button -->
                <div class="pt-2">
                    <button
                        type="button"
                        @click="resetAll()"
                        class="w-full py-2 px-4 rounded-md border border-neutral-300 text-xs font-bold text-[#2B3326] hover:bg-[#74826B] hover:text-white transition-all text-center cursor-pointer"
                    >
                        Clear All Filters
                    </button>
                </div>

            </aside>

            <!-- Right Column: Product Grid -->
            <main class="lg:col-span-9">
                <!-- 2-col on mobile, 3-col on tablet/desktop -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                    @foreach($allProducts as $index => $product)
                        <div
                            x-show="matches({{ json_encode($product) }})"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                        >
                            <x-product-card :product="$product" :active="$index === 0" />
                        </div>
                    @endforeach
                </div>

                <!-- Empty State -->
                <div
                    x-show="false"
                    id="empty-state-card"
                    class="rounded-xl bg-white p-10 sm:p-12 text-center border border-neutral-200 shadow-xs my-8"
                >
                    <div class="w-12 h-12 rounded-full bg-neutral-100 text-[#74826B] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-[#2B3326]">No products found</h3>
                    <p class="text-xs text-[#5C6654] mt-1 max-w-sm mx-auto">
                        No items match your active filters. Try clearing your search query or reset your filters.
                    </p>
                    <button
                        type="button"
                        @click="resetAll()"
                        class="mt-4 px-5 py-2 rounded-md bg-[#74826B] text-white text-xs font-bold hover:bg-[#636F5A] transition-colors cursor-pointer"
                    >
                        Reset All Filters
                    </button>
                </div>
            </main>

        </div>

    </div>
</div>
</x-site-shell>
</x-layouts.app>
