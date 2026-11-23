<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Display the wishlist page.
     */
    public function index(Request $request): View|JsonResponse
    {
        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        try {
            $products = $this->cartService->getWishlistProducts($user, $guestToken);
        } catch (\Throwable $e) {
            \Log::error('Wishlist database query error: '.$e->getMessage());
            $products = collect();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $products->count(),
                'products' => $products,
            ]);
        }

        return view('wishlist.index', [
            'products' => $products,
        ]);
    }

    /**
     * Toggle a product in wishlist.
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        try {
            $result = $this->cartService->toggleWishlist($user, $guestToken, (int) $validated['product_id']);

            return response()->json([
                'success' => true,
                'liked' => $result['liked'],
                'count' => $result['count'],
                'product_id' => $result['product_id'],
                'message' => $result['liked'] ? 'Added to wishlist' : 'Removed from wishlist',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Wishlist toggle database error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Unable to update wishlist at this moment. Please check database connection.',
            ], 500);
        }
    }

    /**
     * Get wishlisted product IDs for active user/guest.
     */
    public function status(Request $request): JsonResponse
    {
        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        try {
            $products = $this->cartService->getWishlistProducts($user, $guestToken);

            return response()->json([
                'success' => true,
                'count' => $products->count(),
                'ids' => $products->pluck('id')->values(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Wishlist status database error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'count' => 0,
                'ids' => [],
            ]);
        }
    }
}
