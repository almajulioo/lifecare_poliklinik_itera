<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds snooze_until column to track when a snoozed reminder should
     * be re-shown to the user (for dashboard reminder feature)
     */
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            // When the snoozed reminder should be shown again
            $table->dateTime('snooze_until')->nullable()->after('snooze_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropColumn(['snooze_until']);
        });
    }
};
