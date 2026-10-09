<?php

namespace App\Services;

use App\Models\Departement;
use App\Models\Lecture;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Isi NUPTK dosen secara massal - dipakai tombol "Tempel NUPTK" dan "Isi
 * NUPTK dari Sintesys" di menu Dosen. Sinkronisasi ujian mencocokkan dosen
 * HANYA lewat NUPTK (lihat SintesysSyncService), jadi NUPTK dosen lama
 * (yang dulu cuma punya NIDN) perlu diisi dulu supaya tidak tercipta dosen
 * dobel. Kunci pencocokan di sini adalah NIDN.
 */
class LectureNuptkService
{
    /**
     * Ubah teks tempelan (biasanya dari Excel) jadi baris. Satu baris:
     * nidn, nuptk, nama (opsional), kode jurusan (opsional) - dipisah tab,
     * titik koma, koma, atau minimal dua spasi. Baris yang kolom pertamanya
     * bukan angka (mis. baris judul) diabaikan.
     *
     * @return array<int, array{baris: int, nidn: string, nuptk: ?string, nama: ?string, departement_id: ?string}>
     */
    public function parse(string $text): array
    {
        $rows = [];

        foreach (preg_split('/\R/', $text) as $index => $line) {
            if (trim($line) === '') {
                continue;
            }

            // Tempelan Excel dipisah tab - kalau ada tab, HANYA tab yang
            // dipakai supaya koma di nama bergelar ("Dr. X, M.Pd.") aman.
            $pemisah = match (true) {
                str_contains($line, "\t") => '/\t/',
                str_contains($line, ';') => '/;/',
                default => '/,|\s{2,}/',
            };
            $cols = array_map('trim', preg_split($pemisah, trim($line)));

            if (! ctype_digit($cols[0] ?? '')) {
                continue;
            }

            $rows[] = [
                'baris' => $index + 1,
                'nidn' => $cols[0],
                'nuptk' => filled($cols[1] ?? null) ? $cols[1] : null,
                'nama' => filled($cols[2] ?? null) ? $cols[2] : null,
                'departement_id' => filled($cols[3] ?? null) ? $cols[3] : null,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array{baris?: int, nidn: string, nuptk: ?string, nama?: ?string, departement_id?: ?string}>  $rows
     * @param  bool  $bolehBuat  buat dosen baru kalau NIDN tidak ditemukan (butuh nama + kode jurusan)
     * @param  bool  $timpa  timpa NUPTK yang sudah terisi
     * @return array{diperbarui: int, dibuat: int, tidak_berubah: int, dilewati: array<int, string>}
     */
    public function apply(array $rows, bool $bolehBuat = true, bool $timpa = true): array
    {
        $hasil = ['diperbarui' => 0, 'dibuat' => 0, 'tidak_berubah' => 0, 'dilewati' => []];

        DB::transaction(function () use ($rows, $bolehBuat, $timpa, &$hasil) {
            foreach ($rows as $row) {
                $label = isset($row['baris']) ? "Baris {$row['baris']} (NIDN {$row['nidn']})" : "NIDN {$row['nidn']}";
                $nuptk = $row['nuptk'] ?? null;

                if (! $nuptk) {
                    $hasil['dilewati'][] = "{$label}: NUPTK kosong.";

                    continue;
                }

                $lecture = Lecture::where('nidn', $row['nidn'])->first();

                $pemilikLain = Lecture::where('nuptk', $nuptk)
                    ->when($lecture, fn ($q) => $q->whereKeyNot($lecture->id))
                    ->first();

                if ($pemilikLain) {
                    $hasil['dilewati'][] = "{$label}: NUPTK {$nuptk} sudah dipakai {$pemilikLain->nama}.";

                    continue;
                }

                if ($lecture) {
                    if ($lecture->nuptk === $nuptk) {
                        $hasil['tidak_berubah']++;
                    } elseif (filled($lecture->nuptk) && ! $timpa) {
                        $hasil['tidak_berubah']++;
                    } else {
                        $lecture->update(['nuptk' => $nuptk]);
                        $hasil['diperbarui']++;
                    }

                    continue;
                }

                if (! $bolehBuat) {
                    $hasil['dilewati'][] = "{$label}: dosen dengan NIDN ini tidak ditemukan.";

                    continue;
                }

                if (! filled($row['nama'] ?? null) || ! filled($row['departement_id'] ?? null)) {
                    $hasil['dilewati'][] = "{$label}: NIDN tidak ditemukan; isi nama & kode jurusan untuk membuat dosen baru.";

                    continue;
                }

                if (! Departement::whereKey($row['departement_id'])->exists()) {
                    $hasil['dilewati'][] = "{$label}: kode jurusan {$row['departement_id']} tidak ada.";

                    continue;
                }

                Lecture::create([
                    'nidn' => $row['nidn'],
                    'nuptk' => $nuptk,
                    'nama' => $row['nama'],
                    'departement_id' => $row['departement_id'],
                ]);
                $hasil['dibuat']++;
            }
        });

        return $hasil;
    }

    /**
     * Isi NUPTK yang masih kosong dari pasangan NIDN-NUPTK penguji di data
     * ujian Sintesys (semua jurusan). Tidak membuat dosen baru dan tidak
     * menimpa NUPTK yang sudah terisi.
     *
     * @return array{diperbarui: int, dibuat: int, tidak_berubah: int, dilewati: array<int, string>, gagal_jurusan: array<int, string>}
     */
    public function fillFromSintesys(string $tanggalMulai, string $tanggalSelesai): array
    {
        $sync = app(SintesysSyncService::class);
        $pasangan = [];
        $gagalJurusan = [];

        foreach (Departement::all() as $departement) {
            $exams = [];

            // Sintesys menolak rentang > 183 hari - pecah per 180 hari.
            foreach ($this->potongRentang($tanggalMulai, $tanggalSelesai) as [$mulai, $selesai]) {
                try {
                    $exams = array_merge($exams, $sync->fetchExams((string) $departement->id, $mulai, $selesai));
                } catch (\Throwable $e) {
                    $gagalJurusan[] = $departement->nama;

                    continue 2;
                }
            }

            foreach ($exams as $exam) {
                foreach ($exam['penguji'] ?? [] as $penguji) {
                    if (filled($penguji['nidn'] ?? null) && filled($penguji['nuptk'] ?? null)) {
                        $pasangan[(string) $penguji['nidn']] = (string) $penguji['nuptk'];
                    }
                }
            }
        }

        $rows = collect($pasangan)
            // key array PHP berupa angka murni otomatis jadi int - cast balik.
            ->map(fn ($nuptk, $nidn) => ['nidn' => (string) $nidn, 'nuptk' => (string) $nuptk])
            ->values()
            ->all();

        return $this->apply($rows, bolehBuat: false, timpa: false) + ['gagal_jurusan' => $gagalJurusan];
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    private function potongRentang(string $tanggalMulai, string $tanggalSelesai): array
    {
        $potongan = [];
        $mulai = Carbon::parse($tanggalMulai)->startOfDay();
        $akhir = Carbon::parse($tanggalSelesai)->startOfDay();

        while ($mulai->lte($akhir)) {
            $selesai = $mulai->copy()->addDays(179)->min($akhir);
            $potongan[] = [$mulai->toDateString(), $selesai->toDateString()];
            $mulai = $selesai->copy()->addDay();
        }

        return $potongan;
    }
}
