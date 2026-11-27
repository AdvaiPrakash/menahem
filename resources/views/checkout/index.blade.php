@extends('layouts.app')

@section('title', 'Secure Checkout · Menahem Luxury Home Essentials')

@section('content')
<div class="min-h-screen bg-[#FBFBF9] pt-24 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" 
         x-data="{
            // Checkout flow mode: 'buy_now' (single product) vs 'cart' (shopping bag)
            checkoutMode: new URLSearchParams(window.location.search).get('mode') === 'buy_now' ? 'buy_now' : 'cart',

            // Cart Items pulled from Buy Now session or Cart
            items: [],
            couponCode: '',
            couponApplied: false,
            couponDiscount: 0,
            couponMessage: '',
            couponError: '',
            isApplyingCoupon: false,

            // Financial Summary
            subtotal: 0,
            taxableAmount: 0,
            cgstAmount: 0,
            sgstAmount: 0,
            igstAmount: 0,
            shippingFee: 0,
            gatewayFee: 0,
            grandTotal: 0,
            isIntraState: true,

            // Customer Details Form
            customerName: '{{ $user->name ?? '' }}',
            customerEmail: '{{ $user->email ?? '' }}',
            customerPhone: '{{ $user->phone ?? '' }}',
            shippingLine1: '',
            shippingLine2: '',
            shippingCity: '',
            shippingState: '{{ $shippingSettings['origin_state'] ?? 'Maharashtra' }}',
            shippingPin: '',
            billingSame: true,
            billingLine1: '',
            billingLine2: '',
            billingCity: '',
            billingState: '{{ $shippingSettings['origin_state'] ?? 'Maharashtra' }}',
            billingPin: '',

            // PIN Code Auto-Fill & Dynamic Shipping State
            pinLookupLoading: false,
            pinLookupError: '',
            pinLookupInfo: '',
            pinLookupCache: {},
            shippingCalculated: false,
            shippingCalculating: false,
            shippingError: '',
            shippingMessage: 'Enter delivery PIN code to calculate',
            shippingDistanceKm: null,
            shippingFreeKm: {{ (float) ($shippingSettings['free_distance_km'] ?? 5) }},
            shippingRatePerKm: {{ (float) ($shippingSettings['charge_per_km'] ?? 10) }},
            shippingBillableKm: null,
            calcDebounceTimer: null,

            // UI & Payment State
            isProcessing: false,
            errorMessage: '',
            showSimulatedModal: false,
            simulatedMethod: 'UPI',
            simulatedOrderData: null,
            simulatedPaymentHandler: null,

            init() {
                if (this.checkoutMode === 'buy_now') {
                    try {
                        const buyNowRaw = sessionStorage.getItem('menahem_buy_now');
                        if (buyNowRaw) {
                            const parsed = JSON.parse(buyNowRaw);
                            if (Array.isArray(parsed)) {
                                this.items = parsed;
                            }
                        }
                    } catch (e) {}

                    if (this.items && this.items.length > 0) {
                        this.calculateBreakdown();
                    }
                    return;
                }

                // Cart Checkout mode: Initialize cart items from localStorage
                try {
                    const saved = localStorage.getItem('menahem_cart');
                    if (saved) {
                        const parsed = JSON.parse(saved);
                        if (Array.isArray(parsed)) {
                            this.items = parsed;
                        }
                    }
                } catch(e) {}

                // Check server-side persistent cart
                fetch('{{ route('cart.index') }}', {
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data && data.success && Array.isArray(data.items) && data.items.length > 0) {
                        this.items = data.items;
                        this.calculateBreakdown();
                    }
                }).catch(() => {});

                if (this.items && this.items.length > 0) {
                    this.calculateBreakdown();
                }
            },

            handlePinInput() {
                const clean = String(this.shippingPin || '').replace(/\D/g, '').slice(0, 6);
                this.shippingPin = clean;
                this.pinLookupInfo = '';
                this.shippingError = '';

                if (clean.length === 0) {
                    this.pinLookupError = '';
                    this.shippingCalculated = false;
                    this.shippingFee = 0;
                    this.shippingDistanceKm = null;
                    this.shippingBillableKm = null;
                    this.shippingMessage = 'Enter delivery PIN code to calculate';
                    this.scheduleCalculateBreakdown(0);
                    return;
                }

                if (clean.length < 6) {
                    this.pinLookupError = '';
                    this.shippingCalculated = false;
                    this.shippingFee = 0;
                    this.shippingDistanceKm = null;
                    this.shippingBillableKm = null;
                    this.shippingMessage = 'Enter all 6 digits of your PIN code';
                    return;
                }

                this.lookupPinCode(clean);
            },

            validatePinOnBlur() {
                const clean = String(this.shippingPin || '').trim();
                if (clean.length > 0 && !/^[1-9]\d{5}$/.test(clean)) {
                    this.pinLookupError = 'Please enter a valid 6-digit Indian PIN code.';
                    this.shippingCalculated = false;
                }
            },

            lookupPinCode(pin) {
                if (!/^[1-9]\d{5}$/.test(pin)) {
                    this.pinLookupError = 'Please enter a valid 6-digit Indian PIN code (cannot start with 0).';
                    this.pinLookupInfo = '';
                    this.shippingCalculated = false;
                    this.shippingError = this.pinLookupError;
                    this.shippingMessage = 'Invalid PIN code';
                    return;
                }

                if (this.pinLookupCache[pin]) {
                    const cached = this.pinLookupCache[pin];
                    if (cached.valid) {
                        if (cached.city) this.shippingCity = cached.city;
                        if (cached.state) this.shippingState = cached.state;
                        this.pinLookupError = '';
                        this.pinLookupInfo = [cached.post_office, cached.district, cached.state].filter(Boolean).join(', ');
                        this.scheduleCalculateBreakdown(0);
                    } else {
                        this.pinLookupError = cached.message || 'PIN code not found.';
                        this.pinLookupInfo = '';
                        this.shippingCalculated = false;
                        this.shippingError = this.pinLookupError;
                    }
                    return;
                }

                this.pinLookupLoading = true;
                this.pinLookupError = '';
                this.pinLookupInfo = '';

                fetch('{{ route('checkout.pincode-lookup') }}?pincode=' + encodeURIComponent(pin), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    this.pinLookupLoading = false;
                    this.pinLookupCache[pin] = data;

                    if (ok && data.valid) {
                        if (data.city) this.shippingCity = data.city;
                        if (data.state) this.shippingState = data.state;
                        this.pinLookupError = '';
                        this.pinLookupInfo = [data.post_office, data.district, data.state].filter(Boolean).join(', ');
                        this.scheduleCalculateBreakdown(0);
                    } else {
                        this.pinLookupError = data.message || 'Invalid or unavailable Indian PIN code.';
                        this.pinLookupInfo = '';
                        this.shippingCalculated = false;
                        this.shippingError = this.pinLookupError;
                        this.shippingMessage = 'Unable to verify PIN code';
                    }
                })
                .catch(() => {
                    this.pinLookupLoading = false;
                    this.pinLookupError = 'Unable to verify PIN code right now. Please try again.';
                });
            },

            scheduleCalculateBreakdown(delay = 350) {
                if (this.calcDebounceTimer) {
                    clearTimeout(this.calcDebounceTimer);
                }
                this.calcDebounceTimer = setTimeout(() => {
                    this.calculateBreakdown();
                }, delay);
            },

            calculateBreakdown() {
                let sub = 0;
                this.items.forEach(i => sub += (parseFloat(i.price) * parseInt(i.quantity)));
                this.subtotal = sub;

                const cleanPin = String(this.shippingPin || '').trim();
                if (cleanPin.length === 6) {
                    this.shippingCalculating = true;
                }

                fetch('{{ route('checkout.calculate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: this.items,
                        coupon_code: this.couponCode,
                        state: this.shippingState,
                        shipping_state: this.shippingState,
                        shipping_city: this.shippingCity,
                        shipping_pin_code: cleanPin,
                        shipping_address_line1: this.shippingLine1,
                        shipping_address_line2: this.shippingLine2
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.shippingCalculating = false;
                    this.subtotal = data.subtotal;
                    this.couponDiscount = data.discount;
                    this.taxableAmount = data.taxable_amount;
                    this.cgstAmount = data.cgst_amount;
                    this.sgstAmount = data.sgst_amount;
                    this.igstAmount = data.igst_amount;
                    this.shippingCalculated = Boolean(data.shipping_calculated);
                    this.shippingFee = data.shipping_fee !== null && data.shipping_fee !== undefined ? Number(data.shipping_fee) : 0;
                    this.shippingError = data.shipping_error || '';
                    this.shippingMessage = data.shipping_message || '';
                    this.shippingDistanceKm = data.distance_km ?? null;
                    this.shippingFreeKm = data.free_distance_km ?? this.shippingFreeKm;
                    this.shippingRatePerKm = data.charge_per_km ?? this.shippingRatePerKm;
                    this.shippingBillableKm = data.billable_distance_km ?? null;
                    this.gatewayFee = data.gateway_fee;
                    this.grandTotal = data.grand_total;
                    this.isIntraState = data.is_intra_state;

                    if (data.coupon_valid) {
                        this.couponApplied = true;
                        this.couponMessage = data.coupon_message;
                        this.couponError = '';
                    } else if (this.couponCode && data.coupon_message) {
                        this.couponApplied = false;
                        this.couponError = data.coupon_message;
                    }
                })
                .catch(err => {
                    this.shippingCalculating = false;
                    console.error(err);
                });
            },

            applyCoupon() {
                if (!this.couponCode.trim()) {
                    this.couponError = 'Please enter an offer code.';
                    return;
                }
                this.isApplyingCoupon = true;
                this.couponError = '';
                this.couponMessage = '';

                fetch('{{ route('checkout.validate-coupon') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        code: this.couponCode,
                        amount: this.subtotal
                    })
                })
                .then(r => r.json())
                .then(res => {
                    this.isApplyingCoupon = false;
                    if (res.valid) {
                        this.couponApplied = true;
                        this.couponMessage = res.message;
                        this.calculateBreakdown();
                    } else {
                        this.couponApplied = false;
                        this.couponDiscount = 0;
                        this.couponError = res.message;
                        this.calculateBreakdown();
                    }
                })
                .catch(() => {
                    this.isApplyingCoupon = false;
                    this.couponError = 'Failed to validate coupon. Please try again.';
                });
            },

            removeCoupon() {
                this.couponCode = '';
                this.couponApplied = false;
                this.couponDiscount = 0;
                this.couponMessage = '';
                this.couponError = '';
                this.calculateBreakdown();
            },

            proceedToPayment() {
                // Client validation
                if (!this.items || this.items.length === 0) {
                    this.errorMessage = 'Your shopping bag is empty. Please add items before checking out.';
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                    return;
                }
                if (!this.customerName.trim() || !this.customerEmail.trim() || !this.customerPhone.trim()) {
                    this.errorMessage = 'Please provide your full name, email, and mobile number.';
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                    return;
                }
                if (!this.shippingLine1.trim() || !this.shippingCity.trim() || !this.shippingPin.trim()) {
                    this.errorMessage = 'Please complete your shipping address details.';
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                    return;
                }
                if (!/^[1-9]\d{5}$/.test(this.shippingPin.trim()) || this.pinLookupError) {
                    this.errorMessage = this.pinLookupError || 'Please enter a valid 6-digit Indian delivery PIN code.';
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                    return;
                }
                if (this.shippingError || !this.shippingCalculated) {
                    this.errorMessage = this.shippingError || 'Please wait for delivery shipping calculation to complete.';
                    window.scrollTo({ top: 100, behavior: 'smooth' });
                    return;
                }

                this.isProcessing = true;
                this.errorMessage = '';

                // 1. Create order on backend
                fetch('{{ route('checkout.create-order') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        items: this.items,
                        customer_name: this.customerName,
                        customer_email: this.customerEmail,
                        customer_phone: this.customerPhone,
                        shipping_address_line1: this.shippingLine1,
                        shipping_address_line2: this.shippingLine2,
                        shipping_city: this.shippingCity,
                        shipping_state: this.shippingState,
                        shipping_pin_code: this.shippingPin,
                        billing_same_as_shipping: this.billingSame,
                        billing_address_line1: this.billingLine1,
                        billing_address_line2: this.billingLine2,
                        billing_city: this.billingCity,
                        billing_state: this.billingState,
                        billing_pin_code: this.billingPin,
                        coupon_code: this.couponApplied ? this.couponCode : null
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        this.isProcessing = false;
                        this.errorMessage = data.message || 'Unable to initialize order. Please check inputs.';
                        return;
                    }

                    // 2. Open Razorpay Checkout Modal
                    const options = {
                        key: data.razorpay_key_id,
                        amount: data.amount,
                        currency: data.currency,
                        name: data.business_name || 'Menahem Home & Living',
                        description: 'Order #' + data.order_number,
                        image: '{{ asset('/images/logo.png') }}',
                        order_id: data.razorpay_order_id,
                        prefill: {
                            name: data.customer_name,
                            email: data.customer_email,
                            contact: data.customer_phone
                        },
                        theme: {
                            color: '#384230'
                        },
                        handler: (response) => {
                            // 3. Verify payment signature on backend
                            fetch('{{ route('checkout.verify-payment') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    order_number: data.order_number,
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            })
                            .then(r => r.json())
                            .then(verifyRes => {
                                if (verifyRes.success) {
                                    if (this.checkoutMode === 'buy_now') {
                                        try { sessionStorage.removeItem('menahem_buy_now'); } catch(e){}
                                        window.location.href = verifyRes.redirect_url;
                                    } else {
                                        try { localStorage.removeItem('menahem_cart'); } catch(e){}
                                        fetch('{{ route('cart.clear') }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        }).catch(() => {}).finally(() => {
                                            window.location.href = verifyRes.redirect_url;
                                        });
                                    }
                                } else {
                                    this.isProcessing = false;
                                    this.errorMessage = verifyRes.message || 'Payment signature verification failed.';
                                }
                            })
                            .catch(err => {
                                this.isProcessing = false;
                                this.errorMessage = 'Network error while confirming payment.';
                            });
                        },
                        modal: {
                            ondismiss: () => {
                                this.isProcessing = false;
                            }
                        }
                    };

                    // Check if Razorpay script is loaded for live/keyed checkout, otherwise open Simulated Test Payment Modal
                    if (typeof Razorpay !== 'undefined' && data.razorpay_key_id && !data.is_simulated && !String(data.razorpay_order_id || '').startsWith('order_sim_')) {
                        const rzp = new Razorpay(options);
                        rzp.open();
                    } else {
                        this.simulatedOrderData = data;
                        this.simulatedPaymentHandler = options.handler;
                        this.showSimulatedModal = true;
                    }
                })
                .catch(err => {
                    this.isProcessing = false;
                    this.errorMessage = 'Something went wrong while connecting to the checkout server.';
                });
            },

            confirmSimulatedPayment() {
                if (!this.simulatedOrderData || !this.simulatedPaymentHandler) {
                    return;
                }
                const handler = this.simulatedPaymentHandler;
                const orderId = this.simulatedOrderData.razorpay_order_id;
                this.showSimulatedModal = false;
                handler({
                    razorpay_order_id: orderId,
                    razorpay_payment_id: 'pay_sim_' + Math.random().toString(36).substring(2, 12),
                    razorpay_signature: 'simulated_valid_signature'
                });
            },

            cancelSimulatedPayment() {
                this.showSimulatedModal = false;
                this.isProcessing = false;
                this.simulatedOrderData = null;
                this.simulatedPaymentHandler = null;
            }
         }">

        <!-- Page Header -->
        <div class="mb-8 text-center sm:text-left">
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#2B3326]">Checkout</h1>
            <p class="text-xs sm:text-sm text-[#5C6654] mt-1">Review your order details and complete payment via Razorpay secure gateway.</p>
        </div>

        <!-- Global Error Notification -->
        <div x-show="errorMessage" x-cloak class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-xs">
            <span class="text-lg">⚠️</span>
            <div class="flex-1 font-medium" x-text="errorMessage"></div>
            <button type="button" @click="errorMessage = ''" class="text-rose-500 font-bold hover:text-rose-800">✕</button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Column: Customer & Delivery Details Form (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Customer Contact Details -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-neutral-200 shadow-xs">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-neutral-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-6 h-6 rounded-full bg-[#384230] text-white text-xs font-bold flex items-center justify-center">1</span>
                            <h2 class="text-base font-bold text-[#2B3326]">Customer Details</h2>
                        </div>
                        @guest
                            <div class="text-xs text-[#5C6654]">
                                Checking out as <strong class="text-[#2B3326]">Guest</strong> • 
                                <a href="{{ route('customer.login') }}" class="text-[#74826B] font-semibold hover:underline">Log in for faster checkout</a>
                            </div>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                Logged in as {{ Auth::user()->name }}
                            </span>
                        @endguest
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                Full Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="customerName" placeholder="e.g. Aditi Sharma" required
                                   class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                Mobile Number <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" x-model="customerPhone" placeholder="+91 98201 23456" required
                                   class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                Email Address <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" x-model="customerEmail" placeholder="aditi@example.com" required
                                   class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                            <p class="text-[11px] text-[#5C6654] mt-1">Order receipt and GST tax invoice PDF will be sent here.</p>
                        </div>
                    </div>
                </div>

                <!-- Delivery Address -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-neutral-200 shadow-xs">
                    <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-neutral-100">
                        <span class="w-6 h-6 rounded-full bg-[#384230] text-white text-xs font-bold flex items-center justify-center">2</span>
                        <h2 class="text-base font-bold text-[#2B3326]">Delivery Address</h2>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                Flat / House No., Building Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="shippingLine1" @input.debounce.450ms="if (shippingPin.length === 6) scheduleCalculateBreakdown(100)" placeholder="Flat 402, Oakwood Residences" required
                                   class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                Street Address, Area, Landmark
                            </label>
                            <input type="text" x-model="shippingLine2" @input.debounce.450ms="if (shippingPin.length === 6) scheduleCalculateBreakdown(100)" placeholder="Near Bandra Kurla Complex, Bandra East"
                                   class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                    PIN Code <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text"
                                           x-model="shippingPin"
                                           @input="handlePinInput()"
                                           @blur="validatePinOnBlur()"
                                           placeholder="400051"
                                           required
                                           maxlength="6"
                                           inputmode="numeric"
                                           :class="pinLookupError ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/20' : 'border-neutral-200 focus:border-[#74826B] focus:ring-[#74826B]/20'"
                                           class="w-full px-4 py-2.5 rounded-lg border text-sm font-mono focus:outline-none focus:ring-2">
                                    <span x-show="pinLookupLoading" x-cloak class="absolute right-3 top-3 text-[11px] font-semibold text-[#74826B]">
                                        ...
                                    </span>
                                </div>
                                <p x-show="pinLookupLoading" x-cloak class="text-[11px] text-[#74826B] font-medium mt-1">Looking up PIN code...</p>
                                <p x-show="!pinLookupLoading && pinLookupInfo" x-cloak class="text-[11px] text-emerald-700 font-medium mt-1">
                                    ✓ <span x-text="pinLookupInfo"></span>
                                </p>
                                <p x-show="!pinLookupLoading && pinLookupError" x-cloak class="text-[11px] text-rose-600 font-semibold mt-1" x-text="pinLookupError"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                    City <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" x-model="shippingCity" @input.debounce.450ms="if (shippingPin.length === 6) scheduleCalculateBreakdown(100)" placeholder="Mumbai" required
                                       class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">
                                    State <span class="text-rose-500">*</span>
                                </label>
                                <select x-model="shippingState" @change="calculateBreakdown()" required
                                        class="w-full px-3 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                                    @foreach($states as $stateName => $stateCode)
                                        <option value="{{ $stateName }}">{{ $stateName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Billing Same Toggle -->
                        <div class="pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-[#2B3326]">
                                <input type="checkbox" x-model="billingSame" class="w-4 h-4 rounded text-[#74826B] focus:ring-[#74826B]">
                                <span>Billing address is same as delivery address</span>
                            </label>
                        </div>

                        <!-- Separate Billing Address -->
                        <div x-show="!billingSame" x-cloak class="pt-4 border-t border-neutral-100 space-y-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#5C6654]">Billing Address</h3>
                            <input type="text" x-model="billingLine1" placeholder="Billing House / Flat No." class="w-full px-4 py-2 rounded-lg border border-neutral-200 text-sm">
                            <div class="grid grid-cols-3 gap-3">
                                <input type="text" x-model="billingCity" placeholder="City" class="px-3 py-2 rounded-lg border border-neutral-200 text-sm">
                                <input type="text" x-model="billingState" placeholder="State" class="px-3 py-2 rounded-lg border border-neutral-200 text-sm">
                                <input type="text" x-model="billingPin" placeholder="PIN" class="px-3 py-2 rounded-lg border border-neutral-200 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Information -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-neutral-200 shadow-xs">
                    <div class="flex items-center gap-2.5 pb-4 mb-4 border-b border-neutral-100">
                        <span class="w-6 h-6 rounded-full bg-[#384230] text-white text-xs font-bold flex items-center justify-center">3</span>
                        <h2 class="text-base font-bold text-[#2B3326]">Payment Method</h2>
                    </div>

                    <div class="p-4 rounded-xl border-2 border-[#74826B] bg-[#74826B]/5 flex items-start gap-4">
                        <div class="w-5 h-5 rounded-full border-4 border-[#384230] bg-white shrink-0 mt-0.5"></div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-sm text-[#2B3326]">Razorpay Secure Gateway</span>
                                <span class="text-[10px] font-bold bg-[#384230] text-white px-2 py-0.5 rounded">UPI / Cards / NetBanking</span>
                            </div>
                            <p class="text-xs text-[#5C6654] mt-1">
                                Pay securely using Google Pay, PhonePe, Paytm, any UPI App, Visa, MasterCard, RuPay, Net Banking, or Wallets.
                            </p>
                            <div class="flex items-center gap-3 mt-3 opacity-80">
                                <span class="text-xs font-semibold text-[#2B3326]">🔒 256-Bit SSL Encrypted Payment</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary & Live Offer Code (5 cols) -->
            <div class="lg:col-span-5 lg:sticky lg:top-8 space-y-6">
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-neutral-200 shadow-xs">
                    <h2 class="text-base font-bold text-[#2B3326] pb-4 border-b border-neutral-100 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span>Order Summary</span>
                            <span x-show="checkoutMode === 'buy_now'" x-cloak class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-[#384230]/10 text-[#384230] rounded">Buy Now</span>
                        </span>
                        <span class="text-xs font-semibold text-[#74826B]" x-text="items.length + ' item(s)'"></span>
                    </h2>

                    <!-- Items List -->
                    <div class="divide-y divide-neutral-100 max-h-64 overflow-y-auto py-2">
                        <template x-if="!items || items.length === 0">
                            <div class="text-center py-6 text-neutral-400 bg-neutral-50 rounded-lg p-4">
                                <p class="text-xs font-medium">Your shopping bag is currently empty.</p>
                                <a href="{{ route('products.index') }}" class="inline-block mt-2 text-xs font-bold text-[#384230] hover:underline">
                                    &larr; Browse Products
                                </a>
                            </div>
                        </template>
                        <template x-for="item in items" :key="item.id">
                            <div class="py-3 flex items-center gap-3">
                                <img :src="item.image" :alt="item.name" class="w-12 h-12 rounded-lg object-cover bg-neutral-100 border border-neutral-200 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-bold text-[#2B3326] truncate" x-text="item.name"></h4>
                                    <template x-if="item.custom_size">
                                        <p class="text-[10px] text-[#74826B]" x-text="'Custom: ' + item.custom_size"></p>
                                    </template>
                                    <p class="text-[11px] text-[#5C6654]" x-text="'Qty: ' + item.quantity + ' × ₹' + Number(item.price).toLocaleString()"></p>
                                </div>
                                <span class="text-xs font-extrabold text-[#2B3326]" x-text="'₹' + (Number(item.price) * parseInt(item.quantity)).toLocaleString()"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Module 1: Offer Code / Coupon Section -->
                    <div class="pt-4 border-t border-neutral-100">
                        <label class="block text-xs font-bold text-[#2B3326] uppercase tracking-wider mb-2">
                            Have an Offer Code?
                        </label>
                        <div class="flex gap-2">
                            <input type="text" 
                                   x-model="couponCode" 
                                   placeholder="e.g. WELCOME10" 
                                   :disabled="couponApplied"
                                   class="flex-1 px-3 py-2 text-xs font-mono uppercase font-bold rounded-lg border border-neutral-300 focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B] disabled:bg-neutral-100">
                            <template x-if="!couponApplied">
                                <button type="button" 
                                        @click="applyCoupon()" 
                                        :disabled="isApplyingCoupon"
                                        class="px-4 py-2 bg-[#384230] hover:bg-[#2C3424] text-white text-xs font-bold rounded-lg transition-colors cursor-pointer">
                                    <span x-show="!isApplyingCoupon">Apply</span>
                                    <span x-show="isApplyingCoupon">...</span>
                                </button>
                            </template>
                            <template x-if="couponApplied">
                                <button type="button" 
                                        @click="removeCoupon()" 
                                        class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg transition-colors cursor-pointer">
                                    Remove
                                </button>
                            </template>
                        </div>

                        <!-- Coupon Feedback -->
                        <div x-show="couponMessage" x-cloak class="mt-2 text-xs font-semibold text-emerald-700 flex items-center gap-1">
                            <span>✓</span> <span x-text="couponMessage"></span>
                        </div>
                        <div x-show="couponError" x-cloak class="mt-2 text-xs font-semibold text-rose-600 flex items-center gap-1">
                            <span>✕</span> <span x-text="couponError"></span>
                        </div>
                    </div>

                    <!-- Price Breakdown Table -->
                    <div class="pt-5 mt-5 border-t border-neutral-200 space-y-2 text-xs">
                        <div class="flex justify-between text-[#5C6654]">
                            <span>Product Subtotal (Incl. GST)</span>
                            <span class="font-bold text-[#2B3326]" x-text="'₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>

                        <template x-if="couponDiscount > 0">
                            <div class="flex justify-between text-emerald-700 font-bold">
                                <span x-text="'Coupon Discount (' + couponCode + ')'"></span>
                                <span x-text="'-₹' + couponDiscount.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                        </template>

                        <div class="pt-1.5 border-t border-dashed border-neutral-200 space-y-1 text-[11px] text-[#5C6654]">
                            <div class="flex justify-between">
                                <span>Taxable Value (Excl. GST)</span>
                                <span class="font-semibold text-[#2B3326]" x-text="'₹' + taxableAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>

                            <template x-if="isIntraState">
                                <div class="space-y-1">
                                    <div class="flex justify-between">
                                        <span>CGST (2.5% • Included)</span>
                                        <span x-text="'₹' + cgstAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>SGST (2.5% • ' + shippingState + ' • Included)</span>
                                        <span x-text="'₹' + sgstAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!isIntraState">
                                <div class="flex justify-between">
                                    <span>IGST (Interstate • Included)</span>
                                    <span x-text="'₹' + igstAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                </div>
                            </template>
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between text-[#5C6654]">
                                <span>Shipping & Delivery</span>
                                <template x-if="shippingCalculating">
                                    <span class="font-semibold text-[#74826B]">Calculating...</span>
                                </template>
                                <template x-if="!shippingCalculating && shippingError">
                                    <span class="font-semibold text-rose-600">Unavailable</span>
                                </template>
                                <template x-if="!shippingCalculating && !shippingError && !shippingCalculated">
                                    <span class="font-medium text-neutral-500">Enter PIN code</span>
                                </template>
                                <template x-if="!shippingCalculating && !shippingError && shippingCalculated">
                                    <span class="font-bold" :class="shippingFee === 0 ? 'text-[#74826B]' : 'text-[#2B3326]'"
                                          x-text="shippingFee === 0 ? 'FREE' : '₹' + shippingFee.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                                </template>
                            </div>
                            <p x-show="shippingError" x-cloak class="text-[11px] text-rose-600 font-medium" x-text="shippingError"></p>
                            <p x-show="!shippingError && shippingCalculated && shippingMessage" x-cloak class="text-[11px] text-[#5C6654]" x-text="shippingMessage"></p>
                        </div>

                        <template x-if="gatewayFee > 0">
                            <div class="flex justify-between text-[#5C6654]">
                                <span>Gateway / Convenience Fee</span>
                                <span x-text="'₹' + gatewayFee.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                            </div>
                        </template>

                        <div class="pt-3 border-t border-neutral-200 flex justify-between items-center text-sm font-bold text-[#2B3326]">
                            <span>Final Payable Amount</span>
                            <span class="text-xl font-extrabold text-[#384230]" x-text="'₹' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <button type="button" 
                            @click="proceedToPayment()" 
                            :disabled="isProcessing || !items || items.length === 0"
                            class="w-full mt-6 py-3.5 px-6 rounded-lg bg-[#384230] hover:bg-[#2C3424] text-white text-sm font-bold tracking-wide transition-all shadow-md hover:shadow-lg disabled:opacity-60 cursor-pointer flex items-center justify-center gap-2">
                        <span x-show="!isProcessing">Pay Now</span>
                        <span x-show="isProcessing" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Processing Payment...</span>
                        </span>
                    </button>

                    <p class="text-[11px] text-center text-[#5C6654] mt-3">
                        By proceeding with payment, you agree to Menahem Terms of Service & Privacy Policy.
                    </p>
                </div>
            </div>
        </div>

        <!-- Simulated Razorpay Test Payment Modal -->
        <div x-show="showSimulatedModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="simulated-payment-title">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-neutral-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-[#384230] text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-400 text-neutral-900 mb-1">
                            Test Mode • Simulated Payment
                        </span>
                        <h3 id="simulated-payment-title" class="text-base font-bold">MENAHEM Checkout</h3>
                        <p class="text-[11px] text-white/80 font-mono" x-text="simulatedOrderData ? ('Order #' + simulatedOrderData.order_number) : ''"></p>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] uppercase tracking-wider text-white/75 block">Amount</span>
                        <span class="text-lg font-extrabold" x-text="'₹' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4">
                    <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                        <strong>Simulated Test Environment:</strong> No real money will be charged. Select a test method below to simulate payment completion.
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#2B3326] mb-2">Select Test Payment Method</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="method in ['UPI', 'Card', 'NetBanking']" :key="method">
                                <button type="button"
                                        @click="simulatedMethod = method"
                                        class="py-2.5 px-3 rounded-lg text-xs font-bold border transition-colors cursor-pointer"
                                        :class="simulatedMethod === method
                                            ? 'bg-[#384230] text-white border-[#384230]'
                                            : 'bg-white text-[#2B3326] border-neutral-200 hover:bg-neutral-50'"
                                        x-text="method">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row gap-2.5">
                        <button type="button"
                                @click="confirmSimulatedPayment()"
                                class="flex-1 py-3 px-4 rounded-lg bg-[#384230] hover:bg-[#2B3326] text-white text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                            Simulate Successful Payment
                        </button>
                        <button type="button"
                                @click="cancelSimulatedPayment()"
                                class="py-3 px-4 rounded-lg border border-neutral-300 text-neutral-700 hover:bg-neutral-100 text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Standard Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endsection
