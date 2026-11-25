<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'hsn_code', 'price', 'gst_rate', 'stock_quantity', 'image']);

        $sellerState = (string) Setting::get('seller_state', 'Maharashtra');

        return view('admin.billing.create', [
            'products' => $products,
            'states' => array_keys(TaxCalculationService::STATES),
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_gstin' => ['nullable', 'string', 'max:20'],
            'shipping_address_line1' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['required', 'string', 'max:100'],
            'shipping_pin_code' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'string', 'in:Cash,UPI,Card,Bank Transfer,Razorpay'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_fee' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.custom_size' => ['nullable', 'string', 'max:100'],
        ]);

        $productIds = collect($validated['items'])->pluck('product_id')->unique()->values();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $stockItems = [];
        $itemsWithGst = [];

        foreach ($validated['items'] as $row) {
            $product = $products->get((int) $row['product_id']);
            if (! $product) {
                continue;
            }

            $qty = (int) $row['quantity'];
            $unitPrice = isset($row['unit_price']) && $row['unit_price'] !== ''
                ? (float) $row['unit_price']
                : (float) $product->price;

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
                'gst_rate' => (float) ($product->gst_rate ?? 12.0),
                'custom_size' => $row['custom_size'] ?? null,
                'image' => $product->image,
            ];
        }

        $stockCheck = $this->stockService->validateStockAvailability($stockItems);
        if (! $stockCheck['success']) {
            return back()
                ->withInput()
                ->withErrors(['items' => implode(' ', $stockCheck['errors'])]);
        }

        $customerState = $this->pincodeService->normalizeStateName($validated['shipping_state']);
        $discount = (float) ($validated['discount_amount'] ?? 0);
        $shippingFee = (float) ($validated['shipping_fee'] ?? 0);

        $calc = $this->taxService->calculate(
            $itemsWithGst,
            $discount,
            $customerState,
            999.0,
            99.0,
            $shippingFee
        );

        $address = [
            'full_name' => $validated['customer_name'],
            'phone' => $validated['customer_phone'],
            'email' => $validated['customer_email'] ?? null,
            'address_line1' => ($validated['shipping_address_line1'] ?? null) ?: 'Manual Billing / Store Counter',
            'address_line2' => null,
            'city' => ($validated['shipping_city'] ?? null) ?: 'Mumbai',
            'state' => $customerState,
            'pin_code' => ($validated['shipping_pin_code'] ?? null) ?: '000000',
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
            $calc
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
                'customer_gstin' => $validated['customer_gstin'] ?? null,
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
                'gateway_fee' => $calc['gateway_fee'],
                'grand_total' => $calc['grand_total'],
                'currency' => 'INR',
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'payment_method' => $validated['payment_method'],
                'razorpay_payment_id' => ($validated['payment_reference'] ?? null) ?: null,
                'paid_at' => Carbon::now(),
                'notes' => $validated['notes'] ?? 'Created via Admin Manual Billing',
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
