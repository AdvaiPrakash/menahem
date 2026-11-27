<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderEmailAndInvoiceAlterationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_order_sends_single_email_to_info_and_payment_update_does_not_duplicate_it(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Kochi Linen Cushion',
            'slug' => 'kochi-linen-cushion',
            'sku' => 'MNH-00999',
            'hsn_code' => '6304',
            'category' => 'Cushions',
            'category_slug' => 'cushions',
            'price' => '₹500',
            'numeric_price' => 500,
            'gst_rate' => 5.00,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        // 1. Customer places a new order
        $createRes = $this->postJson(route('checkout.create-order'), [
            'items' => [
                ['id' => $product->id, 'name' => $product->name, 'price' => 500, 'quantity' => 2],
            ],
            'customer_name' => 'Asha Nair',
            'customer_email' => 'asha@example.com',
            'customer_phone' => '+919820012345',
            'shipping_address_line1' => '12 MG Road',
            'shipping_city' => 'Kochi',
            'shipping_state' => 'Kerala',
            'shipping_pin_code' => '682019',
        ])->assertOk();

        $orderNumber = (string) $createRes->json('order_number');
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        Mail::assertSent(NewOrderNotificationMail::class, function (NewOrderNotificationMail $mail) use ($order): bool {
            return $mail->hasTo('info@menahem.co') && $mail->order->id === $order->id;
        });
        Mail::assertSentCount(1);

        // 2. Admin changes Payment Status from Pending to Paid -> NO duplicate New Order email
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
            'order_status' => 'placed',
            'payment_status' => 'paid',
        ]);

        // Also test explicit 'processing' -> 'paid' without status change
        $order->update(['order_status' => 'processing', 'payment_status' => 'pending']);
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
            'order_status' => 'processing',
            'payment_status' => 'paid',
            'notes' => 'Payment verified manually',
        ])->assertRedirect();

        Mail::assertSent(NewOrderNotificationMail::class, 1);
        Mail::assertNotSent(OrderStatusUpdatedMail::class);
        $this->assertSame(1, Order::where('order_number', $orderNumber)->count());

        // 3. Admin updates Fulfillment Status to Shipped with tracking notes -> Customer receives status email
        $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
            'order_status' => 'shipped',
            'payment_status' => 'paid',
            'notes' => 'DTDC Tracking ID: DTDC99887766',
        ])->assertRedirect();

        Mail::assertSent(OrderStatusUpdatedMail::class, function (OrderStatusUpdatedMail $mail) use ($order): bool {
            return $mail->hasTo('asha@example.com')
                && $mail->order->id === $order->id
                && $mail->order->order_status === 'shipped';
        });
        Mail::assertSent(NewOrderNotificationMail::class, 1);
    }

    public function test_invoice_pdf_displays_kerala_state_cgst_sgst_simplified_columns_without_sku_hsn_or_igst(): void
    {
        $product = Product::create([
            'name' => 'Organic Cotton Bedsheet',
            'slug' => 'organic-cotton-bedsheet',
            'sku' => 'MNH-00421',
            'hsn_code' => '6304',
            'category' => 'Bedsheets',
            'category_slug' => 'bedsheets',
            'price' => '₹1,000',
            'numeric_price' => 1000,
            'gst_rate' => 5.00,
            'stock_quantity' => 5,
            'in_stock' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261127-KRL01',
            'customer_type' => 'guest',
            'customer_name' => 'Rahul Menon',
            'customer_email' => 'rahul@example.com',
            'customer_phone' => '+919820099111',
            'shipping_address' => [
                'full_name' => 'Rahul Menon',
                'address_line1' => '15 Convent Road',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'pin_code' => '682011',
            ],
            'billing_address' => [
                'full_name' => 'Rahul Menon',
                'address_line1' => '15 Convent Road',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'pin_code' => '682011',
            ],
            'subtotal' => 1000.00,
            'discount_amount' => 0.00,
            'taxable_amount' => 952.38,
            'cgst_amount' => 0.00,
            'sgst_amount' => 0.00,
            'igst_amount' => 47.62,
            'total_tax' => 47.62,
            'shipping_fee' => 0.00,
            'gateway_fee' => 0.00,
            'grand_total' => 1000.00,
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'payment_method' => 'Simulated Test Payment',
            'is_simulated_payment' => true,
            'paid_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => 'Organic Cotton Bedsheet',
            'sku' => 'MNH-00421',
            'hsn_code' => '6304',
            'unit_price' => 1000.00,
            'quantity' => 1,
            'discount' => 0.00,
            'taxable_value' => 952.38,
            'gst_rate' => 5.00,
            'cgst_amount' => 0.00,
            'sgst_amount' => 0.00,
            'igst_amount' => 47.62,
            'total_amount' => 1000.00,
        ]);

        $invoice = app(InvoiceService::class)->generateForOrder($order);
        // Simulate legacy Maharashtra seller_state to verify template forces Kerala
        $invoice->update(['seller_state' => 'Maharashtra', 'seller_state_code' => '27']);

        $html = view('invoices.pdf', ['invoice' => $invoice->fresh(), 'order' => $order->load('items')])->render();

        $this->assertStringContainsString('Kerala', $html);
        $this->assertStringNotContainsString('State:</strong> Maharashtra', $html);
        $this->assertStringContainsString('Product Details', $html);
        $this->assertStringContainsString('Quantity', $html);
        $this->assertStringContainsString('Rate', $html);
        $this->assertStringContainsString('Taxable Amount', $html);
        $this->assertStringNotContainsString('MNH-00421', $html);
        $this->assertStringNotContainsString('6304', $html);
        $this->assertStringNotContainsString('HSN', $html);
        $this->assertStringContainsString('CGST – 2.5%', $html);
        $this->assertStringContainsString('SGST – 2.5%', $html);
        $this->assertStringContainsString('23.81', $html);
        $this->assertStringContainsString('Grand Total (Incl. GST)', $html);
        $this->assertStringNotContainsString('IGST (Integrated Tax)', $html);
    }
}
