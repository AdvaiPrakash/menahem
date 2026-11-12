@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $whatsappNumber = $brand['whatsapp_number'] ?? '919876543210';
@endphp

<!--
  SLIDE-OVER SHOPPING BAG / CART DRAWER
  State managed via Alpine.js on the wrapper element.
  Supports:
  - Slide in from right with backdrop blur
  - Add / remove / update quantity dynamically
  - Real-time subtotal & free shipping progress bar (Free delivery threshold: ₹999)
  - One-click checkout via WhatsApp with pre-composed itemized message
-->
<div
    x-data="{
        open: false,
        items: [
            {
                id: 1,
                name: 'Cotton Bedsheet & Dohar Set',
                price: 1499,
                quantity: 1,
                image: '{{ asset('/images/categories/bedsheets.webp') }}',
                category: 'Bedsheets'
            },
            {
                id: 5,
                name: '600 GSM Combed Cotton Towel Set',
                price: 699,
                quantity: 1,
                image: '{{ asset('/images/categories/towels.webp') }}',
                category: 'Towels'
            }
        ],
        get count() {
            return this.items.reduce((total, item) => total + item.quantity, 0);
        },
        get subtotal() {
            return this.items.reduce((total, item) => total + (item.price * item.quantity), 0);
        },
        get shipping() {
            return this.subtotal >= 999 ? 0 : 99;
        },
        get freeShippingDiff() {
            const diff = 999 - this.subtotal;
            return diff > 0 ? diff : 0;
        },
        get freeShippingPercent() {
            if (this.subtotal >= 999) return 100;
            return Math.min(100, Math.round((this.subtotal / 999) * 100));
        },
        get total() {
            return this.subtotal + this.shipping;
        },
        get whatsappCheckoutUrl() {
            if (this.items.length === 0) return 'https://wa.me/{{ $whatsappNumber }}';
            let msg = 'Hello Menahem! I would like to order the following items from your collection:%0A%0A';
            this.items.forEach((item, index) => {
                msg += (index + 1) + '. ' + encodeURIComponent(item.name) + ' (Qty: ' + item.quantity + ') - ₹' + (item.price * item.quantity) + '%0A';
            });
            msg += '%0A*Subtotal:* ₹' + this.subtotal;
            msg += '%0A*Delivery:* ' + (this.shipping === 0 ? 'FREE' : '₹99');
            msg += '%0A*Total Amount:* ₹' + this.total;
            msg += '%0A%0APlease confirm product availability and share payment details. Thank you!';
            return 'https://wa.me/{{ $whatsappNumber }}?text=' + msg;
        },
        updateQuantity(id, delta) {
            const item = this.items.find(i => i.id === id);
            if (!item) return;
            item.quantity += delta;
            if (item.quantity <= 0) {
                this.removeItem(id);
            }
        },
        removeItem(id) {
            this.items = this.items.filter(i => i.id !== id);
        },
        addItem(newItem) {
            const existing = this.items.find(i => i.id === newItem.id);
            if (existing) {
                existing.quantity += (newItem.quantity || 1);
            } else {
                this.items.push({
                    id: newItem.id,
                    name: newItem.name,
                    price: newItem.price,
                    quantity: newItem.quantity || 1,
                    image: newItem.image,
                    category: newItem.category || 'Collection'
                });
            }
            this.open = true;
        }
    }"
    @open-cart.window="open = true"
    @add-to-cart.window="addItem($event.detail)"
    x-cloak
    class="relative z-50"
