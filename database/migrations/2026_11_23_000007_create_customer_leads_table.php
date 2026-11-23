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
        Schema::create('customer_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_type')->default('guest')->index(); // guest, registered, google
            $table->string('name')->index();
            $table->string('email')->index();
            $table->string('phone')->nullable()->index();
            $table->text('delivery_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('customer_gstin')->nullable();
            $table->timestamp('registration_date')->nullable();
            $table->timestamp('first_purchase_date')->nullable();
            $table->timestamp('last_purchase_date')->nullable();
            $table->unsignedInteger('total_orders')->default(0);
            $table->decimal('total_purchase_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_leads');
    }
};
