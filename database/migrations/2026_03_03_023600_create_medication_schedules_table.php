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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('time');                                        // jam minum (JSON array support)
            $table->string('frequency')->nullable();                       // misal: 1x sehari, 2x sehari
            $table->integer('duration_days')->nullable();
            $table->enum('source', ['resep', 'mandiri'])->default('resep');
            $table->enum('source_type', ['ADMIN', 'PATIENT'])->default('ADMIN'); // tipe sumber
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_schedules');
    }
};
