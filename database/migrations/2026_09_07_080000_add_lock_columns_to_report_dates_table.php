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
        Schema::table('report_dates', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('dibayar');
            // Kapan is_locked terakhir DIUBAH (dikunci ATAU dibuka lagi) - satu
            // kolom saja, arahnya dibaca dari is_locked saat ini.
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            // Kapan ujian terakhir ditambahkan/dicabut dari periode ini - SENGAJA
            // terpisah dari updated_at bawaan (updated_at juga ikut berubah saat
            // is_locked/locked_at di-toggle atau tanggal/deskripsi diedit, jadi
            // tidak akurat dipakai sebagai "terakhir ditarik").
            $table->timestamp('last_pulled_at')->nullable()->after('locked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_dates', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'last_pulled_at']);
        });
    }
};
