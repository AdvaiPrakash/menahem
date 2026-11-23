<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryOrderCartEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected Category $parentCat;

    protected Category $childCat;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@menahem.test',
            'role' => UserRole::Admin,
        ]);

        $this->customer = User::factory()->create([
            'email' => 'customer@menahem.test',
            'role' => UserRole::Customer,
        ]);

        $this->parentCat = Category::create([
            'name' => 'Bedsheets & Dohars',
            'slug' => 'bedsheets-dohars',
            'is_active' => true,
        ]);

        $this->childCat = Category::create([
            'name' => 'Cotton Bedsheets',
            'slug' => 'cotton-bedsheets',
            'parent_id' => $this->parentCat->id,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Signature Cotton Bedsheet',
            'slug' => 'signature-cotton-bedsheet',
            'category_id' => $this->childCat->id,
            'category_slug' => $this->childCat->slug,
            'sku' => 'COT-001',
            'hsn_code' => '6304',
            'price' => 1500.00,
            'gst_rate' => 12.00,
            'stock_quantity' => 25,
            'is_active' => true,
        ]);

        Setting::setMany([
            'seller_business_name' => 'MENAHEM HOME & LIVING',
            'seller_address' => 'Plot 12, Industrial Area, Surat, Gujarat - 395002',
            'seller_gstin' => '24ABCDE1234F1Z5',
            'seller_state' => 'Gujarat',
            'seller_state_code' => '24',
        ]);
    }

    /* -------------------------------------------------------------------------
     * MODULE 1: Category & Subcategory Management Redesign
     * ---------------------------------------------------------------------- */

    public function test_admin_can_view_category_tree_and_main_views(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.index', ['view' => 'tree']));
        $response->assertStatus(200);
        $response->assertSee('Expandable Tree View');
        $response->assertSee('Cotton Bedsheets');

        $mainResponse = $this->actingAs($this->admin)->get(route('admin.categories.index', ['view' => 'main']));
        $mainResponse->assertStatus(200);
        $mainResponse->assertSee('Main Categories');

        $subResponse = $this->actingAs($this->admin)->get(route('admin.categories.index', ['parent_id' => $this->parentCat->id]));
        $subResponse->assertStatus(200);
        $subResponse->assertSee('Cotton Bedsheets');
    }

    public function test_admin_can_create_sub_subcategory_with_auto_slug(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'King Size',
            'slug' => '', // should auto-generate
            'parent_id' => $this->childCat->id,
            'is_active' => '1',
            'description' => 'King size bedsheets collection',
        ]);

        $response->assertRedirect();

        $subSub = Category::where('name', 'King Size')->first();
        $this->assertNotNull($subSub);
        $this->assertEquals($this->childCat->id, $subSub->parent_id);
        $this->assertEquals('king-size', $subSub->slug);
        $this->assertTrue((bool) $subSub->is_active);
    }

    public function test_admin_can_toggle_category_active_status(): void
    {
        $this->assertTrue((bool) $this->childCat->is_active);

        $response = $this->actingAs($this->admin)->patch(route('admin.categories.toggle-status', $this->childCat));
        $response->assertRedirect();

        $this->assertFalse((bool) $this->childCat->fresh()->is_active);
    }

    public function test_category_cannot_set_self_or_child_as_parent(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $this->parentCat), [
            'name' => $this->parentCat->name,
            'slug' => $this->parentCat->slug,
            'parent_id' => $this->parentCat->id, // Self as parent
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    /* -------------------------------------------------------------------------
     * MODULE 2: Order Management & Invoice Download
     * ---------------------------------------------------------------------- */

    public function test_admin_can_view_orders_list_and_details(): void
    {
        $order = Order::create([
            'order_number' => 'MNH-20261124-001',
            'customer_name' => 'Priya Patel',
            'customer_email' => 'priya@example.com',
            'customer_phone' => '+91 98200 11223',
            'shipping_address' => [
                'name' => 'Priya Patel',
                'line1' => 'Flat 101, Green Apts',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pin_code' => '380001',
            ],
            'billing_address' => [
                'name' => 'Priya Patel',
                'line1' => 'Flat 101, Green Apts',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pin_code' => '380001',
            ],
            'subtotal' => 1500.00,
            'discount_amount' => 0.00,
            'taxable_amount' => 1339.29,
            'cgst_amount' => 80.36,
            'sgst_amount' => 80.35,
            'igst_amount' => 0.00,
            'shipping_fee' => 0.00,
            'grand_total' => 1500.00,
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
            'order_status' => 'confirmed',
            'paid_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'hsn_code' => $this->product->hsn_code,
            'unit_price' => 1500.00,
            'quantity' => 1,
            'discount' => 0.00,
            'taxable_value' => 1339.29,
            'gst_rate' => 12.00,
            'cgst_amount' => 80.36,
            'sgst_amount' => 80.35,
            'igst_amount' => 0.00,
            'total_amount' => 1500.00,
        ]);

        $listRes = $this->actingAs($this->admin)->get(route('admin.orders.index'));
        $listRes->assertStatus(200);
        $listRes->assertSee('MNH-20261124-001');
        $listRes->assertSee('Priya Patel');

        $showRes = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));
        $showRes->assertStatus(200);
        $showRes->assertSee('MNH-20261124-001');
        $showRes->assertSee('Download Invoice');
    }

    public function test_admin_can_update_order_status_and_download_invoice(): void
    {
        $order = Order::create([
            'order_number' => 'MNH-20261124-002',
            'customer_name' => 'Rahul Sharma',
            'customer_email' => 'rahul@example.com',
            'customer_phone' => '+91 98200 44556',
            'shipping_address' => [
                'name' => 'Rahul Sharma',
                'line1' => '202 Marine Lines',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pin_code' => '400020',
            ],
            'billing_address' => [
                'name' => 'Rahul Sharma',
                'line1' => '202 Marine Lines',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pin_code' => '400020',
            ],
            'subtotal' => 1500.00,
            'discount_amount' => 0.00,
            'taxable_amount' => 1339.29,
            'cgst_amount' => 0.00,
            'sgst_amount' => 0.00,
            'igst_amount' => 160.71,
            'shipping_fee' => 0.00,
            'grand_total' => 1500.00,
            'payment_status' => 'paid',
            'payment_method' => 'razorpay',
            'order_status' => 'processing',
            'paid_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'hsn_code' => $this->product->hsn_code,
            'unit_price' => 1500.00,
            'quantity' => 1,
            'discount' => 0.00,
            'taxable_value' => 1339.29,
            'gst_rate' => 12.00,
            'cgst_amount' => 0.00,
            'sgst_amount' => 0.00,
            'igst_amount' => 160.71,
            'total_amount' => 1500.00,
        ]);

        $updateRes = $this->actingAs($this->admin)->patch(route('admin.orders.status', $order), [
            'order_status' => 'shipped',
            'payment_status' => 'paid',
            'status_note' => 'Dispatched via BlueDart AWB#123456789',
        ]);

        $updateRes->assertRedirect();
        $this->assertEquals('shipped', $order->fresh()->order_status);

        $downloadRes = $this->actingAs($this->admin)->get(route('admin.orders.invoice', $order));
        $downloadRes->assertStatus(200);
        $this->assertEquals('application/pdf', $downloadRes->headers->get('content-type'));
    }

    /* -------------------------------------------------------------------------
     * MODULE 3: Shopping Cart Persistence & Wishlist Management
     * ---------------------------------------------------------------------- */

    public function test_guest_cart_starts_empty_and_can_add_update_remove_items(): void
    {
        $guestToken = 'test_guest_token_'.uniqid();

        // 1. Initial cart is empty
        $indexRes = $this->withHeader('X-Guest-Token', $guestToken)->getJson(route('cart.index'));
        $indexRes->assertStatus(200);
        $this->assertEquals(0, $indexRes->json('count'));
        $this->assertEmpty($indexRes->json('items'));

        // 2. Add item to cart
        $addRes = $this->withHeader('X-Guest-Token', $guestToken)->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'custom_size' => 'King - 108x108',
        ]);
        $addRes->assertStatus(200);
        $this->assertEquals(2, $addRes->json('count'));
        $this->assertCount(1, $addRes->json('items'));

        // Check database
        $this->assertDatabaseHas('cart_items', [
            'guest_token' => $guestToken,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'custom_size' => 'King - 108x108',
        ]);

        // 3. Update quantity
        $cartItemId = $addRes->json('items.0.id');
        $updateRes = $this->withHeader('X-Guest-Token', $guestToken)->patchJson(route('cart.update', ['id' => $cartItemId]), [
            'quantity' => 3,
        ]);
        $updateRes->assertStatus(200);
        $this->assertEquals(3, $updateRes->json('count'));

        // 4. Remove item
        $removeRes = $this->withHeader('X-Guest-Token', $guestToken)->deleteJson(route('cart.remove', ['id' => $cartItemId]));
        $removeRes->assertStatus(200);
        $this->assertEquals(0, $removeRes->json('count'));
        $this->assertDatabaseMissing('cart_items', [
            'guest_token' => $guestToken,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_guest_cart_merges_to_customer_account_on_login(): void
    {
        $guestToken = 'guest_token_merge_'.uniqid();

        // Guest adds product to cart
        $cartService = app(CartService::class);
        $cartService->addItem(null, $guestToken, $this->product->id, 2, 'Queen');

        $this->assertDatabaseHas('cart_items', [
            'guest_token' => $guestToken,
            'user_id' => null,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        // Login as customer with guest token
        $loginRes = $this->withCookie('menahem_guest_token', $guestToken)
            ->post(route('customer.login.submit'), [
                'email' => $this->customer->email,
                'password' => 'password',
            ]);

        $loginRes->assertRedirect(route('account.orders'));

        // Verify cart items are now attached to user_id
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->customer->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    public function test_wishlist_toggle_and_view(): void
    {
        $guestToken = 'wishlist_token_'.uniqid();

        // 1. Check initial status
        $statusRes = $this->withHeader('X-Guest-Token', $guestToken)
            ->getJson(route('wishlist.status'));
        $statusRes->assertStatus(200);
        $this->assertEquals(0, $statusRes->json('count'));
        $this->assertFalse(in_array($this->product->id, $statusRes->json('ids')));

        // 2. Toggle item into wishlist
        $toggleRes = $this->withHeader('X-Guest-Token', $guestToken)
            ->postJson(route('wishlist.toggle'), ['product_id' => $this->product->id]);
        $toggleRes->assertStatus(200);
        $this->assertTrue($toggleRes->json('liked'));
        $this->assertEquals(1, $toggleRes->json('count'));

        $this->assertDatabaseHas('wishlists', [
            'guest_token' => $guestToken,
            'product_id' => $this->product->id,
        ]);

        // 3. View wishlist page
        $viewRes = $this->withCookie('menahem_guest_token', $guestToken)->get(route('wishlist.index'));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('My Wishlist');
        $viewRes->assertSee('Signature Cotton Bedsheet');

        // 4. Toggle item out of wishlist
        $toggleOutRes = $this->withHeader('X-Guest-Token', $guestToken)
            ->postJson(route('wishlist.toggle'), ['product_id' => $this->product->id]);
        $toggleOutRes->assertStatus(200);
        $this->assertFalse($toggleOutRes->json('liked'));
        $this->assertEquals(0, $toggleOutRes->json('count'));
    }
}
