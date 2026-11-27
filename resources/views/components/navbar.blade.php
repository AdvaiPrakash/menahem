@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $nav = $content['NAV'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<header class="w-full fixed top-0 left-0 z-50 shadow-md"
        x-data="{
            cartCount: 0,
            wishlistCount: 0,
            searchOpen: false,
            searchQuery: '',
            userMenuOpen: false,
            mobileMenuOpen: false,
            init() {
                this.syncCartCount();
                this.syncWishlistCount();

                window.addEventListener('cart-updated', (e) => {
                    this.cartCount = e.detail && typeof e.detail.count !== 'undefined' ? e.detail.count : this.cartCount;
                });
                window.addEventListener('wishlist-updated', (e) => {
                    this.wishlistCount = e.detail && typeof e.detail.count !== 'undefined' ? e.detail.count : this.wishlistCount;
                });
                window.addEventListener('storage', (e) => {
                    if (e.key === 'menahem_cart') {
                        this.syncCartCount();
                    }
                });
            },
            syncCartCount() {
                try {
                    const raw = localStorage.getItem('menahem_cart');
                    if (raw) {
                        const items = JSON.parse(raw);
                        if (Array.isArray(items)) {
                            this.cartCount = items.reduce((tot, i) => tot + (parseInt(i.quantity) || 1), 0);
                            return;
                        }
                    }
                } catch(e) {}
                this.cartCount = 0;
            },
            syncWishlistCount() {
                fetch('{{ route('wishlist.status') }}')
                    .then(r => r.json())
                    .then(data => {
                        if (data && typeof data.count !== 'undefined') {
                            this.wishlistCount = data.count;
                        }
                    })
                    .catch(() => {});
            }
        }">

    <!-- Main Navigation Bar (Olive Green Dark Shade #384230) -->
    <nav id="main-navbar" class="w-full bg-[#384230] transition-all duration-300 py-2 sm:py-2.5 lg:py-2 px-3 sm:px-8 border-b border-[#2C3424]">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            
            <!-- Mobile Left: Hamburger Button (Hidden on Desktop) -->
            <div class="flex items-center justify-start lg:hidden flex-shrink-0">
                <button
                    id="mobile-menu-btn"
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    aria-label="Toggle navigation"
                    class="p-1.5 -ml-1 text-white hover:text-[#B5C1A5] transition-colors cursor-pointer focus:outline-none"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Logo: Centered on Mobile, Left-aligned on Desktop -->
            <a
                href="/"
                class="flex items-center justify-center flex-shrink-0 group py-0 w-[56px] h-[56px] sm:w-[72px] sm:h-[72px] lg:w-[76px] lg:h-[76px]"
                aria-label="Menahem Home"
            >
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="Menahem - The one that comforts you"
                    width="76"
                    height="76"
                    class="site-logo object-contain transition-transform duration-300 group-hover:scale-105 max-h-full max-w-full"
                />
            </a>

            <!-- Center: Desktop Menu Links -->
            <div class="hidden lg:flex items-center gap-7">
                <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-white pb-1 border-b-2 border-[#B5C1A5]' : 'text-white/85 hover:text-white' }} transition-colors">
                    Home
                </a>

                <!-- Shop with Dropdown -->
                <div class="relative group py-2">
                    <a href="{{ route('products.index') }}" class="text-sm font-medium {{ request()->routeIs('products.index') ? 'text-white font-bold' : 'text-white/85' }} hover:text-white transition-colors flex items-center gap-1">
                        <span>Shop</span>
                        <svg class="w-3.5 h-3.5 text-white/70 group-hover:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </a>
                    <!-- Dropdown -->
                    <div class="absolute top-full left-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-neutral-200 p-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="{{ route('categories.index') }}" class="flex items-center justify-between px-3 py-2 text-xs font-bold text-[#2B3326] hover:bg-neutral-100 hover:text-[#74826B] rounded-md transition-colors border-b border-neutral-200 mb-1">
                            <span>Shop by Category</span>
                            <span class="text-[10px] text-[#74826B] font-semibold">Explore All →</span>
                        </a>
                        @foreach($nav['shop_categories'] ?? [] as $cat)
                            <a href="{{ url('/categories/' . $cat['slug']) }}" class="flex items-center justify-between px-3 py-2 text-xs font-medium text-[#2B3326] hover:bg-neutral-100 hover:text-[#74826B] rounded-md transition-colors">
                                <span>{{ $cat['name'] }}</span>
                                <span class="text-[10px] text-[#5C6654]">{{ $cat['badge'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Collections with Direct Link -->
                <a href="{{ route('products.index') }}" class="text-sm font-medium {{ request()->routeIs('products.index') ? 'text-white font-bold pb-1 border-b-2 border-[#B5C1A5]' : 'text-white/85 hover:text-white' }} transition-colors">
                    Collections
                </a>

                <a href="{{ route('products.index') }}" class="text-sm font-medium text-white/85 hover:text-white transition-colors">
                    Best Sellers
                </a>

                <a href="{{ route('products.index') }}" class="text-sm font-medium text-white/85 hover:text-white transition-colors">
                    New Arrivals
                </a>

                <a href="{{ route('home') }}#contact" class="text-sm font-medium text-white/85 hover:text-white transition-colors">
                    Contact Us
                </a>
            </div>

            <!-- Right: Interactive Search, Avatar / Account, Cart Icons -->
            <div class="flex items-center justify-end gap-0.5 sm:gap-2.5 lg:gap-3 flex-shrink-0">
                
                <!-- 1. Interactive Search Toggle Button -->
                <button 
                    type="button" 
                    @click="searchOpen = !searchOpen; if(searchOpen) $nextTick(() => $refs.navbarSearchInput.focus())" 
                    aria-label="Search Catalog" 
                    title="Search Products"
                    :class="searchOpen ? 'text-[#B5C1A5] bg-white/10' : 'text-white hover:text-[#B5C1A5]'"
                    class="p-2 rounded-full transition-all cursor-pointer focus:outline-none"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <!-- 2. Avatar / User Account Dropdown -->
                <div class="relative" @click.outside="userMenuOpen = false">
                    @auth
                        <!-- Logged-in Avatar Button -->
                        <button 
                            type="button" 
                            @click="userMenuOpen = !userMenuOpen" 
                            aria-label="User Account Menu" 
                            title="My Account"
                            class="flex items-center gap-1.5 p-1 rounded-full text-white hover:text-[#B5C1A5] transition-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#B5C1A5]/50"
                        >
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#74826B] text-white flex items-center justify-center text-xs font-bold border-2 border-white/30 shadow-xs">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="hidden xl:inline text-xs font-semibold text-white/90 max-w-[90px] truncate">
                                {{ explode(' ', Auth::user()->name)[0] }}
                            </span>
                            <svg class="hidden sm:block w-3 h-3 text-white/70 transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Logged-in Dropdown Panel -->
                        <div 
                            x-show="userMenuOpen" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            x-cloak
                            class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-neutral-200/80 p-2.5 z-50 text-[#2B3326]"
                        >
                            <!-- User Profile Info -->
                            <div class="p-3 bg-neutral-50 rounded-xl border border-neutral-100 mb-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-9 h-9 rounded-full bg-[#384230] text-white flex items-center justify-center text-sm font-bold shrink-0 shadow-xs">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-[#2B3326] truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-[11px] text-[#5C6654] truncate">{{ Auth::user()->email }}</p>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    @if(Auth::user()->isCustomer())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-[#74826B]/15 text-[#384230]">
                                            Customer Account
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            {{ ucfirst(Auth::user()->role->value ?? 'Staff') }}
                                        </span>
                                    @endif
                                    <span class="text-[10px] text-[#74826B] font-semibold">Active</span>
                                </div>
                            </div>

                            <!-- Menu Items -->
                            <div class="space-y-0.5 text-xs font-medium">
                                <a href="{{ route('account.orders') }}" 
                                   @click="userMenuOpen = false"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-neutral-100 text-[#2B3326] transition-colors">
                                    <svg class="w-4 h-4 text-[#74826B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span>My Orders & Invoices</span>
                                </a>

                                <a href="{{ route('checkout.index') }}" 
                                   @click="userMenuOpen = false"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-neutral-100 text-[#2B3326] transition-colors">
                                    <svg class="w-4 h-4 text-[#74826B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span>Proceed to Checkout</span>
                                </a>

                                @if(Auth::user()->role === \App\Enums\UserRole::Admin || Auth::user()->role === \App\Enums\UserRole::Editor || Auth::user()->role === \App\Enums\UserRole::Viewer)
                                    <a href="{{ route('admin.dashboard') }}" 
                                       @click="userMenuOpen = false"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg hover:bg-amber-50 text-amber-900 transition-colors font-bold">
                                        <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Admin Control Panel</span>
                                    </a>
                                @endif
                            </div>

                            <!-- Sign Out -->
                            <div class="pt-2 mt-1.5 border-t border-neutral-100">
                                <form method="POST" action="{{ Auth::user()->isCustomer() ? route('customer.logout') : route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-rose-600 hover:bg-rose-50 text-xs font-bold transition-colors cursor-pointer text-left">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <!-- Guest User Button -->
                        <button 
                            type="button" 
                            @click="userMenuOpen = !userMenuOpen" 
                            aria-label="Sign In / Account" 
                            title="Sign In / Account"
                            class="flex items-center gap-1.5 p-1.5 rounded-full text-white hover:text-[#B5C1A5] transition-colors cursor-pointer focus:outline-none"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="hidden xl:inline text-xs font-semibold text-white/90">Sign In</span>
                        </button>

                        <!-- Guest Dropdown Panel -->
                        <div 
                            x-show="userMenuOpen" 
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                            x-cloak
                            class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-neutral-200/80 p-3.5 z-50 text-[#2B3326]"
                        >
                            <div class="pb-2.5 mb-2.5 border-b border-neutral-100">
                                <h4 class="text-xs font-bold text-[#2B3326] uppercase tracking-wider">Welcome to Menahem</h4>
                                <p class="text-[11px] text-[#5C6654] mt-0.5">Sign in to track orders, save addresses, and download invoices.</p>
                            </div>

                            <div class="space-y-2">
                                <button type="button" 
                                        @click="userMenuOpen = false; $dispatch('open-auth-modal', { tab: 'login' })"
                                        class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-[#384230] hover:bg-[#2C3424] text-white text-xs font-bold transition-colors cursor-pointer shadow-xs">
                                    <span>Sign In</span>
                                </button>

                                <button type="button" 
                                        @click="userMenuOpen = false; $dispatch('open-auth-modal', { tab: 'register' })"
                                        class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-neutral-100 hover:bg-neutral-200 text-[#2B3326] text-xs font-bold transition-colors cursor-pointer border border-neutral-200">
                                    <span>Create Account</span>
                                </button>

                                <a href="{{ route('auth.google') }}" 
                                   class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl border border-neutral-200 hover:bg-neutral-50 text-xs font-semibold text-[#2B3326] transition-colors">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24">
                                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                    </svg>
                                    <span>Continue with Google</span>
                                </a>
                            </div>

                            <div class="pt-2.5 mt-2.5 border-t border-neutral-100 text-center">
                                <a href="{{ route('checkout.index') }}" 
                                   @click="userMenuOpen = false"
                                   class="text-[11px] text-[#74826B] font-bold hover:underline">
                                    Instant Guest Checkout &rarr;
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>

                <!-- 3. Wishlist Button -->
                <a
                    href="{{ route('wishlist.index') }}"
                    aria-label="View Wishlist Favorites"
                    title="View Wishlist Favorites"
                    class="relative p-1.5 sm:p-2 text-white hover:text-[#B5C1A5] transition-colors cursor-pointer focus:outline-none"
                >
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span
                        x-show="wishlistCount > 0"
                        x-text="wishlistCount"
                        x-cloak
                        class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 rounded-full bg-[#74826B] text-white text-[9px] font-extrabold flex items-center justify-center leading-none shadow-xs border border-[#384230]"
                    ></span>
                </a>

                <!-- 4. Dynamic Shopping Bag / Cart Button -->
                <button
                    type="button"
                    @click="$dispatch('open-cart')"
                    aria-label="View Shopping Cart"
                    title="View Shopping Cart"
                    class="relative p-1.5 sm:p-2 text-white hover:text-[#B5C1A5] transition-colors cursor-pointer focus:outline-none"
                >
                    <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <!-- Dynamic Live Badge Counter -->
                    <span
                        x-show="cartCount > 0"
                        x-text="cartCount"
                        x-cloak
                        class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 rounded-full bg-[#74826B] text-white text-[9px] font-extrabold flex items-center justify-center leading-none shadow-xs border border-[#384230]"
                    ></span>
                </button>
            </div>

        </div>

        <!-- Slide-down Search Overlay Bar -->
        <div 
            x-show="searchOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            @click.outside="searchOpen = false"
            @keydown.escape.window="searchOpen = false"
            x-cloak
            class="w-full bg-[#2B3326] border-t border-[#384230] mt-2 py-3 px-3 sm:px-6 shadow-2xl"
        >
            <div class="max-w-4xl mx-auto">
                <form action="{{ route('products.index') }}" method="GET" class="flex items-center gap-2 sm:gap-3">
                    <div class="relative flex-1">
                        <input type="text" 
                               name="search" 
                               x-model="searchQuery" 
                               x-ref="navbarSearchInput"
                               placeholder="Search curtains, bedsheets, luxury towels, table decor, cushion covers..." 
                               class="w-full py-2.5 sm:py-3 pl-10 pr-4 rounded-xl bg-white/10 text-white placeholder-white/50 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#B5C1A5] focus:bg-white/15 border border-white/15 transition-all">
                        <svg class="w-4 h-4 text-white/50 absolute left-3.5 top-3 sm:top-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" 
                            class="px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-bold uppercase tracking-wider transition-colors shrink-0 shadow-xs cursor-pointer">
                        Search
                    </button>
                    <button type="button" 
                            @click="searchOpen = false" 
                            class="p-2 sm:p-2.5 text-white/70 hover:text-white transition-colors cursor-pointer"
                            title="Close Search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </form>
                <!-- Quick Category Search Suggestions -->
                <div class="flex items-center gap-2 mt-2.5 pt-2 border-t border-white/10 text-xs text-white/70 overflow-x-auto no-scrollbar">
                    <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-white/40 shrink-0">Popular:</span>
                    <a href="{{ route('products.index', ['search' => 'Curtains']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white/90 text-[11px] transition-colors shrink-0">Curtains</a>
                    <a href="{{ route('products.index', ['search' => 'Bedsheets']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white/90 text-[11px] transition-colors shrink-0">Bedsheets</a>
                    <a href="{{ route('products.index', ['search' => 'Towels']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white/90 text-[11px] transition-colors shrink-0">Bath Towels</a>
                    <a href="{{ route('products.index', ['search' => 'Cushion']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white/90 text-[11px] transition-colors shrink-0">Cushion Covers</a>
                    <a href="{{ route('products.index', ['search' => 'Table']) }}" class="px-2.5 py-0.5 rounded-full bg-white/10 hover:bg-white/20 text-white/90 text-[11px] transition-colors shrink-0">Table Decor</a>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div 
            id="mobile-menu-drawer" 
            x-show="mobileMenuOpen" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            x-cloak
            class="lg:hidden mt-3 pt-3 border-t border-[#2C3424] pb-3"
        >
            <div class="flex flex-col gap-2">
                <!-- Mobile Search Input -->
                <form action="{{ route('products.index') }}" method="GET" class="relative mb-2">
                    <input type="text" 
                           name="search" 
                           placeholder="Search all products..." 
                           class="w-full py-2.5 pl-9 pr-3 rounded-lg bg-white/10 text-white placeholder-white/50 text-xs border border-white/15 focus:outline-none focus:ring-2 focus:ring-[#B5C1A5]">
                    <svg class="w-4 h-4 text-white/50 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </form>

                <!-- Navigation Links -->
                <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-semibold text-white bg-white/10 rounded-md">Home</a>
                <a href="{{ route('categories.index') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Shop by Category</a>
                <a href="{{ url('/categories/curtains-drapes') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Curtains & Drapes</a>
                <a href="{{ url('/categories/table-decor') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Table Decor</a>
                <a href="{{ url('/categories/sofa-decor') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Sofa Decor</a>
                <a href="{{ url('/categories/towels-wellness') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Towels & Wellness</a>
                <a href="{{ url('/categories/bedsheets-dohars') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Bedsheets & Dohars</a>
                <a href="{{ url('/categories/rugs') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Rugs</a>
                <a href="{{ route('products.index') }}" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">All Products</a>
                <a href="{{ route('home') }}#contact" class="px-3 py-2 text-sm font-medium text-white/85 hover:text-white hover:bg-white/10 rounded-md">Contact Us</a>

                <!-- Mobile Account Card -->
                <div class="mt-2 pt-2 border-t border-white/10">
                    @auth
                        <div class="bg-white/10 rounded-xl p-3 text-white space-y-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#74826B] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-[10px] text-white/60 truncate">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <a href="{{ route('account.orders') }}" class="py-2 px-3 text-center bg-white/15 hover:bg-white/20 rounded-lg text-xs font-semibold text-white">
                                    My Orders
                                </a>
                                @if(Auth::user()->role === \App\Enums\UserRole::Admin || Auth::user()->role === \App\Enums\UserRole::Editor)
                                    <a href="{{ route('admin.dashboard') }}" class="py-2 px-3 text-center bg-amber-500/20 text-amber-200 rounded-lg text-xs font-bold">
                                        Admin Panel
                                    </a>
                                @else
                                    <a href="{{ route('checkout.index') }}" class="py-2 px-3 text-center bg-white/15 hover:bg-white/20 rounded-lg text-xs font-semibold text-white">
                                        Checkout
                                    </a>
                                @endif
                            </div>
                            <form method="POST" action="{{ Auth::user()->isCustomer() ? route('customer.logout') : route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full mt-1 py-1.5 text-center text-xs text-rose-300 hover:text-rose-200 font-semibold cursor-pointer">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="bg-white/10 rounded-xl p-3 text-white space-y-2">
                            <p class="text-xs font-bold">Customer Account</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" 
                                        @click="mobileMenuOpen = false; $dispatch('open-auth-modal', { tab: 'login' })" 
                                        class="py-2 px-3 text-center bg-[#74826B] hover:bg-[#636F5A] rounded-lg text-xs font-bold text-white cursor-pointer">
                                    Sign In
                                </button>
                                <button type="button" 
                                        @click="mobileMenuOpen = false; $dispatch('open-auth-modal', { tab: 'register' })" 
                                        class="py-2 px-3 text-center bg-white/20 hover:bg-white/30 rounded-lg text-xs font-bold text-white cursor-pointer">
                                    Register
                                </button>
                            </div>
                            <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-2 py-2 px-3 bg-white text-[#2B3326] rounded-lg text-xs font-semibold">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                <span>Google Sign In</span>
                            </a>
                        </div>
                    @endauth
                </div>

                <!-- Wishlist Mobile Quick Trigger -->
                <a
                    href="{{ route('wishlist.index') }}"
                    @click="mobileMenuOpen = false"
                    class="mt-1 w-full py-2.5 rounded-lg bg-white hover:bg-neutral-100 text-[#2B3326] border border-neutral-200 text-xs font-bold text-center flex items-center justify-center gap-2 cursor-pointer transition-colors shadow-2xs"
                >
                    <svg class="w-4 h-4 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    <span>My Wishlist & Favorites</span>
                    <span x-show="wishlistCount > 0" x-text="wishlistCount" class="px-2 py-0.5 rounded-md bg-[#74826B] text-white text-[10px] font-bold"></span>
                </a>

                <!-- Shopping Bag Quick Trigger -->
                <button
                    type="button"
                    @click="$dispatch('open-cart'); mobileMenuOpen = false;"
                    class="mt-1 w-full py-2.5 rounded-lg bg-white hover:bg-neutral-100 text-[#2B3326] border border-neutral-200 text-xs font-bold text-center flex items-center justify-center gap-2 cursor-pointer transition-colors shadow-2xs"
                >
                    <span>View Shopping Bag</span>
                    <span x-text="cartCount" class="px-2 py-0.5 rounded-md bg-[#74826B] text-white text-[10px] font-bold"></span>
                </button>

                <!-- WhatsApp Assistance -->
                <a href="{{ $whatsappUrl }}" class="w-full py-2.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-bold text-center uppercase tracking-wider transition-colors shadow-xs">
                    WhatsApp Order Assistance
                </a>
            </div>
        </div>
    </nav>
</header>
