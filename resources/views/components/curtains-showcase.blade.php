@props([
    'content' => [],
])

@php
    $curtains = $content['CURTAINS_SHOWCASE'] ?? [];
    $products = $curtains['products'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $viewAllUrl = $curtains['view_all_url'] ?? url('/categories/curtains-drapes');
    $whatsappNumber = $brand['whatsapp_number'] ?? '919876543210';
    // Exactly 4 curated cards for 1 balanced row on desktop
    $curtainItems = array_slice($products, 0, 4);
@endphp

<section
    id="curtains-showcase"
    x-data="{
        activeTab: 'curated', // 'curated' | 'custom'
        customFabric: 'Belgian Linen Sheer',
        customFabricImage: '{{ asset('/images/products/curtains-sheer.webp') }}',
        customFabricPrice: 1149,
        customHeader: 'Antique Brass Eyelets (Grommet)',
        customUnit: 'inches',
        customBreadth: 48,
        customLength: 84,
        customLining: 'Unlined (Natural Sheer)',
        customHem: 'Floor Touch (-0.5 inch floor glide)',
        customRoom: '',
        customQuantity: 1,

        selectFabric(name, price, img) {
            this.customFabric = name;
            this.customFabricPrice = price;
            this.customFabricImage = img;
        },

        setDimensions(b, l) {
            this.customBreadth = b;
            this.customLength = l;
        },

        get lengthInInches() {
            return this.customUnit === 'feet' ? (this.customLength * 12) : this.customLength;
        },

        get breadthInInches() {
            return this.customUnit === 'feet' ? (this.customBreadth * 12) : this.customBreadth;
        },

        get calculatedUnitPrice() {
            let base = this.customFabricPrice;
            if (this.lengthInInches > 96) base += 250;
            if (this.customLining.includes('Blackout')) base += 350;
            if (this.customLining.includes('Cotton')) base += 150;
            return base;
        },

        get calculatedTotal() {
            return this.calculatedUnitPrice * this.customQuantity;
        },

        get customSizeDescription() {
            return this.customBreadth + ' ' + this.customUnit + ' Breadth × ' + this.customLength + ' ' + this.customUnit + ' Length (' + this.customHeader + ')';
        },

        get dynamicWhatsappUrl() {
            const lines = [
                'Hello Menahem! I would like to order CUSTOM TAILORED CURTAINS:',
                '• Fabric: ' + this.customFabric,
                '• Dimensions: Length = ' + this.customLength + ' ' + this.customUnit + ', Breadth = ' + this.customBreadth + ' ' + this.customUnit,
                '• Header Style: ' + this.customHeader,
                '• Lining: ' + this.customLining,
                '• Hem Finish: ' + this.customHem,
                (this.customRoom ? ('• Window / Room: ' + this.customRoom) : ''),
                '• Quantity: ' + this.customQuantity + ' Pair(s)',
                '• Estimated Total: ₹' + this.calculatedTotal.toLocaleString(),
                '',
                'Please review my custom dimensions and advise on stitching timeline & delivery.'
            ].filter(Boolean).join('\n');

            return 'https://wa.me/{{ $whatsappNumber }}?text=' + encodeURIComponent(lines);
        }
    }"
    class="py-16 sm:py-24 bg-white border-b border-neutral-200"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Section Header: Title, Subtitle, Tab Switcher & View All Link -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 sm:mb-12 gap-4">
            <div>
                <x-reveal delay="100">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-neutral-100 text-[#2B3326] border border-neutral-200 text-xs font-bold uppercase tracking-wider mb-2.5 shadow-2xs">
                        <span>★</span>
                        <span>{{ $curtains['badge'] ?? 'Curtains & Drapes' }}</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#2B3326] font-serif">
                        {{ $curtains['heading'] ?? 'Timeless Curtains & Custom Drapes' }}
                    </h2>
                    <p class="text-sm text-[#5C6654] mt-1.5 max-w-xl font-medium">
                        {{ $curtains['subheading'] ?? 'Artisanal Belgian linen & thermal blackout drapes crafted for comforting natural light' }}
                    </p>
                </x-reveal>
            </div>

            <!-- Header Action Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Section View Mode Switcher -->
                <div class="inline-flex rounded-lg bg-neutral-100 p-1 border border-neutral-200">
                    <button
                        type="button"
                        @click="activeTab = 'curated'"
                        :class="activeTab === 'curated' ? 'bg-white text-[#2B3326] shadow-xs font-bold' : 'text-[#5C6654] hover:text-[#2B3326] font-medium'"
                        class="px-3.5 py-1.5 rounded-md text-xs transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Curated Pairs</span>
                        <span class="text-[10px] opacity-70">({{ count($curtainItems) }})</span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'custom'"
                        :class="activeTab === 'custom' ? 'bg-[#74826B] text-white shadow-xs font-bold' : 'text-[#5C6654] hover:text-[#2B3326] font-medium'"
                        class="px-3.5 py-1.5 rounded-md text-xs transition-all cursor-pointer flex items-center gap-1.5"
                    >
                        <span>Custom Tailoring Studio</span>
                        <span class="text-[10px] px-1.5 py-0.2 bg-white/20 rounded-full font-bold">New</span>
                    </button>
                </div>

                <a href="{{ $viewAllUrl }}" class="hidden lg:inline-flex items-center gap-1.5 text-xs font-bold text-[#2B3326] hover:text-[#74826B] transition-colors group ml-2">
                    <span>{{ $curtains['view_all_label'] ?? 'View all curtains' }}</span>
                    <span class="transition-transform group-hover:translate-x-1 font-mono">→</span>
                </a>
            </div>
        </div>

        <!-- ================= MODE 1: CURATED READY-TO-HANG PAIRS ================= -->
        <div x-show="activeTab === 'curated'" x-transition:enter="transition ease-out duration-300">
            <!-- 4 Cards in 1 Row on Desktop -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                @foreach($curtainItems as $index => $product)
                    <x-reveal delay="{{ 100 * ($index % 4 + 1) }}">
                        <x-product-card :product="$product" :active="$loop->first" />
                    </x-reveal>
                @endforeach
            </div>

            <!-- Customization Option Banner below the curated products -->
            <div class="mt-8 p-6 sm:p-7 rounded-2xl bg-[#FAF8F5] border border-[#D7C7B2]/70 flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xs">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#74826B]/15 border border-[#74826B]/25 flex items-center justify-center text-xl text-[#74826B] shrink-0">
                        📏
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-md bg-[#74826B]/10 text-[#2B3326] text-[11px] font-bold uppercase tracking-wider mb-1">
                            Bespoke Customization Option
                        </div>
                        <h3 class="text-base sm:text-lg font-bold font-serif text-[#2B3326]">
                            Have specific window lengths or extra-wide glass doors?
                        </h3>
                        <p class="text-xs sm:text-sm text-[#5C6654] mt-0.5 leading-relaxed max-w-2xl">
                            We tailor your curtains to exact <strong>length and breadth</strong> specifications with antique eyelets, pinch pleats, or rod pockets.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0 w-full md:w-auto">
                    <!-- Small Writing Button for Measurement Manual -->
                    <button
                        type="button"
                        @click="$dispatch('open-curtain-manual')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-lg bg-white hover:bg-neutral-100 text-[#2B3326] border border-neutral-300 text-xs font-semibold transition-colors cursor-pointer group"
                    >
                        <svg class="w-3.5 h-3.5 text-[#74826B] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Measurement Manual</span>
                        <span class="text-[10px] font-mono text-[#74826B]">↗</span>
                    </button>

                    <!-- Switch to Customizer Studio -->
                    <button
                        type="button"
                        @click="activeTab = 'custom'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-bold transition-all shadow-xs cursor-pointer"
                    >
                        <span>Customize Length & Breadth</span>
                        <span class="font-mono">→</span>
                    </button>
                </div>
            </div>

            <!-- Bottom Explore Collection Action -->
            <div class="mt-10 text-center">
                <a href="{{ $viewAllUrl }}" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-lg bg-neutral-900 hover:bg-[#2B3326] text-white text-sm font-bold tracking-wide transition-all shadow-md transform hover:-translate-y-0.5 active:scale-98">
                    <span>{{ $curtains['cta_label'] ?? 'Explore All Curtains & Drapes' }}</span>
                    <span class="text-base leading-none font-mono">→</span>
                </a>
            </div>
        </div>

        <!-- ================= MODE 2: BESPOKE CURTAIN CUSTOMIZATION STUDIO ================= -->
        <div
            x-show="activeTab === 'custom'"
            x-transition:enter="transition ease-out duration-300"
            class="bg-white rounded-2xl border border-neutral-200/90 shadow-sm overflow-hidden"
        >
            <!-- Customizer Sub-Header Strip -->
            <div class="px-6 sm:px-8 py-5 bg-[#FAF8F5] border-b border-neutral-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-md bg-[#74826B]/15 text-[#2B3326] text-xs font-bold uppercase tracking-wider mb-1">
                        <span>✂️</span>
                        <span>Tailor-Made Drapery Studio</span>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold font-serif text-[#2B3326]">
                        Customize Your Curtain Dimensions
                    </h3>
                    <p class="text-xs sm:text-sm text-[#5C6654] mt-0.5">
                        Specify exact length, breadth, hanging style, and lining for a bespoke architectural fit.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Small writing button for displaying measurement manual -->
                    <button
                        type="button"
                        @click="$dispatch('open-curtain-manual')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white hover:bg-neutral-100 text-[#2B3326] hover:text-[#74826B] border border-neutral-300 text-xs font-bold shadow-2xs transition-colors cursor-pointer group"
                    >
                        <svg class="w-4 h-4 text-[#74826B] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>How to measure curtains? (View Manual)</span>
                        <span class="font-mono text-xs text-[#74826B]">↗</span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'curated'"
                        class="px-3.5 py-2 text-xs font-semibold text-[#5C6654] hover:text-[#2B3326] transition-colors cursor-pointer"
                    >
                        ← Back to Ready Pairs
                    </button>
                </div>
            </div>

            <!-- Customizer Two-Column Workspace -->
            <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Column: Customization Controls (Span 7) -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- 1. Select Drapery Fabric -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#2B3326] mb-2.5">
                            1. Select Drapery Fabric
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Sheer Linen -->
                            <button
                                type="button"
                                @click="selectFabric('Belgian Linen Sheer', 1149, '{{ asset('/images/products/curtains-sheer.webp') }}')"
                                :class="customFabric === 'Belgian Linen Sheer' ? 'border-[#74826B] bg-[#74826B]/5 ring-1 ring-[#74826B]' : 'border-neutral-200 hover:border-neutral-300 bg-white'"
                                class="p-3 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between"
                            >
                                <div>
                                    <span class="text-xs font-bold text-[#2B3326] block">Belgian Linen Sheer</span>
                                    <span class="text-[11px] text-[#5C6654] block mt-0.5">Gentle daylight filter</span>
                                </div>
                                <span class="text-xs font-extrabold text-[#74826B] mt-2 block">₹1,149 / pair</span>
                            </button>

                            <!-- Blackout Insulating -->
                            <button
                                type="button"
                                @click="selectFabric('Thermal Blackout Drapes', 1299, '{{ asset('/images/products/curtains-blackout.webp') }}')"
                                :class="customFabric === 'Thermal Blackout Drapes' ? 'border-[#74826B] bg-[#74826B]/5 ring-1 ring-[#74826B]' : 'border-neutral-200 hover:border-neutral-300 bg-white'"
                                class="p-3 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between"
                            >
                                <div>
                                    <span class="text-xs font-bold text-[#2B3326] block">Thermal Blackout</span>
                                    <span class="text-[11px] text-[#5C6654] block mt-0.5">100% Light blocking</span>
                                </div>
                                <span class="text-xs font-extrabold text-[#74826B] mt-2 block">₹1,299 / pair</span>
                            </button>

                            <!-- Pure Flax Boho -->
                            <button
                                type="button"
                                @click="selectFabric('Pure Flax Weave Drapes', 1499, '{{ asset('/images/categories/curtains.webp') }}')"
                                :class="customFabric === 'Pure Flax Weave Drapes' ? 'border-[#74826B] bg-[#74826B]/5 ring-1 ring-[#74826B]' : 'border-neutral-200 hover:border-neutral-300 bg-white'"
                                class="p-3 rounded-xl border text-left transition-all cursor-pointer flex flex-col justify-between"
                            >
                                <div>
                                    <span class="text-xs font-bold text-[#2B3326] block">Belgian Flax Weave</span>
                                    <span class="text-[11px] text-[#5C6654] block mt-0.5">Heavy architectural drape</span>
                                </div>
                                <span class="text-xs font-extrabold text-[#74826B] mt-2 block">₹1,499 / pair</span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Dimensions: Length and Breadth with Unit Toggle and Measurement Manual Link -->
                    <div class="p-5 rounded-xl border border-neutral-200 bg-[#FAF8F5]/80 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-neutral-200 pb-3">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider text-[#2B3326] block">
                                    2. Dimensions (Length & Breadth)
                                </label>
                                <span class="text-[11px] text-[#5C6654]">Enter exact size or pick a standard preset below</span>
                            </div>

                            <div class="flex items-center gap-3">
                                <!-- Small writing button for displaying measurement manual -->
                                <button
                                    type="button"
                                    @click="$dispatch('open-curtain-manual')"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#74826B] hover:text-[#2B3326] underline underline-offset-4 decoration-[#74826B]/60 transition-colors cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>How to measure? (Manual)</span>
                                </button>

                                <!-- Unit Switcher -->
                                <div class="inline-flex rounded-md bg-white border border-neutral-200 p-0.5 text-xs font-bold">
                                    <button
                                        type="button"
                                        @click="customUnit = 'inches'"
                                        :class="customUnit === 'inches' ? 'bg-[#74826B] text-white' : 'text-[#5C6654] hover:text-[#2B3326]'"
                                        class="px-2.5 py-1 rounded transition-colors cursor-pointer"
                                    >
                                        Inches
                                    </button>
                                    <button
                                        type="button"
                                        @click="customUnit = 'feet'"
                                        :class="customUnit === 'feet' ? 'bg-[#74826B] text-white' : 'text-[#5C6654] hover:text-[#2B3326]'"
                                        class="px-2.5 py-1 rounded transition-colors cursor-pointer"
                                    >
                                        Feet
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Length & Breadth Inputs -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <!-- Breadth (Width) Input -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-bold text-[#2B3326]">
                                        Curtain Breadth (Width)
                                    </label>
                                    <span class="text-[10px] text-[#5C6654]" x-text="customUnit === 'inches' ? 'Rod Width + 6\" to 12\"' : 'Rod Width in Feet'"></span>
                                </div>
                                <div class="relative">
                                    <input
                                        type="number"
                                        min="1"
                                        x-model.number="customBreadth"
                                        class="w-full px-3.5 py-2.5 rounded-lg border border-neutral-300 focus:border-[#74826B] focus:ring-1 focus:ring-[#74826B] text-sm font-bold text-[#2B3326] bg-white outline-none"
                                        placeholder="Enter breadth"
                                    />
                                    <span class="absolute right-3 top-2.5 text-xs text-[#5C6654] font-medium" x-text="customUnit"></span>
                                </div>

                                <!-- Quick Breadth Presets -->
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <button
                                        type="button"
                                        @click="customBreadth = (customUnit === 'feet' ? 4 : 48)"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white hover:bg-neutral-100 border border-neutral-200 text-[#5C6654] transition-colors cursor-pointer"
                                    >
                                        Single Panel (48" / 4ft)
                                    </button>
                                    <button
                                        type="button"
                                        @click="customBreadth = (customUnit === 'feet' ? 8 : 96)"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white hover:bg-neutral-100 border border-neutral-200 text-[#5C6654] transition-colors cursor-pointer"
                                    >
                                        Pair Set (96" / 8ft)
                                    </button>
                                </div>
                            </div>

                            <!-- Length (Height / Drop) Input -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="text-xs font-bold text-[#2B3326]">
                                        Curtain Length (Height / Drop)
                                    </label>
                                    <span class="text-[10px] text-[#5C6654]">Top of rod to hemline</span>
                                </div>
                                <div class="relative">
                                    <input
                                        type="number"
                                        min="1"
                                        x-model.number="customLength"
                                        class="w-full px-3.5 py-2.5 rounded-lg border border-neutral-300 focus:border-[#74826B] focus:ring-1 focus:ring-[#74826B] text-sm font-bold text-[#2B3326] bg-white outline-none"
                                        placeholder="Enter length"
                                    />
                                    <span class="absolute right-3 top-2.5 text-xs text-[#5C6654] font-medium" x-text="customUnit"></span>
                                </div>

                                <!-- Quick Length Presets -->
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <button
                                        type="button"
                                        @click="customLength = (customUnit === 'feet' ? 5 : 60)"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white hover:bg-neutral-100 border border-neutral-200 text-[#5C6654] transition-colors cursor-pointer"
                                    >
                                        Window (5ft / 60")
                                    </button>
                                    <button
                                        type="button"
                                        @click="customLength = (customUnit === 'feet' ? 7 : 84)"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white hover:bg-neutral-100 border border-neutral-200 text-[#5C6654] transition-colors cursor-pointer"
                                    >
                                        Door (7ft / 84")
                                    </button>
                                    <button
                                        type="button"
                                        @click="customLength = (customUnit === 'feet' ? 9 : 108)"
                                        class="px-2 py-0.5 rounded text-[10px] font-semibold bg-white hover:bg-neutral-100 border border-neutral-200 text-[#5C6654] transition-colors cursor-pointer"
                                    >
                                        Long Door (9ft / 108")
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 3. Header / Hanging Style (Writings & Options) -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#2B3326] mb-2">
                            3. Curtain Header / Hanging Style
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <button
                                type="button"
                                @click="customHeader = 'Antique Brass Eyelets (Grommet)'"
                                :class="customHeader.includes('Eyelets') ? 'border-[#74826B] bg-[#74826B]/5 font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 text-[#5C6654] hover:border-neutral-300 bg-white'"
                                class="p-2.5 rounded-lg border text-left text-xs transition-all cursor-pointer"
                            >
                                <span class="block font-bold">Antique Eyelets</span>
                                <span class="text-[10px] opacity-75 block mt-0.5">40mm Brass Rings</span>
                            </button>

                            <button
                                type="button"
                                @click="customHeader = 'Pinch Pleats (Tailored Fold)'"
                                :class="customHeader.includes('Pleats') ? 'border-[#74826B] bg-[#74826B]/5 font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 text-[#5C6654] hover:border-neutral-300 bg-white'"
                                class="p-2.5 rounded-lg border text-left text-xs transition-all cursor-pointer"
                            >
                                <span class="block font-bold">Pinch Pleat</span>
                                <span class="text-[10px] opacity-75 block mt-0.5">Classic Hook Fit</span>
                            </button>

                            <button
                                type="button"
                                @click="customHeader = 'Rod Pocket (Clean Minimal)'"
                                :class="customHeader.includes('Pocket') ? 'border-[#74826B] bg-[#74826B]/5 font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 text-[#5C6654] hover:border-neutral-300 bg-white'"
                                class="p-2.5 rounded-lg border text-left text-xs transition-all cursor-pointer"
                            >
                                <span class="block font-bold">Rod Pocket</span>
                                <span class="text-[10px] opacity-75 block mt-0.5">3" Hidden Casing</span>
                            </button>

                            <button
                                type="button"
                                @click="customHeader = 'Back Tab (Suspended Loop)'"
                                :class="customHeader.includes('Tab') ? 'border-[#74826B] bg-[#74826B]/5 font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 text-[#5C6654] hover:border-neutral-300 bg-white'"
                                class="p-2.5 rounded-lg border text-left text-xs transition-all cursor-pointer"
                            >
                                <span class="block font-bold">Back Tab</span>
                                <span class="text-[10px] opacity-75 block mt-0.5">Floating Wave</span>
                            </button>
                        </div>
                    </div>

                    <!-- 4. Lining & Light Control -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#2B3326] mb-2">
                            4. Lining & Privacy Preference
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <button
                                type="button"
                                @click="customLining = 'Unlined (Natural Sheer)'"
                                :class="customLining.includes('Unlined') ? 'border-[#74826B] bg-[#74826B]/5 font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 text-[#5C6654] hover:border-neutral-300 bg-white'"
                                class="p-2.5 rounded-lg border text-left text-xs transition-all cursor-pointer"
                            >
                                <span class="block font-bold">Unlined Drape</span>
                                <span class="text-[10px] opacity-75 block mt-0.5">Natural sheer airflow</span>
                            </button>

                            <button
                                type="button"
                                @click="customLining = 'Cotton Privacy Lining (+₹150)'"
                                :class="customLining.includes('Cotton') ? 'border-[#74826B] bg-[#74826B]/5 font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 text-[#5C6654] hover:border-neutral-300 bg-white'"
                                class="p-2.5 rounded-lg border text-left text-xs transition-all cursor-pointer"
                            >
                                <span class="block font-bold">Cotton Lined</span>
                                <span class="text-[10px] opacity-75 block mt-0.5">Soft body & 60% filter</span>
                            </button>

                            <button
                                type="button"
                                @click="customLining = '100% Thermal Blackout Lining (+₹350)'"
                                :class="customLining.includes('Blackout') ? 'border-[#74826B] bg-[#74826B]/5 font-bold text-[#2B3326] ring-1 ring-[#74826B]' : 'border-neutral-200 text-[#5C6654] hover:border-neutral-300 bg-white'"
                                class="p-2.5 rounded-lg border text-left text-xs transition-all cursor-pointer"
                            >
                                <span class="block font-bold">Blackout Lined</span>
                                <span class="text-[10px] opacity-75 block mt-0.5">100% Light blocking</span>
                            </button>
                        </div>
                    </div>

                    <!-- 5. Hem Finish & Window Note -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Hemline -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#2B3326] mb-1.5">
                                Hemline & Clearance
                            </label>
                            <select
                                x-model="customHem"
                                class="w-full px-3 py-2 rounded-lg border border-neutral-300 bg-white text-xs font-medium text-[#2B3326] outline-none focus:border-[#74826B]"
                            >
                                <option value="Floor Touch (-0.5 inch floor glide)">Floor Touch (-0.5" floor glide)</option>
                                <option value="Sill Length (-0.5 inch above sill)">Sill Length (-0.5" above sill)</option>
                                <option value="Below Sill Apron (+6 inches below frame)">Below Sill (+6" below frame)</option>
                                <option value="Romantic Floor Puddle (+3 inches pooling)">Luxury Puddle (+3" pooling)</option>
                            </select>
                        </div>

                        <!-- Room / Window Note -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#2B3326] mb-1.5">
                                Window / Room Note (Optional)
                            </label>
                            <input
                                type="text"
                                x-model="customRoom"
                                placeholder="e.g. Living room balcony, Guest bedroom"
                                class="w-full px-3 py-2 rounded-lg border border-neutral-300 bg-white text-xs text-[#2B3326] outline-none focus:border-[#74826B]"
                            />
                        </div>
                    </div>

                </div>

                <!-- Right Column: Live Order Summary Card (Span 5) -->
                <div class="lg:col-span-5 flex flex-col justify-between">
                    <div class="bg-[#FAF8F5] rounded-xl p-5 sm:p-6 border border-neutral-200 shadow-2xs space-y-4">
                        
                        <div class="flex items-center justify-between pb-3 border-b border-neutral-200">
                            <div>
                                <span class="text-[10px] uppercase font-bold text-[#74826B] tracking-wider block">Bespoke Estimate</span>
                                <h4 class="text-base font-bold font-serif text-[#2B3326]" x-text="customFabric"></h4>
                            </div>
                            <img :src="customFabricImage" alt="Custom drapery preview" class="w-14 h-14 rounded-lg object-cover border border-neutral-200" />
                        </div>

                        <!-- Specifications List -->
                        <div class="space-y-2 text-xs divide-y divide-neutral-200/60 text-[#5C6654]">
                            <div class="flex items-center justify-between pt-1">
                                <span class="font-medium">Dimensions:</span>
                                <strong class="text-[#2B3326]" x-text="customBreadth + ' ' + customUnit + ' (B) × ' + customLength + ' ' + customUnit + ' (L)'"></strong>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-medium">Header Style:</span>
                                <span class="text-[#2B3326] font-semibold truncate max-w-[180px]" x-text="customHeader"></span>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-medium">Lining:</span>
                                <span class="text-[#2B3326] font-semibold truncate max-w-[180px]" x-text="customLining"></span>
                            </div>
                            <div class="flex items-center justify-between pt-2">
                                <span class="font-medium">Hemline:</span>
                                <span class="text-[#2B3326] font-semibold truncate max-w-[180px]" x-text="customHem"></span>
                            </div>
                            <template x-if="customRoom">
                                <div class="flex items-center justify-between pt-2">
                                    <span class="font-medium">Room Reference:</span>
                                    <span class="text-[#2B3326] font-semibold truncate max-w-[180px]" x-text="customRoom"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Quantity Selector -->
                        <div class="pt-3 border-t border-neutral-200 flex items-center justify-between">
                            <span class="text-xs font-bold text-[#2B3326] uppercase tracking-wider">Number of Pairs</span>
                            <div class="flex items-center rounded-md bg-white border border-neutral-300 p-0.5">
                                <button
                                    type="button"
                                    @click="if(customQuantity > 1) customQuantity--"
                                    class="w-7 h-7 flex items-center justify-center text-xs font-bold text-[#2B3326] hover:bg-neutral-100 rounded cursor-pointer"
                                >
                                    −
                                </button>
                                <span x-text="customQuantity" class="w-8 text-center text-xs font-bold text-[#2B3326]"></span>
                                <button
                                    type="button"
                                    @click="customQuantity++"
                                    class="w-7 h-7 flex items-center justify-center text-xs font-bold text-[#2B3326] hover:bg-neutral-100 rounded cursor-pointer"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <!-- Total Price Display -->
                        <div class="pt-3 border-t border-neutral-200 flex items-baseline justify-between">
                            <span class="text-xs font-bold text-[#5C6654] uppercase tracking-wider">Estimated Total</span>
                            <div class="text-right">
                                <span class="text-2xl font-extrabold text-[#2B3326]" x-text="'₹' + calculatedTotal.toLocaleString()"></span>
                                <span class="text-[10px] text-[#5C6654] block">Incl. tailoring & express delivery</span>
                            </div>
                        </div>

                        <!-- Direct Primary Actions -->
                        <div class="space-y-2.5 pt-2">
                            <!-- WhatsApp Buy CTA with prefilled custom parameters -->
                            <a
                                :href="dynamicWhatsappUrl"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs sm:text-sm font-bold tracking-wide transition-all shadow-md hover:shadow-lg group"
                            >
                                <svg class="w-4.5 h-4.5 fill-current text-white" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                                </svg>
                                <span>Order Custom via WhatsApp</span>
                                <span class="font-mono text-xs group-hover:translate-x-0.5 transition-transform">→</span>
                            </a>

                            <!-- Add to Bag CTA with custom size payload -->
                            <button
                                type="button"
                                @click="$dispatch('add-to-cart', {
                                    id: 'custom-curtain-' + Date.now(),
                                    name: customFabric + ' (Custom Tailored Pair)',
                                    price: calculatedUnitPrice,
                                    quantity: customQuantity,
                                    image: customFabricImage,
                                    category: 'Curtains & Drapes',
                                    custom_size: customSizeDescription
                                })"
                                class="w-full flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-white hover:bg-neutral-100 text-[#2B3326] border border-neutral-300 text-xs font-bold transition-all shadow-2xs cursor-pointer"
                            >
                                <svg class="w-4 h-4 text-[#2B3326]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>Add Custom Curtain to Bag</span>
                            </button>
                        </div>

                        <!-- Trust Guarantee -->
                        <div class="pt-2 text-center">
                            <span class="text-[11px] text-[#5C6654]">
                                🔒 Crafted by generational artisans • Guaranteed precise stitch fit
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</section>
