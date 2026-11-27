<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiAdminEmailAndMobileUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_configured_admin_recipients_receive_new_order_notification_once(): void
    {
        Mail::fake();
        Storage::fake('public');

        Setting::set('order_notification_emails', 'info@menahem.co, orders@menahem.co; invalid-email, ops@menahem.co, info@menahem.co', 'payment');

        $product = Product::create([
            'name' => 'Handcrafted Brass Lamp',
            'slug' => 'handcrafted-brass-lamp',
            'sku' => 'MNH-LAMP-01',
            'hsn_code' => '9405',
            'category' => 'Lighting',
            'category_slug' => 'lighting',
            'price' => '₹1,200',
            'numeric_price' => 1200,
            'gst_rate' => 12.00,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        $response = $this->postJson(route('checkout.create-order'), [
            'items' => [
                ['id' => $product->id, 'name' => $product->name, 'price' => 1200, 'quantity' => 1],
            ],
            'customer_name' => 'Meera Menon',
            'customer_email' => 'meera@example.com',
            'customer_phone' => '+919847011223',
            'shipping_address_line1' => '45 Marine Drive',
            'shipping_city' => 'Kochi',
            'shipping_state' => 'Kerala',
            'shipping_pin_code' => '682031',
        ]);

        $response->assertOk();
        $order = Order::where('order_number', (string) $response->json('order_number'))->firstOrFail();

        Mail::assertSent(NewOrderNotificationMail::class, 3);

        foreach (['info@menahem.co', 'orders@menahem.co', 'ops@menahem.co'] as $recipient) {
            Mail::assertSent(NewOrderNotificationMail::class, function (NewOrderNotificationMail $mail) use ($recipient, $order): bool {
                return $mail->hasTo($recipient) && $mail->order->id === $order->id;
            });
        }
    }

    public function test_accounts_cancel_sends_cancellation_email_to_customer(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Teak Serving Tray',
            'slug' => 'teak-serving-tray',
            'sku' => 'MNH-TRAY-01',
            'hsn_code' => '4419',
            'category' => 'Serveware',
            'category_slug' => 'serveware',
            'price' => '₹800',
            'numeric_price' => 800,
            'gst_rate' => 5.00,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'billing_mode' => 'walk_in',
            'customer_name' => 'Rohan Varma',
            'customer_email' => 'rohan@example.com',
            'customer_phone' => '+919820011223',
            'payment_method' => 'UPI',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 800],
            ],
        ])->assertSessionHasNoErrors();

        $order = Order::latest('id')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.accounts.cancel', $order), ['cancellation_reason' => 'Returned at counter'])
            ->assertRedirect();

        Mail::assertSent(OrderStatusUpdatedMail::class, function (OrderStatusUpdatedMail $mail) use ($order): bool {
            return $mail->hasTo('rohan@example.com')
                && $mail->order->id === $order->id
                && $mail->order->order_status === 'cancelled';
        });
    }

    public function test_admin_order_show_renders_whatsapp_invoice_share_link(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Linen Table Cloth',
            'slug' => 'linen-table-cloth',
            'sku' => 'MNH-CLOTH-01',
            'hsn_code' => '6302',
            'category' => 'Table Linen',
            'category_slug' => 'table-linen',
            'price' => '₹1,500',
            'numeric_price' => 1500,
            'gst_rate' => 5.00,
            'stock_quantity' => 5,
            'in_stock' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'billing_mode' => 'walk_in',
            'customer_name' => 'Neha Kapoor',
            'customer_phone' => '+919820099887',
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1500],
            ],
        ])->assertSessionHasNoErrors();

        $order = Order::with('invoice')->latest('id')->firstOrFail();
        $this->assertNotNull($order->invoice?->pdf_token);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order));
        $response->assertOk();
        $response->assertSee('Share on WhatsApp');
        $response->assertSee('https://wa.me/919820099887?text=', false);
        $response->assertSee($order->invoice->pdf_token, false);
    }

    public function test_mobile_responsive_classes_present_on_cart_drawer_and_wishlist(): void
    {
        $home = $this->get(route('home'));
        $home->assertOk();
        $home->assertSee('max-w-[calc(100vw-1rem)] sm:max-w-md', false);
        $home->assertSee('overflow-x-hidden', false);
        $home->assertSee('w-[56px] h-[56px] sm:w-[72px] sm:h-[72px]', false);

        $wishlist = $this->get(route('wishlist.index'));
        $wishlist->assertOk();
        $wishlist->assertSee('overflow-x-hidden', false);
        $wishlist->assertSee('pt-24 sm:pt-28', false);
    }
}
