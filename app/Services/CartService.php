<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    public const GUEST_COOKIE_NAME = 'menahem_guest_token';

    /**
     * Resolve or issue a unique guest identifier token.
     */
    public function resolveGuestToken(Request $request): string
    {
        $token = $request->cookie(self::GUEST_COOKIE_NAME)
            ?: $request->header('X-Guest-Token')
            ?: $request->input('guest_token');

        if (! $token || strlen($token) < 16) {
            $token = Str::random(32);
            Cookie::queue(self::GUEST_COOKIE_NAME, $token, 60 * 24 * 60); // 60 days persistence
        }

        return $token;
    }

    /**
     * Get all active cart items for the given user or guest session.
     *
     * @return Collection<int, CartItem>
     */
    public function getItems(?User $user, string $guestToken): Collection
    {
        $query = CartItem::with(['product' => function ($q) {
            $q->with('clearanceItem')
                ->select('id', 'name', 'numeric_price', 'price', 'original_price', 'image', 'stock_quantity', 'in_stock', 'category_id', 'sku');
        }]);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        $items = $query->latest('updated_at')->get();

        // Filter out items whose product no longer exists
        return $items->filter(fn ($item) => $item->product !== null)->values();
    }

    /**
     * Add a product to the user's or guest's cart.
     */
    public function addItem(?User $user, string $guestToken, int $productId, int $quantity = 1, ?string $customSize = null): CartItem
    {
        $product = Product::findOrFail($productId);
        $quantity = max(1, min(99, $quantity));

        $attributes = [
            'product_id' => $product->id,
            'custom_size' => $customSize ?: null,
        ];

        if ($user) {
            $attributes['user_id'] = $user->id;
        } else {
            $attributes['guest_token'] = $guestToken;
        }

        $existing = CartItem::where($attributes)->first();

        if ($existing) {
            $existing->quantity = min(99, $existing->quantity + $quantity);
            $existing->save();

            return $existing->load('product');
        }

        return CartItem::create(array_merge($attributes, [
            'quantity' => $quantity,
        ]))->load('product');
    }

    /**
     * Update quantity of a specific cart item.
     */
    public function updateQuantity(?User $user, string $guestToken, int $cartItemId, int $quantity): ?CartItem
    {
        $query = CartItem::where('id', $cartItemId);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        $item = $query->first();
        if (! $item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();

            return null;
        }

        $item->quantity = min(99, max(1, $quantity));
        $item->save();

        return $item->load('product');
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(?User $user, string $guestToken, int $cartItemId): bool
    {
        $query = CartItem::where('id', $cartItemId);

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        return (bool) $query->delete();
    }

    /**
     * Clear all items in the cart.
     */
    public function clearCart(?User $user, string $guestToken): void
    {
        if ($user) {
            CartItem::where('user_id', $user->id)->delete();
        } else {
            CartItem::where('guest_token', $guestToken)->delete();
        }
    }

    /**
     * Synchronize client cart items with the database.
     */
    public function syncFromClient(?User $user, string $guestToken, array $clientItems): Collection
    {
        foreach ($clientItems as $clientItem) {
            $prodId = (int) ($clientItem['id'] ?? $clientItem['product_id'] ?? 0);
            $qty = (int) ($clientItem['quantity'] ?? 1);
            $size = $clientItem['custom_size'] ?? null;

            if ($prodId > 0 && Product::where('id', $prodId)->exists()) {
                $this->addItem($user, $guestToken, $prodId, $qty, $size);
            }
        }

        return $this->getItems($user, $guestToken);
    }

    /**
     * Merge guest cart into customer account cart upon login / signup.
     */
    public function mergeGuestCartToUser(string $guestToken, User $user): void
    {
        if (empty($guestToken)) {
            return;
        }

        $guestItems = CartItem::where('guest_token', $guestToken)->get();

        foreach ($guestItems as $gItem) {
            $userExisting = CartItem::where('user_id', $user->id)
                ->where('product_id', $gItem->product_id)
                ->where('custom_size', $gItem->custom_size)
                ->first();

            if ($userExisting) {
                $userExisting->quantity = min(99, $userExisting->quantity + $gItem->quantity);
                $userExisting->save();
                $gItem->delete();
            } else {
                $gItem->update([
                    'user_id' => $user->id,
                    'guest_token' => null,
                ]);
            }
        }

        // Also merge guest wishlists
        $guestWishlists = Wishlist::where('guest_token', $guestToken)->get();
        foreach ($guestWishlists as $gw) {
            $userHas = Wishlist::where('user_id', $user->id)
                ->where('product_id', $gw->product_id)
                ->exists();

            if (! $userHas) {
                $gw->update([
                    'user_id' => $user->id,
                    'guest_token' => null,
                ]);
            } else {
                $gw->delete();
            }
        }
    }

    /**
     * Toggle a product in wishlist.
     */
    public function toggleWishlist(?User $user, string $guestToken, int $productId): array
    {
        $product = Product::findOrFail($productId);

        $attributes = ['product_id' => $product->id];
        if ($user) {
            $attributes['user_id'] = $user->id;
        } else {
            $attributes['guest_token'] = $guestToken;
        }

        $existing = Wishlist::where($attributes)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            Wishlist::create($attributes);
            $liked = true;
        }

        $count = $user
            ? Wishlist::where('user_id', $user->id)->count()
            : Wishlist::where('guest_token', $guestToken)->count();

        return [
            'liked' => $liked,
            'count' => $count,
            'product_id' => $product->id,
        ];
    }

    /**
     * Get wishlisted products.
     *
     * @return Collection<int, Product>
     */
    public function getWishlistProducts(?User $user, string $guestToken): Collection
    {
        $query = Wishlist::with('product');

        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('guest_token', $guestToken);
        }

        return $query->latest('created_at')
            ->get()
            ->pluck('product')
            ->filter()
            ->values();
    }
}
