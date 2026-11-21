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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('category')->nullable();
            $table->string('category_slug')->index();
            $table->json('category_slugs')->nullable();
            $table->string('material')->nullable();
            $table->string('price')->default('₹0');
            $table->unsignedInteger('numeric_price')->default(0)->index();
            $table->string('original_price')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedInteger('reviews')->default(0);
            $table->string('badge')->nullable();
            $table->text('desc')->nullable();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('in_stock')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_bestseller')->default(false)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
