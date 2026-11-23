<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'category',
        'category_slug',
        'category_slugs',
        'material',
        'hsn_code',
        'gst_rate',
        'price',
        'numeric_price',
        'original_price',
        'rating',
        'reviews',
        'badge',
        'desc',
        'image',
        'gallery',
        'in_stock',
        'stock_quantity',
        'opening_stock',
        'reserved_stock',
        'sold_quantity',
        'low_stock_threshold',
        'is_featured',
        'is_bestseller',
        'sort_order',
    ];

    /**
     * Cast attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_slugs' => 'array',
            'gallery' => 'array',
            'in_stock' => 'boolean',
            'is_featured' => 'boolean',
            'is_bestseller' => 'boolean',
            'numeric_price' => 'integer',
            'stock_quantity' => 'integer',
            'opening_stock' => 'integer',
            'reserved_stock' => 'integer',
            'sold_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'gst_rate' => 'float',
            'rating' => 'float',
            'reviews' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Category relationship.
     */
    public function categoryItem(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Stock movements relationship.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->orderBy('id', 'desc');
    }

    /**
     * Order items relationship.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0 || ! $this->in_stock;
    }

    /**
     * Check if product is low in stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= ($this->low_stock_threshold ?: 10);
    }

    /**
     * Get stock status string: in_stock, low_stock, out_of_stock.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }

        if ($this->isLowStock()) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Ensure price has currency symbol.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price ?: '₹'.number_format($this->numeric_price);
    }
}
