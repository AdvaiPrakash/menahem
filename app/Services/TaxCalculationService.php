<?php

namespace App\Services;

use App\Models\Setting;

class TaxCalculationService
{
    /**
     * Standard Indian States list with 2-digit GST state codes.
     */
    public const STATES = [
        'Jammu & Kashmir' => '01',
        'Himachal Pradesh' => '02',
        'Punjab' => '03',
        'Chandigarh' => '04',
        'Uttarakhand' => '05',
        'Haryana' => '06',
        'Delhi' => '07',
        'Rajasthan' => '08',
        'Uttar Pradesh' => '09',
        'Bihar' => '10',
        'Sikkim' => '11',
        'Arunachal Pradesh' => '12',
        'Nagaland' => '13',
        'Manipur' => '14',
        'Mizoram' => '15',
        'Tripura' => '16',
        'Meghalaya' => '17',
        'Assam' => '18',
        'West Bengal' => '19',
        'Jharkhand' => '20',
        'Odisha' => '21',
        'Chhattisgarh' => '22',
        'Madhya Pradesh' => '23',
        'Gujarat' => '24',
        'Dadra & Nagar Haveli and Daman & Diu' => '26',
        'Maharashtra' => '27',
        'Andhra Pradesh' => '28',
        'Karnataka' => '29',
        'Goa' => '30',
        'Lakshadweep' => '31',
        'Kerala' => '32',
        'Tamil Nadu' => '33',
        'Puducherry' => '34',
        'Andaman & Nicobar Islands' => '35',
        'Telangana' => '36',
        'Andhra Pradesh (New)' => '37',
        'Ladakh' => '38',
    ];

    /**
     * Calculate order financial breakdown including taxable amount, intra/inter-state GST, shipping, and gateway charges.
     *
     * @param  array<int, array{price: float, quantity: int, gst_rate?: float}>  $items
     * @return array<string, mixed>
     */
    public function calculate(
        array $items,
        float $discount = 0.0,
        string $customerState = 'Maharashtra',
        float $shippingThreshold = 999.0,
        float $defaultShipping = 99.0
    ): array {
        $sellerState = Setting::get('seller_state', 'Maharashtra');
        $isIntraState = strcasecmp(trim($customerState), trim($sellerState)) === 0;

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += ((float) $item['price']) * ((int) $item['quantity']);
        }

        // Discount cannot exceed subtotal
        $discount = (float) min($discount, $subtotal);
        $taxableAmount = (float) max(0.0, $subtotal - $discount);

        // Calculate itemized tax proportionally
        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalIgst = 0.0;
        $discountRatio = $subtotal > 0 ? ($taxableAmount / $subtotal) : 1.0;

        $calculatedItems = [];
        foreach ($items as $item) {
            $itemSubtotal = ((float) $item['price']) * ((int) $item['quantity']);
            $itemDiscount = ($itemSubtotal / ($subtotal ?: 1)) * $discount;
            $itemTaxable = (float) max(0.0, $itemSubtotal - $itemDiscount);
            $gstRate = (float) ($item['gst_rate'] ?? 12.0);

            if ($isIntraState) {
                // CGST + SGST (half rate each)
                $halfRate = $gstRate / 2.0;
                $cgst = round(($itemTaxable * $halfRate) / 100.0, 2);
                $sgst = round(($itemTaxable * $halfRate) / 100.0, 2);
                $igst = 0.0;
            } else {
                // IGST (full rate)
                $cgst = 0.0;
                $sgst = 0.0;
                $igst = round(($itemTaxable * $gstRate) / 100.0, 2);
            }

            $totalCgst += $cgst;
            $totalSgst += $sgst;
            $totalIgst += $igst;

            $calculatedItems[] = array_merge($item, [
                'discount' => round($itemDiscount, 2),
                'taxable_value' => round($itemTaxable, 2),
                'gst_rate' => $gstRate,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => round($itemTaxable + $cgst + $sgst + $igst, 2),
            ]);
        }

        $totalTax = $totalCgst + $totalSgst + $totalIgst;

        // Shipping logic: Free over threshold (default ₹999)
        $shippingFee = $subtotal >= $shippingThreshold ? 0.0 : $defaultShipping;

        // Gateway fee handling: configurable by admin (absorbed vs customer_charged)
        $gatewayFeeMode = Setting::get('gateway_fee_mode', 'absorbed');
        $gatewayFeePercent = (float) Setting::get('gateway_fee_percent', 0);
        $gatewayFee = 0.0;

        if ($gatewayFeeMode === 'customer_charged' && $gatewayFeePercent > 0) {
            $baseForGateway = $taxableAmount + $totalTax + $shippingFee;
            $gatewayFee = round(($baseForGateway * $gatewayFeePercent) / 100.0, 2);
        }

        $grandTotal = round($taxableAmount + $totalTax + $shippingFee + $gatewayFee, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'taxable_amount' => round($taxableAmount, 2),
            'is_intra_state' => $isIntraState,
            'seller_state' => $sellerState,
            'customer_state' => $customerState,
            'cgst_amount' => round($totalCgst, 2),
            'sgst_amount' => round($totalSgst, 2),
            'igst_amount' => round($totalIgst, 2),
            'total_tax' => round($totalTax, 2),
            'shipping_fee' => round($shippingFee, 2),
            'gateway_fee' => round($gatewayFee, 2),
            'grand_total' => $grandTotal,
            'items' => $calculatedItems,
        ];
    }
}
