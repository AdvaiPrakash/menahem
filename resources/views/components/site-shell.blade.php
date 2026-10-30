@props([
    'content' => [],
])

@php
    $brand = $content['BRAND'] ?? [];
    $whatsappUrl = $brand['whatsapp_url'] ?? '#';
@endphp

<div class="min-h-screen flex flex-col justify-between relative selection:bg-[#AC9661] selection:text-white">
    <!-- Navbar Component -->
    <x-navbar :content="$content" />

    <!-- Main Content Stage -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Footer Component -->
    <x-footer :content="$content" />

    <!-- Floating Persistent WhatsApp Quick-Action CTA -->
    <aside class="fixed bottom-6 right-6 z-40 group">
        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Direct WhatsApp Consultation" class="flex items-center gap-2.5 px-4 py-3 rounded-full bg-[#25d366] text-white shadow-2xl hover:bg-[#20ba5a] transition-all duration-300 transform hover:scale-105 active:scale-95 border-2 border-white/60">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
            </svg>
            <span class="text-xs font-bold tracking-wider uppercase hidden sm:inline-block">Order on WhatsApp</span>
        </a>
    </aside>
</div>
