@props([
    'content' => [],
])

@php
    $categories = $content['CATEGORIES'] ?? [];
    $items = $categories['items'] ?? [];
@endphp

<section id="shop-categories" class="py-16 sm:py-20 bg-[#faf9f5] border-b border-[#353819]/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header: Title & View All Link (Matches Reference) -->
        <div class="flex items-center justify-between mb-8 sm:mb-10">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#212411]">
                    {{ $categories['heading'] ?? 'Shop by Category' }}
                </h2>
                <p class="text-xs sm:text-sm text-[#595b45] mt-1">
                    Bedsheets, Curtains, Table Decor, Rugs, Towels & Sofa Essentials
                </p>
            </div>
            <a href="#collections" class="text-sm font-semibold text-[#212411] hover:text-[#AC9661] transition-colors flex items-center gap-1.5 group">
                <span>{{ $categories['view_all_label'] ?? 'View all collections' }}</span>
                <span class="transition-transform group-hover:translate-x-1">→</span>
            </a>
        </div>

        <!-- 8 Cards Grid (Matches Reference Layout) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-4">
            @foreach($items as $index => $item)
                <x-reveal delay="{{ 50 * ($index + 1) }}">
                    <a href="#collections" class="group flex flex-col items-center text-center">
                        <!-- Rounded Card Box (Full Cover Canvas without inner border) -->
                        <div class="w-full aspect-square rounded-2xl bg-[#f3efe6] group-hover:shadow-md transition-all duration-300 flex items-center justify-center relative overflow-hidden">
                            
                            <!-- Real Image / Photographic preview covering full canvas -->
                            @if(!empty($item['image']))
                                <img
                                    src="{{ asset($item['image']) }}"
                                    alt="{{ $item['name'] }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                    onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                />
                            @endif

                            <!-- Fallback Elegant Icon / Graphic in Camel -->
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-white/80 items-center justify-center text-[#AC9661] group-hover:scale-110 transition-transform duration-300 shadow-xs {{ !empty($item['image']) ? 'hidden' : 'flex' }}">
                                @if($item['slug'] === 'curtains')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 4h16v2H4V4zm2 2v14h4V6H6zm8 0v14h4V6h-4z"/>
                                    </svg>
                                @elseif($item['slug'] === 'towels')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                @elseif($item['slug'] === 'table-decor')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                @elseif($item['slug'] === 'sofa-decor')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="3" y="8" width="18" height="10" rx="3" stroke-width="1.6"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 12h18M7 8V6a2 2 0 012-2h6a2 2 0 012 2v2"/>
                                    </svg>
                                @elseif($item['slug'] === 'bedsheets')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M3 7h18v12H3V7zm0 5h18M7 7v5"/>
                                    </svg>
                                @elseif($item['slug'] === 'rugs')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <rect x="3" y="6" width="18" height="12" rx="2" stroke-width="1.6"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M7 6v12M17 6v12M3 10h18M3 14h18"/>
                                    </svg>
                                @elseif($item['slug'] === 'dohars-quilts')
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                @else
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                @endif
                            </div>

                        </div>

                        <!-- Category Name Underneath -->
                        <span class="mt-2.5 text-xs sm:text-sm font-semibold text-[#212411] group-hover:text-[#AC9661] transition-colors line-clamp-1">
                            {{ $item['name'] }}
                        </span>
                        @if(!empty($item['desc']))
                            <span class="text-[10px] text-[#595b45] line-clamp-1">
                                {{ $item['desc'] }}
                            </span>
                        @endif
                    </a>
                </x-reveal>
            @endforeach
        </div>

    </div>
</section>
