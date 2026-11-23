<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CustomerLeadService;
use App\Services\InvoiceService;
use App\Services\RazorpayService;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected RazorpayService $razorpayService,
        protected StockService $stockService,
        protected InvoiceService $invoiceService,
        protected CustomerLeadService $leadService
    ) {}

    /**
     * Handle incoming Razorpay Webhook notifications.
     */
    public function handle(Request $request): JsonResponse
    {
        $signature = $request->header('X-Razorpay-Signature');
        $rawPayload = $request->getContent();

        if (empty($signature) || ! $this->razorpayService->verifyWebhookSignature($rawPayload, $signature)) {
            Log::warning('Razorpay webhook invalid signature attempt.');

            return response()->json(['status' => 'invalid_signature'], 400);
        }

        $data = json_decode($rawPayload, true);
        $event = $data['event'] ?? '';
        $payload = $data['payload'] ?? [];

        Log::info("Razorpay webhook event received: {$event}");

        if ($event === 'payment.captured' || $event === 'order.paid') {
            $paymentEntity = $payload['payment']['entity'] ?? [];
            $razorpayOrderId = $paymentEntity['order_id'] ?? null;
            $razorpayPaymentId = $paymentEntity['id'] ?? null;

            if ($razorpayOrderId) {
                $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();

                if ($order && ! $order->isPaid()) {
                    DB::transaction(function () use ($order, $razorpayPaymentId) {
                        $order->update([
                            'payment_status' => 'paid',
                            'order_status' => 'confirmed',
                            'razorpay_payment_id' => $razorpayPaymentId ?: $order->razorpay_payment_id,
                            'paid_at' => Carbon::now(),
                        ]);

                        if ($order->coupon) {
                            $order->coupon->increment('used_count');
                        }

                        $this->stockService->deductStockForOrder($order);
                        $this->invoiceService->generateForOrder($order);
                        $this->leadService->recordLeadFromOrder($order);
                    });
                }
            }
        } elseif ($event === 'payment.failed') {
            $paymentEntity = $payload['payment']['entity'] ?? [];
            $razorpayOrderId = $paymentEntity['order_id'] ?? null;

            if ($razorpayOrderId) {
                $order = Order::where('razorpay_order_id', $razorpayOrderId)->first();
                if ($order && ! $order->isPaid()) {
                    $order->update([
                        'payment_status' => 'failed',
                        'notes' => 'Payment failed via webhook notification: '.($paymentEntity['error_description'] ?? 'Unknown error'),
                    ]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
