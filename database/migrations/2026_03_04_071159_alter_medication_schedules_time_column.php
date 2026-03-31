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
        Schema::table('medication_schedules', function (Blueprint $table) {
            // Change time column from time to string to support JSON arrays
            $table->string('time')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medication_schedules', function (Blueprint $table) {
            // Revert back to time type
            $table->time('time')->change();
        });
    }
};
