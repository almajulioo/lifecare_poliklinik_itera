<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role_user', ['mahasiswa', 'pegawai']);
            $table->string('name');
            $table->string('nim')->nullable();
            $table->string('prodi')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('fcm_token')->nullable();
            $table->json('notification_preferences')->nullable();
            $table->string('timezone')->default('Asia/Jakarta');
            $table->json('medical_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
