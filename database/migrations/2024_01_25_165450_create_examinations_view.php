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
                        LEFT(exam_registrations.tanggal_ujian,7) AS kode_laporan,
                        exam_types.kode_ujian AS kode_ujian,
                        exam_types.singkat_ujian AS ujian,
                        departements.nama AS prodi,
                        students.nama AS mahasiswa,
                        students.nim AS nim,
                        (SELECT lectures.nama FROM lectures WHERE exam_registrations.pembimbing1_id=lectures.id) AS pembimbing1_nama,
                        (SELECT lectures.nama FROM lectures WHERE exam_registrations.pembimbing2_id=lectures.id) AS pembimbing2_nama,
                        (SELECT lectures.nama FROM lectures WHERE exam_registrations.penguji1_id=lectures.id) AS penguji1_nama,
                        (SELECT lectures.nama FROM lectures WHERE exam_registrations.penguji2_id=lectures.id) AS penguji2_nama,
                        (SELECT lectures.nama FROM lectures WHERE exam_registrations.penguji3_id=lectures.id) AS penguji3_nama
                FROM exam_registrations
                    JOIN exam_types ON exam_types.id=exam_registrations.exam_type_id
                    JOIN students ON students.id=exam_registrations.student_id
                    JOIN departements ON departements.id=exam_registrations.departement_id
                ";
        Schema::createOrReplaceView('view_exam_registrations', $query);

        // view laporan ujian
        $query = "SELECT exam_payment_reports.*,
                        TRIM(CONCAT(IF(ISNULL(lectures.gelar_depan),'',CONCAT(lectures.gelar_depan,' ')),lectures.nama,', ',lectures.gelar_belakang)) AS dosen,
                        lectures.departement_id AS departemen_id,
                        CASE WHEN exam_payment_reports.status=1 THEN 'ASN' ELSE 'NON ASN' END AS status_nama,
                        CASE WHEN exam_payment_reports.golongan=3 THEN 'III'
                            WHEN exam_payment_reports.golongan=4 THEN 'IV'
                            ELSE 'BELUM SET' END
                            AS golongan_nama,
                        exam_payment_reports.honor_pembimbing*(exam_payment_reports.banyak_membimbing1+exam_payment_reports.banyak_membimbing2) AS jumlah_honor_pembimbing,
                        exam_payment_reports.honor_penguji_skripsi*exam_payment_reports.banyak_menguji_skripsi AS jumlah_honor_penguji_skripsi,
                        exam_payment_reports.honor_penguji_proposal*exam_payment_reports.banyak_menguji_proposal AS jumlah_honor_penguji_proposal,
                        exam_payment_reports.honor_penguji_seminar*exam_payment_reports.banyak_menguji_seminar AS jumlah_honor_penguji_seminar,
                        exam_payment_reports.honor_pembimbing*(exam_payment_reports.banyak_membimbing1+exam_payment_reports.banyak_membimbing2)
                        +exam_payment_reports.honor_penguji_skripsi*exam_payment_reports.banyak_menguji_skripsi
                        +exam_payment_reports.honor_penguji_proposal*exam_payment_reports.banyak_menguji_proposal
                        +exam_payment_reports.honor_penguji_seminar*exam_payment_reports.banyak_menguji_seminar AS total_honor,
                        CASE WHEN (exam_payment_reports.golongan=4 AND exam_payment_reports.status=1) THEN 0.15
                            WHEN (exam_payment_reports.golongan=3 AND exam_payment_reports.npwp=NULL) THEN 0.06
                            ELSE 0.05 END
                            AS persen_pajak,
                        (CASE WHEN (exam_payment_reports.golongan=4 AND exam_payment_reports.status=1) THEN 0.15
                            WHEN (exam_payment_reports.golongan=3 AND exam_payment_reports.npwp=NULL) THEN 0.06
                            ELSE 0.05 END)
                            *(exam_payment_reports.honor_pembimbing*(exam_payment_reports.banyak_membimbing1+exam_payment_reports.banyak_membimbing2)
                        +exam_payment_reports.honor_penguji_skripsi*exam_payment_reports.banyak_menguji_skripsi
                        +exam_payment_reports.honor_penguji_proposal*exam_payment_reports.banyak_menguji_proposal
                        +exam_payment_reports.honor_penguji_seminar*exam_payment_reports.banyak_menguji_seminar) AS potong_pajak,
                        (1-(CASE WHEN (exam_payment_reports.golongan=4 AND exam_payment_reports.status=1) THEN 0.15
                            WHEN (exam_payment_reports.golongan=3 AND exam_payment_reports.npwp=NULL) THEN 0.06
                            ELSE 0.05 END))
                            *(exam_payment_reports.honor_pembimbing*(exam_payment_reports.banyak_membimbing1+exam_payment_reports.banyak_membimbing2)
                        +exam_payment_reports.honor_penguji_skripsi*exam_payment_reports.banyak_menguji_skripsi
                        +exam_payment_reports.honor_penguji_proposal*exam_payment_reports.banyak_menguji_proposal
                        +exam_payment_reports.honor_penguji_seminar*exam_payment_reports.banyak_menguji_seminar) AS honor_dibayar
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
