<?php

use Illuminate\Database\Migrations\Migration;
use Staudenmeir\LaravelMigrationViews\Facades\Schema;

/**
 * Menghapus SQL view yang dulu dipakai model ViewStudent/ViewLecture/
 * ViewExamRegistration/ViewExamPaymentReport. Kolom turunannya sudah pindah
 * jadi relasi + accessor di model tabel asli (Student, Lecture,
 * ExamRegistration, ExamPaymentReport), supaya skema aplikasi sepenuhnya
 * berupa tabel biasa + migration — tidak ada lagi CREATE VIEW terpisah yang
 * gampang tidak sinkron dengan migration (lihat bug view_exam_dates yang
 * tidak pernah punya migration sama sekali).
 *
 * Sengaja tidak mengedit 2024_01_25_163847_create_stakeholders_view.php dan
 * 2024_01_25_165450_create_examinations_view.php secara in-place — environment
 * yang sudah pernah migrate akan tetap punya view-nya sampai migration ini
 * dijalankan, dan environment baru akan create-lalu-drop (tidak masalah).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropViewIfExists('view_exam_payment_reports');
        Schema::dropViewIfExists('view_exam_registrations');
        Schema::dropViewIfExists('view_students');
        Schema::dropViewIfExists('view_lectures');
        // view_exam_dates tidak pernah dibuat migration manapun — tidak ada yang perlu di-drop.
    }

    public function down(): void
    {
        // Sengaja dikosongkan: merekonstruksi view berarti menduplikasi SQL
        // CREATE VIEW dari kedua migration di atas, yang justru bertentangan
        // dengan tujuan migration ini. Kalau rollback benar-benar dibutuhkan,
        // jalankan ulang bagian relevan dari kedua migration tersebut secara manual.
    }
};
