@props([
    'title' => null,
    'description' => null,
    'content' => [],
])

@php
    $seo = $content['SEO'] ?? [];
    $brand = $content['BRAND'] ?? [];
    $pageTitle = $title ?? ($seo['title'] ?? 'Menahem · Luxury Home Essentials');
    $pageDesc = $description ?? ($seo['description'] ?? 'Transform your home with timeless elegance. Curated curtains, luxury towels, and linen essentials.');
    $siteUrl = url()->current();

    $jsonLd = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => url('/') . '#organization',
                'name' => 'Menahem',
                'url' => url('/'),
                'logo' => asset('images/logo.png'),
                'description' => $brand['description'] ?? '',
                'slogan' => $brand['tagline'] ?? '',
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
                'url' => url('/'),
                'name' => 'Menahem',
                'publisher' => [
                    '@id' => url('/') . '#organization',
                ],
            ],
            [
                '@type' => 'Store',
                'name' => 'Menahem Home Essentials',
                'image' => asset('images/hero/menahem-og.jpg'),
                'priceRange' => '₹₹',
                'telephone' => $brand['whatsapp_number'] ?? '',
                'currenciesAccepted' => 'INR',
                'paymentAccepted' => 'Cash, Credit Card, UPI, Netbanking',
            ],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    @if(!empty($seo['keywords']))
        <meta name="keywords" content="{{ $seo['keywords'] }}">
    @endif
    <link rel="canonical" href="{{ $siteUrl }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $siteUrl }}">
    <meta property="og:title" content="{{ $seo['og_title'] ?? $pageTitle }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $pageDesc }}">
    <meta property="og:image" content="{{ asset('images/hero/menahem-og.jpg') }}">
    <meta property="og:site_name" content="Menahem">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['og_title'] ?? $pageTitle }}">
    <meta name="twitter:description" content="{{ $seo['og_description'] ?? $pageDesc }}">
    <meta name="twitter:image" content="{{ asset('images/hero/menahem-og.jpg') }}">

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192x192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#384230">

    <!-- Google Web Fonts (Playfair Display & Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600;1,700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- JSON-LD Structured Data safely encoded from PHP array -->
    <script type="application/ld+json">
    {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[var(--color-canvas)] text-[var(--color-charcoal)] font-sans antialiased relative selection:bg-[#B5C1A5] selection:text-[#2B3326]">
    {{ $slot }}
</body>
</html>
