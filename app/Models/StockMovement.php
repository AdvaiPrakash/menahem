<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'previous_quantity',
        'quantity_changed',
        'updated_quantity',
        'user_id',
        'order_id',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'previous_quantity' => 'integer',
            'quantity_changed' => 'integer',
            'updated_quantity' => 'integer',
            'order_id' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function movementTypeLabel(): string
    {
        return match ($this->movement_type) {
            'initial' => 'Initial Stock',
            'restock' => 'Stock Added',
            'sold' => 'Product Sold',
            'order_cancelled' => 'Order Cancelled Restock',
            'order_returned' => 'Returned Product',
            'damaged' => 'Damaged / Discarded',
            'manual_adjustment' => 'Manual Adjustment',
            default => ucfirst(str_replace('_', ' ', $this->movement_type)),
        };
    }
}
