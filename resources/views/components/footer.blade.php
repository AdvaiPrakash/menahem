@props([
    'content' => [],
])

@php
    $footer = $content['FOOTER'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<footer class="bg-gradient-to-b from-[#F3EFE6] to-[#E8E2D5] text-[#2B3326] pt-14 pb-10 border-t border-[#D7C7B2]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Main Footer Columns Grid in Linen & Sand Palette -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10 pb-12 border-b border-[#D7C7B2]/70">
            
            <!-- Brand & Socials Column -->
            <div class="lg:col-span-3 flex flex-col items-start">
                <a href="/" class="flex items-center gap-3 group mb-4">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Menahem"
                        class="w-10 h-10 object-contain transition-transform duration-300 group-hover:scale-105 drop-shadow-xs"
                    />
                    <div class="flex flex-col">
                        <span class="text-xl font-bold tracking-tight text-[#2B3326] leading-none font-serif">
                            Menahem
                        </span>
                        <span class="text-[9px] uppercase tracking-wider text-[#74826B] font-bold mt-1">
                            The one that comforts you
                        </span>
                    </div>
                </a>
                
                <p class="text-xs text-[#5C6654] leading-relaxed max-w-xs mb-5 font-medium">
                    Quality products, unmatched style, and exceptional customer service. That's our promise to you.
                </p>

                <!-- Social Icons in Sand & White -->
                <div class="flex items-center gap-2.5 text-[#2B3326]">
                    <a href="#" class="w-8 h-8 rounded-full bg-white hover:bg-[#74826B] hover:text-white border border-[#D7C7B2] transition-colors flex items-center justify-center text-xs font-bold shadow-2xs" aria-label="Facebook">
                        f
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white hover:bg-[#74826B] hover:text-white border border-[#D7C7B2] transition-colors flex items-center justify-center text-xs font-bold shadow-2xs" aria-label="Instagram">
                        ig
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white hover:bg-[#74826B] hover:text-white border border-[#D7C7B2] transition-colors flex items-center justify-center text-xs font-bold shadow-2xs" aria-label="Twitter">
                        𝕏
                    </a>
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-full bg-white hover:bg-[#25d366] hover:text-white border border-[#D7C7B2] transition-colors flex items-center justify-center text-xs font-bold shadow-2xs" aria-label="WhatsApp">
                        wa
                    </a>
                </div>
            </div>

            <!-- Shop Column -->
            <div class="lg:col-span-2">
                <h4 class="text-xs uppercase tracking-wider font-bold text-[#2B3326] mb-4">Shop</h4>
                <ul class="space-y-2.5">
                    @foreach($footer['shop_links'] ?? [] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-xs text-[#5C6654] hover:text-[#2B3326] font-medium transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Collections Column -->
            <div class="lg:col-span-2">
                <h4 class="text-xs uppercase tracking-wider font-bold text-[#2B3326] mb-4">Collections</h4>
                <ul class="space-y-2.5">
                    @foreach($footer['collections_links'] ?? [] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-xs text-[#5C6654] hover:text-[#2B3326] font-medium transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Customer Service Column -->
            <div class="lg:col-span-2">
                <h4 class="text-xs uppercase tracking-wider font-bold text-[#2B3326] mb-4">Customer Service</h4>
                <ul class="space-y-2.5">
                    @foreach($footer['customer_service_links'] ?? [] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-xs text-[#5C6654] hover:text-[#2B3326] font-medium transition-colors">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Company Column -->
            <div class="lg:col-span-3">
                <h4 class="text-xs uppercase tracking-wider font-bold text-[#2B3326] mb-4">Payment Methods</h4>
                
                <!-- Payment Badges -->
                <div class="flex flex-wrap items-center gap-2 mb-6">
                    <span class="px-3 py-1 rounded-lg bg-white border border-[#D7C7B2] text-[#1a1f71] text-[11px] font-extrabold tracking-tight shadow-2xs">VISA</span>
                    <span class="px-3 py-1 rounded-lg bg-white border border-[#D7C7B2] text-[#eb001b] text-[11px] font-extrabold tracking-tight shadow-2xs">Mastercard</span>
                    <span class="px-3 py-1 rounded-lg bg-[#006fcf] text-white text-[11px] font-extrabold tracking-tight shadow-2xs">RuPay</span>
                    <span class="px-3 py-1 rounded-lg bg-white border border-[#D7C7B2] text-[#097939] text-[11px] font-extrabold tracking-tight shadow-2xs">UPI</span>
                    <span class="px-3 py-1 rounded-lg bg-white border border-[#D7C7B2] text-black text-[11px] font-bold tracking-tight shadow-2xs">GPay</span>
                </div>

                <div class="pt-2">
                    <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs text-[#74826B] hover:text-[#2B3326] font-bold">
                        <span>Direct WhatsApp Concierge</span>
                        <span class="font-mono">→</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Bottom Bar: Copyright & Currency Switcher -->
        <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#5C6654] font-medium">
            <div>
                <p>© {{ date('Y') }} Menahem Home Essentials. All Rights Reserved.</p>
            </div>

            <!-- Country/Currency Pill Dropdown in Crisp Sand -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-[#D7C7B2] text-[#2B3326] text-[11px] font-bold shadow-2xs">
                <span>India | INR ₹</span>
                <span class="text-[9px]">▼</span>
            </div>
        </div>

    </div>
</footer>
