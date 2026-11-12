<x-layouts.app :content="$content" title="Menahem Collection · Home Essentials">
<x-site-shell :content="$content">
@php
    $catalog = $content['CATALOG'] ?? [];
    $allProducts = $catalog['products'] ?? [];
    $categories = $catalog['filters']['categories'] ?? [];
    $materials = $catalog['filters']['materials'] ?? [];
    $priceRange = $catalog['filters']['price_range'] ?? ['min' => 349, 'max' => 2999];
    $initialCategory = $selectedCategory ?? 'all';
@endphp

<div
    x-data="{
        selectedCategory: '{{ $initialCategory }}',
        selectedMaterials: [],
        maxPrice: {{ $priceRange['max'] ?? 2999 }},
        searchQuery: '',
        quickFilter: 'all',
        showMoreCategories: false,

        resetCategories() {
            this.selectedCategory = 'all';
        },
        resetMaterials() {
            this.selectedMaterials = [];
        },
        resetPrice() {
            this.maxPrice = {{ $priceRange['max'] ?? 2999 }};
        },
        resetAll() {
            this.selectedCategory = 'all';
            this.selectedMaterials = [];
            this.maxPrice = {{ $priceRange['max'] ?? 2999 }};
            this.searchQuery = '';
            this.quickFilter = 'all';
        },

        matches(product) {
            // Category check
            if (this.selectedCategory !== 'all' && product.category_slug !== this.selectedCategory) {
                return false;
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
        },

        countVisible() {
            return {{ count($allProducts) }};
        }
    }"
    class="bg-[#F6FAF7] min-h-screen pt-28 pb-20"
