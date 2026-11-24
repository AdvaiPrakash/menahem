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
        Schema::table('inquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('inquiries', 'notification_status')) {
                $table->string('notification_status')->default('pending')->after('status')->index();
            }
            if (! Schema::hasColumn('inquiries', 'notification_error')) {
                $table->text('notification_error')->nullable()->after('notification_status');
            }
            if (! Schema::hasColumn('inquiries', 'notified_at')) {
                $table->timestamp('notified_at')->nullable()->after('notification_error');
            }
        });

        Schema::table('inquiry_replies', function (Blueprint $table) {
            if (! Schema::hasColumn('inquiry_replies', 'smtp_message_id')) {
                $table->string('smtp_message_id')->nullable()->after('error_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiry_replies', function (Blueprint $table) {
            if (Schema::hasColumn('inquiry_replies', 'smtp_message_id')) {
                $table->dropColumn('smtp_message_id');
            }
        });

        Schema::table('inquiries', function (Blueprint $table) {
            $drop = array_filter([
                Schema::hasColumn('inquiries', 'notification_status') ? 'notification_status' : null,
                Schema::hasColumn('inquiries', 'notification_error') ? 'notification_error' : null,
                Schema::hasColumn('inquiries', 'notified_at') ? 'notified_at' : null,
            ]);
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
