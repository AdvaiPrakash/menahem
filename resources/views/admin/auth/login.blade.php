<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign In | Menahem Administration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,500&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/product-sans" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Product Sans', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#141B13] text-slate-100">
    <div class="w-full max-w-md">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block group">
                <img src="{{ asset('images/logo.png') }}" alt="Menahem" class="w-20 h-20 object-contain mx-auto drop-shadow-xl transition-transform group-hover:scale-105">
            </a>
            <h1 class="font-serif tracking-widest text-2xl font-bold text-white mt-4 uppercase">MENAHEM</h1>
            <p class="text-xs tracking-wider uppercase text-[#B5C1A5] font-semibold mt-1">Management Portal</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-6 sm:p-8 shadow-2xl">
            @if(session('error'))
                <div class="mb-5 p-3 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-200 text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B] focus:border-transparent transition-all"
                           placeholder="admin@menahem.com">
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B] focus:border-transparent transition-all"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-300">
                        <input type="checkbox" name="remember" class="rounded bg-white/10 border-white/20 text-[#74826B] focus:ring-[#74826B]">
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-3 px-4 rounded-xl bg-[#74826B] hover:bg-[#636F5A] text-white font-semibold text-sm tracking-wide transition-all shadow-lg shadow-[#74826B]/25 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#B5C1A5]">
                    Sign In to Dashboard
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-white/10 text-center text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">← Return to Menahem Storefront</a>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-500 mt-6">
            Menahem Home Essentials &copy; {{ date('Y') }}. Role-Gated Admin Environment.
        </p>
    </div>
</body>
</html>
