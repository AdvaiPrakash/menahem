<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClearanceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CustomerLeadService;
use App\Services\InvoiceService;
use App\Services\PincodeService;
use App\Services\StockService;
use App\Services\TaxCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ManualBillingController extends Controller
{
    public function __construct(
        protected TaxCalculationService $taxService,
        protected StockService $stockService,
        protected InvoiceService $invoiceService,
        protected CustomerLeadService $leadService,
        protected PincodeService $pincodeService
    ) {}

    /**
     * Show the Manual Billing / Offline Order creation form.
     */
    public function create(): View
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $productsQuery = Product::query()->where('in_stock', true)->orderBy('name');
        if (ClearanceItem::tableExists()) {
            $productsQuery->with('clearanceItem');
        }

        $products = $productsQuery
            ->get(['id', 'name', 'sku', 'hsn_code', 'price', 'numeric_price', 'gst_rate', 'stock_quantity', 'image'])
            ->map(static fn (Product $p): array => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'hsn_code' => $p->hsn_code,
                'price' => $p->effectivePrice(),
                'gst_rate' => (float) ($p->gst_rate ?? 5.0),
                'stock_quantity' => (int) $p->stock_quantity,
                'image' => $p->image,
            ])
            ->values();

        $sellerState = (string) Setting::get('seller_state', 'Maharashtra');

        return view('admin.billing.create', [
            'products' => $products,
            'sellerState' => $sellerState,
        ]);
    }

    /**
     * Create a paid manual order, deduct stock, and generate its GST invoice.
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->canEditContent(), 403);

        $validated = $request->validate([
            'billing_mode' => ['nullable', 'string', 'in:manual_order,walk_in'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'shipping_address_line1' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_pin_code' => ['nullable', 'string', 'max:20'],
            'is_split_payment' => ['nullable', 'boolean'],
            'payment_method' => ['required_unless:is_split_payment,1', 'nullable', 'string', 'in:Cash,UPI,Card,Bank Transfer,Razorpay'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'split_payments' => ['nullable', 'array'],
            'split_payments.*.method' => ['required_with:split_payments', 'string', 'in:Cash,UPI,Card'],
            'split_payments.*.amount' => ['required_with:split_payments', 'numeric', 'gt:0'],
            'edited_final_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.custom_size' => ['nullable', 'string', 'max:100'],
        ]);

        $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
        $productsQuery = Product::whereIn('id', $productIds);
        if (ClearanceItem::tableExists()) {
            $productsQuery->with('clearanceItem');
        }
        $products = $productsQuery->get()->keyBy('id');

        $stockItems = [];
        $itemsWithGst = [];
        $originalSubtotal = 0.0;

        foreach ($validated['items'] as $row) {
            $product = $products->get((int) $row['product_id']);
            if (! $product) {
                continue;
            }

            $qty = (int) $row['quantity'];
            $unitPrice = isset($row['unit_price']) && $row['unit_price'] !== ''
                ? (float) $row['unit_price']
                : $product->effectivePrice();
            $originalSubtotal += ($unitPrice * $qty);

            $stockItems[] = [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => $qty,
            ];

            $itemsWithGst[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'hsn_code' => $product->hsn_code ?? '6304',
                'price' => $unitPrice,
                'quantity' => $qty,
                'gst_rate' => (float) ($product->gst_rate ?? 5.0),
                'custom_size' => $row['custom_size'] ?? null,
                'image' => $product->image,
            ];
        }

        $originalSubtotal = round($originalSubtotal, 2);

        $stockCheck = $this->stockService->validateStockAvailability($stockItems);
        if (! $stockCheck['success']) {
            return back()
                ->withInput()
                ->withErrors(['items' => implode(' ', $stockCheck['errors'])]);
        }

        $sellerState = $this->pincodeService->normalizeStateName((string) Setting::get('seller_state', 'Maharashtra'));
        $sellerCity = (string) Setting::get('shipping_origin_city', 'Mumbai');

        // Auto-calculate discount from edited_final_amount when provided
        if (isset($validated['edited_final_amount']) && $validated['edited_final_amount'] !== '') {
            $editedFinal = round((float) $validated['edited_final_amount'], 2);
            if ($editedFinal > $originalSubtotal) {
                return back()
                    ->withInput()
                    ->withErrors(['edited_final_amount' => 'Final payable amount cannot exceed the original bill amount (₹'.number_format($originalSubtotal, 2).').']);
            }
            $discount = round(max(0.0, $originalSubtotal - $editedFinal), 2);
        } else {
            $discount = round(min(max(0.0, (float) ($validated['discount_amount'] ?? 0)), $originalSubtotal), 2);
        }

        $calc = $this->taxService->calculate(
            $itemsWithGst,
            $discount,
            $sellerState,
            999.0,
            0.0,
            0.0,
            false
        );

        $isSplit = $request->boolean('is_split_payment');
        $paymentDetails = [];
        $paymentMethodLabel = (string) ($validated['payment_method'] ?? 'Cash');

        if ($isSplit) {
            $rawSplits = collect($validated['split_payments'] ?? [])
                ->map(static fn (array $entry): array => [
                    'method' => (string) $entry['method'],
                    'amount' => round((float) $entry['amount'], 2),
                ])
                ->values();

            if ($rawSplits->count() < 2) {
                return back()
                    ->withInput()
                    ->withErrors(['split_payments' => 'Split payment requires at least two payment methods.']);
            }

            if ($rawSplits->pluck('method')->unique()->count() !== $rawSplits->count()) {
                return back()
                    ->withInput()
                    ->withErrors(['split_payments' => 'Duplicate payment methods are not allowed in split payment.']);
            }

            $splitSum = round((float) $rawSplits->sum('amount'), 2);
            if (abs($splitSum - (float) $calc['grand_total']) > 0.01) {
                return back()
                    ->withInput()
                    ->withErrors(['split_payments' => 'Split payment total (₹'.number_format($splitSum, 2).') must match the final payable amount (₹'.number_format($calc['grand_total'], 2).').']);
            }

            $paymentDetails = $rawSplits->all();
            $paymentMethodLabel = 'Split ('.$rawSplits->pluck('method')->implode(' + ').')';
        } else {
            $paymentDetails = [
                [
                    'method' => $paymentMethodLabel,
                    'amount' => (float) $calc['grand_total'],
                ],
            ];
        }

        $billingMode = (string) ($validated['billing_mode'] ?? 'walk_in');
        $isWalkIn = $billingMode === 'walk_in';

        $address = [
            'billing_mode' => $billingMode,
            'full_name' => $validated['customer_name'],
            'phone' => $validated['customer_phone'],
            'email' => $validated['customer_email'] ?? null,
            'address_line1' => $isWalkIn ? null : (($validated['shipping_address_line1'] ?? null) ?: null),
            'address_line2' => null,
            'city' => $isWalkIn ? null : (($validated['shipping_city'] ?? null) ?: $sellerCity),
            'state' => $isWalkIn ? null : $sellerState,
            'pin_code' => $isWalkIn ? null : (($validated['shipping_pin_code'] ?? null) ?: null),
        ];

        $orderNumber = 'ORD-'.Carbon::now()->format('Ymd').'-'.strtoupper(Str::random(6));
        $customerEmail = ! empty($validated['customer_email'])
            ? strtolower(trim($validated['customer_email']))
            : 'walkin+'.strtolower($orderNumber).'@menahem.local';

        $order = DB::transaction(function () use (
            $orderNumber,
            $validated,
            $customerEmail,
            $address,
            $calc,
            $paymentMethodLabel,
            $paymentDetails
        ): Order {
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => null,
                'customer_type' => 'guest',
                'customer_name' => $validated['customer_name'],
                'customer_email' => $customerEmail,
                'customer_phone' => $validated['customer_phone'],
                'shipping_address' => $address,
                'billing_address' => $address,
                'customer_gstin' => null,
                'subtotal' => $calc['subtotal'],
                'coupon_id' => null,
                'coupon_code' => null,
                'discount_amount' => $calc['discount'],
                'taxable_amount' => $calc['taxable_amount'],
                'cgst_amount' => $calc['cgst_amount'],
                'sgst_amount' => $calc['sgst_amount'],
                'igst_amount' => $calc['igst_amount'],
                'total_tax' => $calc['total_tax'],
                'shipping_fee' => $calc['shipping_fee'],
                'gateway_fee' => 0.0,
                'grand_total' => $calc['grand_total'],
                'currency' => 'INR',
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'payment_method' => $paymentMethodLabel,
                'payment_details' => $paymentDetails,
                'razorpay_payment_id' => ($validated['payment_reference'] ?? null) ?: null,
                'paid_at' => Carbon::now(),
                'notes' => null,
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

            $this->stockService->deductStockForOrder($order);
            $this->invoiceService->generateForOrder($order);
            $this->leadService->recordLeadFromOrder($order);

            return $order;
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', "Manual bill #{$order->order_number} created and GST invoice generated.");
    }
}
