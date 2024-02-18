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
            $table->string('name')->nullable();
            $table->string('mapel')->nullable();
            $table->string('singkatan')->nullable();
            $table->timestamps();
        });
        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Departement::class);
            $table->string('name')->nullable();
            $table->string('gelar_depan')->nullable();
            $table->string('gelar_belakang')->nullable();
            $table->string('nidn')->nullable();
            $table->string('nip')->nullable();
            $table->string('jafung')->nullable();
            $table->date('tmt_jafung')->nullable();
            $table->string('golongan')->nullable();
            $table->date('tmt_golongan')->nullable();
            $table->string('kualifikasi')->nullable();
            $table->date('tmt_kualifikasi')->nullable();
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
            $table->string('name')->nullable();
            $table->string('nim')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->bigInteger('pembimbing1')->nullable();
            $table->bigInteger('pembimbing2')->nullable();
            $table->bigInteger('penguji1')->nullable();
            $table->bigInteger('penguji2')->nullable();
            $table->bigInteger('penguji3')->nullable();
            $table->bigInteger('ketuapenguji')->nullable();
            $table->date('tanggal_proposal')->nullable();
            $table->date('tanggal_seminar')->nullable();
            $table->date('tanggal_sidang')->nullable();
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
