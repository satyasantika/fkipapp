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
            $table->string('nama_ujian')->nullable();
            $table->string('kode_ujian')->nullable();
            $table->string('singkat_ujian')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_payments', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('jabatan_akademik')->nullable();
            $table->string('pendidikan')->nullable();
            $table->unsignedMediumInteger('honor')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Departement::class);
            $table->foreignIdFor(Student::class);
            $table->foreignIdFor(ExamType::class)->nullable();
            $table->unsignedTinyInteger('ujian_ke')->nullable();
            $table->text('judul_penelitian')->nullable();
            $table->double('ipk',3,2)->nullable();
            $table->date('tanggal_ujian')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_akhir')->nullable();
            $table->string('ruangan')->nullable();
            $table->unsignedBigInteger('penguji1_id')->nullable();
            $table->unsignedBigInteger('penguji2_id')->nullable();
            $table->unsignedBigInteger('penguji3_id')->nullable();
            $table->unsignedBigInteger('pembimbing1_id')->nullable();
            $table->unsignedBigInteger('pembimbing2_id')->nullable();
            $table->unsignedBigInteger('ketuapenguji_id')->nullable();
            $table->boolean('penguji1_dibayar')->default(1);
            $table->boolean('penguji2_dibayar')->default(1);
            $table->boolean('penguji3_dibayar')->default(1);
            $table->boolean('pembimbing1_dibayar')->default(1);
            $table->boolean('pembimbing2_dibayar')->default(1);
            $table->date('tanggal_dilaporkan')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_payment_reports', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laporan');// diisi tahun dan bulan YYYY-MM
            $table->foreignIdFor(Lecture::class);
            $table->string('status')->nullable(); // PNS or NON PNS
            $table->string('golongan')->nullable();
            $table->string('npwp')->nullable();
            $table->string('rekening')->nullable();
            $table->string('jabatan_akademik')->nullable();
            $table->string('pendidikan')->nullable();
            $table->unsignedMediumInteger('honor_pembimbing')->nullable();
            $table->unsignedMediumInteger('honor_penguji_skripsi')->nullable();
            $table->unsignedMediumInteger('honor_penguji_proposal')->nullable();
            $table->unsignedMediumInteger('honor_penguji_seminar')->nullable();
            $table->unsignedTinyInteger('banyak_membimbing1')->default(0);
            $table->unsignedTinyInteger('banyak_membimbing2')->default(0);
            $table->unsignedTinyInteger('banyak_menguji_skripsi')->default(0);
            $table->unsignedTinyInteger('banyak_menguji_proposal')->default(0);
            $table->unsignedTinyInteger('banyak_menguji_seminar')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_payment_reports');
        Schema::dropIfExists('exam_registrations');
        Schema::dropIfExists('exam_payments');
        Schema::dropIfExists('exam_types');
    }
};
