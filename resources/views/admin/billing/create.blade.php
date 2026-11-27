@extends('admin.layouts.app')

@section('title', 'Manual Billing')
@section('header_title', 'Manual Billing')

@section('header_actions')
    <a href="{{ route('admin.accounts.index') }}"
       class="px-3.5 py-2 border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        Accounts
    </a>
    <a href="{{ route('admin.orders.index') }}"
       class="px-3.5 py-2 border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        &larr; Orders
    </a>
@endsection

@section('content')
<div x-data="manualBillingForm()" class="flex flex-col lg:h-[calc(100vh-8.5rem)]">
    <form action="{{ route('admin.billing.store') }}"
          method="POST"
          @submit="handleFormSubmit($event)"
          class="flex flex-col flex-1 min-h-0 gap-3.5">
        @csrf

        <!-- Validation Error Banner (Server + Client) -->
        @if($errors->any())
            <div class="shrink-0 p-3 bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold">
                {{ $errors->first() }}
            </div>
        @endif
        <div x-show="formError || finalAmountError" x-cloak class="shrink-0 p-3 bg-rose-50 border border-rose-300 text-rose-800 text-xs font-semibold flex items-center justify-between gap-2">
            <span x-text="formError || finalAmountError"></span>
            <button type="button" @click="formError = ''; finalAmountError = ''" class="text-rose-600 font-bold px-1 cursor-pointer">&times;</button>
        </div>

        <!-- Middle Two-Column Area -->
        <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-12 gap-4 items-stretch">

            <!-- LEFT COLUMN: Main Independently Scrollable Product Billing Section -->
            <div class="lg:col-span-7 flex flex-col min-h-0 bg-white border border-neutral-200">
                <div class="shrink-0 px-4 sm:px-5 py-3.5 border-b border-neutral-200 bg-neutral-50/80 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Product Billing</h2>
                        <p class="text-[11px] text-neutral-500 mt-0.5">Search &amp; add items (all prices include 5% GST)</p>
                    </div>
                    <button type="button"
                            @click="addRow()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                        <span>+ Add Item</span>
                    </button>
                </div>

                <!-- Independently Scrollable Product Rows -->
                <div class="flex-1 lg:overflow-y-auto p-4 space-y-3">
                    <template x-for="(row, index) in rows" :key="row.uid">
                        <div class="p-3.5 border border-neutral-200 bg-neutral-50/40 space-y-2.5">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 items-end">
                                <!-- Searchable Product Selector -->
                                <div class="sm:col-span-5 relative" @click.outside="row.dropdownOpen = false">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Product *</label>
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="row.product_id">
                                    <button type="button"
                                            @click="toggleProductDropdown(row)"
                                            class="w-full px-3 py-2 text-xs border bg-white text-left flex items-center justify-between gap-2 focus:outline-none focus:border-neutral-900 cursor-pointer"
                                            :class="row.product_id ? 'border-neutral-300 text-neutral-900' : 'border-neutral-300 text-neutral-500'">
                                        <span class="truncate" x-text="selectedProductLabel(row)"></span>
                                        <svg class="w-3.5 h-3.5 text-neutral-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="row.dropdownOpen"
                                         x-cloak
                                         class="absolute left-0 right-0 mt-1 bg-white border border-neutral-300 shadow-lg z-40">
                                        <div class="p-2 border-b border-neutral-200 bg-neutral-50">
                                            <input type="text"
                                                   :id="`billing-product-search-${row.uid}`"
                                                   x-model="row.searchQuery"
                                                   @keydown.escape.stop="row.dropdownOpen = false"
                                                   @keydown.enter.prevent="selectFirstMatch(row)"
                                                   placeholder="Search product by name or SKU..."
                                                   class="w-full px-2.5 py-1.5 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                        </div>
                                        <div class="max-h-60 overflow-y-auto divide-y divide-neutral-100">
                                            <template x-for="prod in filteredProducts(row)" :key="prod.id">
                                                <button type="button"
                                                        @click="selectProduct(row, prod)"
                                                        class="w-full px-3 py-2 text-left hover:bg-neutral-100 transition-colors flex items-center justify-between gap-2 cursor-pointer"
                                                        :class="String(row.product_id) === String(prod.id) ? 'bg-neutral-100 font-bold' : ''">
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-semibold text-neutral-900 truncate" x-text="prod.name"></p>
                                                        <p class="text-[10px] text-neutral-500 truncate" x-text="(prod.sku ? prod.sku + ' • ' : '') + 'Stock: ' + prod.stock_quantity"></p>
                                                    </div>
                                                    <span class="text-xs font-bold text-neutral-900 shrink-0" x-text="'₹' + Number(prod.price).toFixed(2)"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredProducts(row).length === 0" class="px-3 py-4 text-center text-xs text-neutral-400">
                                                No matching products found.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Qty *</label>
                                    <div class="flex items-center border border-neutral-300 bg-white">
                                        <button type="button"
                                                @click="if (row.quantity > 1) { row.quantity -= 1; onTotalsInput(); }"
                                                class="px-2 py-2 text-xs font-bold text-neutral-600 hover:bg-neutral-100 cursor-pointer">−</button>
                                        <input type="number"
                                               :name="`items[${index}][quantity]`"
                                               x-model.number="row.quantity"
                                               @input="onTotalsInput()"
                                               min="1"
                                               required
                                               class="w-full py-2 text-xs text-center border-0 bg-white focus:outline-none">
                                        <button type="button"
                                                @click="row.quantity = (Number(row.quantity) || 1) + 1; onTotalsInput();"
                                                class="px-2 py-2 text-xs font-bold text-neutral-600 hover:bg-neutral-100 cursor-pointer">+</button>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Price (₹)</label>
                                    <input type="number"
                                           step="any"
                                           min="0"
                                           :name="`items[${index}][unit_price]`"
                                           x-model.number="row.unit_price"
                                           @input="onTotalsInput()"
                                           required
                                           class="w-full px-2.5 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Custom Size</label>
                                    <input type="text"
                                           :name="`items[${index}][custom_size]`"
                                           x-model="row.custom_size"
                                           placeholder="Optional"
                                           class="w-full px-2.5 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                </div>

                                <div class="sm:col-span-1 flex justify-end">
                                    <button type="button"
                                            @click="removeRow(index)"
                                            :disabled="rows.length <= 1"
                                            class="px-2.5 py-2 border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-700 hover:text-white text-xs font-bold disabled:opacity-40 cursor-pointer"
                                            title="Remove item">
                                        &times;
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px] text-neutral-500">
                                <span>
                                    GST: <strong x-text="`${row.gst_rate}% (Incl.)`"></strong>
                                    <template x-if="row.stock_quantity !== null">
                                        <span> • Stock: <strong x-text="row.stock_quantity"></strong></span>
                                    </template>
                                </span>
                                <span class="font-bold text-neutral-900">
                                    Line Total: ₹<span x-text="((Number(row.unit_price) || 0) * (Number(row.quantity) || 0)).toFixed(2)"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Product Subtotal Bar -->
                <div class="shrink-0 px-4 sm:px-5 py-2.5 border-t border-neutral-200 bg-neutral-50 flex items-center justify-between text-xs">
                    <span class="font-bold uppercase tracking-wider text-neutral-600">
                        Products Subtotal (<span x-text="rows.length"></span> item(s), Incl. GST)
                    </span>
                    <span class="text-sm font-black text-neutral-900">₹<span x-text="totals.subtotal.toFixed(2)"></span></span>
                </div>
            </div>

            <!-- RIGHT COLUMN: Single Combined Sticky Card (Customer Details + Payment Method) -->
            <div class="lg:col-span-5 flex flex-col min-h-0">
                <div class="bg-white border border-neutral-200 p-4 sm:p-5 flex flex-col gap-4 lg:max-h-full lg:overflow-y-auto">
                    <!-- Billing Mode Switch: Manual Order vs Walk-in Purchase -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Billing Mode</span>
                            <span class="text-[10px] font-semibold text-neutral-600"
                                  x-text="billingMode === 'walk_in' ? 'Immediate Store Purchase' : 'Phone / Message Order'"></span>
                        </div>
                        <input type="hidden" name="billing_mode" :value="billingMode">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                    @click="billingMode = 'walk_in'"
                                    class="py-2 px-3 text-xs font-bold uppercase tracking-wider border transition-colors cursor-pointer"
                                    :class="billingMode === 'walk_in'
                                        ? 'bg-neutral-900 text-white border-neutral-900'
                                        : 'bg-white text-neutral-700 border-neutral-300 hover:bg-neutral-50'">
                                Walk-in Purchase
                            </button>
                            <button type="button"
                                    @click="billingMode = 'manual_order'"
                                    class="py-2 px-3 text-xs font-bold uppercase tracking-wider border transition-colors cursor-pointer"
                                    :class="billingMode === 'manual_order'
                                        ? 'bg-neutral-900 text-white border-neutral-900'
                                        : 'bg-white text-neutral-700 border-neutral-300 hover:bg-neutral-50'">
                                Manual Order
                            </button>
                        </div>
                    </div>

                    <!-- Customer Details Section -->
                    <div class="pt-3 border-t border-neutral-200 space-y-3">
                        <div class="border-b border-neutral-200 pb-2">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Customer Details</h2>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Customer Name *</label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                       placeholder="Full name"
                                       class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Phone Number *</label>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required
                                       placeholder="+91 98XXXXXXXX"
                                       class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Email ID (Optional)</label>
                                <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                                       placeholder="customer@example.com"
                                       class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                            </div>

                            <template x-if="billingMode === 'manual_order'">
                                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div class="sm:col-span-2">
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Delivery Address</label>
                                        <input type="text" name="shipping_address_line1" value="{{ old('shipping_address_line1') }}"
                                               placeholder="House / Street / Area"
                                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">City</label>
                                        <input type="text" name="shipping_city" value="{{ old('shipping_city') }}"
                                               placeholder="City"
                                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">PIN Code</label>
                                        <input type="text" name="shipping_pin_code" value="{{ old('shipping_pin_code') }}"
                                               placeholder="682019"
                                               maxlength="10"
                                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Payment Method Section (Inside the same combined card) -->
                    <div class="pt-3 border-t border-neutral-200 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Payment Method</h2>
                            <label class="inline-flex items-center gap-2 cursor-pointer px-2.5 py-1 border border-neutral-300 bg-neutral-50 hover:bg-neutral-100 text-[11px] font-bold text-neutral-800">
                                <input type="checkbox"
                                       name="is_split_payment"
                                       value="1"
                                       x-model="isSplitPayment"
                                       @change="onSplitToggle()"
                                       class="border-neutral-300 text-neutral-900 focus:ring-neutral-900">
                                <span>Split Payment</span>
                            </label>
                        </div>

                        <!-- Single Payment Mode: Segmented Selector -->
                        <div x-show="!isSplitPayment" class="space-y-3">
                            <input type="hidden" name="payment_method" :value="paymentMethod" :disabled="isSplitPayment">
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="method in ['UPI', 'Cash', 'Card']" :key="method">
                                    <button type="button"
                                            @click="paymentMethod = method"
                                            class="py-2 px-3 text-xs font-bold uppercase tracking-wider border transition-colors cursor-pointer"
                                            :class="paymentMethod === method
                                                ? 'bg-neutral-900 text-white border-neutral-900'
                                                : 'bg-white text-neutral-700 border-neutral-300 hover:bg-neutral-50'"
                                            x-text="method">
                                    </button>
                                </template>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Reference / UTR (Optional)</label>
                                <input type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                                       placeholder="Transaction ID / Last 4 digits"
                                       class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                            </div>
                        </div>

                        <!-- Split Payment Mode (Enhanced POS Visibility & Quick Fill) -->
                        <div x-show="isSplitPayment" x-cloak class="space-y-3">
                            <div class="space-y-2">
                                <template x-for="(split, sIdx) in splitPayments" :key="split.uid">
                                    <div class="p-2.5 border border-neutral-200 bg-neutral-50/60 flex items-center gap-2">
                                        <div class="w-28 shrink-0">
                                            <select :name="`split_payments[${sIdx}][method]`"
                                                    x-model="split.method"
                                                    :disabled="!isSplitPayment"
                                                    class="w-full px-2.5 py-2 text-xs font-semibold border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                                <option value="UPI" :disabled="isMethodUsedElsewhere(split.uid, 'UPI')">UPI</option>
                                                <option value="Cash" :disabled="isMethodUsedElsewhere(split.uid, 'Cash')">Cash</option>
                                                <option value="Card" :disabled="isMethodUsedElsewhere(split.uid, 'Card')">Card</option>
                                            </select>
                                        </div>

                                        <div class="flex-1 relative">
                                            <span class="absolute left-2.5 top-2 text-xs font-bold text-neutral-400">₹</span>
                                            <input type="number"
                                                   step="any"
                                                   min="0"
                                                   :name="`split_payments[${sIdx}][amount]`"
                                                   x-model="split.amount"
                                                   :disabled="!isSplitPayment"
                                                   placeholder="0.00"
                                                   class="w-full pl-6 pr-2.5 py-2 text-xs font-bold border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                        </div>

                                        <button type="button"
                                                x-show="splitRemainingBalance > 0.01"
                                                @click="fillRemainingInto(split)"
                                                title="Fill remaining balance into this method"
                                                class="px-2 py-2 border border-neutral-300 bg-white hover:bg-neutral-900 hover:text-white text-[10px] font-bold uppercase tracking-wider text-neutral-700 shrink-0 cursor-pointer">
                                            Fill
                                        </button>

                                        <button type="button"
                                                @click="removeSplitRow(sIdx)"
                                                :disabled="splitPayments.length <= 2"
                                                class="px-2.5 py-2 border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-700 hover:text-white text-xs font-bold disabled:opacity-40 cursor-pointer shrink-0">
                                            &times;
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="button"
                                        @click="addSplitRow()"
                                        x-show="splitPayments.length < 3"
                                        class="text-[11px] font-bold uppercase tracking-wider text-neutral-900 underline cursor-pointer">
                                    + Add Payment Method
                                </button>
                                <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider border ml-auto"
                                      :class="splitStatusBadgeClass"
                                      x-text="splitStatusText"></span>
                            </div>

                            <!-- Clear Split Payment Breakdown Box with Progress Bar -->
                            <div class="p-3 bg-neutral-50 border border-neutral-200 space-y-2 text-xs">
                                <div class="w-full h-1.5 bg-neutral-200 overflow-hidden">
                                    <div class="h-full transition-all duration-200"
                                         :class="splitRemainingBalance < -0.01 ? 'bg-rose-600' : (Math.abs(splitRemainingBalance) <= 0.01 ? 'bg-emerald-600' : 'bg-amber-500')"
                                         :style="`width: ${splitProgressPercent}%`"></div>
                                </div>

                                <div class="flex justify-between text-neutral-600">
                                    <span>Total Payable</span>
                                    <span class="font-bold text-neutral-900">₹<span x-text="totals.grandTotal.toFixed(2)"></span></span>
                                </div>
                                <template x-for="split in splitPayments" :key="'sum-' + split.uid">
                                    <div class="flex justify-between text-neutral-600">
                                        <span x-text="split.method"></span>
                                        <span class="font-semibold text-neutral-800">₹<span x-text="(Number(split.amount) || 0).toFixed(2)"></span></span>
                                    </div>
                                </template>
                                <div class="pt-1.5 border-t border-neutral-200 flex justify-between font-bold">
                                    <span>Remaining Balance</span>
                                    <span :class="Math.abs(splitRemainingBalance) <= 0.01 ? 'text-emerald-700' : (splitRemainingBalance > 0 ? 'text-amber-700' : 'text-rose-700')">
                                        ₹<span x-text="splitRemainingBalance.toFixed(2)"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTTOM STICKY SECTION: Subtotal, Auto-Discount, Editable Final Payable Amount & Generate Bill -->
        <div class="shrink-0 bg-white border border-neutral-300 px-4 py-3 shadow-sm sticky bottom-0 z-20">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <!-- Left: Subtotal, Discount, Taxable & GST Breakup -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-5 text-xs flex-1">
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500">Subtotal (Incl. GST)</span>
                        <span class="text-sm font-bold text-neutral-900">₹<span x-text="totals.subtotal.toFixed(2)"></span></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500">Discount Amount</span>
                        <span class="text-sm font-bold text-emerald-700">-₹<span x-text="totals.discount.toFixed(2)"></span></span>
                        <input type="hidden" name="discount_amount" :value="totals.discount.toFixed(2)">
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500">Taxable Value</span>
                        <span class="text-sm font-semibold text-neutral-900">₹<span x-text="totals.taxable.toFixed(2)"></span></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500">CGST (2.5%) + SGST (2.5%)</span>
                        <span class="text-xs font-semibold text-neutral-800">
                            ₹<span x-text="totals.cgst.toFixed(2)"></span> + ₹<span x-text="totals.sgst.toFixed(2)"></span>
                        </span>
                    </div>
                </div>

                <!-- Right: Editable Final Payable Amount & Generate Bill Button -->
                <div class="flex flex-wrap sm:flex-nowrap items-center justify-between lg:justify-end gap-4 pt-2.5 lg:pt-0 border-t lg:border-t-0 border-neutral-200 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <label for="billing-final-payable-input" class="text-[10px] font-bold uppercase tracking-wider text-neutral-600 whitespace-nowrap">
                            Final Payable (₹)
                        </label>
                        <input id="billing-final-payable-input"
                               type="number"
                               step="any"
                               min="0"
                               :max="totals.subtotal"
                               name="edited_final_amount"
                               x-model="editedFinalInput"
                               @input="onFinalAmountEdit()"
                               @blur="normalizeFinalAmountInput()"
                               class="w-32 px-3 py-2 text-base font-black text-neutral-900 border border-neutral-400 bg-white focus:outline-none focus:border-neutral-900 text-right">
                    </div>

                    <button type="submit"
                            class="px-5 py-2.5 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer whitespace-nowrap">
                        Generate Bill
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function manualBillingForm() {
        const catalog = @json($products);

        return {
            products: catalog,
            billingMode: @json(old('billing_mode', 'walk_in')),
            uidCounter: 1,
            splitUidCounter: 2,
            formError: '',
            finalAmountError: '',
            isManualFinalOverride: false,
            editedFinalInput: '0.00',
            isSplitPayment: false,
            paymentMethod: @json(old('payment_method', 'UPI')),
            splitPayments: [
                { uid: 1, method: 'UPI', amount: '' },
                { uid: 2, method: 'Cash', amount: '' },
            ],
            rows: [
                {
                    uid: 1,
                    product_id: '',
                    quantity: 1,
                    unit_price: 0,
                    gst_rate: 5,
                    custom_size: '',
                    stock_quantity: null,
                    dropdownOpen: false,
                    searchQuery: '',
                }
            ],

            addRow() {
                this.uidCounter += 1;
                this.rows.push({
                    uid: this.uidCounter,
                    product_id: '',
                    quantity: 1,
                    unit_price: 0,
                    gst_rate: 5,
                    custom_size: '',
                    stock_quantity: null,
                    dropdownOpen: false,
                    searchQuery: '',
                });
            },

            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                    this.onTotalsInput();
                }
            },

            toggleProductDropdown(row) {
                const nextState = !row.dropdownOpen;
                this.rows.forEach((r) => {
                    r.dropdownOpen = false;
                });
                row.dropdownOpen = nextState;
                if (nextState) {
                    this.$nextTick(() => {
                        const input = document.getElementById(`billing-product-search-${row.uid}`);
                        if (input) {
                            input.focus();
                        }
                    });
                }
            },

            filteredProducts(row) {
                const query = String(row.searchQuery || '').trim().toLowerCase();
                if (!query) {
                    return this.products.slice(0, 60);
                }
                return this.products
                    .filter((p) => {
                        const name = String(p.name || '').toLowerCase();
                        const sku = String(p.sku || '').toLowerCase();
                        return name.includes(query) || sku.includes(query);
                    })
                    .slice(0, 60);
            },

            selectedProductLabel(row) {
                const selected = this.products.find((p) => String(p.id) === String(row.product_id));
                if (!selected) {
                    return '-- Select Product --';
                }
                return `${selected.name} (₹${Number(selected.price).toFixed(2)} • Stock: ${selected.stock_quantity})`;
            },

            selectProduct(row, prod) {
                row.product_id = String(prod.id);
                row.dropdownOpen = false;
                row.searchQuery = '';
                this.onProductChange(row);
            },

            selectFirstMatch(row) {
                const matches = this.filteredProducts(row);
                if (matches.length > 0) {
                    this.selectProduct(row, matches[0]);
                }
            },

            onProductChange(row) {
                const selected = this.products.find((p) => String(p.id) === String(row.product_id));
                if (selected) {
                    row.unit_price = Number(selected.price) || 0;
                    row.gst_rate = Number(selected.gst_rate ?? 5);
                    row.stock_quantity = Number(selected.stock_quantity ?? 0);
                } else {
                    row.unit_price = 0;
                    row.gst_rate = 5;
                    row.stock_quantity = null;
                }
                this.onTotalsInput();
            },

            get originalSubtotal() {
                let subtotal = 0;
                this.rows.forEach((r) => {
                    subtotal += (Number(r.unit_price) || 0) * (Number(r.quantity) || 0);
                });
                return Math.round(subtotal * 100) / 100;
            },

            onTotalsInput() {
                const sub = this.originalSubtotal;
                if (!this.isManualFinalOverride) {
                    this.editedFinalInput = sub.toFixed(2);
                    this.finalAmountError = '';
                } else {
                    const current = Number(this.editedFinalInput);
                    if (!Number.isNaN(current) && current > sub) {
                        this.editedFinalInput = sub.toFixed(2);
                        this.finalAmountError = '';
                    }
                }
            },

            onFinalAmountEdit() {
                const sub = this.originalSubtotal;
                if (this.editedFinalInput === '' || this.editedFinalInput === null) {
                    this.isManualFinalOverride = false;
                    this.finalAmountError = '';
                    return;
                }

                this.isManualFinalOverride = true;
                const val = Number(this.editedFinalInput);

                if (Number.isNaN(val) || val < 0) {
                    this.finalAmountError = 'Final payable amount cannot be negative.';
                    return;
                }

                if (val > sub) {
                    this.finalAmountError = `Final payable amount cannot exceed original bill amount (₹${sub.toFixed(2)}).`;
                    this.editedFinalInput = sub.toFixed(2);
                    return;
                }

                this.finalAmountError = '';
            },

            normalizeFinalAmountInput() {
                const sub = this.originalSubtotal;
                let val = Number(this.editedFinalInput);
                if (Number.isNaN(val) || val < 0) {
                    val = 0;
                }
                if (val > sub) {
                    val = sub;
                }
                this.editedFinalInput = val.toFixed(2);
                this.finalAmountError = '';
                if (Math.abs(val - sub) < 0.005) {
                    this.isManualFinalOverride = false;
                }
            },

            get totals() {
                const subtotal = this.originalSubtotal;
                let finalTotal = subtotal;

                if (this.isManualFinalOverride && this.editedFinalInput !== '') {
                    const parsed = Number(this.editedFinalInput);
                    if (!Number.isNaN(parsed)) {
                        finalTotal = Math.min(subtotal, Math.max(0, Math.round(parsed * 100) / 100));
                    }
                }

                const discount = Math.max(0, Math.round((subtotal - finalTotal) * 100) / 100);

                let totalTaxable = 0;
                let totalCgst = 0;
                let totalSgst = 0;
                let remainingDiscount = discount;

                this.rows.forEach((r, idx) => {
                    const lineSub = Math.round((Number(r.unit_price) || 0) * (Number(r.quantity) || 0) * 100) / 100;
                    let lineDiscount = 0;
                    if (idx === this.rows.length - 1) {
                        lineDiscount = Math.min(remainingDiscount, lineSub);
                    } else {
                        lineDiscount = subtotal > 0 ? Math.round(((lineSub / subtotal) * discount) * 100) / 100 : 0;
                        lineDiscount = Math.min(lineDiscount, remainingDiscount, lineSub);
                        remainingDiscount = Math.round((remainingDiscount - lineDiscount) * 100) / 100;
                    }

                    const lineInclusive = Math.max(0, Math.round((lineSub - lineDiscount) * 100) / 100);
                    const gstRate = Number(r.gst_rate ?? 5);
                    const divisor = 1 + (gstRate / 100);
                    const lineTaxable = divisor > 0 ? Math.round((lineInclusive / divisor) * 100) / 100 : lineInclusive;
                    const lineTax = Math.max(0, Math.round((lineInclusive - lineTaxable) * 100) / 100);
                    const cgst = Math.round((lineTax / 2) * 100) / 100;
                    const sgst = Math.round((lineTax - cgst) * 100) / 100;

                    totalTaxable += lineTaxable;
                    totalCgst += cgst;
                    totalSgst += sgst;
                });

                return {
                    subtotal,
                    discount,
                    taxable: Math.round(totalTaxable * 100) / 100,
                    cgst: Math.round(totalCgst * 100) / 100,
                    sgst: Math.round(totalSgst * 100) / 100,
                    tax: Math.round((totalCgst + totalSgst) * 100) / 100,
                    grandTotal: finalTotal,
                };
            },

            onSplitToggle() {
                if (this.isSplitPayment && this.splitPayments.length < 2) {
                    this.splitPayments = [
                        { uid: 1, method: 'UPI', amount: '' },
                        { uid: 2, method: 'Cash', amount: '' },
                    ];
                }
            },

            isMethodUsedElsewhere(uid, method) {
                return this.splitPayments.some((s) => s.uid !== uid && s.method === method);
            },

            fillRemainingInto(split) {
                const rem = this.splitRemainingBalance;
                if (rem > 0) {
                    const current = Math.max(0, Number(split.amount) || 0);
                    split.amount = (Math.round((current + rem) * 100) / 100).toFixed(2);
                }
            },

            addSplitRow() {
                const used = this.splitPayments.map((s) => s.method);
                const available = ['UPI', 'Cash', 'Card'].find((m) => !used.includes(m));
                if (!available) {
                    return;
                }
                this.splitUidCounter += 1;
                this.splitPayments.push({
                    uid: this.splitUidCounter,
                    method: available,
                    amount: '',
                });
            },

            removeSplitRow(index) {
                if (this.splitPayments.length > 2) {
                    this.splitPayments.splice(index, 1);
                }
            },

            get splitTotalPaid() {
                let sum = 0;
                this.splitPayments.forEach((s) => {
                    sum += Math.max(0, Number(s.amount) || 0);
                });
                return Math.round(sum * 100) / 100;
            },

            get splitRemainingBalance() {
                return Math.round((this.totals.grandTotal - this.splitTotalPaid) * 100) / 100;
            },

            get splitProgressPercent() {
                const total = this.totals.grandTotal;
                if (total <= 0) {
                    return this.splitTotalPaid > 0 ? 100 : 0;
                }
                return Math.min(100, Math.max(0, Math.round((this.splitTotalPaid / total) * 100)));
            },

            get splitHasDuplicates() {
                const methods = this.splitPayments.map((s) => s.method);
                return new Set(methods).size !== methods.length;
            },

            get splitMatchesTotal() {
                if (!this.isSplitPayment) {
                    return true;
                }
                return !this.splitHasDuplicates && Math.abs(this.splitRemainingBalance) <= 0.01;
            },

            get splitStatusText() {
                if (this.splitHasDuplicates) {
                    return 'Duplicate Method';
                }
                if (Math.abs(this.splitRemainingBalance) <= 0.01) {
                    return 'Fully Paid';
                }
                if (this.splitRemainingBalance > 0) {
                    return 'Partially Paid';
                }
                return 'Exceeds Total';
            },

            get splitStatusBadgeClass() {
                if (this.splitHasDuplicates || this.splitRemainingBalance < -0.01) {
                    return 'bg-rose-50 text-rose-700 border-rose-300';
                }
                if (Math.abs(this.splitRemainingBalance) <= 0.01) {
                    return 'bg-emerald-50 text-emerald-700 border-emerald-300';
                }
                return 'bg-amber-50 text-amber-800 border-amber-300';
            },

            handleFormSubmit(event) {
                this.formError = '';
                if (this.rows.some((r) => !r.product_id)) {
                    event.preventDefault();
                    this.formError = 'Please select a product for every line item before generating the bill.';
                    return;
                }
                if (this.finalAmountError) {
                    event.preventDefault();
                    return;
                }
                if (this.isSplitPayment) {
                    if (this.splitHasDuplicates) {
                        event.preventDefault();
                        this.formError = 'Duplicate payment methods are not allowed in split payment.';
                        return;
                    }
                    if (this.splitPayments.some((s) => (Number(s.amount) || 0) <= 0)) {
                        event.preventDefault();
                        this.formError = 'Please enter a valid amount greater than ₹0 for each split payment method.';
                        return;
                    }
                    if (!this.splitMatchesTotal) {
                        event.preventDefault();
                        this.formError = `Split payment total (₹${this.splitTotalPaid.toFixed(2)}) must match Final Payable (₹${this.totals.grandTotal.toFixed(2)}). Remaining: ₹${this.splitRemainingBalance.toFixed(2)}.`;
                        return;
                    }
                }
            },
        };
    }
</script>
@endsection
