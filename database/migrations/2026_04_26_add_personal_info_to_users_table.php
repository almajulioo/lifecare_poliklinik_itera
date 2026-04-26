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
        Schema::table('users', function (Blueprint $table) {
            // Tambah field usia, jenis kelamin, nomor telepon
            $table->unsignedInteger('age')->nullable()->after('prodi');
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable()->after('age');
            $table->string('phone')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['age', 'gender', 'phone']);
        });
    }
};
