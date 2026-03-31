<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_schedules', function (Blueprint $table) {
            $table->id();

            // relasi ke user (pasien)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // relasi ke medicine
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();

            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->time('time'); // jam minum
            $table->string('frequency')->nullable(); // misal: 1x sehari, 2x sehari
            $table->integer('duration_days')->nullable();

            $table->enum('source', ['resep', 'mandiri'])->default('resep');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_schedules');
    }
};
