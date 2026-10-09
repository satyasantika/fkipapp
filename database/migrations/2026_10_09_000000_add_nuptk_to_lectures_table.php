<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NUPTK menggantikan NIDN sebagai kunci pencocokan dosen saat sinkronisasi
     * Sintesys (lihat SintesysSyncService).
     */
    public function up(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->string('nuptk')->nullable()->unique()->after('nidn');
        });
    }

    public function down(): void
    {
        Schema::table('lectures', function (Blueprint $table) {
            $table->dropUnique(['nuptk']);
            $table->dropColumn('nuptk');
        });
    }
};
