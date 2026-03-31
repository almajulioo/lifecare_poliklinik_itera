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
        Schema::table('medication_logs', function (Blueprint $table) {
            $table->boolean('offline_synced')->default(false)->after('note');
            $table->dateTime('offline_synced_at')->nullable()->after('offline_synced');
            $table->text('sync_metadata')->nullable()->after('offline_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medication_logs', function (Blueprint $table) {
            $table->dropColumn('offline_synced');
            $table->dropColumn('offline_synced_at');
            $table->dropColumn('sync_metadata');
        });
    }
};
