<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CustomerLead;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CustomerLeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadAndProductFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_enquiry_creates_enquiry_only_lead_and_upgrades_to_ordered_without_duplicates(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->post(route('inquiries.store'), [
            'name' => 'Meera Nair',
            'email' => 'meera@example.com',
            'phone' => '+919845012345',
            'type' => 'curtains',
            'subject' => 'Sheer Living Room Curtains',
            'message' => 'Looking for 9ft sheer linen curtains.',
        ])->assertStatus(302);

        $this->post(route('inquiries.store'), [
            'name' => 'Meera Nair',
            'email' => 'meera@example.com',
            'phone' => '+919845012345',
            'type' => 'curtains',
            'subject' => 'Follow up on measurements',
            'message' => 'Can you tailor 4 panels?',
        ])->assertStatus(302);

        $this->assertSame(1, CustomerLead::where('email', 'meera@example.com')->count());
        $lead = CustomerLead::where('email', 'meera@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.leads.index', ['status' => 'enquiry_only']))
            ->assertOk()
            ->assertSee('Meera Nair')
            ->assertSee('Enquiry Only');

        $this->actingAs($admin)
            ->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('Enquiry Only')
            ->assertSee('Sheer Living Room Curtains')
            ->assertSee('Follow up on measurements');

        $order = Order::create([
            'order_number' => 'ORD-20261125-LEAD01',
            'customer_type' => 'guest',
            'customer_name' => 'Meera Nair',
            'customer_email' => 'meera@example.com',
            'customer_phone' => '+919845012345',
            'shipping_address' => [
                'full_name' => 'Meera Nair',
                'address_line1' => '10 Indiranagar',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pin_code' => '560038',
            ],
            'billing_address' => [
                'full_name' => 'Meera Nair',
                'address_line1' => '10 Indiranagar',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pin_code' => '560038',
            ],
            'subtotal' => 3200.00,
            'grand_total' => 3200.00,
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'payment_method' => 'Razorpay',
        ]);

        app(CustomerLeadService::class)->recordLeadFromOrder($order);

        $this->assertSame(1, CustomerLead::where('email', 'meera@example.com')->count());
        $this->assertSame(1, $lead->fresh()->total_orders);

        $this->actingAs($admin)
            ->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('Ordered')
            ->assertSee('ORD-20261125-LEAD01')
            ->assertSee('Sheer Living Room Curtains');
    }

    public function test_product_detail_page_removes_buy_via_whatsapp_and_uses_buy_now_checkout(): void
    {
        $product = Product::create([
            'name' => 'Belgian Linen Curtain',
            'slug' => 'belgian-linen-curtain',
            'category' => 'Curtains',
            'category_slug' => 'curtains',
            'price' => 2400.00,
            'gst_rate' => 12.00,
            'stock_quantity' => 5,
            'in_stock' => true,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.show', $product->id));

        $response->assertStatus(200);
        $response->assertDontSee('Buy via WhatsApp');
        $response->assertSee('Buy Now');
        $response->assertSee('Add to Bag');
        $response->assertSee(route('checkout.index'), false);
        $response->assertSee('Curtain Size &amp; Customization', false);
    }
}
