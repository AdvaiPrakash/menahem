@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $whatsappNumber = $brand['whatsapp_number'] ?? '919876543210';
@endphp

<div
    x-data="{
        open: false,
        items: [
            { id: 1, name: 'Cotton Bedsheet & Dohar Set', price: 1499, image: '{{ asset('/images/categories/bedsheets.webp') }}', quantity: 1, category: 'Bedsheets' },
            { id: 2, name: 'Premium Blackout Curtains', price: 1299, image: '{{ asset('/images/categories/curtains.webp') }}', quantity: 1, category: 'Curtains' }
        ],
        addItem(product) {
            const found = this.items.find(i => String(i.id) === String(product.id));
            if (found) {
                found.quantity += (product.quantity || 1);
            } else {
                this.items.push({
                    id: product.id,
                    name: product.name,
                    price: product.price || 999,
                    image: product.image || '{{ asset('/images/categories/bedsheets.webp') }}',
                    quantity: product.quantity || 1,
                    category: product.category || 'Essential'
                });
            }
            this.open = true;
        },
        removeItem(id) {
            this.items = this.items.filter(i => String(i.id) !== String(id));
        },
        updateQuantity(id, delta) {
            const found = this.items.find(i => String(i.id) === String(id));
            if (!found) return;
            found.quantity += delta;
            if (found.quantity <= 0) {
                this.removeItem(id);
            }
        },
        get count() {
            return this.items.reduce((sum, item) => sum + item.quantity, 0);
        },
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        get shipping() {
            return this.subtotal >= 999 ? 0 : 99;
        },
        get total() {
            return this.subtotal + this.shipping;
        },
        get freeShippingDiff() {
            return Math.max(0, 999 - this.subtotal);
        },
        get freeShippingPercent() {
            return Math.min(100, Math.round((this.subtotal / 999) * 100));
        },
        get whatsappCheckoutUrl() {
            if (this.items.length === 0) return 'https://wa.me/{{ $whatsappNumber }}';
            let summary = 'Hello Menahem! I would like to place an order:%0A%0A';
            this.items.forEach((item, index) => {
                summary += (index + 1) + '. ' + encodeURIComponent(item.name) + ' x ' + item.quantity + ' - ₹' + (item.price * item.quantity).toLocaleString() + '%0A';
            });
            summary += '%0ASubtotal: ₹' + this.subtotal.toLocaleString();
            summary += '%0AShipping: ' + (this.shipping === 0 ? 'FREE' : '₹99');
            summary += '%0ATotal Payable: ₹' + this.total.toLocaleString();
            summary += '%0A%0APlease confirm my order and share payment instructions.';
            return 'https://wa.me/{{ $whatsappNumber }}?text=' + summary;
        }
    }"
    @open-cart.window="open = true"
    @add-to-cart.window="addItem($event.detail)"
    x-cloak
    class="relative z-[999]"
