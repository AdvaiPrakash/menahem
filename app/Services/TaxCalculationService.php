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
     * Calculate order financial breakdown using GST-inclusive product pricing,
     * extracting taxable value and intra/inter-state GST without adding GST on top of product prices.
     *
     * @param  array<int, array{price: float, quantity: int, gst_rate?: float}>  $items
     * @return array<string, mixed>
     */
    public function calculate(
        array $items,
        float $discount = 0.0,
        string $customerState = 'Maharashtra',
        float $shippingThreshold = 999.0,
        float $defaultShipping = 99.0,
        ?float $shippingFeeOverride = null,
        bool $applyGatewayFee = true
    ): array {
        $sellerState = Setting::get('seller_state', 'Maharashtra');
        $isIntraState = strcasecmp(trim($customerState), trim($sellerState)) === 0;

        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += ((float) $item['price']) * ((int) $item['quantity']);
        }
        $subtotal = round($subtotal, 2);

        // Discount cannot exceed GST-inclusive subtotal
        $discount = round((float) min(max(0.0, $discount), $subtotal), 2);
        $netProductTotal = round(max(0.0, $subtotal - $discount), 2);

        // Extract itemized taxable value and GST components from GST-inclusive prices
        $totalTaxable = 0.0;
        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalIgst = 0.0;

        $calculatedItems = [];
        $remainingDiscount = $discount;
        $itemCount = count($items);

        foreach ($items as $idx => $item) {
            $itemSubtotal = round(((float) $item['price']) * ((int) $item['quantity']), 2);
            if ($idx === $itemCount - 1) {
                $itemDiscount = round(min($remainingDiscount, $itemSubtotal), 2);
            } else {
                $itemDiscount = $subtotal > 0 ? round(($itemSubtotal / $subtotal) * $discount, 2) : 0.0;
                $itemDiscount = min($itemDiscount, $remainingDiscount, $itemSubtotal);
                $remainingDiscount = round($remainingDiscount - $itemDiscount, 2);
            }

            $itemInclusiveTotal = round(max(0.0, $itemSubtotal - $itemDiscount), 2);
            $gstRate = (float) ($item['gst_rate'] ?? 5.0);

            // Extract taxable value from GST-inclusive amount: inclusive / (1 + rate/100)
            $divisor = 1.0 + ($gstRate / 100.0);
            $itemTaxable = $divisor > 0 ? round($itemInclusiveTotal / $divisor, 2) : $itemInclusiveTotal;
            $itemTotalTax = round(max(0.0, $itemInclusiveTotal - $itemTaxable), 2);

            if ($isIntraState) {
                $cgst = round($itemTotalTax / 2.0, 2);
                $sgst = round($itemTotalTax - $cgst, 2);
                $igst = 0.0;
            } else {
                $cgst = 0.0;
                $sgst = 0.0;
                $igst = $itemTotalTax;
            }

            $totalTaxable += $itemTaxable;
            $totalCgst += $cgst;
            $totalSgst += $sgst;
            $totalIgst += $igst;

            $calculatedItems[] = array_merge($item, [
                'discount' => $itemDiscount,
                'taxable_value' => $itemTaxable,
                'gst_rate' => $gstRate,
                'cgst_amount' => $cgst,
                'sgst_amount' => $sgst,
                'igst_amount' => $igst,
                'total_amount' => $itemInclusiveTotal,
            ]);
        }

        $totalTax = round($totalCgst + $totalSgst + $totalIgst, 2);

        // Shipping logic: Use distance-based override when supplied, else fallback to threshold rule
        $shippingFee = $shippingFeeOverride !== null
            ? max(0.0, round($shippingFeeOverride, 2))
            : ($subtotal >= $shippingThreshold ? 0.0 : $defaultShipping);

        // Gateway fee handling: configurable by admin (absorbed vs customer_charged), excluded from Manual Billing
        $gatewayFeeMode = Setting::get('gateway_fee_mode', 'absorbed');
        $gatewayFeePercent = (float) Setting::get('gateway_fee_percent', 0);
        $gatewayFee = 0.0;

        if ($applyGatewayFee && $gatewayFeeMode === 'customer_charged' && $gatewayFeePercent > 0) {
            $baseForGateway = $netProductTotal + $shippingFee;
            $gatewayFee = round(($baseForGateway * $gatewayFeePercent) / 100.0, 2);
        }

        // Product prices already include GST; do NOT add $totalTax again to $netProductTotal
        $grandTotal = round($netProductTotal + $shippingFee + $gatewayFee, 2);

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'taxable_amount' => round($totalTaxable, 2),
            'is_intra_state' => $isIntraState,
            'seller_state' => $sellerState,
            'customer_state' => $customerState,
            'cgst_amount' => round($totalCgst, 2),
            'sgst_amount' => round($totalSgst, 2),
            'igst_amount' => round($totalIgst, 2),
            'total_tax' => $totalTax,
            'shipping_fee' => round($shippingFee, 2),
            'gateway_fee' => round($gatewayFee, 2),
            'grand_total' => $grandTotal,
            'items' => $calculatedItems,
        ];
    }
}
