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
        if (! Schema::hasTable('clearance_items')) {
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
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'highlights')) {
                    $table->json('highlights')->nullable()->after('gallery');
                }
                if (! Schema::hasColumn('products', 'fabric_details')) {
                    $table->json('fabric_details')->nullable()->after('highlights');
                }
                if (! Schema::hasColumn('products', 'wash_care')) {
                    $table->text('wash_care')->nullable()->after('fabric_details');
                }
                if (! Schema::hasColumn('products', 'shipping_returns')) {
                    $table->text('shipping_returns')->nullable()->after('wash_care');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            $columns = array_values(array_filter(
                ['highlights', 'fabric_details', 'wash_care', 'shipping_returns'],
                static fn (string $col): bool => Schema::hasColumn('products', $col)
            ));

            if ($columns !== []) {
                Schema::table('products', function (Blueprint $table) use ($columns): void {
                    $table->dropColumn($columns);
                });
            }
        }

        Schema::dropIfExists('clearance_items');
    }
};
