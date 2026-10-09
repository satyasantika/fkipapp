<?php

namespace App\Console\Commands;

use App\Services\SintesysSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Bersihkan baris exam_registrations BELUM dilaporkan yang dobel (mahasiswa,
 * jenis ujian, tanggal sama) - aturan yang sama dengan pembersihan otomatis
 * saat sinkronisasi (SintesysSyncService::dobelQuery()), tapi untuk semua
 * tanggal sekaligus. Default hanya menampilkan kandidat;
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

    protected $description = 'Hapus ujian belum dilaporkan yang dobel (mahasiswa, jenis, tanggal sama)';

    public function handle(): int
    {
        $kandidat = $this->kandidat();

        if ($kandidat->isEmpty()) {
            $this->info('Tidak ada ujian dobel.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID dobel', 'NIM', 'Nama', 'Jenis', 'Tanggal', 'Ujian ke', 'Jurusan'],
            $kandidat->map(fn ($row) => [
                $row->id, $row->nim, $row->nama, $row->singkat_ujian,
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
        $departementIds = $this->option('departement') ? [$this->option('departement')] : null;

        return app(SintesysSyncService::class)->dobelQuery($departementIds)
            ->join('students as s', 's.id', '=', 'u.student_id')
            ->join('exam_types as t', 't.id', '=', 'u.exam_type_id')
            ->orderBy('u.tanggal_ujian')
            ->get(['u.id', 's.nim', 's.nama', 't.singkat_ujian', 'u.tanggal_ujian', 'u.ujian_ke', 'u.departement_id']);
    }

    /**
     * @return array<int, int>
     */
    private function idDilaporkan(): array
    {
        return DB::table('exam_registrations')->where('dilaporkan', true)->orderBy('id')->pluck('id')->all();
    }
}
