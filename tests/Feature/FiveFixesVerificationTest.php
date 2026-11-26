<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FiveFixesVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_clearance_sale_opens_even_if_table_was_missing(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Schema::dropIfExists('clearance_items');
        $this->assertFalse(Schema::hasTable('clearance_items'));

        $response = $this->actingAs($admin)->get(route('admin.clearance.index'));

        $response->assertStatus(200);
        $response->assertSee('Clearance Sale Management');
        $this->assertTrue(Schema::hasTable('clearance_items'));
    }

    public function test_admin_notification_runtime_includes_reliable_voice_playback_logic(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('blockedAudioQueue', false);
        $response->assertSee('removeGestureUnlockListeners', false);
        $response->assertSee('markAudioPlayed', false);
    }

    public function test_manual_billing_page_includes_product_search_combobox(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.billing.create'));

        $response->assertStatus(200);
        $response->assertSee('Search product by name or SKU...', false);
        $response->assertSee('filteredProducts(row)', false);
    }

    public function test_buy_now_and_cart_checkout_are_isolated_and_gst_is_calculated_per_product_rate(): void
    {
        Setting::set('seller_state', 'Maharashtra');

        $product5 = Product::create([
            'name' => 'Handloom Cotton Bedcover',
            'slug' => 'handloom-cotton-bedcover',
            'category' => 'Bedsheets',
            'category_slug' => 'bedsheets',
            'price' => '₹1,000',
            'numeric_price' => 1000,
            'gst_rate' => 5.0,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        $product18 = Product::create([
            'name' => 'Brass Curtain Rod Finials',
            'slug' => 'brass-curtain-rod-finials',
            'category' => 'Accessories',
            'category_slug' => 'accessories',
            'price' => '₹1,000',
            'numeric_price' => 1000,
            'gst_rate' => 18.0,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        Coupon::create([
            'code' => 'FLAT200',
            'discount_type' => 'fixed',
            'discount_value' => 200,
            'min_order_amount' => 500,
            'is_active' => true,
        ]);

        // Product detail page Buy Now uses isolated sessionStorage and ?mode=buy_now
        $detail = $this->get(route('products.show', $product5->id));
        $detail->assertOk();
        $detail->assertSee('menahem_buy_now', false);
        $detail->assertSee('mode=buy_now', false);
        $detail->assertSee('h-11 inline-flex items-center justify-center', false);

        // Checkout page supports separate buy_now vs cart modes
        $checkout = $this->get(route('checkout.index', ['mode' => 'buy_now']));
        $checkout->assertOk();
        $checkout->assertSee('checkoutMode', false);
        $checkout->assertSee('menahem_buy_now', false);

        // Intra-state GST calculation with per-product GST rates (5% and 18%) + coupon discount
        $intra = $this->postJson(route('checkout.calculate'), [
            'items' => [
                ['id' => $product5->id, 'name' => $product5->name, 'price' => 1000, 'quantity' => 1],
                ['id' => $product18->id, 'name' => $product18->name, 'price' => 1000, 'quantity' => 1],
            ],
            'coupon_code' => 'FLAT200',
            'shipping_state' => 'Maharashtra',
        ])->assertOk();

        // Subtotal = 2000 (GST-inclusive), Discount = 200, Net Inclusive = 1800 (900 each)
        // 5% inclusive on 900 => taxable = 857.14, tax = 42.86 (CGST 21.43 + SGST 21.43)
        // 18% inclusive on 900 => taxable = 762.71, tax = 137.29 (CGST 68.65 + SGST 68.64)
        // Total taxable = 1619.85, CGST = 90.08, SGST = 90.07, Total Tax = 180.15, Grand Total = 1800
        $this->assertEquals(2000.0, (float) $intra->json('subtotal'));
        $this->assertEquals(200.0, (float) $intra->json('discount'));
        $this->assertEquals(1619.85, (float) $intra->json('taxable_amount'));
        $this->assertTrue((bool) $intra->json('is_intra_state'));
        $this->assertEquals(90.08, (float) $intra->json('cgst_amount'));
        $this->assertEquals(90.07, (float) $intra->json('sgst_amount'));
        $this->assertEquals(0.0, (float) $intra->json('igst_amount'));
        $this->assertEquals(180.15, (float) $intra->json('total_tax'));
        $this->assertEquals(1800.0, (float) $intra->json('grand_total'));

        // Inter-state GST calculation (Maharashtra -> Gujarat) -> IGST included in ₹2000
        $inter = $this->postJson(route('checkout.calculate'), [
            'items' => [
                ['id' => "{$product5->id}-1732600000000", 'product_id' => $product5->id, 'name' => $product5->name, 'price' => 1000, 'quantity' => 2],
            ],
            'shipping_state' => 'Gujarat',
        ])->assertOk();

        $this->assertFalse((bool) $inter->json('is_intra_state'));
        $this->assertEquals(1904.76, (float) $inter->json('taxable_amount'));
        $this->assertEquals(0.0, (float) $inter->json('cgst_amount'));
        $this->assertEquals(0.0, (float) $inter->json('sgst_amount'));
        $this->assertEquals(95.24, (float) $inter->json('igst_amount'));
        $this->assertEquals(2000.0, (float) $inter->json('grand_total'));
    }
}
