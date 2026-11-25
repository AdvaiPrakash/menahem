<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AdminNotification;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_and_customers_cannot_access_admin_notification_endpoints(): void
    {
        $this->getJson(route('admin.notifications.poll'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.notifications.stream'))
            ->assertRedirect(route('admin.login'));

        $this->getJson(route('admin.notifications.live-data', ['context' => 'dashboard']))
            ->assertRedirect(route('admin.login'));

        $this->postJson(route('admin.notifications.read-all'))
            ->assertRedirect(route('admin.login'));

        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)
            ->getJson(route('admin.notifications.poll'))
            ->assertStatus(403);

        $this->actingAs($customer)
            ->getJson(route('admin.notifications.live-data', ['context' => 'orders']))
            ->assertStatus(403);

        $this->actingAs($customer)
            ->postJson(route('admin.notifications.read-all'))
            ->assertStatus(403);
    }

    public function test_admin_layout_renders_notification_ui_sse_and_voice_audio_files(): void
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
        $response->assertSee(json_encode(route('admin.notifications.stream')), false);
        $response->assertSee(json_encode(route('admin.notifications.live-data')), false);
    }

    public function test_poll_returns_new_website_enquiry_without_duplicates(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $existingInquiry = Inquiry::create([
            'name' => 'Existing Visitor',
            'email' => 'existing@example.com',
            'type' => 'general',
            'status' => 'new',
        ]);

        $cursorBefore = (int) AdminNotification::max('id');

        $this->post(route('inquiries.store'), [
            'name' => 'Radhika Sharma',
            'email' => 'radhika@example.com',
            'phone' => '+919876543210',
            'type' => 'Curtains & Drapes',
            'subject' => 'Floor-to-ceiling sheer drapes',
            'message' => 'Need custom floor-to-ceiling sheer drapes.',
        ])->assertStatus(302);

        $newInquiry = Inquiry::latest('id')->first();
        $this->assertNotNull($newInquiry);
        $this->assertGreaterThan($existingInquiry->id, $newInquiry->id);

        $response = $this->actingAs($admin)->getJson(route('admin.notifications.poll', [
            'after_id' => $cursorBefore,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.type', 'inquiry')
            ->assertJsonPath('notifications.0.key', "inquiry:{$newInquiry->id}")
            ->assertJsonPath('notifications.0.title', 'New Enquiry Received')
            ->assertJsonPath('notifications.0.payload.customer_name', 'Radhika Sharma')
            ->assertJsonPath('notifications.0.payload.subject', 'Floor-to-ceiling sheer drapes')
            ->assertJsonPath('notifications.0.url', route('admin.inquiries.show', $newInquiry))
            ->assertJsonPath('notifications.0.audio_url', asset('audio/enquiry-notification.mp3'))
            ->assertJsonPath('counts.new_inquiries', 2);

        $nextCursor = (int) $response->json('cursor.after_id');

        // Re-polling with the returned cursor must not emit duplicate notifications
        $this->actingAs($admin)
            ->getJson(route('admin.notifications.poll', ['after_id' => $nextCursor]))
            ->assertOk()
            ->assertJsonCount(0, 'notifications');
    }

    public function test_new_order_creation_and_payment_confirmation_emit_accurate_notifications(): void
    {
        $viewer = User::factory()->create(['role' => UserRole::Viewer]);
        $cursorBefore = (int) AdminNotification::max('id');

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

        // Order creation must notify immediately with accurate order & payment status
        $createdResponse = $this->actingAs($viewer)
            ->getJson(route('admin.notifications.poll', ['after_id' => $cursorBefore]));

        $createdResponse->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.type', 'order')
            ->assertJsonPath('notifications.0.event_name', 'order.created')
            ->assertJsonPath('notifications.0.key', "order_created:{$order->id}")
            ->assertJsonPath('notifications.0.title', 'New Order Placed')
            ->assertJsonPath('notifications.0.payload.order_number', 'ORD-20261124-TEST01')
            ->assertJsonPath('notifications.0.payload.customer_name', 'Priya Patel')
            ->assertJsonPath('notifications.0.payload.order_status', 'placed')
            ->assertJsonPath('notifications.0.payload.payment_status', 'pending')
            ->assertJsonPath('notifications.0.url', route('admin.orders.show', $order))
            ->assertJsonPath('counts.pending_orders', 1);

        $cursorAfterCreate = (int) $createdResponse->json('cursor.after_id');

        // Once payment completes, order.paid notification is emitted with updated status
        $order->update([
            'payment_status' => 'paid',
            'order_status' => 'confirmed',
            'paid_at' => Carbon::now(),
        ]);

        $paidResponse = $this->actingAs($viewer)
            ->getJson(route('admin.notifications.poll', ['after_id' => $cursorAfterCreate]));

        $paidResponse->assertOk()
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.type', 'order')
            ->assertJsonPath('notifications.0.event_name', 'order.paid')
            ->assertJsonPath('notifications.0.key', "order_paid:{$order->id}")
            ->assertJsonPath('notifications.0.title', 'Order Payment Confirmed')
            ->assertJsonPath('notifications.0.payload.order_status', 'confirmed')
            ->assertJsonPath('notifications.0.payload.payment_status', 'paid');
    }

    public function test_live_data_endpoints_sync_dashboard_inquiries_and_orders_without_refresh(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $inquiry = Inquiry::create([
            'name' => 'Aarav Mehta',
            'email' => 'aarav@example.com',
            'type' => 'custom',
            'subject' => 'Linen Curtains',
            'status' => 'new',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-20261125-LIVE01',
            'customer_type' => 'guest',
            'customer_name' => 'Neha Kapoor',
            'customer_email' => 'neha@example.com',
            'customer_phone' => '+919811122233',
            'shipping_address' => [
                'full_name' => 'Neha Kapoor',
                'address_line1' => '12 MG Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pin_code' => '400001',
            ],
            'billing_address' => [
                'full_name' => 'Neha Kapoor',
                'address_line1' => '12 MG Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pin_code' => '400001',
            ],
            'subtotal' => 4500.00,
            'grand_total' => 4500.00,
            'payment_status' => 'pending',
            'order_status' => 'placed',
            'payment_method' => 'Razorpay',
        ]);

        $dashboardLive = $this->actingAs($admin)
            ->getJson(route('admin.notifications.live-data', ['context' => 'dashboard']))
            ->assertOk()
            ->assertJsonPath('context', 'dashboard');

        $this->assertStringContainsString('Aarav Mehta', (string) $dashboardLive->json('html.recent_inquiries'));
        $this->assertStringContainsString('ORD-20261125-LIVE01', (string) $dashboardLive->json('html.recent_orders'));

        $inquiriesLive = $this->actingAs($admin)
            ->getJson(route('admin.notifications.live-data', ['context' => 'inquiries']))
            ->assertOk()
            ->assertJsonPath('status_counts.all', 1)
            ->assertJsonPath('status_counts.pending', 1);

        $this->assertStringContainsString('Aarav Mehta', (string) $inquiriesLive->json('html.desktop_rows'));
        $this->assertStringContainsString('Linen Curtains', (string) $inquiriesLive->json('html.mobile_cards'));

        $ordersLive = $this->actingAs($admin)
            ->getJson(route('admin.notifications.live-data', ['context' => 'orders']))
            ->assertOk()
            ->assertJsonPath('stats.total', '1');

        $this->assertStringContainsString('ORD-20261125-LIVE01', (string) $ordersLive->json('html.table_rows'));
        $this->assertStringContainsString('Neha Kapoor', (string) $ordersLive->json('html.table_rows'));
    }

    public function test_multiple_admin_users_have_independent_read_state_and_offline_sync(): void
    {
        $adminOne = User::factory()->create(['role' => UserRole::Admin]);
        $adminTwo = User::factory()->create(['role' => UserRole::Editor]);

        Inquiry::create([
            'name' => 'Offline Enquiry',
            'email' => 'offline@example.com',
            'type' => 'general',
            'status' => 'new',
        ]);

        $notification = AdminNotification::latest('id')->firstOrFail();

        // Both admins initially see 1 unread notification
        $this->actingAs($adminOne)
            ->getJson(route('admin.notifications.poll', ['after_id' => 0]))
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'notifications');

        $this->actingAs($adminTwo)
            ->getJson(route('admin.notifications.poll', ['after_id' => 0]))
            ->assertOk()
            ->assertJsonPath('unread_count', 1);

        // Admin One marks it read; Admin Two still sees 1 unread
        $this->actingAs($adminOne)
            ->postJson(route('admin.notifications.read', $notification))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->actingAs($adminTwo)
            ->getJson(route('admin.notifications.poll', ['after_id' => 0]))
            ->assertOk()
            ->assertJsonPath('unread_count', 1);

        // Admin Two marks all read
        $this->actingAs($adminTwo)
            ->postJson(route('admin.notifications.read-all'))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);
    }

    public function test_sse_stream_endpoint_returns_event_stream_headers(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->actingAs($admin)->get(route('admin.notifications.stream', ['after_id' => 0]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/event-stream', (string) $response->headers->get('Content-Type'));
    }
}
