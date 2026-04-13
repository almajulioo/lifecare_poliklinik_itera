<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds columns to track second reminder for medication notifications
     * When a patient hasn't confirmed medication after first reminder,
     * the system will send a second reminder after a configured interval.
     */
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            // Track which reminder number this is (1st or 2nd)
            $table->unsignedTinyInteger('reminder_number')->default(1)->after('notification_type');
            
            // When the next (second) reminder should be sent
            $table->dateTime('second_reminder_at')->nullable()->after('reminder_number');
            
            // When the second reminder was actually sent
            $table->dateTime('second_reminder_sent_at')->nullable()->after('second_reminder_at');
            
            // Add index for checking pending reminders (with shorter index name to avoid MySQL identifier length limit)
            $table->index(['user_id', 'reminder_number', 'scheduled_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'reminder_number', 'scheduled_time']);
            $table->dropColumn(['reminder_number', 'second_reminder_at', 'second_reminder_sent_at']);
        });
    }
};
