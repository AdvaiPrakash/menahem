<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    /**
     * Get Razorpay Key ID from database settings or environment.
     */
    public function getKeyId(): string
    {
        return Setting::get('razorpay_key_id') ?: env('RAZORPAY_KEY_ID', '');
    }

    /**
     * Get Razorpay Key Secret from database settings or environment.
     */
    public function getKeySecret(): string
    {
        return Setting::get('razorpay_key_secret') ?: env('RAZORPAY_KEY_SECRET', '');
    }

    /**
     * Check if Razorpay integration is enabled.
     */
    public function isEnabled(): bool
    {
        return Setting::get('razorpay_status', 'enabled') === 'enabled';
    }

    /**
     * Get Razorpay mode ('test' or 'live').
     */
    public function getMode(): string
    {
        return Setting::get('razorpay_mode', 'test');
    }

    /**
     * Create a Razorpay Order through Razorpay REST API v1.
     *
     * @return array{success: bool, order_id?: string, amount?: int, currency?: string, error?: string}
     */
    public function createOrder(Order $order): array
    {
        $keyId = $this->getKeyId();
        $keySecret = $this->getKeySecret();

        if (empty($keyId) || empty($keySecret)) {
            // Test fallback simulation if credentials not yet configured
            $mockOrderId = 'order_sim_'.substr(md5($order->order_number.time()), 0, 14);
            $order->update(['razorpay_order_id' => $mockOrderId]);

            return [
                'success' => true,
                'order_id' => $mockOrderId,
                'amount' => (int) round($order->grand_total * 100),
                'currency' => 'INR',
                'simulated' => true,
            ];
        }

        try {
            // Amount in paise (1 INR = 100 paise)
            $amountInPaise = (int) round($order->grand_total * 100);

            $response = Http::withBasicAuth($keyId, $keySecret)
                ->timeout(15)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                    'receipt' => $order->order_number,
                    'notes' => [
                        'order_id' => (string) $order->id,
                        'customer_name' => $order->customer_name,
                        'customer_email' => $order->customer_email,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $razorpayOrderId = $data['id'] ?? null;
                $order->update(['razorpay_order_id' => $razorpayOrderId]);

                return [
                    'success' => true,
                    'order_id' => $razorpayOrderId,
                    'amount' => $amountInPaise,
                    'currency' => 'INR',
                ];
            }

            Log::error('Razorpay order creation failed: '.$response->body());

            return [
                'success' => false,
                'error' => $response->json('error.description') ?? 'Payment gateway order creation failed.',
            ];
        } catch (\Throwable $e) {
            Log::error('Razorpay exception: '.$e->getMessage());

            return [
                'success' => false,
                'error' => 'Unable to connect to payment gateway: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment signature returned by Razorpay Checkout.
     */
    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $signature): bool
    {
        $keySecret = $this->getKeySecret();

        // In test simulation mode with placeholder keys
        if (str_starts_with($razorpayOrderId, 'order_sim_')) {
            return true;
        }

        if (empty($keySecret)) {
            return true;
        }

        $expectedSignature = hash_hmac('sha256', "{$razorpayOrderId}|{$razorpayPaymentId}", $keySecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify Razorpay Webhook signature against raw request body.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signature, ?string $webhookSecret = null): bool
    {
        $secret = $webhookSecret ?: Setting::get('razorpay_webhook_secret', $this->getKeySecret());

        if (empty($secret) || empty($signature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $rawPayload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}
