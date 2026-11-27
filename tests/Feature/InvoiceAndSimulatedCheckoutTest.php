<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceAndSimulatedCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_pdf_uses_dejavu_sans_for_rupee_symbol_and_official_menahem_business_details(): void
    {
        $product = Product::create([
            'name' => 'Kochi Handloom Curtain',
            'slug' => 'kochi-handloom-curtain',
            'category' => 'Curtains',
            'category_slug' => 'curtains',
            'price' => '₹100',
            'numeric_price' => 100,
            'gst_rate' => 5.00,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261127-INV01',
            'customer_type' => 'guest',
            'customer_name' => 'Meera Menon',
            'customer_email' => 'meera@example.com',
            'customer_phone' => '+919820011223',
            'shipping_address' => [
                'full_name' => 'Meera Menon',
                'address_line1' => 'MG Road',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'pin_code' => '682016',
            ],
            'billing_address' => [
                'full_name' => 'Meera Menon',
                'address_line1' => 'MG Road',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'pin_code' => '682016',
            ],
            'subtotal' => 100.00,
            'discount_amount' => 0.00,
            'taxable_amount' => 95.24,
            'cgst_amount' => 2.38,
            'sgst_amount' => 2.38,
            'igst_amount' => 0.00,
            'total_tax' => 4.76,
            'shipping_fee' => 0.00,
            'gateway_fee' => 0.00,
            'grand_total' => 100.00,
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'payment_method' => 'Simulated Test Payment',
            'is_simulated_payment' => true,
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => 100.00,
            'quantity' => 1,
            'discount' => 0.00,
            'taxable_value' => 95.24,
            'gst_rate' => 5.00,
            'cgst_amount' => 2.38,
            'sgst_amount' => 2.38,
            'igst_amount' => 0.00,
            'total_amount' => 100.00,
        ]);

        $invoice = app(InvoiceService::class)->generateForOrder($order);

        $this->assertSame('MENAHEM', $invoice->seller_name);
        $this->assertSame('32DDLPP5032G1ZN', $invoice->seller_gstin);
        $this->assertSame('+91 8129 4620 45', $invoice->seller_phone);
        $this->assertSame('info@menahem.co.in', $invoice->seller_email);
        $this->assertStringContainsString('52/1307, St. James Road', (string) $invoice->seller_address);

        $html = view('invoices.pdf', ['invoice' => $invoice, 'order' => $order])->render();
        $this->assertStringContainsString("font-family: 'DejaVu Sans', sans-serif;", $html);
        $this->assertStringContainsString('32DDLPP5032G1ZN', $html);
        $this->assertStringContainsString('Thykoodam, Kochi', $html);
        $this->assertStringContainsString('+91 8129 4620 45', $html);
        $this->assertStringContainsString('info@menahem.co.in', $html);
        $this->assertStringContainsString('SIMULATED TEST PAYMENT', $html);

        $pdf = app(InvoiceService::class)->renderPdf($invoice);
        $this->assertNotEmpty($pdf->output());

        // Storefront footer also shows official Menahem details
        $this->get('/')->assertOk()
            ->assertSee('info@menahem.co.in')
            ->assertSee('+91 8129 4620 45')
            ->assertSee('Thykoodam, Kochi');
    }

    public function test_simulated_payment_guest_and_customer_order_history_and_invoice_access(): void
    {
        Storage::fake('public');
        Mail::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $customerA = User::factory()->create(['role' => UserRole::Customer, 'email' => 'customera@example.com']);
        $customerB = User::factory()->create(['role' => UserRole::Customer, 'email' => 'customerb@example.com']);

        $product = Product::create([
            'name' => 'Linen Table Runner',
            'slug' => 'linen-table-runner',
            'category' => 'Table Decor',
            'category_slug' => 'table-decor',
            'price' => '₹500',
            'numeric_price' => 500,
            'gst_rate' => 5.00,
            'stock_quantity' => 5,
            'in_stock' => true,
        ]);

        // Checkout page includes Simulated Test Payment Modal
        $this->get(route('checkout.index', ['mode' => 'buy_now']))
            ->assertOk()
            ->assertSee('Simulate Successful Payment')
            ->assertSee('Test Mode');

        // 1. Guest checkout creates simulated order
        $createRes = $this->postJson(route('checkout.create-order'), [
            'items' => [
                ['id' => $product->id, 'name' => $product->name, 'price' => 500, 'quantity' => 1],
            ],
            'customer_name' => 'Guest Buyer',
            'customer_email' => 'guestbuyer@example.com',
            'customer_phone' => '+919820000111',
            'shipping_address_line1' => '10 Marine Drive',
            'shipping_city' => 'Kochi',
            'shipping_state' => 'Kerala',
            'shipping_pin_code' => '682019',
        ])->assertOk();

        $this->assertTrue((bool) $createRes->json('is_simulated'));
        $orderNumber = (string) $createRes->json('order_number');
        $razorpayOrderId = (string) $createRes->json('razorpay_order_id');

        // 2. Verify simulated payment
        $this->postJson(route('checkout.verify-payment'), [
            'order_number' => $orderNumber,
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => 'pay_sim_abc123',
            'razorpay_signature' => 'simulated_valid_signature',
        ])->assertOk()->assertJsonPath('success', true);

        $guestOrder = Order::where('order_number', $orderNumber)->with('invoice')->firstOrFail();
        $this->assertTrue($guestOrder->isSimulatedPayment());
        $this->assertSame('Simulated Test Payment', $guestOrder->payment_method);
        $this->assertNotNull($guestOrder->invoice);

        // Guest confirmation and token-based PDF download work without login
        $this->get(route('checkout.confirmation', ['orderNumber' => $orderNumber]))
            ->assertOk()
            ->assertSee('Simulated Test Payment')
            ->assertSee('Download GST Tax Invoice (PDF)');

        $this->get(route('invoices.download.token', ['token' => $guestOrder->invoice->pdf_token]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        // Admin panel shows simulated order clearly labeled
        $this->actingAs($admin)->get(route('admin.orders.show', $guestOrder))
            ->assertOk()
            ->assertSee('Simulated Test');

        // 3. Logged-in Customer A places an order and can view + download only their own invoice
        $custCreate = $this->actingAs($customerA)->postJson(route('checkout.create-order'), [
            'items' => [
                ['id' => $product->id, 'name' => $product->name, 'price' => 500, 'quantity' => 1],
            ],
            'customer_name' => $customerA->name,
            'customer_email' => $customerA->email,
            'customer_phone' => '+919820000222',
            'shipping_address_line1' => '20 Panampilly Nagar',
            'shipping_city' => 'Kochi',
            'shipping_state' => 'Kerala',
            'shipping_pin_code' => '682019',
        ])->assertOk();

        $custOrderNum = (string) $custCreate->json('order_number');
        $this->actingAs($customerA)->postJson(route('checkout.verify-payment'), [
            'order_number' => $custOrderNum,
            'razorpay_order_id' => $custCreate->json('razorpay_order_id'),
            'razorpay_payment_id' => 'pay_sim_custA',
            'razorpay_signature' => 'simulated_valid_signature',
        ])->assertOk();

        $this->actingAs($customerA)->get(route('account.orders'))
            ->assertOk()
            ->assertSee($custOrderNum)
            ->assertSee('Simulated Test')
            ->assertDontSee($orderNumber);

        $this->actingAs($customerA)->get(route('account.orders.invoice', ['orderNumber' => $custOrderNum]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        // Customer B cannot access Customer A's order or invoice
        $this->actingAs($customerB)->get(route('account.orders.show', ['orderNumber' => $custOrderNum]))
            ->assertStatus(404);

        $this->actingAs($customerB)->get(route('account.orders.invoice', ['orderNumber' => $custOrderNum]))
            ->assertStatus(403);
    }
}
