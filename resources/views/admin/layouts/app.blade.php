<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') | Menahem Admin</title>
    
    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Force Roboto font across all admin panel elements */
        * {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        }

        /* Strict Zero Roundness across the entire admin panel */
        *, .rounded, .rounded-sm, .rounded-md, .rounded-lg, .rounded-xl, .rounded-2xl, .rounded-3xl, .rounded-full {
            border-radius: 0px !important;
        }
        input, select, textarea, button, a, div, span, table, th, td, tr, .card, img, form, label {
            border-radius: 0px !important;
        }

        /* Clean White Theme Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #ffffff;
        }
        ::-webkit-scrollbar-thumb {
            background: #d4d4d4;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a3a3a3;
        }
    </style>
</head>
<body class="h-full antialiased text-neutral-900 bg-white" x-data="{ mobileMenu: false }">
    <div class="min-h-full flex flex-col md:flex-row bg-white">
        <!-- Mobile Drawer Backdrop -->
        <div x-show="mobileMenu" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileMenu = false" 
             @keydown.escape.window="mobileMenu = false"
             class="fixed inset-0 bg-neutral-900/40 backdrop-blur-xs z-40 md:hidden"
             style="display: none;"></div>

        <!-- Sidebar Navigation (Fully White Theme, Crisp Borders, No Roundness) -->
        <aside :class="mobileMenu ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-white text-neutral-800 border-r border-neutral-200 flex flex-col transition-transform duration-200 ease-in-out md:sticky md:top-0 md:h-screen md:shrink-0 md:translate-x-0 md:overflow-y-auto shadow-none">
            
            <!-- Brand Header -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-neutral-200 bg-white shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="Menahem" class="w-8 h-8 object-contain">
                    <div>
                        <span class="tracking-widest text-base font-extrabold text-neutral-900 block leading-tight">MENAHEM</span>
                        <span class="text-[9px] tracking-wider uppercase font-bold text-neutral-500">Admin Control</span>
                    </div>
                </a>
                <button @click="mobileMenu = false" class="md:hidden text-neutral-500 hover:text-neutral-900 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Current User Badge Bar -->
            <div class="px-5 py-3 bg-neutral-50 border-b border-neutral-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-neutral-900 text-white font-bold flex items-center justify-center text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-neutral-900 truncate">{{ auth()->user()->name }}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="inline-block w-1.5 h-1.5 bg-emerald-600"></span>
                            <span class="text-[10px] uppercase tracking-wider font-semibold text-neutral-500">
                                {{ auth()->user()->role?->value ?? 'staff' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto bg-white">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.dashboard') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Overview</span>
                </a>

                <a href="{{ route('admin.orders.index') }}" 
                   class="flex items-center justify-between px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.orders.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        <span>Order Management</span>
                    </div>
                    @php $pendingOrders = \App\Models\Order::whereIn('order_status', ['placed', 'processing', 'confirmed'])->count(); @endphp
                    <span id="admin-sidebar-orders-badge" class="px-1.5 py-0.2 text-[10px] font-bold bg-neutral-900 text-white {{ $pendingOrders > 0 ? '' : 'hidden' }}">{{ $pendingOrders }}</span>
                </a>

                @if(auth()->user()->canEditContent())
                <a href="{{ route('admin.billing.create') }}"
                   class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.billing.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Manual Billing</span>
                </a>
                @endif

                <a href="{{ route('admin.products.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.products.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Products Catalog</span>
                </a>

                <a href="{{ route('admin.stock.index') }}" 
                   class="flex items-center justify-between px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.stock.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Stock Management</span>
                    </div>
                    @php 
                        $lowStockAlert = \App\Models\Product::where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
                        $outStockAlert = \App\Models\Product::where('stock_quantity', '<=', 0)->count();
                    @endphp
                    @if($outStockAlert > 0)
                        <span class="px-1.5 py-0.2 text-[10px] font-bold bg-rose-600 text-white">{{ $outStockAlert }}</span>
                    @elseif($lowStockAlert > 0)
                        <span class="px-1.5 py-0.2 text-[10px] font-bold bg-amber-500 text-neutral-900">{{ $lowStockAlert }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.categories.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.categories.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Categories & Drapes</span>
                </a>

                <a href="{{ route('admin.coupons.index') }}" 
                   class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.coupons.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span>Offer & Coupons</span>
                </a>

                <a href="{{ route('admin.leads.index') }}" 
                   class="flex items-center justify-between px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.leads.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Customer Leads</span>
                    </div>
                </a>

                <a href="{{ route('admin.inquiries.index') }}" 
                   class="flex items-center justify-between px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.inquiries.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Custom Inquiries</span>
                    </div>
                    @php $newCount = \App\Models\Inquiry::whereIn('status', ['new', 'pending'])->count(); @endphp
                    <span id="admin-sidebar-inquiries-badge" class="px-1.5 py-0.2 text-[10px] font-bold bg-amber-500 text-neutral-900 {{ $newCount > 0 ? '' : 'hidden' }}">{{ $newCount }}</span>
                </a>

                @if(auth()->user()->isAdmin())
                <div class="pt-3 mt-3 border-t border-neutral-200">
                    <p class="px-3 text-[10px] font-bold tracking-wider text-neutral-400 uppercase mb-1">Configuration</p>
                    <a href="{{ route('admin.settings.payment') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.settings.payment') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Payment Settings</span>
                    </a>

                    <a href="{{ route('admin.settings.shipping') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.settings.shipping*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Shipping Configuration</span>
                    </a>

                    <a href="{{ route('admin.users.index') }}" 
                       class="flex items-center gap-3 px-3 py-2 text-xs transition-colors border-l-2 {{ request()->routeIs('admin.users.*') ? 'bg-neutral-100 text-neutral-900 font-bold border-neutral-900' : 'text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 border-transparent font-medium' }}">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Team & Roles</span>
                    </a>
                </div>
                @endif
            </nav>

            <!-- Bottom Actions -->
            <div class="p-3 border-t border-neutral-200 space-y-1.5 bg-neutral-50">
                <a href="{{ route('home') }}" target="_blank" 
                   class="flex items-center justify-center gap-2 w-full px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-neutral-700 hover:text-neutral-900 bg-white hover:bg-neutral-100 border border-neutral-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>View Storefront</span>
                </a>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="flex items-center justify-center gap-2 w-full px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-rose-700 hover:bg-rose-50 border border-rose-200 bg-white transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#F9FAFB]">
            <!-- Top Mobile & Title Header -->
            <header class="h-16 bg-white border-b border-neutral-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-none">
                <div class="flex items-center gap-4">
                    <button @click="mobileMenu = true" class="md:hidden text-neutral-700 hover:text-neutral-900 p-2 hover:bg-neutral-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-bold text-neutral-900 tracking-tight uppercase">
                        @yield('header_title', 'Dashboard')
                    </h1>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @include('admin.partials.notifications-header')
                    @yield('header_actions')
                </div>
            </header>

            <!-- Alerts & Flash Messages -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                @if(session('success'))
                    <div class="mb-6 p-3.5 bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-start gap-3 shadow-none">
                        <svg class="w-5 h-5 text-emerald-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <div class="flex-1 text-xs font-semibold">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-3.5 bg-rose-50 border border-rose-300 text-rose-900 flex items-start gap-3 shadow-none">
                        <svg class="w-5 h-5 text-rose-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="flex-1 text-xs font-semibold">{{ session('error') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-3.5 bg-rose-50 border border-rose-300 text-rose-900 shadow-none">
                        <p class="text-xs font-bold mb-1">Please correct the following errors:</p>
                        <ul class="list-disc list-inside text-xs space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @include('admin.partials.notifications-runtime')
</body>
</html>
