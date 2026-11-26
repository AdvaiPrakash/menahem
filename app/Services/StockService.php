<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Validate that all requested items are in stock and have sufficient quantities.
     *
     * @param  array<int, array{id: int, quantity: int}>  $items
     * @return array{success: bool, errors: array<string>}
     */
    public function validateStockAvailability(array $items): array
    {
        $errors = [];

        foreach ($items as $item) {
            $rawId = $item['product_id'] ?? $item['id'] ?? null;
            $productId = is_numeric($rawId)
                ? (int) $rawId
                : (is_string($rawId) && preg_match('/^(\d+)-/', $rawId, $m) === 1 ? (int) $m[1] : null);
            $qty = (int) ($item['quantity'] ?? 1);

            if (! $productId) {
                continue;
            }

            $product = Product::find($productId);

            if (! $product) {
                $errors[] = "Product #{$productId} is no longer available.";

                continue;
            }

            if (! $product->in_stock || $product->stock_quantity <= 0) {
                $errors[] = "'{$product->name}' is currently out of stock.";

                continue;
            }

            if ($product->stock_quantity < $qty) {
                $errors[] = "Only {$product->stock_quantity} units of '{$product->name}' are currently available in stock (you requested {$qty}).";
            }
        }

        return [
            'success' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Deduct stock for a successfully confirmed order inside a transaction with row locking.
     */
    public function deductStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                // Pessimistic locking to prevent race conditions and overselling
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if (! $product) {
                    continue;
                }

                $prevQty = $product->stock_quantity;
                $deductQty = (int) $item->quantity;
                $newQty = max(0, $prevQty - $deductQty);

                $product->update([
                    'stock_quantity' => $newQty,
                    'sold_quantity' => $product->sold_quantity + $deductQty,
                    'in_stock' => $newQty > 0,
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'sold',
                    'previous_quantity' => $prevQty,
                    'quantity_changed' => -$deductQty,
                    'updated_quantity' => $newQty,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'reason' => "Customer Order #{$order->order_number}",
                ]);
            }
        });
    }

    /**
     * Restore stock when an order is cancelled or refunded.
     */
    public function restoreStockForOrder(Order $order, string $reason = 'Order cancelled'): void
    {
        DB::transaction(function () use ($order, $reason) {
            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if (! $product) {
                    continue;
                }

                $prevQty = $product->stock_quantity;
                $restoreQty = (int) $item->quantity;
                $newQty = $prevQty + $restoreQty;

                $product->update([
                    'stock_quantity' => $newQty,
                    'sold_quantity' => max(0, $product->sold_quantity - $restoreQty),
                    'in_stock' => true,
                ]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'order_cancelled',
                    'previous_quantity' => $prevQty,
                    'quantity_changed' => $restoreQty,
                    'updated_quantity' => $newQty,
                    'order_id' => $order->id,
                    'reason' => "Restock: {$reason} (#{$order->order_number})",
                ]);
            }
        });
    }

    /**
     * Manually adjust stock for a product with audit trail.
     */
    public function adjustStock(
        Product $product,
        int $delta,
        string $movementType,
        ?int $userId = null,
        ?string $reason = null
    ): StockMovement {
        return DB::transaction(function () use ($product, $delta, $movementType, $userId, $reason) {
            // Lock product record
            $locked = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();
            $prevQty = $locked->stock_quantity;
            $newQty = max(0, $prevQty + $delta);

            $locked->update([
                'stock_quantity' => $newQty,
                'in_stock' => $newQty > 0,
            ]);

            return StockMovement::create([
                'product_id' => $locked->id,
                'movement_type' => $movementType,
                'previous_quantity' => $prevQty,
                'quantity_changed' => $delta,
                'updated_quantity' => $newQty,
                'user_id' => $userId,
                'reason' => $reason ?: ucfirst(str_replace('_', ' ', $movementType)),
            ]);
        });
    }
}
