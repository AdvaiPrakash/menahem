<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\CustomerLead;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeadsAndWalkinEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_walkin_billing_sends_invoice_email_only_when_valid_email_is_provided(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Kochi Cotton Bath Towel',
            'slug' => 'kochi-cotton-bath-towel',
            'category' => 'Towels',
            'category_slug' => 'towels',
            'price' => '₹400',
            'numeric_price' => 400,
            'gst_rate' => 5.00,
            'stock_quantity' => 20,
            'in_stock' => true,
        ]);

        // 1. Walk-in without email -> NO email sent, bill succeeds
        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'customer_name' => 'Suresh Kumar',
            'customer_phone' => '+919820011001',
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 400],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, Mail::sent(Mailable::class)->count());

        // 2. Walk-in with valid email -> email sent to customer with PDF attachment
        $sentToCustomer = false;
        $hasPdfAttachment = false;
        Mail::shouldReceive('send')
            ->once()
            ->withArgs(function ($view, $data, $callback) use (&$sentToCustomer, &$hasPdfAttachment): bool {
                $sentToCustomer = $view === 'emails.invoice' && ($data['invoice']->customer_email ?? '') === 'divya@example.com';
                $hasPdfAttachment = $callback instanceof \Closure;

                return true;
            });

        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'customer_name' => 'Divya Nair',
            'customer_email' => 'divya@example.com',
            'customer_phone' => '+919820011002',
            'payment_method' => 'UPI',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 400],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertTrue($sentToCustomer);
        $this->assertTrue($hasPdfAttachment);
    }

    public function test_manual_order_and_walkin_billing_modes_and_invoice_ship_to_behavior(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Handloom Dohar Blanket',
            'slug' => 'handloom-dohar-blanket',
            'category' => 'Bedsheets',
            'category_slug' => 'bedsheets',
            'price' => '₹1,000',
            'numeric_price' => 1000,
            'gst_rate' => 5.00,
            'stock_quantity' => 20,
            'in_stock' => true,
        ]);

        // Billing page renders both Walk-in Purchase and Manual Order toggles
        $this->actingAs($admin)->get(route('admin.billing.create'))
            ->assertOk()
            ->assertSee('Walk-in Purchase')
            ->assertSee('Manual Order');

        // 1. Walk-in Purchase without shipping address -> Shipped To omitted on invoice PDF
        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'billing_mode' => 'walk_in',
            'customer_name' => 'Walkin Buyer',
            'customer_phone' => '+919820011220',
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1000],
            ],
        ])->assertSessionHasNoErrors();

        $walkInOrder = Order::where('customer_name', 'Walkin Buyer')->with(['items', 'invoice'])->firstOrFail();
        $walkInHtml = view('invoices.pdf', ['invoice' => $walkInOrder->invoice, 'order' => $walkInOrder])->render();
        $this->assertStringNotContainsString('Shipped To (Delivery Destination)', $walkInHtml);
        $this->assertStringContainsString('SL No.', $walkInHtml);
        $this->assertStringContainsString('Total Amount', $walkInHtml);

        // 2. Manual Order with delivery address + email -> Shipped To included + Manual Order email sent
        $manualOrderEmailSent = false;
        Mail::shouldReceive('send')
            ->once()
            ->withArgs(function ($view, $data) use (&$manualOrderEmailSent): bool {
                $manualOrderEmailSent = $view === 'emails.invoice'
                    && ($data['isManualOrder'] ?? false) === true
                    && ($data['invoice']->customer_email ?? '') === 'manualorder@example.com';

                return true;
            });

        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'billing_mode' => 'manual_order',
            'customer_name' => 'Phone Order Buyer',
            'customer_email' => 'manualorder@example.com',
            'customer_phone' => '+919820011221',
            'shipping_address_line1' => '45 Marine Drive',
            'shipping_city' => 'Kochi',
            'shipping_pin_code' => '682019',
            'payment_method' => 'UPI',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertTrue($manualOrderEmailSent);

        $manualOrder = Order::where('customer_name', 'Phone Order Buyer')->with(['items', 'invoice'])->firstOrFail();
        $manualHtml = view('invoices.pdf', ['invoice' => $manualOrder->invoice, 'order' => $manualOrder])->render();
        $this->assertStringContainsString('Shipped To (Delivery Destination)', $manualHtml);
        $this->assertStringContainsString('45 Marine Drive', $manualHtml);

        // Visible in Order Management
        $this->actingAs($admin)->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee($manualOrder->order_number);
    }

    public function test_leads_preserve_separate_names_for_shared_phone_and_support_profile_download(): void
    {
        Mail::fake();
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Handloom Cushion Set',
            'slug' => 'handloom-cushion-set',
            'category' => 'Cushions',
            'category_slug' => 'cushions',
            'price' => '₹600',
            'numeric_price' => 600,
            'gst_rate' => 5.00,
            'stock_quantity' => 20,
            'in_stock' => true,
        ]);

        // Two different walk-in customers sharing the same phone number
        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'customer_name' => 'Anil Menon',
            'customer_phone' => '+919847000111',
            'payment_method' => 'Cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 600],
            ],
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'customer_name' => 'Lakshmi Menon',
            'customer_phone' => '+919847000111',
            'payment_method' => 'UPI',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 600],
            ],
        ])->assertSessionHasNoErrors();

        $anilLead = CustomerLead::where('name', 'Anil Menon')->first();
        $lakshmiLead = CustomerLead::where('name', 'Lakshmi Menon')->first();

        $this->assertNotNull($anilLead);
        $this->assertNotNull($lakshmiLead);
        $this->assertNotSame($anilLead->id, $lakshmiLead->id);

        // View Profile shows Manual Billing history and other profile sharing same phone
        $profile = $this->actingAs($admin)->get(route('admin.leads.show', $anilLead))->assertOk();
        $profile->assertSee('Anil Menon');
        $profile->assertSee('Manual Billing');
        $profile->assertSee('Lakshmi Menon');
        $profile->assertSee('Download Customer Details');

        // Download complete customer details CSV
        $download = $this->actingAs($admin)->get(route('admin.leads.download', $anilLead))->assertOk();
        $this->assertStringContainsString('text/csv', (string) $download->headers->get('content-type'));
        $csv = $download->streamedContent();
        $this->assertStringContainsString('Anil Menon', $csv);
        $this->assertStringContainsString('Handloom Cushion Set', $csv);
        $this->assertStringContainsString('Manual Billing', $csv);
    }
}
