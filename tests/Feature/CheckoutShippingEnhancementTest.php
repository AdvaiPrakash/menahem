<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\ShippingCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutShippingEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $viewer;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin-ship@menahem.test',
            'role' => UserRole::Admin,
        ]);

        $this->viewer = User::factory()->create([
            'email' => 'viewer-ship@menahem.test',
            'role' => UserRole::Viewer,
        ]);

        $category = Category::create([
            'name' => 'Curtains',
            'slug' => 'curtains',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Linen Sheer Curtain',
            'slug' => 'linen-sheer-curtain',
            'category_id' => $category->id,
            'category_slug' => $category->slug,
            'sku' => 'CUR-101',
            'hsn_code' => '6303',
            'price' => '₹1,000',
            'numeric_price' => 1000.00,
            'gst_rate' => 12.00,
            'stock_quantity' => 15,
            'opening_stock' => 15,
            'sold_quantity' => 0,
            'low_stock_threshold' => 3,
            'is_active' => true,
        ]);

        Setting::setMany([
            'seller_name' => 'Menahem Home & Living',
            'seller_state' => 'Maharashtra',
            'seller_state_code' => '27',
            'seller_gstin' => '27AABCM1234F1Z8',
            'razorpay_key_id' => '',
            'razorpay_key_secret' => '',
        ], 'payment');

        Setting::setMany([
            'shipping_origin_pincode' => '400002',
            'shipping_origin_address' => '42, Textile Market Road, Kalbadevi',
            'shipping_origin_city' => 'Mumbai',
            'shipping_origin_state' => 'Maharashtra',
            'shipping_origin_lat' => '18.945139',
            'shipping_origin_lng' => '72.828639',
            'shipping_free_distance_km' => '5',
            'shipping_charge_per_km' => '10',
        ], 'shipping');
    }

    public function test_checkout_page_removes_gstin_simplifies_pay_button_and_uses_desktop_only_sticky_summary(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertOk();
        $response->assertDontSee('Business GSTIN');
        $response->assertDontSee('customerGstin');
        $response->assertDontSee('Pay Now via Razorpay');
        $response->assertSee('>Pay Now<', false);

        // Desktop-only sticky summary column (lg:sticky), mobile layout remains normal
        $response->assertSee('lg:col-span-5 lg:sticky lg:top-8', false);
        $response->assertDontSee('shadow-xs sticky top-28');
    }

    public function test_indian_pincode_lookup_returns_city_and_state_across_india(): void
    {
        $samples = [
            '400051' => ['city' => 'Mumbai', 'state' => 'Maharashtra'],
            '682001' => ['city' => 'Ernakulam', 'state' => 'Kerala'],
            '110001' => ['city' => 'New Delhi', 'state' => 'Delhi'],
            '560001' => ['city' => 'Bengaluru', 'state' => 'Karnataka'],
            '380001' => ['city' => 'Ahmedabad', 'state' => 'Gujarat'],
        ];

        foreach ($samples as $pincode => $expected) {
            $res = $this->getJson(route('checkout.pincode-lookup', ['pincode' => (string) $pincode]));
            $res->assertOk()
                ->assertJsonPath('valid', true)
                ->assertJsonPath('pincode', (string) $pincode)
                ->assertJsonPath('city', $expected['city'])
                ->assertJsonPath('state', $expected['state']);
        }
    }

    public function test_invalid_indian_pincodes_return_validation_errors(): void
    {
        foreach (['000000', '12345', '999999'] as $invalidPin) {
            $res = $this->getJson(route('checkout.pincode-lookup', ['pincode' => $invalidPin]));
            $res->assertStatus(422)
                ->assertJsonPath('valid', false);
        }
    }

    public function test_only_admin_can_access_and_update_shipping_configuration(): void
    {
        $this->actingAs($this->viewer)
            ->get(route('admin.settings.shipping'))
            ->assertStatus(403);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.shipping'))
            ->assertOk()
            ->assertSee('Shipping Configuration')
            ->assertSee('Business / Pickup Location')
            ->assertSee('Free Delivery Distance (KM)')
            ->assertSee('Shipping Charge Per KM');

        $update = $this->actingAs($this->admin)->post(route('admin.settings.shipping.update'), [
            'shipping_origin_pincode' => '682001',
            'shipping_origin_address' => '14, Marine Drive, Ernakulam',
            'shipping_origin_city' => 'Kochi',
            'shipping_origin_state' => 'Kerala',
            'shipping_free_distance_km' => 8,
            'shipping_charge_per_km' => 15,
        ]);

        $update->assertRedirect(route('admin.settings.shipping'));

        $this->assertEquals('682001', Setting::get('shipping_origin_pincode'));
        $this->assertEquals('Kochi', Setting::get('shipping_origin_city'));
        $this->assertEquals('Kerala', Setting::get('shipping_origin_state'));
        $this->assertEquals('8', Setting::get('shipping_free_distance_km'));
        $this->assertEquals('15', Setting::get('shipping_charge_per_km'));
        $this->assertNotEmpty(Setting::get('shipping_origin_lat'));
        $this->assertNotEmpty(Setting::get('shipping_origin_lng'));
    }

    public function test_dynamic_shipping_is_free_within_limit_and_charged_beyond_limit(): void
    {
        $itemsPayload = [
            [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 1000,
                'quantity' => 1,
            ],
        ];

        // 1. Within free delivery limit (same PIN 400002 -> 0 km <= 5 km)
        $freeRes = $this->postJson(route('checkout.calculate'), [
            'items' => $itemsPayload,
            'shipping_state' => 'Maharashtra',
            'shipping_city' => 'Mumbai',
            'shipping_pin_code' => '400002',
        ]);

        $freeRes->assertOk()
            ->assertJsonPath('shipping_calculated', true)
            ->assertJsonPath('shipping_fee', 0)
            ->assertJsonPath('distance_km', 0);

        // 2. Beyond free delivery limit (400002 Kalbadevi -> 400051 Bandra East, ~13.12 km > 5 km)
        $shippingService = app(ShippingCalculationService::class);
        $expectedCalc = $shippingService->calculateForAddress([
            'pincode' => '400051',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
        ]);

        $this->assertTrue($expectedCalc['determined']);
        $this->assertGreaterThan(5.0, $expectedCalc['distance_km']);
        $this->assertGreaterThan(0.0, $expectedCalc['shipping_fee']);

        $paidRes = $this->postJson(route('checkout.calculate'), [
            'items' => $itemsPayload,
            'shipping_state' => 'Maharashtra',
            'shipping_city' => 'Mumbai',
            'shipping_pin_code' => '400051',
        ]);

        $paidRes->assertOk()
            ->assertJsonPath('shipping_calculated', true)
            ->assertJsonPath('shipping_fee', $expectedCalc['shipping_fee'])
            ->assertJsonPath('distance_km', $expectedCalc['distance_km'])
            ->assertJsonPath('free_distance_km', 5)
            ->assertJsonPath('charge_per_km', 10);

        // 3. Updating free limit dynamically to 25 km makes 400051 free
        Setting::set('shipping_free_distance_km', '25', 'shipping');

        $updatedRes = $this->postJson(route('checkout.calculate'), [
            'items' => $itemsPayload,
            'shipping_state' => 'Maharashtra',
            'shipping_city' => 'Mumbai',
            'shipping_pin_code' => '400051',
        ]);

        $updatedRes->assertOk()
            ->assertJsonPath('shipping_calculated', true)
            ->assertJsonPath('shipping_fee', 0);
    }

    public function test_address_level_geocoding_refines_distance_within_same_pincode(): void
    {
        Cache::flush();

        // Simulate Nominatim returning exact street coordinates 7.5 km away inside the same city/PIN
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([
                [
                    'lat' => '19.012588',
                    'lon' => '72.828639',
                ],
            ], 200),
        ]);

        $shippingService = app(ShippingCalculationService::class);
        $result = $shippingService->calculateForAddress([
            'pincode' => '400002',
            'address_line1' => '100 Worli Sea Face',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
        ]);

        $this->assertTrue($result['determined']);
        $this->assertEquals('address_geocoded', $result['distance_source']);
        $this->assertEquals(7.5, $result['distance_km']);
        // 7.5 km - 5 km free = 2.5 km * ₹10 = ₹25.00
        $this->assertEquals(25.0, $result['shipping_fee']);
    }

    public function test_order_creation_calculates_shipping_server_side_ignores_tampering_and_completes_razorpay_flow(): void
    {
        $shippingService = app(ShippingCalculationService::class);
        $expectedShipping = $shippingService->calculateForAddress([
            'pincode' => '400051',
            'address_line1' => 'Flat 402, Oakwood Residences',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
        ]);

        // Attempt to tamper with shipping_fee and grand_total in request payload
        $createRes = $this->postJson(route('checkout.create-order'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
            'customer_name' => 'Aditi Sharma',
            'customer_email' => 'aditi@example.com',
            'customer_phone' => '+919820123456',
            'shipping_address_line1' => 'Flat 402, Oakwood Residences',
            'shipping_address_line2' => 'Bandra East',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_pin_code' => '400051',
            'billing_same_as_shipping' => true,
            'shipping_fee' => 0, // tampered!
            'grand_total' => 1,  // tampered!
        ]);

        $createRes->assertOk()
            ->assertJsonPath('success', true);

        $orderNumber = $createRes->json('order_number');
        $razorpayOrderId = $createRes->json('razorpay_order_id');
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        // Server must use its own computed shipping fee and GST-inclusive grand total (1000 + shipping_fee)
        $this->assertEquals($expectedShipping['shipping_fee'], $order->shipping_fee);
        $this->assertEquals(round(1000.0 + $expectedShipping['shipping_fee'], 2), $order->grand_total);
        $this->assertNull($order->customer_gstin);

        // Razorpay verification flow completes successfully
        $verifyRes = $this->postJson(route('checkout.verify-payment'), [
            'order_number' => $orderNumber,
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => 'pay_sim_test_123456',
            'razorpay_signature' => 'simulated_valid_signature',
        ]);

        $verifyRes->assertOk()
            ->assertJsonPath('success', true);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('confirmed', $order->order_status);
        $this->assertNotNull($order->invoice);
        $this->assertEquals(14, $this->product->fresh()->stock_quantity);
    }

    public function test_order_creation_rejects_invalid_pincode_without_creating_order(): void
    {
        $res = $this->postJson(route('checkout.create-order'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => 1000,
                    'quantity' => 1,
                ],
            ],
            'customer_name' => 'Aditi Sharma',
            'customer_email' => 'aditi@example.com',
            'customer_phone' => '+919820123456',
            'shipping_address_line1' => 'Flat 402',
            'shipping_city' => 'Mumbai',
            'shipping_state' => 'Maharashtra',
            'shipping_pin_code' => '999999',
        ]);

        $res->assertStatus(422)
            ->assertJsonPath('success', false);
        $this->assertDatabaseCount('orders', 0);
    }
}
