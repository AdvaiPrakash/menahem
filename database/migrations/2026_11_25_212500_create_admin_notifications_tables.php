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
        Schema::create('admin_notifications', function (Blueprint $table): void {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('type', 32)->index();
            $table->string('event_name', 64);
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('title');
            $table->string('message');
            $table->string('detail')->nullable();
            $table->string('url');
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('admin_notification_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('admin_notification_id')
                ->constrained('admin_notifications')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();

            $table->unique(['admin_notification_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_notification_reads');
        Schema::dropIfExists('admin_notifications');
    }
};
