<?php

use App\Models\Student;
use App\Models\ExamDate;
use App\Models\ExamType;
use App\Models\Departement;
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
        Schema::create('exam_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Departement::class);
            $table->string('name')->nullable();
            $table->date('tanggal_ujian')->nullable();
            $table->string('kelompok_ujian')->nullable(); //berisi tahun-bulan format (YYYYYMM) untuk admin
            $table->timestamps();
        });

        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('kode')->nullable();
            $table->string('singkat')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExamDate::class);
            $table->foreignIdFor(Student::class);
            $table->foreignIdFor(ExamType::class)->nullable();
            $table->integer('ujianke')->nullable();
            $table->text('judul')->nullable();
            $table->string('ipk')->nullable();
            $table->time('pukul_awal')->nullable();
            $table->time('pukul_akhir')->nullable();
            $table->string('tempat')->nullable();
            $table->foreignId('dosen1')->nullable();
            $table->foreignId('dosen2')->nullable();
            $table->foreignId('dosen3')->nullable();
            $table->foreignId('dosen4')->nullable();
            $table->foreignId('dosen5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_registrations');
        Schema::dropIfExists('exam_types');
        Schema::dropIfExists('exam_dates');
    }
};
