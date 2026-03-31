<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add source_type to medicines table to clarify ADMIN vs PATIENT
        Schema::table('medicines', function (Blueprint $table) {
            $table->enum('source_type', ['ADMIN', 'PATIENT'])->default('PATIENT')->after('name');
        });

        // Rename 'source' to 'source_type' in medication_schedules for consistency
        // Note: This preserves data - 'resep' becomes 'ADMIN', 'mandiri' becomes 'PATIENT'
        Schema::table('medication_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('medication_schedules', 'source_type')) {
                $table->enum('source_type', ['ADMIN', 'PATIENT'])->default('ADMIN')->after('source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('source_type');
        });

        Schema::table('medication_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('medication_schedules', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};
