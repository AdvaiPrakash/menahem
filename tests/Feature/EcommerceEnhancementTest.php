<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CustomerLead;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\CouponService;
use App\Services\InvoiceService;
use App\Services\RazorpayService;
use App\Services\StockService;
use App\Services\TaxCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => UserRole::Admin,
        ]);

        $this->category = Category::create([
            'name' => 'Bedsheets',
            'slug' => 'bedsheets',
        ]);

        $this->product = Product::create([
            'name' => 'Royal King Bedsheet',
            'slug' => 'royal-king-bedsheet',
            'category_id' => $this->category->id,
            'category_slug' => $this->category->slug,
            'sku' => 'MNH-BED-001',
            'hsn_code' => '6304',
            'price' => 1200.00,
            'gst_rate' => 12.00,
            'stock_quantity' => 20,
            'opening_stock' => 20,
            'sold_quantity' => 0,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);

        Setting::setMany([
            'seller_business_name' => 'MENAHEM TEXTILES',
            'seller_state' => 'Gujarat',
            'seller_state_code' => '24',
            'seller_gstin' => '24AAAAA0000A1Z5',
            'razorpay_key_id' => 'rzp_test_placeholder',
            'razorpay_key_secret' => 'rzp_secret_placeholder',
            'razorpay_mode' => 'test',
            'shipping_free_threshold' => 999,
            'shipping_default_charge' => 99,
        ]);
    }

    // ==========================================
    // MODULE 1: COUPONS & DISCOUNTS
    // ==========================================

    public function test_admin_can_create_and_manage_coupons(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), [
            'code' => 'SAVE20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'max_discount_amount' => 500,
            'min_order_amount' => 500,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.coupons.index'));
        $this->assertDatabaseHas('coupons', ['code' => 'SAVE20', 'discount_type' => 'percentage']);
    }

    public function test_coupon_service_calculates_percentage_and_fixed_discounts(): void
    {
        $service = app(CouponService::class);

        Coupon::create([
            'code' => 'PERC10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        $resPercent = $service->validate('PERC10', 1000);
        $this->assertTrue($resPercent['valid']);
        $this->assertEquals(100.0, $resPercent['discount']);

        Coupon::create([
            'code' => 'FLAT150',
            'discount_type' => 'fixed',
            'discount_value' => 150,
            'is_active' => true,
        ]);

        $resFixed = $service->validate('FLAT150', 1000);
        $this->assertTrue($resFixed['valid']);
        $this->assertEquals(150.0, $resFixed['discount']);

        // Discount cannot exceed subtotal
        Coupon::create([
            'code' => 'FLAT2000',
            'discount_type' => 'fixed',
            'discount_value' => 2000,
            'is_active' => true,
        ]);
        $resExcess = $service->validate('FLAT2000', 500);
        $this->assertTrue($resExcess['valid']);
        $this->assertEquals(500.0, $resExcess['discount']);
    }

    public function test_coupon_validation_api_endpoint(): void
    {
        Coupon::create([
            'code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_order_amount' => 500,
            'is_active' => true,
        ]);

        // Valid
        $response = $this->postJson(route('checkout.validate-coupon'), [
            'code' => 'WELCOME10',
            'amount' => 1200,
        ]);
        $response->assertJson([
            'valid' => true,
            'discount' => 120,
        ]);

        // Below min order
        $responseBelow = $this->postJson(route('checkout.validate-coupon'), [
            'code' => 'WELCOME10',
            'amount' => 300,
        ]);
        $responseBelow->assertJson([
            'valid' => false,
        ]);
    }

    // ==========================================
    // MODULE 2: TAX CALCULATION & GST INVOICE
    // ==========================================

    public function test_tax_service_handles_intra_and_inter_state_gst(): void
    {
        $taxService = app(TaxCalculationService::class);

        $items = [
            [
                'product_id' => $this->product->id,
                'name' => $this->product->name,
                'sku' => $this->product->sku,
                'hsn_code' => $this->product->hsn_code,
                'price' => 100.0,
                'quantity' => 1,
                'gst_rate' => 5.0,
            ],
        ];

        // Intra-state (Gujarat to Gujarat) -> GST-inclusive ₹100 at 5% (CGST 2.5% + SGST 2.5%)
        $intra = $taxService->calculate($items, 0.0, 'Gujarat', 999.0, 0.0, 0.0, false);
        $this->assertTrue($intra['is_intra_state']);
        $this->assertEquals(95.24, $intra['taxable_amount']);
        $this->assertEquals(2.38, $intra['cgst_amount']);
        $this->assertEquals(2.38, $intra['sgst_amount']);
        $this->assertEquals(0.0, $intra['igst_amount']);
        $this->assertEquals(4.76, $intra['total_tax']);
        $this->assertEquals(100.0, $intra['grand_total']);

        // Inter-state (Gujarat to Maharashtra) -> GST-inclusive ₹100 at 5% (IGST 5%)
        $inter = $taxService->calculate($items, 0.0, 'Maharashtra', 999.0, 0.0, 0.0, false);
        $this->assertFalse($inter['is_intra_state']);
        $this->assertEquals(95.24, $inter['taxable_amount']);
        $this->assertEquals(0.0, $inter['cgst_amount']);
        $this->assertEquals(0.0, $inter['sgst_amount']);
        $this->assertEquals(4.76, $inter['igst_amount']);
        $this->assertEquals(4.76, $inter['total_tax']);
        $this->assertEquals(100.0, $inter['grand_total']);
    }

    public function test_razorpay_signature_verification(): void
    {
        $rzp = app(RazorpayService::class);

        $orderId = 'order_test_12345';
        $paymentId = 'pay_test_67890';
        $secret = 'rzp_secret_placeholder';

        $validSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        $this->assertTrue($rzp->verifySignature($orderId, $paymentId, $validSignature));
        $this->assertFalse($rzp->verifySignature($orderId, $paymentId, 'invalid_signature_hash'));
    }

    public function test_gst_invoice_generation_with_dompdf(): void
    {
        $order = Order::create([
            'order_number' => 'MNH-20261123-0001',
            'customer_type' => 'guest',
            'customer_name' => 'Advaith Sharma',
            'customer_email' => 'advaith@example.com',
            'customer_phone' => '9876543210',
            'shipping_address' => [
                'address' => '123 MG Road',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380001',
            ],
            'subtotal' => 1200.0,
            'discount_amount' => 120.0,
            'taxable_amount' => 1080.0,
            'cgst_amount' => 64.8,
            'sgst_amount' => 64.8,
            'igst_amount' => 0.0,
            'total_tax' => 129.6,
            'shipping_fee' => 0.0,
            'gateway_fee' => 0.0,
            'grand_total' => 1209.6,
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'paid_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'hsn_code' => $this->product->hsn_code,
            'unit_price' => 1200.0,
            'quantity' => 1,
            'discount' => 120.0,
            'taxable_value' => 1080.0,
            'gst_rate' => 12.0,
            'cgst_amount' => 64.8,
            'sgst_amount' => 64.8,
            'igst_amount' => 0.0,
            'total_amount' => 1209.6,
        ]);

        $invoiceService = app(InvoiceService::class);
        $invoice = $invoiceService->generateForOrder($order);

        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertNotEmpty($invoice->pdf_token);
        $this->assertDatabaseHas('invoices', ['order_id' => $order->id]);

        // Secure guest download via token
        $response = $this->get(route('invoices.download.token', ['token' => $invoice->pdf_token]));
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
    }

    // ==========================================
    // MODULE 3: INVENTORY & STOCK MANAGEMENT
    // ==========================================

    public function test_stock_service_checks_and_deducts_stock_on_order(): void
    {
        $stockService = app(StockService::class);

        // Pre-order check
        $validCheck = $stockService->validateStockAvailability([
            ['id' => $this->product->id, 'quantity' => 5],
        ]);
        $this->assertTrue($validCheck['success']);

        $excessCheck = $stockService->validateStockAvailability([
            ['id' => $this->product->id, 'quantity' => 50],
        ]);
        $this->assertFalse($excessCheck['success']);

        // Create order
        $order = Order::create([
            'order_number' => 'MNH-STOCK-001',
            'customer_type' => 'guest',
            'customer_name' => 'Stock Test User',
            'customer_email' => 'stock@test.com',
            'customer_phone' => '9999999999',
            'shipping_address' => [
                'address' => 'Line 1',
                'city' => 'City',
                'state' => 'Gujarat',
                'pincode' => '380001',
            ],
            'subtotal' => 2400.0,
            'grand_total' => 2400.0,
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 2,
            'unit_price' => 1200.0,
            'taxable_value' => 2400.0,
            'total_amount' => 2400.0,
        ]);

        // Deduct stock
        $stockService->deductStockForOrder($order);

        $this->product->refresh();
        $this->assertEquals(18, $this->product->stock_quantity);
        $this->assertEquals(2, $this->product->sold_quantity);

        // Stock movement audit record
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'sold',
            'quantity_changed' => -2,
            'updated_quantity' => 18,
        ]);

        // Restock on cancel
        $stockService->restoreStockForOrder($order);
        $this->product->refresh();
        $this->assertEquals(20, $this->product->stock_quantity);
        $this->assertEquals(0, $this->product->sold_quantity);
    }

    public function test_admin_manual_stock_adjustment(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.stock.adjust', $this->product), [
            'movement_type' => 'restock',
            'quantity' => 10,
            'reason' => 'Received fresh shipment',
        ]);

        $response->assertRedirect();
        $this->product->refresh();
        $this->assertEquals(30, $this->product->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'restock',
            'quantity_changed' => 10,
            'updated_quantity' => 30,
        ]);
    }

    // ==========================================
    // MODULE 4: AUTH, GUEST CHECKOUT & LEADS
    // ==========================================

    public function test_customer_registration_and_login(): void
    {
        $response = $this->post(route('customer.register.submit'), [
            'name' => 'Priya Patel',
            'email' => 'priya@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'phone' => '9825098250',
        ]);

        $response->assertRedirect(route('account.orders'));
        $this->assertAuthenticated();

        $user = User::where('email', 'priya@example.com')->first();
        $this->assertEquals(UserRole::Customer, $user->role);

        // Unified customer lead was created
        $this->assertDatabaseHas('customer_leads', [
            'email' => 'priya@example.com',
            'customer_type' => 'registered',
        ]);
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $customer = User::factory()->create([
            'role' => UserRole::Customer,
        ]);

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_and_export_leads(): void
    {
        CustomerLead::create([
            'name' => 'Guest Buyer',
            'email' => 'guest@example.com',
            'phone' => '9876500000',
            'customer_type' => 'guest',
            'total_orders' => 1,
            'total_purchase_amount' => 1500.0,
        ]);

        $indexResponse = $this->actingAs($this->admin)->get(route('admin.leads.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Guest Buyer');

        $exportResponse = $this->actingAs($this->admin)->get(route('admin.leads.export'));
        $exportResponse->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $exportResponse->headers->get('content-type'));
    }

    public function test_checkout_page_renders_with_active_products(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertStatus(200);
        $response->assertSee('Checkout');
        $response->assertSee('Have an Offer Code?');
        $response->assertSee('Order Summary');
    }
}
