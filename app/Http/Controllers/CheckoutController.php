<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CouponService;
use App\Services\CustomerLeadService;
use App\Services\InvoiceService;
use App\Services\PincodeService;
use App\Services\RazorpayService;
use App\Services\ShippingCalculationService;
use App\Services\StockService;
use App\Services\TaxCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CouponService $couponService,
        protected TaxCalculationService $taxService,
        protected RazorpayService $razorpayService,
        protected StockService $stockService,
        protected InvoiceService $invoiceService,
        protected CustomerLeadService $leadService,
        protected PincodeService $pincodeService,
        protected ShippingCalculationService $shippingService
    ) {}

    /**
     * Show Checkout Page.
     */
    public function index(): View
    {
        $content = require resource_path('content.php');
        $user = Auth::user();
        $states = TaxCalculationService::STATES;
        $razorpayKeyId = $this->razorpayService->getKeyId();
        $shippingSettings = $this->shippingService->getSettings();

        return view('checkout.index', [
            'content' => $content,
            'user' => $user,
            'states' => $states,
            'razorpayKeyId' => $razorpayKeyId,
            'shippingSettings' => $shippingSettings,
        ]);
    }

    /**
     * Look up Indian PIN code details (city, district, post office, state).
     */
    public function lookupPincode(Request $request): JsonResponse
    {
        $pincode = (string) $request->query('pincode', '');
        $result = $this->pincodeService->lookup($pincode);

        return response()->json($result, $result['valid'] ? 200 : 422);
    }

    /**
     * Validate offer code via AJAX.
     */
    public function validateCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->couponService->validate($validated['code'], (float) $validated['amount']);

        return response()->json($result);
    }

    /**
     * Calculate financial totals via AJAX (including server-side distance shipping).
     */
    public function calculateTotals(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $couponCode = $request->input('coupon_code');
        $customerState = (string) ($request->input('shipping_state') ?: $request->input('state', 'Maharashtra'));
        $shippingPin = trim((string) $request->input('shipping_pin_code', ''));

        $subtotal = 0.0;
        $itemsWithGst = [];

        foreach ($items as $item) {
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $price = (float) ($item['price'] ?? 0);
            $subtotal += ($price * $qty);

            $product = null;
            if (! empty($item['id'])) {
                $product = Product::find($item['id']);
            }

            $itemsWithGst[] = [
                'id' => $item['id'] ?? null,
                'name' => $item['name'] ?? 'Product',
                'price' => $price,
                'quantity' => $qty,
                'gst_rate' => $product?->gst_rate ?? 12.0,
            ];
        }

        $discount = 0.0;
        $couponMessage = null;
        $couponValid = false;

        if (! empty($couponCode)) {
            $couponResult = $this->couponService->validate($couponCode, $subtotal);
            if ($couponResult['valid']) {
                $discount = $couponResult['discount'];
                $couponValid = true;
                $couponMessage = $couponResult['message'];
            } else {
                $couponMessage = $couponResult['message'];
            }
        }

        $shippingCalculated = false;
        $shippingError = null;
        $shippingMessage = 'Enter a 6-digit delivery PIN code to calculate shipping.';
        $shippingResult = null;
        $shippingOverride = 0.0;

        if ($shippingPin !== '') {
            $shippingResult = $this->shippingService->calculateForAddress([
                'pincode' => $shippingPin,
                'address_line1' => $request->input('shipping_address_line1'),
                'address_line2' => $request->input('shipping_address_line2'),
                'city' => $request->input('shipping_city'),
                'state' => $customerState,
            ]);

            if ($shippingResult['determined']) {
                $shippingCalculated = true;
                $shippingOverride = (float) $shippingResult['shipping_fee'];
                $shippingMessage = (string) $shippingResult['message'];
            } else {
                $shippingError = (string) $shippingResult['message'];
                $shippingMessage = $shippingError;
            }
        }

        $calc = $this->taxService->calculate($itemsWithGst, $discount, $customerState, 999.0, 99.0, $shippingOverride);

        return response()->json(array_merge($calc, [
            'shipping_fee' => $shippingCalculated ? $calc['shipping_fee'] : null,
            'shipping_calculated' => $shippingCalculated,
            'shipping_error' => $shippingError,
            'shipping_message' => $shippingMessage,
            'distance_km' => $shippingResult['distance_km'] ?? null,
            'free_distance_km' => $shippingResult['free_distance_km'] ?? null,
            'charge_per_km' => $shippingResult['charge_per_km'] ?? null,
            'billable_distance_km' => $shippingResult['billable_distance_km'] ?? null,
            'distance_source' => $shippingResult['distance_source'] ?? null,
            'coupon_valid' => $couponValid,
            'coupon_message' => $couponMessage,
        ]));
    }

    /**
     * Create Order and initialize Razorpay Order ID.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required'],
            'items.*.name' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.custom_size' => ['nullable', 'string'],
            'items.*.image' => ['nullable', 'string'],

            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_type' => ['nullable', 'string', 'in:guest,registered,google'],

            'shipping_address_line1' => ['required', 'string', 'max:255'],
            'shipping_address_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_state' => ['required', 'string', 'max:100'],
            'shipping_pin_code' => ['required', 'string', 'regex:/^[1-9]\d{5}$/'],

            'billing_same_as_shipping' => ['boolean'],
            'billing_address_line1' => ['nullable', 'string', 'max:255'],
            'billing_address_line2' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:100'],
            'billing_state' => ['nullable', 'string', 'max:100'],
            'billing_pin_code' => ['nullable', 'string', 'max:20'],

            'coupon_code' => ['nullable', 'string'],
        ], [
            'shipping_pin_code.regex' => 'Please enter a valid 6-digit Indian delivery PIN code.',
        ]);

        // 1. Stock check
        $stockCheck = $this->stockService->validateStockAvailability($validated['items']);
        if (! $stockCheck['success']) {
            return response()->json([
                'success' => false,
                'message' => implode(' ', $stockCheck['errors']),
            ], 422);
        }

        // 2. Prepare items with GST info
        $itemsWithGst = [];
        $subtotal = 0.0;
        foreach ($validated['items'] as $item) {
            $qty = (int) $item['quantity'];
            $price = (float) $item['price'];
            $subtotal += ($price * $qty);

            $product = Product::find($item['id']);

            $itemsWithGst[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'sku' => $product?->sku,
                'hsn_code' => $product?->hsn_code ?? '6304',
                'price' => $price,
                'quantity' => $qty,
                'gst_rate' => (float) ($product?->gst_rate ?? 12.0),
                'custom_size' => $item['custom_size'] ?? null,
                'image' => $item['image'] ?? ($product?->image ?? null),
            ];
        }

        // 3. Backend Coupon Validation (Never trust frontend discount amounts)
        $coupon = null;
        $discount = 0.0;
        if (! empty($validated['coupon_code'])) {
            $couponResult = $this->couponService->validate($validated['coupon_code'], $subtotal);
            if ($couponResult['valid']) {
                $coupon = $couponResult['coupon'];
                $discount = $couponResult['discount'];
            }
        }

        // 4. Server-side Distance & Shipping Calculation (Never trust frontend shipping charges)
        $shippingResult = $this->shippingService->calculateForAddress([
            'pincode' => $validated['shipping_pin_code'],
            'address_line1' => $validated['shipping_address_line1'],
            'address_line2' => $validated['shipping_address_line2'] ?? null,
            'city' => $validated['shipping_city'],
            'state' => $validated['shipping_state'],
        ]);

        if (! $shippingResult['determined']) {
            return response()->json([
                'success' => false,
                'message' => $shippingResult['message'] ?? 'Unable to verify delivery PIN code or calculate shipping distance.',
            ], 422);
        }

        // 5. Tax and Totals Calculation
        $customerState = $this->pincodeService->normalizeStateName($validated['shipping_state']);
        $calc = $this->taxService->calculate(
            $itemsWithGst,
            $discount,
            $customerState,
            999.0,
            99.0,
            (float) $shippingResult['shipping_fee']
        );

        // 6. Build shipping and billing address payloads
        $shippingAddress = [
            'full_name' => $validated['customer_name'],
            'phone' => $validated['customer_phone'],
            'email' => $validated['customer_email'],
            'address_line1' => $validated['shipping_address_line1'],
            'address_line2' => $validated['shipping_address_line2'] ?? null,
            'city' => $validated['shipping_city'],
            'state' => $customerState,
            'pin_code' => $validated['shipping_pin_code'],
            'distance_km' => $shippingResult['distance_km'],
            'distance_source' => $shippingResult['distance_source'],
        ];

        $billingSame = $request->boolean('billing_same_as_shipping', true);
        $billingAddress = $billingSame ? $shippingAddress : [
            'full_name' => $validated['customer_name'],
            'phone' => $validated['customer_phone'],
            'email' => $validated['customer_email'],
            'address_line1' => $validated['billing_address_line1'] ?? $validated['shipping_address_line1'],
            'address_line2' => $validated['billing_address_line2'] ?? null,
            'city' => $validated['billing_city'] ?? $validated['shipping_city'],
            'state' => $validated['billing_state'] ?? $validated['shipping_state'],
            'pin_code' => $validated['billing_pin_code'] ?? $validated['shipping_pin_code'],
        ];

        // Customer Type
        $user = Auth::user();
        $customerType = 'guest';
        if ($user) {
            $customerType = $user->google_id ? 'google' : 'registered';
        }

        $orderNumber = 'ORD-'.Carbon::now()->format('Ymd').'-'.strtoupper(Str::random(6));

        // 6. Persist Order in Pending status inside transaction
        $order = DB::transaction(function () use (
            $orderNumber,
            $user,
            $customerType,
            $validated,
            $shippingAddress,
            $billingAddress,
            $calc,
            $coupon
        ) {
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user?->id,
                'customer_type' => $customerType,
                'customer_name' => $validated['customer_name'],
                'customer_email' => strtolower(trim($validated['customer_email'])),
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'customer_gstin' => null,
                'subtotal' => $calc['subtotal'],
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'discount_amount' => $calc['discount'],
                'taxable_amount' => $calc['taxable_amount'],
                'cgst_amount' => $calc['cgst_amount'],
                'sgst_amount' => $calc['sgst_amount'],
                'igst_amount' => $calc['igst_amount'],
                'total_tax' => $calc['total_tax'],
                'shipping_fee' => $calc['shipping_fee'],
                'gateway_fee' => $calc['gateway_fee'],
                'grand_total' => $calc['grand_total'],
                'currency' => 'INR',
                'payment_status' => 'pending',
                'order_status' => 'placed',
                'payment_method' => 'Razorpay',
            ]);

            foreach ($calc['items'] as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['id'] ?? null,
                    'product_name' => $itemData['name'],
                    'sku' => $itemData['sku'] ?? null,
                    'hsn_code' => $itemData['hsn_code'] ?? '6304',
                    'unit_price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'discount' => $itemData['discount'] ?? 0,
                    'taxable_value' => $itemData['taxable_value'],
                    'gst_rate' => $itemData['gst_rate'],
                    'cgst_amount' => $itemData['cgst_amount'],
                    'sgst_amount' => $itemData['sgst_amount'],
                    'igst_amount' => $itemData['igst_amount'],
                    'total_amount' => $itemData['total_amount'],
                    'custom_size' => $itemData['custom_size'] ?? null,
                    'image' => $itemData['image'] ?? null,
                ]);
            }

            return $order;
        });

        // 7. Initialize Razorpay Order
        $razorpayOrder = $this->razorpayService->createOrder($order);

        if (! $razorpayOrder['success']) {
            return response()->json([
                'success' => false,
                'message' => $razorpayOrder['error'] ?? 'Payment gateway could not initialize.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'razorpay_order_id' => $razorpayOrder['order_id'],
            'amount' => $razorpayOrder['amount'],
            'currency' => $razorpayOrder['currency'],
            'razorpay_key_id' => $this->razorpayService->getKeyId(),
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'business_name' => Setting::get('seller_name', 'Menahem'),
        ]);
    }

    /**
     * Verify payment signature from Razorpay Checkout.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $order = Order::where('order_number', $validated['order_number'])->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        // Idempotency check: if already paid, do not re-process
        if ($order->isPaid()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('checkout.confirmation', ['orderNumber' => $order->order_number]),
            ]);
        }

        // Verify Razorpay signature
        $validSignature = $this->razorpayService->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature']
        );

        if (! $validSignature) {
            $order->update([
                'payment_status' => 'failed',
                'notes' => 'Signature verification failed',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment signature verification failed. Please contact support.',
            ], 400);
        }

        // Mark paid and process order within transaction
        DB::transaction(function () use ($order, $validated) {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'paid_at' => Carbon::now(),
            ]);

            // Increment coupon usage
            if ($order->coupon) {
                $this->couponService->incrementUsage($order->coupon);
            }

            // Deduct stock safely with locking
            $this->stockService->deductStockForOrder($order);

            // Generate GST Tax Invoice
            $this->invoiceService->generateForOrder($order);

            // Record Unified Customer Lead
            $this->leadService->recordLeadFromOrder($order);
        });

        return response()->json([
            'success' => true,
            'redirect_url' => route('checkout.confirmation', ['orderNumber' => $order->order_number]),
        ]);
    }

    /**
     * Display Order Confirmation Page.
     */
    public function confirmation(string $orderNumber): View
    {
        $content = require resource_path('content.php');
        $order = Order::where('order_number', $orderNumber)->with(['items', 'invoice'])->firstOrFail();

        return view('checkout.confirmation', [
            'content' => $content,
            'order' => $order,
            'invoice' => $order->invoice,
        ]);
    }
}
