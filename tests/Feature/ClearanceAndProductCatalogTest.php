<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ClearanceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClearanceAndProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_clearance_sale_percentage_and_fixed_discounts_and_visibility_controls(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $homeOnlyProduct = Product::create([
            'name' => 'Archival Linen Drape',
            'slug' => 'archival-linen-drape',
            'category' => 'Curtains',
            'category_slug' => 'curtains',
            'price' => '₹2,000',
            'numeric_price' => 2000,
            'stock_quantity' => 8,
            'in_stock' => true,
        ]);

        $allProductsItem = Product::create([
            'name' => 'Mulmul Cotton Throw',
            'slug' => 'mulmul-cotton-throw',
            'category' => 'Throws',
            'category_slug' => 'throws',
            'price' => '₹2,000',
            'numeric_price' => 2000,
            'stock_quantity' => 6,
            'in_stock' => true,
        ]);

        // Add first with 25% discount, homepage only (hidden from All Products)
        $this->actingAs($admin)->post(route('admin.clearance.store'), [
            'product_ids' => [$homeOnlyProduct->id],
            'discount_type' => 'percentage',
            'discount_value' => 25,
            'show_on_homepage' => '1',
        ])->assertRedirect(route('admin.clearance.index'));

        // Add second with ₹500 fixed discount, All Products only (hidden from Homepage)
        $this->actingAs($admin)->post(route('admin.clearance.store'), [
            'product_ids' => [$allProductsItem->id],
            'discount_type' => 'fixed',
            'discount_value' => 500,
            'show_in_all_products' => '1',
        ])->assertRedirect(route('admin.clearance.index'));

        $this->assertEquals(1500.0, $homeOnlyProduct->fresh()->effectivePrice());
        $this->assertEquals(1500.0, $allProductsItem->fresh()->effectivePrice());

        // Homepage Clearance Sale shows homeOnlyProduct, hides allProductsItem from clearance grid
        $homeResponse = $this->get(route('home'))->assertOk();
        $homeResponse->assertSee('Archival Linen Drape');
        $homeResponse->assertSee('25% OFF');

        // All Products page shows allProductsItem with discounted price, hides homeOnlyProduct
        $shopResponse = $this->get(route('products.index'))->assertOk();
        $shopResponse->assertSee('Mulmul Cotton Throw');
        $shopResponse->assertDontSee('Archival Linen Drape');

        // Server-side checkout calculation ignores client-tampered price and enforces ₹1,500
        $calcResponse = $this->postJson(route('checkout.calculate'), [
            'items' => [
                [
                    'id' => $homeOnlyProduct->id,
                    'name' => $homeOnlyProduct->name,
                    'price' => 10, // tampered low price
                    'quantity' => 1,
                ],
            ],
            'shipping_state' => 'Maharashtra',
        ])->assertOk();

        $this->assertEquals(1500.0, (float) $calcResponse->json('subtotal'));

        // Removing from Clearance Sale removes from homepage while keeping product in catalog
        $clearanceRow = ClearanceItem::where('product_id', $homeOnlyProduct->id)->firstOrFail();
        $this->actingAs($admin)
            ->delete(route('admin.clearance.destroy', $clearanceRow))
            ->assertRedirect(route('admin.clearance.index'));

        $this->assertDatabaseHas('products', ['id' => $homeOnlyProduct->id]);
        $this->get(route('home'))->assertOk()->assertDontSee('Archival Linen Drape');
    }

    public function test_clearance_validation_rejects_negative_or_excessive_discounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Cotton Cushion Cover',
            'slug' => 'cotton-cushion-cover',
            'category' => 'Cushions',
            'category_slug' => 'cushions',
            'price' => '₹500',
            'numeric_price' => 500,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.clearance.store'), [
            'product_ids' => [$product->id],
            'discount_type' => 'percentage',
            'discount_value' => 120,
        ])->assertSessionHasErrors('discount_value');

        $this->actingAs($admin)->post(route('admin.clearance.store'), [
            'product_ids' => [$product->id],
            'discount_type' => 'fixed',
            'discount_value' => 800,
        ])->assertSessionHasErrors('discount_value');

        $this->assertSame(0, ClearanceItem::count());
    }

    public function test_editable_product_details_and_gallery_are_product_specific(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Handwoven Waffle Towel',
            'numeric_price' => 899,
            'stock_quantity' => 15,
            'in_stock' => '1',
            'material' => 'Organic Turkish Cotton',
            'image' => '/images/products/towel-1.webp',
            'highlights' => ['Quick-Dry Waffle Weave', 'Zero Lint Shedding'],
            'fabric_summary' => 'Woven in small batches for spa-grade absorbency.',
            'weave_density' => '550 GSM',
            'craft_origin' => 'Karur, Tamil Nadu',
            'wash_care' => "Cold gentle wash\nTumble dry low",
            'shipping_returns' => 'Ships within 24 hours. 14-day easy exchange.',
        ])->assertRedirect(route('admin.products.index'));

        $productA = Product::where('slug', 'handwoven-waffle-towel')->firstOrFail();

        $productB = Product::create([
            'name' => 'Plain Linen Runner',
            'slug' => 'plain-linen-runner',
            'category' => 'Table Decor',
            'category_slug' => 'table-decor',
            'price' => '₹799',
            'numeric_price' => 799,
            'stock_quantity' => 10,
            'in_stock' => true,
            'image' => '/images/products/runner.webp',
        ]);

        // Product A shows its configured highlights, fabric details, wash care, and shipping
        $this->get(route('products.show', $productA->id))
            ->assertOk()
            ->assertSee('Quick-Dry Waffle Weave')
            ->assertSee('Zero Lint Shedding')
            ->assertSee('550 GSM')
            ->assertSee('Karur, Tamil Nadu')
            ->assertSee('Cold gentle wash')
            ->assertSee('Ships within 24 hours');

        // Product B does NOT show Product A's details or old hardcoded highlights
        $this->get(route('products.show', $productB->id))
            ->assertOk()
            ->assertDontSee('Quick-Dry Waffle Weave')
            ->assertDontSee('100% Breathable Weave')
            ->assertDontSee('550 GSM');
    }

    public function test_system_clear_cache_requires_admin_authentication(): void
    {
        $this->get(route('system.clear-cache'))
            ->assertRedirect(route('admin.login'));

        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $this->actingAs($viewer)
            ->get(route('system.clear-cache'))
            ->assertStatus(403);
    }
}
