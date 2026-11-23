<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign In | Menahem Administration</title>
    
    <!-- Google Fonts: Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
            border-radius: 0px !important;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#F8F9FA] text-neutral-900">
    <div class="w-full max-w-sm">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block group">
                <img src="{{ asset('images/logo.png') }}" alt="Menahem" class="w-16 h-16 object-contain mx-auto">
            </a>
            <h1 class="tracking-widest text-xl font-black text-neutral-900 mt-3 uppercase">MENAHEM</h1>
            <p class="text-[10px] tracking-widest uppercase text-neutral-500 font-bold mt-0.5">Management Portal</p>
        </div>

        <!-- Login Card: Pure White, Sharp Corners, Clean Borders -->
        <div class="bg-white border border-neutral-300 p-6 sm:p-7 shadow-xs">
            @if(session('error'))
                <div class="mb-4 p-3 bg-rose-50 border border-rose-300 text-rose-800 text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 p-3 bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-neutral-700 mb-1">
                        Username or Email Address
                    </label>
                    <input type="text" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3 py-2 bg-white border border-neutral-300 text-neutral-900 placeholder-neutral-400 text-xs focus:outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 transition-all"
                           placeholder="admin or admin@menahem.com">
                    @error('email')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-neutral-700 mb-1">
                        Password
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 bg-white border border-neutral-300 text-neutral-900 placeholder-neutral-400 text-xs focus:outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900 transition-all"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-neutral-600 text-xs">
                        <input type="checkbox" name="remember" class="border-neutral-300 text-neutral-900 focus:ring-0">
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs uppercase tracking-wider transition-all cursor-pointer mt-2 shadow-none border border-neutral-900">
                    Sign In to Dashboard
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-neutral-200 text-center text-xs text-neutral-500">
                <a href="{{ route('home') }}" class="hover:text-neutral-900 transition-colors">← Return to Menahem Storefront</a>
            </div>
        </div>
        
        <p class="text-center text-[10px] text-neutral-400 uppercase tracking-widest mt-6 font-semibold">
            Protected Administrator Area · Menahem Luxury Essentials
        </p>
    </div>
</body>
</html>
