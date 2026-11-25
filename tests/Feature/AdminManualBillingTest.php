<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CustomerLead;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManualBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_manual_billing_button_and_hides_invoice_management(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manual Billing');
        $response->assertSee(route('admin.billing.create'));
        $response->assertDontSee('Invoice Management');
        $response->assertDontSee('View All Invoices');
    }

    public function test_viewer_cannot_access_manual_billing(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);

        $this->actingAs($viewer)
            ->get(route('admin.billing.create'))
            ->assertStatus(403);

        $this->actingAs($viewer)
            ->post(route('admin.billing.store'), [])
            ->assertStatus(403);
    }

    public function test_admin_can_create_manual_bill_deduct_stock_and_generate_invoice(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Handloom Cotton Curtain',
            'slug' => 'handloom-cotton-curtain',
            'category' => 'Curtains',
            'category_slug' => 'curtains',
            'price' => 1500.00,
            'gst_rate' => 12.00,
            'stock_quantity' => 10,
            'in_stock' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.billing.store'), [
            'customer_name' => 'Rohan Verma',
            'customer_phone' => '+919820099887',
            'customer_email' => 'rohan@example.com',
            'shipping_state' => 'Maharashtra',
            'shipping_city' => 'Mumbai',
            'payment_method' => 'UPI',
            'payment_reference' => 'UTR123456789',
            'discount_amount' => 100,
            'shipping_fee' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'unit_price' => 1500,
                    'custom_size' => '7ft',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('confirmed', $order->order_status);
        $this->assertSame('UPI', $order->payment_method);
        $this->assertSame('UTR123456789', $order->razorpay_payment_id);
        $this->assertSame(8, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('invoices', ['order_id' => $order->id]);
        $this->assertSame(1, Invoice::where('order_id', $order->id)->count());
        $this->assertDatabaseHas('customer_leads', ['email' => 'rohan@example.com']);
    }

    public function test_manual_billing_validates_stock_before_creating_order(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Limited Edition Cushion',
            'slug' => 'limited-edition-cushion',
            'category' => 'Cushions',
            'category_slug' => 'cushions',
            'price' => 800.00,
            'gst_rate' => 12.00,
            'stock_quantity' => 1,
            'in_stock' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.billing.store'), [
            'customer_name' => 'Anita Desai',
            'customer_phone' => '+919811199222',
            'shipping_state' => 'Maharashtra',
            'payment_method' => 'Cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 800,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertSame(0, CustomerLead::count());
    }
}
