<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_login_page(): void
    {
        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('MENAHEM');
        $response->assertSee('Sign In to Dashboard');
    }

    public function test_admin_can_authenticate_and_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => UserRole::Admin,
        ]);

        $loginResponse = $this->post(route('admin.login.submit'), [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        $dashResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Dashboard Overview');
    }

    public function test_admin_can_authenticate_with_username_admin_and_password_admin(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@menahem.com',
            'name' => 'admin',
            'password' => bcrypt('admin'),
            'role' => UserRole::Admin,
        ]);

        $loginResponse = $this->post(route('admin.login.submit'), [
            'email' => 'admin',
            'password' => 'admin',
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_create_a_product(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::create([
            'name' => 'Bedsheets',
            'slug' => 'bedsheets',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Luxury 500TC Sateen Sheet',
            'category_id' => $category->id,
            'numeric_price' => 2499,
            'material' => 'Egyptian Cotton',
            'in_stock' => 1,
            'desc' => 'Finest sateen weave.',
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Luxury 500TC Sateen Sheet',
            'numeric_price' => 2499,
        ]);
    }

    public function test_viewer_cannot_modify_products(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $product = Product::create([
            'name' => 'Existing Product',
            'slug' => 'existing-product',
            'category_slug' => 'general',
            'price' => '₹999',
            'numeric_price' => 999,
            'image' => '/test.webp',
        ]);

        $response = $this->actingAs($viewer)->delete(route('admin.products.destroy', $product));

        $response->assertStatus(403);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_public_inquiry_submission_stores_lead_in_database(): void
    {
        $response = $this->post(route('inquiries.store'), [
            'name' => 'Ananya Roy',
            'email' => 'ananya@example.com',
            'phone' => '+919988776655',
            'type' => 'curtain_measurement',
            'message' => 'Need custom floor-to-ceiling drapes.',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('inquiries', [
            'name' => 'Ananya Roy',
            'email' => 'ananya@example.com',
            'type' => 'curtain_measurement',
            'status' => 'new',
        ]);
    }
}
