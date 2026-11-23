@extends('admin.layouts.app')

@section('title', 'Payment Gateway & GST Settings')
@section('header_title', 'Payment Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <form action="{{ route('admin.settings.payment.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Razorpay Payment Gateway Credentials -->
        <div class="bg-white rounded-none border border-neutral-200 p-6 sm:p-7">
            <div class="flex items-center justify-between pb-4 mb-6 border-b border-neutral-200">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-900">
                        Razorpay Payment Gateway
                    </h3>
                    <p class="text-[11px] text-neutral-500 mt-0.5">API credentials for credit cards, debit cards, UPI, and net banking.</p>
                </div>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-none uppercase tracking-wider border {{ ($settings['razorpay_status'] ?? 'enabled') === 'enabled' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : 'bg-neutral-100 text-neutral-700 border-neutral-300' }}">
                    {{ strtoupper($settings['razorpay_status'] ?? 'enabled') }}
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Integration Status <span class="text-rose-600">*</span>
                    </label>
                    <select name="razorpay_status" class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                        <option value="enabled" {{ ($settings['razorpay_status'] ?? 'enabled') === 'enabled' ? 'selected' : '' }}>Enabled (Accept Payments Online)</option>
                        <option value="disabled" {{ ($settings['razorpay_status'] ?? '') === 'disabled' ? 'selected' : '' }}>Disabled (Temporarily Turn Off)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Gateway Environment Mode <span class="text-rose-600">*</span>
                    </label>
                    <select name="razorpay_mode" class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                        <option value="test" {{ ($settings['razorpay_mode'] ?? 'test') === 'test' ? 'selected' : '' }}>Test Mode (Sandbox / Testing)</option>
                        <option value="live" {{ ($settings['razorpay_mode'] ?? '') === 'live' ? 'selected' : '' }}>Live Mode (Production Real Money)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Razorpay Key ID
                    </label>
                    <input type="text" 
                           name="razorpay_key_id" 
                           value="{{ old('razorpay_key_id', $settings['razorpay_key_id'] ?? '') }}" 
                           placeholder="rzp_test_..." 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 font-mono text-xs bg-white focus:outline-none focus:border-neutral-900">
                    <p class="text-[10px] text-neutral-500 mt-1">Available in your Razorpay Dashboard &rarr; Settings &rarr; API Keys.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Razorpay Key Secret
                    </label>
                    <input type="password" 
                           name="razorpay_key_secret" 
                           value="{{ old('razorpay_key_secret', $settings['razorpay_key_secret'] ?? '') }}" 
                           placeholder="••••••••••••••••••••••••" 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 font-mono text-xs bg-white focus:outline-none focus:border-neutral-900">
                    <p class="text-[10px] text-neutral-500 mt-1">Stored securely on the backend. Never exposed to customer browser.</p>
                </div>
            </div>
        </div>

        <!-- 2. Gateway Fee & Surcharge Policy -->
        <div class="bg-white rounded-none border border-neutral-200 p-6 sm:p-7">
            <div class="pb-4 mb-6 border-b border-neutral-200">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Gateway / Convenience Fee Handling</h3>
                <p class="text-[11px] text-neutral-500 mt-0.5">Control whether transaction gateway charges are absorbed by business or added to customer bill.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Fee Absorption Model <span class="text-rose-600">*</span>
                    </label>
                    <select name="gateway_fee_mode" class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                        <option value="absorbed" {{ ($settings['gateway_fee_mode'] ?? 'absorbed') === 'absorbed' ? 'selected' : '' }}>
                            Absorbed by Business (Standard & compliant)
                        </option>
                        <option value="customer_charged" {{ ($settings['gateway_fee_mode'] ?? '') === 'customer_charged' ? 'selected' : '' }}>
                            Charged to Customer (Convenience Fee)
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Customer Charge Percentage (%)
                    </label>
                    <input type="number" 
                           step="0.01" 
                           name="gateway_fee_percent" 
                           value="{{ old('gateway_fee_percent', $settings['gateway_fee_percent'] ?? '0') }}" 
                           placeholder="e.g. 2.0" 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                    <p class="text-[10px] text-neutral-500 mt-1">Only applied when Customer Charge mode is enabled.</p>
                </div>
            </div>
        </div>

        <!-- 3. Business Seller Details (Printed on GST Tax Invoices) -->
        <div class="bg-white rounded-none border border-neutral-200 p-6 sm:p-7">
            <div class="pb-4 mb-6 border-b border-neutral-200">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-900">Seller Information (GST Tax Invoice)</h3>
                <p class="text-[11px] text-neutral-500 mt-0.5">Statutory registered seller details printed on all customer GST tax invoices.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Registered Business Name <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           name="seller_name" 
                           value="{{ old('seller_name', $settings['seller_name'] ?? 'Menahem Home & Living') }}" 
                           required 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900 font-bold">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        GSTIN (15-digit) <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           name="seller_gstin" 
                           value="{{ old('seller_gstin', $settings['seller_gstin'] ?? '27AABCM1234F1Z8') }}" 
                           required 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 font-mono text-xs uppercase font-bold bg-white focus:outline-none focus:border-neutral-900">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Registered Business Address <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="seller_address" 
                              rows="2" 
                              required 
                              class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">{{ old('seller_address', $settings['seller_address'] ?? '42, Textile Market Road, Kalbadevi, Mumbai, Maharashtra 400002') }}</textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Seller State <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           name="seller_state" 
                           value="{{ old('seller_state', $settings['seller_state'] ?? 'Maharashtra') }}" 
                           required 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                    <p class="text-[10px] text-neutral-500 mt-1">Used to determine Intra-state (CGST+SGST) vs Inter-state (IGST).</p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        State Code (2-digit) <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           name="seller_state_code" 
                           value="{{ old('seller_state_code', $settings['seller_state_code'] ?? '27') }}" 
                           required 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 font-mono text-xs bg-white focus:outline-none focus:border-neutral-900">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Contact Phone <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           name="seller_phone" 
                           value="{{ old('seller_phone', $settings['seller_phone'] ?? '+91 98765 43210') }}" 
                           required 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Contact Email <span class="text-rose-600">*</span>
                    </label>
                    <input type="email" 
                           name="seller_email" 
                           value="{{ old('seller_email', $settings['seller_email'] ?? 'orders@menahem.com') }}" 
                           required 
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
            <button type="submit" 
                    class="px-6 py-2.5 rounded-none bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider transition-all border border-neutral-900 cursor-pointer">
                Save All Payment & GST Settings
            </button>
        </div>
    </form>
</div>
@endsection
