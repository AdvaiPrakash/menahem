<!--
  LOGIN / SIGNUP / GUEST ENTRY MODAL
  Module 4.1 Requirement:
  - First-time website entry modal with 3 clean options:
    1. Login (Email + Password)
    2. Continue with Google
    3. Continue as Guest
  - Guest access: customers browse & checkout without being forced to create an account.
  - Stored in localStorage so it does not annoy repeating visitors, but easily reopened via navbar user icon.
-->
<div x-data="{
    modalOpen: false,
    authTab: 'options', // 'options' | 'login' | 'register'
    init() {
        @guest
            // Check if customer already acknowledged or dismissed guest prompt
            const acknowledged = localStorage.getItem('menahem_guest_acknowledged');
            if (!acknowledged) {
                // Brief 1.5s delay on initial visit for clean UX
                setTimeout(() => {
                    this.modalOpen = true;
                }, 1200);
            }
        @endguest
    },
    continueAsGuest() {
        localStorage.setItem('menahem_guest_acknowledged', 'true');
        this.modalOpen = false;
    },
    openModal(tab = 'options') {
        this.authTab = tab;
        this.modalOpen = true;
    }
}" 
@open-auth-modal.window="openModal($event.detail?.tab || 'options')"
x-cloak
class="relative z-50">

    <!-- Backdrop Overlay -->
    <div x-show="modalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="continueAsGuest()"
         class="fixed inset-0 bg-[#2B3326]/60 backdrop-blur-xs transition-opacity"
         style="display: none;"></div>

    <!-- Modal Box -->
    <div x-show="modalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="continueAsGuest()" class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-neutral-200 overflow-hidden relative">
            <!-- Close Button -->
            <button type="button" 
                    @click="continueAsGuest()"
                    aria-label="Close"
                    class="absolute top-4 right-4 w-8 h-8 rounded-full bg-neutral-100 hover:bg-neutral-200 text-[#2B3326] flex items-center justify-center font-bold text-sm cursor-pointer transition-colors z-10">
                ✕
            </button>

            <!-- Brand Header -->
            <div class="bg-[#384230] p-6 text-center text-white relative">
                <img src="{{ asset('images/logo.png') }}" alt="Menahem" class="w-12 h-12 mx-auto object-contain mb-2">
                <h3 class="font-serif text-xl font-bold tracking-wide">Welcome to Menahem</h3>
                <p class="text-xs text-[#B5C1A5] mt-1 font-medium">Comfort, Style & Beauty in Every Detail</p>
            </div>

            <!-- Tab 1: Three Primary Options (Login, Google, Continue as Guest) -->
            <div x-show="authTab === 'options'" class="p-6 sm:p-8 space-y-4">
                <p class="text-xs text-center text-[#5C6654] font-medium mb-2">
                    How would you like to explore our luxury home essentials today?
                </p>

                <!-- 1. Google Authentication -->
                <a href="{{ route('auth.google') }}" 
                   class="w-full flex items-center justify-center gap-3 py-3 px-4 rounded-xl border border-neutral-300 bg-white hover:bg-neutral-50 text-[#2B3326] text-xs sm:text-sm font-bold shadow-2xs transition-all hover:shadow-xs">
                    <svg class="w-4 h-4" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                    </svg>
                    <span>Continue with Google</span>
                </a>

                <!-- 2. Email Login -->
                <button type="button" 
                        @click="authTab = 'login'" 
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#384230] hover:bg-[#2C3424] text-white text-xs sm:text-sm font-bold shadow-xs transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    <span>Sign In with Email</span>
                </button>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-neutral-200"></div>
                    <span class="flex-shrink mx-3 text-[11px] uppercase tracking-wider text-neutral-400 font-semibold">Or</span>
                    <div class="flex-grow border-t border-neutral-200"></div>
                </div>

                <!-- 3. Continue as Guest (Unrestricted Access) -->
                <button type="button" 
                        @click="continueAsGuest()" 
                        class="w-full py-3 px-4 rounded-xl border border-neutral-300 text-[#2B3326] hover:bg-neutral-50 text-xs sm:text-sm font-bold transition-colors cursor-pointer flex items-center justify-center gap-2">
                    <span>Continue as Guest</span>
                    <span class="text-neutral-400">&rarr;</span>
                </button>

                <p class="text-[11px] text-center text-[#5C6654] pt-1">
                    No account required. You can browse and complete purchases freely as a guest.
                </p>
            </div>

            <!-- Tab 2: Customer Email Login -->
            <div x-show="authTab === 'login'" class="p-6 sm:p-8 space-y-4">
                <form action="{{ route('customer.login.submit') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" name="email" required placeholder="aditi@example.com"
                               class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#2B3326] uppercase tracking-wider mb-1.5">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                               class="w-full px-4 py-2.5 rounded-lg border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#74826B]/20 focus:border-[#74826B]">
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-[#5C6654]">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#74826B] focus:ring-[#74826B]">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 rounded-xl bg-[#384230] hover:bg-[#2C3424] text-white text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                        Sign In
                    </button>
                </form>

                <div class="pt-2 text-center text-xs space-y-2">
                    <p class="text-[#5C6654]">
                        Don't have an account? 
                        <a href="{{ route('customer.register') }}" class="text-[#74826B] font-bold hover:underline">Create one</a>
                    </p>
                    <p>
                        <button type="button" @click="authTab = 'options'" class="text-[#5C6654] hover:text-[#2B3326] underline font-medium">
                            &larr; Back to sign in options
                        </button>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
