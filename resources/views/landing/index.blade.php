<x-layouts.app :content="$content">
    <x-site-shell :content="$content">
        <!-- 1. Hero Section with Single Direction Marquee & Editorial Banner -->
        <x-hero :content="$content" />

        <!-- 2. Shop by Category (5 Images in 1 Row on Desktop, 2 on Mobile) -->
        <x-categories :content="$content" />

        <!-- 3. Shop by Rooms (Shop for Bedroom, Shop for Livingroom, Shop for Kitchen) -->
        <x-rooms-showcase :content="$content" />

        <!-- 4. Curtains & Custom Drapes Showcase -->
        <x-curtains-showcase :content="$content" />

        <!-- 5. Handcrafted Bedsheets & Mulmul Dohars Showcase -->
        <x-bedsheets-showcase :content="$content" />

        <!-- 6. About Menahem: Essence, Philosophy & Quiet Craftsmanship -->
        <x-about-section :content="$content" />

        <!-- 7. Trending Home Essentials (Curated Favorites with Direct Actions) -->
        <x-trending :content="$content" />

        <!-- 7. Trust Badges Strip (Rating, Free Shipping, Guarantee, Secure Checkout) -->
        <x-trust-strip :content="$content" />

        <!-- 8. Newsletter Subscription Strip -->
        <x-newsletter :content="$content" />
    </x-site-shell>
</x-layouts.app>
