@props([
    'whatsappNumber' => '919876543210',
])

<!--
  CURTAIN MEASUREMENT MANUAL MODAL
  Interactive modal displaying the measurement manual image, architectural diagram,
  and step-by-step instructions for measuring length and breadth.
  Triggered via: $dispatch('open-curtain-manual') or Alpine state
-->
<div
    x-data="{
        open: false,
        activeTab: 'diagram'
    }"
    @open-curtain-manual.window="open = true"
    @keydown.escape.window="open = false"
    x-cloak
    class="relative z-50"
>
    <!-- Backdrop Overlay -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-[#2B3326]/60 backdrop-blur-sm transition-opacity"
    ></div>

    <!-- Modal Dialog Window -->
    <div
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="fixed inset-0 z-10 overflow-y-auto flex items-center justify-center p-3 sm:p-6"
    >
        <div
            @click.stop
            class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-neutral-200/90 overflow-hidden flex flex-col max-h-[92vh]"
        >
            <!-- Modal Header -->
            <div class="px-5 sm:px-8 py-5 border-b border-neutral-200 bg-white flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-[#74826B]/10 border border-[#74826B]/20 flex items-center justify-center text-[#74826B] font-bold text-lg">
                        📏
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg sm:text-xl font-bold font-serif text-[#2B3326] tracking-tight">
                                Curtain Measurement Manual
                            </h2>
                            <span class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#74826B]/15 text-[#2B3326] uppercase tracking-wider">
                                Tailoring Guide
                            </span>
                        </div>
                        <p class="text-xs sm:text-sm text-[#5C6654] mt-0.5">
                            How to measure length (height) and breadth (width) for perfect custom drapes
                        </p>
                    </div>
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    @click="open = false"
                    aria-label="Close modal"
                    class="w-9 h-9 rounded-full bg-neutral-100 hover:bg-neutral-200 text-[#2B3326] flex items-center justify-center transition-colors cursor-pointer text-lg font-bold"
                >
                    ✕
                </button>
            </div>

            <!-- Tab Switcher (Diagram vs Step-by-Step Instructions) -->
            <div class="px-5 sm:px-8 pt-4 pb-2 border-b border-neutral-100 bg-[#FAF8F5] flex items-center gap-4">
                <button
                    type="button"
                    @click="activeTab = 'diagram'"
                    :class="activeTab === 'diagram' ? 'text-[#2B3326] border-[#74826B] font-bold bg-white shadow-2xs' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                    class="px-4 py-2 rounded-lg border text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Visual Measurement Manual</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'steps'"
                    :class="activeTab === 'steps' ? 'text-[#2B3326] border-[#74826B] font-bold bg-white shadow-2xs' : 'text-[#5C6654] border-transparent font-medium hover:text-[#2B3326]'"
                    class="px-4 py-2 rounded-lg border text-xs sm:text-sm transition-all cursor-pointer flex items-center gap-2"
                >
                    <svg class="w-4 h-4 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Measurement Steps & Standards</span>
                </button>
            </div>

            <!-- Modal Scrollable Content -->
            <div class="p-5 sm:p-8 overflow-y-auto space-y-6">

                <!-- TAB 1: Visual Manual Diagram (The requested manual image) -->
                <div x-show="activeTab === 'diagram'" class="space-y-4">
                    <div class="relative rounded-xl border border-neutral-200 overflow-hidden bg-[#FAF8F5] shadow-xs">
                        <img
                            src="{{ asset('images/curtain-measurement-manual.svg') }}"
                            alt="Menahem Curtain Measurement Manual Diagram"
                            class="w-full h-auto object-contain mx-auto max-h-[520px]"
                        />
                        <div class="absolute bottom-3 right-3">
                            <a
                                href="{{ asset('images/curtain-measurement-manual.svg') }}"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white/95 backdrop-blur-xs text-[#2B3326] hover:text-[#74826B] border border-neutral-300 text-xs font-bold shadow-xs transition-colors"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span>Open Full Size</span>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Highlights Strip -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-2">
                        <div class="p-3.5 rounded-lg bg-neutral-50 border border-neutral-200">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#2B3326] flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#74826B]"></span>
                                Breadth (Width) Rule
                            </h4>
                            <p class="text-xs text-[#5C6654] mt-1 leading-relaxed">
                                Always measure the <strong>curtain rod length</strong> between brackets, not just the glass pane. For rich luxury pleats, order <strong>1.5x to 2x</strong> the rod width.
                            </p>
                        </div>
                        <div class="p-3.5 rounded-lg bg-neutral-50 border border-neutral-200">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#2B3326] flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-[#74826B]"></span>
                                Length (Drop) Rule
                            </h4>
                            <p class="text-xs text-[#5C6654] mt-1 leading-relaxed">
                                Measure from the <strong>top of the rod</strong> (eyelets) or <strong>ring eye</strong> (hooks) to <strong>0.5 inch (1.5 cm) above the floor</strong> so drapes hang straight without dragging.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Detailed Step-by-Step Instructions -->
                <div x-show="activeTab === 'steps'" class="space-y-6">
                    
                    <!-- Step 1: Measuring Breadth (Width) -->
                    <div class="p-4 sm:p-5 rounded-xl border border-neutral-200 bg-white">
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-full bg-[#74826B] text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                1
                            </span>
                            <div class="space-y-2">
                                <h3 class="text-sm sm:text-base font-bold text-[#2B3326] font-serif">
                                    Step 1: How to Measure Curtain Breadth (Width)
                                </h3>
                                <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                    Measure the width of your curtain rod or track from bracket to bracket. <strong>Do not include decorative finials.</strong>
                                </p>
                                <div class="bg-[#FAF8F5] p-3 rounded-lg border border-neutral-200 text-xs text-[#2B3326] space-y-1">
                                    <p><strong>• Standard Fullness:</strong> Rod width × 1.5 (Standard wave fold)</p>
                                    <p><strong>• Luxury Fullness:</strong> Rod width × 2.0 (Deep, opulent hotel folds — highly recommended for sheers and linens)</p>
                                    <p><strong>• If rod is not installed yet:</strong> Measure window frame width and add <strong>6 to 12 inches on each side</strong> so curtains can be pulled completely clear of the window to let daylight in.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Measuring Length (Drop / Height) -->
                    <div class="p-4 sm:p-5 rounded-xl border border-neutral-200 bg-white">
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-full bg-[#74826B] text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                2
                            </span>
                            <div class="space-y-2">
                                <h3 class="text-sm sm:text-base font-bold text-[#2B3326] font-serif">
                                    Step 2: How to Measure Curtain Length (Drop)
                                </h3>
                                <p class="text-xs sm:text-sm text-[#5C6654] leading-relaxed">
                                    Choose where you want your curtains to start and where you want them to finish:
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1 text-xs">
                                    <div class="p-3 rounded-lg border border-neutral-200 bg-[#FAF8F5]">
                                        <div class="font-bold text-[#2B3326]">① Sill Length</div>
                                        <p class="text-[#5C6654] mt-1">Ends <strong>0.5 inch (1.5 cm) above</strong> the window sill. Best for kitchen counters or radiators below windows.</p>
                                    </div>
                                    <div class="p-3 rounded-lg border border-neutral-200 bg-[#FAF8F5]">
                                        <div class="font-bold text-[#2B3326]">② Below Sill (Apron)</div>
                                        <p class="text-[#5C6654] mt-1">Ends <strong>4 to 6 inches below</strong> the window sill frame. Casual and functional for bedrooms.</p>
                                    </div>
                                    <div class="p-3 rounded-lg border border-neutral-200 bg-[#FAF8F5]">
                                        <div class="font-bold text-[#2B3326]">③ Floor Length (Top Choice)</div>
                                        <p class="text-[#5C6654] mt-1">Ends <strong>0.5 inch (1.5 cm) above</strong> the floor so the fabric glides freely and stays clean.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Standard Sizing Quick Chart -->
                    <div class="p-4 sm:p-5 rounded-xl border border-neutral-200 bg-white">
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-full bg-[#74826B] text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                3
                            </span>
                            <div class="space-y-3 w-full">
                                <h3 class="text-sm sm:text-base font-bold text-[#2B3326] font-serif">
                                    Standard Indian Window & Door Dimensions
                                </h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs text-left border-collapse">
                                        <thead>
                                            <tr class="border-b border-neutral-200 bg-neutral-50 text-[#2B3326]">
                                                <th class="py-2 px-3 font-bold">Curtain Type</th>
                                                <th class="py-2 px-3 font-bold">Recommended Length</th>
                                                <th class="py-2 px-3 font-bold">Single Breadth</th>
                                                <th class="py-2 px-3 font-bold">Best Fit For</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-neutral-200 text-[#5C6654]">
                                            <tr>
                                                <td class="py-2 px-3 font-semibold text-[#2B3326]">Window Curtain</td>
                                                <td class="py-2 px-3">5 Feet (60" / 152 cm)</td>
                                                <td class="py-2 px-3">4 Feet (48" / 122 cm)</td>
                                                <td class="py-2 px-3">Standard Bedroom & Kitchen Windows</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 px-3 font-semibold text-[#2B3326]">Door Curtain</td>
                                                <td class="py-2 px-3">7 Feet (84" / 213 cm)</td>
                                                <td class="py-2 px-3">4 Feet (48" / 122 cm)</td>
                                                <td class="py-2 px-3">Balcony & Standard Entry Doors</td>
                                            </tr>
                                            <tr>
                                                <td class="py-2 px-3 font-semibold text-[#2B3326]">Long Door Curtain</td>
                                                <td class="py-2 px-3">9 Feet (108" / 274 cm)</td>
                                                <td class="py-2 px-3">4 Feet (48" / 122 cm)</td>
                                                <td class="py-2 px-3">High-Ceiling Living Rooms & French Doors</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pro Tips Box -->
                    <div class="p-4 rounded-xl bg-[#74826B]/10 border border-[#74826B]/30 flex items-start gap-3">
                        <span class="text-base text-[#74826B]">💡</span>
                        <div class="text-xs text-[#2B3326] space-y-1">
                            <p class="font-bold">Pro Master Tailor Tip:</p>
                            <p class="text-[#5C6654] leading-relaxed">
                                Always measure both the left, center, and right drop heights. Ceilings and floors are rarely 100% level. Take the shortest measurement to ensure your curtains never drag unpleasantly on the floor.
                            </p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer Actions -->
            <div class="px-5 sm:px-8 py-4 border-t border-neutral-200 bg-white flex flex-col sm:flex-row items-center justify-between gap-3 sticky bottom-0 z-20">
                <div class="text-xs text-[#5C6654]">
                    Have an unusual window or corner track? We tailor to millimeter precision.
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a
                        href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode('Hello Menahem! I am looking at custom curtains and would like help taking measurements for my windows.') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-[#74826B] hover:bg-[#636F5A] text-white text-xs font-bold transition-all shadow-xs"
                    >
                        <span>Ask Tailor on WhatsApp</span>
                        <span class="font-mono">→</span>
                    </a>
                    <button
                        type="button"
                        @click="open = false"
                        class="px-4 py-2.5 rounded-lg border border-neutral-200 hover:bg-neutral-100 text-[#2B3326] text-xs font-bold transition-colors cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
