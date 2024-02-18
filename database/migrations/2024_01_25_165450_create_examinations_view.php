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
                        exam_types.kode AS kode_ujian,
                        exam_types.singkatan AS ujian,
                        departements.name AS prodi,
                        students.name AS mahasiswa,
                        students.nim AS nim
                FROM exam_registrations
                    JOIN exam_types ON exam_types.id=exam_registrations.exam_type_id
                    JOIN students ON students.id=exam_registrations.student_id
                    JOIN departements ON departements.id=exam_registrations.departement_id
                ";
        Schema::createOrReplaceView('view_exam_registrations', $query);

        // view penguji
        $query = "SELECT exam_examiners.*,
                        exam_types.kode AS kode_ujian,
                        exam_types.singkatan AS ujian,
                        departements.name AS prodi,
                        lectures.name AS dosen,
                        students.name AS mahasiswa,
                        students.nim AS nim,
                        exam_payments.name AS pembayaran,
                        lectures.rekening AS rekening,
                        lectures.bank AS bank,
                        lectures.npwp AS npwp,
                        lectures.nik AS nik,
                        lectures.email AS email,
                        lectures.phone AS phone
                FROM exam_examiners
                    JOIN exam_registrations ON exam_registrations.id=exam_examiners.exam_registration_id
                    JOIN exam_payments ON exam_payments.id=exam_examiners.exam_payment_id
                    JOIN lectures ON lectures.id=exam_examiners.lecture_id
                    JOIN students ON students.id=exam_registrations.student_id
                    JOIN exam_types ON exam_types.id=exam_registrations.exam_type_id
                    JOIN departements ON departements.id=exam_registrations.departement_id
                ";
        Schema::createOrReplaceView('view_exam_examiners', $query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropViewIfExists('view_exam_examiners');
        Schema::dropViewIfExists('view_exam_registrations');
    }
};