>
    <!-- Secondary Search & Filter Navigation Bar (Matches Reference Layout) -->
    <div class="border-b border-[#162B1F]/10 bg-white/80 backdrop-blur-md sticky top-[80px] sm:top-[96px] z-30 py-3 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                
                <!-- Left: Category Dropdown & Quick Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0 scrollbar-none">
                    <!-- Dropdown Trigger (Dark Khaki) -->
                    <div class="relative flex-shrink-0">
                        <select
                            x-model="selectedCategory"
                            class="appearance-none pl-3.5 pr-8 py-2 rounded-xl bg-[#EAF4EE] border border-[#162B1F]/15 text-xs font-bold text-[#14281E] cursor-pointer hover:border-[#2E7D5B] focus:outline-hidden focus:ring-1 focus:ring-[#2E7D5B] transition-colors"
                        >
                            <option value="all">All Collections</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat['slug'] }}">{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-[#4E6858]">
                            <svg class="w-3.5 h-3.5" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Filter Pill: New Arrivals -->
                    <button
                        type="button"
                        @click="quickFilter = (quickFilter === 'new' ? 'all' : 'new')"
                        :class="quickFilter === 'new' ? 'bg-[#162B1F] text-white border-[#162B1F]' : 'bg-white text-[#14281E] border-[#162B1F]/20 hover:border-[#162B1F]'"
                        class="px-3.5 py-2 rounded-xl border text-xs font-semibold whitespace-nowrap transition-colors flex-shrink-0"
                    >
                        New Arrivals
                    </button>

                    <!-- Filter Pill: Sale -->
                    <button
                        type="button"
                        @click="quickFilter = (quickFilter === 'sale' ? 'all' : 'sale')"
                        :class="quickFilter === 'sale' ? 'bg-[#162B1F] text-white border-[#162B1F]' : 'bg-white text-[#14281E] border-[#162B1F]/20 hover:border-[#162B1F]'"
                        class="px-3.5 py-2 rounded-xl border text-xs font-semibold whitespace-nowrap transition-colors flex-shrink-0"
                    >
                        Sale
                    </button>

                    <!-- Filter Pill: Bestsellers -->
                    <button
                        type="button"
                        @click="quickFilter = (quickFilter === 'bestseller' ? 'all' : 'bestseller')"
                        :class="quickFilter === 'bestseller' ? 'bg-[#162B1F] text-white border-[#162B1F]' : 'bg-white text-[#14281E] border-[#162B1F]/20 hover:border-[#162B1F]'"
                        class="px-3.5 py-2 rounded-xl border text-xs font-semibold whitespace-nowrap transition-colors flex-shrink-0"
                    >
                        Bestsellers
                    </button>
                </div>

                <!-- Center/Right: Search Bar & Quick Category Chips -->
                <div class="flex items-center gap-3 w-full lg:w-auto">
                    <!-- Search Input Field with Magnifier -->
                    <div class="relative w-full lg:w-72">
                        <input
                            type="text"
                            x-model="searchQuery"
                            placeholder="Search products..."
                            class="w-full pl-9 pr-4 py-2 rounded-xl bg-white border border-[#162B1F]/20 text-xs text-[#14281E] placeholder-[#4E6858]/60 focus:outline-hidden focus:border-[#2E7D5B] focus:ring-1 focus:ring-[#2E7D5B] transition-colors"
                        />
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[#4E6858]">
                            <svg class="w-4 h-4" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Category Pills (Desktop tags) -->
                    <div class="hidden xl:flex items-center gap-1.5 flex-shrink-0">
                        @foreach($categories as $cat)
                            <button
                                type="button"
                                @click="selectedCategory = (selectedCategory === '{{ $cat['slug'] }}' ? 'all' : '{{ $cat['slug'] }}')"
                                :class="selectedCategory === '{{ $cat['slug'] }}' ? 'bg-[#162B1F] text-white border-[#162B1F]' : 'bg-white text-[#4E6858] border-[#162B1F]/15 hover:text-[#14281E] hover:border-[#162B1F]/40'"
                                class="px-2.5 py-1.5 rounded-lg border text-[11px] font-medium transition-colors"
                            >
                                {{ $cat['name'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        
        <!-- Collection Hero Banner (Matches Reference Top Showcase) -->
        <div class="rounded-3xl overflow-hidden bg-[#EAF4EE] border border-[#162B1F]/15 shadow-xs mb-10">
            <div class="grid grid-cols-1 md:grid-cols-12 items-center">
                
                <!-- Left Banner Graphic: Hanging Linens / Lifestyle Preview -->
                <div class="md:col-span-5 h-48 md:h-64 relative overflow-hidden bg-[#1C3526]">
                    <img
                        src="{{ asset($catalog['banner_image'] ?? '/images/hero/hero-lifestyle.webp') }}"
                        alt="Menahem Curated Collection"
                        class="w-full h-full object-cover object-center"
                    />
                    <div class="absolute inset-0 bg-linear-to-r from-black/40 via-transparent to-transparent"></div>
                </div>

                <!-- Right Banner Text -->
                <div class="md:col-span-7 p-6 sm:p-10 flex flex-col items-start justify-center text-left">
                    <span class="text-xs font-bold tracking-widest uppercase text-[#2E7D5B] mb-2">
                        - Collections
                    </span>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold font-serif text-[#14281E] tracking-tight leading-tight">
                        {{ $catalog['subtitle'] ?? 'Explore The Various Collection of Menahem' }}
                    </h1>
                    <p class="text-xs sm:text-sm text-[#4E6858] mt-2 max-w-xl leading-relaxed">
                        {{ $catalog['description'] ?? 'Don’t miss out to shopping collection from us! You’ll not be let down.' }}
                    </p>
                </div>

            </div>
        </div>

        <!-- Breadcrumbs & Collection Heading (Matches Reference) -->
        <div class="mb-8">
            <nav class="flex items-center gap-2 text-xs text-[#4E6858] mb-2 font-medium">
                <a href="{{ route('home') }}" class="hover:text-[#2E7D5B] transition-colors">Home</a>
                <span class="text-gray-400">•</span>
                <span class="text-[#14281E] font-semibold">Menahem Collection</span>
            </nav>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl sm:text-3xl font-bold font-serif tracking-tight text-[#14281E]">
                    Menahem Collection
                </h2>
                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#EAF4EE] text-[#4E6858] border border-[#162B1F]/10">
                    {{ count($allProducts) }} products
                </span>
            </div>
        </div>

        <!-- Two-Column Layout: Sidebar Filters + 3-Column Product Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- Left Column: Filter Sidebar (Matches Reference Layout) -->
            <aside class="lg:col-span-3 bg-white rounded-3xl p-6 border border-[#162B1F]/10 shadow-xs space-y-7 sticky top-36">
                
                <!-- Filter Section 1: Category -->
                <div>
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#162B1F]/10">
                        <h3 class="text-sm font-bold text-[#14281E] uppercase tracking-wider">
                            Category
                        </h3>
                        <button
                            type="button"
                            @click="resetCategories()"
                            class="text-xs text-[#4E6858] hover:text-[#2E7D5B] transition-colors"
                        >
                            Reset
                        </button>
                    </div>

                    <!-- Category Radio / Checkbox List -->
                    <div class="space-y-2.5">
                        <label class="flex items-center justify-between text-xs text-[#14281E] cursor-pointer hover:text-[#2E7D5B] transition-colors">
                            <div class="flex items-center gap-2.5">
                                <input
                                    type="radio"
                                    name="cat_filter"
                                    value="all"
                                    x-model="selectedCategory"
                                    class="w-4 h-4 rounded-sm text-[#162B1F] focus:ring-[#2E7D5B] border-gray-300"
                                />
                                <span class="font-medium">All Products</span>
                            </div>
                            <span class="text-[11px] text-[#4E6858]">{{ count($allProducts) }}</span>
                        </label>

                        @foreach($categories as $index => $cat)
                            <label
                                x-show="{{ $index < 4 ? 'true' : 'showMoreCategories' }}"
                                class="flex items-center justify-between text-xs text-[#14281E] cursor-pointer hover:text-[#2E7D5B] transition-colors"
                            >
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="radio"
                                        name="cat_filter"
                                        value="{{ $cat['slug'] }}"
                                        x-model="selectedCategory"
                                        class="w-4 h-4 rounded-sm text-[#162B1F] focus:ring-[#2E7D5B] border-gray-300"
                                    />
                                    <span class="font-medium">{{ $cat['name'] }}</span>
                                </div>
                                <span class="text-[11px] text-[#4E6858]">{{ $cat['count'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    @if(count($categories) > 4)
                        <button
                            type="button"
                            @click="showMoreCategories = !showMoreCategories"
                            class="mt-3.5 text-xs font-bold text-[#162B1F] hover:text-[#2E7D5B] transition-colors flex items-center gap-1"
                        >
                            <span x-text="showMoreCategories ? 'Show less' : 'Show more'"></span>
                            <span x-text="showMoreCategories ? '↑' : '↓'"></span>
                        </button>
                    @endif
                </div>

                <!-- Filter Section 2: Material / Fabric -->
                <div>
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-[#162B1F]/10">
                        <h3 class="text-sm font-bold text-[#14281E] uppercase tracking-wider">
                            Material
                        </h3>
                        <button
                            type="button"
                            @click="resetMaterials()"
                            class="text-xs text-[#4E6858] hover:text-[#2E7D5B] transition-colors"
                        >
                            Reset
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        @foreach($materials as $mat)
                            <label class="flex items-center justify-between text-xs text-[#14281E] cursor-pointer hover:text-[#2E7D5B] transition-colors">
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="checkbox"
                                        value="{{ $mat['slug'] }}"
                                        x-model="selectedMaterials"
                                        class="w-4 h-4 rounded-sm text-[#162B1F] focus:ring-[#2E7D5B] border-gray-300"
                                    />
                                    <span class="font-medium">{{ $mat['name'] }}</span>
                                </div>
                                <span class="text-[11px] text-[#4E6858]">{{ $mat['count'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Filter Section 3: Price Slider -->
                <div>
                    <div class="flex items-center justify-between mb-3 pb-2 border-b border-[#162B1F]/10">
                        <h3 class="text-sm font-bold text-[#14281E] uppercase tracking-wider">
                            Price
                        </h3>
                        <button
                            type="button"
                            @click="resetPrice()"
                            class="text-xs text-[#4E6858] hover:text-[#2E7D5B] transition-colors"
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
                            class="w-full accent-[#162B1F] cursor-pointer"
                        />
                        <div class="flex items-center justify-between text-[11px] font-bold text-[#4E6858] mt-2">
                            <span>₹{{ $priceRange['min'] ?? 349 }}</span>
                            <span class="text-xs font-bold text-[#14281E] bg-[#EAF4EE] px-2 py-0.5 rounded-md border border-[#162B1F]/15">
                                Up to ₹<span x-text="maxPrice"></span>
                            </span>
                            <span>₹{{ $priceRange['max'] ?? 2999 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Reset All Filters Button -->
                <div class="pt-2">
                    <button
                        type="button"
                        @click="resetAll()"
                        class="w-full py-2.5 px-4 rounded-xl border border-[#162B1F]/20 text-xs font-bold text-[#14281E] hover:bg-[#162B1F] hover:text-white transition-all text-center"
                    >
                        Clear All Filters
                    </button>
                </div>

            </aside>

            <!-- Right Column: 3-Column Product Grid (Matches Reference Layout) -->
            <main class="lg:col-span-9">
                
                <!-- 3-Column Grid of Product Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-7">
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

                <!-- Empty State (Shown when filters match no products) -->
                <div
                    x-show="true"
                    x-cloak
                    id="empty-state-card"
                    class="hidden rounded-3xl bg-white p-12 text-center border border-[#162B1F]/10 shadow-xs my-8"
                >
                    <div class="w-16 h-16 rounded-full bg-[#EAF4EE] text-[#2E7D5B] flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#14281E]">No products found</h3>
                    <p class="text-xs text-[#4E6858] mt-1 max-w-sm mx-auto">
                        No home essentials match your selected filters. Try clearing your search query or adjusting your filters.
                    </p>
                    <button
                        type="button"
                        @click="resetAll()"
                        class="mt-5 px-6 py-2.5 rounded-xl bg-[#162B1F] text-white text-xs font-bold hover:bg-[#180914] transition-colors"
                    >
                        Reset All Filters
                    </button>
                </div>

            </main>

        </div>

    </div>
</div>

<script>
    // Simple helper to reveal empty state if 0 cards match
    document.addEventListener('alpine:init', () => {
        // Alpine handles reactive filtering
    });
</script>
</x-site-shell>
</x-layouts.app>
