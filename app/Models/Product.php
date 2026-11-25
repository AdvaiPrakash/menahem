<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'highlights',
        'fabric_details',
        'wash_care',
        'shipping_returns',
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
            'highlights' => 'array',
            'fabric_details' => 'array',
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
     * Clearance sale configuration for this product, if any.
     */
    public function clearanceItem(): HasOne
    {
        return $this->hasOne(ClearanceItem::class);
    }

    /**
     * Effective selling price (applies active clearance discount without double-discounting).
     */
    public function effectivePrice(): float
    {
        $base = (float) $this->numeric_price;
        $clearance = $this->clearanceItem;

        if ($clearance && $clearance->is_active) {
            return $clearance->calculateSalePrice($base);
        }

        return $base;
    }

    /**
     * Effective formatted selling price string.
     */
    public function effectiveFormattedPrice(): string
    {
        $effective = $this->effectivePrice();

        return '₹'.number_format($effective, fmod($effective, 1.0) === 0.0 ? 0 : 2);
    }

    /**
     * Effective original (strikethrough) price string when a clearance discount or original price exists.
     */
    public function effectiveOriginalPrice(): ?string
    {
        $clearance = $this->clearanceItem;
        if ($clearance && $clearance->is_active && $this->effectivePrice() < (float) $this->numeric_price) {
            return '₹'.number_format((float) $this->numeric_price);
        }

        return $this->original_price;
    }

    /**
     * Ordered list of all product images starting with the primary image.
     *
     * @return array<int, string>
     */
    public function allImages(): array
    {
        $images = [];
        if (! empty($this->image)) {
            $images[] = $this->image;
        }
        if (is_array($this->gallery)) {
            foreach ($this->gallery as $img) {
                if (is_string($img) && trim($img) !== '' && ! in_array($img, $images, true)) {
                    $images[] = $img;
                }
            }
        }

        return $images;
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
