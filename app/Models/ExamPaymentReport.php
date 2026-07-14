<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamPaymentReport extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class, 'lecture_id');
    }

    public function reportdate(): BelongsTo
    {
        return $this->belongsTo(ReportDate::class, 'report_date_id');
    }

    // Accessor di bawah ini mereplikasi persis kolom turunan yang dulu dihitung
    // SQL view `view_exam_payment_reports` (lihat migration lama
    // 2024_01_25_165450_create_examinations_view.php sebelum view dihapus).
    protected function dosen(): Attribute
    {
        return Attribute::make(get: function () {
            $lecture = $this->lecture;

            if (! $lecture) {
                return null;
            }

            $depan = $lecture->gelar_depan ? $lecture->gelar_depan.' ' : '';

            return trim($depan.$lecture->nama.', '.$lecture->gelar_belakang);
        });
    }

    protected function departemenId(): Attribute
    {
        return Attribute::make(get: fn () => $this->lecture?->departement_id);
    }

    protected function statusNama(): Attribute
    {
        return Attribute::make(get: fn () => (int) $this->status === 1 ? 'ASN' : 'NON ASN');
    }

    protected function golonganNama(): Attribute
    {
        return Attribute::make(get: fn () => match ((int) $this->golongan) {
            3 => 'III',
            4 => 'IV',
            default => 'BELUM SET',
        });
    }

    protected function jumlahHonorPembimbing(): Attribute
    {
        return Attribute::make(get: fn () => $this->honor_pembimbing * ($this->banyak_membimbing1 + $this->banyak_membimbing2));
    }

    protected function jumlahHonorPengujiSkripsi(): Attribute
    {
        return Attribute::make(get: fn () => $this->honor_penguji_skripsi * $this->banyak_menguji_skripsi);
    }

    protected function jumlahHonorPengujiProposal(): Attribute
    {
        return Attribute::make(get: fn () => $this->honor_penguji_proposal * $this->banyak_menguji_proposal);
    }

    protected function jumlahHonorPengujiSeminar(): Attribute
    {
        return Attribute::make(get: fn () => $this->honor_penguji_seminar * $this->banyak_menguji_seminar);
    }

    protected function totalHonor(): Attribute
    {
        return Attribute::make(get: fn () => $this->jumlah_honor_pembimbing
            + $this->jumlah_honor_penguji_skripsi
            + $this->jumlah_honor_penguji_proposal
            + $this->jumlah_honor_penguji_seminar);
    }

    protected function persenPajak(): Attribute
    {
        return Attribute::make(get: function () {
            if ((int) $this->golongan === 4 && (int) $this->status === 1) {
                return 0.15;
            }

            // Bug lama di SQL view: kondisi ini pakai "npwp=NULL", yang di SQL
            // selalu bernilai UNKNOWN/false (bukan IS NULL) — jadi golongan III
            // TIDAK PERNAH benar-benar kena tarif 0.06, selalu jatuh ke default
            // 0.05 di bawah. Dipertahankan apa adanya di sini supaya potongan
            // pajak siapa pun tidak berubah diam-diam lewat refactor ini —
            // perbaikan sesungguhnya (mis. is_null($this->npwp)) perlu
            // keputusan terpisah karena berdampak ke potongan pajak nyata.
            if ((int) $this->golongan === 3 && false) {
                return 0.06;
            }

            return 0.05;
        });
    }

    protected function potongPajak(): Attribute
    {
        return Attribute::make(get: fn () => $this->persen_pajak * $this->total_honor);
    }

    protected function honorDibayar(): Attribute
    {
        return Attribute::make(get: fn () => $this->total_honor - $this->potong_pajak);
    }
}
