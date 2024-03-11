<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Staudenmeir\LaravelMigrationViews\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // view registrasi ujian
        $query = "SELECT exam_registrations.*,
                        exam_types.kode_ujian AS kode_ujian,
                        exam_types.singkat_ujian AS ujian,
                        departements.nama AS prodi,
                        students.nama AS mahasiswa,
                        students.nim AS nim
                FROM exam_registrations
                    JOIN exam_types ON exam_types.id=exam_registrations.exam_type_id
                    JOIN students ON students.id=exam_registrations.student_id
                    JOIN departements ON departements.id=exam_registrations.departement_id
                ";
        Schema::createOrReplaceView('view_exam_registrations', $query);

        // view penguji
        $query = "SELECT exam_payment_reports.*,
                        lectures.nama AS dosen,
                        lectures.bank AS bank,
                        lectures.nik AS nik,
                        lectures.email AS email,
                        lectures.phone AS phone
                FROM exam_payment_reports
                    JOIN lectures ON lectures.id=exam_payment_reports.lecture_id
                ";
        Schema::createOrReplaceView('view_exam_payment_reports', $query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropViewIfExists('view_exam_payment_reports');
        Schema::dropViewIfExists('view_exam_registrations');
    }
};
