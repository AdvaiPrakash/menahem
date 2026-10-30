<x-layouts.app :content="$content">
    <x-site-shell :content="$content">
        <!-- 1. Hero Section with Trust Benefits Bar & UP TO 30% OFF Badge -->
        <x-hero :content="$content" />

        <!-- 2. Shop by Category (8 Minimalist Cards) -->
        <x-categories :content="$content" />

        <!-- 3. Promotional Banner (Split Offer with Live Countdown Timer) -->
        <x-promo-banner :content="$content" />

        <!-- 4. Trending Products (5 Handpicked Essentials with Ratings & Prices) -->
        <x-trending :content="$content" />

        <!-- 5. Trust Badges Strip (Rating, Free Shipping, Guarantee, Secure Checkout) -->
        <x-trust-strip :content="$content" />

        <!-- 6. Newsletter Subscription Strip -->
        <x-newsletter :content="$content" />
    </x-site-shell>
</x-layouts.app>
