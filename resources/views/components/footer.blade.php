@props([
    'content' => [],
])

@php
    $footer = $content['FOOTER'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<footer class="bg-[#353819] text-white pt-14 pb-10 border-t border-black/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Main Footer Columns Grid in Dark Khaki & Camel -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 pb-12 border-b border-white/10">
            
            <!-- Brand & Socials Column -->
            <div class="lg:col-span-3 flex flex-col items-start">
                <a href="/" class="flex items-center gap-3 group mb-4">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Menahem"
                        class="w-10 h-10 object-contain transition-transform duration-300 group-hover:scale-105 drop-shadow-sm"
                    />
                    <div class="flex flex-col">
                        <span class="text-xl font-bold tracking-tight text-white leading-none">
                            Menahem
                        </span>
                        <span class="text-[9px] uppercase tracking-wider text-[#AC9661] font-semibold mt-1">
                            The one that comforts you
                        </span>
                    </div>
                </a>
                
                <p class="text-xs text-white/70 leading-relaxed max-w-xs mb-5">
                    Quality products, unmatched style, and exceptional customer service. That's our promise to you.
                </p>

                <!-- Social Icons (Facebook, Instagram, Twitter, WhatsApp) -->
                <div class="flex items-center gap-3 text-white/80">
                    <a href="#" class="w-7 h-7 rounded-full bg-white/10 hover:bg-[#AC9661] transition-colors flex items-center justify-center text-xs" aria-label="Facebook">
                        f
                    </a>
                    <a href="#" class="w-7 h-7 rounded-full bg-white/10 hover:bg-[#AC9661] transition-colors flex items-center justify-center text-xs" aria-label="Instagram">
                        ig
                    </a>
                    <a href="#" class="w-7 h-7 rounded-full bg-white/10 hover:bg-[#AC9661] transition-colors flex items-center justify-center text-xs" aria-label="Twitter">
                        𝕏
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="w-7 h-7 rounded-full bg-white/10 hover:bg-[#25d366] transition-colors flex items-center justify-center text-xs" aria-label="WhatsApp">
                        wa
                    </a>
                </div>
            </div>

            <!-- Shop Column -->
            <div class="lg:col-span-2">
                <h4 class="text-xs uppercase tracking-wider font-bold text-white mb-4">Shop</h4>
                <ul class="space-y-2.5">
                    @foreach($footer['shop_links'] ?? [] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-xs text-white/70 hover:text-[#AC9661] transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Collections Column -->
            <div class="lg:col-span-2">
                <h4 class="text-xs uppercase tracking-wider font-bold text-white mb-4">Collections</h4>
                <ul class="space-y-2.5">
                    @foreach($footer['collections_links'] ?? [] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-xs text-white/70 hover:text-[#AC9661] transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Customer Service Column -->
            <div class="lg:col-span-2">
                <h4 class="text-xs uppercase tracking-wider font-bold text-white mb-4">Customer Service</h4>
                <ul class="space-y-2.5">
                    @foreach($footer['customer_service_links'] ?? [] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-xs text-white/70 hover:text-[#AC9661] transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Company Column -->
            <div class="lg:col-span-3">
                <h4 class="text-xs uppercase tracking-wider font-bold text-white mb-4">Payment Methods</h4>
                
                <!-- Payment Badges (Clean White Badges) -->
                <div class="flex flex-wrap items-center gap-2 mb-6">
                    <span class="px-3 py-1 rounded bg-white text-[#1a1f71] text-[11px] font-extrabold tracking-tight">VISA</span>
                    <span class="px-3 py-1 rounded bg-white text-[#eb001b] text-[11px] font-extrabold tracking-tight">Mastercard</span>
                    <span class="px-3 py-1 rounded bg-[#006fcf] text-white text-[11px] font-extrabold tracking-tight">RuPay</span>
                    <span class="px-3 py-1 rounded bg-white text-[#097939] text-[11px] font-extrabold tracking-tight">UPI</span>
                    <span class="px-3 py-1 rounded bg-white text-black text-[11px] font-bold tracking-tight">GPay</span>
                </div>

                <div class="pt-2">
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs text-[#AC9661] hover:underline font-semibold">
                        <span>Direct WhatsApp Concierge</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Bottom Bar: Copyright & Currency Switcher -->
        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/60">
            <div>
                <p>© {{ date('Y') }} Menahem Home Essentials. All Rights Reserved.</p>
            </div>

            <!-- Country/Currency Pill Dropdown in Dark Khaki -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-md bg-white/10 text-white/80 text-[11px]">
                <span>India | INR ₹</span>
                <span class="text-[9px]">▼</span>
            </div>
        </div>

    </div>
</footer>
