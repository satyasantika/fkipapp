<?php

use App\Models\Lecture;
use App\Models\Student;
use App\Models\ExamType;
use App\Models\Departement;
use App\Models\ExamPayment;
use App\Models\ExamRegistration;
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
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('kode')->nullable();
            $table->string('singkatan')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_payments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('honor')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Departement::class);
            $table->foreignIdFor(Student::class);
            $table->foreignIdFor(ExamType::class)->nullable();
            $table->integer('ujianke')->nullable();
            $table->text('judul')->nullable();
            $table->double('ipk',3,2)->nullable();
            $table->date('tanggal_ujian')->nullable();
            $table->string('kelompok_ujian')->nullable(); //berisi tahun-bulan format (YYYYYMM) untuk admin
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_akhir')->nullable();
            $table->string('tempat')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_examiners', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExamRegistration::class);
            $table->foreignIdFor(Lecture::class);
            $table->foreignIdFor(ExamPayment::class);
            $table->integer('pengujike')->nullable();
            $table->boolean('ketua')->default(0);
            $table->integer('pembimbingke')->default(0);
            $table->integer('honor')->nullable();
            $table->boolean('aktif')->default(1);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_examiners');
        Schema::dropIfExists('exam_registrations');
        Schema::dropIfExists('exam_payments');
        Schema::dropIfExists('exam_types');
    }
};
