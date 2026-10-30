@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $nav = $content['NAV'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<header class="w-full fixed top-0 left-0 z-50 shadow-xs">
    <!-- Top Announcement Bar (Dark Khaki #353819 with Camel #AC9661 Accents) -->
    <div class="bg-[#353819] text-white/95 text-xs py-2 px-4 sm:px-8 border-b border-black/10">
        <div class="max-w-7xl mx-auto flex items-center justify-between sm:justify-center lg:justify-between gap-2 font-normal">
            
            <!-- Left: Free Shipping -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#AC9661] flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1-4l-3 4h4z"/>
                </svg>
                <span class="text-[10px] sm:text-xs font-medium whitespace-nowrap">Free Shipping on Orders Over ₹999</span>
            </div>

            <!-- Divider -->
            <span class="text-white/30 text-[10px] sm:text-xs mx-1">|</span>

            <!-- Center: 30-Day Money Back Guarantee -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-[#AC9661] flex-shrink-0" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="text-[10px] sm:text-xs font-medium whitespace-nowrap">30-Day Money Back Guarantee</span>
            </div>

            <!-- Right: Rating -->
            <div class="hidden lg:flex items-center gap-1.5">
                <div class="flex text-[#AC9661]">
                    <svg class="w-3.5 h-3.5 fill-current" width="14" height="14" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                </div>
                <span class="text-xs">Excellent 4.9 out of 5</span>
            </div>

        </div>
    </div>

    <!-- Main Navigation Bar (Clean Ivory Background with Dark Khaki & Camel Branding) -->
    <nav id="main-navbar" class="w-full bg-[#faf9f5]/95 backdrop-blur-md transition-all duration-300 py-3.5 px-4 sm:px-8 border-b border-[#353819]/10">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            
            <!-- Left: Logo & Subtext -->
            <a href="/" class="flex items-center gap-2.5 sm:gap-3 group">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Menahem"
                    class="w-9 h-9 sm:w-11 sm:h-11 object-contain transition-transform duration-300 group-hover:scale-105 drop-shadow-xs"
                />
                <div class="flex flex-col">
                    <span class="text-[18px] sm:text-[22px] font-bold tracking-tight text-[#212411] leading-none group-hover:text-[#AC9661] transition-colors">
                        Menahem
                    </span>
                    <span class="text-[7px] sm:text-[8px] uppercase tracking-wider text-[#595b45] font-semibold mt-0.5 whitespace-nowrap">
                        The one that comforts you
                    </span>
                </div>
            </a>

            <!-- Center: Menu Links -->
            <div class="hidden lg:flex items-center gap-7">
                <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-[#212411] pb-1 border-b-2 border-[#AC9661]' : 'text-[#595b45] hover:text-[#212411]' }} transition-colors">
                    Home
                </a>

                <!-- Shop with Dropdown -->
                <div class="relative group py-2">
                    <a href="{{ route('products.index') }}" class="text-sm font-medium {{ request()->routeIs('products.index') ? 'text-[#212411] font-bold' : 'text-[#595b45]' }} hover:text-[#212411] transition-colors flex items-center gap-1">
                        <span>Shop</span>
                        <svg class="w-3.5 h-3.5 text-[#595b45] group-hover:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                    <!-- Dropdown -->
                    <div class="absolute top-full left-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-[#353819]/10 p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <a href="{{ route('products.index') }}" class="flex items-center justify-between px-3 py-2 text-xs font-bold text-[#212411] hover:bg-[#f3efe6] hover:text-[#AC9661] rounded-lg transition-colors border-b border-[#353819]/10 mb-1">
                            <span>All Collections</span>
                            <span class="text-[10px] text-[#AC9661]">View All →</span>
                        </a>
                        @foreach($nav['shop_categories'] ?? [] as $cat)
                            <a href="{{ route('products.index', ['category' => $cat['slug']]) }}" class="flex items-center justify-between px-3 py-2 text-xs font-medium text-[#212411] hover:bg-[#f3efe6] hover:text-[#AC9661] rounded-lg transition-colors">
                                <span>{{ $cat['name'] }}</span>
                                <span class="text-[10px] text-[#595b45]">{{ $cat['badge'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Collections with Direct Link -->
                <a href="{{ route('products.index') }}" class="text-sm font-medium {{ request()->routeIs('products.index') ? 'text-[#212411] font-bold pb-1 border-b-2 border-[#AC9661]' : 'text-[#595b45] hover:text-[#212411]' }} transition-colors">
                    Collections
                </a>

                <a href="{{ route('products.index') }}" class="text-sm font-medium text-[#595b45] hover:text-[#212411] transition-colors">
                    Best Sellers
                </a>

                <a href="{{ route('products.index') }}" class="text-sm font-medium text-[#595b45] hover:text-[#212411] transition-colors">
                    New Arrivals
                </a>

                <a href="{{ route('home') }}#why-us" class="text-sm font-medium text-[#595b45] hover:text-[#212411] transition-colors">
                    About
                </a>
            </div>

            <!-- Right: Search, Account, Cart Icons (Camel Accents) -->
            <div class="flex items-center gap-4">
                <!-- Search Icon -->
                <a href="{{ route('products.index') }}" aria-label="Search" class="p-1.5 text-[#212411] hover:text-[#AC9661] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </a>

                <!-- User Account Icon -->
                <a href="{{ $whatsappUrl }}" aria-label="Account" class="p-1.5 text-[#212411] hover:text-[#AC9661] transition-colors hidden sm:block">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>

                <!-- Cart / Order Icon with Badge '2' in Camel #AC9661 -->
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="relative p-1.5 text-[#212411] hover:text-[#AC9661] transition-colors" aria-label="Shopping Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#AC9661] text-white text-[10px] font-bold flex items-center justify-center leading-none">
                        2
                    </span>
                </a>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" type="button" aria-label="Toggle navigation" class="lg:hidden p-1.5 text-[#212411]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                    </svg>
                </button>
            </div>

        </div>

        <!-- Mobile Drawer -->
        <div id="mobile-menu-drawer" class="hidden lg:hidden mt-3 pt-3 border-t border-[#353819]/10 pb-2">
            <div class="flex flex-col gap-2.5">
                <a href="#hero" class="px-2 py-1.5 text-sm font-semibold text-[#212411]">Home</a>
                <a href="#shop-categories" class="px-2 py-1.5 text-sm font-medium text-[#595b45]">Shop Categories</a>
                <a href="#trending" class="px-2 py-1.5 text-sm font-medium text-[#595b45]">Best Sellers</a>
                <a href="#promo-banner" class="px-2 py-1.5 text-sm font-medium text-[#595b45]">New Arrivals</a>
                <a href="#why-us" class="px-2 py-1.5 text-sm font-medium text-[#595b45]">Why Menahem</a>
                <a href="{{ $whatsappUrl }}" class="mt-2 w-full py-2.5 rounded-lg bg-[#AC9661] text-white text-xs font-semibold text-center uppercase tracking-wider">
                    WhatsApp Order Assistance
                </a>
            </div>
        </div>
    </nav>
</header>
