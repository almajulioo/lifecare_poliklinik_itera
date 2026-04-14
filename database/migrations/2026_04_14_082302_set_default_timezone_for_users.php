<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update semua user dengan timezone default 'Asia/Jakarta' jika NULL atau tidak tersedia
        DB::table('users')
            ->whereNull('timezone')
            ->orWhere('timezone', '')
            ->update(['timezone' => 'Asia/Jakarta']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert timezone menjadi NULL jika diperlukan rollback
        DB::table('users')
            ->where('timezone', 'Asia/Jakarta')
            ->update(['timezone' => null]);
    }
};
