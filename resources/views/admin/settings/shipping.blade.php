@extends('admin.layouts.app')

@section('title', 'Shipping Configuration')
@section('header_title', 'Shipping Configuration')

@section('content')
<div class="max-w-4xl mx-auto space-y-6"
     x-data="{
        originPin: '{{ old('shipping_origin_pincode', $settings['origin_pincode'] ?? '400002') }}',
        originCity: '{{ old('shipping_origin_city', $settings['origin_city'] ?? 'Mumbai') }}',
        originState: '{{ old('shipping_origin_state', $settings['origin_state'] ?? 'Maharashtra') }}',
        freeKm: {{ (float) old('shipping_free_distance_km', $settings['free_distance_km'] ?? 5) }},
        ratePerKm: {{ (float) old('shipping_charge_per_km', $settings['charge_per_km'] ?? 10) }},
        pinLoading: false,
        pinMessage: '',
        pinError: '',
        lookupOriginPin() {
            const clean = String(this.originPin || '').replace(/\D/g, '').slice(0, 6);
            this.originPin = clean;
            this.pinMessage = '';
            this.pinError = '';
            if (clean.length !== 6) {
                return;
            }
            this.pinLoading = true;
            fetch('{{ route('checkout.pincode-lookup') }}?pincode=' + encodeURIComponent(clean), {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                this.pinLoading = false;
                if (ok && data.valid) {
                    if (data.city) this.originCity = data.city;
                    if (data.state) this.originState = data.state;
                    this.pinMessage = [data.post_office, data.district, data.state].filter(Boolean).join(', ');
                } else {
                    this.pinError = data.message || 'PIN code could not be verified.';
                }
            })
            .catch(() => {
                this.pinLoading = false;
                this.pinError = 'Unable to verify PIN code right now.';
            });
        }
     }">

    <form action="{{ route('admin.settings.shipping.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Business / Pickup Location -->
        <div class="bg-white rounded-none border border-neutral-200 p-6 sm:p-7">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 mb-6 border-b border-neutral-200">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-900">
                        Business / Pickup Location
                    </h3>
                    <p class="text-[11px] text-neutral-500 mt-0.5">
                        Starting dispatch location used as the origin for calculating customer delivery distances.
                    </p>
                </div>
                @if(!empty($settings['origin_lat']) && !empty($settings['origin_lng']))
                    <span class="px-2.5 py-1 text-[10px] font-mono font-bold uppercase tracking-wider border bg-emerald-50 text-emerald-900 border-emerald-300 shrink-0">
                        Origin Locked: {{ number_format((float) $settings['origin_lat'], 4) }}, {{ number_format((float) $settings['origin_lng'], 4) }}
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Business PIN Code <span class="text-rose-600">*</span>
                    </label>
                    <div class="relative">
                        <input type="text"
                               name="shipping_origin_pincode"
                               x-model="originPin"
                               @input.debounce.350ms="lookupOriginPin()"
                               maxlength="6"
                               inputmode="numeric"
                               required
                               placeholder="400002"
                               class="w-full px-3 py-2 rounded-none border border-neutral-300 font-mono text-xs bg-white focus:outline-none focus:border-neutral-900">
                        <span x-show="pinLoading" x-cloak class="absolute right-2.5 top-2 text-[10px] font-semibold text-neutral-500">Checking...</span>
                    </div>
                    <p x-show="pinMessage" x-cloak x-text="pinMessage" class="text-[10px] text-emerald-700 font-semibold mt-1"></p>
                    <p x-show="pinError" x-cloak x-text="pinError" class="text-[10px] text-rose-600 font-semibold mt-1"></p>
                    <p x-show="!pinMessage && !pinError" class="text-[10px] text-neutral-500 mt-1">6-digit Indian PIN code of dispatch hub.</p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        City <span class="text-rose-600">*</span>
                    </label>
                    <input type="text"
                           name="shipping_origin_city"
                           x-model="originCity"
                           required
                           placeholder="Mumbai"
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        State <span class="text-rose-600">*</span>
                    </label>
                    <select name="shipping_origin_state"
                            x-model="originState"
                            required
                            class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">
                        @foreach($states as $stateName => $stateCode)
                            <option value="{{ $stateName }}" {{ old('shipping_origin_state', $settings['origin_state'] ?? 'Maharashtra') === $stateName ? 'selected' : '' }}>
                                {{ $stateName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Business Pickup Address <span class="text-rose-600">*</span>
                    </label>
                    <textarea name="shipping_origin_address"
                              rows="2"
                              required
                              placeholder="42, Textile Market Road, Kalbadevi"
                              class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs bg-white focus:outline-none focus:border-neutral-900">{{ old('shipping_origin_address', $settings['origin_address'] ?? '') }}</textarea>
                    <p class="text-[10px] text-neutral-500 mt-1">Used for address-level geocoding and distance calculation.</p>
                </div>
            </div>
        </div>

        <!-- 2. Distance & Per-KM Rate Configuration -->
        <div class="bg-white rounded-none border border-neutral-200 p-6 sm:p-7">
            <div class="pb-4 mb-6 border-b border-neutral-200">
                <h3 class="text-xs font-bold uppercase tracking-wider text-neutral-900">
                    Distance Limits &amp; Shipping Rates
                </h3>
                <p class="text-[11px] text-neutral-500 mt-0.5">
                    Configure the free delivery radius and per-kilometer fee charged beyond that distance.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Free Delivery Distance (KM) <span class="text-rose-600">*</span>
                    </label>
                    <input type="number"
                           step="0.1"
                           min="0"
                           name="shipping_free_distance_km"
                           x-model.number="freeKm"
                           required
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs font-bold bg-white focus:outline-none focus:border-neutral-900">
                    <p class="text-[10px] text-neutral-500 mt-1">Deliveries within this distance from the pickup location are free (₹0).</p>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-neutral-700 uppercase tracking-wider mb-1.5">
                        Shipping Charge Per KM (₹) <span class="text-rose-600">*</span>
                    </label>
                    <input type="number"
                           step="0.5"
                           min="0"
                           name="shipping_charge_per_km"
                           x-model.number="ratePerKm"
                           required
                           class="w-full px-3 py-2 rounded-none border border-neutral-300 text-xs font-bold bg-white focus:outline-none focus:border-neutral-900">
                    <p class="text-[10px] text-neutral-500 mt-1">Rate charged per kilometer beyond the free delivery distance.</p>
                </div>
            </div>

            <!-- Live Calculation Preview -->
            <div class="mt-6 p-4 bg-neutral-50 border border-neutral-200 text-xs space-y-1.5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-neutral-500">Live Example Calculation (12 KM Delivery)</p>
                <div class="flex flex-wrap items-center justify-between gap-2 text-neutral-700">
                    <span>
                        Free up to <strong class="text-neutral-900" x-text="(Number(freeKm) || 0) + ' KM'"></strong>
                        &bull; Billable extra distance:
                        <strong class="text-neutral-900" x-text="Math.max(0, 12 - (Number(freeKm) || 0)).toFixed(2) + ' KM'"></strong>
                        &times; ₹<span x-text="(Number(ratePerKm) || 0).toFixed(2)"></span>/KM
                    </span>
                    <span class="font-bold text-neutral-900 text-sm">
                        Shipping: ₹<span x-text="(Math.max(0, 12 - (Number(freeKm) || 0)) * (Number(ratePerKm) || 0)).toFixed(2)"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end">
            <button type="submit"
                    class="px-6 py-2.5 rounded-none bg-neutral-900 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider transition-all border border-neutral-900 cursor-pointer">
                Save Shipping Configuration
            </button>
        </div>
    </form>
</div>
@endsection
