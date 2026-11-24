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
        if (! Schema::hasColumn('inquiries', 'subject')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->string('subject')->nullable()->after('type');
            });
        }

        Schema::create('inquiry_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('inquiries')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('recipient_email');
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('sent')->index(); // sent, failed
            $table->text('error_message')->nullable();
            $table->string('idempotency_key', 100)->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_replies');

        if (Schema::hasColumn('inquiries', 'subject')) {
            Schema::table('inquiries', function (Blueprint $table) {
                $table->dropColumn('subject');
            });
        }
    }
};
