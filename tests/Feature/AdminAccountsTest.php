<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_page_shows_ledger_filters_exports_and_cancels_bill_with_stock_restore(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $product = Product::create([
            'name' => 'Handwoven Cotton Runner',
            'slug' => 'handwoven-cotton-runner',
            'category' => 'Table Decor',
            'category_slug' => 'table-decor',
            'price' => '₹1,000',
            'numeric_price' => 1000,
            'gst_rate' => 5.00,
            'stock_quantity' => 10,
            'in_stock' => true,
        ]);

        // Create a manual bill with split payment
        $this->actingAs($admin)->post(route('admin.billing.store'), [
            'customer_name' => 'Kiran Joshi',
            'customer_phone' => '+919820055443',
            'is_split_payment' => '1',
            'edited_final_amount' => 900,
            'split_payments' => [
                ['method' => 'UPI', 'amount' => 600],
                ['method' => 'Cash', 'amount' => 300],
            ],
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 500],
            ],
        ])->assertSessionHasNoErrors();

        $order = Order::latest('id')->firstOrFail();
        $this->assertSame(8, $product->fresh()->stock_quantity);

        // Accounts index displays the bill and summary
        $index = $this->actingAs($admin)->get(route('admin.accounts.index'))->assertOk();
        $index->assertSee('Accounts & Billing Ledger');
        $index->assertSee($order->order_number);
        $index->assertSee('Kiran Joshi');
        $index->assertSee('Export Excel');
        $index->assertSee('Export PDF');

        // Date + payment method filter works
        $this->actingAs($admin)->get(route('admin.accounts.index', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'payment_method' => 'Split',
        ]))->assertOk()->assertSee($order->order_number);

        // Excel (CSV) export downloads cleanly
        $excel = $this->actingAs($admin)->get(route('admin.accounts.export-excel'));
        $excel->assertOk();
        $this->assertStringContainsString('text/csv', (string) $excel->headers->get('content-type'));
        $csvContent = $excel->streamedContent();
        $this->assertStringContainsString($order->order_number, $csvContent);
        $this->assertStringContainsString('Kiran Joshi', $csvContent);

        // PDF export downloads cleanly
        $pdf = $this->actingAs($admin)->get(route('admin.accounts.export-pdf'));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));

        // Cancel bill from Accounts -> marks cancelled/refunded and restores stock
        $this->actingAs($admin)
            ->post(route('admin.accounts.cancel', $order), ['cancellation_reason' => 'Customer returned at counter'])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('cancelled', $order->order_status);
        $this->assertSame('refunded', $order->payment_status);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }
}
