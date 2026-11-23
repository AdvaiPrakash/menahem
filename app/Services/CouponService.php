<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;

class CouponService
{
    /**
     * Validate an offer code against an order subtotal.
     *
     * @return array{valid: bool, coupon?: Coupon, discount: float, message: string}
     */
    public function validate(string $code, float $orderAmount): array
    {
        $code = strtoupper(trim($code));

        if (empty($code)) {
            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => 'Please enter an offer code.',
            ];
        }

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => "Offer code '{$code}' does not exist.",
            ];
        }

        if (! $coupon->is_active) {
            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => "Offer code '{$code}' is currently inactive.",
            ];
        }

        $now = Carbon::now();

        if ($coupon->starts_at && $now->isBefore($coupon->starts_at)) {
            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => "Offer code '{$code}' is not yet active.",
            ];
        }

        if ($coupon->expires_at && $now->isAfter($coupon->expires_at)) {
            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => "Offer code '{$code}' has expired.",
            ];
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => "Offer code '{$code}' has reached its maximum usage limit.",
            ];
        }

        if ($coupon->min_order_amount !== null && $orderAmount < $coupon->min_order_amount) {
            $formattedMin = number_format($coupon->min_order_amount);

            return [
                'valid' => false,
                'discount' => 0.0,
                'message' => "Minimum order amount of ₹{$formattedMin} required to use '{$code}'.",
            ];
        }

        $discount = $coupon->calculateDiscount($orderAmount);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => "Coupon '{$code}' applied successfully!",
        ];
    }

    /**
     * Increment coupon usage count.
     */
    public function incrementUsage(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }
}
