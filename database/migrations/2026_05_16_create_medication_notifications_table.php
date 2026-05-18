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
            $table->timestamps();
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
