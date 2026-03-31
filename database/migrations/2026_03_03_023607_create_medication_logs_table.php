<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('medication_logs', function (Blueprint $table) {
            $table->id();

            // relasi ke user (pasien)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // relasi ke jadwal
            $table->foreignId('medication_schedule_id')
                ->constrained('medication_schedules')
                ->cascadeOnDelete();

            $table->dateTime('taken_at')->nullable(); // waktu dikonfirmasi minum

            $table->enum('status', ['pending', 'taken', 'missed'])
                ->default('pending');

            $table->text('note')->nullable(); // opsional catatan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_logs');
    }
};
