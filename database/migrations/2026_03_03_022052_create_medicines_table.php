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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();              // FK ke user (pembuat obat)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name');                                        // nama obat
            $table->enum('source_type', ['ADMIN', 'PATIENT'])->default('PATIENT'); // tipe sumber
            $table->string('dose')->nullable();                            // contoh: 500mg / 1 tablet
            $table->string('unit')->nullable();                            // satuan (tablet, ml, etc)
            $table->text('notes')->nullable();                             // keterangan tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
