<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'category',
        'category_slug',
        'category_slugs',
        'material',
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
     * Ensure price has currency symbol.
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price ?: '₹'.number_format($this->numeric_price);
    }
}
