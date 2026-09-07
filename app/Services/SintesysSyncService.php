<?php

namespace App\Services;

use App\Models\Departement;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\Lecture;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Tarik data ujian tugas akhir dari Sintesys UNSIL dan cocokkan ke
 * exam_registrations lokal. analyze() TIDAK menulis ke DB sama sekali
 * (dipakai untuk preview) - commit() baru benar-benar menyimpan, dari
 * hasil analyze() yang sama supaya preview & hasil akhir konsisten
 * (tidak fetch API dua kali).
 */
class SintesysSyncService
{
    /**
     * urutan (1-based) di array "penguji" Sintesys -> kolom exam_registrations.
     * Dikonfirmasi oleh pengguna: urutan 1 = ketua penguji, 2-3 = penguji lain,
     * 4-5 = pembimbing. Kalau ada urutan ke-6 (jarang), ditaruh di penguji3_id.
     */
    private const URUTAN_KE_KOLOM = [
        1 => 'ketuapenguji_id',
        2 => 'penguji1_id',
        3 => 'penguji2_id',
        4 => 'pembimbing1_id',
        5 => 'pembimbing2_id',
        6 => 'penguji3_id',
    ];

    /**
     * jenis_ujian (string, persis dari Sintesys) -> singkat_ujian lokal.
     * String lain (Kolokium, Sidang Akhir Tesis, Kualifikasi, dst - biasanya
     * S2/S3) sengaja tidak dipetakan sama sekali, jadi otomatis dilewati.
     */
    private const JENIS_UJIAN_KE_SINGKAT = [
        'Ujian Proposal' => 'sempro',
        'Ujian Hasil Penelitian' => 'semhas',
        'Ujian Sidang Akhir' => 'sidang',
    ];

    /**
     * @return array<int, array<string, mixed>> baris mentah dari Sintesys (data[])
     */
    public function fetchExams(string $kodeProdi, string $tanggalMulai, string $tanggalSelesai): array
    {
        $response = Http::withToken(config('services.sintesys.token'))
            ->baseUrl(config('services.sintesys.url'))
            ->timeout(30)
            ->post('/api/akademik/tugas_akhir', [
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'kode_prodi' => $kodeProdi,
            ]);

        if ($response->failed()) {
            $message = $response->json('message') ?? $response->reason();

            throw new \RuntimeException("Gagal menghubungi Sintesys ({$response->status()}): {$message}");
        }

        return $response->json('data') ?? [];
    }

