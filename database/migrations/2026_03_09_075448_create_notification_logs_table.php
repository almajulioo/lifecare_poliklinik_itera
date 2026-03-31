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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('medication_schedule_id')->constrained('medication_schedules')->onDelete('cascade');
            $table->dateTime('scheduled_time');  // When notification was supposed to trigger
            $table->dateTime('sent_at')->nullable();  // When notification actually sent
            $table->string('status')->default('pending'); // pending, sent, dismissed, snoozed
            $table->integer('snooze_minutes')->nullable();  // If snoozed, how many minutes
            $table->string('notification_type')->default('browser'); // browser, sound, both
            $table->text('device_info')->nullable();  // Browser info for debugging
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'scheduled_time']);
            $table->index(['medication_schedule_id', 'scheduled_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
