<?php

namespace Database\Seeders;

use App\Models\Departement;
use App\Models\ExamRegistration;
use App\Models\Lecture;
use App\Models\ReportDate;
use App\Models\Student;
use App\Services\ExamPaymentReportService;
use Illuminate\Database\Seeder;

/**
 * Data uji coba untuk mencoba alur kerja Filament secara langsung - dipakai
 * saat aplikasi belum punya data ujian sungguhan (ExamRegistration/ReportDate
 * masih kosong) meski data master (Student/Lecture) sudah data produksi asli.
 *
 * Tidak membuat mahasiswa/dosen palsu - memakai mahasiswa & dosen ASLI yang
 * sudah ada di satu jurusan (supaya scoping role jurusan juga bisa dicoba),
 * lalu melengkapi pembimbing/penguji mereka (kosong semua sebelumnya) dan
 * membuat pendaftaran ujian dengan berbagai status supaya setiap alur bisa
 * langsung dicoba:
 *   - 2 ujian proposal yang BELUM dilaporkan (coba tombol assign "+" di
 *     halaman "Belum Dilaporkan").
 *   - 1 ujian sidang + 1 saudaranya (proposal) yang sama-sama belum
 *     dilaporkan (coba tombol cascade "+N" di halaman "Pasti Sidang").
 *   - 2 ujian yang SUDAH dilaporkan lewat App\Services\ExamPaymentReportService
 *     asli (bukan angka rekaan) - supaya ExamPaymentReportResource langsung
 *     berisi baris honor yang benar-benar terhitung, dan tombol "cabut
 *     laporan" bisa dicoba.
 *
 * Jalankan manual saat dibutuhkan: php artisan db:seed --class=SimulationDataSeeder
 * (sengaja tidak didaftarkan di DatabaseSeeder supaya tidak ikut jalan
 * otomatis pada seeding production/setup awal).
 */
class SimulationDataSeeder extends Seeder
{
    public function run(): void
    {
        $departement = Departement::whereHas('users', fn ($q) => $q->role('jurusan'))->first()
            ?? Departement::first();

        $lecturers = Lecture::where('departement_id', $departement->id)
            ->whereNotNull('jabatan_akademik')
            ->whereNotNull('pendidikan')
            ->orderBy('id')
            ->take(6)
            ->get();

        if ($lecturers->count() < 5) {
            $this->command?->warn('SimulationDataSeeder: kurang dari 5 dosen dengan jabatan_akademik+pendidikan terisi di departemen ini, dilewati.');

            return;
        }

        $students = Student::where('departement_id', $departement->id)
            ->doesntHave('examregistrations')
            ->orderBy('id')
            ->take(6)
            ->get();

        if ($students->count() < 6) {
            $this->command?->warn('SimulationDataSeeder: kurang dari 6 mahasiswa tanpa registrasi ujian di departemen ini, dilewati.');

            return;
        }

        $judulContoh = [
            'Pemberdayaan Masyarakat Desa Melalui Pelatihan Keterampilan Produktif',
            'Efektivitas Program Literasi bagi Kelompok Belajar Masyarakat',
            'Peran Pendidikan Nonformal dalam Peningkatan Kesejahteraan Warga',
            'Model Pendampingan Masyarakat Berbasis Potensi Lokal',
            'Strategi Pengembangan Kewirausahaan Sosial di Tingkat Komunitas',
            'Evaluasi Program Pendidikan Kecakapan Hidup bagi Masyarakat Desa',
        ];

        foreach ($students as $i => $student) {
            $student->update([
                'pembimbing1_id' => $lecturers[$i % $lecturers->count()]->id,
                'pembimbing2_id' => $lecturers[($i + 1) % $lecturers->count()]->id,
                'penguji1_id' => $lecturers[($i + 2) % $lecturers->count()]->id,
                'penguji2_id' => $lecturers[($i + 3) % $lecturers->count()]->id,
                'penguji3_id' => $lecturers[($i + 4) % $lecturers->count()]->id,
            ]);
        }

        $baseData = fn (Student $student, int $i) => [
            'departement_id' => $student->departement_id,
            'student_id' => $student->id,
            'ujian_ke' => 1,
            'ruangan' => (($i % 4) + 1),
            'waktu_mulai' => '09:00:00',
            'waktu_akhir' => '10:30:00',
            'judul_penelitian' => $judulContoh[$i],
            'ipk' => round(3.00 + ($i * 0.12), 2),
            'pembimbing1_id' => $student->pembimbing1_id,
            'pembimbing2_id' => $student->pembimbing2_id,
            'penguji1_id' => $student->penguji1_id,
            'penguji2_id' => $student->penguji2_id,
            'penguji3_id' => $student->penguji3_id,
        ];

        // 2 ujian proposal, belum dilaporkan - untuk coba alur "Belum Dilaporkan".
        foreach ([0, 1] as $i) {
            $student = $students[$i];
            ExamRegistration::create(array_merge($baseData($student, $i), [
                'exam_type_id' => 1,
                'tanggal_ujian' => now()->subDays(10 - $i)->toDateString(),
                'dilaporkan' => false,
            ]));
            $student->update(['tanggal_proposal' => now()->subDays(10 - $i)->toDateString()]);
        }

        // Sidang + saudara (proposal) yang sama-sama belum dilaporkan - untuk
        // coba tombol cascade "+N" di halaman "Pasti Sidang".
        $sidangStudent = $students[2];
        ExamRegistration::create(array_merge($baseData($sidangStudent, 2), [
            'exam_type_id' => 1,
            'tanggal_ujian' => now()->subDays(60)->toDateString(),
            'dilaporkan' => false,
        ]));
        ExamRegistration::create(array_merge($baseData($sidangStudent, 2), [
            'exam_type_id' => 3,
            'tanggal_ujian' => now()->subDays(3)->toDateString(),
            'dilaporkan' => false,
        ]));
        $sidangStudent->update([
            'tanggal_proposal' => now()->subDays(60)->toDateString(),
            'tanggal_skripsi' => now()->subDays(3)->toDateString(),
        ]);

        // Periode laporan uji coba + 2 ujian yang sudah dilaporkan sungguhan
        // lewat service asli, supaya ExamPaymentReportResource langsung
        // berisi baris honor yang benar-benar terhitung (bukan angka rekaan).
        $reportDate = ReportDate::create([
            'tanggal' => now()->toDateString(),
            'deskripsi' => 'Periode Uji Coba',
        ]);

        $service = app(ExamPaymentReportService::class);
        $examTypeByIndex = [3 => 1, 4 => 2, 5 => 3];
        $dateColumnByExamType = [1 => 'tanggal_proposal', 2 => 'tanggal_seminar', 3 => 'tanggal_skripsi'];

        foreach ([3, 4, 5] as $i) {
            $student = $students[$i];
            $examType = $examTypeByIndex[$i];
            $registration = ExamRegistration::create(array_merge($baseData($student, $i), [
                'exam_type_id' => $examType,
                'tanggal_ujian' => now()->subDays(5)->toDateString(),
                'dilaporkan' => true,
                'report_date_id' => $reportDate->id,
            ]));
            $student->update([
                $dateColumnByExamType[$examType] => now()->subDays(5)->toDateString(),
            ]);
            $service->store($registration->id, $reportDate->id);
        }

        $this->command?->info('SimulationDataSeeder: 7 registrasi ujian + 1 periode laporan dibuat untuk departemen "'.$departement->nama.'".');
    }
}