    /**
     * Analisis baris mentah Sintesys TANPA menulis ke DB - untuk preview
     * maupun sebagai input commit(). Setiap item diperkaya dengan status
     * ('dibuat'/'diperbarui'/'dilewati'), alasan (kalau dilewati), dan
     * flag "akan membuat mahasiswa/dosen baru" supaya kelihatan di preview.
     *
     * @return array{items: array<int, array<string, mixed>>, summary: array<string, int>}
     */
    public function analyze(array $rows, int $departementId): array
    {
        $departementNama = Departement::find($departementId)?->nama;

        $items = [];
        $summary = [
            'total' => count($rows),
            'dibuat' => 0,
            'diperbarui' => 0,
            'dilewati_jenis_tidak_dikenal' => 0,
            'mahasiswa_baru' => 0,
            'dosen_baru' => 0,
        ];

        foreach ($rows as $row) {
            $singkatUjian = self::JENIS_UJIAN_KE_SINGKAT[$row['jenis_ujian'] ?? null] ?? null;

            if (! $singkatUjian) {
                $summary['dilewati_jenis_tidak_dikenal']++;

                $items[] = [
                    'nim' => $row['nim'] ?? null,
                    'nama' => $row['nama'] ?? null,
                    'jenis_ujian' => $row['jenis_ujian'] ?? '-',
                    'tanggal_ujian' => $row['tanggal_ujian'] ?? null,
                    'status' => 'dilewati',
                    'alasan' => 'Jenis ujian "'.($row['jenis_ujian'] ?? '-').'" tidak dikenali (bukan Proposal/Hasil Penelitian/Sidang Akhir).',
                    'departement_nama' => $departementNama,
                    'raw' => $row,
                ];

                continue;
            }

            $examType = ExamType::where('singkat_ujian', $singkatUjian)->first();
            $student = Student::where('nim', $row['nim'] ?? null)->first();
            $studentBaru = ! $student;

            $lectureSlots = [];
            $dosenBaruNama = [];

            foreach ($row['penguji'] ?? [] as $penguji) {
                $urutan = (int) ($penguji['urutan'] ?? 0);
                $kolom = self::URUTAN_KE_KOLOM[$urutan] ?? null;

                if (! $kolom) {
                    continue;
                }

                $lecture = Lecture::where('nidn', $penguji['nidn'] ?? null)->first();

                if (! $lecture) {
                    $dosenBaruNama[] = $penguji['nama'] ?? $penguji['nidn'] ?? '-';
                }

                $lectureSlots[$kolom] = [
                    'nidn' => $penguji['nidn'] ?? null,
                    'nama' => $penguji['nama'] ?? null,
                    'existing_id' => $lecture?->id,
                ];
            }

            $tanggalUjian = Carbon::parse($row['tanggal_ujian']);

            // Cari baris exam_registrations target: yang BELUM dilaporkan untuk
            // pasangan (student, exam_type) ini -> update. Kalau tidak ada
            // (belum pernah ada, atau yang ada semua sudah dilaporkan) -> buat
            // baru, ujian_ke = jumlah baris existing + 1. Data yang sudah
            // dilaporkan/dikunci TIDAK PERNAH disentuh oleh sinkronisasi ini.
            $existingUnreported = null;
            $ujianKeBaru = 1;

            if ($student && $examType) {
                $existingUnreported = ExamRegistration::where('student_id', $student->id)
                    ->where('exam_type_id', $examType->id)
                    ->where('dilaporkan', false)
                    ->first();

                if (! $existingUnreported) {
                    $ujianKeBaru = ExamRegistration::where('student_id', $student->id)
                        ->where('exam_type_id', $examType->id)
                        ->count() + 1;
                }
            }

            $status = $existingUnreported ? 'diperbarui' : 'dibuat';
            $summary[$status]++;

            if ($studentBaru) {
                $summary['mahasiswa_baru']++;
            }

            if ($dosenBaruNama) {
                $summary['dosen_baru'] += count($dosenBaruNama);
            }

            $items[] = [
                'nim' => $row['nim'] ?? null,
                'nama' => $row['nama'] ?? null,
                'jenis_ujian' => $row['jenis_ujian'],
                'tanggal_ujian' => $tanggalUjian->toDateTimeString(),
                'status' => $status,
                'alasan' => null,
                'student_baru' => $studentBaru,
                'dosen_baru' => $dosenBaruNama,
                'departement_id' => $departementId,
                'departement_nama' => $departementNama,
                'exam_type_id' => $examType?->id,
                'student_nim' => $row['nim'] ?? null,
                'student_nama' => $row['nama'] ?? null,
                'existing_registration_id' => $existingUnreported?->id,
                'ujian_ke_baru' => $ujianKeBaru,
                'tanggal' => $tanggalUjian->toDateString(),
                'waktu_mulai' => $tanggalUjian->toTimeString(),
                'ruangan' => $row['tempat_ujian'] ?? null,
                'judul_penelitian' => $row['judul_unformated'] ?? null,
                'lecture_slots' => $lectureSlots,
            ];
        }

        return ['items' => $items, 'summary' => $summary];
    }

