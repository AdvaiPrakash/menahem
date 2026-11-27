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
        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'is_simulated_payment')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->boolean('is_simulated_payment')->default(false)->after('payment_method')->index();
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table): void {
                if (! Schema::hasColumn('invoices', 'seller_phone')) {
                    $table->string('seller_phone', 30)->nullable()->after('seller_state_code');
                }
                if (! Schema::hasColumn('invoices', 'seller_email')) {
                    $table->string('seller_email', 100)->nullable()->after('seller_phone');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            $officialAddress = "Karunya,\nNo. 52/1307, St. James Road,\nThykoodam, Kochi,\nErnakulam, Kerala.\nKochi 682019, Kerala, India.";

            $defaults = [
                'seller_name' => 'MENAHEM',
                'seller_gstin' => '32DDLPP5032G1ZN',
                'seller_address' => $officialAddress,
                'seller_state' => 'Kerala',
                'seller_state_code' => '32',
                'seller_phone' => '+91 8129 4620 45',
                'seller_email' => 'info@menahem.co.in',
            ];

            $placeholders = [
                'Menahem Home & Living',
                '27AABCM1234F1Z8',
                '42, Textile Market Road, Kalbadevi, Mumbai, Maharashtra 400002',
                'Maharashtra',
                '27',
                '+91 98765 43210',
                'orders@menahem.com',
            ];

            foreach ($defaults as $key => $value) {
                $existing = DB::table('settings')->where('key', $key)->first();
                if (! $existing) {
                    DB::table('settings')->insert([
                        'key' => $key,
                        'value' => $value,
                        'group' => 'payment',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } elseif (in_array((string) $existing->value, $placeholders, true) || trim((string) $existing->value) === '') {
                    DB::table('settings')->where('key', $key)->update([
                        'value' => $value,
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'is_simulated_payment')) {
            Schema::table('orders', function (Blueprint $table): void {
                $table->dropColumn('is_simulated_payment');
            });
        }

        if (Schema::hasTable('invoices')) {
            $cols = array_values(array_filter(
                ['seller_phone', 'seller_email'],
                static fn (string $c): bool => Schema::hasColumn('invoices', $c)
            ));
            if ($cols !== []) {
                Schema::table('invoices', function (Blueprint $table) use ($cols): void {
                    $table->dropColumn($cols);
                });
            }
        }
    }
};
