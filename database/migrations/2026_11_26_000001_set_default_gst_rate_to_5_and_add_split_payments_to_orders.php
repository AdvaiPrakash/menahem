<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'gst_rate')) {
            DB::table('products')
                ->whereNull('gst_rate')
                ->orWhere('gst_rate', 12.00)
                ->update(['gst_rate' => 5.00]);
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'payment_details')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->json('payment_details')->nullable()->after('payment_method');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'payment_details')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('payment_details');
            });
        }
    }
};
