<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('invoice_number')->unique()->index();
            $table->date('invoice_date');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_type')->default('guest');
            $table->string('customer_name')->index();
            $table->string('customer_email')->index();
            $table->string('customer_phone');
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();
            $table->string('customer_gstin')->nullable();
            $table->string('seller_name')->default('Menahem Home & Living');
            $table->string('seller_gstin')->default('27AABCM1234F1Z8');
            $table->text('seller_address')->nullable();
            $table->string('seller_state')->default('Maharashtra');
            $table->string('seller_state_code', 5)->default('27');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('taxable_amount', 10, 2);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('shipping_charges', 10, 2)->default(0);
            $table->decimal('gateway_charges', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('payment_status')->default('paid')->index();
            $table->string('pdf_path')->nullable();
            $table->string('pdf_token', 64)->unique()->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
