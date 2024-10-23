<?php

use App\Models\ReportDate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_dates', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('deskripsi')->nullable();
            $table->string('dibayar')->nullable();
            $table->timestamps();
        });
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->dropColumn('tanggal_dilaporkan');
            $table->foreignIdFor(ReportDate::class)->after('dilaporkan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'report_date_id',
            ]);
            $table->date('tanggal_dilaporkan')->nullable();
        });
        Schema::dropIfExists('report_dates');
    }
};
