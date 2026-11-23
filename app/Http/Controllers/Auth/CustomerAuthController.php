<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use App\Services\CustomerLeadService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function __construct(
        protected CustomerLeadService $leadService,
        protected CartService $cartService
    ) {}

    /**
     * Show customer login modal / page.
     */
    public function showLoginForm(): View
    {
        $content = require resource_path('content.php');

        return view('auth.customer-login', [
            'content' => $content,
        ]);
    }

    /**
     * Show customer registration page.
     */
    public function showRegisterForm(): View
    {
        $content = require resource_path('content.php');

        return view('auth.customer-register', [
            'content' => $content,
        ]);
    }

    /**
     * Handle customer login submission.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => Carbon::now()]);

            // Sync with customer leads
            $this->leadService->recordLeadFromUser($user, $user->customer_type ?: 'registered');

            // Merge guest cart with customer account
            $guestToken = $this->cartService->resolveGuestToken($request);
            $this->cartService->mergeGuestCartToUser($guestToken, $user);

            // Redirect to intended or account
            return redirect()->intended(route('account.orders'))
                ->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our customer records.',
        ])->onlyInput('email');
    }

    /**
     * Handle customer registration submission.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => UserRole::Customer,
            'customer_type' => 'registered',
            'last_login_at' => Carbon::now(),
        ]);

        // Auto login
        Auth::login($user);

        // Record in Customer Leads CRM
        $this->leadService->recordLeadFromUser($user, 'registered');

        // Merge guest cart with customer account
        $guestToken = $this->cartService->resolveGuestToken($request);
        $this->cartService->mergeGuestCartToUser($guestToken, $user);

        return redirect()->intended(route('account.orders'))
            ->with('success', 'Your customer account has been created successfully!');
    }

    /**
     * Customer Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }

    /**
     * Redirect customer to Google OAuth authorization URL.
     */
    public function redirectToGoogle(Request $request)
    {
        $clientId = Setting::get('google_client_id') ?: env('GOOGLE_CLIENT_ID');
        $redirectUri = route('auth.google.callback');

        if (empty($clientId)) {
            // If Google credentials are not yet configured in production, provide a graceful message
            return redirect()->route('customer.login')->with('error', 'Google Sign-In is being configured. Please use email login or continue as guest.');
        }

        $state = Str::random(40);
        session(['google_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $code = $request->input('code');
        $state = $request->input('state');
        $savedState = session('google_oauth_state');

        if (! $code || ($state && $savedState && $state !== $savedState)) {
            return redirect()->route('customer.login')
                ->with('error', 'Google authentication was cancelled or could not be verified.');
        }

        $clientId = Setting::get('google_client_id') ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = Setting::get('google_client_secret') ?: env('GOOGLE_CLIENT_SECRET');
        $redirectUri = route('auth.google.callback');

        try {
            // Exchange code for Google Access Token
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if (! $tokenResponse->successful()) {
                Log::error('Google token exchange error: '.$tokenResponse->body());

                return redirect()->route('customer.login')->with('error', 'Failed to retrieve access token from Google.');
            }

            $accessToken = $tokenResponse->json('access_token');

            // Fetch customer profile from Google UserInfo endpoint
            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (! $userResponse->successful()) {
                return redirect()->route('customer.login')->with('error', 'Failed to retrieve Google profile information.');
            }

            $googleUser = $userResponse->json();
            $googleId = $googleUser['sub'] ?? null;
            $email = strtolower(trim($googleUser['email'] ?? ''));
            $name = $googleUser['name'] ?? 'Google Customer';
            $avatar = $googleUser['picture'] ?? null;

            if (! $email) {
                return redirect()->route('customer.login')->with('error', 'No email returned by Google.');
            }

            // Find existing user by Google ID or by Email
            $user = User::where('google_id', $googleId)->orWhere('email', $email)->first();

            if ($user) {
                // Link Google ID if not already linked
                $user->update([
                    'google_id' => $googleId,
                    'avatar' => $avatar ?: $user->avatar,
                    'last_login_at' => Carbon::now(),
                    'customer_type' => 'google',
                ]);
            } else {
                // Create new Google customer account
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $avatar,
                    'password' => Hash::make(Str::random(32)),
                    'role' => UserRole::Customer,
                    'customer_type' => 'google',
                    'last_login_at' => Carbon::now(),
                ]);
            }

            Auth::login($user, true);

            // Sync with Customer Leads CRM
            $this->leadService->recordLeadFromUser($user, 'google');

            // Merge guest cart with customer account
            $guestToken = $this->cartService->resolveGuestToken($request);
            $this->cartService->mergeGuestCartToUser($guestToken, $user);

            return redirect()->intended(route('account.orders'))
                ->with('success', "Signed in with Google as {$user->name}.");
        } catch (\Throwable $e) {
            Log::error('Google OAuth exception: '.$e->getMessage());

            return redirect()->route('customer.login')->with('error', 'Google authentication error: '.$e->getMessage());
        }
    }
}
