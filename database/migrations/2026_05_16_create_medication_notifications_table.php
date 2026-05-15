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
        Schema::create('medication_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_schedule_id')
                ->constrained('medication_schedules')
                ->onDelete('cascade');
            $table->string('onesignal_id')->nullable();
            $table->enum('reminder_type', ['first', 'second']);
            $table->dateTime('scheduled_at');
            $table->enum('status', ['pending', 'sent', 'canceled'])->default('pending');
            $table->timestamps();

            // Index untuk query yang sering
            $table->index('medication_schedule_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_notifications');
    }
};
