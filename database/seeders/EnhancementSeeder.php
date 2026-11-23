<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

class EnhancementSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed initial Coupons
        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_order_amount' => 999.00,
                'max_discount_amount' => 500.00,
                'usage_limit' => 1000,
                'is_active' => true,
                'description' => '10% OFF on orders above ₹999 (Max discount ₹500)',
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'FESTIVE200'],
            [
                'discount_type' => 'fixed',
                'discount_value' => 200.00,
                'min_order_amount' => 1499.00,
                'usage_limit' => 500,
                'is_active' => true,
                'description' => 'Flat ₹200 OFF on festive orders above ₹1,499',
            ]
        );

        // 2. Initialize Product SKUs, HSN codes, and initial stock movements
        $products = Product::all();
        foreach ($products as $index => $prod) {
            $catPrefix = match (strtolower($prod->category_slug ?? '')) {
                'curtains-drapes', 'sheer-curtains', 'blackout-drapes' => 'CUR',
                'bedsheets-dohars', 'cotton-bedsheets', 'dohars-quilts' => 'BED',
                'towels-wellness', 'bath-towels', 'hand-towels' => 'TWL',
                'table-decor' => 'TBL',
                'sofa-decor' => 'SFA',
                'rugs' => 'RUG',
                default => 'MNH',
            };

            $sku = $prod->sku ?: sprintf('MNH-%s-%03d', $catPrefix, $prod->id);
            $hsnCode = in_array($catPrefix, ['CUR', 'TBL', 'SFA', 'RUG']) ? '6304' : '6302';
            $stockQty = $prod->stock_quantity > 0 ? $prod->stock_quantity : (35 + ($prod->id % 20));

            $prod->update([
                'sku' => $sku,
                'hsn_code' => $prod->hsn_code ?: $hsnCode,
                'gst_rate' => $prod->gst_rate ?: 12.00,
                'stock_quantity' => $stockQty,
                'opening_stock' => $prod->opening_stock ?: $stockQty,
                'low_stock_threshold' => 10,
                'in_stock' => $stockQty > 0,
            ]);

            // Create initial movement if none exists
            if ($prod->stockMovements()->count() === 0) {
                StockMovement::create([
                    'product_id' => $prod->id,
                    'movement_type' => 'initial',
                    'previous_quantity' => 0,
                    'quantity_changed' => $stockQty,
                    'updated_quantity' => $stockQty,
                    'user_id' => 1,
                    'reason' => 'Initial inventory setup',
                ]);
            }
        }

        // 3. Seed default Settings
        $defaultSettings = [
            'razorpay_key_id' => 'rzp_test_placeholder_key',
            'razorpay_key_secret' => 'rzp_test_placeholder_secret',
            'razorpay_mode' => 'test',
            'razorpay_status' => 'enabled',
            'gateway_fee_mode' => 'absorbed',
            'gateway_fee_percent' => '0',
            'seller_name' => 'Menahem Home & Living',
            'seller_gstin' => '27AABCM1234F1Z8',
            'seller_address' => '42, Textile Market Road, Kalbadevi, Mumbai, Maharashtra 400002',
            'seller_state' => 'Maharashtra',
            'seller_state_code' => '27',
            'seller_phone' => '+91 98765 43210',
            'seller_email' => 'orders@menahem.com',
        ];

        foreach ($defaultSettings as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v, 'group' => 'system']);
        }
    }
}