    /**
     * Sama seperti analyze(), tapi untuk SEMUA jurusan sekaligus - dipakai
     * role keuangan supaya tidak perlu memilih satu jurusan dulu (Sintesys
     * mewajibkan kode_prodi tunggal per request, jadi di sini di-loop satu
     * request per jurusan). Kalau satu jurusan gagal ditarik (mis. timeout),
     * jurusan itu dilewati (dicatat di summary['gagal_jurusan']) tanpa
     * menggagalkan jurusan lain.
     *
     * @return array{items: array<int, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function analyzeAllDepartments(string $tanggalMulai, string $tanggalSelesai): array
    {
        $items = [];
        $summary = [
            'total' => 0,
            'dibuat' => 0,
            'diperbarui' => 0,
            'dilewati_jenis_tidak_dikenal' => 0,
            'mahasiswa_baru' => 0,
            'dosen_baru' => 0,
            'gagal_jurusan' => [],
        ];

        foreach (Departement::all() as $departement) {
            try {
                $rows = $this->fetchExams((string) $departement->id, $tanggalMulai, $tanggalSelesai);
            } catch (\Throwable $e) {
                $summary['gagal_jurusan'][] = $departement->nama;

                continue;
            }

            $result = $this->analyze($rows, $departement->id);

            $items = array_merge($items, $result['items']);

            foreach (['total', 'dibuat', 'diperbarui', 'dilewati_jenis_tidak_dikenal', 'mahasiswa_baru', 'dosen_baru'] as $key) {
                $summary[$key] += $result['summary'][$key];
            }
        }

        return ['items' => $items, 'summary' => $summary];
    }

    /**
     * Simpan hasil analyze()/analyzeAllDepartments() ke DB. $items harus
     * berasal dari salah satu method itu (item ber-status 'dilewati'
     * otomatis diabaikan di sini juga). departement_id diambil dari
     * masing-masing item (bukan satu parameter global) - supaya jalur
     * "semua jurusan" (analyzeAllDepartments) menyimpan setiap baris ke
     * jurusan asalnya masing-masing, tidak tercampur ke satu jurusan.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, int> ringkasan final (bentuk sama seperti summary analyze())
     */
    public function commit(array $items): array
    {
        $summary = [
            'dibuat' => 0,
            'diperbarui' => 0,
            'dilewati_jenis_tidak_dikenal' => 0,
            'mahasiswa_baru' => 0,
            'dosen_baru' => 0,
        ];

        DB::transaction(function () use ($items, &$summary) {
            foreach ($items as $item) {
                if ($item['status'] === 'dilewati') {
                    $summary['dilewati_jenis_tidak_dikenal']++;

                    continue;
                }

                $departementId = $item['departement_id'];

                $student = Student::firstOrCreate(
                    ['nim' => $item['student_nim']],
                    ['nama' => $item['student_nama'], 'departement_id' => $departementId],
                );

                if ($student->wasRecentlyCreated) {
                    $summary['mahasiswa_baru']++;
                }

                $lectureIds = [];

                foreach ($item['lecture_slots'] as $kolom => $slot) {
                    if (! $slot['nidn']) {
                        continue;
                    }

                    $lecture = Lecture::firstOrCreate(
                        ['nidn' => $slot['nidn']],
                        ['nama' => $slot['nama'], 'departement_id' => $departementId],
                    );

                    if ($lecture->wasRecentlyCreated) {
                        $summary['dosen_baru']++;
                    }

                    $lectureIds[$kolom] = $lecture->id;
                }

                $fields = array_merge([
                    'departement_id' => $departementId,
                    'student_id' => $student->id,
                    'exam_type_id' => $item['exam_type_id'],
                    'tanggal_ujian' => $item['tanggal'],
                    'waktu_mulai' => $item['waktu_mulai'],
                    'ruangan' => $item['ruangan'],
                    'judul_penelitian' => $item['judul_penelitian'],
                ], $lectureIds);

                if ($item['existing_registration_id']) {
                    ExamRegistration::whereKey($item['existing_registration_id'])->update($fields);
                    $summary['diperbarui']++;
                } else {
                    ExamRegistration::create(array_merge($fields, [
                        'ujian_ke' => $item['ujian_ke_baru'],
                    ]));
                    $summary['dibuat']++;
                }
            }
        });

        return $summary;
    }
}
