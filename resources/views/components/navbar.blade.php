@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $nav = $content['NAV'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<header class="w-full fixed top-0 left-0 z-50 shadow-md">
    <!-- Top Announcement Bar (Deep Olive Green Tone with Rose Accents) -->
    <div class="bg-[#2f3014] text-white/95 text-xs py-1.5 sm:py-2 px-3 sm:px-8 border-b border-black/20">
        
        <!-- Mobile Announcement Bar: Compact 2 lines matching reference screenshot -->
        <div class="sm:hidden flex flex-col items-center justify-center text-center gap-0.5 py-0.5">
            <!-- Line 1: Free Shipping with shopping bag icon -->
            <div class="flex items-center justify-center gap-1.5 text-[11px] font-medium text-white/95">
                <svg class="w-3.5 h-3.5 text-[#f4b8da] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>Free Shipping on Orders Over ₹999</span>
            </div>

            <!-- Line 2: 30-Day Guarantee | Excellent 4.9 rating -->
            <div class="flex items-center justify-center gap-2 text-[10px] text-white/85">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-[#f4b8da] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>30-Day Money Back Guarantee</span>
                </span>
                <span class="text-white/30">|</span>
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-[#f4b8da] fill-current" viewBox="0 0 20 20">
                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                    </svg>
                    <span>Excellent 4.9 out of 5</span>
                </span>
            </div>
        </div>

        <!-- Desktop Announcement Bar (Preserved exactly as existing) -->
        <div class="hidden sm:flex max-w-7xl mx-auto items-center justify-between sm:justify-center lg:justify-between gap-2 font-normal">
            
            <!-- Left: Free Shipping -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#f4b8da] flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1-4l-3 4h4z"/>
                </svg>
                <span class="text-[10px] sm:text-xs font-medium whitespace-nowrap">Free Shipping on Orders Over ₹999</span>
            </div>

            <!-- Divider -->
            <span class="text-white/30 text-[10px] sm:text-xs mx-1">|</span>

            <!-- Center: 30-Day Money Back Guarantee -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#f4b8da] flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="text-[10px] sm:text-xs font-medium whitespace-nowrap">30-Day Money Back Guarantee</span>
            </div>

            <!-- Right: Rating -->
            <div class="hidden lg:flex items-center gap-1.5">
                <div class="flex text-[#f4b8da]">
                    <svg class="w-3.5 h-3.5 fill-current" width="14" height="14" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                </div>
                <span class="text-xs">Excellent 4.9 out of 5</span>
            </div>

        </div>
    </div>

    <!-- Main Navigation Bar (Olive Green #3c3d1b with Enriched Gold Medallion Logo) -->
    <nav id="main-navbar" class="w-full bg-[#3c3d1b] transition-all duration-300 py-1.5 sm:py-2 px-3 sm:px-8 border-b border-[#2e2f14]">
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

            <!-- Logo: Balanced in the center on mobile, positioned on left on desktop -->
            <a
                href="/"
                class="flex items-center justify-center flex-shrink-0 group py-0"
                style="width: 52px; height: 52px; max-width: 56px; max-height: 56px;"
                aria-label="Menahem Home"
            >
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Menahem"
                    width="52"
                    height="52"
                    class="site-logo object-contain transition-transform duration-300 group-hover:scale-105"
                    style="width: 50px; height: 50px; max-width: 56px; max-height: 56px; object-fit: contain;"
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
