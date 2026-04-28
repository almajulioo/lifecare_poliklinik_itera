<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds columns to track user interactions with notifications
     * (notification clicked, dismissed, etc.) for analytics and
     * determining if user actually saw the notification on their device.
     */
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            // When user clicked on the notification
            $table->dateTime('clicked_at')->nullable()->after('sent_at');
            
            // When user dismissed/closed the notification
            $table->dateTime('dismissed_at')->nullable()->after('clicked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            $table->dropColumn(['clicked_at', 'dismissed_at']);
        });
    }
};
