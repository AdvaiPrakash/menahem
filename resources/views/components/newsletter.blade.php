@props([
    'content' => [],
])

@php
    $newsletter = $content['NEWSLETTER'] ?? [];
@endphp

<section class="py-14 sm:py-16 bg-[#faf9f5] border-b border-[#353819]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <x-reveal delay="100">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8 p-6 sm:p-10 rounded-3xl bg-[#f3efe6] border border-[#353819]/15">
                
                <!-- Left: Mail Icon & Text in Camel & Dark Khaki -->
                <div class="flex items-center gap-4 text-left w-full lg:w-auto">
                    <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-[#212411] shadow-2xs flex-shrink-0">
                        <svg class="w-6 h-6 text-[#AC9661]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-[#212411]">
                            {{ $newsletter['heading'] ?? 'Subscribe to our newsletter' }}
                        </h3>
                        <p class="text-xs sm:text-sm text-[#595b45] mt-0.5">
                            {{ $newsletter['description'] ?? 'Get the latest updates, new arrivals and exclusive offers.' }}
                        </p>
                    </div>
                </div>

                <!-- Right: Form Input & Camel Button -->
                <div class="w-full lg:w-auto flex-1 max-w-md">
                    <form onsubmit="event.preventDefault(); alert('Thank you for subscribing to Menahem!');" class="flex items-center gap-2">
                        <input
                            type="email"
                            required
                            placeholder="{{ $newsletter['placeholder'] ?? 'Enter your email' }}"
                            class="w-full px-4 py-3 rounded-lg bg-white border border-[#353819]/20 text-sm text-[#212411] placeholder-[#595b45]/60 focus:outline-hidden focus:border-[#AC9661] focus:ring-1 focus:ring-[#AC9661] transition-colors"
                        />
                        <button
                            type="submit"
                            class="px-6 py-3 rounded-lg bg-[#AC9661] hover:bg-[#937f4f] text-white text-sm font-semibold tracking-wide transition-all shadow-xs flex-shrink-0"
                        >
                            {{ $newsletter['button'] ?? 'Subscribe' }}
                        </button>
                    </form>
                </div>

            </div>
        </x-reveal>

    </div>
</section>
