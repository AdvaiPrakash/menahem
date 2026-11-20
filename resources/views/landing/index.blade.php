<x-layouts.app :content="$content">
    <x-site-shell :content="$content">
        <!-- 1. Hero Section with Single Direction Marquee & Editorial Banner -->
        <x-hero :content="$content" />

        <!-- 2. Shop by Category (Multi-Level Category Navigation Flow) -->
        <x-categories :content="$content" />

        <!-- 3. Clearance Sale Section (With Specified Percent Off) -->
        <x-clearance-sale :content="$content" />

        <!-- 4. About Menahem: Essence, Philosophy & Quiet Craftsmanship -->
        <x-about-section :content="$content" />

        <!-- 5. Trust Badges Strip (Rating, Free Shipping, Guarantee, Secure Checkout) -->
        <x-trust-strip :content="$content" />

        <!-- 5. Newsletter Subscription Strip -->
        <x-newsletter :content="$content" />
    </x-site-shell>
</x-layouts.app>
