<?php

namespace App\Services;

use App\Models\Departement;
use App\Models\ExamRegistration;
use App\Models\ExamType;
use App\Models\Lecture;
use App\Models\Student;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
            'dilewati_sudah_dilaporkan' => 0,
            'dobel_dihapus' => 0,
            'mahasiswa_baru' => 0,
            'dosen_baru' => 0,
            'nuptk_diisi' => 0,
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
                    'departement_id' => $departementId,
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
            $dosenIsiNuptkNama = [];

            foreach ($row['penguji'] ?? [] as $penguji) {
                $urutan = (int) ($penguji['urutan'] ?? 0);
                $kolom = self::URUTAN_KE_KOLOM[$urutan] ?? null;

                if (! $kolom) {
                    continue;
                }

                // Dosen dicocokkan lewat NUPTK. Kalau tidak ketemu, dicari
                // lewat NIDN di antara dosen yang NUPTK-nya MASIH KOSONG (dosen
                // lama) - NUPTK-nya lalu diisi di commit(), supaya tidak
                // tercipta dosen dobel. NUPTK yang sudah terisi tidak ditimpa.
                $nuptk = filled($penguji['nuptk'] ?? null) ? (string) $penguji['nuptk'] : null;
                $nidn = filled($penguji['nidn'] ?? null) ? (string) $penguji['nidn'] : null;
                $lecture = $nuptk ? Lecture::where('nuptk', $nuptk)->first() : null;
                $isiNuptk = false;

                if (! $lecture && $nuptk && $nidn) {
                    $lecture = Lecture::where('nidn', $nidn)
                        ->where(fn ($q) => $q->whereNull('nuptk')->orWhere('nuptk', ''))
                        ->first();
                    $isiNuptk = (bool) $lecture;
                }

                if (! $lecture) {
                    $dosenBaruNama[] = $penguji['nama'] ?? $nuptk ?? '-';
                } elseif ($isiNuptk) {
                    $dosenIsiNuptkNama[] = $lecture->nama ?? $penguji['nama'] ?? $nidn;
                }

                $lectureSlots[$kolom] = [
                    'nuptk' => $nuptk,
                    'nidn' => $nidn,
                    'nama' => $penguji['nama'] ?? null,
                    'existing_id' => $lecture?->id,
                    'isi_nuptk' => $isiNuptk,
                ];
            }

            $tanggalUjian = Carbon::parse($row['tanggal_ujian']);

            // Kalau ujian ini (student, exam_type, tanggal sama) SUDAH
            // dilaporkan -> lewati (dobel yang belum dilaporkan dibersihkan
            // di commit() lewat hapusDobel()). Selain itu, cari baris
            // exam_registrations yang BELUM dilaporkan untuk pasangan
            // (student, exam_type) ini -> update. Kalau tidak ada (belum pernah
            // ada, atau yang ada semua sudah dilaporkan di tanggal lain = ujian
            // ulang) -> buat baru, ujian_ke = jumlah baris existing + 1. Data
            // yang sudah dilaporkan/dikunci TIDAK PERNAH disentuh oleh
            // sinkronisasi ini.
            $existingUnreported = null;
            $ujianKeBaru = 1;

            if ($student && $examType) {
                $sudahDilaporkan = ExamRegistration::where('student_id', $student->id)
                    ->where('exam_type_id', $examType->id)
                    ->where('dilaporkan', true)
                    ->whereDate('tanggal_ujian', $tanggalUjian->toDateString())
                    ->exists();

                if ($sudahDilaporkan) {
                    $summary['dilewati_sudah_dilaporkan']++;

                    $items[] = [
                        'nim' => $row['nim'] ?? null,
                        'nama' => $row['nama'] ?? null,
                        'jenis_ujian' => $row['jenis_ujian'],
                        'tanggal_ujian' => $tanggalUjian->toDateTimeString(),
                        'status' => 'dilewati',
                        'sudah_dilaporkan' => true,
                        'alasan' => 'Ujian ini sudah dilaporkan (tanggal sama), tidak disinkronkan ulang.',
                        'departement_id' => $departementId,
                        'departement_nama' => $departementNama,
                        'raw' => $row,
                    ];

                    continue;
                }

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

            $summary['nuptk_diisi'] += count($dosenIsiNuptkNama);

            $items[] = [
                'nim' => $row['nim'] ?? null,
                'nama' => $row['nama'] ?? null,
                'jenis_ujian' => $row['jenis_ujian'],
                'tanggal_ujian' => $tanggalUjian->toDateTimeString(),
                'status' => $status,
                'alasan' => null,
                'student_baru' => $studentBaru,
                'dosen_baru' => $dosenBaruNama,
                'dosen_isi_nuptk' => $dosenIsiNuptkNama,
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

        if ($rows) {
            $summary['dobel_dihapus'] = $this->dobelQuery([$departementId])->count();
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
            'dilewati_sudah_dilaporkan' => 0,
            'dobel_dihapus' => 0,
            'mahasiswa_baru' => 0,
            'dosen_baru' => 0,
            'nuptk_diisi' => 0,
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

            foreach (['total', 'dibuat', 'diperbarui', 'dilewati_jenis_tidak_dikenal', 'dilewati_sudah_dilaporkan', 'dobel_dihapus', 'mahasiswa_baru', 'dosen_baru', 'nuptk_diisi'] as $key) {
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
            'dilewati_sudah_dilaporkan' => 0,
            'dobel_dihapus' => 0,
            'mahasiswa_baru' => 0,
            'dosen_baru' => 0,
            'nuptk_diisi' => 0,
        ];

        DB::transaction(function () use ($items, &$summary) {
            foreach ($items as $item) {
                if ($item['status'] === 'dilewati') {
                    $summary[($item['sudah_dilaporkan'] ?? false) ? 'dilewati_sudah_dilaporkan' : 'dilewati_jenis_tidak_dikenal']++;

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
                    if (! $slot['nuptk']) {
                        continue;
                    }

                    $lecture = null;

                    if ($slot['isi_nuptk'] ?? false) {
                        $lecture = Lecture::find($slot['existing_id']);

                        if ($lecture && blank($lecture->nuptk)) {
                            $lecture->update(['nuptk' => $slot['nuptk']]);
                            $summary['nuptk_diisi']++;
                        }
                    }

                    $lecture ??= Lecture::firstOrCreate(
                        ['nuptk' => $slot['nuptk']],
                        ['nama' => $slot['nama'], 'nidn' => $slot['nidn'], 'departement_id' => $departementId],
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

            $departementIds = collect($items)->pluck('departement_id')->filter()->unique()->values()->all();

            if ($departementIds) {
                $summary['dobel_dihapus'] = $this->hapusDobel($departementIds);
            }
        });

        return $summary;
    }

    /**
     * Ujian dobel (mahasiswa, jenis, tanggal sama) yang BELUM dilaporkan dan
     * perlu dihapus: (1) ada kembaran yang sudah dilaporkan, atau (2) ada
     * kembaran lain yang juga belum dilaporkan dengan id lebih kecil - yang
     * id terkecil disisakan. Baris yang sudah dilaporkan tidak pernah
     * termasuk. Dipakai juga oleh perintah exam:hapus-dobel-sinkron.
     *
     * @param  array<int, int|string>|null  $departementIds  null = semua jurusan
     */
    public function dobelQuery(?array $departementIds = null): QueryBuilder
    {
        return DB::table('exam_registrations as u')
            ->where('u.dilaporkan', false)
            ->when($departementIds !== null, fn ($q) => $q->whereIn('u.departement_id', $departementIds))
            ->whereExists(fn ($q) => $q->selectRaw('1')
                ->from('exam_registrations as r')
                ->whereColumn('r.student_id', 'u.student_id')
                ->whereColumn('r.exam_type_id', 'u.exam_type_id')
                ->whereRaw('DATE(r.tanggal_ujian) = DATE(u.tanggal_ujian)')
                ->whereColumn('r.id', '<>', 'u.id')
                ->where(fn ($q) => $q->where('r.dilaporkan', true)->orWhereColumn('r.id', '<', 'u.id')));
    }

    /**
     * @param  array<int, int|string>|null  $departementIds  null = semua jurusan
     */
    public function hapusDobel(?array $departementIds = null): int
    {
        // MySQL tidak boleh DELETE dengan subquery ke tabel yang sama -
        // ambil id-nya dulu. Syarat dilaporkan = false diulang sebagai
        // pengaman supaya baris yang sudah dilaporkan tidak mungkin terhapus.
        $ids = $this->dobelQuery($departementIds)->pluck('u.id')->all();

        return $ids
            ? ExamRegistration::whereIn('id', $ids)->where('dilaporkan', false)->delete()
            : 0;
    }
}
