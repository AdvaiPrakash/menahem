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
        Schema::create('clearance_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')
                ->unique()
                ->constrained('products')
                ->cascadeOnDelete();
            $table->string('discount_type', 20)->default('percentage'); // percentage | fixed
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->boolean('show_on_homepage')->default(true)->index();
            $table->boolean('show_in_all_products')->default(true)->index();
            $table->integer('display_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->json('highlights')->nullable()->after('gallery');
            $table->json('fabric_details')->nullable()->after('highlights');
            $table->text('wash_care')->nullable()->after('fabric_details');
            $table->text('shipping_returns')->nullable()->after('wash_care');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['highlights', 'fabric_details', 'wash_care', 'shipping_returns']);
        });

        Schema::dropIfExists('clearance_items');
    }
};
