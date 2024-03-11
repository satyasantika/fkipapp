<?php

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
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->string('mapel')->nullable();
            $table->string('singkatan')->nullable();
            $table->timestamps();
        });
        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Departement::class);
            $table->string('nama')->nullable();
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->string('nidn')->nullable();
            $table->string('nip')->nullable();
            $table->string('jabatan_akademik')->nullable();
            $table->date('tmt_jabatan_akademik')->nullable();
            $table->string('golongan')->nullable();
            $table->date('tmt_golongan')->nullable();
            $table->string('pendidikan')->nullable();
            $table->date('tmt_pendidikan')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('rekening')->nullable();
            $table->string('bank')->nullable();
            $table->string('npwp')->nullable();
            $table->string('nik')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Departement::class);
            $table->string('nama')->nullable();
            $table->string('nim')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('pembimbing1_id')->nullable();
            $table->unsignedBigInteger('pembimbing2_id')->nullable();
            $table->unsignedBigInteger('penguji1_id')->nullable();
            $table->unsignedBigInteger('penguji2_id')->nullable();
            $table->unsignedBigInteger('penguji3_id')->nullable();
            $table->unsignedBigInteger('ketuapenguji_id')->nullable();
            $table->date('tanggal_proposal')->nullable();
            $table->date('tanggal_seminar')->nullable();
            $table->date('tanggal_skripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departements');
        Schema::dropIfExists('lectures');
        Schema::dropIfExists('students');
    }
};
