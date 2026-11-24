<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\InquiryReplyMail;
use App\Mail\NewInquiryNotificationMail;
use App\Models\Inquiry;
use App\Models\InquiryReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminInquiryReplyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $viewer;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Menahem Admin',
            'email' => 'admin@menahem.test',
            'role' => UserRole::Admin,
        ]);

        $this->viewer = User::factory()->create([
            'name' => 'Read Only Viewer',
            'email' => 'viewer@menahem.test',
            'role' => UserRole::Viewer,
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@menahem.test',
            'role' => UserRole::Customer,
        ]);
    }

    public function test_hostinger_smtp_configuration_uses_env_and_never_exposes_password_in_admin_views(): void
    {
        config([
            'mail.mailers.smtp.host' => 'smtp.hostinger.com',
            'mail.mailers.smtp.port' => 465,
            'mail.mailers.smtp.scheme' => 'smtps',
            'mail.mailers.smtp.username' => 'info@menahem.co.in',
            'mail.mailers.smtp.password' => 'super-secret-smtp-pass-999',
            'mail.enquiry_from.address' => 'info@menahem.co.in',
        ]);

        $inquiry = Inquiry::create([
            'name' => 'Ananya Nair',
            'email' => 'ananya@example.com',
            'phone' => '+919876543210',
            'type' => 'Curtains & Drapes',
            'subject' => 'Custom sheer curtains',
            'message' => 'Please share fabric options.',
            'status' => 'new',
        ]);

        $res = $this->actingAs($this->admin)->get(route('admin.inquiries.show', $inquiry));
        $res->assertOk();
        $res->assertSee('Ananya Nair');
        $res->assertSee('ananya@example.com');
        $res->assertSee('Custom sheer curtains');
        $res->assertSee('info@menahem.co.in');
        $res->assertSee('Send Reply');
        $res->assertDontSee('super-secret-smtp-pass-999');
    }

    public function test_unauthorized_users_cannot_view_or_reply_to_enquiries(): void
    {
        $inquiry = Inquiry::create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'type' => 'general',
            'message' => 'Hello',
            'status' => 'new',
        ]);

        $this->actingAs($this->customer)
            ->get(route('admin.inquiries.show', $inquiry))
            ->assertStatus(403);

        $this->actingAs($this->customer)
            ->post(route('admin.inquiries.reply', $inquiry), ['message' => 'Test'])
            ->assertStatus(403);

        $this->actingAs($this->viewer)
            ->post(route('admin.inquiries.reply', $inquiry), ['message' => 'Test'])
            ->assertStatus(403);
    }

    public function test_admin_can_send_reply_which_emails_customer_saves_history_and_updates_status_to_replied(): void
    {
        Mail::fake();

        $inquiry = Inquiry::create([
            'name' => 'Radhika Menon',
            'email' => 'radhika@example.com',
            'phone' => '+919820012345',
            'type' => 'Curtains & Drapes',
            'subject' => 'Living room blackout drapes',
            'message' => 'Looking for 9ft ivory linen blackout curtains.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.inquiries.reply', $inquiry), [
            'subject' => 'Re: Living room blackout drapes',
            'message' => "Thank you for contacting Menahem.\nWe can custom-tailor 9ft ivory linen drapes within 5 business days.",
            'idempotency_key' => 'reply-key-001',
        ]);

        $response->assertRedirect(route('admin.inquiries.show', $inquiry));
        $response->assertSessionHas('success');

        Mail::assertSent(InquiryReplyMail::class, function (InquiryReplyMail $mail) use ($inquiry): bool {
            return $mail->hasTo($inquiry->email)
                && $mail->hasFrom('info@menahem.co.in')
                && $mail->replySubject === 'Re: Living room blackout drapes'
                && str_contains($mail->replyMessage, 'ivory linen drapes');
        });

        $this->assertDatabaseHas('inquiry_replies', [
            'inquiry_id' => $inquiry->id,
            'sender_email' => 'info@menahem.co.in',
            'recipient_email' => 'radhika@example.com',
            'subject' => 'Re: Living room blackout drapes',
            'status' => 'sent',
            'idempotency_key' => 'reply-key-001',
        ]);

        $this->assertEquals('replied', $inquiry->fresh()->status);

        // Conversation history renders on show page and index displays Replied badge
        $showRes = $this->actingAs($this->admin)->get(route('admin.inquiries.show', $inquiry));
        $showRes->assertOk();
        $showRes->assertSee('Looking for 9ft ivory linen blackout curtains.');
        $showRes->assertSee('custom-tailor 9ft ivory linen drapes');
        $showRes->assertSee('Replied');
        $showRes->assertSee('Sent');

        $indexRes = $this->actingAs($this->admin)->get(route('admin.inquiries.index', ['status' => 'replied']));
        $indexRes->assertOk();
        $indexRes->assertSee('Radhika Menon');
        $indexRes->assertSee('View / Reply');
    }

    public function test_admin_can_send_multiple_replies_and_prevent_duplicate_button_clicks(): void
    {
        Mail::fake();

        $inquiry = Inquiry::create([
            'name' => 'Vikram Rao',
            'email' => 'vikram@example.com',
            'type' => 'Bedsheets & Dohars',
            'message' => 'Do you ship king-size dohars to Pune?',
            'status' => 'new',
        ]);

        // Duplicate click with the same idempotency_key only sends once
        $this->actingAs($this->admin)->post(route('admin.inquiries.reply', $inquiry), [
            'message' => 'Yes, we deliver across Pune within 3-4 days.',
            'idempotency_key' => 'dup-click-key-1',
        ])->assertRedirect();

        $this->actingAs($this->admin)->post(route('admin.inquiries.reply', $inquiry), [
            'message' => 'Yes, we deliver across Pune within 3-4 days.',
            'idempotency_key' => 'dup-click-key-1',
        ])->assertRedirect();

        Mail::assertSent(InquiryReplyMail::class, 1);
        $this->assertEquals(1, InquiryReply::where('inquiry_id', $inquiry->id)->count());

        // Second distinct reply in ongoing conversation
        $this->actingAs($this->admin)->post(route('admin.inquiries.reply', $inquiry), [
            'message' => 'We have also attached fabric care instructions.',
            'idempotency_key' => 'follow-up-key-2',
        ])->assertRedirect();

        Mail::assertSent(InquiryReplyMail::class, 2);
        $this->assertEquals(2, InquiryReply::where('inquiry_id', $inquiry->id)->count());

        $showRes = $this->actingAs($this->admin)->get(route('admin.inquiries.show', $inquiry));
        $showRes->assertSee('Yes, we deliver across Pune within 3-4 days.');
        $showRes->assertSee('We have also attached fabric care instructions.');
    }

    public function test_failed_smtp_send_marks_status_failed_preserves_enquiry_and_redacts_password(): void
    {
        config(['mail.mailers.smtp.password' => 'secret-hostinger-pass']);

        Mail::shouldReceive('to')
            ->once()
            ->with('kiran@example.com', 'Kiran Joshi')
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP auth failed using password secret-hostinger-pass'));

        $inquiry = Inquiry::create([
            'name' => 'Kiran Joshi',
            'email' => 'kiran@example.com',
            'type' => 'general',
            'message' => 'Need fabric swatch samples.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.inquiries.reply', $inquiry), [
            'message' => 'We will courier the swatch kit tomorrow.',
            'idempotency_key' => 'fail-test-key',
        ]);

        $response->assertRedirect(route('admin.inquiries.show', $inquiry));
        $response->assertSessionHas('error');

        $inquiry->refresh();
        $this->assertEquals('failed', $inquiry->status);
        $this->assertEquals('Need fabric swatch samples.', $inquiry->message);

        $reply = InquiryReply::where('inquiry_id', $inquiry->id)->firstOrFail();
        $this->assertEquals('failed', $reply->status);
        $this->assertNull($reply->sent_at);
        $this->assertStringNotContainsString('secret-hostinger-pass', (string) $reply->error_message);
        $this->assertStringContainsString('[REDACTED]', (string) $reply->error_message);
    }

    public function test_enquiry_without_valid_email_cannot_receive_email_reply(): void
    {
        Mail::fake();

        $inquiry = Inquiry::create([
            'name' => 'Phone Only Lead',
            'email' => null,
            'phone' => '+919999988888',
            'type' => 'general',
            'message' => 'Call me back.',
            'status' => 'new',
        ]);

        $res = $this->actingAs($this->admin)->post(route('admin.inquiries.reply', $inquiry), [
            'message' => 'Trying to reply via email.',
        ]);

        $res->assertRedirect(route('admin.inquiries.show', $inquiry));
        $res->assertSessionHas('error');
        Mail::assertNothingSent();
        $this->assertDatabaseCount('inquiry_replies', 0);
        $this->assertEquals('new', $inquiry->fresh()->status);
    }

    public function test_public_enquiry_submission_continues_to_work_with_optional_subject(): void
    {
        Mail::fake();

        $res = $this->post(route('inquiries.store'), [
            'name' => 'Meera Kapoor',
            'email' => 'meera@example.com',
            'phone' => '+919811122233',
            'type' => 'Curtains & Drapes',
            'subject' => 'Bedroom Sheer Drapes Quote',
            'message' => 'Need pricing for 4 windows.',
        ]);

        $res->assertStatus(302);
        $this->assertDatabaseHas('inquiries', [
            'name' => 'Meera Kapoor',
            'email' => 'meera@example.com',
            'subject' => 'Bedroom Sheer Drapes Quote',
            'status' => 'new',
        ]);

        Mail::assertSent(NewInquiryNotificationMail::class, function (NewInquiryNotificationMail $mail): bool {
            return $mail->hasTo('info@menahem.co.in')
                && $mail->hasFrom('info@menahem.co.in')
                && $mail->hasReplyTo('meera@example.com')
                && $mail->inquiry->name === 'Meera Kapoor'
                && $mail->inquiry->message === 'Need pricing for 4 windows.';
        });
    }

    public function test_public_enquiry_still_saves_to_database_if_notification_email_fails(): void
    {
        Mail::shouldReceive('to')
            ->once()
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('Temporary SMTP outage'));

        $res = $this->postJson(route('inquiries.store'), [
            'name' => 'Rohan Verma',
            'email' => 'rohan@example.com',
            'phone' => '+919800011122',
            'type' => 'Bedsheets & Dohars',
            'message' => 'Bulk hotel order inquiry.',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('inquiries', [
            'name' => 'Rohan Verma',
            'email' => 'rohan@example.com',
            'status' => 'new',
        ]);
    }
}
