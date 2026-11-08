@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $nav = $content['NAV'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<header class="w-full fixed top-0 left-0 z-50 shadow-md">
    <!-- Main Navigation Bar (Olive Green #3c3d1b with Enriched Gold Medallion Logo) -->
    <nav id="main-navbar" class="w-full bg-[#3c3d1b] transition-all duration-300 py-2 sm:py-2.5 px-3 sm:px-8 border-b border-[#2e2f14]">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            
            <!-- Mobile Left: Hamburger Container (w-20 to balance the right icons on mobile, hidden on desktop) -->
            <div class="w-20 sm:w-24 flex items-center justify-start lg:hidden flex-shrink-0">
                <button
                    id="mobile-menu-btn"
                    type="button"
                    aria-label="Toggle navigation"
                    class="p-1.5 -ml-1 text-white hover:text-[#f4b8da] transition-colors cursor-pointer focus:outline-none"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Logo: Balanced in the center on mobile, positioned on left on desktop (Enlarged for clear readability) -->
            <a
                href="/"
                class="flex items-center justify-center flex-shrink-0 group py-0 w-[66px] h-[66px] sm:w-[74px] sm:h-[74px] lg:w-[82px] lg:h-[82px]"
                aria-label="Menahem Home"
            >
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Menahem - The one that comforts you"
                    width="82"
                    height="82"
                    class="site-logo object-contain transition-transform duration-300 group-hover:scale-105"
                    style="max-width: 86px; max-height: 86px; object-fit: contain;"
                />
            </a>

            <!-- Center: Desktop Menu Links (Preserved exactly as existing on desktop, hidden on mobile) -->
            <div class="hidden lg:flex items-center gap-7">
                <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-white pb-1 border-b-2 border-[#e68bbe]' : 'text-white/80 hover:text-white' }} transition-colors">
                    Home
                </a>

                <!-- Shop with Dropdown -->
                <div class="relative group py-2">
                    <a href="{{ route('products.index') }}" class="text-sm font-medium {{ request()->routeIs('products.index') ? 'text-white font-bold' : 'text-white/80' }} hover:text-white transition-colors flex items-center gap-1">
                        <span>Shop</span>
                        <svg class="w-3.5 h-3.5 text-white/70 group-hover:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                    <!-- Dropdown -->
                    <div class="absolute top-full left-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-[#23121F]/10 p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="{{ route('products.index') }}" class="flex items-center justify-between px-3 py-2 text-xs font-bold text-[#22121E] hover:bg-[#F8EAF3] hover:text-[#e68bbe] rounded-lg transition-colors border-b border-[#23121F]/10 mb-1">
                            <span>All Collections</span>
                            <span class="text-[10px] text-[#e68bbe]">View All →</span>
                        </a>
                        @foreach($nav['shop_categories'] ?? [] as $cat)
                            <a href="{{ route('products.index', ['category' => $cat['slug']]) }}" class="flex items-center justify-between px-3 py-2 text-xs font-medium text-[#22121E] hover:bg-[#F8EAF3] hover:text-[#e68bbe] rounded-lg transition-colors">
                                <span>{{ $cat['name'] }}</span>
                                <span class="text-[10px] text-[#6E5666]">{{ $cat['badge'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Collections with Direct Link -->
                <a href="{{ route('products.index') }}" class="text-sm font-medium {{ request()->routeIs('products.index') ? 'text-white font-bold pb-1 border-b-2 border-[#e68bbe]' : 'text-white/80 hover:text-white' }} transition-colors">
                    Collections
                </a>

                <a href="{{ route('products.index') }}" class="text-sm font-medium text-white/80 hover:text-white transition-colors">
                    Best Sellers
                </a>

                <a href="{{ route('products.index') }}" class="text-sm font-medium text-white/80 hover:text-white transition-colors">
                    New Arrivals
                </a>

                <a href="{{ route('home') }}#contact" class="text-sm font-medium text-white/80 hover:text-white transition-colors">
                    Contact Us
                </a>
            </div>

            <!-- Right: Search, Account, Cart Icons (w-20 on mobile to balance hamburger, auto on desktop) -->
            <div class="w-20 sm:w-24 lg:w-auto flex items-center justify-end gap-2 sm:gap-4 flex-shrink-0">
                <!-- Search Icon -->
                <a href="{{ route('products.index') }}" aria-label="Search Catalog" class="p-1.5 text-white/90 hover:text-[#f4b8da] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </a>

                <!-- User Account Icon -->
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp Concierge" class="p-1.5 text-white/90 hover:text-[#f4b8da] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>

                <!-- Cart Button: Click to Open Slide-Over Cart Drawer -->
                <button
                    type="button"
                    @click="$dispatch('open-cart')"
                    aria-label="View Shopping Cart"
                    title="View Shopping Cart"
                    class="relative p-1.5 text-white hover:text-[#f4b8da] transition-colors cursor-pointer"
                >
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span
                        class="absolute -top-0.5 -right-0.5 w-4 h-4 rounded-full bg-[#e68bbe] text-white text-[9px] font-bold flex items-center justify-center leading-none shadow-sm"
                    >
                        2
                    </span>
                </button>
            </div>

        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu-drawer" class="hidden lg:hidden mt-3 pt-3 border-t border-white/10 pb-2">
            <div class="flex flex-col gap-2.5">
                <a href="{{ route('home') }}" class="px-2 py-1.5 text-sm font-semibold text-white">Home</a>
                <a href="{{ route('products.index') }}" class="px-2 py-1.5 text-sm font-medium text-white/85">Shop Catalog</a>
                <a href="{{ route('products.index', ['category' => 'curtains']) }}" class="px-2 py-1.5 text-sm font-medium text-white/85">Curtains</a>
                <a href="{{ route('products.index', ['category' => 'towels']) }}" class="px-2 py-1.5 text-sm font-medium text-white/85">Towels</a>
                <a href="{{ route('products.index', ['category' => 'table-decor']) }}" class="px-2 py-1.5 text-sm font-medium text-white/85">Table Linen</a>
                <a href="{{ route('products.index', ['category' => 'rugs']) }}" class="px-2 py-1.5 text-sm font-medium text-white/85">Rugs</a>
                <a href="{{ route('home') }}#contact" class="px-2 py-1.5 text-sm font-medium text-white/85">Contact Us</a>
                <button
                    type="button"
                    @click="$dispatch('open-cart'); document.getElementById('mobile-menu-drawer').classList.add('hidden');"
                    class="mt-2 w-full py-2.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold text-center flex items-center justify-center gap-2 cursor-pointer"
                >
                    <span>View Shopping Bag</span>
                    <span class="px-2 py-0.5 rounded-full bg-[#e68bbe] text-white text-[10px] font-bold">2</span>
                </button>
                <a href="{{ $whatsappUrl }}" class="w-full py-2.5 rounded-lg bg-[#e68bbe] text-white text-xs font-semibold text-center uppercase tracking-wider">
                    WhatsApp Order Assistance
                </a>
            </div>
        </div>
    </nav>
</header>
