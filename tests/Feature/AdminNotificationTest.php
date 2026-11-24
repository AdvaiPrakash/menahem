<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_customers_cannot_poll_admin_notifications(): void
    {
        $this->getJson(route('admin.notifications.poll'))
            ->assertRedirect(route('admin.login'));

        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)
            ->getJson(route('admin.notifications.poll'))
            ->assertStatus(403);
    }

    public function test_admin_layout_renders_notification_ui_and_voice_audio_files(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->assertFileExists(public_path('audio/enquiry-notification.mp3'));
        $this->assertFileExists(public_path('audio/order-notification.mp3'));

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('admin-notification-bell-btn');
        $response->assertSee('admin-voice-toggle-btn');
        $response->assertSee('admin-notification-toast-container');
        $response->assertSee('/audio/enquiry-notification.mp3', false);
        $response->assertSee('/audio/order-notification.mp3', false);
        $response->assertSee(json_encode(route('admin.notifications.poll')), false);
    }

    public function test_poll_returns_new_website_enquiry_with_enquiry_voice_url(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $existingInquiry = Inquiry::create([
            'name' => 'Existing Visitor',
            'email' => 'existing@example.com',
            'type' => 'general',
            'status' => 'new',
        ]);

        $sinceOrderAt = Carbon::now()->toIso8601String();

        $this->post(route('inquiries.store'), [
            'name' => 'Radhika Sharma',
            'email' => 'radhika@example.com',
            'phone' => '+919876543210',
            'type' => 'Curtains & Drapes',
            'message' => 'Need custom floor-to-ceiling sheer drapes.',
        ])->assertStatus(302);

        $newInquiry = Inquiry::latest('id')->first();
        $this->assertNotNull($newInquiry);
        $this->assertGreaterThan($existingInquiry->id, $newInquiry->id);

        $response = $this->actingAs($admin)->getJson(route('admin.notifications.poll', [
            'after_inquiry_id' => $existingInquiry->id,
            'since_order_at' => $sinceOrderAt,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.type', 'inquiry')
            ->assertJsonPath('notifications.0.key', "inquiry-{$newInquiry->id}")
            ->assertJsonPath('notifications.0.title', 'New Enquiry Received')
            ->assertJsonPath('notifications.0.url', route('admin.inquiries.show', $newInquiry))
            ->assertJsonPath('notifications.0.audio_url', asset('audio/enquiry-notification.mp3'))
            ->assertJsonPath('cursor.after_inquiry_id', $newInquiry->id)
            ->assertJsonPath('counts.new_inquiries', 2);
    }

    public function test_poll_ignores_unpaid_pending_orders_and_notifies_when_order_is_paid(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $sinceOrderAt = Carbon::now()->subSeconds(5)->toIso8601String();

        $order = Order::create([
            'order_number' => 'ORD-20261124-TEST01',
            'customer_type' => 'guest',
            'customer_name' => 'Priya Patel',
            'customer_email' => 'priya@example.com',
            'customer_phone' => '+919820011223',
            'shipping_address' => [
                'full_name' => 'Priya Patel',
                'address_line1' => 'Flat 101',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pin_code' => '380001',
            ],
            'billing_address' => [
                'full_name' => 'Priya Patel',
                'address_line1' => 'Flat 101',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pin_code' => '380001',
            ],
            'subtotal' => 2499.00,
            'grand_total' => 2499.00,
            'payment_status' => 'pending',
            'order_status' => 'placed',
            'payment_method' => 'Razorpay',
        ]);

        // Pending unpaid checkout should not emit an order-received notification
        $this->actingAs($viewer)
            ->getJson(route('admin.notifications.poll', [
                'after_inquiry_id' => 0,
                'since_order_at' => $sinceOrderAt,
            ]))
            ->assertOk()
            ->assertJsonCount(0, 'notifications');

        // Once payment completes, the order notification and order voice URL are returned
        $order->update([
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'paid_at' => Carbon::now(),
        ]);

        $paidResponse = $this->actingAs($viewer)->getJson(route('admin.notifications.poll', [
            'after_inquiry_id' => 0,
            'since_order_at' => $sinceOrderAt,
        ]));

        $paidResponse->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.type', 'order')
            ->assertJsonPath('notifications.0.key', "order-{$order->id}")
            ->assertJsonPath('notifications.0.title', 'New Order Received')
            ->assertJsonPath('notifications.0.url', route('admin.orders.show', $order))
            ->assertJsonPath('notifications.0.audio_url', asset('audio/order-notification.mp3'))
            ->assertJsonPath('counts.pending_orders', 1);
    }
}
