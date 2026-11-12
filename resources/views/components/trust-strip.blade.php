@props([
    'content' => [],
])

<section class="py-10 bg-[#FAF7F0] border-b border-[#D7C7B2]/70">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <x-reveal delay="100">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8">
                
                <!-- 1. Rating -->
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#E8E2D5] shadow-2xs border border-[#D7C7B2] flex items-center justify-center text-[#2B3326] flex-shrink-0">
                        <svg class="w-5 h-5 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-[#2B3326] leading-tight">4.9/5 Average Rating</h4>
                        <p class="text-[11px] text-[#5C6654] mt-0.5 font-medium">From 10,000+ Reviews</p>
                    </div>
                </div>

                <!-- 2. Fast & Free Shipping -->
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#E8E2D5] shadow-2xs border border-[#D7C7B2] flex items-center justify-center text-[#2B3326] flex-shrink-0">
                        <svg class="w-5 h-5 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1-4l-3 4h4z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-[#2B3326] leading-tight">Fast & Free Shipping</h4>
                        <p class="text-[11px] text-[#5C6654] mt-0.5 font-medium">On orders over ₹999</p>
                    </div>
                </div>

                <!-- 3. 30-Day Money Back -->
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#E8E2D5] shadow-2xs border border-[#D7C7B2] flex items-center justify-center text-[#2B3326] flex-shrink-0">
                        <svg class="w-5 h-5 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-[#2B3326] leading-tight">30-Day Money Back</h4>
                        <p class="text-[11px] text-[#5C6654] mt-0.5 font-medium">No questions asked</p>
                    </div>
                </div>

                <!-- 4. Secure & Safe Checkout -->
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-[#E8E2D5] shadow-2xs border border-[#D7C7B2] flex items-center justify-center text-[#2B3326] flex-shrink-0">
                        <svg class="w-5 h-5 text-[#74826B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold text-[#2B3326] leading-tight">Secure & Safe Checkout</h4>
                        <p class="text-[11px] text-[#5C6654] mt-0.5 font-medium">Your data is protected</p>
                    </div>
                </div>

            </div>
        </x-reveal>

    </div>
</section>
