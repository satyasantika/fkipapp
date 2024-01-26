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
        // view tanggal ujian
        $query = "SELECT exam_dates.*,departements.name AS prodi FROM exam_dates JOIN departements ON departements.id=exam_dates.departement_id";
        Schema::createOrReplaceView('view_exam_dates', $query);

        // view registrasi ujian
        $query = "SELECT exam_registrations.*,
                        exam_dates.tanggal_ujian AS tanggal,
                        departements.name AS prodi,
                        students.name AS mahasiswa,
                        students.nim AS nim,
                        exam_types.name AS ujian,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=exam_registrations.dosen1) AS penguji1,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=exam_registrations.dosen2) AS penguji2,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=exam_registrations.dosen3) AS penguji3,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=exam_registrations.dosen4) AS penguji4,
                        (SELECT lectures.name FROM lectures WHERE lectures.id=exam_registrations.dosen5) AS penguji5
                FROM exam_registrations
                    JOIN exam_dates ON exam_dates.id=exam_registrations.exam_date_id
                    JOIN students ON students.id=exam_registrations.student_id
                    JOIN exam_types ON exam_types.id=exam_registrations.exam_type_id
                    JOIN departements ON departements.id=exam_dates.departement_id
                ";
        Schema::createOrReplaceView('view_exam_registrations', $query);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropViewIfExists('view_exam_registrations');
        Schema::dropViewIfExists('view_exam_dates');
    }
};
