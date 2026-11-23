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
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->unique()->after('slug');
            $table->string('hsn_code')->default('6304')->after('material');
            $table->decimal('gst_rate', 5, 2)->default(12.00)->after('hsn_code');
            $table->integer('stock_quantity')->default(50)->after('gallery');
            $table->integer('opening_stock')->default(50)->after('stock_quantity');
            $table->integer('reserved_stock')->default(0)->after('opening_stock');
            $table->integer('sold_quantity')->default(0)->after('reserved_stock');
            $table->integer('low_stock_threshold')->default(10)->after('sold_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'sku',
                'hsn_code',
                'gst_rate',
                'stock_quantity',
                'opening_stock',
                'reserved_stock',
                'sold_quantity',
                'low_stock_threshold',
            ]);
        });
    }
};
