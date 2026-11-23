<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Get current cart status and items.
     */
    public function index(Request $request): JsonResponse
    {
        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        try {
            $items = $this->cartService->getItems($user, $guestToken);
        } catch (\Throwable $e) {
            \Log::error('Cart query error: '.$e->getMessage());
            $items = collect();
        }

        $subtotal = $items->sum(fn ($i) => ($i->product?->numeric_price ?? 0) * $i->quantity);
        $shipping = $subtotal >= 999 ? 0 : ($subtotal > 0 ? 99 : 0);

        return response()->json([
            'success' => true,
            'guest_token' => $guestToken,
            'count' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'items' => $items->map(fn ($i) => [
                'cart_item_id' => $i->id,
                'id' => $i->product_id,
                'name' => $i->product?->name,
                'price' => $i->product?->numeric_price,
                'image' => $i->product?->image,
                'quantity' => $i->quantity,
                'custom_size' => $i->custom_size,
                'category' => $i->product?->category,
                'stock' => $i->product?->stock_quantity,
            ]),
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'custom_size' => ['nullable', 'string', 'max:100'],
        ]);

        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        $cartItem = $this->cartService->addItem(
            $user,
            $guestToken,
            (int) $validated['product_id'],
            (int) ($validated['quantity'] ?? 1),
            $validated['custom_size'] ?? null
        );

        return $this->index($request);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        $this->cartService->updateQuantity($user, $guestToken, $id, (int) $validated['quantity']);

        return $this->index($request);
    }

    /**
     * Remove item from cart.
     */
    public function remove(Request $request, int $id): JsonResponse
    {
        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        $this->cartService->removeItem($user, $guestToken, $id);

        return $this->index($request);
    }

    /**
     * Clear the cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();

        $this->cartService->clearCart($user, $guestToken);

        return $this->index($request);
    }

    /**
     * Synchronize items from client storage.
     */
    public function sync(Request $request): JsonResponse
    {
        $guestToken = $this->cartService->resolveGuestToken($request);
        $user = $request->user();
        $clientItems = $request->input('items', []);

        if (is_array($clientItems) && count($clientItems) > 0) {
            $this->cartService->syncFromClient($user, $guestToken, $clientItems);
        }

        return $this->index($request);
    }
}
