<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClearanceItem extends Model
{
    protected $fillable = [
        'product_id',
        'discount_type',
        'discount_value',
        'show_on_homepage',
        'show_in_all_products',
        'display_order',
        'is_active',
    ];

    /**
     * Cast attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_value' => 'float',
            'show_on_homepage' => 'boolean',
            'show_in_all_products' => 'boolean',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate discounted sale price from an original price without ever going below zero.
     */
    public function calculateSalePrice(int|float $originalPrice): float
    {
        $base = max(0.0, (float) $originalPrice);
        $value = max(0.0, (float) $this->discount_value);

        if ($this->discount_type === 'fixed') {
            return round(max(0.0, $base - min($value, $base)), 2);
        }

        $pct = min(99.0, $value);

        return round(max(0.0, $base * (1 - ($pct / 100))), 2);
    }

    /**
     * Format a short badge label for the discount (e.g. "25% OFF" or "₹500 OFF").
     */
    public function discountBadgeLabel(int|float $originalPrice): string
    {
        if ($this->discount_type === 'fixed') {
            $amount = min((float) $this->discount_value, max(0.0, (float) $originalPrice));

            return '₹'.number_format($amount, 0).' OFF';
        }

        $pct = min(99.0, max(0.0, (float) $this->discount_value));

        return rtrim(rtrim(number_format($pct, 2, '.', ''), '0'), '.').'% OFF';
    }

    /**
     * Format the savings string for clearance cards.
     */
    public function savingsLabel(int|float $originalPrice): string
    {
        $sale = $this->calculateSalePrice($originalPrice);
        $saved = max(0.0, (float) $originalPrice - $sale);
        $pct = $originalPrice > 0 ? round(($saved / $originalPrice) * 100) : 0;

        return 'Save ₹'.number_format($saved, 0)." ({$pct}% OFF)";
    }
}
