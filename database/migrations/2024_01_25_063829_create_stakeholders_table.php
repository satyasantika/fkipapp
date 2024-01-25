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
            $table->string('rekening')->nullable();
            $table->string('bank')->nullable();
            $table->string('npwp')->nullable();
            $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Departement::class);
            $table->string('name')->nullable();
            $table->string('nim')->nullable();
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