>
    <!-- Backdrop Overlay -->
    <div
        x-show="open"
        x-transition:enter="ease-in-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-[#2B3326]/40 backdrop-blur-xs transition-opacity"
    ></div>

    <!-- Slide-over Drawer Panel -->
    <div
        x-show="open"
        x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 max-w-full flex pl-10"
    >
        <div class="w-screen max-w-md bg-[#FAF7F0] shadow-2xl flex flex-col justify-between border-l border-[#D7C7B2]">
            
            <!-- Drawer Header -->
            <div class="p-5 sm:p-6 border-b border-[#D7C7B2]/70 bg-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-[#74826B]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <h2 class="text-lg font-bold font-serif text-[#2B3326]">
                        Your Shopping Bag
                    </h2>
                    <span x-text="count + ' items'" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#E8E2D5] text-[#2B3326] border border-[#D7C7B2]">
                    </span>
                </div>

                <!-- Close Button -->
                <button
                    type="button"
                    @click="open = false"
                    aria-label="Close cart"
                    class="w-8 h-8 rounded-full bg-[#FAF7F0] hover:bg-[#E8E2D5] text-[#2B3326] flex items-center justify-center transition-colors text-base font-bold cursor-pointer"
                >
                    ✕
                </button>
            </div>

            <!-- Free Shipping Progress Tracker -->
            <div class="px-5 sm:px-6 py-3 bg-[#F3EFE6] border-b border-[#D7C7B2]">
                <template x-if="subtotal >= 999">
                    <div class="flex items-center gap-2 text-xs font-semibold text-[#2B3326]">
                        <span>🎉</span>
                        <span>Congratulations! You've unlocked <strong>Free Express Shipping</strong>.</span>
                    </div>
                </template>
                <template x-if="subtotal < 999">
                    <div>
                        <div class="flex items-center justify-between text-xs text-[#5C6654] mb-1.5 font-medium">
                            <span>Add <strong class="text-[#2B3326]">₹<span x-text="freeShippingDiff"></span></strong> for Free Shipping</span>
                            <span x-text="freeShippingPercent + '%'"></span>
                        </div>
                        <div class="w-full bg-white rounded-full h-2 overflow-hidden border border-[#D7C7B2]">
                            <div class="bg-[#74826B] h-full rounded-full transition-all duration-300" :style="'width: ' + freeShippingPercent + '%'"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Cart Items List (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-4">
                <!-- If Cart is Empty -->
                <template x-if="items.length === 0">
                    <div class="py-16 text-center flex flex-col items-center">
                        <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center text-[#5C6654] shadow-sm mb-4 border border-[#D7C7B2]">
                            <svg class="w-8 h-8 text-[#74826B]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold font-serif text-[#2B3326]">Your Bag is Empty</h3>
                        <p class="text-xs text-[#5C6654] mt-1 max-w-xs font-medium">
                            Discover handpicked curtains, bedsheets, and towels to fill your living space with comfort.
                        </p>
                        <a
                            href="{{ route('products.index') }}"
                            @click="open = false"
                            class="mt-6 px-6 py-2.5 rounded-xl bg-[#74826B] text-white text-xs font-bold hover:bg-[#636F5A] transition-colors shadow-xs"
                        >
                            Explore Collections →
                        </a>
                    </div>
                </template>

                <!-- Items Loop -->
                <template x-for="item in items" :key="item.id">
                    <div class="flex items-start gap-3.5 p-3 rounded-2xl bg-white border border-[#D7C7B2] shadow-2xs">
                        <!-- Product Thumbnail -->
                        <img :src="item.image" :alt="item.name" class="w-20 h-20 rounded-xl object-cover bg-[#F3EFE6] flex-shrink-0 border border-[#D7C7B2]/70" />

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <span x-text="item.category" class="text-[10px] uppercase font-bold text-[#74826B] tracking-wider block"></span>
                            <h4 x-text="item.name" class="text-xs sm:text-sm font-bold text-[#2B3326] truncate mt-0.5"></h4>
                            <div class="text-xs font-extrabold text-[#2B3326] mt-1">
                                ₹<span x-text="item.price.toLocaleString()"></span>
                            </div>

                            <!-- Quantity Counter + Remove -->
                            <div class="mt-2.5 flex items-center justify-between">
                                <div class="flex items-center rounded-lg bg-[#FAF7F0] border border-[#D7C7B2] px-1 py-0.5">
                                    <button
                                        type="button"
                                        @click="updateQuantity(item.id, -1)"
                                        class="w-6 h-6 flex items-center justify-center text-xs font-bold text-[#2B3326] hover:text-[#74826B] cursor-pointer"
                                    >
                                        −
                                    </button>
                                    <span x-text="item.quantity" class="w-6 text-center text-xs font-bold text-[#2B3326]"></span>
                                    <button
                                        type="button"
                                        @click="updateQuantity(item.id, 1)"
                                        class="w-6 h-6 flex items-center justify-center text-xs font-bold text-[#2B3326] hover:text-[#74826B] cursor-pointer"
                                    >
                                        +
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    @click="removeItem(item.id)"
                                    class="text-[11px] text-[#5C6654] hover:text-red-500 transition-colors cursor-pointer font-medium"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Drawer Footer (Subtotal & WhatsApp Checkout) -->
            <div class="p-5 sm:p-6 bg-white border-t border-[#D7C7B2]">
                <div class="space-y-1.5 mb-4 text-xs">
                    <div class="flex items-center justify-between text-[#5C6654] font-medium">
                        <span>Subtotal</span>
                        <span class="font-bold text-[#2B3326]">₹<span x-text="subtotal.toLocaleString()"></span></span>
                    </div>
                    <div class="flex items-center justify-between text-[#5C6654] font-medium">
                        <span>Express Delivery</span>
                        <span x-text="shipping === 0 ? 'FREE' : '₹99'" class="font-bold" :class="shipping === 0 ? 'text-[#74826B]' : 'text-[#2B3326]'"></span>
                    </div>
                    <div class="pt-2 border-t border-[#D7C7B2]/70 flex items-center justify-between text-sm font-bold text-[#2B3326]">
                        <span>Estimated Total</span>
                        <span class="text-base text-[#2B3326] font-extrabold">₹<span x-text="total.toLocaleString()"></span></span>
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
                    class="w-full mt-2.5 py-2 text-center text-xs font-semibold text-[#5C6654] hover:text-[#2B3326] transition-colors cursor-pointer"
                >
                    Continue Browsing
                </button>
            </div>

        </div>
    </div>
</div>