>
    <!-- Backdrop Blur Overlay -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity"
    ></div>

    <!-- Sliding Drawer Panel (from right) -->
    <div
        x-show="open"
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 max-w-full flex pl-10"
    >
        <div class="w-screen max-w-md bg-[#FCF8FA] shadow-2xl flex flex-col justify-between border-l border-[#23121F]/10">
            
            <!-- Drawer Header -->
            <div class="p-5 sm:p-6 border-b border-[#23121F]/10 bg-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-[#3c3d1b]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h2 class="text-lg font-bold font-serif text-[#22121E]">
                        Your Shopping Bag
                    </h2>
                    <span x-text="count + ' items'" class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#fde4f2] text-[#e68bbe]">
                    </span>
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    @click="open = false"
                    aria-label="Close cart"
                    class="w-8 h-8 rounded-full bg-[#FCF8FA] hover:bg-[#F8EAF3] text-[#22121E] flex items-center justify-center transition-colors text-base font-bold cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Free Shipping Progress Tracker -->
            <div class="px-5 sm:px-6 py-3 bg-[#F8EAF3]/70 border-b border-[#23121F]/10">
                <template x-if="subtotal >= 999">
                    <div class="flex items-center gap-2 text-xs font-semibold text-[#22121E]">
                        <span>🎉</span>
                        <span>Congratulations! You've unlocked <strong>Free Express Shipping</strong>.</span>
                    </div>
                </template>
                <template x-if="subtotal < 999">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#6E5666] mb-1.5 font-medium">
                            <span>Add <strong class="text-[#22121E]">₹<span x-text="freeShippingDiff"></span></strong> for Free Shipping</span>
                            <span x-text="freeShippingPercent + '%'"></span>
                        </div>
                        <div class="w-full bg-white rounded-full h-2 overflow-hidden border border-[#23121F]/10">
                            <div class="bg-[#e68bbe] h-full rounded-full transition-all duration-300" :style="'width: ' + freeShippingPercent + '%'"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Cart Items List (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
                <!-- If Cart is Empty -->
                <template x-if="items.length === 0">
                    <div class="py-16 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-[#6E5666] shadow-sm mb-4 border border-[#23121F]/10">
                            <svg class="w-8 h-8 text-[#e68bbe]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold font-serif text-[#22121E]">Your Bag is Empty</h3>
                        <p class="text-xs text-[#6E5666] mt-1 max-w-xs">
                            Discover handpicked curtains, bedsheets, and towels to fill your living space with comfort.
                        </p>
                        <a
                            href="{{ route('products.index') }}"
                            @click="open = false"
                            class="mt-6 px-6 py-2.5 rounded-xl bg-[#3c3d1b] text-white text-xs font-bold hover:bg-[#2e2f14] transition-colors"
                        >
                            Explore Collections →
                        </a>
                    </div>
                </template>

                <!-- Items Loop -->
                <template x-for="item in items" :key="item.id">
                    <div class="flex items-start gap-3.5 p-3 rounded-2xl bg-white border border-[#23121F]/10 shadow-2xs">
                        <!-- Product Thumbnail -->
                        <img :src="item.image" :alt="item.name" class="w-20 h-20 rounded-xl object-cover bg-[#F8EAF3] flex-shrink-0 border border-[#23121F]/10" />

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <span x-text="item.category" class="text-[10px] uppercase font-bold text-[#e68bbe] tracking-wider block"></span>
                            <h4 x-text="item.name" class="text-xs sm:text-sm font-bold text-[#22121E] truncate mt-0.5"></h4>
                            <div class="text-xs font-extrabold text-[#22121E] mt-1">
                                ₹<span x-text="item.price.toLocaleString()"></span>
                            </div>

                            <!-- Quantity Counter + Remove -->
                            <div class="mt-2.5 flex items-center justify-between">
                                <div class="flex items-center rounded-lg bg-[#FCF8FA] border border-[#23121F]/15 px-1 py-0.5">
                                    <button
                                        type="button"
                                        @click="updateQuantity(item.id, -1)"
                                        class="w-6 h-6 flex items-center justify-center text-xs font-bold text-[#22121E] hover:text-[#e68bbe] cursor-pointer"
                                    >
                                        −
                                    </button>
                                    <span x-text="item.quantity" class="w-6 text-center text-xs font-bold text-[#22121E]"></span>
                                    <button
                                        type="button"
                                        @click="updateQuantity(item.id, 1)"
                                        class="w-6 h-6 flex items-center justify-center text-xs font-bold text-[#22121E] hover:text-[#e68bbe] cursor-pointer"
                                    >
                                        +
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    @click="removeItem(item.id)"
                                    class="text-[11px] text-[#6E5666] hover:text-red-500 transition-colors cursor-pointer"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Drawer Footer (Subtotal & WhatsApp Checkout) -->
            <div class="p-5 sm:p-6 bg-white border-t border-[#23121F]/10">
                <div class="space-y-1.5 mb-4 text-xs">
                    <div class="flex items-center justify-between text-[#6E5666]">
                        <span>Subtotal</span>
                        <span class="font-bold text-[#22121E]">₹<span x-text="subtotal.toLocaleString()"></span></span>
                    </div>
                    <div class="flex items-center justify-between text-[#6E5666]">
                        <span>Express Delivery</span>
                        <span x-text="shipping === 0 ? 'FREE' : '₹99'" class="font-bold" :class="shipping === 0 ? 'text-[#e68bbe]' : 'text-[#22121E]'"></span>
                    </div>
                    <div class="pt-2 border-t border-[#23121F]/10 flex items-center justify-between text-sm font-bold text-[#22121E]">
                        <span>Estimated Total</span>
                        <span class="text-base text-[#3c3d1b] font-extrabold">₹<span x-text="total.toLocaleString()"></span></span>
                    </div>
                </div>

                <!-- WhatsApp Direct Order Button -->
                <a
                    :href="whatsappCheckoutUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    :class="items.length === 0 ? 'opacity-50 pointer-events-none' : ''"
                    class="w-full flex items-center justify-center gap-2.5 py-3.5 px-6 rounded-xl bg-[#25d366] hover:bg-[#20ba5a] text-white text-sm font-bold tracking-wide transition-all shadow-md hover:shadow-lg transform active:scale-98"
                >
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    <span>Checkout via WhatsApp</span>
                    <span>→</span>
                </a>

                <!-- Continue Shopping -->
                <button
                    type="button"
                    @click="open = false"
                    class="w-full mt-2.5 py-2 text-center text-xs font-semibold text-[#6E5666] hover:text-[#22121E] transition-colors cursor-pointer"
                >
                    Continue Browsing
                </button>
            </div>

        </div>
    </div>
</div>
