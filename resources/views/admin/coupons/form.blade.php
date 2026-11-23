@extends('admin.layouts.app')

@section('title', $isEdit ? 'Edit Offer Code' : 'Create Offer Code')
@section('header_title', $isEdit ? "Edit Offer Code: {$coupon->code}" : 'Create New Offer Code')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-none border border-neutral-200 shadow-none p-6 sm:p-8">
        <form action="{{ $isEdit ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" 
              method="POST" 
              class="space-y-6"
              x-data="{ discountType: '{{ old('discount_type', $coupon->discount_type ?? 'percentage') }}' }">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <!-- Code & Discount Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        Offer Code <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="code" 
                           value="{{ old('code', $coupon->code) }}" 
                           placeholder="e.g. WELCOME10" 
                           required 
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 font-mono uppercase font-bold text-sm bg-white">
                    <p class="text-[11px] text-neutral-500 mt-1">Customers enter this code at checkout (case-insensitive).</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        Discount Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="discount_type" 
                            x-model="discountType"
                            required 
                            class="w-full px-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm bg-white">
                        <option value="percentage">Option A: Percentage Discount (%)</option>
                        <option value="fixed">Option B: Fixed Amount Discount (₹)</option>
                    </select>
                </div>
            </div>

            <!-- Value & Max Cap -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        <span x-text="discountType === 'percentage' ? 'Discount Percentage (%)' : 'Discount Amount (₹)'"></span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm font-bold text-neutral-400" x-text="discountType === 'percentage' ? '%' : '₹'"></span>
                        <input type="number" 
                               step="0.01" 
                               name="discount_value" 
                               value="{{ old('discount_value', $coupon->discount_value) }}" 
                               placeholder="e.g. 10 or 200" 
                               required 
                               class="w-full pl-9 pr-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm font-semibold bg-white">
                    </div>
                </div>

                <div x-show="discountType === 'percentage'">
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        Maximum Discount Cap (₹)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm font-bold text-neutral-400">₹</span>
                        <input type="number" 
                               step="0.01" 
                               name="max_discount_amount" 
                               value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" 
                               placeholder="e.g. 500 (Optional)" 
                               class="w-full pl-9 pr-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm bg-white">
                    </div>
                    <p class="text-[11px] text-neutral-500 mt-1">Limits max discount amount for percentage coupons.</p>
                </div>
            </div>

            <!-- Conditions: Min Order & Usage Limit -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        Minimum Order Amount (₹)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-2.5 text-sm font-bold text-neutral-400">₹</span>
                        <input type="number" 
                               step="0.01" 
                               name="min_order_amount" 
                               value="{{ old('min_order_amount', $coupon->min_order_amount) }}" 
                               placeholder="e.g. 999 (Optional)" 
                               class="w-full pl-9 pr-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        Usage Limit (Total times)
                    </label>
                    <input type="number" 
                           name="usage_limit" 
                           value="{{ old('usage_limit', $coupon->usage_limit) }}" 
                           placeholder="e.g. 100 (Leave empty for unlimited)" 
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm bg-white">
                </div>
            </div>

            <!-- Validity Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        Start Date & Time (Optional)
                    </label>
                    <input type="datetime-local" 
                           name="starts_at" 
                           value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}" 
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm bg-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                        Expiry Date & Time (Optional)
                    </label>
                    <input type="datetime-local" 
                           name="expires_at" 
                           value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}" 
                           class="w-full px-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm bg-white">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-neutral-700 uppercase tracking-wider mb-2">
                    Description / Offer Subtitle
                </label>
                <input type="text" 
                       name="description" 
                       value="{{ old('description', $coupon->description) }}" 
                       placeholder="e.g. 10% OFF on all luxury bedsheets above ₹999" 
                       class="w-full px-4 py-2.5 rounded-none border border-neutral-300 focus:outline-none focus:border-neutral-900 text-sm bg-white">
            </div>

            <!-- Active Status Toggle -->
            <div class="flex items-center gap-3 p-4 bg-neutral-50 rounded-none border border-neutral-200">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1" 
                       {{ old('is_active', $coupon->exists ? $coupon->is_active : true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded-none text-neutral-900 focus:ring-neutral-900 border-neutral-300">
                <label for="is_active" class="text-sm font-medium text-neutral-800 cursor-pointer">
                    Enable this offer code immediately (Active)
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                <a href="{{ route('admin.coupons.index') }}" 
                   class="px-5 py-2.5 rounded-none border border-neutral-300 text-neutral-700 text-xs font-semibold uppercase tracking-wider hover:bg-neutral-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-none bg-neutral-900 hover:bg-black text-white text-xs font-semibold uppercase tracking-wider transition-all shadow-none">
                    {{ $isEdit ? 'Save Changes' : 'Create Offer Code' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
