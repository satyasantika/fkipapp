<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Bersihkan baris exam_registrations BELUM dilaporkan yang terlanjur dibuat
 * oleh sinkronisasi Sintesys padahal ujian yang sama (mahasiswa, jenis
 * ujian, tanggal) sudah dilaporkan. Default hanya menampilkan kandidat;
 * penghapusan butuh --force. Baris yang sudah dilaporkan TIDAK PERNAH
 * dihapus: syarat dilaporkan = 0 diulang di query DELETE, dan jumlah + ID
 * baris dilaporkan dicek sebelum/sesudah di dalam transaksi (rollback kalau
 * berubah).
 */
class HapusUjianDobelSinkron extends Command
{
    protected $signature = 'exam:hapus-dobel-sinkron
        {--force : Benar-benar hapus (tanpa ini hanya menampilkan kandidat)}
        {--departement= : Batasi ke satu jurusan (id)}';

    protected $description = 'Hapus ujian belum dilaporkan yang dobel dengan ujian sudah dilaporkan di tanggal yang sama';

    public function handle(): int
    {
        $kandidat = $this->kandidat();

        if ($kandidat->isEmpty()) {
            $this->info('Tidak ada ujian dobel.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID dobel', 'ID dilaporkan', 'NIM', 'Nama', 'Jenis', 'Tanggal', 'Ujian ke', 'Jurusan'],
            $kandidat->map(fn ($row) => [
                $row->id, $row->id_dilaporkan, $row->nim, $row->nama, $row->singkat_ujian,
                $row->tanggal_ujian, $row->ujian_ke, $row->departement_id,
            ])->all(),
        );
        $this->line("Kandidat: {$kandidat->count()} baris belum dilaporkan.");

        if (! $this->option('force')) {
            $this->warn('Dry-run: tidak ada yang dihapus. Jalankan dengan --force untuk menghapus.');

            return self::SUCCESS;
        }

        if ($this->input->isInteractive() && ! $this->confirm('Hapus baris-baris di atas?')) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        $ids = $kandidat->pluck('id')->unique()->values()->all();

        $backupPath = 'backup/ujian-dobel-'.now()->format('Ymd-His').'.json';
        Storage::disk('local')->put(
            $backupPath,
            DB::table('exam_registrations')->whereIn('id', $ids)->get()->toJson(JSON_PRETTY_PRINT),
        );

        $dilaporkanSebelum = $this->idDilaporkan();

        $terhapus = DB::transaction(function () use ($ids, $dilaporkanSebelum) {
            $terhapus = DB::table('exam_registrations')
                ->whereIn('id', $ids)
                ->where('dilaporkan', false)
                ->delete();

            if ($this->idDilaporkan() !== $dilaporkanSebelum) {
                throw new \RuntimeException('Data ujian dilaporkan berubah - penghapusan dibatalkan (rollback).');
            }

            return $terhapus;
        });

        $this->info("Terhapus: {$terhapus} baris. Ujian dilaporkan sebelum/sesudah: ".count($dilaporkanSebelum).'/'.count($this->idDilaporkan()).'.');
        $this->line('Backup: '.Storage::disk('local')->path($backupPath));

        return self::SUCCESS;
    }

    private function kandidat()
    {
        return DB::table('exam_registrations as u')
            ->join('exam_registrations as r', function ($join) {
                $join->on('r.student_id', '=', 'u.student_id')
                    ->on('r.exam_type_id', '=', 'u.exam_type_id')
                    ->whereRaw('DATE(r.tanggal_ujian) = DATE(u.tanggal_ujian)')
                    ->where('r.dilaporkan', true);
            })
            ->join('students as s', 's.id', '=', 'u.student_id')
            ->join('exam_types as t', 't.id', '=', 'u.exam_type_id')
            ->where('u.dilaporkan', false)
            ->when($this->option('departement'), fn ($q, $id) => $q->where('u.departement_id', $id))
            ->orderBy('u.tanggal_ujian')
            ->get([
                'u.id', 'r.id as id_dilaporkan', 's.nim', 's.nama', 't.singkat_ujian',
                'u.tanggal_ujian', 'u.ujian_ke', 'u.departement_id',
            ])
            ->unique('id')
            ->values();
    }

    /**
     * @return array<int, int>
     */
    private function idDilaporkan(): array
    {
        return DB::table('exam_registrations')->where('dilaporkan', true)->orderBy('id')->pluck('id')->all();
    }
}
