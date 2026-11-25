@extends('admin.layouts.app')

@section('title', 'Manual Billing')
@section('header_title', 'Manual Billing')

@section('header_actions')
    <a href="{{ route('admin.orders.index') }}"
       class="px-3.5 py-2 border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
        &larr; Back to Orders
    </a>
@endsection

@section('content')
<div x-data="manualBillingForm()" class="space-y-6">
    <form action="{{ route('admin.billing.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <!-- Left 2 Columns: Product Line Items & Customer Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Products Selection -->
            <div class="bg-white border border-neutral-200 p-5 sm:p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-neutral-200 pb-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Select Products</h2>
                        <p class="text-[11px] text-neutral-500 mt-0.5">Choose catalog items, quantities, and optional custom sizes</p>
                    </div>
                    <button type="button"
                            @click="addRow()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                        <span>+ Add Item</span>
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(row, index) in rows" :key="row.uid">
                        <div class="p-3.5 border border-neutral-200 bg-neutral-50/50 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                <div class="sm:col-span-5">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Product</label>
                                    <select :name="`items[${index}][product_id]`"
                                            x-model="row.product_id"
                                            @change="onProductChange(row)"
                                            required
                                            class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                        <option value="">-- Select Product --</option>
                                        <template x-for="prod in products" :key="prod.id">
                                            <option :value="String(prod.id)"
                                                    x-text="`${prod.name} (₹${Number(prod.price).toFixed(2)} • Stock: ${prod.stock_quantity})`"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Qty</label>
                                    <input type="number"
                                           :name="`items[${index}][quantity]`"
                                           x-model.number="row.quantity"
                                           min="1"
                                           required
                                           class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Unit Price (₹)</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           :name="`items[${index}][unit_price]`"
                                           x-model.number="row.unit_price"
                                           required
                                           class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-neutral-500 mb-1">Custom Size</label>
                                    <input type="text"
                                           :name="`items[${index}][custom_size]`"
                                           x-model="row.custom_size"
                                           placeholder="Optional"
                                           class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
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
                                    GST Rate: <strong x-text="`${row.gst_rate}%`"></strong>
                                    <template x-if="row.stock_quantity !== null">
                                        <span> • Available Stock: <strong x-text="row.stock_quantity"></strong></span>
                                    </template>
                                </span>
                                <span class="font-bold text-neutral-900">
                                    Line Total: ₹<span x-text="((Number(row.unit_price) || 0) * (Number(row.quantity) || 0)).toFixed(2)"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Customer & Billing Details -->
            <div class="bg-white border border-neutral-200 p-5 sm:p-6 space-y-4">
                <div class="border-b border-neutral-200 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Customer &amp; Billing Details</h2>
                    <p class="text-[11px] text-neutral-500 mt-0.5">Customer details printed on the GST tax invoice</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                               placeholder="Full name"
                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Phone Number *</label>
                        <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" required
                               placeholder="+91 98XXXXXXXX"
                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Email Address</label>
                        <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                               placeholder="customer@example.com (optional)"
                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Customer GSTIN</label>
                        <input type="text" name="customer_gstin" value="{{ old('customer_gstin') }}"
                               placeholder="Optional 15-char GSTIN"
                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900 uppercase">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">State *</label>
                        <select name="shipping_state" x-model="customerState" required
                                class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                            @foreach($states as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">City</label>
                        <input type="text" name="shipping_city" value="{{ old('shipping_city') }}"
                               placeholder="City"
                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Address</label>
                        <input type="text" name="shipping_address_line1" value="{{ old('shipping_address_line1') }}"
                               placeholder="Street address (optional for counter / walk-in bill)"
                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Payment & Live Bill Summary -->
        <div class="space-y-6">
            <div class="bg-white border border-neutral-200 p-5 sm:p-6 space-y-4">
                <div class="border-b border-neutral-200 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Payment &amp; Adjustments</h2>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Payment Method *</label>
                        <select name="payment_method" required
                                class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                            <option value="UPI" {{ old('payment_method') === 'UPI' ? 'selected' : '' }}>UPI</option>
                            <option value="Cash" {{ old('payment_method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Card" {{ old('payment_method') === 'Card' ? 'selected' : '' }}>Card</option>
                            <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="Razorpay" {{ old('payment_method') === 'Razorpay' ? 'selected' : '' }}>Razorpay</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Payment Reference / UTR</label>
                        <input type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                               placeholder="Optional transaction ID"
                               class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Discount (₹)</label>
                            <input type="number" step="0.01" min="0" name="discount_amount" x-model.number="discount"
                                   class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Shipping (₹)</label>
                            <input type="number" step="0.01" min="0" name="shipping_fee" x-model.number="shippingFee"
                                   class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-neutral-600 mb-1">Bill Notes</label>
                        <textarea name="notes" rows="2" placeholder="Internal notes or remarks..."
                                  class="w-full px-3 py-2 text-xs border border-neutral-300 bg-white focus:outline-none focus:border-neutral-900">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Live Bill Totals -->
            <div class="bg-white border border-neutral-200 p-5 sm:p-6 space-y-4">
                <div class="border-b border-neutral-200 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Bill Summary</h2>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-neutral-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-neutral-900">₹<span x-text="totals.subtotal.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-neutral-600" x-show="totals.discount > 0">
                        <span>Discount</span>
                        <span class="font-semibold text-emerald-700">-₹<span x-text="totals.discount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-neutral-600">
                        <span>Taxable Value</span>
                        <span class="font-semibold text-neutral-900">₹<span x-text="totals.taxable.toFixed(2)"></span></span>
                    </div>
                    <template x-if="isIntraState">
                        <div class="space-y-1">
                            <div class="flex justify-between text-neutral-600">
                                <span>CGST</span>
                                <span>₹<span x-text="(totals.tax / 2).toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between text-neutral-600">
                                <span>SGST</span>
                                <span>₹<span x-text="(totals.tax / 2).toFixed(2)"></span></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!isIntraState">
                        <div class="flex justify-between text-neutral-600">
                            <span>IGST</span>
                            <span>₹<span x-text="totals.tax.toFixed(2)"></span></span>
                        </div>
                    </template>
                    <div class="flex justify-between text-neutral-600" x-show="totals.shipping > 0">
                        <span>Shipping</span>
                        <span>₹<span x-text="totals.shipping.toFixed(2)"></span></span>
                    </div>

                    <div class="pt-3 border-t border-neutral-200 flex justify-between items-center text-sm font-black text-neutral-900">
                        <span>Grand Total</span>
                        <span>₹<span x-text="totals.grandTotal.toFixed(2)"></span></span>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 bg-neutral-900 hover:bg-black text-white text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                    Create Bill &amp; Generate Invoice
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function manualBillingForm() {
        const catalog = @json($products);
        const sellerState = @json($sellerState);

        return {
            products: catalog,
            sellerState: sellerState,
            customerState: @json(old('shipping_state', $sellerState)),
            discount: Number(@json(old('discount_amount', 0))) || 0,
            shippingFee: Number(@json(old('shipping_fee', 0))) || 0,
            uidCounter: 1,
            rows: [
                {
                    uid: 1,
                    product_id: '',
                    quantity: 1,
                    unit_price: 0,
                    gst_rate: 12,
                    custom_size: '',
                    stock_quantity: null,
                }
            ],

            addRow() {
                this.uidCounter += 1;
                this.rows.push({
                    uid: this.uidCounter,
                    product_id: '',
                    quantity: 1,
                    unit_price: 0,
                    gst_rate: 12,
                    custom_size: '',
                    stock_quantity: null,
                });
            },

            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },

            onProductChange(row) {
                const selected = this.products.find((p) => String(p.id) === String(row.product_id));
                if (selected) {
                    row.unit_price = Number(selected.price) || 0;
                    row.gst_rate = Number(selected.gst_rate) || 12;
                    row.stock_quantity = Number(selected.stock_quantity ?? 0);
                } else {
                    row.unit_price = 0;
                    row.gst_rate = 12;
                    row.stock_quantity = null;
                }
            },

            get isIntraState() {
                return String(this.customerState || '').trim().toLowerCase() === String(this.sellerState || '').trim().toLowerCase();
            },

            get totals() {
                let subtotal = 0;
                this.rows.forEach((r) => {
                    subtotal += (Number(r.unit_price) || 0) * (Number(r.quantity) || 0);
                });

                const discount = Math.min(Math.max(0, Number(this.discount) || 0), subtotal);
                const taxable = Math.max(0, subtotal - discount);
                const shipping = Math.max(0, Number(this.shippingFee) || 0);

                let totalTax = 0;
                this.rows.forEach((r) => {
                    const lineSub = (Number(r.unit_price) || 0) * (Number(r.quantity) || 0);
                    const lineDiscount = subtotal > 0 ? (lineSub / subtotal) * discount : 0;
                    const lineTaxable = Math.max(0, lineSub - lineDiscount);
                    const gstRate = Number(r.gst_rate) || 0;
                    totalTax += lineTaxable * (gstRate / 100);
                });

                const grandTotal = taxable + totalTax + shipping;

                return {
                    subtotal,
                    discount,
                    taxable,
                    tax: totalTax,
                    shipping,
                    grandTotal,
                };
            },
        };
    }
</script>
@endsection
